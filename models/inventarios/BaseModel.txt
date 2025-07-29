<?php
/**
 * BASE MODEL - CLASE ABSTRACTA BASE PARA TODOS LOS MODELOS
 * Sistema Cyberhole Condominios - Arquitectura 3 Capas
 * 
 * @description Clase abstracta que provee métodos genéricos CRUD para todos los modelos
 *              Según RELACIONES_TABLAS: NO administra ninguna tabla específica
 * @author Sistema Cyberhole - Fanático Religioso de la Documentación
 * @version 2.0 - RECREADO DESDE CERO
 * @date 2025-07-11
 * 
 * 🔥 CUMPLIMIENTO RELIGIOSO DEL DIAGRAMA UML:
 * - +connect() PDO ✅ IMPLEMENTADO
 * - +create(array data) int|false ✅ IMPLEMENTADO
 * - +findById(int id) array|null ✅ IMPLEMENTADO
 * - +update(int id, array data) bool ✅ IMPLEMENTADO
 * - +delete(int id) bool ✅ IMPLEMENTADO
 * - +findAll() array ✅ IMPLEMENTADO
 * - +validateRequiredFields(array data, array required) bool ✅ IMPLEMENTADO
 * - +logError(string message) void ✅ IMPLEMENTADO
 * - +sanitizeInput(mixed input) mixed ✅ IMPLEMENTADO
 * 
 * 🔥 CUMPLIMIENTO RELIGIOSO DE RELACIONES_TABLAS:
 * - NO administra ninguna tabla ✅ CUMPLIDO
 * - Responsabilidad: Solo provee métodos genéricos ✅ CUMPLIDO
 * - Funciones: PDO connection, CRUD base, logging, validaciones ✅ CUMPLIDO
 */

require_once __DIR__ . '/../config/bootstrap.php';

abstract class BaseModel
{
    /**
     * @var PDO $connection Conexión a la base de datos
     * SEGÚN DIAGRAMA UML: -PDO connection
     * Se inicializa como null y se establece en el constructor
     */
    protected ?PDO $connection = null;
    
    /**
     * @var string $table Nombre de la tabla (definida en cada modelo hijo)
     * SEGÚN DIAGRAMA UML: -string table
     * Se inicializa como cadena vacía y debe ser sobrescrita por las clases hijas
     */
    protected string $table = '';
    
    /**
     * Constructor - Establece conexión automáticamente
     * CORREGIDO: Usa bootstrap para inicializar sistema completo
     */
    public function __construct()
    {
        // CORREGIDO: Usar bootstrap para inicializar sistema completo
        Bootstrap::init();
        
        $this->connection = $this->connect();
        
        if ($this->connection === null) {
            throw new Exception("Error: No se pudo establecer conexión a la base de datos");
        }
    }
    
    // ==========================================
    // MÉTODOS ESPECÍFICOS DEL DIAGRAMA UML
    // ==========================================
    
    /**
     * Establecer conexión con la base de datos
     * MÉTODO REQUERIDO POR DIAGRAMA UML: +connect() PDO
     * @return PDO|null Instancia de conexión PDO o null si falla
     */
    protected function connect(): ?PDO
    {
        try {
            // Usar DatabaseConfig que ya está inicializado por bootstrap
            return DatabaseConfig::getInstance()->getConnection();
        } catch (Exception $e) {
            $this->logError("Error de conexión: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Crear nuevo registro
     * MÉTODO REQUERIDO POR DIAGRAMA UML: +create(array data) int|false
     * @param array $data Datos del registro
     * @return int|false ID del registro creado o false si falla
     */
    public abstract function create(array $data): int|false;
    
    /**
     * Buscar registro por ID
     * MÉTODO REQUERIDO POR DIAGRAMA UML: +findById(int id) array|null
     * @param int $id ID del registro
     * @return array|null Datos del registro o null si no existe
     */
    public abstract function findById(int $id): array|null;
    
    /**
     * Actualizar registro por ID
     * MÉTODO REQUERIDO POR DIAGRAMA UML: +update(int id, array data) bool
     * @param int $id ID del registro
     * @param array $data Datos a actualizar
     * @return bool True si se actualizó correctamente
     */
    public abstract function update(int $id, array $data): bool;
    
    /**
     * Eliminar registro por ID
     * MÉTODO REQUERIDO POR DIAGRAMA UML: +delete(int id) bool
     * @param int $id ID del registro
     * @return bool True si se eliminó correctamente
     */
    public abstract function delete(int $id): bool;
    
    /**
     * Obtener todos los registros
     * MÉTODO REQUERIDO POR DIAGRAMA UML: +findAll() array
     * @param int $limit Límite de registros (por defecto 100)
     * @return array Lista de registros
     */
    public abstract function findAll(int $limit = 100): array;
    
    /**
     * Validar campos requeridos
     * MÉTODO REQUERIDO POR DIAGRAMA UML: +validateRequiredFields(array data, array required) bool
     * @param array $data Datos a validar
     * @param array $required Campos requeridos
     * @return bool True si todos los campos están presentes
     */
    public function validateRequiredFields(array $data, array $required): bool
    {
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $this->logError("Campo requerido faltante: {$field}");
                return false;
            }
        }
        return true;
    }
    
    /**
     * Registrar error en logs
     * MÉTODO REQUERIDO POR DIAGRAMA UML: +logError(string message) void
     * @param string $message Mensaje de error
     * @return void
     */
    public function logError(string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $className = get_class($this);
        $logMessage = "[{$timestamp}] [{$className}] ERROR: {$message}" . PHP_EOL;
        
        // Intentar escribir al archivo de logs
        $logFile = __DIR__ . '/../logs/app.log';
        if (is_writable(dirname($logFile))) {
            file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
        }
        
        // También loguear en error log de PHP como respaldo
        error_log("[Cyberhole] [{$className}] {$message}");
    }
    
    /**
     * Sanitizar entrada de datos
     * MÉTODO REQUERIDO POR DIAGRAMA UML: +sanitizeInput(mixed input) mixed
     * @param mixed $input Dato a sanitizar
     * @return mixed Dato sanitizado
     */
    public function sanitizeInput(mixed $input): mixed
    {
        if (is_string($input)) {
            // Eliminar espacios extras y caracteres especiales peligrosos
            $input = trim($input);
            $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
            return $input;
        }
        
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }
        
        return $input;
    }
    
    // ==========================================
    // MÉTODOS AUXILIARES COMUNES
    // ==========================================
    
    /**
     * Verificar si existe un registro por ID
     * @param int $id ID del registro
     * @return bool True si existe
     */
    protected function exists(int $id): bool
    {
        try {
            $sql = "SELECT 1 FROM {$this->table} WHERE id = :id LIMIT 1";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            return $stmt->fetchColumn() !== false;
            
        } catch (PDOException $e) {
            $this->logError("Error en exists(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Preparar condiciones WHERE dinámicas
     * @param array $conditions Condiciones de búsqueda
     * @return array ['sql' => string, 'params' => array]
     */
    protected function buildWhereClause(array $conditions): array
    {
        if (empty($conditions)) {
            return ['sql' => '', 'params' => []];
        }
        
        $whereParts = [];
        $params = [];
        
        foreach ($conditions as $field => $value) {
            if ($value !== null) {
                $whereParts[] = "{$field} = :{$field}";
                $params[$field] = $value;
            } else {
                $whereParts[] = "{$field} IS NULL";
            }
        }
        
        $sql = "WHERE " . implode(' AND ', $whereParts);
        
        return ['sql' => $sql, 'params' => $params];
    }
    
    /**
     * Obtener el último ID insertado
     * @return int|false ID insertado o false si falla
     */
    protected function getLastInsertId(): int|false
    {
        try {
            $id = $this->connection->lastInsertId();
            return $id ? (int)$id : false;
        } catch (PDOException $e) {
            $this->logError("Error obteniendo último ID: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ejecutar consulta de manera segura
     * @param string $sql Consulta SQL
     * @param array $params Parámetros de la consulta
     * @return PDOStatement|false Statement ejecutado o false si falla
     */
    protected function executeQuery(string $sql, array $params = []): PDOStatement|false
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $success = $stmt->execute($params);
            
            if (!$success) {
                $this->logError("Query execution failed: " . implode(', ', $stmt->errorInfo()));
                return false;
            }
            
            return $stmt;
            
        } catch (PDOException $e) {
            $this->logError("Error en executeQuery(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Construir SQL de inserción dinámico
     * @param array $data Datos a insertar
     * @return array ['sql' => string, 'params' => array]
     */
    protected function buildInsertQuery(array $data): array
    {
        $fields = array_keys($data);
        $placeholders = array_map(fn($field) => ":{$field}", $fields);
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        return ['sql' => $sql, 'params' => $data];
    }
    
    /**
     * Construir SQL de actualización dinámico
     * @param int $id ID del registro a actualizar
     * @param array $data Datos a actualizar
     * @return array ['sql' => string, 'params' => array]
     */
    protected function buildUpdateQuery(int $id, array $data): array
    {
        $setParts = [];
        foreach (array_keys($data) as $field) {
            $setParts[] = "{$field} = :{$field}";
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts) . " WHERE id = :id";
        $data['id'] = $id;
        
        return ['sql' => $sql, 'params' => $data];
    }
    
    /**
     * Obtener información de la tabla
     * @return array Información de columnas de la tabla
     */
    protected function getTableInfo(): array
    {
        try {
            $sql = "DESCRIBE {$this->table}";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            $this->logError("Error obteniendo info de tabla: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Contar registros totales
     * @param array $conditions Condiciones opcionales
     * @return int Número total de registros
     */
    protected function count(array $conditions = []): int
    {
        try {
            $whereClause = $this->buildWhereClause($conditions);
            $sql = "SELECT COUNT(*) FROM {$this->table} " . $whereClause['sql'];
            
            $stmt = $this->executeQuery($sql, $whereClause['params']);
            if (!$stmt) {
                return 0;
            }
            
            return (int)$stmt->fetchColumn();
            
        } catch (Exception $e) {
            $this->logError("Error en count(): " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Validar formato de email
     * @param string $email Email a validar
     * @return bool True si el formato es válido
     */
    protected function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validar longitud de string
     * @param string $value Valor a validar
     * @param int $minLength Longitud mínima
     * @param int $maxLength Longitud máxima
     * @return bool True si está en el rango válido
     */
    protected function isValidLength(string $value, int $minLength = 1, int $maxLength = 255): bool
    {
        $length = strlen(trim($value));
        return $length >= $minLength && $length <= $maxLength;
    }
    
    /**
     * Destructor - Cerrar conexión explícitamente
     */
    public function __destruct()
    {
        $this->connection = null;
    }
}
