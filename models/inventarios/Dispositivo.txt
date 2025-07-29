<?php
/**
 * DISPOSITIVO MODEL - GESTIÓN DE UNIDADES PERSONA Y DISPOSITIVOS
 * Sistema Cyberhole Condominios - Arquitectura 3 Capas
 * 
 * @description Gestiona personas_unidad + persona_dispositivo con 7 campos AES encriptados
 *              Según RELACIONES_TABLAS: NO gestiona tabla 'dispositivos' (no existe)
 * @author Sistema Cyberhole - Fanático Religioso de la Documentación
 * @version 3.0 - RECREADO DESDE CERO SIGUIENDO DOCUMENTACIÓN
 * @date 2025-07-13
 * 
 * 🔥 CUMPLIMIENTO RELIGIOSO DE RELACIONES_TABLAS:
 * - Tabla Principal: personas_unidad ✅ IMPLEMENTADO
 * - Tabla Secundaria: persona_dispositivo ✅ IMPLEMENTADO
 * - Responsabilidad: CRUD de unidades persona + asociaciones dispositivo ✅ IMPLEMENTADO
 * - Flexibilidad: Datos adicionales por persona + gestión de dispositivos ✅ IMPLEMENTADO
 * 
 * 🔥 CUMPLIMIENTO RELIGIOSO DEL DIAGRAMA UML:
 * - +createUnidad(array data) int|false ✅ IMPLEMENTADO
 * - +findUnidadByCURP(string curp) array|null ✅ IMPLEMENTADO
 * - +associateDispositivo(int unidadId, string tipo, int dispositivoId) bool ✅ IMPLEMENTADO
 * - +getDispositivosByUnidad(int unidadId) array ✅ IMPLEMENTADO
 * - +validateCURPUnique(string curp) bool ✅ IMPLEMENTADO
 * - +validateTipoDispositivo(string tipo) bool ✅ IMPLEMENTADO
 * 
 * 🔥 CUMPLIMIENTO RELIGIOSO DE COLECCIÓN_VARIABLES_ENCRIPTACIÓN:
 * - telefono_1 → ENCRIPTAR AES ✅ IMPLEMENTADO
 * - telefono_2 → ENCRIPTAR AES ✅ IMPLEMENTADO
 * - curp → ENCRIPTAR AES ✅ IMPLEMENTADO
 * - nombres → ENCRIPTAR AES ✅ IMPLEMENTADO
 * - apellido1 → ENCRIPTAR AES ✅ IMPLEMENTADO
 * - apellido2 → ENCRIPTAR AES ✅ IMPLEMENTADO
 * - fecha_nacimiento → ENCRIPTAR AES ✅ IMPLEMENTADO
 * - TOTAL: 7 campos AES (según especificación) ✅ CUMPLIDO
 * 
 * 🔥 CUMPLIMIENTO RELIGIOSO DE ESTRUCTURA BD:
 * - personas_unidad: id_persona_unidad, telefono_1, telefono_2, curp, nombres, apellido1, apellido2, fecha_nacimiento, foto, creado_en ✅ CUMPLIDO
 * - persona_dispositivo: id_persona_dispositivo, id_persona_unidad, tipo_dispositivo, id_dispositivo, creado_en ✅ CUMPLIDO
 * - FK: persona_dispositivo.id_persona_unidad → personas_unidad.id_persona_unidad ✅ CUMPLIDO
 */

require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/CryptoModel.php';

class Dispositivo extends BaseModel
{
    /**
     * @var string $table Tabla principal
     * SEGÚN RELACIONES_TABLAS: personas_unidad (NO dispositivos)
     */
    protected string $table = 'personas_unidad';
    
    /**
     * @var CryptoModel $crypto Instancia de encriptación
     * SEGÚN COLECCIÓN_VARIABLES_ENCRIPTACIÓN: Para los 7 campos AES
     */
    private CryptoModel $crypto;
    
    /**
     * @var array $encryptedFields Campos que requieren encriptación AES
     * SEGÚN COLECCIÓN_VARIABLES_ENCRIPTACIÓN: 7 campos específicos
     */
    private array $encryptedFields = [
        'telefono_1',       // ENCRIPTAR AES
        'telefono_2',       // ENCRIPTAR AES
        'curp',             // ENCRIPTAR AES (dato muy sensible)
        'nombres',          // ENCRIPTAR AES
        'apellido1',        // ENCRIPTAR AES
        'apellido2',        // ENCRIPTAR AES
        'fecha_nacimiento'  // ENCRIPTAR AES (dato sensible)
    ];
    
    /**
     * @var array $requiredFields Campos obligatorios para crear unidad
     * SEGÚN ESTRUCTURA BD: id_persona_unidad es AUTO_INCREMENT
     */
    private array $requiredFields = [
        'telefono_1',
        'curp',
        'nombres',
        'apellido1',
        'fecha_nacimiento'
    ];
    
    /**
     * @var array $validTiposDispositivo Tipos válidos de dispositivo
     * SEGÚN ESTRUCTURA BD: enum('tag','engomado')
     */
    private array $validTiposDispositivo = ['tag', 'engomado'];
    
    /**
     * Constructor - Inicializar encriptación
     */
    public function __construct()
    {
        parent::__construct();
        $this->crypto = new CryptoModel();
    }
    
    // ==========================================
    // MÉTODOS CRUD REQUERIDOS POR BASEMODEL
    // ==========================================
    
    /**
     * Crear nueva unidad con encriptación
     * MÉTODO REQUERIDO POR BASEMODEL: +create(array data) int|false
     * @param array $data Datos de la unidad
     * @return int|false ID de la unidad creada o false si falla
     */
    public function create(array $data): int|false
    {
        try {
            // Validar campos requeridos
            if (!$this->validateRequiredFields($data, $this->requiredFields)) {
                $this->logError("Campos requeridos faltantes en create()");
                return false;
            }
            
            // Validar CURP único
            if (!$this->validateCURPUnique($data['curp'])) {
                $this->logError("CURP duplicado: " . $data['curp']);
                return false;
            }
            
            // Encriptar campos sensibles
            $encryptedData = $this->encryptSensitiveFields($data);
            
            // Construir consulta SQL
            $fields = array_keys($encryptedData);
            $placeholders = ':' . implode(', :', $fields);
            $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") VALUES ($placeholders)";
            
            $stmt = $this->connection->prepare($sql);
            
            if ($stmt->execute($encryptedData)) {
                $unidadId = (int)$this->connection->lastInsertId();
                $this->logError("Unidad creada exitosamente con ID: $unidadId");
                return $unidadId;
            }
            
            $this->logError("Error ejecutando inserción de unidad");
            return false;
            
        } catch (Exception $e) {
            $this->logError("Excepción en create(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Buscar unidad por ID con desencriptación
     * MÉTODO REQUERIDO POR BASEMODEL: +findById(int id) array|null
     * @param int $id ID de la unidad
     * @return array|null Datos de la unidad o null si no existe
     */
    public function findById(int $id): array|null
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id_persona_unidad = :id";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result) {
                    // Desencriptar campos sensibles
                    return $this->decryptSensitiveFields($result);
                }
            }
            
            return null;
            
        } catch (Exception $e) {
            $this->logError("Excepción en findById(): " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Actualizar unidad por ID con reencriptación
     * MÉTODO REQUERIDO POR BASEMODEL: +update(int id, array data) bool
     * @param int $id ID de la unidad
     * @param array $data Datos a actualizar
     * @return bool True si se actualizó correctamente
     */
    public function update(int $id, array $data): bool
    {
        try {
            // Validar que la unidad existe
            if (!$this->findById($id)) {
                $this->logError("Unidad no encontrada para actualizar: ID $id");
                return false;
            }
            
            // Validar CURP único si se está actualizando
            if (isset($data['curp'])) {
                $currentRecord = $this->findById($id);
                if ($currentRecord && $currentRecord['curp'] !== $data['curp']) {
                    if (!$this->validateCURPUnique($data['curp'])) {
                        $this->logError("CURP duplicado en actualización: " . $data['curp']);
                        return false;
                    }
                }
            }
            
            // Encriptar campos sensibles si están presentes
            $encryptedData = $this->encryptSensitiveFields($data);
            
            // Construir consulta de actualización
            $setParts = [];
            foreach ($encryptedData as $field => $value) {
                $setParts[] = "$field = :$field";
            }
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts) . " WHERE id_persona_unidad = :id";
            $stmt = $this->connection->prepare($sql);
            
            // Bind parámetros
            $encryptedData['id'] = $id;
            
            if ($stmt->execute($encryptedData)) {
                $this->logError("Unidad actualizada exitosamente: ID $id");
                return true;
            }
            
            $this->logError("Error ejecutando actualización de unidad: ID $id");
            return false;
            
        } catch (Exception $e) {
            $this->logError("Excepción en update(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar unidad por ID
     * MÉTODO REQUERIDO POR BASEMODEL: +delete(int id) bool
     * @param int $id ID de la unidad
     * @return bool True si se eliminó correctamente
     */
    public function delete(int $id): bool
    {
        try {
            // Verificar que la unidad existe
            if (!$this->findById($id)) {
                $this->logError("Unidad no encontrada para eliminar: ID $id");
                return false;
            }
            
            // Eliminar asociaciones de dispositivos primero (FK CASCADE debe manejarlo)
            $sql = "DELETE FROM {$this->table} WHERE id_persona_unidad = :id";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $this->logError("Unidad eliminada exitosamente: ID $id");
                return true;
            }
            
            $this->logError("Error ejecutando eliminación de unidad: ID $id");
            return false;
            
        } catch (Exception $e) {
            $this->logError("Excepción en delete(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener todas las unidades con límite
     * MÉTODO REQUERIDO POR BASEMODEL: +findAll() array
     * @param int $limit Límite de resultados
     * @return array Lista de unidades
     */
    public function findAll(int $limit = 100): array
    {
        try {
            $sql = "SELECT * FROM {$this->table} ORDER BY creado_en DESC LIMIT :limit";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Desencriptar todos los resultados
                return array_map([$this, 'decryptSensitiveFields'], $results);
            }
            
            return [];
            
        } catch (Exception $e) {
            $this->logError("Excepción en findAll(): " . $e->getMessage());
            return [];
        }
    }
    
    // ==========================================
    // MÉTODOS ESPECÍFICOS DEL DIAGRAMA UML
    // ==========================================
    
    /**
     * Crear nueva unidad persona
     * MÉTODO REQUERIDO POR DIAGRAMA UML: +createUnidad(array data) int|false
     * @param array $data Datos de la unidad
     * @return int|false ID de la unidad creada o false si falla
     */
    public function createUnidad(array $data): int|false
    {
        // Delegar al método create() genérico
        return $this->create($data);
    }
    
    /**
     * Buscar unidad por CURP
     * MÉTODO REQUERIDO POR DIAGRAMA UML: +findUnidadByCURP(string curp) array|null
     * @param string $curp CURP a buscar
     * @return array|null Datos de la unidad o null si no existe
     */
    public function findUnidadByCURP(string $curp): array|null
    {
        try {
            // Buscar en todas las unidades (necesario porque CURP está encriptado)
            $sql = "SELECT * FROM {$this->table}";
            $stmt = $this->connection->prepare($sql);
            
            if ($stmt->execute()) {
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Desencriptar y buscar CURP coincidente
                foreach ($results as $result) {
                    $decrypted = $this->decryptSensitiveFields($result);
                    if ($decrypted['curp'] === $curp) {
                        return $decrypted;
                    }
                }
            }
            
            return null;
            
        } catch (Exception $e) {
            $this->logError("Excepción en findUnidadByCURP(): " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Asociar dispositivo a unidad
     * MÉTODO REQUERIDO POR DIAGRAMA UML: +associateDispositivo(int unidadId, string tipo, int dispositivoId) bool
     * @param int $unidadId ID de la unidad
     * @param string $tipo Tipo de dispositivo ('tag' o 'engomado')
     * @param int $dispositivoId ID del dispositivo
     * @return bool True si se asoció correctamente
     */
    public function associateDispositivo(int $unidadId, string $tipo, int $dispositivoId): bool
    {
        try {
            // Validar que la unidad existe
            if (!$this->findById($unidadId)) {
                $this->logError("Unidad no encontrada para asociar dispositivo: ID $unidadId");
                return false;
            }
            
            // Validar tipo de dispositivo
            if (!$this->validateTipoDispositivo($tipo)) {
                $this->logError("Tipo de dispositivo inválido: $tipo");
                return false;
            }
            
            // Verificar que no existe la asociación
            $checkSql = "SELECT COUNT(*) FROM persona_dispositivo WHERE id_persona_unidad = :unidad_id AND tipo_dispositivo = :tipo AND id_dispositivo = :dispositivo_id";
            $checkStmt = $this->connection->prepare($checkSql);
            $checkStmt->execute([
                'unidad_id' => $unidadId,
                'tipo' => $tipo,
                'dispositivo_id' => $dispositivoId
            ]);
            
            if ($checkStmt->fetchColumn() > 0) {
                $this->logError("Asociación ya existe: Unidad $unidadId, Tipo $tipo, Dispositivo $dispositivoId");
                return false;
            }
            
            // Crear asociación
            $sql = "INSERT INTO persona_dispositivo (id_persona_unidad, tipo_dispositivo, id_dispositivo) VALUES (:unidad_id, :tipo, :dispositivo_id)";
            $stmt = $this->connection->prepare($sql);
            
            if ($stmt->execute([
                'unidad_id' => $unidadId,
                'tipo' => $tipo,
                'dispositivo_id' => $dispositivoId
            ])) {
                $this->logError("Dispositivo asociado exitosamente: Unidad $unidadId, Tipo $tipo, Dispositivo $dispositivoId");
                return true;
            }
            
            $this->logError("Error asociando dispositivo: Unidad $unidadId, Tipo $tipo, Dispositivo $dispositivoId");
            return false;
            
        } catch (Exception $e) {
            $this->logError("Excepción en associateDispositivo(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener dispositivos asociados a una unidad
     * MÉTODO REQUERIDO POR DIAGRAMA UML: +getDispositivosByUnidad(int unidadId) array
     * @param int $unidadId ID de la unidad
     * @return array Lista de dispositivos asociados
     */
    public function getDispositivosByUnidad(int $unidadId): array
    {
        try {
            $sql = "SELECT * FROM persona_dispositivo WHERE id_persona_unidad = :unidad_id ORDER BY creado_en DESC";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':unidad_id', $unidadId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return [];
            
        } catch (Exception $e) {
            $this->logError("Excepción en getDispositivosByUnidad(): " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Validar unicidad de CURP
     * MÉTODO REQUERIDO POR DIAGRAMA UML: +validateCURPUnique(string curp) bool
     * @param string $curp CURP a validar
     * @return bool True si el CURP es único
     */
    public function validateCURPUnique(string $curp): bool
    {
        try {
            // Buscar CURP existente
            $existingUnidad = $this->findUnidadByCURP($curp);
            return $existingUnidad === null;
            
        } catch (Exception $e) {
            $this->logError("Excepción en validateCURPUnique(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validar tipo de dispositivo
     * MÉTODO REQUERIDO POR DIAGRAMA UML: +validateTipoDispositivo(string tipo) bool
     * @param string $tipo Tipo de dispositivo
     * @return bool True si el tipo es válido
     */
    public function validateTipoDispositivo(string $tipo): bool
    {
        return in_array($tipo, $this->validTiposDispositivo);
    }
    
    // ==========================================
    // MÉTODOS DE ENCRIPTACIÓN ESPECÍFICOS
    // ==========================================
    
    /**
     * Encriptar campos sensibles antes de guardar
     * SEGÚN COLECCIÓN_VARIABLES_ENCRIPTACIÓN: 7 campos AES
     * @param array $data Datos originales
     * @return array Datos con campos sensibles encriptados
     */
    private function encryptSensitiveFields(array $data): array
    {
        $encryptedData = [];
        
        foreach ($data as $field => $value) {
            if (in_array($field, $this->encryptedFields) && $value !== null && $value !== '') {
                // Encriptar campo sensible
                $encryptedData[$field] = $this->crypto->encryptData((string)$value);
            } else {
                // Campo no sensible, mantener tal como está
                $encryptedData[$field] = $value;
            }
        }
        
        return $encryptedData;
    }
    
    /**
     * Desencriptar campos sensibles después de leer
     * SEGÚN COLECCIÓN_VARIABLES_ENCRIPTACIÓN: 7 campos AES
     * @param array $data Datos encriptados de la BD
     * @return array Datos con campos sensibles desencriptados
     */
    private function decryptSensitiveFields(array $data): array
    {
        $decryptedData = [];
        
        foreach ($data as $field => $value) {
            if (in_array($field, $this->encryptedFields) && $value !== null && $value !== '') {
                try {
                    // Desencriptar campo sensible
                    $decryptedData[$field] = $this->crypto->decryptData($value);
                } catch (Exception $e) {
                    $this->logError("Error desencriptando campo $field: " . $e->getMessage());
                    $decryptedData[$field] = '[ERROR DESENCRIPTACIÓN]';
                }
            } else {
                // Campo no sensible, mantener tal como está
                $decryptedData[$field] = $value;
            }
        }
        
        return $decryptedData;
    }
    
    // ==========================================
    // MÉTODOS ADICIONALES DE CONSULTA
    // ==========================================
    
    /**
     * Obtener unidades con dispositivos asociados
     * @param int $limit Límite de resultados
     * @return array Unidades con sus dispositivos
     */
    public function getUnidadesWithDispositivos(int $limit = 50): array
    {
        try {
            $sql = "
                SELECT 
                    pu.*,
                    pd.id_persona_dispositivo,
                    pd.tipo_dispositivo,
                    pd.id_dispositivo,
                    pd.creado_en as dispositivo_creado_en
                FROM {$this->table} pu
                LEFT JOIN persona_dispositivo pd ON pu.id_persona_unidad = pd.id_persona_unidad
                ORDER BY pu.creado_en DESC, pd.creado_en DESC
                LIMIT :limit
            ";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Agrupar por unidad y desencriptar
                $groupedResults = [];
                foreach ($results as $row) {
                    $unidadId = $row['id_persona_unidad'];
                    
                    if (!isset($groupedResults[$unidadId])) {
                        // Extraer datos de la unidad
                        $unidadData = [];
                        foreach ($row as $key => $value) {
                            if (!in_array($key, ['id_persona_dispositivo', 'tipo_dispositivo', 'id_dispositivo', 'dispositivo_creado_en'])) {
                                $unidadData[$key] = $value;
                            }
                        }
                        
                        $groupedResults[$unidadId] = [
                            'unidad' => $this->decryptSensitiveFields($unidadData),
                            'dispositivos' => []
                        ];
                    }
                    
                    // Agregar dispositivo si existe
                    if ($row['id_persona_dispositivo']) {
                        $groupedResults[$unidadId]['dispositivos'][] = [
                            'id_persona_dispositivo' => $row['id_persona_dispositivo'],
                            'tipo_dispositivo' => $row['tipo_dispositivo'],
                            'id_dispositivo' => $row['id_dispositivo'],
                            'creado_en' => $row['dispositivo_creado_en']
                        ];
                    }
                }
                
                return array_values($groupedResults);
            }
            
            return [];
            
        } catch (Exception $e) {
            $this->logError("Excepción en getUnidadesWithDispositivos(): " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar unidades por nombre (búsqueda parcial)
     * @param string $nombre Nombre a buscar
     * @return array Unidades que coinciden
     */
    public function searchByNombre(string $nombre): array
    {
        try {
            // Como los nombres están encriptados, necesitamos buscar en todos
            $allUnidades = $this->findAll(1000); // Límite alto para búsqueda
            $matches = [];
            
            $nombreLower = strtolower($nombre);
            
            foreach ($allUnidades as $unidad) {
                $nombreCompleto = strtolower($unidad['nombres'] . ' ' . $unidad['apellido1'] . ' ' . ($unidad['apellido2'] ?? ''));
                
                if (strpos($nombreCompleto, $nombreLower) !== false) {
                    $matches[] = $unidad;
                }
            }
            
            return $matches;
            
        } catch (Exception $e) {
            $this->logError("Excepción en searchByNombre(): " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Remover asociación de dispositivo
     * @param int $unidadId ID de la unidad
     * @param string $tipo Tipo de dispositivo
     * @param int $dispositivoId ID del dispositivo
     * @return bool True si se removió correctamente
     */
    public function removeDispositivoAssociation(int $unidadId, string $tipo, int $dispositivoId): bool
    {
        try {
            $sql = "DELETE FROM persona_dispositivo WHERE id_persona_unidad = :unidad_id AND tipo_dispositivo = :tipo AND id_dispositivo = :dispositivo_id";
            $stmt = $this->connection->prepare($sql);
            
            if ($stmt->execute([
                'unidad_id' => $unidadId,
                'tipo' => $tipo,
                'dispositivo_id' => $dispositivoId
            ])) {
                $this->logError("Asociación de dispositivo removida: Unidad $unidadId, Tipo $tipo, Dispositivo $dispositivoId");
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            $this->logError("Excepción en removeDispositivoAssociation(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Contar total de unidades
     * @return int Total de unidades en el sistema
     */
    public function countUnidades(): int
    {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table}";
            $stmt = $this->connection->prepare($sql);
            
            if ($stmt->execute()) {
                return (int)$stmt->fetchColumn();
            }
            
            return 0;
            
        } catch (Exception $e) {
            $this->logError("Excepción en countUnidades(): " . $e->getMessage());
            return 0;
        }
    }
}
?>
