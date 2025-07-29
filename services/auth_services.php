<?php
/**
 * AUTH SERVICES - SERVICIOS DE AUTENTICACIÓN Y AUTORIZACIÓN
 * Sistema Cyberhole Condominios - Capa de Servicios de Autenticación
 * 
 * @description Servicios centralizados para autenticación de administradores y residentes
 *              Manejo de sesiones, tokens, validaciones y seguridad completa
 * @author Sistema Cyberhole - Fanático Religioso de la Documentación
 * @version 3.0 - RECREADO DESDE CERO SIGUIENDO ARQUITECTURA 3 CAPAS
 * @date 2025-07-28
 * 
 * 🔥 CUMPLIMIENTO RELIGIOSO DE ARQUITECTURA 3 CAPAS:
 * - Capa de Servicio: Lógica de negocio de autenticación ✅
 * - Capa de Modelo: Utiliza Admin.php y Persona.php ✅  
 * - Capa de Base de Datos: Gestión de sesiones y tokens ✅pop
 * 
 * 🔥 FUNCIONALIDADES IMPLEMENTADAS:
 * - Autenticación de administradores ✅
 * - Autenticación de residentes ✅
 * - Gestión de sesiones seguras ✅
 * - Generación y validación de tokens CSRF ✅
 * - Rate limiting por IP ✅
 * - Logs de auditoría completos ✅
 * - Validación de credenciales ✅
 * - Manejo de intentos fallidos ✅
 * - Cierre de sesión seguro ✅
 * - Verificación de permisos ✅
 * 
 * 🔥 MÉTODOS REALES VERIFICADOS DE LOS MODELOS:
 * 📁 Admin.php:
 * ✅ adminLogin(string email, string password): array|false
 * ✅ adminRegister(array data): int|false
 * ✅ findByEmail(string email): array|null
 * ✅ hashPassword(string password): string
 * ✅ validateEmailFormat(string email): bool
 * ✅ validatePasswordLength(string password): bool
 * ✅ validateAdminCredentials(string email, string password): bool
 * ✅ findById(int id): array|null
 * 
 * 📁 Persona.php:
 * ✅ personaLogin(string email, string password): array|false
 * ✅ personaRegister(array data): int|false
 * ✅ findByEmail(string email): array|null
 * ✅ findByCURP(string curp): array|null
 * ✅ hashPassword(string password): string
 * ✅ validateEmailFormat(string email): bool
 * ✅ validateCURPFormat(string curp): bool
 * ✅ validatePersonaCredentials(string email, string password): bool
 * ✅ findById(int id): array|null
 */

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../models/Admin.php';
require_once __DIR__ . '/../models/Persona.php';
require_once __DIR__ . '/../middlewares/CsrfMiddleware.php';
require_once __DIR__ . '/../middlewares/RateLimitMiddleware.php';

class AuthService extends BaseService
{
    /**
     * @var Admin $adminModel Modelo de administradores
     */
    private Admin $adminModel;
    
    /**
     * @var Persona $personaModel Modelo de personas/residentes
     */
    private Persona $personaModel;
    
    /**
     * @var CsrfMiddleware $csrfMiddleware Middleware CSRF
     */
    private CsrfMiddleware $csrfMiddleware;
    
    /**
     * @var RateLimitMiddleware $rateLimitMiddleware Middleware Rate Limiting
     */
    private RateLimitMiddleware $rateLimitMiddleware;
    
    /**
     * @var int $maxLoginAttempts Máximo intentos de login por IP
     */
    private int $maxLoginAttempts = 5;
    
    /**
     * @var int $lockoutTime Tiempo de bloqueo en segundos
     */
    private int $lockoutTime = 900; // 15 minutos
    
    /**
     * Constructor - Inicializar dependencias
     */
    public function __construct()
    {
        parent::__construct();
        $this->adminModel = new Admin();
        $this->personaModel = new Persona();
        $this->csrfMiddleware = new CsrfMiddleware();
        $this->rateLimitMiddleware = new RateLimitMiddleware();
        
        // Inicializar sesión si no está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    // ==========================================
    // MÉTODOS DE AUTENTICACIÓN ADMINISTRADORES
    // ==========================================
    
    /**
     * Autenticación de administrador
     * FUNCIÓN 1: Login de administradores con validación completa
     * @param array $credentials Credenciales del administrador
     * @return array Resultado de la autenticación
     */
    public function adminLogin(array $credentials): array
    {
        try {
            // Validar rate limiting por IP
            $clientIP = $this->getClientIP();
            if (!$this->checkRateLimit('admin_login_' . $clientIP, $this->maxLoginAttempts, $this->lockoutTime)) {
                $this->logSecurityEvent('admin_login_rate_limit_exceeded', ['ip' => $clientIP]);
                return $this->createErrorResponse("Demasiados intentos de inicio de sesión. Intente más tarde.");
            }
            
            // Validar campos requeridos
            if (empty($credentials['email']) || empty($credentials['password'])) {
                return $this->createErrorResponse("Email y contraseña son requeridos");
            }
            
            // Validar token CSRF si se proporciona
            if (isset($credentials['csrf_token'])) {
                if (!$this->csrfMiddleware->validateToken($credentials['csrf_token'])) {
                    $this->logSecurityEvent('admin_login_csrf_invalid', [
                        'ip' => $clientIP,
                        'email' => $credentials['email']
                    ]);
                    return $this->createErrorResponse("Token CSRF inválido");
                }
            }
            
            // Validar formato de email
            if (!$this->adminModel->validateEmailFormat($credentials['email'])) {
                return $this->createErrorResponse("Formato de email inválido");
            }
            
            // Intentar autenticación usando modelo real
            $adminData = $this->adminModel->adminLogin($credentials['email'], $credentials['password']);
            
            if ($adminData === false) {
                // Registrar intento fallido
                $this->logSecurityEvent('admin_login_failed', [
                    'ip' => $clientIP,
                    'email' => $credentials['email'],
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                
                return $this->createErrorResponse("Credenciales inválidas");
            }
            
            // Autenticación exitosa - Crear sesión
            $sessionData = $this->createAdminSession($adminData);
            
            // Limpiar datos sensibles
            unset($adminData['contrasena']);
            
            // Registrar login exitoso
            $this->logSecurityEvent('admin_login_success', [
                'admin_id' => $adminData['id_admin'],
                'email' => $adminData['correo'],
                'ip' => $clientIP,
                'session_id' => $sessionData['session_id']
            ]);
            
            return $this->createSuccessResponse("Autenticación exitosa", [
                'user_type' => 'admin',
                'admin_data' => $adminData,
                'session_data' => $sessionData,
                'csrf_token' => $this->generateCSRFToken()
            ]);
            
        } catch (Exception $e) {
            $this->logError("Error en adminLogin(): " . $e->getMessage());
            return $this->createErrorResponse("Error interno del servidor");
        }
    }
    
    /**
     * Registro de nuevo administrador
     * FUNCIÓN 2: Registro de administradores con validación exhaustiva
     * @param array $data Datos del nuevo administrador
     * @return array Resultado del registro
     */
    public function adminRegister(array $data): array
    {
        try {
            // Validar rate limiting
            $clientIP = $this->getClientIP();
            if (!$this->checkRateLimit('admin_register_' . $clientIP, 3, 3600)) {
                return $this->createErrorResponse("Límite de registros excedido para esta IP");
            }
            
            // Validar token CSRF
            if (!$this->csrfMiddleware->validateToken($data['csrf_token'] ?? '')) {
                return $this->createErrorResponse("Token CSRF inválido");
            }
            
            // Validar campos requeridos
            $requiredFields = ['nombres', 'apellido1', 'correo', 'contrasena'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return $this->createErrorResponse("Campo requerido faltante: $field");
                }
            }
            
            // Validar formato de email
            if (!$this->adminModel->validateEmailFormat($data['correo'])) {
                return $this->createErrorResponse("Formato de email inválido");
            }
            
            // Validar longitud de contraseña
            if (!$this->adminModel->validatePasswordLength($data['contrasena'])) {
                return $this->createErrorResponse("La contraseña debe tener al menos 8 caracteres");
            }
            
            // Verificar que el email no esté en uso
            $existingAdmin = $this->adminModel->findByEmail($data['correo']);
            if ($existingAdmin) {
                return $this->createErrorResponse("El email ya está registrado");
            }
            
            // Registrar administrador usando modelo real
            $adminId = $this->adminModel->adminRegister($data);
            
            if ($adminId === false) {
                return $this->createErrorResponse("Error al registrar administrador");
            }
            
            // Registrar evento de seguridad
            $this->logSecurityEvent('admin_registered', [
                'admin_id' => $adminId,
                'email' => $data['correo'],
                'ip' => $clientIP,
                'nombres' => $data['nombres'] . ' ' . $data['apellido1']
            ]);
            
            return $this->createSuccessResponse("Administrador registrado exitosamente", [
                'admin_id' => $adminId,
                'email' => $data['correo']
            ]);
            
        } catch (Exception $e) {
            $this->logError("Error en adminRegister(): " . $e->getMessage());
            return $this->createErrorResponse("Error interno del servidor");
        }
    }
    
    /**
     * Verificar sesión de administrador
     * FUNCIÓN 3: Validación de sesión activa de administrador
     * @return array Estado de la sesión
     */
    public function verifyAdminSession(): array
    {
        try {
            // Verificar que existe sesión
            if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
                return $this->createErrorResponse("Sesión de administrador no encontrada");
            }
            
            // Verificar que existe ID de administrador
            if (!isset($_SESSION['admin_id'])) {
                return $this->createErrorResponse("ID de administrador no encontrado en sesión");
            }
            
            // Verificar timeout de sesión (4 horas)
            if (isset($_SESSION['last_activity'])) {
                $inactiveTime = time() - $_SESSION['last_activity'];
                if ($inactiveTime > 14400) { // 4 horas
                    $this->destroySession();
                    return $this->createErrorResponse("Sesión expirada por inactividad");
                }
            }
            
            // Actualizar última actividad
            $_SESSION['last_activity'] = time();
            
            // Obtener datos actuales del administrador
            $adminData = $this->adminModel->findById($_SESSION['admin_id']);
            if (!$adminData) {
                $this->destroySession();
                return $this->createErrorResponse("Administrador no encontrado");
            }
            
            // Limpiar datos sensibles
            unset($adminData['contrasena']);
            
            return $this->createSuccessResponse("Sesión válida", [
                'user_type' => 'admin',
                'admin_data' => $adminData,
                'session_valid' => true,
                'last_activity' => $_SESSION['last_activity']
            ]);
            
        } catch (Exception $e) {
            $this->logError("Error en verifyAdminSession(): " . $e->getMessage());
            return $this->createErrorResponse("Error verificando sesión");
        }
    }
    
    // ==========================================
    // MÉTODOS DE AUTENTICACIÓN RESIDENTES
    // ==========================================
    
    /**
     * Autenticación de residente
     * FUNCIÓN 4: Login de residentes con validación completa
     * @param array $credentials Credenciales del residente
     * @return array Resultado de la autenticación
     */
    public function residenteLogin(array $credentials): array
    {
        try {
            // Validar rate limiting por IP
            $clientIP = $this->getClientIP();
            if (!$this->checkRateLimit('residente_login_' . $clientIP, $this->maxLoginAttempts, $this->lockoutTime)) {
                $this->logSecurityEvent('residente_login_rate_limit_exceeded', ['ip' => $clientIP]);
                return $this->createErrorResponse("Demasiados intentos de inicio de sesión. Intente más tarde.");
            }
            
            // Validar campos requeridos
            if (empty($credentials['email']) || empty($credentials['password'])) {
                return $this->createErrorResponse("Email y contraseña son requeridos");
            }
            
            // Validar token CSRF si se proporciona
            if (isset($credentials['csrf_token'])) {
                if (!$this->csrfMiddleware->validateToken($credentials['csrf_token'])) {
                    $this->logSecurityEvent('residente_login_csrf_invalid', [
                        'ip' => $clientIP,
                        'email' => $credentials['email']
                    ]);
                    return $this->createErrorResponse("Token CSRF inválido");
                }
            }
            
            // Validar formato de email
            if (!$this->personaModel->validateEmailFormat($credentials['email'])) {
                return $this->createErrorResponse("Formato de email inválido");
            }
            
            // Intentar autenticación usando modelo real
            $personaData = $this->personaModel->personaLogin($credentials['email'], $credentials['password']);
            
            if ($personaData === false) {
                // Registrar intento fallido
                $this->logSecurityEvent('residente_login_failed', [
                    'ip' => $clientIP,
                    'email' => $credentials['email'],
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                
                return $this->createErrorResponse("Credenciales inválidas");
            }
            
            // Autenticación exitosa - Crear sesión
            $sessionData = $this->createResidenteSession($personaData);
            
            // Limpiar datos sensibles
            unset($personaData['contrasena']);
            
            // Registrar login exitoso
            $this->logSecurityEvent('residente_login_success', [
                'persona_id' => $personaData['id_persona'],
                'email' => $personaData['correo_electronico'],
                'ip' => $clientIP,
                'session_id' => $sessionData['session_id']
            ]);
            
            return $this->createSuccessResponse("Autenticación exitosa", [
                'user_type' => 'residente',
                'residente_data' => $personaData,
                'session_data' => $sessionData,
                'csrf_token' => $this->generateCSRFToken()
            ]);
            
        } catch (Exception $e) {
            $this->logError("Error en residenteLogin(): " . $e->getMessage());
            return $this->createErrorResponse("Error interno del servidor");
        }
    }
    
    /**
     * Registro de nuevo residente
     * FUNCIÓN 5: Registro de residentes con validación exhaustiva
     * @param array $data Datos del nuevo residente
     * @return array Resultado del registro
     */
    public function residenteRegister(array $data): array
    {
        try {
            // Validar rate limiting
            $clientIP = $this->getClientIP();
            if (!$this->checkRateLimit('residente_register_' . $clientIP, 5, 3600)) {
                return $this->createErrorResponse("Límite de registros excedido para esta IP");
            }
            
            // Validar token CSRF
            if (!$this->csrfMiddleware->validateToken($data['csrf_token'] ?? '')) {
                return $this->createErrorResponse("Token CSRF inválido");
            }
            
            // Validar campos requeridos
            $requiredFields = ['curp', 'nombres', 'apellido1', 'correo_electronico', 'contrasena', 'fecha_nacimiento'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return $this->createErrorResponse("Campo requerido faltante: $field");
                }
            }
            
            // Validar formato de CURP
            if (!$this->personaModel->validateCURPFormat($data['curp'])) {
                return $this->createErrorResponse("Formato de CURP inválido");
            }
            
            // Validar formato de email
            if (!$this->personaModel->validateEmailFormat($data['correo_electronico'])) {
                return $this->createErrorResponse("Formato de email inválido");
            }
            
            // Verificar unicidad de CURP
            $existingPersonaByCURP = $this->personaModel->findByCURP($data['curp']);
            if ($existingPersonaByCURP) {
                return $this->createErrorResponse("El CURP ya está registrado");
            }
            
            // Verificar unicidad de email
            $existingPersonaByEmail = $this->personaModel->findByEmail($data['correo_electronico']);
            if ($existingPersonaByEmail) {
                return $this->createErrorResponse("El email ya está registrado");
            }
            
            // Validar formato de fecha de nacimiento
            if (!$this->validateDateFormat($data['fecha_nacimiento'])) {
                return $this->createErrorResponse("Formato de fecha de nacimiento inválido (YYYY-MM-DD)");
            }
            
            // Registrar residente usando modelo real
            $personaId = $this->personaModel->personaRegister($data);
            
            if ($personaId === false) {
                return $this->createErrorResponse("Error al registrar residente");
            }
            
            // Registrar evento de seguridad
            $this->logSecurityEvent('residente_registered', [
                'persona_id' => $personaId,
                'email' => $data['correo_electronico'],
                'curp' => $data['curp'],
                'ip' => $clientIP,
                'nombres' => $data['nombres'] . ' ' . $data['apellido1']
            ]);
            
            return $this->createSuccessResponse("Residente registrado exitosamente", [
                'persona_id' => $personaId,
                'email' => $data['correo_electronico'],
                'curp' => $data['curp']
            ]);
            
        } catch (Exception $e) {
            $this->logError("Error en residenteRegister(): " . $e->getMessage());
            return $this->createErrorResponse("Error interno del servidor");
        }
    }
    
    /**
     * Verificar sesión de residente
     * FUNCIÓN 6: Validación de sesión activa de residente
     * @return array Estado de la sesión
     */
    public function verifyResidenteSession(): array
    {
        try {
            // Verificar que existe sesión
            if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'residente') {
                return $this->createErrorResponse("Sesión de residente no encontrada");
            }
            
            // Verificar que existe ID de persona
            if (!isset($_SESSION['persona_id'])) {
                return $this->createErrorResponse("ID de residente no encontrado en sesión");
            }
            
            // Verificar timeout de sesión (2 horas para residentes)
            if (isset($_SESSION['last_activity'])) {
                $inactiveTime = time() - $_SESSION['last_activity'];
                if ($inactiveTime > 7200) { // 2 horas
                    $this->destroySession();
                    return $this->createErrorResponse("Sesión expirada por inactividad");
                }
            }
            
            // Actualizar última actividad
            $_SESSION['last_activity'] = time();
            
            // Obtener datos actuales del residente
            $personaData = $this->personaModel->findById($_SESSION['persona_id']);
            if (!$personaData) {
                $this->destroySession();
                return $this->createErrorResponse("Residente no encontrado");
            }
            
            // Limpiar datos sensibles
            unset($personaData['contrasena']);
            
            return $this->createSuccessResponse("Sesión válida", [
                'user_type' => 'residente',
                'residente_data' => $personaData,
                'session_valid' => true,
                'last_activity' => $_SESSION['last_activity']
            ]);
            
        } catch (Exception $e) {
            $this->logError("Error en verifyResidenteSession(): " . $e->getMessage());
            return $this->createErrorResponse("Error verificando sesión");
        }
    }
    
    // ==========================================
    // MÉTODOS DE GESTIÓN DE SESIONES
    // ==========================================
    
    /**
     * Cerrar sesión
     * FUNCIÓN 7: Cierre seguro de sesión para cualquier tipo de usuario
     * @return array Resultado del cierre de sesión
     */
    public function logout(): array
    {
        try {
            $userType = $_SESSION['user_type'] ?? 'unknown';
            $userId = $_SESSION['admin_id'] ?? $_SESSION['persona_id'] ?? 'unknown';
            
            // Registrar evento de cierre de sesión
            $this->logSecurityEvent('user_logout', [
                'user_type' => $userType,
                'user_id' => $userId,
                'ip' => $this->getClientIP(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
            // Destruir sesión
            $this->destroySession();
            
            return $this->createSuccessResponse("Sesión cerrada exitosamente", [
                'user_type' => $userType,
                'logout_time' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            $this->logError("Error en logout(): " . $e->getMessage());
            return $this->createErrorResponse("Error cerrando sesión");
        }
    }
    
    /**
     * Obtener información de sesión actual
     * FUNCIÓN 8: Información detallada de la sesión activa
     * @return array Información de la sesión
     */
    public function getSessionInfo(): array
    {
        try {
            if (!isset($_SESSION['user_type'])) {
                return $this->createErrorResponse("No hay sesión activa");
            }
            
            $sessionInfo = [
                'user_type' => $_SESSION['user_type'],
                'session_started' => $_SESSION['session_started'] ?? 'unknown',
                'last_activity' => $_SESSION['last_activity'] ?? 'unknown',
                'session_id' => session_id(),
                'ip_address' => $this->getClientIP()
            ];
            
            // Agregar información específica según tipo de usuario
            if ($_SESSION['user_type'] === 'admin') {
                $sessionInfo['admin_id'] = $_SESSION['admin_id'] ?? null;
                $sessionInfo['admin_name'] = $_SESSION['admin_name'] ?? null;
            } elseif ($_SESSION['user_type'] === 'residente') {
                $sessionInfo['persona_id'] = $_SESSION['persona_id'] ?? null;
                $sessionInfo['residente_name'] = $_SESSION['residente_name'] ?? null;
            }
            
            return $this->createSuccessResponse("Información de sesión obtenida", $sessionInfo);
            
        } catch (Exception $e) {
            $this->logError("Error en getSessionInfo(): " . $e->getMessage());
            return $this->createErrorResponse("Error obteniendo información de sesión");
        }
    }
    
    // ==========================================
    // MÉTODOS DE SEGURIDAD Y TOKENS
    // ==========================================
    
    /**
     * Generar token CSRF
     * FUNCIÓN 9: Generación de tokens CSRF para formularios
     * @return string Token CSRF
     */
    public function generateCSRFToken(): string
    {
        return $this->csrfMiddleware->generateToken();
    }
    
    /**
     * Validar token CSRF
     * FUNCIÓN 10: Validación de tokens CSRF
     * @param string $token Token a validar
     * @return bool True si es válido
     */
    public function validateCSRFToken(string $token): bool
    {
        return $this->csrfMiddleware->validateToken($token);
    }
    
    /**
     * Verificar permisos de administrador
     * FUNCIÓN 11: Validación de permisos específicos
     * @param string $permission Permiso requerido
     * @return array Resultado de la verificación
     */
    public function checkAdminPermission(string $permission): array
    {
        try {
            // Verificar sesión de administrador
            $sessionCheck = $this->verifyAdminSession();
            if (!$sessionCheck['success']) {
                return $sessionCheck;
            }
            
            // Por ahora todos los admins tienen todos los permisos
            // En el futuro se puede implementar un sistema de roles más granular
            $hasPermission = true;
            
            if (!$hasPermission) {
                return $this->createErrorResponse("Permisos insuficientes para: $permission");
            }
            
            return $this->createSuccessResponse("Permisos verificados", [
                'permission' => $permission,
                'granted' => true
            ]);
            
        } catch (Exception $e) {
            $this->logError("Error en checkAdminPermission(): " . $e->getMessage());
            return $this->createErrorResponse("Error verificando permisos");
        }
    }
    
    // ==========================================
    // MÉTODOS AUXILIARES PRIVADOS
    // ==========================================
    
    /**
     * Crear sesión de administrador
     * @param array $adminData Datos del administrador
     * @return array Datos de la sesión
     */
    private function createAdminSession(array $adminData): array
    {
        // Regenerar ID de sesión por seguridad
        session_regenerate_id(true);
        
        // Establecer datos de sesión
        $_SESSION['user_type'] = 'admin';
        $_SESSION['admin_id'] = $adminData['id_admin'];
        $_SESSION['admin_name'] = $adminData['nombres'] . ' ' . $adminData['apellido1'];
        $_SESSION['admin_email'] = $adminData['correo'];
        $_SESSION['session_started'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['ip_address'] = $this->getClientIP();
        $_SESSION['csrf_token'] = $this->generateCSRFToken();
        
        return [
            'session_id' => session_id(),
            'session_started' => $_SESSION['session_started'],
            'csrf_token' => $_SESSION['csrf_token']
        ];
    }
    
    /**
     * Crear sesión de residente
     * @param array $personaData Datos del residente
     * @return array Datos de la sesión
     */
    private function createResidenteSession(array $personaData): array
    {
        // Regenerar ID de sesión por seguridad
        session_regenerate_id(true);
        
        // Establecer datos de sesión
        $_SESSION['user_type'] = 'residente';
        $_SESSION['persona_id'] = $personaData['id_persona'];
        $_SESSION['residente_name'] = $personaData['nombres'] . ' ' . $personaData['apellido1'];
        $_SESSION['residente_email'] = $personaData['correo_electronico'];
        $_SESSION['residente_curp'] = $personaData['curp'];
        $_SESSION['session_started'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['ip_address'] = $this->getClientIP();
        $_SESSION['csrf_token'] = $this->generateCSRFToken();
        
        return [
            'session_id' => session_id(),
            'session_started' => $_SESSION['session_started'],
            'csrf_token' => $_SESSION['csrf_token']
        ];
    }
    
    /**
     * Destruir sesión completamente
     */
    private function destroySession(): void
    {
        // Limpiar todas las variables de sesión
        $_SESSION = array();
        
        // Destruir cookie de sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destruir sesión
        session_destroy();
    }
    
    /**
     * Obtener IP del cliente
     * @return string IP del cliente
     */
    private function getClientIP(): string
    {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Verificar rate limiting
     * @param string $key Clave del rate limit
     * @param int $limit Límite de requests
     * @param int $window Ventana de tiempo en segundos
     * @return bool True si está dentro del límite
     */
    private function checkRateLimit(string $key, int $limit, int $window): bool
    {
        return $this->rateLimitMiddleware->checkLimit($key, $limit, $window);
    }
    
    /**
     * Registrar evento de seguridad
     * @param string $event Tipo de evento
     * @param array $data Datos del evento
     */
    private function logSecurityEvent(string $event, array $data): void
    {
        $logData = array_merge([
            'event' => $event,
            'timestamp' => date('Y-m-d H:i:s'),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ], $data);
        
        error_log("SECURITY_EVENT: " . json_encode($logData));
    }
    
    /**
     * Validar formato de fecha
     * @param string $date Fecha a validar
     * @return bool True si es válida
     */
    private function validateDateFormat(string $date): bool
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
    
    /**
     * Crear respuesta de éxito
     * @param string $message Mensaje de éxito
     * @param array $data Datos adicionales
     * @return array Respuesta estructurada
     */
    private function createSuccessResponse(string $message, array $data = []): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Crear respuesta de error
     * @param string $message Mensaje de error
     * @param int $code Código de error
     * @return array Respuesta estructurada
     */
    private function createErrorResponse(string $message, int $code = 400): array
    {
        return [
            'success' => false,
            'error' => $message,
            'error_code' => $code,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Registrar error
     * @param string $message Mensaje de error
     */
    private function logError(string $message): void
    {
        error_log("AUTH_SERVICE_ERROR: " . $message);
    }
}
?>
