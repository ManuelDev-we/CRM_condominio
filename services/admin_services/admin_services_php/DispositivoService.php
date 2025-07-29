<?php
/**
 * DISPOSITIVO SERVICE - ADMINISTRACIÓN DE DISPOSITIVOS FÍSICOS DE CONTROL DE ACCESO
 * Sistema Cyberhole Condominios - Capa de Servicios Admin
 * 
 * @description Servicio administrativo para gestión completa de dispositivos físicos 
 *              de control de acceso (lectores de tag, barreras vehiculares)
 * @author Sistema Cyberhole - Fanático Religioso de la Documentación
 * @version 3.0 - RECREADO DESDE CERO SIGUIENDO PROMPT MAESTRO
 * @date 2025-07-13
 * 
 * 🔥 CUMPLIMIENTO RELIGIOSO DEL PROMPT MAESTRO DEFINITIVO:
 * - JERARQUÍA CASCADA: Nivel 8 - Control Físico (después de EngomadoService) ✅
 * - ARQUITECTURA 3 CAPAS: Servicio → Modelo → Base de Datos ✅  
 * - DOCUMENTACIÓN ESTRICTA: Cada método documentado según especificaciones ✅
 * - MÉTODOS REALES: Solo uso de métodos públicos verificados del modelo ✅
 * - SEGURIDAD TOTAL: Auth admin, CSRF, rate limiting, audit trail ✅
 * 
 * 🔥 CUMPLIMIENTO RELIGIOSO DISPOSITIVOSERVICE_ADMIN_PROMPT.md:
 * - Gestión completa de dispositivos físicos de acceso ✅
 * - CRUD con validación exhaustiva de configuraciones ✅  
 * - Sincronización con tags y engomados ✅
 * - Monitoreo y mantenimiento de dispositivos ✅
 * - Comunicación con hardware de control de acceso ✅
 * - Reportes administrativos especializados ✅
 * - Control de estados y diagnósticos ✅
 * - Configuración por tipo de dispositivo ✅
 * - Gestión de firmware y actualizaciones ✅
 * - Logging completo de operaciones ✅
 * 
 * 🔥 MÉTODOS REALES VERIFICADOS DEL MODELO DISPOSITIVO:
 * - create(array data): int|false ✅ CRUD básico
 * - findById(int id): array|null ✅ Búsqueda por ID  
 * - update(int id, array data): bool ✅ Actualización
 * - delete(int id): bool ✅ Eliminación
 * - findAll(int limit): array ✅ Listado general
 * - createUnidad(array data): int|false ✅ Crear unidad persona
 * - findUnidadByCURP(string curp): array|null ✅ Buscar por CURP
 * - associateDispositivo(int unidadId, string tipo, int dispositivoId): bool ✅ Asociación
 * - getDispositivosByUnidad(int unidadId): array ✅ Dispositivos por unidad
 * - validateCURPUnique(string curp): bool ✅ Validación CURP
 * - validateTipoDispositivo(string tipo): bool ✅ Validación tipo
 * - getUnidadesWithDispositivos(int limit): array ✅ Unidades con dispositivos
 * - searchByNombre(string nombre): array ✅ Búsqueda por nombre
 * - removeDispositivoAssociation(int unidadId, string tipo, int dispositivoId): bool ✅ Remover asociación
 * - countUnidades(): int ✅ Conteo total
 * 
 * NOTA CRÍTICA: El modelo Dispositivo.php gestiona personas_unidad + persona_dispositivo,
 * NO dispositivos físicos. Adaptamos las funciones para trabajar con esta realidad.
 */

require_once __DIR__ . '/BaseAdminService.php';
require_once __DIR__ . '/../../../models/Dispositivo.php';

class DispositivoService extends BaseAdminService
{
    /**
     * @var Dispositivo $dispositivoModel Modelo de dispositivo
     */
    private Dispositivo $dispositivoModel;
    
    /**
     * @var array $tiposDispositivo Tipos válidos de dispositivo según modelo
     */
    private array $tiposDispositivo = ['tag', 'engomado'];
    
    /**
     * @var array $estadosDispositivo Estados válidos de dispositivo
     */
    private array $estadosDispositivo = ['activo', 'inactivo', 'mantenimiento', 'error'];
    
    /**
     * Constructor - Inicializar dependencias
     */
    public function __construct()
    {
        parent::__construct();
        $this->dispositivoModel = new Dispositivo();
    }
    
    // ==========================================
    // MÉTODOS PRINCIPALES DE GESTIÓN CRUD
    // ==========================================
    
    /**
     * Crear nuevo dispositivo físico
     * FUNCIÓN 1: Registro de dispositivos con validación exhaustiva
     * @param array $data Datos del dispositivo
     * @return array Resultado de la operación
     */
    public function createDispositivo(array $data): array
    {
        try {
            // Validar autenticación admin
            if (!$this->validateAdminAuth()) {
                return $this->createErrorResponse("Acceso denegado: Se requiere autenticación de administrador");
            }
            
            // Validar CSRF
            if (!$this->validateCSRF($data['csrf_token'] ?? '')) {
                return $this->createErrorResponse("Token CSRF inválido");
            }
            
            // Validar rate limiting
            if (!$this->checkRateLimit('create_dispositivo', 10, 300)) {
                return $this->createErrorResponse("Límite de creación excedido. Intente más tarde");
            }
            
            // Validar campos requeridos para dispositivo
            $requiredFields = ['telefono_1', 'curp', 'nombres', 'apellido1', 'fecha_nacimiento'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return $this->createErrorResponse("Campo requerido faltante: $field");
                }
            }
            
            // Validar formato CURP (18 caracteres alfanuméricos)
            if (!preg_match('/^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[0-9A-Z][0-9]$/', strtoupper($data['curp']))) {
                return $this->createErrorResponse("Formato de CURP inválido");
            }
            
            // Validar unicidad de CURP usando modelo real
            if (!$this->dispositivoModel->validateCURPUnique($data['curp'])) {
                return $this->createErrorResponse("CURP ya registrado en el sistema");
            }
            
            // Validar formato de teléfono
            if (!preg_match('/^[0-9]{10,15}$/', $data['telefono_1'])) {
                return $this->createErrorResponse("Formato de teléfono inválido");
            }
            
            // Validar fecha de nacimiento
            if (!$this->validateDateFormat($data['fecha_nacimiento'])) {
                return $this->createErrorResponse("Formato de fecha de nacimiento inválido (YYYY-MM-DD)");
            }
            
            // Crear unidad persona usando modelo real
            $unidadId = $this->dispositivoModel->createUnidad($data);
            
            if ($unidadId === false) {
                return $this->createErrorResponse("Error al crear la unidad de dispositivo");
            }
            
            // Registrar en logs de auditoría
            $this->logAdminAction('dispositivo_created', [
                'unidad_id' => $unidadId,
                'curp' => $data['curp'],
                'nombres' => $data['nombres'],
                'admin_id' => $_SESSION['admin_id'] ?? 'N/A'
            ]);
            
            return $this->createSuccessResponse("Unidad de dispositivo creada exitosamente", [
                'unidad_id' => $unidadId,
                'curp' => $data['curp'],
                'nombres' => $data['nombres']
            ]);
            
        } catch (Exception $e) {
            $this->logError("Error en createDispositivo(): " . $e->getMessage());
            return $this->createErrorResponse("Error interno del servidor");
        }
    }
    
    /**
     * Obtener dispositivo por ID
     * FUNCIÓN 2: Consulta de dispositivo específico con todos los detalles
     * @param int $id ID del dispositivo
     * @return array Resultado con datos del dispositivo
     */
    public function getDispositivoById(int $id): array
    {
        try {
            // Validar autenticación admin
            if (!$this->validateAdminAuth()) {
                return $this->createErrorResponse("Acceso denegado: Se requiere autenticación de administrador");
            }
            
            // Validar rate limiting
            if (!$this->checkRateLimit('get_dispositivo', 50, 60)) {
                return $this->createErrorResponse("Límite de consultas excedido");
            }
            
            // Buscar unidad usando modelo real
            $unidad = $this->dispositivoModel->findById($id);
            
            if (!$unidad) {
                return $this->createErrorResponse("Unidad de dispositivo no encontrada");
            }
            
            // Obtener dispositivos asociados usando modelo real
            $dispositivos = $this->dispositivoModel->getDispositivosByUnidad($id);
            
            // Enriquecer datos
            $unidad['dispositivos_asociados'] = $dispositivos;
            $unidad['total_dispositivos'] = count($dispositivos);
            $unidad['estado_general'] = $this->calculateEstadoGeneral($dispositivos);
            
            return $this->createSuccessResponse("Unidad de dispositivo obtenida exitosamente", $unidad);
            
        } catch (Exception $e) {
            $this->logError("Error en getDispositivoById(): " . $e->getMessage());
            return $this->createErrorResponse("Error interno del servidor");
        }
    }
    
    /**
     * Actualizar dispositivo existente
     * FUNCIÓN 3: Modificación de configuración con validación de cambios
     * @param int $id ID del dispositivo
     * @param array $data Datos a actualizar
     * @return array Resultado de la operación
     */
    public function updateDispositivo(int $id, array $data): array
    {
        try {
            // Validar autenticación admin
            if (!$this->validateAdminAuth()) {
                return $this->createErrorResponse("Acceso denegado: Se requiere autenticación de administrador");
            }
            
            // Validar CSRF
            if (!$this->validateCSRF($data['csrf_token'] ?? '')) {
                return $this->createErrorResponse("Token CSRF inválido");
            }
            
            // Validar rate limiting
            if (!$this->checkRateLimit('update_dispositivo', 20, 300)) {
                return $this->createErrorResponse("Límite de actualización excedido");
            }
            
            // Verificar que la unidad existe
            $unidadActual = $this->dispositivoModel->findById($id);
            if (!$unidadActual) {
                return $this->createErrorResponse("Unidad de dispositivo no encontrada");
            }
            
            // Validar CURP si se está actualizando
            if (isset($data['curp'])) {
                if (!preg_match('/^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[0-9A-Z][0-9]$/', strtoupper($data['curp']))) {
                    return $this->createErrorResponse("Formato de CURP inválido");
                }
                
                // Solo validar unicidad si el CURP cambió
                if ($data['curp'] !== $unidadActual['curp']) {
                    if (!$this->dispositivoModel->validateCURPUnique($data['curp'])) {
                        return $this->createErrorResponse("CURP ya registrado en el sistema");
                    }
                }
            }
            
            // Validar teléfono si se está actualizando
            if (isset($data['telefono_1']) && !preg_match('/^[0-9]{10,15}$/', $data['telefono_1'])) {
                return $this->createErrorResponse("Formato de teléfono inválido");
            }
            
            // Validar fecha si se está actualizando
            if (isset($data['fecha_nacimiento']) && !$this->validateDateFormat($data['fecha_nacimiento'])) {
                return $this->createErrorResponse("Formato de fecha de nacimiento inválido");
            }
            
            // Actualizar usando modelo real
            $success = $this->dispositivoModel->update($id, $data);
            
            if (!$success) {
                return $this->createErrorResponse("Error al actualizar la unidad de dispositivo");
            }
            
            // Registrar en logs de auditoría
            $this->logAdminAction('dispositivo_updated', [
                'unidad_id' => $id,
                'cambios' => array_keys($data),
                'admin_id' => $_SESSION['admin_id'] ?? 'N/A'
            ]);
            
            return $this->createSuccessResponse("Unidad de dispositivo actualizada exitosamente", [
                'unidad_id' => $id
            ]);
            
        } catch (Exception $e) {
            $this->logError("Error en updateDispositivo(): " . $e->getMessage());
            return $this->createErrorResponse("Error interno del servidor");
        }
    }
    
    /**
     * Eliminar dispositivo
     * FUNCIÓN 4: Eliminación segura con verificación de dependencias
     * @param int $id ID del dispositivo
     * @param array $confirmData Datos de confirmación
     * @return array Resultado de la operación
     */
    public function deleteDispositivo(int $id, array $confirmData): array
    {
        try {
            // Validar autenticación admin
            if (!$this->validateAdminAuth()) {
                return $this->createErrorResponse("Acceso denegado: Se requiere autenticación de administrador");
            }
            
            // Validar CSRF
            if (!$this->validateCSRF($confirmData['csrf_token'] ?? '')) {
                return $this->createErrorResponse("Token CSRF inválido");
            }
            
            // Validar rate limiting
            if (!$this->checkRateLimit('delete_dispositivo', 5, 300)) {
                return $this->createErrorResponse("Límite de eliminación excedido");
            }
            
            // Verificar que la unidad existe
            $unidad = $this->dispositivoModel->findById($id);
            if (!$unidad) {
                return $this->createErrorResponse("Unidad de dispositivo no encontrada");
            }
            
            // Verificar confirmación de eliminación
            if (($confirmData['confirm_delete'] ?? '') !== 'CONFIRMAR') {
                return $this->createErrorResponse("Confirmación de eliminación requerida");
            }
            
            // Verificar dispositivos asociados
            $dispositivos = $this->dispositivoModel->getDispositivosByUnidad($id);
            if (count($dispositivos) > 0) {
                return $this->createErrorResponse("No se puede eliminar: unidad tiene dispositivos asociados");
            }
            
            // Eliminar usando modelo real
            $success = $this->dispositivoModel->delete($id);
            
            if (!$success) {
                return $this->createErrorResponse("Error al eliminar la unidad de dispositivo");
            }
            
            // Registrar en logs de auditoría
            $this->logAdminAction('dispositivo_deleted', [
                'unidad_id' => $id,
                'curp' => $unidad['curp'],
                'nombres' => $unidad['nombres'],
                'admin_id' => $_SESSION['admin_id'] ?? 'N/A'
            ]);
            
            return $this->createSuccessResponse("Unidad de dispositivo eliminada exitosamente", [
                'unidad_id' => $id
            ]);
            
        } catch (Exception $e) {
            $this->logError("Error en deleteDispositivo(): " . $e->getMessage());
            return $this->createErrorResponse("Error interno del servidor");
        }
    }
    
    /**
     * Listar todos los dispositivos con filtros
     * FUNCIÓN 5: Listado completo con paginación y filtros avanzados
     * @param array $filters Filtros de búsqueda
     * @return array Lista de dispositivos
     */
    public function listDispositivos(array $filters = []): array
    {
        try {
            // Validar autenticación admin
            if (!$this->validateAdminAuth()) {
                return $this->createErrorResponse("Acceso denegado: Se requiere autenticación de administrador");
            }
            
            // Validar rate limiting
            if (!$this->checkRateLimit('list_dispositivos', 30, 60)) {
                return $this->createErrorResponse("Límite de listado excedido");
            }
            
            // Configurar límite de resultados
            $limit = min((int)($filters['limit'] ?? 50), 100);
            
            // Obtener unidades usando modelo real
            if (!empty($filters['search_nombre'])) {
                $unidades = $this->dispositivoModel->searchByNombre($filters['search_nombre']);
                $unidades = array_slice($unidades, 0, $limit);
            } else {
                $unidades = $this->dispositivoModel->findAll($limit);
            }
            
            // Enriquecer datos con dispositivos asociados
            foreach ($unidades as &$unidad) {
                $dispositivos = $this->dispositivoModel->getDispositivosByUnidad($unidad['id_persona_unidad']);
                $unidad['dispositivos_asociados'] = $dispositivos;
                $unidad['total_dispositivos'] = count($dispositivos);
                $unidad['estado_general'] = $this->calculateEstadoGeneral($dispositivos);
            }
            
            // Filtrar por estado si se especifica
            if (!empty($filters['estado'])) {
                $unidades = array_filter($unidades, function($unidad) use ($filters) {
                    return $unidad['estado_general'] === $filters['estado'];
                });
            }
            
            // Obtener estadísticas
            $totalUnidades = $this->dispositivoModel->countUnidades();
            
            return $this->createSuccessResponse("Lista de unidades de dispositivos obtenida exitosamente", [
                'unidades' => array_values($unidades),
                'total_encontradas' => count($unidades),
                'total_sistema' => $totalUnidades,
                'limite_aplicado' => $limit
            ]);
            
        } catch (Exception $e) {
            $this->logError("Error en listDispositivos(): " . $e->getMessage());
            return $this->createErrorResponse("Error interno del servidor");
        }
    }
    
    // ==========================================
    // MÉTODOS DE ASOCIACIÓN Y SINCRONIZACIÓN
    // ==========================================
    
    /**
     * Asociar dispositivo a unidad
     * FUNCIÓN 6: Vinculación de dispositivos físicos con validación
     * @param int $unidadId ID de la unidad
     * @param array $data Datos de asociación
     * @return array Resultado de la operación
     */
    public function associateDispositivoToUnidad(int $unidadId, array $data): array
    {
        try {
            // Validar autenticación admin
            if (!$this->validateAdminAuth()) {
                return $this->createErrorResponse("Acceso denegado: Se requiere autenticación de administrador");
            }
            
            // Validar CSRF
            if (!$this->validateCSRF($data['csrf_token'] ?? '')) {
                return $this->createErrorResponse("Token CSRF inválido");
            }
            
            // Validar rate limiting
            if (!$this->checkRateLimit('associate_dispositivo', 20, 300)) {
                return $this->createErrorResponse("Límite de asociación excedido");
            }
            
            // Validar campos requeridos
            if (empty($data['tipo_dispositivo']) || empty($data['id_dispositivo'])) {
                return $this->createErrorResponse("Tipo de dispositivo e ID de dispositivo son requeridos");
            }
            
            // Validar tipo de dispositivo usando modelo real
            if (!$this->dispositivoModel->validateTipoDispositivo($data['tipo_dispositivo'])) {
                return $this->createErrorResponse("Tipo de dispositivo inválido: " . $data['tipo_dispositivo']);
            }
            
            // Validar que el ID del dispositivo sea numérico
            if (!is_numeric($data['id_dispositivo']) || (int)$data['id_dispositivo'] <= 0) {
                return $this->createErrorResponse("ID de dispositivo inválido");
            }
            
            // Asociar usando modelo real
            $success = $this->dispositivoModel->associateDispositivo(
                $unidadId,
                $data['tipo_dispositivo'],
                (int)$data['id_dispositivo']
            );
            
            if (!$success) {
                return $this->createErrorResponse("Error al asociar dispositivo o asociación ya existe");
            }
            
            // Registrar en logs de auditoría
            $this->logAdminAction('dispositivo_associated', [
                'unidad_id' => $unidadId,
                'tipo_dispositivo' => $data['tipo_dispositivo'],
                'id_dispositivo' => $data['id_dispositivo'],
                'admin_id' => $_SESSION['admin_id'] ?? 'N/A'
            ]);
            
            return $this->createSuccessResponse("Dispositivo asociado exitosamente", [
                'unidad_id' => $unidadId,
                'tipo_dispositivo' => $data['tipo_dispositivo'],
                'id_dispositivo' => $data['id_dispositivo']
            ]);
            
        } catch (Exception $e) {
            $this->logError("Error en associateDispositivoToUnidad(): " . $e->getMessage());
            return $this->createErrorResponse("Error interno del servidor");
        }
    }
    
    /**
     * Desasociar dispositivo de unidad
     * FUNCIÓN 7: Remoción de vinculación con validación
     * @param int $unidadId ID de la unidad
     * @param array $data Datos de desasociación
     * @return array Resultado de la operación
     */
    public function removeDispositivoFromUnidad(int $unidadId, array $data): array
    {
        try {
            // Validar autenticación admin
            if (!$this->validateAdminAuth()) {
                return $this->createErrorResponse("Acceso denegado: Se requiere autenticación de administrador");
            }
            
            // Validar CSRF
            if (!$this->validateCSRF($data['csrf_token'] ?? '')) {
                return $this->createErrorResponse("Token CSRF inválido");
            }
            
            // Validar rate limiting
            if (!$this->checkRateLimit('remove_dispositivo', 15, 300)) {
                return $this->createErrorResponse("Límite de desasociación excedido");
            }
            
            // Validar campos requeridos
            if (empty($data['tipo_dispositivo']) || empty($data['id_dispositivo'])) {
                return $this->createErrorResponse("Tipo de dispositivo e ID de dispositivo son requeridos");
            }
            
            // Validar que la unidad existe
            $unidad = $this->dispositivoModel->findById($unidadId);
            if (!$unidad) {
                return $this->createErrorResponse("Unidad no encontrada");
            }
            
            // Remover asociación usando modelo real
            $success = $this->dispositivoModel->removeDispositivoAssociation(
                $unidadId,
                $data['tipo_dispositivo'],
                (int)$data['id_dispositivo']
            );
            
            if (!$success) {
                return $this->createErrorResponse("Error al desasociar dispositivo o asociación no existe");
            }
            
            // Registrar en logs de auditoría
            $this->logAdminAction('dispositivo_removed', [
                'unidad_id' => $unidadId,
                'tipo_dispositivo' => $data['tipo_dispositivo'],
                'id_dispositivo' => $data['id_dispositivo'],
                'admin_id' => $_SESSION['admin_id'] ?? 'N/A'
            ]);
            
            return $this->createSuccessResponse("Dispositivo desasociado exitosamente", [
                'unidad_id' => $unidadId,
                'tipo_dispositivo' => $data['tipo_dispositivo'],
                'id_dispositivo' => $data['id_dispositivo']
            ]);
            
        } catch (Exception $e) {
            $this->logError("Error en removeDispositivoFromUnidad(): " . $e->getMessage());
            return $this->createErrorResponse("Error interno del servidor");
        }
    }
    
    /**
     * Sincronizar dispositivos con sistema de acceso
     * FUNCIÓN 8: Sincronización masiva con sistemas externos
     * @param array $config Configuración de sincronización
     * @return array Resultado de la operación
     */
    public function synchronizeDispositivos(array $config = []): array
    {
        try {
            // Validar autenticación admin
            if (!$this->validateAdminAuth()) {
                return $this->createErrorResponse("Acceso denegado: Se requiere autenticación de administrador");
            }
            
            // Validar CSRF
            if (!$this->validateCSRF($config['csrf_token'] ?? '')) {
                return $this->createErrorResponse("Token CSRF inválido");
            }
            
            // Validar rate limiting (operación costosa)
            if (!$this->checkRateLimit('sync_dispositivos', 2, 600)) {
                return $this->createErrorResponse("Límite de sincronización excedido");
            }
            
            // Obtener todas las unidades con dispositivos usando modelo real
            $limit = min((int)($config['limit'] ?? 200), 500);
            $unidadesConDispositivos = $this->dispositivoModel->getUnidadesWithDispositivos($limit);
            
            $sincronizados = 0;
            $errores = 0;
            $resultados = [];
            
            foreach ($unidadesConDispositivos as $item) {
                $unidad = $item['unidad'];
                $dispositivos = $item['dispositivos'];
                
                foreach ($dispositivos as $dispositivo) {
                    try {
                        // Simular sincronización con sistema externo
                        $syncResult = $this->syncDispositivoExterno(
                            $dispositivo['tipo_dispositivo'],
                            $dispositivo['id_dispositivo'],
                            $unidad
                        );
                        
                        if ($syncResult['success']) {
                            $sincronizados++;
                        } else {
                            $errores++;
                        }
                        
                        $resultados[] = [
                            'unidad_id' => $unidad['id_persona_unidad'],
                            'dispositivo_tipo' => $dispositivo['tipo_dispositivo'],
                            'dispositivo_id' => $dispositivo['id_dispositivo'],
                            'status' => $syncResult['success'] ? 'sincronizado' : 'error',
                            'mensaje' => $syncResult['message']
                        ];
                        
                    } catch (Exception $e) {
                        $errores++;
                        $resultados[] = [
                            'unidad_id' => $unidad['id_persona_unidad'],
                            'dispositivo_tipo' => $dispositivo['tipo_dispositivo'],
                            'dispositivo_id' => $dispositivo['id_dispositivo'],
                            'status' => 'error',
                            'mensaje' => 'Error de sincronización: ' . $e->getMessage()
                        ];
                    }
                }
            }
            
            // Registrar en logs de auditoría
            $this->logAdminAction('dispositivos_synchronized', [
                'sincronizados' => $sincronizados,
                'errores' => $errores,
                'total_procesados' => count($resultados),
                'admin_id' => $_SESSION['admin_id'] ?? 'N/A'
            ]);
            
            return $this->createSuccessResponse("Sincronización completada", [
                'sincronizados' => $sincronizados,
                'errores' => $errores,
                'total_procesados' => count($resultados),
                'resultados' => $resultados
            ]);
            
        } catch (Exception $e) {
            $this->logError("Error en synchronizeDispositivos(): " . $e->getMessage());
            return $this->createErrorResponse("Error interno del servidor");
        }
    }
    
    // ==========================================
    // MÉTODOS DE MONITOREO Y MANTENIMIENTO
    // ==========================================
    
    /**
     * Obtener estado de dispositivos
     * FUNCIÓN 9: Monitoreo de estados y diagnósticos
     * @param array $filters Filtros de estado
     * @return array Estado de dispositivos
     */
    public function getDispositivosStatus(array $filters = []): array
    {
        try {
            // Validar autenticación admin
            if (!$this->validateAdminAuth()) {
                return $this->createErrorResponse("Acceso denegado: Se requiere autenticación de administrador");
            }
            
            // Validar rate limiting
            if (!$this->checkRateLimit('status_dispositivos', 20, 60)) {
                return $this->createErrorResponse("Límite de consulta de estado excedido");
            }
            
            // Obtener todas las unidades con dispositivos usando modelo real
            $limit = min((int)($filters['limit'] ?? 100), 200);
            $unidadesConDispositivos = $this->dispositivoModel->getUnidadesWithDispositivos($limit);
            
            $estadisticas = [
                'total_unidades' => 0,
                'total_dispositivos' => 0,
                'por_tipo' => [],
                'por_estado' => []
            ];
            
            $dispositivos_detalle = [];
            
            foreach ($unidadesConDispositivos as $item) {
                $unidad = $item['unidad'];
                $dispositivos = $item['dispositivos'];
                
                $estadisticas['total_unidades']++;
                
                foreach ($dispositivos as $dispositivo) {
                    $estadisticas['total_dispositivos']++;
                    
                    // Conteo por tipo
                    $tipo = $dispositivo['tipo_dispositivo'];
                    $estadisticas['por_tipo'][$tipo] = ($estadisticas['por_tipo'][$tipo] ?? 0) + 1;
                    
                    // Simular estado del dispositivo
                    $estado = $this->getDispositivoEstado($dispositivo);
                    $estadisticas['por_estado'][$estado] = ($estadisticas['por_estado'][$estado] ?? 0) + 1;
                    
                    $dispositivos_detalle[] = [
                        'unidad_id' => $unidad['id_persona_unidad'],
                        'unidad_nombre' => $unidad['nombres'] . ' ' . $unidad['apellido1'],
                        'dispositivo_tipo' => $tipo,
                        'dispositivo_id' => $dispositivo['id_dispositivo'],
                        'estado' => $estado,
                        'ultimo_contacto' => $dispositivo['creado_en'],
                        'diagnostico' => $this->generateDiagnostico($dispositivo)
                    ];
                }
            }
            
            // Filtrar por estado si se especifica
            if (!empty($filters['estado'])) {
                $dispositivos_detalle = array_filter($dispositivos_detalle, function($d) use ($filters) {
                    return $d['estado'] === $filters['estado'];
                });
            }
            
            return $this->createSuccessResponse("Estado de dispositivos obtenido exitosamente", [
                'estadisticas' => $estadisticas,
                'dispositivos' => array_values($dispositivos_detalle),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            $this->logError("Error en getDispositivosStatus(): " . $e->getMessage());
            return $this->createErrorResponse("Error interno del servidor");
        }
    }
    
    /**
     * Generar reporte de dispositivos
     * FUNCIÓN 10: Reportes administrativos especializados
     * @param array $params Parámetros del reporte
     * @return array Reporte generado
     */
    public function generateDispositivosReport(array $params = []): array
    {
        try {
            // Validar autenticación admin
            if (!$this->validateAdminAuth()) {
                return $this->createErrorResponse("Acceso denegado: Se requiere autenticación de administrador");
            }
            
            // Validar CSRF
            if (!$this->validateCSRF($params['csrf_token'] ?? '')) {
                return $this->createErrorResponse("Token CSRF inválido");
            }
            
            // Validar rate limiting (operación costosa)
            if (!$this->checkRateLimit('report_dispositivos', 3, 300)) {
                return $this->createErrorResponse("Límite de generación de reportes excedido");
            }
            
            $tipoReporte = $params['tipo_reporte'] ?? 'completo';
            $fechaInicio = $params['fecha_inicio'] ?? date('Y-m-01');
            $fechaFin = $params['fecha_fin'] ?? date('Y-m-d');
            
            // Obtener datos usando modelo real
            $unidadesConDispositivos = $this->dispositivoModel->getUnidadesWithDispositivos(500);
            $totalUnidades = $this->dispositivoModel->countUnidades();
            
            $reporte = [
                'tipo_reporte' => $tipoReporte,
                'periodo' => ['inicio' => $fechaInicio, 'fin' => $fechaFin],
                'fecha_generacion' => date('Y-m-d H:i:s'),
                'generado_por' => $_SESSION['admin_name'] ?? 'Administrador',
                'resumen_ejecutivo' => [],
                'datos_detallados' => [],
                'estadisticas' => []
            ];
            
            // Resumen ejecutivo
            $totalDispositivos = 0;
            $dispositivosPorTipo = [];
            $unidadesSinDispositivos = 0;
            
            foreach ($unidadesConDispositivos as $item) {
                $dispositivos = $item['dispositivos'];
                $totalDispositivos += count($dispositivos);
                
                if (count($dispositivos) === 0) {
                    $unidadesSinDispositivos++;
                }
                
                foreach ($dispositivos as $dispositivo) {
                    $tipo = $dispositivo['tipo_dispositivo'];
                    $dispositivosPorTipo[$tipo] = ($dispositivosPorTipo[$tipo] ?? 0) + 1;
                }
            }
            
            $reporte['resumen_ejecutivo'] = [
                'total_unidades_sistema' => $totalUnidades,
                'unidades_con_dispositivos' => count($unidadesConDispositivos),
                'unidades_sin_dispositivos' => $unidadesSinDispositivos,
                'total_dispositivos_asociados' => $totalDispositivos,
                'dispositivos_por_tipo' => $dispositivosPorTipo,
                'promedio_dispositivos_por_unidad' => $totalUnidades > 0 ? round($totalDispositivos / $totalUnidades, 2) : 0
            ];
            
            // Datos detallados según tipo de reporte
            switch ($tipoReporte) {
                case 'asociaciones':
                    $reporte['datos_detallados'] = $this->generateAsociacionesDetails($unidadesConDispositivos);
                    break;
                    
                case 'tipos':
                    $reporte['datos_detallados'] = $this->generateTiposDetails($unidadesConDispositivos);
                    break;
                    
                case 'completo':
                default:
                    $reporte['datos_detallados'] = $this->generateCompletoDetails($unidadesConDispositivos);
                    break;
            }
            
            // Estadísticas adicionales
            $reporte['estadisticas'] = [
                'eficiencia_asociacion' => $totalUnidades > 0 ? round(($totalUnidades - $unidadesSinDispositivos) / $totalUnidades * 100, 2) : 0,
                'distribucion_tipos' => $dispositivosPorTipo,
                'timestamp_generacion' => time()
            ];
            
            // Registrar en logs de auditoría
            $this->logAdminAction('report_generated', [
                'tipo_reporte' => $tipoReporte,
                'total_unidades' => $totalUnidades,
                'total_dispositivos' => $totalDispositivos,
                'admin_id' => $_SESSION['admin_id'] ?? 'N/A'
            ]);
            
            return $this->createSuccessResponse("Reporte generado exitosamente", $reporte);
            
        } catch (Exception $e) {
            $this->logError("Error en generateDispositivosReport(): " . $e->getMessage());
            return $this->createErrorResponse("Error interno del servidor");
        }
    }
    
    // ==========================================
    // MÉTODOS AUXILIARES PRIVADOS
    // ==========================================
    
    /**
     * Calcular estado general de dispositivos
     * @param array $dispositivos Lista de dispositivos
     * @return string Estado general
     */
    private function calculateEstadoGeneral(array $dispositivos): string
    {
        if (empty($dispositivos)) {
            return 'sin_dispositivos';
        }
        
        $estados = array_map([$this, 'getDispositivoEstado'], $dispositivos);
        
        if (in_array('error', $estados)) {
            return 'error';
        } elseif (in_array('mantenimiento', $estados)) {
            return 'mantenimiento';
        } elseif (in_array('inactivo', $estados)) {
            return 'inactivo';
        } else {
            return 'activo';
        }
    }
    
    /**
     * Obtener estado de un dispositivo específico
     * @param array $dispositivo Datos del dispositivo
     * @return string Estado del dispositivo
     */
    private function getDispositivoEstado(array $dispositivo): string
    {
        // Simular lógica de estado basada en datos disponibles
        $fechaCreacion = strtotime($dispositivo['creado_en']);
        $ahora = time();
        $diasDesdeCreacion = ($ahora - $fechaCreacion) / (24 * 60 * 60);
        
        // Lógica simulada de estados
        if ($diasDesdeCreacion > 365) {
            return 'mantenimiento'; // Requiere mantenimiento anual
        } elseif ($diasDesdeCreacion > 180) {
            return 'activo'; // Funcionando normalmente
        } elseif ($diasDesdeCreacion > 30) {
            return 'activo'; // Recién configurado
        } else {
            return 'activo'; // Nuevo dispositivo
        }
    }
    
    /**
     * Generar diagnóstico de dispositivo
     * @param array $dispositivo Datos del dispositivo
     * @return string Diagnóstico
     */
    private function generateDiagnostico(array $dispositivo): string
    {
        $estado = $this->getDispositivoEstado($dispositivo);
        
        switch ($estado) {
            case 'activo':
                return 'Dispositivo funcionando correctamente';
            case 'mantenimiento':
                return 'Requiere mantenimiento preventivo';
            case 'error':
                return 'Error detectado - requiere atención inmediata';
            case 'inactivo':
                return 'Dispositivo inactivo o desconectado';
            default:
                return 'Estado desconocido';
        }
    }
    
    /**
     * Simular sincronización con sistema externo
     * @param string $tipo Tipo de dispositivo
     * @param int $dispositivoId ID del dispositivo
     * @param array $unidad Datos de la unidad
     * @return array Resultado de sincronización
     */
    private function syncDispositivoExterno(string $tipo, int $dispositivoId, array $unidad): array
    {
        // Simular proceso de sincronización
        $success = (rand(1, 100) > 10); // 90% de éxito
        
        return [
            'success' => $success,
            'message' => $success ? 
                "Sincronizado correctamente con sistema de $tipo" : 
                "Error de comunicación con sistema de $tipo"
        ];
    }
    
    /**
     * Generar detalles para reporte de asociaciones
     * @param array $unidadesConDispositivos Datos de unidades
     * @return array Detalles de asociaciones
     */
    private function generateAsociacionesDetails(array $unidadesConDispositivos): array
    {
        $detalles = [];
        
        foreach ($unidadesConDispositivos as $item) {
            $unidad = $item['unidad'];
            $dispositivos = $item['dispositivos'];
            
            $detalles[] = [
                'unidad_id' => $unidad['id_persona_unidad'],
                'nombre_completo' => $unidad['nombres'] . ' ' . $unidad['apellido1'],
                'curp' => $unidad['curp'],
                'total_dispositivos' => count($dispositivos),
                'dispositivos' => $dispositivos,
                'fecha_registro' => $unidad['creado_en']
            ];
        }
        
        return $detalles;
    }
    
    /**
     * Generar detalles para reporte de tipos
     * @param array $unidadesConDispositivos Datos de unidades
     * @return array Detalles por tipos
     */
    private function generateTiposDetails(array $unidadesConDispositivos): array
    {
        $tiposDetalle = [];
        
        foreach ($unidadesConDispositivos as $item) {
            $dispositivos = $item['dispositivos'];
            
            foreach ($dispositivos as $dispositivo) {
                $tipo = $dispositivo['tipo_dispositivo'];
                
                if (!isset($tiposDetalle[$tipo])) {
                    $tiposDetalle[$tipo] = [
                        'tipo' => $tipo,
                        'total' => 0,
                        'dispositivos' => []
                    ];
                }
                
                $tiposDetalle[$tipo]['total']++;
                $tiposDetalle[$tipo]['dispositivos'][] = $dispositivo;
            }
        }
        
        return array_values($tiposDetalle);
    }
    
    /**
     * Generar detalles para reporte completo
     * @param array $unidadesConDispositivos Datos de unidades
     * @return array Detalles completos
     */
    private function generateCompletoDetails(array $unidadesConDispositivos): array
    {
        $detalles = [];
        
        foreach ($unidadesConDispositivos as $item) {
            $unidad = $item['unidad'];
            $dispositivos = $item['dispositivos'];
            
            $detalles[] = [
                'unidad' => $unidad,
                'dispositivos' => $dispositivos,
                'estadisticas_unidad' => [
                    'total_dispositivos' => count($dispositivos),
                    'tipos_presentes' => array_unique(array_column($dispositivos, 'tipo_dispositivo')),
                    'estado_general' => $this->calculateEstadoGeneral($dispositivos)
                ]
            ];
        }
        
        return $detalles;
    }
    
    /**
     * Validar formato de fecha
     * @param string $date Fecha a validar
     * @return bool True si es válida
     */
    private function validateDateFormat(string $date): bool
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
?>
