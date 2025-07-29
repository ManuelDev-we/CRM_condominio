<?php
/**
 * MODELO EMPLEADO - GESTIÓN DE EMPLEADOS Y TAREAS CON ACCESOS
 * Sistema Cyberhole Condominios - Arquitectura 3 Capas
 * 
 * RESPONSABILIDADES SEGÚN PROMPT MAESTRO + ESTRUCTURA BD HOSTINGER:
 * - TABLA PRINCIPAL: empleados_condominio
 * - TABLA SECUNDARIA: tareas
 * 
 * ARQUITECTURA 3 CAPAS:
 * - Capa 1 (Esta): Solo CRUD y validaciones básicas de integridad + ENCRIPTACIÓN AES
 * - Capa 2 (Servicios): Lógica de negocio (preparado para servicios rápidos)
 * - Capa 3 (Controladores): Presentación (pendiente)
 * 
 * 🔐 CAMPOS ENCRIPTADOS CON AES (CryptoModel):
 * - nombres (datos personales sensibles)
 * - apellido1 (datos personales sensibles)
 * - apellido2 (datos personales sensibles)
 * - puesto (información laboral sensible)
 * - fecha_contrato (información laboral sensible)
 * - descripcion (en tareas - puede contener información sensible)
 * 
 * 🆕 ESTRUCTURA BD HOSTINGER IMPLEMENTADA:
 * empleados_condominio: id_empleado, id_condominio, nombres, apellido1, apellido2, 
 *                      puesto (enum), fecha_contrato, id_acceso (varchar(64)), activo (tinyint(1))
 * tareas: id_tarea, id_condominio, id_calle, id_trabajador, descripcion, imagen
 * 
 * @author Sistema Cyberhole Condominios - PROMPT MAESTRO
 * @version 2.0 - RECREADO DESDE CERO CON ESTRUCTURA BD HOSTINGER
 * @since Julio 2025
 */

require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/CryptoModel.php';

class Empleado extends BaseModel 
{
    /**
     * Tabla principal que administra este modelo
     * @var string
     */
    protected string $table = 'empleados_condominio';
    
    /**
     * Tablas secundarias que administra este modelo
     * @var array
     */
    protected array $secondaryTables = [
        'tareas'
    ];
    
    /**
     * Campos encriptados con AES
     * @var array
     */
    protected array $encryptedFields = [
        'nombres',
        'apellido1', 
        'apellido2',
        'puesto',
        'fecha_contrato'
    ];
    
    /**
     * Campos encriptados en tareas
     * @var array
     */
    protected array $encryptedFieldsTareas = [
        'descripcion'
    ];
    
    /**
     * Campos requeridos para crear un empleado
     * @var array
     */
    protected array $requiredFields = [
        'id_condominio',
        'nombres',
        'apellido1',
        'apellido2',
        'puesto'
    ];
    
    /**
     * Campos requeridos para crear una tarea
     * @var array
     */
    protected array $requiredFieldsTarea = [
        'id_condominio',
        'id_calle',
        'id_trabajador',
        'descripcion'
    ];
    
    /**
     * Constructor del modelo Empleado
     */
    public function __construct() 
    {
        parent::__construct();
    }
    
    // ===============================================
    // MÉTODOS ABSTRACTOS OBLIGATORIOS DE BASEMODEL
    // ===============================================
    
    /**
     * Crear nuevo empleado con encriptación AES y campos de acceso
     * 
     * @param array $data Datos del empleado
     * @return int|false ID del empleado creado o false en error
     */
    public function create(array $data): int|false 
    {
        try {
            // Validar campos requeridos
            if (!$this->validateRequiredFields($data, $this->requiredFields)) {
                $this->logError("Empleado::create - Campos requeridos faltantes");
                return false;
            }
            
            // Validar puesto
            if (!$this->validatePuestoValue($data['puesto'])) {
                $this->logError("Empleado::create - Puesto inválido: " . $data['puesto']);
                return false;
            }
            
            // Validar condominio existe
            if (!$this->validateCondominioExists((int)$data['id_condominio'])) {
                $this->logError("Empleado::create - Condominio no existe: " . $data['id_condominio']);
                return false;
            }
            
            // Validar id_acceso único si se proporciona
            if (!empty($data['id_acceso']) && !$this->validateIdAccesoUnique($data['id_acceso'])) {
                $this->logError("Empleado::create - id_acceso ya existe: " . $data['id_acceso']);
                return false;
            }
            
            // Encriptar campos sensibles
            $encryptedData = [
                'id_condominio' => (int)$data['id_condominio'],
                'nombres' => CryptoModel::encryptData($this->sanitizeInput($data['nombres'])),
                'apellido1' => CryptoModel::encryptData($this->sanitizeInput($data['apellido1'])),
                'apellido2' => CryptoModel::encryptData($this->sanitizeInput($data['apellido2'])),
                'puesto' => CryptoModel::encryptData($this->sanitizeInput($data['puesto'])),
                'fecha_contrato' => isset($data['fecha_contrato']) && !empty($data['fecha_contrato']) 
                    ? CryptoModel::encryptData($this->sanitizeInput($data['fecha_contrato'])) 
                    : null,
                'id_acceso' => isset($data['id_acceso']) && !empty($data['id_acceso']) 
                    ? $this->sanitizeInput($data['id_acceso']) 
                    : null,
                'activo' => isset($data['activo']) ? (int)$data['activo'] : 1
            ];
            
            $stmt = $this->connection->prepare("
                INSERT INTO empleados_condominio (id_condominio, nombres, apellido1, apellido2, puesto, fecha_contrato, id_acceso, activo) 
                VALUES (:id_condominio, :nombres, :apellido1, :apellido2, :puesto, :fecha_contrato, :id_acceso, :activo)
            ");
            
            if ($stmt->execute($encryptedData)) {
                return (int)$this->connection->lastInsertId();
            }
            
            return false;
            
        } catch (Exception $e) {
            $this->logError("Empleado::create - Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Buscar empleado por ID con desencriptación AES
     * 
     * @param int $id ID del empleado
     * @return array|null Datos del empleado o null si no existe
     */
    public function findById(int $id): array|null 
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT ec.*, 
                       c.nombre as condominio_nombre
                FROM empleados_condominio ec
                LEFT JOIN condominios c ON ec.id_condominio = c.id_condominio
                WHERE ec.id_empleado = :id
            ");
            
            $stmt->execute(['id' => $id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return null;
            }
            
            // Desencriptar campos sensibles
            $result = $this->decryptEmployeeData($result);
            
            return $result;
            
        } catch (Exception $e) {
            $this->logError("Empleado::findById - Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Actualizar empleado por ID con encriptación AES
     * 
     * @param int $id ID del empleado
     * @param array $data Datos a actualizar
     * @return bool True si se actualizó, false en error
     */
    public function update(int $id, array $data): bool 
    {
        try {
            $allowedFields = ['nombres', 'apellido1', 'apellido2', 'puesto', 'fecha_contrato', 'id_acceso', 'activo'];
            $updateData = [];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    if (in_array($field, $this->encryptedFields)) {
                        if ($field === 'fecha_contrato' && empty($data[$field])) {
                            $updateData[$field] = null;
                        } else {
                            $updateData[$field] = CryptoModel::encryptData($this->sanitizeInput($data[$field]));
                        }
                    } elseif ($field === 'id_acceso') {
                        // Validar id_acceso único si se está actualizando
                        if (!empty($data[$field]) && !$this->validateIdAccesoUnique($data[$field], $id)) {
                            $this->logError("Empleado::update - id_acceso ya existe: " . $data[$field]);
                            return false;
                        }
                        $updateData[$field] = !empty($data[$field]) ? $this->sanitizeInput($data[$field]) : null;
                    } elseif ($field === 'activo') {
                        $updateData[$field] = (int)$data[$field];
                    } else {
                        $updateData[$field] = $this->sanitizeInput($data[$field]);
                    }
                }
            }
            
            if (empty($updateData)) {
                return false;
            }
            
            // Validar puesto si se está actualizando
            if (isset($data['puesto']) && !$this->validatePuestoValue($data['puesto'])) {
                $this->logError("Empleado::update - Puesto inválido: " . $data['puesto']);
                return false;
            }
            
            $setClause = implode(', ', array_map(fn($field) => "$field = :$field", array_keys($updateData)));
            
            $stmt = $this->connection->prepare("UPDATE empleados_condominio SET $setClause WHERE id_empleado = :id");
            
            $updateData['id'] = $id;
            
            return $stmt->execute($updateData);
            
        } catch (Exception $e) {
            $this->logError("Empleado::update - Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar empleado por ID
     * 
     * @param int $id ID del empleado
     * @return bool True si se eliminó, false en error
     */
    public function delete(int $id): bool 
    {
        try {
            $stmt = $this->connection->prepare("DELETE FROM empleados_condominio WHERE id_empleado = :id");
            
            return $stmt->execute(['id' => $id]);
            
        } catch (Exception $e) {
            $this->logError("Empleado::delete - Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener todos los empleados con desencriptación AES
     * 
     * @param int $limit Límite de registros
     * @return array Lista de empleados
     */
    public function findAll(int $limit = 100): array 
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT ec.*, 
                       c.nombre as condominio_nombre
                FROM empleados_condominio ec
                LEFT JOIN condominios c ON ec.id_condominio = c.id_condominio
                ORDER BY ec.id_empleado DESC
                LIMIT :limit
            ");
            
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Desencriptar datos de todos los empleados
            $decryptedResults = [];
            foreach ($results as $result) {
                $decryptedResults[] = $this->decryptEmployeeData($result);
            }
            
            return $decryptedResults;
            
        } catch (Exception $e) {
            $this->logError("Empleado::findAll - Error: " . $e->getMessage());
            return [];
        }
    }
    
    // ===============================================
    // MÉTODOS ESPECÍFICOS DE EMPLEADOS
    // ===============================================
    
    /**
     * Encontrar empleados por condominio con desencriptación AES
     * 
     * @param int $id_condominio ID del condominio
     * @param array $options Opciones adicionales (activos_solamente, limite)
     * @return array Lista de empleados del condominio
     */
    public function findEmpleadosByCondominio(int $id_condominio, array $options = []): array 
    {
        try {
            $sql = "SELECT ec.*, 
                           c.nombre as condominio_nombre
                    FROM empleados_condominio ec
                    LEFT JOIN condominios c ON ec.id_condominio = c.id_condominio
                    WHERE ec.id_condominio = :id_condominio";
            
            // Filtros adicionales opcionales
            if (!empty($options['activos_solamente'])) {
                $sql .= " AND ec.activo = 1";
            }
            
            $sql .= " ORDER BY ec.apellido1 ASC, ec.nombres ASC";
            
            if (!empty($options['limite'])) {
                $sql .= " LIMIT " . intval($options['limite']);
            }
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(['id_condominio' => $id_condominio]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Desencriptar datos de todos los empleados
            $decryptedResults = [];
            foreach ($results as $result) {
                $decryptedResults[] = $this->decryptEmployeeData($result);
            }
            
            return $decryptedResults;
            
        } catch (Exception $e) {
            $this->logError("Empleado::findEmpleadosByCondominio - Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar empleado por id_acceso
     * 
     * @param string $id_acceso Código de acceso del empleado
     * @return array|null Datos del empleado o null si no existe
     */
    public function findByAcceso(string $id_acceso): array|null 
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT ec.*, 
                       c.nombre as condominio_nombre
                FROM empleados_condominio ec
                LEFT JOIN condominios c ON ec.id_condominio = c.id_condominio
                WHERE ec.id_acceso = :id_acceso AND ec.activo = 1
            ");
            
            $stmt->execute(['id_acceso' => $id_acceso]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return null;
            }
            
            // Desencriptar campos sensibles
            $result = $this->decryptEmployeeData($result);
            
            return $result;
            
        } catch (Exception $e) {
            $this->logError("Empleado::findByAcceso - Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Activar o desactivar empleado
     * 
     * @param int $id ID del empleado
     * @param bool $activo True para activar, false para desactivar
     * @return bool True si se actualizó, false en error
     */
    public function toggleActivo(int $id, bool $activo): bool 
    {
        try {
            $stmt = $this->connection->prepare("
                UPDATE empleados_condominio 
                SET activo = :activo 
                WHERE id_empleado = :id
            ");
            
            return $stmt->execute([
                'id' => $id,
                'activo' => $activo ? 1 : 0
            ]);
            
        } catch (Exception $e) {
            $this->logError("Empleado::toggleActivo - Error: " . $e->getMessage());
            return false;
        }
    }
    
    // ===============================================
    // MÉTODOS ESPECÍFICOS DE TAREAS
    // ===============================================
    
    /**
     * Crear nueva tarea con encriptación AES
     * 
     * @param array $data Datos de la tarea
     * @return int|false ID de la tarea creada o false en error
     */
    public function createTarea(array $data): int|false 
    {
        try {
            // Validar campos requeridos
            if (!$this->validateRequiredFields($data, $this->requiredFieldsTarea)) {
                $this->logError("Empleado::createTarea - Campos requeridos faltantes");
                return false;
            }
            
            // Validar que el trabajador existe y está activo
            if (!$this->validateEmpleadoExists((int)$data['id_trabajador'])) {
                $this->logError("Empleado::createTarea - Trabajador no existe: " . $data['id_trabajador']);
                return false;
            }
            
            // Encriptar campo sensible
            $encryptedData = [
                'id_condominio' => (int)$data['id_condominio'],
                'id_calle' => (int)$data['id_calle'],
                'id_trabajador' => (int)$data['id_trabajador'],
                'descripcion' => CryptoModel::encryptData($this->sanitizeInput($data['descripcion'])),
                'imagen' => $data['imagen'] ?? null
            ];
            
            $stmt = $this->connection->prepare("
                INSERT INTO tareas (id_condominio, id_calle, id_trabajador, descripcion, imagen) 
                VALUES (:id_condominio, :id_calle, :id_trabajador, :descripcion, :imagen)
            ");
            
            if ($stmt->execute($encryptedData)) {
                return (int)$this->connection->lastInsertId();
            }
            
            return false;
            
        } catch (Exception $e) {
            $this->logError("Empleado::createTarea - Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Encontrar tareas por trabajador con desencriptación AES
     * 
     * @param int $id_trabajador ID del trabajador (empleado)
     * @return array Lista de tareas del trabajador
     */
    public function findTareasByTrabajador(int $id_trabajador): array 
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT t.*, 
                       c.nombre as condominio_nombre,
                       cal.nombre as calle_nombre,
                       ec.nombres as empleado_nombres,
                       ec.apellido1 as empleado_apellido1
                FROM tareas t
                LEFT JOIN condominios c ON t.id_condominio = c.id_condominio
                LEFT JOIN calles cal ON t.id_calle = cal.id_calle
                LEFT JOIN empleados_condominio ec ON t.id_trabajador = ec.id_empleado
                WHERE t.id_trabajador = :id_trabajador
                ORDER BY t.id_tarea DESC
            ");
            
            $stmt->execute(['id_trabajador' => $id_trabajador]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Desencriptar datos de todas las tareas
            $decryptedResults = [];
            foreach ($results as $result) {
                $decryptedResults[] = $this->decryptTaskData($result);
            }
            
            return $decryptedResults;
            
        } catch (Exception $e) {
            $this->logError("Empleado::findTareasByTrabajador - Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Encontrar tareas por condominio con desencriptación AES
     * 
     * @param int $id_condominio ID del condominio
     * @return array Lista de tareas del condominio
     */
    public function findTareasByCondominio(int $id_condominio): array 
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT t.*, 
                       c.nombre as condominio_nombre,
                       cal.nombre as calle_nombre,
                       ec.nombres as empleado_nombres,
                       ec.apellido1 as empleado_apellido1
                FROM tareas t
                LEFT JOIN condominios c ON t.id_condominio = c.id_condominio
                LEFT JOIN calles cal ON t.id_calle = cal.id_calle
                LEFT JOIN empleados_condominio ec ON t.id_trabajador = ec.id_empleado
                WHERE t.id_condominio = :id_condominio
                ORDER BY t.id_tarea DESC
            ");
            
            $stmt->execute(['id_condominio' => $id_condominio]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Desencriptar datos de todas las tareas
            $decryptedResults = [];
            foreach ($results as $result) {
                $decryptedResults[] = $this->decryptTaskData($result);
            }
            
            return $decryptedResults;
            
        } catch (Exception $e) {
            $this->logError("Empleado::findTareasByCondominio - Error: " . $e->getMessage());
            return [];
        }
    }
    
    // ===============================================
    // MÉTODOS DE VALIDACIÓN
    // ===============================================
    
    /**
     * Validar que el puesto sea válido según enum de BD
     * 
     * @param string $puesto Puesto a validar
     * @return bool True si es válido, false si no
     */
    public function validatePuestoValue(string $puesto): bool 
    {
        $puestosValidos = ['servicio', 'administracion', 'mantenimiento'];
        return in_array(strtolower($puesto), $puestosValidos);
    }
    
    /**
     * Validar que el condominio existe
     * 
     * @param int $id_condominio ID del condominio
     * @return bool True si existe, false si no
     */
    public function validateCondominioExists(int $id_condominio): bool 
    {
        try {
            $stmt = $this->connection->prepare("SELECT COUNT(*) FROM condominios WHERE id_condominio = :id");
            $stmt->execute(['id' => $id_condominio]);
            
            return $stmt->fetchColumn() > 0;
            
        } catch (Exception $e) {
            $this->logError("Empleado::validateCondominioExists - Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validar que el empleado existe
     * 
     * @param int $id_empleado ID del empleado
     * @return bool True si existe, false si no
     */
    public function validateEmpleadoExists(int $id_empleado): bool 
    {
        try {
            $stmt = $this->connection->prepare("SELECT COUNT(*) FROM empleados_condominio WHERE id_empleado = :id");
            $stmt->execute(['id' => $id_empleado]);
            
            return $stmt->fetchColumn() > 0;
            
        } catch (Exception $e) {
            $this->logError("Empleado::validateEmpleadoExists - Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validar que id_acceso sea único
     * 
     * @param string $id_acceso Código de acceso a validar
     * @param int|null $exclude_id ID a excluir de la validación (para updates)
     * @return bool True si es único, false si ya existe
     */
    public function validateIdAccesoUnique(string $id_acceso, ?int $exclude_id = null): bool 
    {
        try {
            $sql = "SELECT COUNT(*) FROM empleados_condominio WHERE id_acceso = :id_acceso";
            $params = ['id_acceso' => $id_acceso];
            
            if ($exclude_id !== null) {
                $sql .= " AND id_empleado != :exclude_id";
                $params['exclude_id'] = $exclude_id;
            }
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchColumn() == 0;
            
        } catch (Exception $e) {
            $this->logError("Empleado::validateIdAccesoUnique - Error: " . $e->getMessage());
            return false;
        }
    }
    
    // ===============================================
    // MÉTODOS DE DESENCRIPTACIÓN
    // ===============================================
    
    /**
     * Desencriptar datos de empleado
     * 
     * @param array $data Datos encriptados del empleado
     * @return array Datos desencriptados
     */
    private function decryptEmployeeData(array $data): array 
    {
        try {
            foreach ($this->encryptedFields as $field) {
                if (isset($data[$field]) && !empty($data[$field])) {
                    $data[$field] = CryptoModel::decryptData($data[$field]);
                }
            }
            
            return $data;
            
        } catch (Exception $e) {
            $this->logError("Empleado::decryptEmployeeData - Error: " . $e->getMessage());
            return $data; // Retornar datos originales en caso de error
        }
    }
    
    /**
     * Desencriptar datos de tarea
     * 
     * @param array $data Datos encriptados de la tarea
     * @return array Datos desencriptados
     */
    private function decryptTaskData(array $data): array 
    {
        try {
            // Desencriptar campos de la tarea
            foreach ($this->encryptedFieldsTareas as $field) {
                if (isset($data[$field]) && !empty($data[$field])) {
                    $data[$field] = CryptoModel::decryptData($data[$field]);
                }
            }
            
            // CORRECCIÓN: Desencriptar TODOS los campos del empleado si están presentes
            $employeeFields = ['empleado_nombres', 'empleado_apellido1', 'empleado_apellido2', 'empleado_puesto'];
            foreach ($employeeFields as $field) {
                if (isset($data[$field]) && !empty($data[$field])) {
                    // Verificar si el dato está encriptado (contiene el prefijo característico)
                    if (strpos($data[$field], 'encrypted:') === 0 || strlen($data[$field]) > 50) {
                        $data[$field] = CryptoModel::decryptData($data[$field]);
                    }
                }
            }
            
            return $data;
            
        } catch (Exception $e) {
            $this->logError("Empleado::decryptTaskData - Error desencriptando: " . $e->getMessage());
            return $data; // Retornar datos originales en caso de error
        }
    }
    
    // ===============================================
    // MÉTODOS ESTÁTICOS PARA CAPA DE SERVICIOS
    // ===============================================
    
    /**
     * Obtener empleados por condominio (método estático)
     * Facilita el acceso desde la capa de servicios
     * 
     * @param int $id_condominio ID del condominio
     * @param array $options Opciones adicionales
     * @return array Lista de empleados del condominio
     * @throws Exception Si ocurre error crítico de BD
     */
    public static function obtenerEmpleadosPorCondominio(int $id_condominio, array $options = []): array 
    {
        try {
            $instance = new self();
            
            // Validar que el condominio existe antes de proceder
            if (!$instance->validateCondominioExists($id_condominio)) {
                throw new Exception("Condominio con ID $id_condominio no existe");
            }
            
            $result = $instance->findEmpleadosByCondominio($id_condominio, $options);
            
            if ($result === false) {
                throw new Exception("Error crítico al obtener empleados del condominio $id_condominio");
            }
            
            return $result;
            
        } catch (Exception $e) {
            // Log específico del error
            error_log("Error crítico obtenerEmpleadosPorCondominio ID($id_condominio): " . $e->getMessage());
            
            // Re-lanzar excepción para manejo en capa superior
            throw new Exception("Fallo al obtener empleados: " . $e->getMessage());
        }
    }
}
?>
