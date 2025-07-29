<?php
/**
 * Configuración de Base de Datos
 * Sistema Cyberhole Condominios
 * 
 * Define los parámetros para la conexión a la base de datos MySQL.
 * Utiliza variables de entorno para mantener las credenciales seguras.
 */

class DatabaseConfig {
    private static $instance = null;
    private $connection = null;
    
    // Configuración de conexión
    private $host;
    private $port;
    private $database;
    private $username;
    private $password;
    private $charset;
    private $options;
    
    private function __construct() {
        $this->loadConfiguration();
        $this->setOptions();
    }
    
    /**
     * Singleton para obtener instancia única
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new DatabaseConfig();
        }
        return self::$instance;
    }
    
    /**
     * Carga la configuración desde variables de entorno
     */
    private function loadConfiguration() {
        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->port = $_ENV['DB_PORT'] ?? '3306';
        $this->database = $_ENV['DB_DATABASE'] ?? 'u837350477_Cuestionario';
        $this->username = $_ENV['DB_USERNAME'] ?? 'root';
        $this->password = $_ENV['DB_PASSWORD'] ?? '';
        $this->charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';
    }
    
    /**
     * Configura las opciones de PDO
     */
    private function setOptions() {
        $this->options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}",
            PDO::ATTR_TIMEOUT => 30,
            PDO::ATTR_PERSISTENT => false
        ];
    }
    
    /**
     * Obtiene la conexión PDO
     */
    public function getConnection() {
        if ($this->connection === null) {
            try {
                $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->database};charset={$this->charset}";
                $this->connection = new PDO($dsn, $this->username, $this->password, $this->options);
                
                // Log de conexión exitosa
                error_log("[DB] Conexión establecida correctamente a: {$this->database}");
                
            } catch (PDOException $e) {
                error_log("[DB ERROR] Error de conexión: " . $e->getMessage());
                throw new Exception("Error de conexión a la base de datos: " . $e->getMessage());
            }
        }
        
        return $this->connection;
    }
    
    /**
     * Cierra la conexión
     */
    public function closeConnection() {
        $this->connection = null;
    }
    
    /**
     * Verifica si la conexión está activa
     */
    public function isConnected() {
        try {
            if ($this->connection !== null) {
                $this->connection->query('SELECT 1');
                return true;
            }
        } catch (PDOException $e) {
            return false;
        }
        return false;
    }
    
    /**
     * Obtiene información de la configuración (sin credenciales)
     */
    public function getInfo() {
        return [
            'host' => $this->host,
            'port' => $this->port,
            'database' => $this->database,
            'charset' => $this->charset,
            'connected' => $this->isConnected()
        ];
    }
}

// Función helper para obtener conexión rápidamente
function getDatabase() {
    return DatabaseConfig::getInstance()->getConnection();
}

// Función helper para obtener información de la base de datos
function getDatabaseInfo() {
    return DatabaseConfig::getInstance()->getInfo();
}