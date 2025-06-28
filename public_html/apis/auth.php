<?php
/**
 * API de Autenticación - Sistema de Condominios
 */

// Definir acceso seguro
define('SECURE_ACCESS', true);

// Cargar configuración de seguridad
require_once __DIR__ . '/../config/security.php';
SecurityConfig::loadConfig();

// Headers de seguridad
SecurityConfig::setSecurityHeaders();
header('Content-Type: application/json; charset=utf-8');

// Iniciar sesión
session_start();

// Cargar dependencias
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/crypto.php';
require_once 'Models.php';

/**
 * Clase para manejar autenticación
 */
class AuthController {
    
    /**
     * Procesar solicitudes de autenticación
     */
    public static function handleRequest() {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            
            // Obtener acción desde GET o POST
            $action = $_GET['action'] ?? $_POST['action'] ?? '';
            
            if (SecurityConfig::isDebugMode()) {
                error_log("=== DEBUG AUTH CONTROLLER ===");
                error_log("Method: $method");
                error_log("Action: $action");
                error_log("GET: " . json_encode($_GET));
                error_log("POST: " . json_encode($_POST));
            }
            
            // Validar método HTTP
            if (!in_array($method, ['GET', 'POST'])) {
                throw new Exception('Método no permitido', 405);
            }
            
            // Enrutar según la acción
            switch ($action) {
                case 'login_admin':
                    if ($method !== 'POST') {
                        throw new Exception('Método no permitido para login', 405);
                    }
                    return self::loginAdmin();
                    
                case 'login_resident':
                    if ($method !== 'POST') {
                        throw new Exception('Método no permitido para login', 405);
                    }
                    return self::loginResident();
                    
                case 'register_admin':
                    if ($method !== 'POST') {
                        throw new Exception('Método no permitido para registro', 405);
                    }
                    return self::registerAdmin();
                    
                case 'register_resident':
                    if ($method !== 'POST') {
                        throw new Exception('Método no permitido para registro', 405);
                    }
                    return self::registerResident();
                    
                case 'logout':
                    return self::logout();
                    
                case 'check_session':
                    return self::checkSession();
                    
                case 'get_condominios':
                    return self::getCondominios();
                    
                case 'get_calles':
                    return self::getCalles();
                    
                case 'get_casas':
                    return self::getCasas();
                    
                case 'get_empleados':
                    return self::getEmpleados();
                    
                case 'create_condominio':
                    if ($method !== 'POST') {
                        throw new Exception('Método no permitido para crear condominio', 405);
                    }
                    return self::createCondominio();
                    
                case 'create_calle':
                    if ($method !== 'POST') {
                        throw new Exception('Método no permitido para crear calle', 405);
                    }
                    return self::createCalle();
                    
                case 'create_casa':
                    if ($method !== 'POST') {
                        throw new Exception('Método no permitido para crear casa', 405);
                    }
                    return self::createCasa();
                    
                case 'debug_auth':
                    if (!SecurityConfig::isDebugMode()) {
                        throw new Exception('Debug no disponible en producción', 403);
                    }
                    return self::debugAuth();
                    
                default:
                    throw new Exception('Acción no válida', 400);
            }
            
        } catch (Exception $e) {
            return self::errorResponse($e->getMessage(), $e->getCode() ?: 500);
        }
    }
    
    /**
     * Login de administrador
     */
    private static function loginAdmin() {
        $data = self::getPostData();
        
        // Validar datos requeridos
        if (empty($data['email']) || empty($data['password'])) {
            throw new Exception('Email y contraseña son requeridos', 400);
        }
        
        // Validar formato de email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Formato de email inválido', 400);
        }
        
        // Autenticar administrador
        $admin = Admin::authenticate($data['email'], $data['password']);
        
        if (!$admin) {
            // Debug información en modo desarrollo
            $debugInfo = '';
            if (SecurityConfig::isDebugMode()) {
                $testAdmin = Admin::findByEmail($data['email']);
                if ($testAdmin) {
                    $debugInfo = ' - Usuario encontrado pero contraseña incorrecta';
                    error_log("Debug Login: Usuario encontrado para email: " . $data['email']);
                    error_log("Debug Login: Hash en BD: " . substr($testAdmin->getAttribute('contrasena'), 0, 50) . "...");
                } else {
                    $debugInfo = ' - Usuario no encontrado con email: ' . $data['email'];
                    error_log("Debug Login: Usuario NO encontrado para email: " . $data['email']);
                }
            }
            throw new Exception('Credenciales inválidas' . $debugInfo, 401);
        }
        
        // Obtener datos desencriptados para la sesión
        $adminData = $admin->toArray();
        
        // Crear sesión (mantener estructura original para compatibilidad)
        $_SESSION['user_type'] = 'admin';
        $_SESSION['user_id'] = $admin->getAttribute('id_admin');
        $_SESSION['user_email'] = $adminData['correo']; // Email desencriptado
        $_SESSION['user_name'] = $admin->getAttribute('nombres') . ' ' . $admin->getAttribute('apellido1');
        
        // También mantener la estructura original en $_SESSION['user'] para compatibilidad
        $_SESSION['user'] = $adminData;
        $_SESSION['user']['id_admin'] = $admin->getAttribute('id_admin');
        
        // Obtener condominios del administrador usando el nuevo sistema de permisos
        try {
            $db = Database::getConnection();
            $sql = "SELECT c.* FROM condominios c 
                    INNER JOIN admin_cond ac ON c.id_condominio = ac.id_condominio 
                    WHERE ac.id_admin = :id_admin 
                    ORDER BY c.nombre";
            $stmt = $db->prepare($sql);
            $stmt->execute(['id_admin' => $admin->getAttribute('id_admin')]);
            $condominios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener condominios del admin: " . $e->getMessage());
            $condominios = [];
        }
        
        return self::successResponse([
            'message' => 'Login exitoso',
            'data' => [
                'user' => $adminData,
                'condominios' => $condominios
            ]
        ]);
    }
    
    /**
     * Login de residente
     */
    private static function loginResident() {
        $data = self::getPostData();
        
        // Validar datos requeridos
        if (empty($data['email']) || empty($data['password'])) {
            throw new Exception('Email y contraseña son requeridos', 400);
        }
        
        // Validar formato de email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Formato de email inválido', 400);
        }
        
        // Autenticar residente
        $persona = Persona::authenticate($data['email'], $data['password']);
        
        if (!$persona) {
            throw new Exception('Credenciales inválidas', 401);
        }
        
        // Crear sesión
        $_SESSION['user_type'] = 'resident';
        $_SESSION['user_id'] = $persona->getAttribute('id_persona');
        $_SESSION['user_email'] = $persona->getAttribute('correo_electronico');
        $_SESSION['user_name'] = $persona->getAttribute('nombres') . ' ' . $persona->getAttribute('apellido1');
        $_SESSION['is_admin'] = $persona->isAdmin();
        
        return self::successResponse([
            'message' => 'Login exitoso',
            'data' => [
                'user' => $persona->toArray(),
                'casa' => $persona->getCasa(),
                'is_admin' => $persona->isAdmin()
            ]
        ]);
    }
    
    /**
     * Registro de administrador
     */
    private static function registerAdmin() {
        $data = self::getPostData();
        
        // Validar datos requeridos
        $required = ['nombres', 'apellido1', 'correo', 'contrasena'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("El campo {$field} es requerido", 400);
            }
        }
        
        // Validar formato de email
        if (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Formato de email inválido', 400);
        }
        
        // Validar longitud de contraseña
        if (strlen($data['contrasena']) < 6) {
            throw new Exception('La contraseña debe tener al menos 6 caracteres', 400);
        }
        
        // Crear administrador
        $admin = Admin::createAdmin($data);
        
        return self::successResponse([
            'message' => 'Administrador registrado exitosamente. Para gestionar condominios, debe crear o ser asignado a uno.',
            'data' => [
                'user' => $admin->toArray()
            ]
        ]);
    }
    
    /**
     * Registro de residente
     */
    private static function registerResident() {
        $data = self::getPostData();
        
        // Validar datos requeridos
        $required = ['nombres', 'apellido1', 'correo_electronico', 'contrasena', 'curp'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("El campo {$field} es requerido", 400);
            }
        }
        
        // Validar formato de email
        if (!filter_var($data['correo_electronico'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Formato de email inválido', 400);
        }
        
        // Validar CURP (18 caracteres)
        if (strlen($data['curp']) !== 18) {
            throw new Exception('CURP debe tener 18 caracteres', 400);
        }
        
        // Validar longitud de contraseña
        if (strlen($data['contrasena']) < 6) {
            throw new Exception('La contraseña debe tener al menos 6 caracteres', 400);
        }
        
        // Crear residente
        $persona = Persona::createResident($data);
        
        return self::successResponse([
            'message' => 'Residente registrado exitosamente',
            'data' => [
                'user' => $persona->toArray()
            ]
        ]);
    }
    
    /**
     * Cerrar sesión
     */
    private static function logout() {
        session_destroy();
        return self::successResponse(['message' => 'Sesión cerrada exitosamente']);
    }
    
    /**
     * Verificar sesión activa
     */
    private static function checkSession() {
        if (!isset($_SESSION['user_type']) || !isset($_SESSION['user_id'])) {
            throw new Exception('No hay sesión activa', 401);
        }
        
        $response = [
            'authenticated' => true,
            'user_type' => $_SESSION['user_type'],
            'user_id' => $_SESSION['user_id'],
            'user_email' => $_SESSION['user_email'] ?? '',
            'user_name' => $_SESSION['user_name'] ?? ''
        ];
        
        // Para compatibilidad con el sistema anterior, incluir datos del usuario
        if (isset($_SESSION['user'])) {
            $response['user'] = $_SESSION['user'];
        }
        
        return self::successResponse($response);
    }
    
    /**
     * Obtener condominios
     */
    private static function getCondominios() {
        // Si hay sesión de admin, filtrar por permisos
        if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin' && isset($_SESSION['user_id'])) {
            $id_admin = (int)$_SESSION['user_id'];
            
            try {
                $db = Database::getConnection();
                $sql = "SELECT c.* FROM condominios c 
                        INNER JOIN admin_cond ac ON c.id_condominio = ac.id_condominio 
                        WHERE ac.id_admin = :id_admin 
                        ORDER BY c.nombre";
                $stmt = $db->prepare($sql);
                $stmt->execute(['id_admin' => $id_admin]);
                $condominios = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                return self::successResponse($condominios);
            } catch (PDOException $e) {
                error_log("Error al obtener condominios del admin: " . $e->getMessage());
                throw new Exception('Error al obtener condominios', 500);
            }
        } else {
            // Si no hay sesión de admin, obtener todos (para formularios públicos)
            $condominios = Condominio::getAll();
            return self::successResponse($condominios);
        }
    }
    
    /**
     * Obtener calles de un condominio
     */
    private static function getCalles() {
        $condominioId = $_GET['condominio_id'] ?? '';
        
        if (empty($condominioId)) {
            throw new Exception('ID de condominio requerido', 400);
        }
        
        $condominio = Condominio::find($condominioId);
        if (!$condominio) {
            throw new Exception('Condominio no encontrado', 404);
        }
        
        $calles = $condominio->getCalles();
        return self::successResponse($calles);
    }
    
    /**
     * Obtener casas de un condominio
     */
    private static function getCasas() {
        $condominioId = $_GET['condominio_id'] ?? '';
        
        if (empty($condominioId)) {
            throw new Exception('ID de condominio requerido', 400);
        }
        
        $condominio = Condominio::find($condominioId);
        if (!$condominio) {
            throw new Exception('Condominio no encontrado', 404);
        }
        
        $casas = $condominio->getCasas();
        return self::successResponse($casas);
    }
    
    /**
     * Obtener empleados de un condominio
     */
    private static function getEmpleados() {
        $condominioId = $_GET['condominio_id'] ?? '';
        
        if (empty($condominioId)) {
            throw new Exception('ID de condominio requerido', 400);
        }
        
        try {
            $db = Database::getConnection();
            $sql = "SELECT * FROM empleados_condominio WHERE id_condominio = :id_condominio ORDER BY nombres";
            $stmt = $db->prepare($sql);
            $stmt->execute(['id_condominio' => $condominioId]);
            $empleados = $stmt->fetchAll();
            
            return self::successResponse($empleados);
        } catch (PDOException $e) {
            error_log("Error al obtener empleados: " . $e->getMessage());
            throw new Exception('Error al obtener empleados', 500);
        }
    }
    
    /**
     * Obtener ID del administrador de la sesión actual
     */
    private static function getCurrentAdminId() {
        if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id_admin'])) {
            throw new Exception('Sesión no válida. Debes iniciar sesión', 401);
        }
        
        return (int)$_SESSION['user']['id_admin'];
    }
    
    /**
     * Verificar que hay una sesión válida de administrador
     */
    private static function requireAdminSession() {
        if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id_admin'])) {
            throw new Exception('Acceso denegado. Debes iniciar sesión como administrador', 401);
        }
        
        return (int)$_SESSION['user']['id_admin'];
    }

    /**
     * Obtener datos POST de forma segura (soporta JSON y FormData)
     */
    private static function getPostData() {
        // Verificar si es FormData
        if (!empty($_POST)) {
            return $_POST;
        }
        
        // Si no hay $_POST, intentar JSON
        $input = file_get_contents('php://input');
        if (empty($input)) {
            return [];
        }
        
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Datos inválidos', 400);
        }
        
        return $data ?: [];
    }
    
    /**
     * Respuesta de éxito
     */
    private static function successResponse($data = null) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Crear condominio
     */
    private static function createCondominio() {
        // Verificar sesión de administrador
        $id_admin = self::requireAdminSession();
        
        $data = self::getPostData();
        
        // Validar datos requeridos
        if (empty($data['nombre']) || empty($data['direccion'])) {
            throw new Exception('Nombre y dirección son requeridos', 400);
        }
        
        try {
            $db = Database::getConnection();
            $db->beginTransaction();
            
            // 1. Crear el condominio
            $sql = "INSERT INTO condominios (nombre, direccion) VALUES (:nombre, :direccion)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'nombre' => trim($data['nombre']),
                'direccion' => trim($data['direccion'])
            ]);
            
            $id_condominio = $db->lastInsertId();
            
            // 2. Asignar condominio al administrador en admin_cond
            $sql_assign = "INSERT INTO admin_cond (id_admin, id_condominio) VALUES (:id_admin, :id_condominio)";
            $stmt_assign = $db->prepare($sql_assign);
            $stmt_assign->execute([
                'id_admin' => $id_admin,
                'id_condominio' => $id_condominio
            ]);
            
            $db->commit();
            
            self::successResponse([
                'id' => $id_condominio, 
                'message' => 'Condominio creado exitosamente y asignado a tu cuenta'
            ]);
            
        } catch (PDOException $e) {
            $db->rollBack();
            error_log("Error al crear condominio: " . $e->getMessage());
            throw new Exception('Error al crear el condominio', 500);
        }
    }
    
    /**
     * Crear calles (múltiples) con validación de permisos
     */
    private static function createCalle() {
        // Verificar sesión de administrador
        $id_admin = self::requireAdminSession();
        
        $data = self::getPostData();
        
        // Validar datos requeridos
        if (empty($data['id_condominio']) || empty($data['nombres'])) {
            throw new Exception('ID de condominio y nombres son requeridos', 400);
        }
        
        $id_condominio = (int)$data['id_condominio'];
        $nombres_raw = trim($data['nombres']);
        $descripcion = trim($data['descripcion'] ?? '');
        
        // Verificar permisos sobre el condominio
        try {
            $db = Database::getConnection();
            $sql_check = "SELECT 1 FROM admin_cond WHERE id_admin = :id_admin AND id_condominio = :id_condominio";
            $stmt_check = $db->prepare($sql_check);
            $stmt_check->execute(['id_admin' => $id_admin, 'id_condominio' => $id_condominio]);
            
            if (!$stmt_check->fetchColumn()) {
                throw new Exception('No tienes permisos para agregar calles a este condominio', 403);
            }
        } catch (PDOException $e) {
            error_log("Error verificando permisos: " . $e->getMessage());
            throw new Exception('Error al verificar permisos', 500);
        }
        
        // Separar nombres por diferentes delimitadores
        $nombres = [];
        $temp = preg_split('/[;,\n\r]+/', $nombres_raw);
        
        foreach ($temp as $nombre) {
            $nombre = trim($nombre);
            if (!empty($nombre)) {
                $nombres[] = $nombre;
            }
        }
        
        if (empty($nombres)) {
            throw new Exception('Debe proporcionar al menos un nombre de calle', 400);
        }
        
        $calle = new Calle();
        $resultados = [];
        $errores = [];
        
        foreach ($nombres as $nombre) {
            try {
                $result = $calle->create([
                    'id_condominio' => $id_condominio,
                    'nombre' => $nombre,
                    'descripcion' => $descripcion
                ], $id_admin);
                
                if ($result) {
                    $resultados[] = ['nombre' => $nombre, 'id' => $result];
                } else {
                    $errores[] = "Error al crear calle: $nombre";
                }
            } catch (Exception $e) {
                $errores[] = "Error en calle '$nombre': " . $e->getMessage();
            }
        }
        
        if (empty($resultados)) {
            throw new Exception('No se pudo crear ninguna calle: ' . implode(', ', $errores));
        }
        
        $mensaje = count($resultados) . ' calle(s) creada(s) exitosamente';
        if (!empty($errores)) {
            $mensaje .= '. Errores: ' . implode(', ', $errores);
        }
        
        self::successResponse([
            'calles_creadas' => $resultados,
            'total' => count($resultados),
            'errores' => $errores,
            'message' => $mensaje
        ]);
    }
    
    /**
     * Crear casas (múltiples automáticamente)
     */
    private static function createCasa() {
        // Verificar sesión de administrador
        $id_admin = self::requireAdminSession();
        
        $data = self::getPostData();
        
        // Validar datos requeridos
        if (empty($data['id_condominio']) || empty($data['id_calle']) || 
            empty($data['numero_inicio']) || empty($data['cantidad'])) {
            throw new Exception('ID de condominio, ID de calle, número inicial y cantidad son requeridos', 400);
        }
        
        $id_condominio = (int)$data['id_condominio'];
        $id_calle = (int)$data['id_calle'];
        $numero_inicio = (int)$data['numero_inicio'];
        $cantidad = (int)$data['cantidad'];
        $prefijo = trim($data['prefijo'] ?? '');
        
        // Verificar permisos sobre el condominio
        try {
            $db = Database::getConnection();
            $sql_check = "SELECT 1 FROM admin_cond WHERE id_admin = :id_admin AND id_condominio = :id_condominio";
            $stmt_check = $db->prepare($sql_check);
            $stmt_check->execute(['id_admin' => $id_admin, 'id_condominio' => $id_condominio]);
            
            if (!$stmt_check->fetchColumn()) {
                throw new Exception('No tienes permisos para agregar casas a este condominio', 403);
            }
        } catch (PDOException $e) {
            error_log("Error verificando permisos para casas: " . $e->getMessage());
            throw new Exception('Error al verificar permisos', 500);
        }
        
        // Validar límites
        if ($numero_inicio < 1) {
            throw new Exception('El número inicial debe ser mayor a 0', 400);
        }
        
        if ($cantidad < 1 || $cantidad > 500) {
            throw new Exception('La cantidad debe estar entre 1 y 500', 400);
        }
        
        $casa = new Casa();
        $resultados = [];
        $errores = [];
        
        for ($i = 0; $i < $cantidad; $i++) {
            $numero_casa = $numero_inicio + $i;
            $nombre_casa = $prefijo . $numero_casa;
            
            try {
                $result = $casa->create([
                    'id_condominio' => $id_condominio,
                    'id_calle' => $id_calle,
                    'casa' => $nombre_casa
                ]);
                
                if ($result) {
                    $resultados[] = ['nombre' => $nombre_casa, 'numero' => $numero_casa, 'id' => $result];
                } else {
                    $errores[] = "Error al crear casa: $nombre_casa";
                }
            } catch (Exception $e) {
                $errores[] = "Error en casa '$nombre_casa': " . $e->getMessage();
            }
        }
        
        if (empty($resultados)) {
            throw new Exception('No se pudo crear ninguna casa: ' . implode(', ', $errores));
        }
        
        $mensaje = count($resultados) . ' casa(s) creada(s) exitosamente';
        if (!empty($errores)) {
            $mensaje .= '. Errores: ' . implode(', ', $errores);
        }
        
        self::successResponse([
            'casas_creadas' => $resultados,
            'total' => count($resultados),
            'rango' => "Del $numero_inicio al " . ($numero_inicio + $cantidad - 1),
            'errores' => $errores,
            'message' => $mensaje
        ]);
    }
    
    /**
     * Debug de autenticación
     */
    private static function debugAuth() {
        $data = self::getPostData();
        
        if (empty($data['email'])) {
            throw new Exception('Email requerido para debug', 400);
        }
        
        $email = $data['email'];
        $password = $data['password'] ?? '';
        
        // Buscar usuario
        $admin = Admin::findByEmail($email);
        
        $debug = [
            'email_buscado' => $email,
            'usuario_encontrado' => $admin ? true : false,
            'password_proporcionada' => !empty($password)
        ];
        
        if ($admin) {
            $debug['datos_usuario'] = [
                'id' => $admin->getAttribute('id_admin'),
                'nombres' => $admin->getAttribute('nombres'),
                'email_en_bd' => substr($admin->getAttribute('correo'), 0, 50) . '...',
                'password_hash' => substr($admin->getAttribute('contrasena'), 0, 50) . '...'
            ];
            
            if (!empty($password)) {
                $debug['verificacion_password'] = $admin->verifyPassword($password);
            }
        }
        
        return self::successResponse($debug);
    }
    
    /**
     * Respuesta de error
     */
    private static function errorResponse($message, $code = 500) {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'error' => $message
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// Procesar la solicitud
AuthController::handleRequest();
?>
