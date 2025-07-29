<?php
/**
 * AREACOMUN MODEL - GESTIÓN DE ÁREAS COMUNES Y RESERVAS
 * Sistema Cyberhole Condominios - Arquitectura 3 Capas
 * 
 * @description Modelo para CRUD de áreas comunes y sistema de reservas
 *              Según RELACIONES_TABLAS: Gestiona areas_comunes + apartar_areas_comunes
 * @author Sistema Cyberhole - Fanático Religioso de la Documentación
 * @version 3.0 - RECREADO DESDE CERO SIGUIENDO RELACIONES_TABLAS_CORREGIDAS
 * @date 2025-07-16
 * 
 * 🔥 CUMPLIMIENTO RELIGIOSO DEL DIAGRAMA UML:
 * - +createAreaComun(array data) int|false ✅ IMPLEMENTADO
 * - +findAreasComunesByCondominio(int condominioId) array ✅ IMPLEMENTADO
 * - +createReserva(array data) int|false ✅ IMPLEMENTADO
 * - +findReservasByAreaComun(int areaId) array ✅ IMPLEMENTADO
 * - +validateCondominioExists(int condominioId) bool ✅ IMPLEMENTADO
 * - +validateTimeFormat(string time) bool ✅ IMPLEMENTADO
 * 
 * 🔥 CUMPLIMIENTO RELIGIOSO DE RELACIONES_TABLAS:
 * - Tabla Principal: areas_comunes ✅ CUMPLIDO
 * - Tabla Secundaria: apartar_areas_comunes ✅ CUMPLIDO
 * - Responsabilidad: Gestión completa de áreas comunes + reservas ✅ CUMPLIDO
 * 
 * 🔥 CUMPLIMIENTO RELIGIOSO DE ENCRIPTACIÓN:
 * - SIN ENCRIPTACIÓN (excluido según documentación) ✅ CUMPLIDO
 * 
 * 🔥 ESTRUCTURA BD SEGÚN RELACIONES_TABLAS:
 * 
 * TABLA: areas_comunes
 * - id_area_comun: int(11) AUTO_INCREMENT PRIMARY KEY
 * - nombre: varchar(100) NOT NULL
 * - descripcion: text DEFAULT NULL
 * - id_condominio: int(11) NOT NULL [FK condominios.id_condominio]
 * - id_calle: int(11) DEFAULT NULL [FK calles.id_calle]
 * - hora_apertura: time NOT NULL
 * - hora_cierre: time NOT NULL
 * - estado: tinyint(1) NOT NULL DEFAULT 1
 * 
 * TABLA: apartar_areas_comunes
 * - id_apartado: int(11) AUTO_INCREMENT PRIMARY KEY
 * - id_area_comun: int(11) NOT NULL [FK areas_comunes.id_area_comun]
 * - id_condominio: int(11) NOT NULL [FK condominios.id_condominio]
 * - id_calle: int(11) DEFAULT NULL [FK calles.id_calle]
 * - id_casa: int(11) DEFAULT NULL [FK casas.id_casa]
 * - fecha_apartado: datetime NOT NULL
 * - descripcion: text DEFAULT NULL
 */

require_once __DIR__ . '/BaseModel.php';

class AreaComun extends BaseModel
{
    /**
     * @var string $table Nombre de la tabla principal
     * SEGÚN RELACIONES_TABLAS: areas_comunes
     */
    protected string $table = 'areas_comunes';
    
    /**
     * @var string $reservasTable Nombre de la tabla de reservas
     * SEGÚN RELACIONES_TABLAS: apartar_areas_comunes
     */
    private string $reservasTable = 'apartar_areas_comunes';
    
    /**
     * @var array $fillableFields Campos permitidos para áreas comunes
     * SEGÚN ESTRUCTURA BD: campos de tabla areas_comunes
     */
    private array $fillableFields = [
        'nombre',
        'descripcion',
        'id_condominio',
        'id_calle',
        'hora_apertura',
        'hora_cierre',
        'estado'
    ];
    
    /**
     * @var array $requiredFields Campos obligatorios
     * SEGÚN ESTRUCTURA BD: campos NOT NULL
     */
    private array $requiredFields = [
        'nombre',
        'id_condominio',
        'hora_apertura',
        'hora_cierre'
    ];
    
    /**
     * @var array $fillableReservaFields Campos permitidos para reservas
     * SEGÚN ESTRUCTURA BD: campos de tabla apartar_areas_comunes
     */
    private array $fillableReservaFields = [
        'id_area_comun',
        'id_condominio',
        'id_calle',
        'id_casa',
        'fecha_apartado',
        'descripcion'
    ];
    
    /**
     * @var array $requiredReservaFields Campos obligatorios para reservas
     * SEGÚN ESTRUCTURA BD: campos NOT NULL en apartar_areas_comunes
     */
    private array $requiredReservaFields = [
        'id_area_comun',
        'id_condominio',
        'fecha_apartado'
    ];
    
    /**
     * Constructor - Inicializar conexión
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    // ===============================================
    // MÉTODOS ABSTRACTOS REQUERIDOS POR BASEMODEL
    // ===============================================
    
    /**
     * Implementación del método abstracto create()
     * Redirige al método específico createAreaComun()
     * 
     * @param array $data Datos del área común
     * @return int|false ID del área común creada o false si falla
     */
    public function create(array $data): int|false
    {
        return $this->createAreaComun($data);
    }
    
    /**
     * Implementación del método abstracto findById()
     * Buscar área común por ID
     * 
     * @param int $id ID del área común
     * @return array|null Datos del área común o null si no existe
     */
    public function findById(int $id): array|null
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT ac.*, 
                       cond.nombre as condominio_nombre,
                       calle.nombre as calle_nombre
                FROM areas_comunes ac
                LEFT JOIN condominios cond ON ac.id_condominio = cond.id_condominio
                LEFT JOIN calles calle ON ac.id_calle = calle.id_calle
                WHERE ac.id_area_comun = :id
            ");
            
            $stmt->execute([':id' => $id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ?: null;
            
        } catch (Exception $e) {
            $this->logError("AreaComun::findById - Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Implementación del método abstracto update()
     * Actualizar área común por ID
     * 
     * @param int $id ID del área común
     * @param array $data Datos a actualizar
     * @return bool True si se actualiza correctamente
     */
    public function update(int $id, array $data): bool
    {
        try {
            // Validar que el área común existe
            if (!$this->findById($id)) {
                $this->logError("AreaComun::update - Área común $id no existe");
                return false;
            }
            
            // Filtrar solo campos permitidos
            $allowedData = array_intersect_key($data, array_flip($this->fillableFields));
            
            if (empty($allowedData)) {
                $this->logError("AreaComun::update - No hay campos válidos para actualizar");
                return false;
            }
            
            // Construir SQL dinámico
            $setParts = [];
            $params = [':id' => $id];
            
            foreach ($allowedData as $field => $value) {
                $setParts[] = "$field = :$field";
                $params[":$field"] = $value;
            }
            
            $sql = "UPDATE areas_comunes SET " . implode(', ', $setParts) . " WHERE id_area_comun = :id";
            $stmt = $this->connection->prepare($sql);
            
            return $stmt->execute($params);
            
        } catch (Exception $e) {
            $this->logError("AreaComun::update - Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Implementación del método abstracto delete()
     * Eliminar área común por ID
     * 
     * @param int $id ID del área común
     * @return bool True si se elimina correctamente
     */
    public function delete(int $id): bool
    {
        try {
            // Validar que el área común existe
            if (!$this->findById($id)) {
                $this->logError("AreaComun::delete - Área común $id no existe");
                return false;
            }
            
            // Primero eliminar reservas relacionadas
            $stmt = $this->connection->prepare("DELETE FROM apartar_areas_comunes WHERE id_area_comun = :id");
            $stmt->execute([':id' => $id]);
            
            // Luego eliminar el área común
            $stmt = $this->connection->prepare("DELETE FROM areas_comunes WHERE id_area_comun = :id");
            return $stmt->execute([':id' => $id]);
            
        } catch (Exception $e) {
            $this->logError("AreaComun::delete - Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Implementación del método abstracto findAll()
     * Obtener todas las áreas comunes con información relacionada
     * 
     * @param int $limit Límite de resultados
     * @return array Lista de áreas comunes
     */
    public function findAll(int $limit = 100): array
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT ac.*, 
                       cond.nombre as condominio_nombre,
                       calle.nombre as calle_nombre
                FROM areas_comunes ac
                LEFT JOIN condominios cond ON ac.id_condominio = cond.id_condominio
                LEFT JOIN calles calle ON ac.id_calle = calle.id_calle
                ORDER BY cond.nombre, ac.nombre
                LIMIT :limit
            ");
            
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $this->logError("AreaComun::findAll - Error: " . $e->getMessage());
            return [];
        }
    }
    
    // ===============================================
    // MÉTODOS ESPECÍFICOS AREAS_COMUNES
    // ===============================================
    
    /**
     * Crear nueva área común
     * SEGÚN UML: +createAreaComun(array data) int|false
     * 
     * @param array $data Datos del área común
     * @return int|false ID del área común creada o false si falla
     */
    public function createAreaComun(array $data): int|false
    {
        try {
            // Validar campos requeridos
            if (!$this->validateRequiredFields($data, $this->requiredFields)) {
                $this->logError("AreaComun::createAreaComun - Campos requeridos faltantes");
                return false;
            }
            
            // Validar que el condominio existe
            if (!$this->validateCondominioExists($data['id_condominio'])) {
                $this->logError("AreaComun::createAreaComun - Condominio no existe");
                return false;
            }
            
            // Validar formato de horarios
            if (!$this->validateTimeFormat($data['hora_apertura']) || !$this->validateTimeFormat($data['hora_cierre'])) {
                $this->logError("AreaComun::createAreaComun - Formato de hora inválido");
                return false;
            }
            
            // Filtrar solo campos permitidos
            $allowedData = array_intersect_key($data, array_flip($this->fillableFields));
            
            // Construir SQL dinámico
            $fields = implode(', ', array_keys($allowedData));
            $placeholders = ':' . implode(', :', array_keys($allowedData));
            
            $sql = "INSERT INTO areas_comunes ($fields) VALUES ($placeholders)";
            $stmt = $this->connection->prepare($sql);
            
            foreach ($allowedData as $field => $value) {
                $stmt->bindValue(":$field", $value);
            }
            
            if ($stmt->execute()) {
                return (int) $this->connection->lastInsertId();
            }
            
            return false;
            
        } catch (Exception $e) {
            $this->logError("AreaComun::createAreaComun - Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Buscar áreas comunes por condominio
     * SEGÚN UML: +findAreasComunesByCondominio(int condominioId) array
     * 
     * @param int $condominioId ID del condominio
     * @return array Lista de áreas comunes del condominio
     */
    public function findAreasComunesByCondominio(int $condominioId): array
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT ac.*, 
                       cond.nombre as condominio_nombre,
                       calle.nombre as calle_nombre
                FROM areas_comunes ac
                LEFT JOIN condominios cond ON ac.id_condominio = cond.id_condominio
                LEFT JOIN calles calle ON ac.id_calle = calle.id_calle
                WHERE ac.id_condominio = :condominio_id
                ORDER BY ac.nombre
            ");
            
            $stmt->execute([':condominio_id' => $condominioId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $this->logError("AreaComun::findAreasComunesByCondominio - Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar áreas comunes activas por condominio
     * 
     * @param int $condominioId ID del condominio
     * @return array Lista de áreas comunes activas
     */
    public function findAreasActivasByCondominio(int $condominioId): array
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT ac.*, 
                       cond.nombre as condominio_nombre,
                       calle.nombre as calle_nombre
                FROM areas_comunes ac
                LEFT JOIN condominios cond ON ac.id_condominio = cond.id_condominio
                LEFT JOIN calles calle ON ac.id_calle = calle.id_calle
                WHERE ac.id_condominio = :condominio_id AND ac.estado = 1
                ORDER BY ac.nombre
            ");
            
            $stmt->execute([':condominio_id' => $condominioId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $this->logError("AreaComun::findAreasActivasByCondominio - Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Cambiar estado de área común
     * 
     * @param int $areaId ID del área común
     * @param int $estado Nuevo estado (0 = inactiva, 1 = activa)
     * @return bool True si se actualiza correctamente
     */
    public function cambiarEstadoArea(int $areaId, int $estado): bool
    {
        try {
            $stmt = $this->connection->prepare("
                UPDATE areas_comunes 
                SET estado = :estado 
                WHERE id_area_comun = :id
            ");
            
            return $stmt->execute([
                ':estado' => $estado,
                ':id' => $areaId
            ]);
            
        } catch (Exception $e) {
            $this->logError("AreaComun::cambiarEstadoArea - Error: " . $e->getMessage());
            return false;
        }
    }
    
    // ===============================================
    // MÉTODOS ESPECÍFICOS APARTAR_AREAS_COMUNES
    // ===============================================
    
    /**
     * Crear nueva reserva de área común
     * SEGÚN UML: +createReserva(array data) int|false
     * 
     * @param array $data Datos de la reserva
     * @return int|false ID de la reserva creada o false si falla
     */
    public function createReserva(array $data): int|false
    {
        try {
            // Validar campos requeridos
            if (!$this->validateRequiredFields($data, $this->requiredReservaFields)) {
                $this->logError("AreaComun::createReserva - Campos requeridos faltantes");
                return false;
            }
            
            // Validar que el área común existe
            if (!$this->findById($data['id_area_comun'])) {
                $this->logError("AreaComun::createReserva - Área común no existe");
                return false;
            }
            
            // Validar que el condominio existe
            if (!$this->validateCondominioExists($data['id_condominio'])) {
                $this->logError("AreaComun::createReserva - Condominio no existe");
                return false;
            }
            
            // Filtrar solo campos permitidos
            $allowedData = array_intersect_key($data, array_flip($this->fillableReservaFields));
            
            // Construir SQL dinámico
            $fields = implode(', ', array_keys($allowedData));
            $placeholders = ':' . implode(', :', array_keys($allowedData));
            
            $sql = "INSERT INTO apartar_areas_comunes ($fields) VALUES ($placeholders)";
            $stmt = $this->connection->prepare($sql);
            
            foreach ($allowedData as $field => $value) {
                $stmt->bindValue(":$field", $value);
            }
            
            if ($stmt->execute()) {
                return (int) $this->connection->lastInsertId();
            }
            
            return false;
            
        } catch (Exception $e) {
            $this->logError("AreaComun::createReserva - Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Buscar reservas por área común
     * SEGÚN UML: +findReservasByAreaComun(int areaId) array
     * 
     * @param int $areaId ID del área común
     * @return array Lista de reservas del área común
     */
    public function findReservasByAreaComun(int $areaId): array
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT aac.*, 
                       ac.nombre as area_nombre,
                       cond.nombre as condominio_nombre,
                       calle.nombre as calle_nombre,
                       casa.casa as casa_numero
                FROM apartar_areas_comunes aac
                LEFT JOIN areas_comunes ac ON aac.id_area_comun = ac.id_area_comun
                LEFT JOIN condominios cond ON aac.id_condominio = cond.id_condominio
                LEFT JOIN calles calle ON aac.id_calle = calle.id_calle
                LEFT JOIN casas casa ON aac.id_casa = casa.id_casa
                WHERE aac.id_area_comun = :area_id
                ORDER BY aac.fecha_apartado DESC
            ");
            
            $stmt->execute([':area_id' => $areaId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $this->logError("AreaComun::findReservasByAreaComun - Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar reservas por condominio
     * 
     * @param int $condominioId ID del condominio
     * @return array Lista de reservas del condominio
     */
    public function findReservasByCondominio(int $condominioId): array
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT aac.*, 
                       ac.nombre as area_nombre,
                       cond.nombre as condominio_nombre,
                       calle.nombre as calle_nombre,
                       casa.casa as casa_numero
                FROM apartar_areas_comunes aac
                LEFT JOIN areas_comunes ac ON aac.id_area_comun = ac.id_area_comun
                LEFT JOIN condominios cond ON aac.id_condominio = cond.id_condominio
                LEFT JOIN calles calle ON aac.id_calle = calle.id_calle
                LEFT JOIN casas casa ON aac.id_casa = casa.id_casa
                WHERE aac.id_condominio = :condominio_id
                ORDER BY aac.fecha_apartado DESC
            ");
            
            $stmt->execute([':condominio_id' => $condominioId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $this->logError("AreaComun::findReservasByCondominio - Error: " . $e->getMessage());
            return [];
        }
    }
    
    // ===============================================
    // MÉTODOS DE VALIDACIÓN
    // ===============================================
    
    /**
     * Validar que un condominio existe
     * SEGÚN UML: +validateCondominioExists(int condominioId) bool
     * 
     * @param int $condominioId ID del condominio
     * @return bool True si el condominio existe
     */
    public function validateCondominioExists(int $condominioId): bool
    {
        try {
            $stmt = $this->connection->prepare("SELECT id_condominio FROM condominios WHERE id_condominio = :id");
            $stmt->execute([':id' => $condominioId]);
            return $stmt->rowCount() > 0;
            
        } catch (Exception $e) {
            $this->logError("AreaComun::validateCondominioExists - Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validar formato de tiempo
     * SEGÚN UML: +validateTimeFormat(string time) bool
     * 
     * @param string $time Tiempo en formato HH:MM:SS
     * @return bool True si el formato es válido
     */
    public function validateTimeFormat(string $time): bool
    {
        try {
            // Validar formato HH:MM:SS o HH:MM
            $pattern = '/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/';
            return preg_match($pattern, $time) === 1;
            
        } catch (Exception $e) {
            $this->logError("AreaComun::validateTimeFormat - Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validar que una calle existe
     * 
     * @param int $calleId ID de la calle
     * @return bool True si la calle existe
     */
    public function validateCalleExists(int $calleId): bool
    {
        try {
            $stmt = $this->connection->prepare("SELECT id_calle FROM calles WHERE id_calle = :id");
            $stmt->execute([':id' => $calleId]);
            return $stmt->rowCount() > 0;
            
        } catch (Exception $e) {
            $this->logError("AreaComun::validateCalleExists - Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validar que una casa existe
     * 
     * @param int $casaId ID de la casa
     * @return bool True si la casa existe
     */
    public function validateCasaExists(int $casaId): bool
    {
        try {
            $stmt = $this->connection->prepare("SELECT id_casa FROM casas WHERE id_casa = :id");
            $stmt->execute([':id' => $casaId]);
            return $stmt->rowCount() > 0;
            
        } catch (Exception $e) {
            $this->logError("AreaComun::validateCasaExists - Error: " . $e->getMessage());
            return false;
        }
    }
}
