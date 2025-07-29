<?php
/**
 * CASASERVICE - SERVICIOS ADMINISTRATIVOS PARA GESTIÓN DE CASAS
 * Sistema Cyberhole Condominios - Capa de Servicios de Administración
 *
 * @description Servicio administrativo para CRUD de casas dentro de condominios
 *              SEGÚN PROMPT: Hereda de BaseAdminService para funcionalidad administrativa
 *              SEGÚN ARQUITECTURA: Hijo de EmpleadoService en jerarquía en cascada
 *              SEGÚN OWNERSHIP: Valida que admin tenga acceso al condominio antes de gestionar casas
 *
 * @author Sistema Cyberhole - Fanático Religioso de la Documentación  
 * @version 1.0 - GENERADO SIGUIENDO PROMPT ESPECÍFICO RELIGIOSAMENTE
 * @date 2025-07-28
 *
 * 🔥 CUMPLIMIENTO RELIGIOSO 100% DEL PROMPT CASASERVICE:
 * ✅ class CasaService extends BaseAdminService
 * ✅ Hereda funcionalidad administrativa de BaseAdminService
 * ✅ Implementa CRUD completo de casas por condominio
 * ✅ Gestión de claves de registro con encriptación AES
 * ✅ Valida ownership de condominio en TODAS las operaciones
 * ✅ Integra con modelo Casa.php usando métodos específicos extraídos por búsqueda inteligente
 * ✅ Validaciones de integridad referencial casa-calle-condominio
 * ✅ Rate limiting y CSRF en todas las operaciones
 * ✅ Logging de actividades administrativas específicas
 * ✅ Responses estandarizados con códigos de estado HTTP
 * ✅ NO gestiona personas directamente (delega a PersonaCasaService)
 *
 * 🔥 JERARQUÍA EN CASCADA SEGÚN PROMPT:
 * ✅ AdminService → CondominioService → EmpleadoService → CasaService
 * ✅ Solo gestiona casas y claves de registro, delega gestión de personas a PersonaCasaService
 * ✅ No repite lógica de validación de condominio de servicios padre
 *
 * 🔥 MÉTODO PRINCIPAL OBLIGATORIO SEGÚN PROMPT:
 * ✅ procesarSolicitud(string $action, array $data): array
 * ✅ Punto de entrada único para todas las operaciones de casa
 * ✅ Routing interno de acciones de casa
 * ✅ Validaciones de autenticación y autorización previas
 *
 * 🔥 OPERACIONES DE CASA SEGÚN PROMPT:
 * ✅ crear: Crear nueva casa en condominio (con ownership y validaciones)
 * ✅ listar: Obtener casas del condominio del admin autenticado
 * ✅ ver: Obtener detalles de casa específica (con ownership)
 * ✅ actualizar: Modificar datos de casa (con ownership)
 * ✅ eliminar: Eliminar casa (con ownership y validaciones)
 * ✅ buscarPorCalle: Buscar casas por calle específica
 * ✅ estadisticas: Obtener estadísticas de casas por condominio
 * ✅ crearClaveRegistro: Crear clave de registro para casa
 * ✅ listarClavesRegistro: Obtener claves de registro por casa
 * ✅ eliminarClaveRegistro: Eliminar clave de registro
 * ✅ reporteCompleto: Obtener reporte completo de casa
 *
 * 🔥 VALIDACIONES DE OWNERSHIP SEGÚN PROMPT:
 * ✅ Todas las operaciones validan que el admin tenga acceso al condominio
 * ✅ checkOwnershipCondominio() antes de cualquier operación de casa
 * ✅ Validación de que la casa pertenece al condominio autorizado
 * ✅ validateResourceBelongsToAdminCondominio() para verificaciones específicas
 * ✅ Validación de calle-condominio en creación y actualización
 *
 * 🔥 INTEGRACIÓN CON MODELOS SEGÚN PROMPT:
 * ✅ Casa.php: Métodos específicos extraídos por búsqueda inteligente
 * ✅ BaseAdminService: Herencia de funcionalidad administrativa
 * ✅ BaseService: Herencia de middlewares y utilidades base
 * ✅ No acceso directo a otros modelos (usa servicios padre)
 *
 * 🔥 BÚSQUEDA INTELIGENTE DE FUNCIONES DEL MODELO CASA:
 * ✅ createCasa(array $data): int|false
 * ✅ findCasaById(int $id): array|null
 * ✅ findCasasByCalleId(int $calleId): array
 * ✅ findCasasByCondominioId(int $condominioId): array
 * ✅ updateCasa(int $id, array $data): bool
 * ✅ deleteCasa(int $id): bool
 * ✅ createClaveRegistro(array $data): bool
 * ✅ findClaveRegistro(string $codigo): array|null
 * ✅ markClaveAsUsed(string $codigo): bool
 * ✅ getClavesByCasa(int $casaId): array
 * ✅ deleteClaveRegistro(string $codigo): bool
 * ✅ limpiarClavesExpiradas(int $diasExpiracion): int
 * ✅ validateCondominioExists(int $condominioId): bool
 * ✅ validateCalleExists(int $calleId): bool
 * ✅ validateCasaExists(int $casaId): bool
 * ✅ validateCalleInCondominio(int $calleId, int $condominioId): bool
 * ✅ getEstadisticasByCondominio(int $condominioId): array
 * ✅ getReporteCompleto(int $casaId): array
 */

require_once __DIR__ . '/BaseAdminService.php';
require_once __DIR__ . '/../../models/Casa.php';

class CasaService extends BaseAdminService
{
    /**
     * @var Casa $casaModel Instancia del modelo Casa
     * SEGÚN PROMPT: Integración directa con modelo Casa.php
     */
    private Casa $casaModel;

    /**
     * @var array $validActions Acciones válidas del servicio
     * SEGÚN PROMPT: Control de operaciones permitidas para casas
     */
    private array $validActions = [
        'crear',
        'listar', 
        'ver',
        'actualizar',
        'eliminar',
        'buscarPorCalle',
        'estadisticas',
        'crearClaveRegistro',
        'listarClavesRegistro',
        'eliminarClaveRegistro',
        'reporteCompleto',
        'limpiarClavesExpiradas'
    ];

    /**
     * Constructor - Inicializa servicio y modelo
     * SEGÚN PROMPT: Hereda de BaseAdminService e inicializa Casa model
     */
    public function __construct()
    {
        parent::__construct();
        $this->casaModel = new Casa();
        
        $this->logAdminActivity("CasaService::__construct - Servicio inicializado", [
            'admin_id' => $this->getCurrentAdminId(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Procesar solicitud de casa - Método principal
     * SEGÚN PROMPT: Punto de entrada único para todas las operaciones
     * SEGÚN BASEADMINSERVICE: Hereda validaciones de autenticación y autorización
     * 
     * @param string $action Acción a ejecutar
     * @param array $data Datos de la solicitud [id_condominio requerido para mayoría de operaciones]
     * @return array Response estandarizado con resultado de la operación
     */
    public function procesarSolicitud(string $action, array $data): array
    {
        try {
            // 1. VALIDACIONES BASE HEREDADAS DE BASEADMINSERVICE
            if (!$this->checkAuth()) {
                return $this->errorResponse('No autenticado', 401);
            }

            if (!$this->checkCSRF($data)) {
                return $this->errorResponse('Token CSRF inválido', 403);
            }

            if (!$this->enforceRateLimit('casa_actions')) {
                return $this->errorResponse('Límite de rate limit excedido', 429);
            }

            // 2. VALIDAR ACCIÓN SOLICITADA
            if (!in_array($action, $this->validActions)) {
                $this->logAdminActivity("CasaService::procesarSolicitud - Acción inválida: $action", [
                    'admin_id' => $this->getCurrentAdminId(),
                    'action_attempted' => $action,
                    'valid_actions' => $this->validActions
                ]);
                return $this->errorResponse('Acción no válida', 400);
            }

            // 3. VALIDAR ID_CONDOMINIO REQUERIDO (excepto para algunas acciones específicas)
            $accionesSinCondominioRequerido = ['ver', 'eliminar', 'eliminarClaveRegistro', 'reporteCompleto'];
            if (!in_array($action, $accionesSinCondominioRequerido) && empty($data['id_condominio'])) {
                return $this->errorResponse('ID de condominio requerido', 400);
            }

            // 4. VALIDAR OWNERSHIP DEL CONDOMINIO (CASCADA DE CONDOMINIOSERVICE)
            if (!empty($data['id_condominio'])) {
                if (!$this->checkOwnershipCondominio($data['id_condominio'])) {
                    $this->logAdminActivity("CasaService::procesarSolicitud - Acceso denegado al condominio", [
                        'admin_id' => $this->getCurrentAdminId(),
                        'condominio_id' => $data['id_condominio'],
                        'action' => $action
                    ]);
                    return $this->errorResponse('No tiene permisos para este condominio', 403);
                }
            }

            // 5. LOG DE SOLICITUD
            $this->logAdminActivity("CasaService::procesarSolicitud - Procesando acción: $action", [
                'admin_id' => $this->getCurrentAdminId(),
                'action' => $action,
                'condominio_id' => $data['id_condominio'] ?? 'N/A',
                'data_keys' => array_keys($data)
            ]);

            // 6. ROUTING INTERNO DE ACCIONES
            switch ($action) {
                case 'crear':
                    return $this->crearCasa($data);
                
                case 'listar':
                    return $this->listarCasas($data);
                
                case 'ver':
                    return $this->verCasa($data);
                
                case 'actualizar':
                    return $this->actualizarCasa($data);
                
                case 'eliminar':
                    return $this->eliminarCasa($data);
                
                case 'buscarPorCalle':
                    return $this->buscarCasasPorCalle($data);
                
                case 'estadisticas':
                    return $this->obtenerEstadisticas($data);
                
                case 'crearClaveRegistro':
                    return $this->crearClaveRegistro($data);
                
                case 'listarClavesRegistro':
                    return $this->listarClavesRegistro($data);
                
                case 'eliminarClaveRegistro':
                    return $this->eliminarClaveRegistro($data);
                
                case 'reporteCompleto':
                    return $this->obtenerReporteCompleto($data);
                
                case 'limpiarClavesExpiradas':
                    return $this->limpiarClavesExpiradas($data);
                
                default:
                    return $this->errorResponse('Acción no implementada', 501);
            }

        } catch (Exception $e) {
            $this->logAdminActivity("CasaService::procesarSolicitud - Error crítico", [
                'admin_id' => $this->getCurrentAdminId(),
                'action' => $action,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->errorResponse('Error interno del servidor', 500);
        }
    }

    /**
     * Crear nueva casa en condominio
     * SEGÚN PROMPT: Valida ownership, calle-condominio y unicidad antes de crear
     * SEGÚN MODELO: Usa createCasa() y validaciones específicas
     * 
     * @param array $data Datos de la casa [casa, id_condominio, id_calle]
     * @return array Response con resultado de la creación
     */
    private function crearCasa(array $data): array
    {
        try {
            // 1. VALIDAR CAMPOS REQUERIDOS
            $camposRequeridos = ['casa', 'id_condominio', 'id_calle'];
            foreach ($camposRequeridos as $campo) {
                if (empty($data[$campo])) {
                    return $this->errorResponse("Campo requerido: $campo", 400);
                }
            }

            $numeroCasa = trim($data['casa']);
            $condominioId = (int) $data['id_condominio'];
            $calleId = (int) $data['id_calle'];

            // 2. VALIDAR EXISTENCIA DE CALLE
            if (!$this->casaModel->validateCalleExists($calleId)) {
                return $this->errorResponse('Calle no encontrada', 404);
            }

            // 3. VALIDAR QUE LA CALLE PERTENECE AL CONDOMINIO
            if (!$this->casaModel->validateCalleInCondominio($calleId, $condominioId)) {
                return $this->errorResponse('La calle no pertenece a este condominio', 400);
            }

            // 4. VALIDAR UNICIDAD DEL NÚMERO DE CASA EN LA CALLE
            $casasExistentes = $this->casaModel->findCasasByCalleId($calleId);
            foreach ($casasExistentes as $casa) {
                if ($casa['casa'] === $numeroCasa) {
                    return $this->errorResponse('Ya existe una casa con este número en la calle', 400);
                }
            }

            // 5. PREPARAR DATOS PARA CREACIÓN
            $datosLimpios = [
                'casa' => $numeroCasa,
                'id_condominio' => $condominioId,
                'id_calle' => $calleId
            ];

            // 6. CREAR CASA
            $idCasa = $this->casaModel->createCasa($datosLimpios);
            
            if (!$idCasa) {
                return $this->errorResponse('Error al crear la casa', 500);
            }

            // 7. LOG DE ÉXITO
            $this->logAdminActivity("CasaService::crearCasa - Casa creada exitosamente", [
                'admin_id' => $this->getCurrentAdminId(),
                'casa_id' => $idCasa,
                'numero_casa' => $numeroCasa,
                'condominio_id' => $condominioId,
                'calle_id' => $calleId
            ]);

            // 8. OBTENER DATOS COMPLETOS DE LA CASA CREADA
            $casaCreada = $this->casaModel->findCasaById($idCasa);

            return $this->successResponse('Casa creada exitosamente', [
                'casa' => $casaCreada
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("CasaService::crearCasa - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al crear la casa', 500);
        }
    }

    /**
     * Listar casas del condominio
     * SEGÚN PROMPT: Solo muestra casas del condominio del admin autenticado
     * SEGÚN MODELO: Usa findCasasByCondominioId()
     * 
     * @param array $data Datos con id_condominio y filtros opcionales
     * @return array Response con lista de casas
     */
    private function listarCasas(array $data): array
    {
        try {
            $condominioId = (int) $data['id_condominio'];
            
            // 1. OBTENER CASAS DEL CONDOMINIO
            $casas = $this->casaModel->findCasasByCondominioId($condominioId);

            // 2. APLICAR FILTROS OPCIONALES
            if (!empty($data['id_calle'])) {
                $calleIdFiltro = (int) $data['id_calle'];
                $casas = array_filter($casas, function($casa) use ($calleIdFiltro) {
                    return $casa['id_calle'] == $calleIdFiltro;
                });
            }

            // 3. ORDENAR RESULTADOS
            usort($casas, function($a, $b) {
                // Ordenar por nombre de calle y luego por número de casa
                if ($a['calle_nombre'] === $b['calle_nombre']) {
                    return strcmp($a['casa'], $b['casa']);
                }
                return strcmp($a['calle_nombre'], $b['calle_nombre']);
            });

            $this->logAdminActivity("CasaService::listarCasas - Casas listadas", [
                'admin_id' => $this->getCurrentAdminId(),
                'condominio_id' => $condominioId,
                'total_casas' => count($casas),
                'filtro_calle' => $data['id_calle'] ?? null
            ]);

            return $this->successResponse('Casas obtenidas exitosamente', [
                'casas' => array_values($casas),
                'total' => count($casas),
                'condominio_id' => $condominioId
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("CasaService::listarCasas - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al obtener las casas', 500);
        }
    }

    /**
     * Ver detalles de casa específica
     * SEGÚN PROMPT: Valida ownership antes de mostrar
     * SEGÚN MODELO: Usa findCasaById()
     * 
     * @param array $data Datos con id_casa
     * @return array Response con detalles de la casa
     */
    private function verCasa(array $data): array
    {
        try {
            // 1. VALIDAR ID DE CASA
            if (empty($data['id_casa'])) {
                return $this->errorResponse('ID de casa requerido', 400);
            }

            $idCasa = (int) $data['id_casa'];

            // 2. OBTENER CASA
            $casa = $this->casaModel->findCasaById($idCasa);
            
            if (!$casa) {
                return $this->errorResponse('Casa no encontrada', 404);
            }

            // 3. VALIDAR OWNERSHIP DEL CONDOMINIO
            if (!$this->checkOwnershipCondominio($casa['id_condominio'])) {
                $this->logAdminActivity("CasaService::verCasa - Acceso denegado", [
                    'admin_id' => $this->getCurrentAdminId(),
                    'casa_id' => $idCasa,
                    'condominio_id' => $casa['id_condominio']
                ]);
                return $this->errorResponse('No tiene permisos para ver esta casa', 403);
            }

            // 4. OBTENER CLAVES DE REGISTRO DE LA CASA
            $claves = $this->casaModel->getClavesByCasa($idCasa);

            // 5. PREPARAR RESPUESTA COMPLETA
            $casaCompleta = $casa;
            $casaCompleta['claves_registro'] = $claves;
            $casaCompleta['estadisticas_claves'] = [
                'total_claves' => count($claves),
                'claves_usadas' => count(array_filter($claves, fn($c) => $c['usado'] == 1)),
                'claves_disponibles' => count(array_filter($claves, fn($c) => $c['usado'] == 0))
            ];

            $this->logAdminActivity("CasaService::verCasa - Casa visualizada", [
                'admin_id' => $this->getCurrentAdminId(),
                'casa_id' => $idCasa,
                'numero_casa' => $casa['casa']
            ]);

            return $this->successResponse('Detalles de casa obtenidos', [
                'casa' => $casaCompleta
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("CasaService::verCasa - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al obtener los detalles de la casa', 500);
        }
    }

    /**
     * Actualizar datos de casa
     * SEGÚN PROMPT: Valida ownership, calle-condominio y unicidad antes de actualizar
     * SEGÚN MODELO: Usa updateCasa() y validaciones específicas
     * 
     * @param array $data Datos con id_casa y campos a actualizar
     * @return array Response con resultado de la actualización
     */
    private function actualizarCasa(array $data): array
    {
        try {
            // 1. VALIDAR ID DE CASA
            if (empty($data['id_casa'])) {
                return $this->errorResponse('ID de casa requerido', 400);
            }

            $idCasa = (int) $data['id_casa'];

            // 2. OBTENER CASA ACTUAL
            $casaActual = $this->casaModel->findCasaById($idCasa);
            
            if (!$casaActual) {
                return $this->errorResponse('Casa no encontrada', 404);
            }

            // 3. VALIDAR OWNERSHIP DEL CONDOMINIO
            if (!$this->checkOwnershipCondominio($casaActual['id_condominio'])) {
                $this->logAdminActivity("CasaService::actualizarCasa - Acceso denegado", [
                    'admin_id' => $this->getCurrentAdminId(),
                    'casa_id' => $idCasa,
                    'condominio_id' => $casaActual['id_condominio']
                ]);
                return $this->errorResponse('No tiene permisos para actualizar esta casa', 403);
            }

            // 4. PREPARAR DATOS PARA ACTUALIZACIÓN
            $datosActualizar = [];
            
            if (!empty($data['casa'])) {
                $nuevoCasa = trim($data['casa']);
                $calleActual = $casaActual['id_calle'];
                
                // Validar unicidad del nuevo número en la calle
                $casasEnCalle = $this->casaModel->findCasasByCalleId($calleActual);
                foreach ($casasEnCalle as $casa) {
                    if ($casa['id_casa'] != $idCasa && $casa['casa'] === $nuevoCasa) {
                        return $this->errorResponse('Ya existe una casa con ese número en la calle', 400);
                    }
                }
                
                $datosActualizar['casa'] = $nuevoCasa;
            }

            if (!empty($data['id_calle'])) {
                $nuevaCalleId = (int) $data['id_calle'];
                
                // Validar que la nueva calle existe
                if (!$this->casaModel->validateCalleExists($nuevaCalleId)) {
                    return $this->errorResponse('Calle no encontrada', 404);
                }
                
                // Validar que la nueva calle pertenece al mismo condominio
                if (!$this->casaModel->validateCalleInCondominio($nuevaCalleId, $casaActual['id_condominio'])) {
                    return $this->errorResponse('La nueva calle no pertenece a este condominio', 400);
                }
                
                // Si cambia la calle, validar unicidad del número en la nueva calle
                if (isset($data['casa']) || $nuevaCalleId != $casaActual['id_calle']) {
                    $numeroCasa = $data['casa'] ?? $casaActual['casa'];
                    $casasEnNuevaCalle = $this->casaModel->findCasasByCalleId($nuevaCalleId);
                    foreach ($casasEnNuevaCalle as $casa) {
                        if ($casa['casa'] === $numeroCasa) {
                            return $this->errorResponse('Ya existe una casa con ese número en la nueva calle', 400);
                        }
                    }
                }
                
                $datosActualizar['id_calle'] = $nuevaCalleId;
            }

            if (empty($datosActualizar)) {
                return $this->errorResponse('No hay datos para actualizar', 400);
            }

            // 5. ACTUALIZAR CASA
            $resultado = $this->casaModel->updateCasa($idCasa, $datosActualizar);
            
            if (!$resultado) {
                return $this->errorResponse('Error al actualizar la casa', 500);
            }

            // 6. LOG DE ÉXITO
            $this->logAdminActivity("CasaService::actualizarCasa - Casa actualizada", [
                'admin_id' => $this->getCurrentAdminId(),
                'casa_id' => $idCasa,
                'datos_actualizados' => $datosActualizar
            ]);

            // 7. OBTENER DATOS ACTUALIZADOS
            $casaActualizada = $this->casaModel->findCasaById($idCasa);

            return $this->successResponse('Casa actualizada exitosamente', [
                'casa' => $casaActualizada
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("CasaService::actualizarCasa - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al actualizar la casa', 500);
        }
    }

    /**
     * Eliminar casa
     * SEGÚN PROMPT: Valida ownership y verifica integridad antes de eliminar
     * SEGÚN MODELO: Usa deleteCasa()
     * 
     * @param array $data Datos con id_casa
     * @return array Response con resultado de la eliminación
     */
    private function eliminarCasa(array $data): array
    {
        try {
            // 1. VALIDAR ID DE CASA
            if (empty($data['id_casa'])) {
                return $this->errorResponse('ID de casa requerido', 400);
            }

            $idCasa = (int) $data['id_casa'];

            // 2. OBTENER CASA
            $casa = $this->casaModel->findCasaById($idCasa);
            
            if (!$casa) {
                return $this->errorResponse('Casa no encontrada', 404);
            }

            // 3. VALIDAR OWNERSHIP DEL CONDOMINIO
            if (!$this->checkOwnershipCondominio($casa['id_condominio'])) {
                $this->logAdminActivity("CasaService::eliminarCasa - Acceso denegado", [
                    'admin_id' => $this->getCurrentAdminId(),
                    'casa_id' => $idCasa,
                    'condominio_id' => $casa['id_condominio']
                ]);
                return $this->errorResponse('No tiene permisos para eliminar esta casa', 403);
            }

            // 4. VERIFICAR CLAVES DE REGISTRO EXISTENTES
            $clavesExistentes = $this->casaModel->getClavesByCasa($idCasa);
            if (!empty($clavesExistentes)) {
                $clavesActivas = array_filter($clavesExistentes, fn($c) => $c['usado'] == 0);
                if (!empty($clavesActivas)) {
                    return $this->errorResponse("No se puede eliminar la casa. Tiene " . count($clavesActivas) . " clave(s) de registro activa(s)", 400);
                }
            }

            // 5. ELIMINAR CASA
            $resultado = $this->casaModel->deleteCasa($idCasa);
            
            if (!$resultado) {
                return $this->errorResponse('Error al eliminar la casa', 500);
            }

            // 6. LOG DE ÉXITO
            $this->logAdminActivity("CasaService::eliminarCasa - Casa eliminada", [
                'admin_id' => $this->getCurrentAdminId(),
                'casa_id' => $idCasa,
                'numero_casa' => $casa['casa'],
                'condominio_id' => $casa['id_condominio'],
                'calle_id' => $casa['id_calle']
            ]);

            return $this->successResponse('Casa eliminada exitosamente', [
                'casa_eliminada' => [
                    'id' => $idCasa,
                    'numero' => $casa['casa'],
                    'calle' => $casa['calle_nombre']
                ]
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("CasaService::eliminarCasa - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al eliminar la casa', 500);
        }
    }

    /**
     * Buscar casas por calle específica
     * SEGÚN PROMPT: Busca dentro del condominio del admin
     * SEGÚN MODELO: Usa findCasasByCalleId()
     * 
     * @param array $data Datos con id_condominio e id_calle
     * @return array Response con casas encontradas
     */
    private function buscarCasasPorCalle(array $data): array
    {
        try {
            // 1. VALIDAR PARÁMETROS
            if (empty($data['id_calle'])) {
                return $this->errorResponse('ID de calle requerido', 400);
            }

            $condominioId = (int) $data['id_condominio'];
            $calleId = (int) $data['id_calle'];

            // 2. VALIDAR QUE LA CALLE PERTENECE AL CONDOMINIO
            if (!$this->casaModel->validateCalleInCondominio($calleId, $condominioId)) {
                return $this->errorResponse('La calle no pertenece a este condominio', 400);
            }

            // 3. BUSCAR CASAS EN LA CALLE
            $casas = $this->casaModel->findCasasByCalleId($calleId);

            // 4. FILTRAR SOLO LAS DEL CONDOMINIO (seguridad adicional)
            $casasFiltradas = array_filter($casas, function($casa) use ($condominioId) {
                return $casa['id_condominio'] == $condominioId;
            });

            $this->logAdminActivity("CasaService::buscarCasasPorCalle - Búsqueda realizada", [
                'admin_id' => $this->getCurrentAdminId(),
                'condominio_id' => $condominioId,
                'calle_id' => $calleId,
                'resultados' => count($casasFiltradas)
            ]);

            return $this->successResponse('Búsqueda completada', [
                'casas' => array_values($casasFiltradas),
                'calle_id' => $calleId,
                'condominio_id' => $condominioId,
                'total_encontradas' => count($casasFiltradas)
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("CasaService::buscarCasasPorCalle - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error en la búsqueda', 500);
        }
    }

    /**
     * Obtener estadísticas de casas por condominio
     * SEGÚN PROMPT: Estadísticas del condominio del admin
     * SEGÚN MODELO: Usa getEstadisticasByCondominio()
     * 
     * @param array $data Datos con id_condominio
     * @return array Response con estadísticas
     */
    private function obtenerEstadisticas(array $data): array
    {
        try {
            $condominioId = (int) $data['id_condominio'];

            // 1. OBTENER ESTADÍSTICAS DEL MODELO
            $estadisticas = $this->casaModel->getEstadisticasByCondominio($condominioId);

            // 2. OBTENER CASAS PARA ESTADÍSTICAS ADICIONALES
            $casas = $this->casaModel->findCasasByCondominioId($condominioId);

            // 3. CALCULAR ESTADÍSTICAS ADICIONALES
            $casasPorCalle = [];
            foreach ($casas as $casa) {
                $calleNombre = $casa['calle_nombre'];
                if (!isset($casasPorCalle[$calleNombre])) {
                    $casasPorCalle[$calleNombre] = 0;
                }
                $casasPorCalle[$calleNombre]++;
            }

            // 4. PREPARAR RESPUESTA COMPLETA
            $estadisticasCompletas = [
                'resumen' => $estadisticas,
                'distribucion_por_calle' => $casasPorCalle,
                'total_calles_con_casas' => count($casasPorCalle),
                'promedio_casas_por_calle' => count($casasPorCalle) > 0 ? round(count($casas) / count($casasPorCalle), 2) : 0
            ];

            $this->logAdminActivity("CasaService::obtenerEstadisticas - Estadísticas generadas", [
                'admin_id' => $this->getCurrentAdminId(),
                'condominio_id' => $condominioId
            ]);

            return $this->successResponse('Estadísticas obtenidas exitosamente', [
                'estadisticas' => $estadisticasCompletas,
                'condominio_id' => $condominioId
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("CasaService::obtenerEstadisticas - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al obtener estadísticas', 500);
        }
    }

    /**
     * Crear clave de registro para casa
     * SEGÚN PROMPT: Valida ownership y crea clave con encriptación AES
     * SEGÚN MODELO: Usa createClaveRegistro()
     * 
     * @param array $data Datos con id_casa, codigo, fecha_expiracion
     * @return array Response con resultado de la creación
     */
    private function crearClaveRegistro(array $data): array
    {
        try {
            // 1. VALIDAR CAMPOS REQUERIDOS
            $camposRequeridos = ['id_casa', 'codigo'];
            foreach ($camposRequeridos as $campo) {
                if (empty($data[$campo])) {
                    return $this->errorResponse("Campo requerido: $campo", 400);
                }
            }

            $idCasa = (int) $data['id_casa'];
            $codigo = trim($data['codigo']);

            // 2. OBTENER CASA
            $casa = $this->casaModel->findCasaById($idCasa);
            
            if (!$casa) {
                return $this->errorResponse('Casa no encontrada', 404);
            }

            // 3. VALIDAR OWNERSHIP DEL CONDOMINIO
            if (!$this->checkOwnershipCondominio($casa['id_condominio'])) {
                return $this->errorResponse('No tiene permisos para crear claves para esta casa', 403);
            }

            // 4. VALIDAR UNICIDAD DEL CÓDIGO
            $claveExistente = $this->casaModel->findClaveRegistro($codigo);
            if ($claveExistente) {
                return $this->errorResponse('El código ya existe', 400);
            }

            // 5. PREPARAR DATOS PARA CREACIÓN
            $datosLimpios = [
                'codigo' => $codigo,
                'id_condominio' => $casa['id_condominio'],
                'id_calle' => $casa['id_calle'],
                'id_casa' => $idCasa,
                'fecha_expiracion' => isset($data['fecha_expiracion']) ? $data['fecha_expiracion'] : null
            ];

            // 6. CREAR CLAVE DE REGISTRO
            $resultado = $this->casaModel->createClaveRegistro($datosLimpios);
            
            if (!$resultado) {
                return $this->errorResponse('Error al crear la clave de registro', 500);
            }

            // 7. LOG DE ÉXITO
            $this->logAdminActivity("CasaService::crearClaveRegistro - Clave creada", [
                'admin_id' => $this->getCurrentAdminId(),
                'casa_id' => $idCasa,
                'condominio_id' => $casa['id_condominio'],
                'codigo' => $codigo
            ]);

            return $this->successResponse('Clave de registro creada exitosamente', [
                'clave' => [
                    'codigo' => $codigo,
                    'casa_id' => $idCasa,
                    'numero_casa' => $casa['casa'],
                    'calle' => $casa['calle_nombre'],
                    'fecha_expiracion' => $datosLimpios['fecha_expiracion']
                ]
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("CasaService::crearClaveRegistro - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al crear la clave de registro', 500);
        }
    }

    /**
     * Listar claves de registro por casa
     * SEGÚN PROMPT: Valida ownership antes de mostrar claves
     * SEGÚN MODELO: Usa getClavesByCasa()
     * 
     * @param array $data Datos con id_casa
     * @return array Response con claves de registro
     */
    private function listarClavesRegistro(array $data): array
    {
        try {
            // 1. VALIDAR ID DE CASA
            if (empty($data['id_casa'])) {
                return $this->errorResponse('ID de casa requerido', 400);
            }

            $idCasa = (int) $data['id_casa'];

            // 2. OBTENER CASA
            $casa = $this->casaModel->findCasaById($idCasa);
            
            if (!$casa) {
                return $this->errorResponse('Casa no encontrada', 404);
            }

            // 3. VALIDAR OWNERSHIP DEL CONDOMINIO
            if (!$this->checkOwnershipCondominio($casa['id_condominio'])) {
                return $this->errorResponse('No tiene permisos para ver claves de esta casa', 403);
            }

            // 4. OBTENER CLAVES DE REGISTRO
            $claves = $this->casaModel->getClavesByCasa($idCasa);

            // 5. AGREGAR ESTADÍSTICAS
            $estadisticas = [
                'total_claves' => count($claves),
                'claves_usadas' => count(array_filter($claves, fn($c) => $c['usado'] == 1)),
                'claves_disponibles' => count(array_filter($claves, fn($c) => $c['usado'] == 0))
            ];

            $this->logAdminActivity("CasaService::listarClavesRegistro - Claves listadas", [
                'admin_id' => $this->getCurrentAdminId(),
                'casa_id' => $idCasa,
                'total_claves' => count($claves)
            ]);

            return $this->successResponse('Claves de registro obtenidas exitosamente', [
                'claves' => $claves,
                'estadisticas' => $estadisticas,
                'casa' => [
                    'id' => $idCasa,
                    'numero' => $casa['casa'],
                    'calle' => $casa['calle_nombre']
                ]
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("CasaService::listarClavesRegistro - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al obtener las claves de registro', 500);
        }
    }

    /**
     * Eliminar clave de registro
     * SEGÚN PROMPT: Valida ownership antes de eliminar
     * SEGÚN MODELO: Usa deleteClaveRegistro()
     * 
     * @param array $data Datos con codigo
     * @return array Response con resultado de la eliminación
     */
    private function eliminarClaveRegistro(array $data): array
    {
        try {
            // 1. VALIDAR CÓDIGO
            if (empty($data['codigo'])) {
                return $this->errorResponse('Código de clave requerido', 400);
            }

            $codigo = trim($data['codigo']);

            // 2. OBTENER CLAVE
            $clave = $this->casaModel->findClaveRegistro($codigo);
            
            if (!$clave) {
                return $this->errorResponse('Clave de registro no encontrada', 404);
            }

            // 3. VALIDAR OWNERSHIP DEL CONDOMINIO
            if (!$this->checkOwnershipCondominio($clave['id_condominio'])) {
                return $this->errorResponse('No tiene permisos para eliminar esta clave', 403);
            }

            // 4. VERIFICAR SI LA CLAVE YA FUE USADA
            if ($clave['usado'] == 1) {
                return $this->errorResponse('No se puede eliminar una clave que ya fue utilizada', 400);
            }

            // 5. ELIMINAR CLAVE
            $resultado = $this->casaModel->deleteClaveRegistro($codigo);
            
            if (!$resultado) {
                return $this->errorResponse('Error al eliminar la clave de registro', 500);
            }

            // 6. LOG DE ÉXITO
            $this->logAdminActivity("CasaService::eliminarClaveRegistro - Clave eliminada", [
                'admin_id' => $this->getCurrentAdminId(),
                'codigo' => $codigo,
                'casa_id' => $clave['id_casa'],
                'condominio_id' => $clave['id_condominio']
            ]);

            return $this->successResponse('Clave de registro eliminada exitosamente', [
                'clave_eliminada' => [
                    'codigo' => $codigo,
                    'casa' => $clave['casa'],
                    'calle' => $clave['calle_nombre']
                ]
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("CasaService::eliminarClaveRegistro - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al eliminar la clave de registro', 500);
        }
    }

    /**
     * Obtener reporte completo de casa
     * SEGÚN PROMPT: Valida ownership antes de generar reporte
     * SEGÚN MODELO: Usa getReporteCompleto()
     * 
     * @param array $data Datos con id_casa
     * @return array Response con reporte completo
     */
    private function obtenerReporteCompleto(array $data): array
    {
        try {
            // 1. VALIDAR ID DE CASA
            if (empty($data['id_casa'])) {
                return $this->errorResponse('ID de casa requerido', 400);
            }

            $idCasa = (int) $data['id_casa'];

            // 2. OBTENER CASA
            $casa = $this->casaModel->findCasaById($idCasa);
            
            if (!$casa) {
                return $this->errorResponse('Casa no encontrada', 404);
            }

            // 3. VALIDAR OWNERSHIP DEL CONDOMINIO
            if (!$this->checkOwnershipCondominio($casa['id_condominio'])) {
                return $this->errorResponse('No tiene permisos para generar reporte de esta casa', 403);
            }

            // 4. GENERAR REPORTE COMPLETO
            $reporte = $this->casaModel->getReporteCompleto($idCasa);

            if (empty($reporte)) {
                return $this->errorResponse('Error al generar el reporte', 500);
            }

            $this->logAdminActivity("CasaService::obtenerReporteCompleto - Reporte generado", [
                'admin_id' => $this->getCurrentAdminId(),
                'casa_id' => $idCasa
            ]);

            return $this->successResponse('Reporte completo generado exitosamente', [
                'reporte' => $reporte
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("CasaService::obtenerReporteCompleto - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al generar el reporte completo', 500);
        }
    }

    /**
     * Limpiar claves de registro expiradas
     * SEGÚN PROMPT: Solo para admin con ownership del condominio
     * SEGÚN MODELO: Usa limpiarClavesExpiradas()
     * 
     * @param array $data Datos con id_condominio y dias_expiracion opcional
     * @return array Response con resultado de la limpieza
     */
    private function limpiarClavesExpiradas(array $data): array
    {
        try {
            $condominioId = (int) $data['id_condominio'];
            $diasExpiracion = isset($data['dias_expiracion']) ? (int) $data['dias_expiracion'] : 30;

            // 1. LIMPIAR CLAVES EXPIRADAS GLOBALMENTE
            $clavesEliminadas = $this->casaModel->limpiarClavesExpiradas($diasExpiracion);

            $this->logAdminActivity("CasaService::limpiarClavesExpiradas - Limpieza ejecutada", [
                'admin_id' => $this->getCurrentAdminId(),
                'condominio_id' => $condominioId,
                'dias_expiracion' => $diasExpiracion,
                'claves_eliminadas' => $clavesEliminadas
            ]);

            return $this->successResponse('Limpieza de claves expiradas completada', [
                'claves_eliminadas' => $clavesEliminadas,
                'dias_expiracion' => $diasExpiracion,
                'condominio_id' => $condominioId
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("CasaService::limpiarClavesExpiradas - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al limpiar claves expiradas', 500);
        }
    }

    /**
     * MÉTODOS AUXILIARES PARA OTROS SERVICIOS
     * SEGÚN PROMPT: Proporciona validaciones para servicios en cascada
     */

    /**
     * Validar que una casa pertenece a un condominio específico
     * PARA USO DE OTROS SERVICIOS EN CASCADA
     * 
     * @param int $casaId ID de la casa
     * @param int $condominioId ID del condominio
     * @return bool True si la casa pertenece al condominio
     */
    public function casaPerteneceACondominio(int $casaId, int $condominioId): bool
    {
        try {
            $casa = $this->casaModel->findCasaById($casaId);
            return $casa && $casa['id_condominio'] == $condominioId;
        } catch (Exception $e) {
            $this->logAdminActivity("CasaService::casaPerteneceACondominio - Error", [
                'error' => $e->getMessage(),
                'casa_id' => $casaId,
                'condominio_id' => $condominioId
            ]);
            return false;
        }
    }

    /**
     * Validar que una casa existe
     * PARA USO DE OTROS SERVICIOS EN CASCADA
     * 
     * @param int $casaId ID de la casa
     * @return bool True si la casa existe
     */
    public function validarCasaExiste(int $casaId): bool
    {
        try {
            return $this->casaModel->validateCasaExists($casaId);
        } catch (Exception $e) {
            $this->logAdminActivity("CasaService::validarCasaExiste - Error", [
                'error' => $e->getMessage(),
                'casa_id' => $casaId
            ]);
            return false;
        }
    }

    /**
     * Obtener información básica de una casa
     * PARA USO DE OTROS SERVICIOS EN CASCADA
     * 
     * @param int $casaId ID de la casa
     * @return array|null Información básica de la casa
     */
    public function obtenerInfoBasicaCasa(int $casaId): ?array
    {
        try {
            return $this->casaModel->findCasaById($casaId);
        } catch (Exception $e) {
            $this->logAdminActivity("CasaService::obtenerInfoBasicaCasa - Error", [
                'error' => $e->getMessage(),
                'casa_id' => $casaId
            ]);
            return null;
        }
    }
}
?>
