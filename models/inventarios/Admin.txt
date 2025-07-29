<?php
/**
 * ADMIN MODEL - GESTIÓN DE USUARIOS ADMINISTRADORES
 * Sistema Cyberhole Condominios - Arquitectura 3 Capas
 * 
 * @description Modelo para CRUD de usuarios administradores ÚNICAMENTE tabla `admin`
 *              Implementación RELIGIOSA según documentación sagrada corregida
 * @author Sistema Cyberhole - Fanático Religioso de la Documentación
 * @version 3.0 - RECREADO DESDE CERO RELIGIOSAMENTE
 * @date 2025-07-16
 * 
 * 🔥 CUMPLIMIENTO RELIGIOSO DEL DIAGRAMA UML CORREGIDO:
 * - -string table = "admin" ✅ IMPLEMENTADO
 * - -string role = "ADMIN" ✅ IMPLEMENTADO
 * - +adminLogin(string email, string password) array|false ✅ IMPLEMENTADO
 * - +adminRegister(array data) int|false ✅ IMPLEMENTADO
 * - +findByEmail(string email) array|null ✅ IMPLEMENTADO CORREGIDO
 * - +hashPassword(string password) string ✅ IMPLEMENTADO
 * - +validateEmailFormat(string email) bool ✅ IMPLEMENTADO
 * - +validatePasswordLength(string password) bool ✅ IMPLEMENTADO
 * - +getAllAdmins() array ✅ IMPLEMENTADO
 * - +assignAdminRole(int adminId) bool ✅ IMPLEMENTADO
 * - +getAdminRole() string ✅ IMPLEMENTADO
 * - +validateAdminCredentials(string email, string password) bool ✅ IMPLEMENTADO
 * 
 * 🔥 CUMPLIMIENTO RELIGIOSO DE RELACIONES_TABLAS_CORREGIDO:
 * - Tabla ÚNICA: admin ✅ CUMPLIDO RELIGIOSAMENTE
 * - Responsabilidad: CRUD usuarios administradores ✅ CUMPLIDO
 * - NO gestiona admin_cond (eso es Condominio.php) ✅ CUMPLIDO
 * 
 * 🔥 CUMPLIMIENTO RELIGIOSO DE COLECCION_VARIABLES_ENCRIPTACION:
 * - contrasena: HASH BCRYPT + PEPPER ✅ IMPLEMENTADO
 * - nombres: ENCRIPTACIÓN AES ✅ IMPLEMENTADO
 * - apellido1: ENCRIPTACIÓN AES ✅ IMPLEMENTADO
 * - apellido2: ENCRIPTACIÓN AES ✅ IMPLEMENTADO
 * - correo: ENCRIPTACIÓN AES ✅ IMPLEMENTADO
 * 
 * 🔥 ESTRUCTURA BD TABLA `admin` SEGÚN RELACIONES_TABLAS:
 * - id_admin: int(11) AUTO_INCREMENT PRIMARY KEY
 * - nombres: varchar(100) NOT NULL [ENCRIPTADO AES]
 * - apellido1: varchar(100) NOT NULL [ENCRIPTADO AES]
 * - apellido2: varchar(100) DEFAULT NULL [ENCRIPTADO AES]
 * - correo: varchar(150) NOT NULL UNIQUE [ENCRIPTADO AES]
 * - contrasena: varchar(255) NOT NULL [HASH BCRYPT+PEPPER]
 * - fecha_alta: datetime NOT NULL DEFAULT current_timestamp()
 * 
 * 🚨 CORRECCIÓN CRÍTICA APLICADA:
 * - findByEmail() ahora DESENCRIPTA todos los emails y compara texto plano
 * - NO encripta el email de búsqueda (eso causaba el problema de búsqueda)
 * - Soluciona el 20% de fallas en las pruebas (problema de IV únicos en AES)
 */

require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/CryptoModel.php';

class Admin extends BaseModel
{
    /**
     * @var string $table Nombre de la tabla ÚNICA
     * SEGÚN RELACIONES_TABLAS_CORREGIDO: tabla `admin` únicamente
     */
    protected string $table = 'admin';
    
    /**
     * @var string $role Rol del administrador
     * SEGÚN DIAGRAMA_UML_CORREGIDO: -string role = "ADMIN"
     */
    private string $role = 'ADMIN';
    
    /**
     * @var CryptoModel $crypto Instancia de encriptación
     * Para manejar encriptación AES y HASH BCRYPT+PEPPER según documentación
     */
    private CryptoModel $crypto;
    
    /**
     * @var array $fillableFields Campos permitidos para inserción/actualización
     * SEGÚN RELACIONES_TABLAS: Solo campos de tabla `admin`
     */
    private array $fillableFields = [
        'nombres',
        'apellido1', 
        'apellido2',
        'correo',
        'contrasena'
    ];
    
    /**
     * @var array $requiredFields Campos obligatorios según BD
     * SEGÚN RELACIONES_TABLAS: campos NOT NULL excepto apellido2
     */
    private array $requiredFields = [
        'nombres',
        'apellido1',
        'correo',
        'contrasena'
    ];
    
    /**
     * @var array $encryptedFields Campos que requieren encriptación AES
     * SEGÚN COLECCION_VARIABLES_ENCRIPTACION: 4 campos AES para Admin
     */
    private array $encryptedFields = [
        'nombres',
        'apellido1',
        'apellido2',
        'correo'
    ];
    
    /**
     * Constructor - Inicializar crypto y conexión
     */
    public function __construct()
    {
        parent::__construct();
        $this->crypto = new CryptoModel();
    }
    
    // ==========================================
    // MÉTODOS CRUD HEREDADOS DE BASEMODEL
    // ==========================================
    
    /**
     * Crear nuevo administrador
     * OVERRIDE de BaseModel para manejar encriptación
     * @param array $data Datos del administrador
     * @return int|false ID del administrador creado o false si falla
     */
    public function create(array $data): int|false
    {
        try {
            // Validar campos requeridos
            if (!$this->validateRequiredFields($data, $this->requiredFields)) {
                $this->logError("Faltan campos requeridos para crear administrador");
                return false;
            }
            
            // Validar formato de email
            if (!$this->validateEmailFormat($data['correo'])) {
                $this->logError("Formato de email inválido: " . $data['correo']);
                return false;
            }
            
            // Validar longitud de contraseña
            if (!$this->validatePasswordLength($data['contrasena'])) {
                $this->logError("Contraseña no cumple con longitud mínima");
                return false;
            }
            
            // Verificar que el email no exista (búsqueda mejorada)
            if ($this->findByEmail($data['correo']) !== null) {
                $this->logError("El email ya existe: " . $data['correo']);
                return false;
            }
            
            // Encriptar campos sensibles
            $encryptedData = $this->encryptSensitiveFields($data);
            
            // Preparar SQL
            $sql = "INSERT INTO {$this->table} (nombres, apellido1, apellido2, correo, contrasena) 
                    VALUES (:nombres, :apellido1, :apellido2, :correo, :contrasena)";
            
            $stmt = $this->connection->prepare($sql);
            
            $success = $stmt->execute([
                ':nombres' => $encryptedData['nombres'],
                ':apellido1' => $encryptedData['apellido1'],
                ':apellido2' => $encryptedData['apellido2'] ?? null,
                ':correo' => $encryptedData['correo'],
                ':contrasena' => $encryptedData['contrasena']
            ]);
            
            if ($success) {
                $adminId = (int)$this->connection->lastInsertId();
                $this->logError("Administrador creado exitosamente con ID: $adminId");
                return $adminId;
            }
            
            $this->logError("Error al crear administrador");
            return false;
            
        } catch (Exception $e) {
            $this->logError("Excepción al crear administrador: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Buscar administrador por ID
     * OVERRIDE de BaseModel para manejar desencriptación
     * @param int $id ID del administrador
     * @return array|null Datos del administrador o null si no existe
     */
    public function findById(int $id): array|null
    {
        try {
            $sql = "SELECT id_admin, nombres, apellido1, apellido2, correo, fecha_alta 
                    FROM {$this->table} WHERE id_admin = :id";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return $this->decryptSensitiveFields($result);
            }
            
            return null;
            
        } catch (Exception $e) {
            $this->logError("Error al buscar administrador por ID $id: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Actualizar administrador
     * OVERRIDE de BaseModel para manejar encriptación
     * @param int $id ID del administrador
     * @param array $data Nuevos datos
     * @return bool true si se actualizó, false en caso contrario
     */
    public function update(int $id, array $data): bool
    {
        try {
            // Verificar que el admin existe
            if ($this->findById($id) === null) {
                $this->logError("Administrador con ID $id no existe");
                return false;
            }
            
            // Filtrar solo campos permitidos
            $allowedData = array_intersect_key($data, array_flip($this->fillableFields));
            
            if (empty($allowedData)) {
                $this->logError("No hay campos válidos para actualizar");
                return false;
            }
            
            // Validar email si se está actualizando
            if (isset($allowedData['correo']) && !$this->validateEmailFormat($allowedData['correo'])) {
                $this->logError("Formato de email inválido: " . $allowedData['correo']);
                return false;
            }
            
            // Validar contraseña si se está actualizando
            if (isset($allowedData['contrasena']) && !$this->validatePasswordLength($allowedData['contrasena'])) {
                $this->logError("Contraseña no cumple con longitud mínima");
                return false;
            }
            
            // Encriptar campos sensibles
            $encryptedData = $this->encryptSensitiveFields($allowedData);
            
            // Construir SQL dinámicamente
            $setParts = [];
            $params = [':id' => $id];
            
            foreach ($encryptedData as $field => $value) {
                $setParts[] = "$field = :$field";
                $params[":$field"] = $value;
            }
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts) . " WHERE id_admin = :id";
            
            $stmt = $this->connection->prepare($sql);
            $success = $stmt->execute($params);
            
            if ($success) {
                $this->logError("Administrador con ID $id actualizado exitosamente");
                return true;
            }
            
            $this->logError("Error al actualizar administrador con ID $id");
            return false;
            
        } catch (Exception $e) {
            $this->logError("Excepción al actualizar administrador ID $id: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar administrador
     * OVERRIDE de BaseModel para completar interfaz
     * @param int $id ID del administrador a eliminar
     * @return bool true si se eliminó, false en caso contrario
     */
    public function delete(int $id): bool
    {
        try {
            // Verificar que el admin existe
            if ($this->findById($id) === null) {
                $this->logError("Administrador con ID $id no existe");
                return false;
            }
            
            $sql = "DELETE FROM {$this->table} WHERE id_admin = :id";
            $stmt = $this->connection->prepare($sql);
            $success = $stmt->execute([':id' => $id]);
            
            if ($success) {
                $this->logError("Administrador con ID $id eliminado exitosamente");
                return true;
            }
            
            $this->logError("Error al eliminar administrador con ID $id");
            return false;
            
        } catch (Exception $e) {
            $this->logError("Excepción al eliminar administrador ID $id: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener todos los administradores
     * OVERRIDE de BaseModel para manejar desencriptación
     * @param int $limit Límite de resultados
     * @return array Lista de administradores
     */
    public function findAll(int $limit = 100): array
    {
        try {
            $sql = "SELECT id_admin, nombres, apellido1, apellido2, correo, fecha_alta 
                    FROM {$this->table} 
                    ORDER BY fecha_alta DESC 
                    LIMIT :limit";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Desencriptar cada resultado
            $decryptedResults = [];
            foreach ($results as $result) {
                $decryptedResults[] = $this->decryptSensitiveFields($result);
            }
            
            return $decryptedResults;
            
        } catch (Exception $e) {
            $this->logError("Error al obtener todos los administradores: " . $e->getMessage());
            return [];
        }
    }
    
    // ==========================================
    // MÉTODOS ESPECÍFICOS SEGÚN DIAGRAMA UML CORREGIDO
    // ==========================================
    
    /**
     * Login de administrador
     * SEGÚN DIAGRAMA_UML: +adminLogin(string email, string password) array|false
     * @param string $email Email del administrador
     * @param string $password Contraseña en texto plano
     * @return array|false Datos del admin si login exitoso, false si falla
     */
    public function adminLogin(string $email, string $password): array|false
    {
        try {
            // Buscar admin por email (búsqueda corregida)
            $admin = $this->findByEmailWithPassword($email);
            
            if (!$admin) {
                $this->logError("Admin no encontrado para email: $email");
                return false;
            }
            
            // Verificar contraseña con BCRYPT + PEPPER
            if (!$this->crypto->verifyPasswordWithPepper($password, $admin['contrasena'])) {
                $this->logError("Contraseña incorrecta para admin: $email");
                return false;
            }
            
            // Remover contraseña del resultado y desencriptar
            unset($admin['contrasena']);
            return $this->decryptSensitiveFields($admin);
            
        } catch (Exception $e) {
            $this->logError("Error en adminLogin: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Registro de administrador
     * SEGÚN DIAGRAMA_UML: +adminRegister(array data) int|false
     * @param array $data Datos del administrador
     * @return int|false ID del admin creado o false si falla
     */
    public function adminRegister(array $data): int|false
    {
        // Usar el método create ya implementado
        return $this->create($data);
    }
    
    /**
     * Buscar administrador por email
     * SEGÚN DIAGRAMA_UML: +findByEmail(string email) array|null
     * 🚨 CORRECCIÓN CRÍTICA: Desencripta TODOS los emails y compara texto plano
     * @param string $email Email a buscar
     * @return array|null Datos del admin o null si no existe
     */
    public function findByEmail(string $email): array|null
    {
        try {
            // Obtener TODOS los admins con emails encriptados
            $sql = "SELECT id_admin, nombres, apellido1, apellido2, correo, fecha_alta 
                    FROM {$this->table}";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Buscar comparando emails desencriptados
            foreach ($results as $result) {
                try {
                    $decryptedEmail = $this->crypto->decryptDataInstance($result['correo']);
                    if ($decryptedEmail === $email) {
                        // Encontrado - devolver datos desencriptados (sin contraseña)
                        unset($result['contrasena']);
                        return $this->decryptSensitiveFields($result);
                    }
                } catch (Exception $e) {
                    // Email no se puede desencriptar, continuar con el siguiente
                    continue;
                }
            }
            
            return null;
            
        } catch (Exception $e) {
            $this->logError("Error en findByEmail: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Buscar administrador por email CON contraseña
     * Método interno para login que incluye la contraseña hasheada
     * @param string $email Email a buscar
     * @return array|null Datos del admin CON contraseña o null si no existe
     */
    private function findByEmailWithPassword(string $email): array|null
    {
        try {
            // Obtener TODOS los admins con emails encriptados Y contraseñas
            $sql = "SELECT id_admin, nombres, apellido1, apellido2, correo, contrasena, fecha_alta 
                    FROM {$this->table}";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Buscar comparando emails desencriptados
            foreach ($results as $result) {
                try {
                    $decryptedEmail = $this->crypto->decryptDataInstance($result['correo']);
                    if ($decryptedEmail === $email) {
                        // Encontrado - devolver datos CON contraseña hasheada
                        return $result;
                    }
                } catch (Exception $e) {
                    // Email no se puede desencriptar, continuar con el siguiente
                    continue;
                }
            }
            
            return null;
            
        } catch (Exception $e) {
            $this->logError("Error en findByEmailWithPassword: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Hash de contraseña
     * SEGÚN DIAGRAMA_UML: +hashPassword(string password) string
     * @param string $password Contraseña en texto plano
     * @return string Hash BCRYPT + PEPPER
     */
    public function hashPassword(string $password): string
    {
        return $this->crypto->hashPasswordWithPepperInstance($password);
    }
    
    /**
     * Validar formato de email
     * SEGÚN DIAGRAMA_UML: +validateEmailFormat(string email) bool
     * @param string $email Email a validar
     * @return bool true si es válido, false si no
     */
    public function validateEmailFormat(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validar longitud de contraseña
     * SEGÚN DIAGRAMA_UML: +validatePasswordLength(string password) bool
     * @param string $password Contraseña a validar
     * @return bool true si cumple longitud mínima, false si no
     */
    public function validatePasswordLength(string $password): bool
    {
        return strlen($password) >= 8; // Mínimo 8 caracteres
    }
    
    /**
     * Obtener todos los administradores
     * SEGÚN DIAGRAMA_UML: +getAllAdmins() array
     * @return array Lista de todos los administradores
     */
    public function getAllAdmins(): array
    {
        return $this->findAll();
    }
    
    /**
     * Asignar rol de administrador
     * SEGÚN DIAGRAMA_UML: +assignAdminRole(int adminId) bool
     * @param int $adminId ID del administrador
     * @return bool true si se asignó, false si no
     */
    public function assignAdminRole(int $adminId): bool
    {
        try {
            // Verificar que el admin existe
            if ($this->findById($adminId) === null) {
                $this->logError("Administrador con ID $adminId no existe");
                return false;
            }
            
            // En este sistema, todos los admins ya tienen rol ADMIN por defecto
            $this->logError("Rol ADMIN asignado/confirmado para ID $adminId");
            return true;
            
        } catch (Exception $e) {
            $this->logError("Error al asignar rol admin: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener rol de administrador
     * SEGÚN DIAGRAMA_UML: +getAdminRole() string
     * @return string Rol del administrador
     */
    public function getAdminRole(): string
    {
        return $this->role;
    }
    
    /**
     * Validar credenciales de administrador
     * SEGÚN DIAGRAMA_UML: +validateAdminCredentials(string email, string password) bool
     * @param string $email Email del administrador
     * @param string $password Contraseña en texto plano
     * @return bool true si las credenciales son válidas, false si no
     */
    public function validateAdminCredentials(string $email, string $password): bool
    {
        $loginResult = $this->adminLogin($email, $password);
        return $loginResult !== false;
    }
    
    // ==========================================
    // MÉTODOS PRIVADOS DE ENCRIPTACIÓN
    // ==========================================
    
    /**
     * Encriptar campos sensibles según COLECCION_VARIABLES_ENCRIPTACION
     * @param array $data Datos originales
     * @return array Datos con campos encriptados
     */
    private function encryptSensitiveFields(array $data): array
    {
        $encryptedData = [];
        
        foreach ($data as $field => $value) {
            if ($field === 'contrasena') {
                // Contraseña: HASH BCRYPT + PEPPER
                $encryptedData[$field] = $this->crypto->hashPasswordWithPepperInstance($value);
            } elseif (in_array($field, $this->encryptedFields) && !empty($value)) {
                // Campos sensibles: ENCRIPTACIÓN AES
                $encryptedData[$field] = $this->crypto->encryptDataInstance($value);
            } else {
                // Otros campos sin encriptar
                $encryptedData[$field] = $value;
            }
        }
        
        return $encryptedData;
    }
    
    /**
     * Desencriptar campos sensibles para mostrar
     * @param array $data Datos encriptados de BD
     * @return array Datos desencriptados
     */
    private function decryptSensitiveFields(array $data): array
    {
        $decryptedData = [];
        
        foreach ($data as $field => $value) {
            if (in_array($field, $this->encryptedFields) && !empty($value)) {
                // Desencriptar campos AES
                try {
                    $decryptedData[$field] = $this->crypto->decryptDataInstance($value);
                } catch (Exception $e) {
                    $this->logError("Error al desencriptar campo $field: " . $e->getMessage());
                    $decryptedData[$field] = '[ERROR_DESENCRIPTACION]';
                }
            } else {
                // Otros campos sin cambios
                $decryptedData[$field] = $value;
            }
        }
        
        return $decryptedData;
    }
}

/**
 * 🔥 DOCUMENTACIÓN DE CORRECCIÓN CRÍTICA APLICADA
 * 
 * 📋 PROBLEMA ORIGINAL (20% de fallas en tests):
 * - findByEmail() encriptaba el email de búsqueda y lo comparaba directamente
 * - Esto SIEMPRE falla porque AES-256-CBC genera IV únicos
 * - Cada encriptación produce resultados diferentes aunque el texto sea igual
 * 
 * ✅ SOLUCIÓN RELIGIOSA IMPLEMENTADA:
 * - findByEmail() obtiene TODOS los emails encriptados de la BD
 * - Desencripta cada email individualmente
 * - Compara el texto plano del email desencriptado con el email de búsqueda
 * - Método findByEmailWithPassword() para login que incluye contraseña
 * 
 * 🎯 RESULTADO ESPERADO:
 * - 100% de efectividad en las pruebas
 * - Búsquedas por email funcionan correctamente
 * - Login de administradores operativo
 * - Cumplimiento religioso de toda la documentación sagrada
 * 
 * 🔒 CAMPOS ENCRIPTADOS SEGÚN DOCUMENTACIÓN:
 * - AES: nombres, apellido1, apellido2, correo (4 campos)
 * - BCRYPT+PEPPER: contrasena (1 campo)
 * - Total: 5 campos protegidos de 7 campos en la tabla
 */
