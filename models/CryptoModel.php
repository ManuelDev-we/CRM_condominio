<?php
/**
 * CRYPTO MODEL - SISTEMA DE ENCRIPTACIÓN Y HASH
 * Sistema Cyberhole Condominios - Arquitectura 3 Capas
 * 
 * @description Modelo maestro de encriptación para datos sensibles
 *              Implementa AES-256-CBC + BCRYPT + PEPPER según especificaciones
 * @author Sistema Cyberhole - Fanático Religioso de la Documentación
 * @version 2.0 - RECREADO DESDE CERO
 * @date 2025-07-16
 * 
 * 🔥 CUMPLIMIENTO RELIGIOSO DE COLECCION_VARIABLES_ENCRIPTACION:
 * - AES-256-CBC para datos sensibles ✅ IMPLEMENTADO
 * - BCRYPT + PEPPER para contraseñas ✅ IMPLEMENTADO
 * - Clave de 32 caracteres ✅ IMPLEMENTADO
 * - Método AES-256-CBC ✅ IMPLEMENTADO
 * - 14 rounds de BCRYPT ✅ IMPLEMENTADO
 * 
 * 🔥 CUMPLIMIENTO RELIGIOSO DE CONFIGURACIÓN ENV:
 * - ENCRYPTION_ALGORITHM=AES-256-CBC ✅ CUMPLIDO
 * - AES_KEY=CyberholeProd2025AESKey32CharKe! ✅ CUMPLIDO
 * - AES_METHOD=AES-256-CBC ✅ CUMPLIDO
 * - BCRYPT_ROUNDS=14 ✅ CUMPLIDO
 * - PEPPER_SECRET=CyberholeProdCondominios2025PepperSecretKey!@#$% ✅ CUMPLIDO
 * 
 * 🔥 PATRONES SEGUIDOS SEGÚN MODELOS EXISTENTES:
 * - EXTIENDE BASEMODEL para consistencia total ✅ IMPLEMENTADO
 * - MÉTODOS DE INSTANCIA siguiendo patrón estándar ✅ IMPLEMENTADO  
 * - CONFIGURACIÓN desde env() helper ✅ IMPLEMENTADO
 * - VALIDACIONES extensas ✅ IMPLEMENTADO
 * - MANEJO DE ERRORES heredado de BaseModel ✅ IMPLEMENTADO
 * - COMPATIBILIDAD RETROACTIVA con métodos estáticos ✅ MANTENIDA
 */

require_once __DIR__ . '/BaseModel.php';

class CryptoModel extends BaseModel
{
    /**
     * @var string $table Tabla asociada al modelo
     * SEGÚN PATRÓN DE BASEMODEL: CryptoModel es un modelo especial sin tabla propia
     * Se define como vacío pero se mantiene la propiedad para consistencia
     */
    protected string $table = '';
    
    /**
     * @var string $algorithm Algoritmo de encriptación
     * SEGÚN COLECCION_VARIABLES_ENCRIPTACION: AES-256-CBC
     */
    private const ENCRYPTION_ALGORITHM = 'aes-256-cbc';
    
    /**
     * @var string $aes_key Clave AES de 32 caracteres
     * SEGÚN CONFIGURACIÓN ENV: AES_KEY
     */
    private string $aes_key;
    
    /**
     * @var string $pepper Pepper para contraseñas
     * SEGÚN CONFIGURACIÓN ENV: PEPPER_SECRET
     */
    private string $pepper;
    
    /**
     * @var int $bcrypt_rounds Rounds de BCRYPT
     * SEGÚN COLECCION_VARIABLES_ENCRIPTACION: 14
     */
    private int $bcrypt_rounds;
    
    /**
     * Constructor - Inicializar configuración siguiendo patrón BaseModel
     * PATRÓN ESTÁNDAR: Llamar parent::__construct() como otros modelos
     */
    public function __construct()
    {
        // PATRÓN ESTÁNDAR: Llamar constructor padre primero
        parent::__construct();
        
        // Cargar configuración específica de encriptación
        $this->loadEncryptionConfig();
        
        // Validar configuración específica
        $this->validateEncryptionConfig();
    }
    
    /**
     * Cargar configuración de encriptación
     * PATRÓN SEGUIDO: Método separado para configuración específica
     */
    private function loadEncryptionConfig(): void
    {
        $this->aes_key = env('AES_KEY', '');
        $this->pepper = env('PEPPER_SECRET', '');
        $this->bcrypt_rounds = (int)env('BCRYPT_ROUNDS', 14);
    }
    
    /**
     * Validar configuración de encriptación
     * PATRÓN SEGUIDO: Método separado para validaciones específicas
     */
    private function validateEncryptionConfig(): void
    {
        if (empty($this->aes_key)) {
            throw new RuntimeException("AES_KEY no está configurada en variables de entorno");
        }
        
        if (strlen($this->aes_key) !== 32) {
            throw new InvalidArgumentException("La clave AES debe tener exactamente 32 caracteres. Longitud actual: " . strlen($this->aes_key));
        }
        
        if (empty($this->pepper)) {
            throw new RuntimeException("PEPPER_SECRET no está configurada en variables de entorno");
        }
        
        if (strlen($this->pepper) < 20) {
            throw new InvalidArgumentException("El pepper debe tener al menos 20 caracteres. Longitud actual: " . strlen($this->pepper));
        }
        
        if ($this->bcrypt_rounds < 10 || $this->bcrypt_rounds > 20) {
            throw new InvalidArgumentException("BCRYPT_ROUNDS debe estar entre 10 y 20. Valor actual: " . $this->bcrypt_rounds);
        }
        
        // Validar que el algoritmo esté disponible
        if (!in_array(self::ENCRYPTION_ALGORITHM, openssl_get_cipher_methods())) {
            throw new RuntimeException("El algoritmo de encriptación AES-256-CBC no está disponible");
        }
    }
    
    // ==========================================
    // MÉTODOS DE ENCRIPTACIÓN AES-256-CBC
    // ==========================================
    
    /**
     * Encriptar datos sensibles con AES-256-CBC
     * SEGÚN COLECCION_VARIABLES_ENCRIPTACION: Para campos sensibles
     * PATRÓN ESTÁTICO: Para uso desde Casa.php
     * 
     * @param string $data Datos a encriptar
     * @return string Datos encriptados en base64
     * @throws Exception Si falla la encriptación
     */
    public static function encryptData(string $data): string
    {
        $instance = new self();
        return $instance->encryptDataInstance($data);
    }
    
    /**
     * Desencriptar datos con AES-256-CBC
     * SEGÚN COLECCION_VARIABLES_ENCRIPTACION: Para leer campos sensibles
     * PATRÓN ESTÁTICO: Para uso desde Casa.php
     * 
     * @param string $encryptedData Datos encriptados en base64
     * @return string Datos desencriptados
     * @throws Exception Si falla la desencriptación
     */
    public static function decryptData(string $encryptedData): string
    {
        $instance = new self();
        return $instance->decryptDataInstance($encryptedData);
    }
    
    /**
     * Encriptar datos (método de instancia)
     * PATRÓN DE INSTANCIA: Para uso desde Persona.php
     */
    public function encryptDataInstance(string $data): string
    {
        if (empty($data)) {
            return '';
        }
        
        try {
            // Generar IV aleatorio
            $ivLength = openssl_cipher_iv_length(self::ENCRYPTION_ALGORITHM);
            $iv = openssl_random_pseudo_bytes($ivLength);
            
            // Encriptar datos
            $encrypted = openssl_encrypt(
                $data,
                self::ENCRYPTION_ALGORITHM,
                $this->aes_key,
                OPENSSL_RAW_DATA,
                $iv
            );
            
            if ($encrypted === false) {
                throw new RuntimeException("Error en encriptación AES");
            }
            
            // Combinar IV + datos encriptados y codificar en base64
            $result = base64_encode($iv . $encrypted);
            
            return $result;
            
        } catch (Exception $e) {
            $this->logError("Error encriptando datos: " . $e->getMessage());
            throw new RuntimeException("Falla en encriptación de datos", 0, $e);
        }
    }
    
    /**
     * Desencriptar datos (método de instancia)
     * PATRÓN DE INSTANCIA: Para uso desde Persona.php
     */
    public function decryptDataInstance(string $encryptedData): string
    {
        if (empty($encryptedData)) {
            return '';
        }
        
        try {
            // Decodificar de base64
            $data = base64_decode($encryptedData);
            if ($data === false) {
                throw new InvalidArgumentException("Datos encriptados inválidos");
            }
            
            // Extraer IV y datos encriptados
            $ivLength = openssl_cipher_iv_length(self::ENCRYPTION_ALGORITHM);
            $iv = substr($data, 0, $ivLength);
            $encrypted = substr($data, $ivLength);
            
            // Desencriptar
            $decrypted = openssl_decrypt(
                $encrypted,
                self::ENCRYPTION_ALGORITHM,
                $this->aes_key,
                OPENSSL_RAW_DATA,
                $iv
            );
            
            if ($decrypted === false) {
                throw new RuntimeException("Error en desencriptación AES");
            }
            
            return $decrypted;
            
        } catch (Exception $e) {
            $this->logError("Error desencriptando datos: " . $e->getMessage());
            throw new RuntimeException("Falla en desencriptación de datos", 0, $e);
        }
    }
    
    // ==========================================
    // MÉTODOS DE HASH BCRYPT + PEPPER
    // ==========================================
    
    /**
     * Hash de contraseña con BCRYPT + PEPPER
     * SEGÚN COLECCION_VARIABLES_ENCRIPTACION: Para admin.contrasena y personas.contrasena
     * PATRÓN ESTÁTICO: Para uso desde Casa.php
     */
    public static function hashPasswordWithPepper(string $password): string
    {
        $instance = new self();
        return $instance->hashPasswordWithPepperInstance($password);
    }
    
    /**
     * Verificar contraseña con BCRYPT + PEPPER
     * SEGÚN COLECCION_VARIABLES_ENCRIPTACION: Para validar login
     * PATRÓN ESTÁTICO: Para uso desde Casa.php
     */
    public static function verifyPasswordWithPepper(string $password, string $hash): bool
    {
        $instance = new self();
        return $instance->verifyPasswordWithPepperInstance($password, $hash);
    }
    
    /**
     * Hash de contraseña (método de instancia)
     * PATRÓN DE INSTANCIA: Para uso desde Persona.php
     */
    public function hashPasswordWithPepperInstance(string $password): string
    {
        if (empty($password)) {
            throw new InvalidArgumentException("La contraseña no puede estar vacía");
        }
        
        try {
            // Combinar contraseña con pepper
            $passwordWithPepper = $password . $this->pepper;
            
            // Crear hash con BCRYPT
            $hash = password_hash(
                $passwordWithPepper,
                PASSWORD_BCRYPT,
                ['cost' => $this->bcrypt_rounds]
            );
            
            if ($hash === false) {
                throw new RuntimeException("Error creando hash de contraseña");
            }
            
            return $hash;
            
        } catch (Exception $e) {
            $this->logError("Error creando hash: " . $e->getMessage());
            throw new RuntimeException("Falla en hash de contraseña", 0, $e);
        }
    }
    
    /**
     * Verificar contraseña (método de instancia)
     * PATRÓN DE INSTANCIA: Para uso desde Persona.php
     */
    public function verifyPasswordWithPepperInstance(string $password, string $hash): bool
    {
        if (empty($password) || empty($hash)) {
            return false;
        }
        
        try {
            // Combinar contraseña con pepper
            $passwordWithPepper = $password . $this->pepper;
            
            // Verificar hash
            return password_verify($passwordWithPepper, $hash);
            
        } catch (Exception $e) {
            $this->logError("Error verificando contraseña: " . $e->getMessage());
            return false;
        }
    }
    
    // ==========================================
    // MÉTODOS DE VALIDACIÓN
    // ==========================================
    
    /**
     * Validar fortaleza de contraseña
     * SEGÚN PATRÓN DE OTROS MODELOS: Validaciones completas
     */
    public function validatePasswordStrength(string $password): bool
    {
        // Criterios de seguridad
        $minLength = 8;
        $hasUppercase = preg_match('/[A-Z]/', $password);
        $hasLowercase = preg_match('/[a-z]/', $password);
        $hasNumber = preg_match('/\d/', $password);
        $hasSpecialChar = preg_match('/[^A-Za-z\d]/', $password);
        
        return strlen($password) >= $minLength &&
               $hasUppercase &&
               $hasLowercase &&
               $hasNumber &&
               $hasSpecialChar;
    }
    
    /**
     * Validar que los datos están encriptados correctamente
     */
    public function isValidEncryptedData(string $encryptedData): bool
    {
        if (empty($encryptedData)) {
            return false;
        }
        
        // Verificar que es base64 válido
        $decoded = base64_decode($encryptedData, true);
        if ($decoded === false) {
            return false;
        }
        
        // Verificar longitud mínima (IV + al menos 1 byte de datos)
        $ivLength = openssl_cipher_iv_length(self::ENCRYPTION_ALGORITHM);
        return strlen($decoded) > $ivLength;
    }
    
    /**
     * Generar clave aleatoria segura
     */
    public function generateSecureKey(int $length = 32): string
    {
        try {
            $randomBytes = openssl_random_pseudo_bytes($length, $strong);
            
            if (!$strong) {
                throw new RuntimeException("Generador de números aleatorios no es seguro");
            }
            
            return base64_encode($randomBytes);
            
        } catch (Exception $e) {
            $this->logError("Error generando clave: " . $e->getMessage());
            throw new RuntimeException("Falla generando clave segura", 0, $e);
        }
    }
    
    /**
     * Generar código único para registro
     */
    public function generateUniqueCode(int $length = 12): string
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';
        
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $code;
    }
    
    // ==========================================
    // MÉTODOS DE CONFIGURACIÓN
    // ==========================================
    
    /**
     * Obtener información de configuración de encriptación
     * @return array Información de configuración (sin claves sensibles)
     */
    public function getEncryptionInfo(): array
    {
        return [
            'algorithm' => self::ENCRYPTION_ALGORITHM,
            'bcrypt_rounds' => $this->bcrypt_rounds,
            'aes_key_length' => strlen($this->aes_key),
            'pepper_length' => strlen($this->pepper),
            'openssl_version' => OPENSSL_VERSION_TEXT
        ];
    }
    
    /**
     * Verificar que la configuración de encriptación es válida
     */
    public function validateEncryptionConfiguration(): bool
    {
        try {
            // Verificar clave AES
            if (strlen($this->aes_key) !== 32) {
                return false;
            }
            
            // Verificar pepper
            if (strlen($this->pepper) < 20) {
                return false;
            }
            
            // Verificar rounds de BCRYPT
            if ($this->bcrypt_rounds < 10 || $this->bcrypt_rounds > 20) {
                return false;
            }
            
            // Verificar algoritmo disponible
            if (!in_array(self::ENCRYPTION_ALGORITHM, openssl_get_cipher_methods())) {
                return false;
            }
            
            // Test de encriptación/desencriptación
            $testData = 'test_encryption_' . time();
            $encrypted = $this->encryptDataInstance($testData);
            $decrypted = $this->decryptDataInstance($encrypted);
            
            if ($testData !== $decrypted) {
                return false;
            }
            
            // Test de hash/verificación
            $testPassword = 'test_password_' . time();
            $hash = $this->hashPasswordWithPepperInstance($testPassword);
            
            if (!$this->verifyPasswordWithPepperInstance($testPassword, $hash)) {
                return false;
            }
            
            return true;
            
        } catch (Exception $e) {
            $this->logError("Error validando configuración: " . $e->getMessage());
            return false;
        }
    }
    
    // ===============================
    // MÉTODOS ABSTRACTOS DE BASEMODEL
    // ===============================
    
    /**
     * Crear nuevo registro de configuración de encriptación
     * @param array $data
     * @return int|false
     */
    public function create(array $data): int|false
    {
        // CryptoModel no maneja datos de tabla específica
        // Es un modelo de servicio para encriptación
        $this->logError("CryptoModel::create() no implementado - es modelo de servicio");
        return false;
    }
    
    /**
     * Buscar por ID
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): array|null
    {
        // CryptoModel no maneja datos de tabla específica
        $this->logError("CryptoModel::findById() no implementado - es modelo de servicio");
        return null;
    }
    
    /**
     * Actualizar registro
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        // CryptoModel no maneja datos de tabla específica
        $this->logError("CryptoModel::update() no implementado - es modelo de servicio");
        return false;
    }
    
    /**
     * Eliminar registro
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        // CryptoModel no maneja datos de tabla específica
        $this->logError("CryptoModel::delete() no implementado - es modelo de servicio");
        return false;
    }
    
    /**
     * Obtener todos los registros
     * @param int $limit
     * @return array
     */
    public function findAll(int $limit = 100): array
    {
        // CryptoModel no maneja datos de tabla específica
        $this->logError("CryptoModel::findAll() no implementado - es modelo de servicio");
        return [];
    }
}
