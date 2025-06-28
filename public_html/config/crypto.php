<?php
/**
 * Utilidades de Cifrado AES - Sistema de Condominios
 */

// Prevenir acceso directo
if (!defined('SECURE_ACCESS')) {
    define('SECURE_ACCESS', true);
}

class CryptoUtils {
    
    private static $aesKey = null;
    private static $aesMethod = null;
    
    /**
     * Inicializar configuración de cifrado
     */
    private static function init() {
        if (self::$aesKey === null) {
            // Cargar variables de entorno
            self::loadEnvVariables();
            
            self::$aesKey = $_ENV['AES_KEY'] ?? 'Cyberhole2025SecretKey32CharLong!';
            self::$aesMethod = $_ENV['AES_METHOD'] ?? 'AES-256-CBC';
            
            // Verificar que la clave tenga la longitud correcta para AES-256
            if (strlen(self::$aesKey) !== 32) {
                self::$aesKey = substr(hash('sha256', self::$aesKey), 0, 32);
            }
        }
    }
    
    /**
     * Cifrar datos sensibles con AES
     */
    public static function encryptSensitiveData($data) {
        self::init();
        
        if (empty($data)) {
            return $data;
        }
        
        try {
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::$aesMethod));
            $encrypted = openssl_encrypt($data, self::$aesMethod, self::$aesKey, 0, $iv);
            
            if ($encrypted === false) {
                throw new Exception('Error en el cifrado');
            }
            
            // Combinar IV y datos cifrados, codificar en base64
            return base64_encode($iv . $encrypted);
            
        } catch (Exception $e) {
            error_log("Error de cifrado: " . $e->getMessage());
            throw new Exception('Error al cifrar datos sensibles');
        }
    }
    
    /**
     * Descifrar datos sensibles con AES
     */
    public static function decryptSensitiveData($encryptedData) {
        self::init();
        
        if (empty($encryptedData)) {
            return $encryptedData;
        }
        
        try {
            $data = base64_decode($encryptedData);
            if ($data === false) {
                throw new Exception('Datos inválidos para descifrado');
            }
            
            $ivLength = openssl_cipher_iv_length(self::$aesMethod);
            $iv = substr($data, 0, $ivLength);
            $encrypted = substr($data, $ivLength);
            
            $decrypted = openssl_decrypt($encrypted, self::$aesMethod, self::$aesKey, 0, $iv);
            
            if ($decrypted === false) {
                throw new Exception('Error en el descifrado');
            }
            
            return $decrypted;
            
        } catch (Exception $e) {
            error_log("Error de descifrado: " . $e->getMessage());
            throw new Exception('Error al descifrar datos sensibles');
        }
    }
    
    /**
     * Hash de contraseña con bcrypt y pepper
     */
    public static function hashPassword($password) {
        self::init();
        
        $pepper = $_ENV['PEPPER_SECRET'] ?? 'CyberholeCondominios2025PepperSecret';
        $rounds = (int)($_ENV['BCRYPT_ROUNDS'] ?? 12);
        
        // Agregar pepper a la contraseña
        $passwordWithPepper = $password . $pepper;
        
        return password_hash($passwordWithPepper, PASSWORD_BCRYPT, ['cost' => $rounds]);
    }
    
    /**
     * Verificar contraseña
     */
    public static function verifyPassword($password, $hash) {
        self::init();
        
        $pepper = $_ENV['PEPPER_SECRET'] ?? 'CyberholeCondominios2025PepperSecret';
        $passwordWithPepper = $password . $pepper;
        
        return password_verify($passwordWithPepper, $hash);
    }
    
    /**
     * Cifrar email para búsquedas
     */
    public static function encryptEmail($email) {
        return self::encryptSensitiveData(strtolower(trim($email)));
    }
    
    /**
     * Descifrar email
     */
    public static function decryptEmail($encryptedEmail) {
        return self::decryptSensitiveData($encryptedEmail);
    }
    
    /**
     * Cifrar CURP
     */
    public static function encryptCURP($curp) {
        return self::encryptSensitiveData(strtoupper(trim($curp)));
    }
    
    /**
     * Descifrar CURP
     */
    public static function decryptCURP($encryptedCURP) {
        return self::decryptSensitiveData($encryptedCURP);
    }
    
    /**
     * Cargar variables de entorno desde archivo .env
     */
    private static function loadEnvVariables() {
        $envFile = __DIR__ . '/../.env';
        
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            foreach ($lines as $line) {
                $line = trim($line);
                
                // Ignorar comentarios y líneas vacías
                if (empty($line) || $line[0] === '#') {
                    continue;
                }
                
                // Buscar = en la línea
                if (strpos($line, '=') === false) {
                    continue;
                }
                
                list($key, $value) = explode('=', $line, 2);
                
                $key = trim($key);
                $value = trim($value);
                
                // Remover comillas si existen
                if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                    (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                    $value = substr($value, 1, -1);
                }
                
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}
?>
