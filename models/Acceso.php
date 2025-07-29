<?php
/**
 * MODELO ACCESO - SISTEMA DE ACCESOS DIFERENCIADOS POR CONDOMINIO
 * Sistema Cyberhole Condominios - Arquitectura 3 Capas
 * 
 * RESPONSABILIDADES SEGÚN PROMPT MAESTRO:
 * - TABLA PRINCIPAL: accesos_residentes
 * - TABLA SECUNDARIA: accesos_empleados  
 * - TABLA SECUNDARIA: visitantes
 * 
 * ARQUITECTURA 3 CAPAS:
 * - Capa 1 (Esta): Solo CRUD y validaciones básicas de integridad + FILTROS POR CONDOMINIO
 * - Capa 2 (Servicios): Lógica de negocio (preparado para servicios rápidos)
 * - Capa 3 (Controladores): Presentación (pendiente)
 * 
 * 🚨 CONVENCIÓN ACTUAL MANTENIDA (FUNCIONANDO HOY):
 * - PK accesos_empleados: id_acceso ✅ MANTENER
 * - PK accesos_residentes: id_acceso ✅ MANTENER  
 * - PK visitantes: id_visitante ✅ CORRECTO
 * - Campo código empleado: id_acceso_empleado ✅ MANTENER
 * 
 * 💡 CONVENCIÓN SUGERIDA A FUTURO (REFACTORIZACIÓN):
 * - PK accesos_empleados: id_acceso_empleado (más semántico)
 * - PK accesos_residentes: id_acceso_residente (más semántico)
 * - Campo código empleado: id_acceso_empleado_codigo (más claro)
 * 
 * 🎯 FILTROS POR CONDOMINIO IMPLEMENTADOS:
 * - obtenerResidentesPorCondominio(int $id_condominio): array
 * - obtenerEmpleadosPorCondominio(int $id_condominio): array  
 * - obtenerVisitantesPorCondominio(int $id_condominio): array
 * 
 * @author Sistema Cyberhole Condominios - PROMPT MAESTRO
 * @version 1.0 - CREADO DESDE CERO CON FILTROS POR CONDOMINIO
 * @since Julio 2025
 */

require_once __DIR__ . '/BaseModel.php';

class Acceso extends BaseModel 
{
    /**
     * Tabla principal que administra este modelo
     * @var string
     */
    protected string $table = 'accesos_residentes';
    
    /**
     * Tablas secundarias que administra este modelo
     * @var array
     */
    protected array $secondaryTables = [
        'accesos_empleados',
        'visitantes'
    ];
    
    /**
     * Campos requeridos para crear un acceso de residente
     * @var array
     */
    protected array $requiredFieldsResidente = [
        'id_persona',
        'id_condominio', 
        'id_casa',
        'id_persona_dispositivo',
        'tipo_dispositivo'
    ];
    
    /**
     * Campos requeridos para crear un acceso de empleado
     * @var array
     */
    protected array $requiredFieldsEmpleado = [
        'id_empleado',
        'id_condominio',
        'id_acceso_empleado'
    ];
    
    /**
     * Campos requeridos para crear un visitante
     * @var array
     */
    protected array $requiredFieldsVisitante = [
        'nombre',
        'id_condominio',
        'id_casa'
    ];
    
    /**
     * Constructor del modelo Acceso
     */
    public function __construct() 
    {
        parent::__construct();
    }
    
    // ===============================================
    // MÉTODOS ABSTRACTOS OBLIGATORIOS DE BASEMODEL
    // ===============================================
    
    /**
     * Crear nuevo registro en tabla principal (accesos_residentes)
     * 
     * @param array $data Datos del acceso residente
     * @return int|false ID del registro creado o false en error
     */
    public function create(array $data): int|false 
    {
        return $this->registrarAccesoResidente($data);
    }
    
    /**
     * Buscar registro por ID en tabla principal (accesos_residentes)
     * 
     * @param int $id ID del acceso residente
     * @return array|null Datos del acceso o null si no existe
     */
    public function findById(int $id): array|null 
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT ar.*, 
                       p.nombres, p.apellido1, p.apellido2,
                       c.numero as casa_numero,
                       cond.nombre as condominio_nombre
                FROM accesos_residentes ar
                LEFT JOIN personas p ON ar.id_persona = p.id_persona
                LEFT JOIN casas c ON ar.id_casa = c.id_casa
                LEFT JOIN condominios cond ON ar.id_condominio = cond.id_condominio
                WHERE ar.id_acceso = :id
            ");
            
            $stmt->execute(['id' => $id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ?: null;
            
        } catch (Exception $e) {
            $this->logError("Acceso::findById - Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Actualizar registro por ID en tabla principal (accesos_residentes)
     * 
     * @param int $id ID del acceso residente
     * @param array $data Datos a actualizar
     * @return bool True si se actualizó, false en error
     */
    public function update(int $id, array $data): bool 
    {
        try {
            $allowedFields = ['fecha_hora_salida'];
            $updateData = [];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $this->sanitizeInput($data[$field]);
                }
            }
            
            if (empty($updateData)) {
                return false;
            }
            
            $setClause = implode(', ', array_map(fn($field) => "$field = :$field", array_keys($updateData)));
            
            $stmt = $this->connection->prepare("UPDATE accesos_residentes SET $setClause WHERE id_acceso = :id");
            
            $updateData['id'] = $id;
            
            return $stmt->execute($updateData);
            
        } catch (Exception $e) {
            $this->logError("Acceso::update - Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar registro por ID en tabla principal (accesos_residentes)
     * 
     * @param int $id ID del acceso residente
     * @return bool True si se eliminó, false en error
     */
    public function delete(int $id): bool 
    {
        try {
            $stmt = $this->connection->prepare("DELETE FROM accesos_residentes WHERE id_acceso = :id");
            
            return $stmt->execute(['id' => $id]);
            
        } catch (Exception $e) {
            $this->logError("Acceso::delete - Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener todos los registros de tabla principal (accesos_residentes)
     * 
     * @param int $limit Límite de registros
     * @return array Lista de accesos de residentes
     */
    public function findAll(int $limit = 100): array 
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT ar.*, 
                       p.nombres, p.apellido1, p.apellido2,
                       c.numero as casa_numero,
                       cond.nombre as condominio_nombre
                FROM accesos_residentes ar
                LEFT JOIN personas p ON ar.id_persona = p.id_persona
                LEFT JOIN casas c ON ar.id_casa = c.id_casa
                LEFT JOIN condominios cond ON ar.id_condominio = cond.id_condominio
                ORDER BY ar.fecha_hora_entrada DESC
                LIMIT :limit
            ");
            
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $this->logError("Acceso::findAll - Error: " . $e->getMessage());
            return [];
        }
    }
    
    // ===============================================
    // MÉTODOS PRINCIPALES REQUERIDOS POR PROMPT MAESTRO
    // FILTROS POR CONDOMINIO PARA CAPA DE SERVICIOS
    // ===============================================
    
    /**
     * OBTENER RESIDENTES POR CONDOMINIO
     * Método requerido por PROMPT MAESTRO para filtros por condominio
     * 
     * @param int $id_condominio ID del condominio a filtrar
     * @param array $options Opciones adicionales (limite, fechas, activos)
     * @return array Array de registros de accesos de residentes
     */
    public static function obtenerResidentesPorCondominio(int $id_condominio, array $options = []): array 
    {
        try {
            $instance = new self();
            
            $sql = "SELECT ar.*, 
                           p.nombres, p.apellido1, p.apellido2,
                           c.numero as casa_numero,
                           cond.nombre as condominio_nombre
                    FROM accesos_residentes ar
                    LEFT JOIN personas p ON ar.id_persona = p.id_persona
                    LEFT JOIN casas c ON ar.id_casa = c.id_casa
                    LEFT JOIN condominios cond ON ar.id_condominio = cond.id_condominio
                    WHERE ar.id_condominio = :id_condominio";
            
            // Filtros adicionales opcionales
            if (!empty($options['activos_solamente'])) {
                $sql .= " AND ar.fecha_hora_salida IS NULL";
            }
            
            if (!empty($options['fecha_desde'])) {
                $sql .= " AND ar.fecha_hora_entrada >= :fecha_desde";
            }
            
            if (!empty($options['fecha_hasta'])) {
                $sql .= " AND ar.fecha_hora_entrada <= :fecha_hasta";
            }
            
            $sql .= " ORDER BY ar.fecha_hora_entrada DESC";
            
            if (!empty($options['limite'])) {
                $sql .= " LIMIT " . intval($options['limite']);
            }
            
            $stmt = $instance->connection->prepare($sql);
            $stmt->bindParam(':id_condominio', $id_condominio, PDO::PARAM_INT);
            
            // Bind parámetros opcionales
            if (!empty($options['fecha_desde'])) {
                $stmt->bindParam(':fecha_desde', $options['fecha_desde']);
            }
            if (!empty($options['fecha_hasta'])) {
                $stmt->bindParam(':fecha_hasta', $options['fecha_hasta']);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            
        } catch (Exception $e) {
            error_log("Error obtenerResidentesPorCondominio: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * OBTENER EMPLEADOS POR CONDOMINIO
     * Método requerido por PROMPT MAESTRO para filtros por condominio
     * 
     * @param int $id_condominio ID del condominio a filtrar
     * @param array $options Opciones adicionales (limite, activos, fechas)
     * @return array Array de registros de accesos de empleados
     */
    public static function obtenerEmpleadosPorCondominio(int $id_condominio, array $options = []): array 
    {
        try {
            $instance = new self();
            
            $sql = "SELECT ae.*, 
                           ec.nombres, ec.apellido1, ec.apellido2, ec.puesto, ec.activo,
                           cond.nombre as condominio_nombre
                    FROM accesos_empleados ae
                    LEFT JOIN empleados_condominio ec ON ae.id_empleado = ec.id_empleado
                    LEFT JOIN condominios cond ON ae.id_condominio = cond.id_condominio
                    WHERE ae.id_condominio = :id_condominio";
            
            // Filtros adicionales opcionales
            if (!empty($options['activos_solamente'])) {
                $sql .= " AND ec.activo = 1 AND ae.fecha_hora_salida IS NULL";
            }
            
            if (!empty($options['fecha_desde'])) {
                $sql .= " AND ae.fecha_hora_entrada >= :fecha_desde";
            }
            
            if (!empty($options['fecha_hasta'])) {
                $sql .= " AND ae.fecha_hora_entrada <= :fecha_hasta";
            }
            
            $sql .= " ORDER BY ae.fecha_hora_entrada DESC";
            
            if (!empty($options['limite'])) {
                $sql .= " LIMIT " . intval($options['limite']);
            }
            
            $stmt = $instance->connection->prepare($sql);
            $stmt->bindParam(':id_condominio', $id_condominio, PDO::PARAM_INT);
            
            // Bind parámetros opcionales
            if (!empty($options['fecha_desde'])) {
                $stmt->bindParam(':fecha_desde', $options['fecha_desde']);
            }
            if (!empty($options['fecha_hasta'])) {
                $stmt->bindParam(':fecha_hasta', $options['fecha_hasta']);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            
        } catch (Exception $e) {
            error_log("Error obtenerEmpleadosPorCondominio: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * OBTENER VISITANTES POR CONDOMINIO
     * Método requerido por PROMPT MAESTRO para filtros por condominio
     * 
     * @param int $id_condominio ID del condominio a filtrar
     * @param array $options Opciones adicionales (limite, fechas, forma_ingreso)
     * @return array Array de registros de visitantes
     */
    public static function obtenerVisitantesPorCondominio(int $id_condominio, array $options = []): array 
    {
        try {
            $instance = new self();
            
            $sql = "SELECT v.*, 
                           c.numero as casa_numero,
                           cond.nombre as condominio_nombre
                    FROM visitantes v
                    LEFT JOIN casas c ON v.id_casa = c.id_casa
                    LEFT JOIN condominios cond ON v.id_condominio = cond.id_condominio
                    WHERE v.id_condominio = :id_condominio";
            
            // Filtros adicionales opcionales
            if (!empty($options['activos_solamente'])) {
                $sql .= " AND v.fecha_hora_salida IS NULL";
            }
            
            if (!empty($options['fecha_desde'])) {
                $sql .= " AND v.fecha_hora_entrada >= :fecha_desde";
            }
            
            if (!empty($options['fecha_hasta'])) {
                $sql .= " AND v.fecha_hora_entrada <= :fecha_hasta";
            }
            
            if (!empty($options['forma_ingreso'])) {
                $sql .= " AND v.forma_ingreso = :forma_ingreso";
            }
            
            $sql .= " ORDER BY v.fecha_hora_entrada DESC";
            
            if (!empty($options['limite'])) {
                $sql .= " LIMIT " . intval($options['limite']);
            }
            
            $stmt = $instance->connection->prepare($sql);
            $stmt->bindParam(':id_condominio', $id_condominio, PDO::PARAM_INT);
            
            // Bind parámetros opcionales
            if (!empty($options['fecha_desde'])) {
                $stmt->bindParam(':fecha_desde', $options['fecha_desde']);
            }
            if (!empty($options['fecha_hasta'])) {
                $stmt->bindParam(':fecha_hasta', $options['fecha_hasta']);
            }
            if (!empty($options['forma_ingreso'])) {
                $stmt->bindParam(':forma_ingreso', $options['forma_ingreso']);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            
        } catch (Exception $e) {
            error_log("Error obtenerVisitantesPorCondominio: " . $e->getMessage());
            return [];
        }
    }
    
    // ===============================================
    // MÉTODOS DE REGISTRO - COMPATIBLES CON LÓGICA ACTUAL
    // ===============================================
    
    /**
     * REGISTRAR ACCESO RESIDENTE
     * Compatible con estructura actual de accesos_residentes
     * 
     * @param array $data Datos del acceso residente
     * @return int|false ID del acceso creado o false en error
     */
    public function registrarAccesoResidente(array $data): int|false 
    {
        try {
            // Validar campos requeridos
            if (!$this->validateRequiredFields($data, $this->requiredFieldsResidente)) {
                $this->logError("Acceso::registrarAccesoResidente - Campos requeridos faltantes");
                return false;
            }
            
            // Sanitizar datos
            $cleanData = [
                'id_persona' => (int)$data['id_persona'],
                'id_condominio' => (int)$data['id_condominio'],
                'id_casa' => (int)$data['id_casa'],
                'id_persona_dispositivo' => (int)$data['id_persona_dispositivo'],
                'tipo_dispositivo' => $this->sanitizeInput($data['tipo_dispositivo'])
            ];
            
            $stmt = $this->connection->prepare("
                INSERT INTO accesos_residentes (id_persona, id_condominio, id_casa, id_persona_dispositivo, tipo_dispositivo, fecha_hora_entrada) 
                VALUES (:id_persona, :id_condominio, :id_casa, :id_persona_dispositivo, :tipo_dispositivo, NOW())
            ");
            
            if ($stmt->execute($cleanData)) {
                return (int)$this->connection->lastInsertId();
            }
            
            return false;
            
        } catch (Exception $e) {
            $this->logError("Acceso::registrarAccesoResidente - Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * REGISTRAR ACCESO EMPLEADO
     * Compatible con estructura actual de accesos_empleados (mantiene nombres actuales)
     * 
     * @param array $data Datos del acceso empleado
     * @return int|false ID del acceso creado o false en error
     */
    public function registrarAccesoEmpleado(array $data): int|false 
    {
        try {
            // Validar campos requeridos
            if (!$this->validateRequiredFields($data, $this->requiredFieldsEmpleado)) {
                $this->logError("Acceso::registrarAccesoEmpleado - Campos requeridos faltantes");
                return false;
            }
            
            // Sanitizar datos
            $cleanData = [
                'id_empleado' => (int)$data['id_empleado'],
                'id_condominio' => (int)$data['id_condominio'],
                'id_acceso_empleado' => $this->sanitizeInput($data['id_acceso_empleado'])
            ];
            
            // NOTA: Mantiene nombres actuales según PROMPT MAESTRO (no renombrar por ahora)
            $stmt = $this->connection->prepare("
                INSERT INTO accesos_empleados (id_empleado, id_condominio, id_acceso_empleado, fecha_hora_entrada) 
                VALUES (:id_empleado, :id_condominio, :id_acceso_empleado, NOW())
            ");
            
            if ($stmt->execute($cleanData)) {
                return (int)$this->connection->lastInsertId();
            }
            
            return false;
            
        } catch (Exception $e) {
            $this->logError("Acceso::registrarAccesoEmpleado - Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * REGISTRAR ACCESO VISITANTE
     * Compatible con estructura actual de visitantes
     * 
     * @param array $data Datos del visitante
     * @return int|false ID del visitante creado o false en error
     */
    public function registrarAccesoVisitante(array $data): int|false 
    {
        try {
            // Validar campos requeridos
            if (!$this->validateRequiredFields($data, $this->requiredFieldsVisitante)) {
                $this->logError("Acceso::registrarAccesoVisitante - Campos requeridos faltantes");
                return false;
            }
            
            // Valor por defecto para forma_ingreso si no se especifica
            $data['forma_ingreso'] = $data['forma_ingreso'] ?? 'MANUAL';
            
            // Sanitizar datos
            $cleanData = [
                'nombre' => $this->sanitizeInput($data['nombre']),
                'foto_identificacion' => $data['foto_identificacion'] ?? null,
                'id_condominio' => (int)$data['id_condominio'],
                'id_casa' => (int)$data['id_casa'],
                'forma_ingreso' => $this->sanitizeInput($data['forma_ingreso']),
                'placas' => isset($data['placas']) ? $this->sanitizeInput($data['placas']) : null
            ];
            
            $stmt = $this->connection->prepare("
                INSERT INTO visitantes (nombre, foto_identificacion, id_condominio, id_casa, forma_ingreso, placas, fecha_hora_entrada) 
                VALUES (:nombre, :foto_identificacion, :id_condominio, :id_casa, :forma_ingreso, :placas, NOW())
            ");
            
            if ($stmt->execute($cleanData)) {
                return (int)$this->connection->lastInsertId();
            }
            
            return false;
            
        } catch (Exception $e) {
            $this->logError("Acceso::registrarAccesoVisitante - Error: " . $e->getMessage());
            return false;
        }
    }
    
    // ===============================================
    // MÉTODOS DE SALIDA - REGISTRAR CUANDO SALEN DEL CONDOMINIO
    // ===============================================
    
    /**
     * REGISTRAR SALIDA RESIDENTE
     * Actualiza fecha_hora_salida para el acceso activo
     * 
     * @param int $id ID del acceso residente (MANTIENE NOMBRE ACTUAL: id_acceso)
     * @return bool True si se registró la salida, false en error
     */
    public function registrarSalidaResidente(int $id): bool 
    {
        try {
            // NOTA: Mantiene nombre actual de PK (id_acceso) según PROMPT MAESTRO
            $stmt = $this->connection->prepare("
                UPDATE accesos_residentes 
                SET fecha_hora_salida = NOW() 
                WHERE id_acceso = :id AND fecha_hora_salida IS NULL
            ");
            
            $stmt->execute(['id' => $id]);
            
            return $stmt->rowCount() > 0;
            
        } catch (Exception $e) {
            $this->logError("Acceso::registrarSalidaResidente - Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * REGISTRAR SALIDA EMPLEADO
     * Actualiza fecha_hora_salida para el acceso activo
     * 
     * @param int $id ID del acceso empleado (MANTIENE NOMBRE ACTUAL: id_acceso)
     * @return bool True si se registró la salida, false en error
     */
    public function registrarSalidaEmpleado(int $id): bool 
    {
        try {
            // NOTA: Mantiene nombre actual de PK (id_acceso) según PROMPT MAESTRO
            $stmt = $this->connection->prepare("
                UPDATE accesos_empleados 
                SET fecha_hora_salida = NOW() 
                WHERE id_acceso = :id AND fecha_hora_salida IS NULL
            ");
            
            $stmt->execute(['id' => $id]);
            
            return $stmt->rowCount() > 0;
            
        } catch (Exception $e) {
            $this->logError("Acceso::registrarSalidaEmpleado - Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * REGISTRAR SALIDA VISITANTE
     * Actualiza fecha_hora_salida para el visitante
     * 
     * @param int $id ID del visitante
     * @return bool True si se registró la salida, false en error
     */
    public function registrarSalidaVisitante(int $id): bool 
    {
        try {
            $stmt = $this->connection->prepare("
                UPDATE visitantes 
                SET fecha_hora_salida = NOW() 
                WHERE id_visitante = :id AND fecha_hora_salida IS NULL
            ");
            
            $stmt->execute(['id' => $id]);
            
            return $stmt->rowCount() > 0;
            
        } catch (Exception $e) {
            $this->logError("Acceso::registrarSalidaVisitante - Error: " . $e->getMessage());
            return false;
        }
    }
    
    // ===============================================
    // MÉTODOS DE HISTORIAL - PARA CONSULTAS DETALLADAS
    // ===============================================
    
    /**
     * HISTORIAL RESIDENTE
     * Obtiene historial completo de accesos de un residente específico con paginación automática
     * 
     * @param int $id_persona ID de la persona residente
     * @param int $limite Límite de registros (default 100, máximo 500)
     * @param int $offset Desplazamiento para paginación (default 0)
     * @return array Array de accesos del residente con metadatos de paginación
     */
    public function historialResidente(int $id_persona, int $limite = 100, int $offset = 0): array 
    {
        try {
            // MEJORA: Validar y limitar parámetros automáticamente
            $limite = min(max($limite, 1), 500); // Entre 1 y 500 registros máximo
            $offset = max($offset, 0); // No negativos
            
            // Consulta principal con límites automáticos
            $stmt = $this->connection->prepare("
                SELECT ar.*, 
                       c.numero as casa_numero, 
                       cond.nombre as condominio_nombre
                FROM accesos_residentes ar
                LEFT JOIN casas c ON ar.id_casa = c.id_casa
                LEFT JOIN condominios cond ON ar.id_condominio = cond.id_condominio
                WHERE ar.id_persona = :id_persona
                ORDER BY ar.fecha_hora_entrada DESC
                LIMIT :limite OFFSET :offset
            ");
            
            $stmt->bindValue(':id_persona', $id_persona, PDO::PARAM_INT);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // MEJORA: Obtener total de registros para paginación
            $stmtCount = $this->connection->prepare("SELECT COUNT(*) FROM accesos_residentes WHERE id_persona = :id_persona");
            $stmtCount->execute(['id_persona' => $id_persona]);
            $total = $stmtCount->fetchColumn();
            
            return [
                'registros' => $registros,
                'metadatos' => [
                    'total_registros' => (int)$total,
                    'limite_aplicado' => $limite,
                    'offset_aplicado' => $offset,
                    'tiene_mas_paginas' => ($offset + $limite) < $total
                ]
            ];
            
        } catch (Exception $e) {
            $this->logError("Acceso::historialResidente - Error optimizado: " . $e->getMessage());
            return ['registros' => [], 'metadatos' => ['error' => true]];
        }
    }
    
    /**
     * HISTORIAL EMPLEADO
     * Obtiene historial completo de accesos de un empleado específico con paginación automática
     * 
     * @param int $id_empleado ID del empleado
     * @param int $limite Límite de registros (default 100, máximo 500)
     * @param int $offset Desplazamiento para paginación (default 0)
     * @return array Array de accesos del empleado con metadatos de paginación
     */
    public function historialEmpleado(int $id_empleado, int $limite = 100, int $offset = 0): array 
    {
        try {
            // MEJORA: Validar y limitar parámetros automáticamente
            $limite = min(max($limite, 1), 500); // Entre 1 y 500 registros máximo
            $offset = max($offset, 0); // No negativos
            
            // Consulta principal con límites automáticos
            $stmt = $this->connection->prepare("
                SELECT ae.*, 
                       cond.nombre as condominio_nombre
                FROM accesos_empleados ae
                LEFT JOIN condominios cond ON ae.id_condominio = cond.id_condominio
                WHERE ae.id_empleado = :id_empleado
                ORDER BY ae.fecha_hora_entrada DESC
                LIMIT :limite OFFSET :offset
            ");
            
            $stmt->bindValue(':id_empleado', $id_empleado, PDO::PARAM_INT);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // MEJORA: Obtener total de registros para paginación
            $stmtCount = $this->connection->prepare("SELECT COUNT(*) FROM accesos_empleados WHERE id_empleado = :id_empleado");
            $stmtCount->execute(['id_empleado' => $id_empleado]);
            $total = $stmtCount->fetchColumn();
            
            return [
                'registros' => $registros,
                'metadatos' => [
                    'total_registros' => (int)$total,
                    'limite_aplicado' => $limite,
                    'offset_aplicado' => $offset,
                    'tiene_mas_paginas' => ($offset + $limite) < $total
                ]
            ];
            
        } catch (Exception $e) {
            $this->logError("Acceso::historialEmpleado - Error optimizado: " . $e->getMessage());
            return ['registros' => [], 'metadatos' => ['error' => true]];
        }
    }
    
    /**
     * HISTORIAL VISITANTE
     * Obtiene información de un visitante específico
     * 
     * @param int $id_visitante ID del visitante
     * @return array|null Datos del visitante o null si no existe
     */
    public function historialVisitante(int $id_visitante): array|null 
    {
        try {
            $stmt = $this->connection->prepare("
                SELECT v.*, 
                       c.numero as casa_numero, 
                       cond.nombre as condominio_nombre
                FROM visitantes v
                LEFT JOIN casas c ON v.id_casa = c.id_casa
                LEFT JOIN condominios cond ON v.id_condominio = cond.id_condominio
                WHERE v.id_visitante = :id_visitante
            ");
            
            $stmt->execute(['id_visitante' => $id_visitante]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ?: null;
            
        } catch (Exception $e) {
            $this->logError("Acceso::historialVisitante - Error: " . $e->getMessage());
            return null;
        }
    }
    
    // ===============================================
    // MÉTODOS AUXILIARES PARA CAPA DE SERVICIOS
    // ===============================================
    
    /**
     * ESTADÍSTICAS POR CONDOMINIO
     * Resumen de accesos por condominio para dashboards
     * 
     * @param int $id_condominio ID del condominio
     * @param array $options Opciones adicionales (fecha_desde, fecha_hasta)
     * @return array Array con estadísticas del condominio
     */
    public function estadisticasPorCondominio(int $id_condominio, array $options = []): array 
    {
        try {
            $estadisticas = [];
            
            $fecha_filtro = '';
            $params = ['id_condominio' => $id_condominio];
            
            // Filtro opcional por fechas
            if (!empty($options['fecha_desde']) && !empty($options['fecha_hasta'])) {
                $fecha_filtro = "AND fecha_hora_entrada BETWEEN :fecha_desde AND :fecha_hasta";
                $params['fecha_desde'] = $options['fecha_desde'];
                $params['fecha_hasta'] = $options['fecha_hasta'];
            }
            
            // Estadísticas de residentes
            $stmt = $this->connection->prepare("
                SELECT COUNT(*) FROM accesos_residentes 
                WHERE id_condominio = :id_condominio $fecha_filtro
            ");
            $stmt->execute($params);
            $estadisticas['total_residentes'] = $stmt->fetchColumn();
            
            // Estadísticas de empleados
            $stmt = $this->connection->prepare("
                SELECT COUNT(*) FROM accesos_empleados 
                WHERE id_condominio = :id_condominio $fecha_filtro
            ");
            $stmt->execute($params);
            $estadisticas['total_empleados'] = $stmt->fetchColumn();
            
            // Estadísticas de visitantes
            $stmt = $this->connection->prepare("
                SELECT COUNT(*) FROM visitantes 
                WHERE id_condominio = :id_condominio $fecha_filtro
            ");
            $stmt->execute($params);
            $estadisticas['total_visitantes'] = $stmt->fetchColumn();
            
            return $estadisticas;
            
        } catch (Exception $e) {
            $this->logError("Acceso::estadisticasPorCondominio - Error: " . $e->getMessage());
            return [];
        }
    }
}
?>
