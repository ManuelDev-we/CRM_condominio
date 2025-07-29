<?php
/**
 * MODELO CALLE - SISTEMA CYBERHOLE CONDOMINIOS
 * Arquitectura 3 Capas - Capa de Datos
 * 
 * @description Modelo para gestión de calles dentro de condominios
 * @table calles
 * @version 2.0 - CORREGIDO SEGÚN DOCUMENTACIÓN UML
 * @date 2025-07-15
 * 
 * ESTRUCTURA SEGÚN DOCUMENTACIÓN:
 * - Tabla: calles (NO ENCRIPTAR según COLECCION_VARIABLES_ENCRIPTACION.md)
 * - Segundo nivel de jerarquía física (según RELACIONES_TABLAS)
 * - Conecta condominios con casas
 * - Métodos UML requeridos implementados
 */

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/BaseModel.php';

class Calle extends BaseModel {
    
    /**
     * @var string Nombre de la tabla (según RELACIONES_TABLAS_CORREGIDO.md)
     */
    protected string $table = 'calles';
    
    /**
     * @var array Campos requeridos para crear una calle
     */
    private $requiredFields = ['nombre', 'id_condominio'];
    
    /**
     * @var array Campos opcionales
     */
    private $optionalFields = ['descripcion'];
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }
    
    // ==========================================
    // MÉTODOS CRUD BÁSICOS (ABSTRACTOS DE BASEMODEL)
    // ==========================================
    
    /**
     * Crear nueva calle
     * @param array $data Datos de la calle
     * @return int|false ID de la calle creada o false en caso de error
     */
    public function create(array $data): int|false {
        try {
            // Validar campos requeridos
            if (!$this->validateRequiredFields($data, $this->requiredFields)) {
                $this->logError("Campos requeridos faltantes para crear calle");
                return false;
            }
            
            // Validar que el condominio existe
            if (!$this->validateCondominioExists($data['id_condominio'])) {
                $this->logError("Condominio no existe: " . $data['id_condominio']);
                return false;
            }
            
            // Validar unicidad del nombre en el condominio
            if (!$this->validateNameUniqueInCondominio($data['nombre'], $data['id_condominio'])) {
                $this->logError("Ya existe una calle con ese nombre en el condominio");
                return false;
            }
            
            // Sanitizar datos
            $nombre = $this->sanitizeInput($data['nombre']);
            $id_condominio = (int) $data['id_condominio'];
            $descripcion = isset($data['descripcion']) ? $this->sanitizeInput($data['descripcion']) : null;
            
            // Preparar consulta
            $sql = "INSERT INTO calles (nombre, id_condominio, descripcion) VALUES (?, ?, ?)";
            $stmt = $this->connection->prepare($sql);
            
            if ($stmt->execute([$nombre, $id_condominio, $descripcion])) {
                $id = $this->connection->lastInsertId();
                error_log("Calle creada exitosamente con ID: $id");
                return (int) $id;
            }
            
            return false;
            
        } catch (Exception $e) {
            $this->logError("Error al crear calle: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Buscar calle por ID
     * @param int $id ID de la calle
     * @return array|null Datos de la calle o null si no existe
     */
    public function findById(int $id): array|null {
        try {
            $sql = "SELECT c.*, co.nombre as condominio_nombre 
                    FROM calles c 
                    LEFT JOIN condominios co ON c.id_condominio = co.id_condominio 
                    WHERE c.id_calle = ?";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$id]);
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ?: null;
            
        } catch (Exception $e) {
            $this->logError("Error al buscar calle por ID $id: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Actualizar calle
     * @param int $id ID de la calle
     * @param array $data Datos a actualizar
     * @return bool true si se actualizó correctamente
     */
    public function update(int $id, array $data): bool {
        try {
            // Verificar que la calle existe
            if (!$this->findById($id)) {
                $this->logError("Calle no encontrada para actualizar: $id");
                return false;
            }
            
            $updateFields = [];
            $updateValues = [];
            
            // Campos actualizables
            if (isset($data['nombre'])) {
                // Validar unicidad del nuevo nombre
                $calleActual = $this->findById($id);
                if ($data['nombre'] !== $calleActual['nombre']) {
                    if (!$this->validateNameUniqueInCondominio($data['nombre'], $calleActual['id_condominio'])) {
                        $this->logError("Ya existe una calle con ese nombre en el condominio");
                        return false;
                    }
                }
                $updateFields[] = 'nombre = ?';
                $updateValues[] = $this->sanitizeInput($data['nombre']);
            }
            
            if (isset($data['descripcion'])) {
                $updateFields[] = 'descripcion = ?';
                $updateValues[] = $this->sanitizeInput($data['descripcion']);
            }
            
            if (isset($data['id_condominio'])) {
                if (!$this->validateCondominioExists($data['id_condominio'])) {
                    $this->logError("Condominio no existe: " . $data['id_condominio']);
                    return false;
                }
                $updateFields[] = 'id_condominio = ?';
                $updateValues[] = (int) $data['id_condominio'];
            }
            
            if (empty($updateFields)) {
                $this->logError("No hay campos para actualizar");
                return false;
            }
            
            $updateValues[] = $id;
            $sql = "UPDATE calles SET " . implode(', ', $updateFields) . " WHERE id_calle = ?";
            
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->execute($updateValues);
            
            if ($result) {
                error_log("Calle actualizada exitosamente: ID $id");
            }
            
            return $result;
            
        } catch (Exception $e) {
            $this->logError("Error al actualizar calle $id: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar calle
     * @param int $id ID de la calle
     * @return bool true si se eliminó correctamente
     */
    public function delete(int $id): bool {
        try {
            // Verificar que la calle existe
            if (!$this->findById($id)) {
                $this->logError("Calle no encontrada para eliminar: $id");
                return false;
            }
            
            // Verificar si tiene casas asociadas
            $casasCount = $this->contarCasasEnCalle($id);
            if ($casasCount > 0) {
                $this->logError("No se puede eliminar la calle $id porque tiene $casasCount casas asociadas");
                return false;
            }
            
            $sql = "DELETE FROM calles WHERE id_calle = ?";
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->execute([$id]);
            
            if ($result) {
                error_log("Calle eliminada exitosamente: ID $id");
            }
            
            return $result;
            
        } catch (Exception $e) {
            $this->logError("Error al eliminar calle $id: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener todas las calles
     * @param int $limit Límite de resultados (por defecto 100)
     * @return array Array de todas las calles
     */
    public function findAll(int $limit = 100): array {
        try {
            $sql = "SELECT c.*, co.nombre as condominio_nombre 
                    FROM calles c 
                    LEFT JOIN condominios co ON c.id_condominio = co.id_condominio 
                    ORDER BY co.nombre, c.nombre
                    LIMIT ?";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$limit]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $this->logError("Error al obtener todas las calles: " . $e->getMessage());
            return [];
        }
    }
    
    // ==========================================
    // MÉTODOS ESPECÍFICOS SEGÚN UML (DIAGRAMA_CLASES_CORREGIDO.md)
    // ==========================================
    
    /**
     * Buscar calles por ID de condominio (REQUERIDO UML)
     * @param int $condominioId ID del condominio
     * @return array Array de calles del condominio
     */
    public function findByCondominioId(int $condominioId) {
        try {
            $sql = "SELECT c.*, co.nombre as condominio_nombre 
                    FROM calles c 
                    LEFT JOIN condominios co ON c.id_condominio = co.id_condominio 
                    WHERE c.id_condominio = ? 
                    ORDER BY c.nombre";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$condominioId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $this->logError("Error al buscar calles por condominio $condominioId: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Validar que existe un condominio (REQUERIDO UML)
     * @param int $condominioId ID del condominio
     * @return bool true si el condominio existe
     */
    public function validateCondominioExists(int $condominioId) {
        try {
            $sql = "SELECT COUNT(*) FROM condominios WHERE id_condominio = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$condominioId]);
            
            return $stmt->fetchColumn() > 0;
            
        } catch (Exception $e) {
            $this->logError("Error al validar condominio $condominioId: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validar que el nombre es único en el condominio (REQUERIDO UML)
     * @param string $nombre Nombre de la calle
     * @param int $condominioId ID del condominio
     * @param int|null $excludeId ID de calle a excluir (para updates)
     * @return bool true si el nombre es único
     */
    public function validateNameUniqueInCondominio(string $nombre, int $condominioId, int $excludeId = null) {
        try {
            $sql = "SELECT COUNT(*) FROM calles WHERE nombre = ? AND id_condominio = ?";
            $params = [$nombre, $condominioId];
            
            if ($excludeId !== null) {
                $sql .= " AND id_calle != ?";
                $params[] = $excludeId;
            }
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchColumn() == 0;
            
        } catch (Exception $e) {
            $this->logError("Error al validar unicidad de nombre '$nombre' en condominio $condominioId: " . $e->getMessage());
            return false;
        }
    }
    
    // ==========================================
    // MÉTODOS ADICIONALES DE UTILIDAD
    // ==========================================
    
    /**
     * Crear calle con validaciones completas
     * @param array $data Datos de la calle
     * @return int|false ID de la calle creada o false
     */
    public function createCalle(array $data) {
        return $this->create($data);
    }
    
    /**
     * Actualizar calle con validaciones
     * @param int $id ID de la calle
     * @param array $data Datos a actualizar
     * @return bool true si se actualizó
     */
    public function updateCalle(int $id, array $data) {
        return $this->update($id, $data);
    }
    
    /**
     * Obtener todas las calles activas
     * @return array Array de calles activas
     */
    public function getAllCallesActivas() {
        // Como la tabla calles no tiene campo activo según la documentación,
        // retornamos todas las calles
        return $this->findAll();
    }
    
    /**
     * Buscar calle por nombre en un condominio específico
     * @param string $nombre Nombre de la calle
     * @param int $condominioId ID del condominio
     * @return array|null Datos de la calle o null
     */
    public function findByNameInCondominio(string $nombre, int $condominioId) {
        try {
            $sql = "SELECT c.*, co.nombre as condominio_nombre 
                    FROM calles c 
                    LEFT JOIN condominios co ON c.id_condominio = co.id_condominio 
                    WHERE c.nombre = ? AND c.id_condominio = ?";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$nombre, $condominioId]);
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ?: null;
            
        } catch (Exception $e) {
            $this->logError("Error al buscar calle '$nombre' en condominio $condominioId: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Contar casas en una calle
     * @param int $calleId ID de la calle
     * @return int Número de casas
     */
    public function contarCasasEnCalle(int $calleId) {
        try {
            $sql = "SELECT COUNT(*) FROM casas WHERE id_calle = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$calleId]);
            
            return (int) $stmt->fetchColumn();
            
        } catch (Exception $e) {
            $this->logError("Error al contar casas en calle $calleId: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Obtener calles con información de casas
     * @param int|null $condominioId ID del condominio (opcional)
     * @return array Array de calles con conteo de casas
     */
    public function getCallesWithCasaCount(int $condominioId = null) {
        try {
            $sql = "SELECT c.*, co.nombre as condominio_nombre, 
                           COUNT(ca.id_casa) as total_casas
                    FROM calles c 
                    LEFT JOIN condominios co ON c.id_condominio = co.id_condominio 
                    LEFT JOIN casas ca ON c.id_calle = ca.id_calle";
            
            $params = [];
            if ($condominioId !== null) {
                $sql .= " WHERE c.id_condominio = ?";
                $params[] = $condominioId;
            }
            
            $sql .= " GROUP BY c.id_calle ORDER BY co.nombre, c.nombre";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $this->logError("Error al obtener calles con conteo de casas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar calles por patrón de nombre
     * @param string $patron Patrón de búsqueda
     * @param int|null $condominioId ID del condominio (opcional)
     * @return array Array de calles que coinciden
     */
    public function searchByNamePattern(string $patron, int $condominioId = null) {
        try {
            $sql = "SELECT c.*, co.nombre as condominio_nombre 
                    FROM calles c 
                    LEFT JOIN condominios co ON c.id_condominio = co.id_condominio 
                    WHERE c.nombre LIKE ?";
            
            $params = ["%$patron%"];
            
            if ($condominioId !== null) {
                $sql .= " AND c.id_condominio = ?";
                $params[] = $condominioId;
            }
            
            $sql .= " ORDER BY co.nombre, c.nombre";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $this->logError("Error al buscar calles por patrón '$patron': " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener estadísticas de calles por condominio
     * @return array Estadísticas agrupadas por condominio
     */
    public function getStatisticsByCondominio() {
        try {
            $sql = "SELECT co.id_condominio, co.nombre as condominio_nombre,
                           COUNT(c.id_calle) as total_calles,
                           COUNT(ca.id_casa) as total_casas
                    FROM condominios co
                    LEFT JOIN calles c ON co.id_condominio = c.id_condominio
                    LEFT JOIN casas ca ON c.id_calle = ca.id_calle
                    GROUP BY co.id_condominio, co.nombre
                    ORDER BY co.nombre";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $this->logError("Error al obtener estadísticas por condominio: " . $e->getMessage());
            return [];
        }
    }
    
    // ==========================================
    // MÉTODOS DE VALIDACIÓN ADICIONALES
    // ==========================================
    
    /**
     * Validar formato del nombre de calle
     * @param string $nombre Nombre a validar
     * @return bool true si el formato es válido
     */
    public function validateNameFormat(string $nombre) {
        // Nombre debe tener entre 3 y 150 caracteres
        $nombre = trim($nombre);
        return strlen($nombre) >= 3 && strlen($nombre) <= 150;
    }
    
    /**
     * Validar datos completos para crear calle
     * @param array $data Datos a validar
     * @return array Array con 'valid' (bool) y 'errors' (array)
     */
    public function validateCalleData(array $data) {
        $errors = [];
        
        // Validar nombre
        if (empty($data['nombre'])) {
            $errors[] = "El nombre es requerido";
        } elseif (!$this->validateNameFormat($data['nombre'])) {
            $errors[] = "El nombre debe tener entre 3 y 150 caracteres";
        }
        
        // Validar condominio
        if (empty($data['id_condominio'])) {
            $errors[] = "El ID del condominio es requerido";
        } elseif (!is_numeric($data['id_condominio']) || $data['id_condominio'] <= 0) {
            $errors[] = "El ID del condominio debe ser un número positivo";
        } elseif (!$this->validateCondominioExists($data['id_condominio'])) {
            $errors[] = "El condominio especificado no existe";
        }
        
        // Validar unicidad del nombre si ambos campos están presentes
        if (!empty($data['nombre']) && !empty($data['id_condominio']) && is_numeric($data['id_condominio'])) {
            if (!$this->validateNameUniqueInCondominio($data['nombre'], $data['id_condominio'])) {
                $errors[] = "Ya existe una calle con ese nombre en el condominio";
            }
        }
        
        // Validar descripción (opcional)
        if (isset($data['descripcion']) && strlen($data['descripcion']) > 1000) {
            $errors[] = "La descripción no puede exceder 1000 caracteres";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
?>