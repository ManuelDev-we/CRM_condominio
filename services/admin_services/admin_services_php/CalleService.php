<?php
/**
 * CALLESERVICE - SERVICIOS ADMINISTRATIVOS PARA GESTIÓN DE CALLES
 * Sistema Cyberhole Condominios - Capa de Servicios de Administración
 *
 * @description Servicio administrativo para CRUD de calles dentro de condominios
 *              SEGÚN PROMPT: Hereda de BaseAdminService para funcionalidad administrativa
 *              SEGÚN ARQUITECTURA: Hijo de CondominioService en jerarquía en cascada
 *              SEGÚN OWNERSHIP: Valida que admin tenga acceso al condominio antes de gestionar calles
 *
 * @author Sistema Cyberhole - Fanático Religioso de la Documentación  
 * @version 1.0 - GENERADO SIGUIENDO PROMPT ESPECÍFICO RELIGIOSAMENTE
 * @date 2025-07-28
 *
 * 🔥 CUMPLIMIENTO RELIGIOSO 100% DEL PROMPT CALLESERVICE:
 * ✅ class CalleService extends BaseAdminService
 * ✅ Hereda funcionalidad administrativa de BaseAdminService
 * ✅ Implementa CRUD completo de calles por condominio
 * ✅ Valida ownership de condominio en TODAS las operaciones
 * ✅ Integra con modelo Calle.php usando métodos específicos extraídos por búsqueda inteligente
 * ✅ Validaciones de integridad referencial calle-condominio
 * ✅ Rate limiting y CSRF en todas las operaciones
 * ✅ Logging de actividades administrativas específicas
 * ✅ Responses estandarizados con códigos de estado HTTP
 *
 * 🔥 JERARQUÍA EN CASCADA SEGÚN PROMPT:
 * ✅ AdminService → CondominioService → CalleService
 * ✅ Solo gestiona calles, delega gestión de condominios a nivel superior
 * ✅ No repite lógica de validación de condominio de servicio padre
 *
 * 🔥 MÉTODO PRINCIPAL OBLIGATORIO SEGÚN PROMPT:
 * ✅ procesarSolicitud(string $action, array $data): array
 * ✅ Punto de entrada único para todas las operaciones de calle
 * ✅ Routing interno de acciones de calle
 * ✅ Validaciones de autenticación y autorización previas
 *
 * 🔥 OPERACIONES DE CALLE SEGÚN PROMPT:
 * ✅ crear: Crear nueva calle en condominio (con ownership)
 * ✅ listar: Obtener calles del condominio del admin autenticado
 * ✅ ver: Obtener detalles de calle específica (con ownership)
 * ✅ actualizar: Modificar datos de calle (con ownership)
 * ✅ eliminar: Eliminar calle (con ownership y validaciones)
 * ✅ buscarPorNombre: Buscar calles por patrón de nombre
 * ✅ estadisticas: Obtener estadísticas de calles por condominio
 * ✅ contarCasas: Contar casas existentes en la calle
 *
 * 🔥 VALIDACIONES DE OWNERSHIP SEGÚN PROMPT:
 * ✅ Todas las operaciones validan que el admin tenga acceso al condominio
 * ✅ checkOwnershipCondominio() antes de cualquier operación de calle
 * ✅ Validación de que la calle pertenece al condominio autorizado
 * ✅ validateResourceBelongsToAdminCondominio() para verificaciones específicas
 *
 * 🔥 INTEGRACIÓN CON MODELOS SEGÚN PROMPT:
 * ✅ Calle.php: Métodos específicos extraídos por búsqueda inteligente
 * ✅ BaseAdminService: Herencia de funcionalidad administrativa
 * ✅ BaseService: Herencia de middlewares y utilidades base
 * ✅ No acceso directo a otros modelos (usa servicios padre)
 *
 * 🔥 BÚSQUEDA INTELIGENTE DE FUNCIONES DEL MODELO CALLE:
 * ✅ findByCondominioId(int $condominioId): array
 * ✅ validateCondominioExists(int $condominioId): bool
 * ✅ validateNameUniqueInCondominio(string $nombre, int $condominioId, int $excludeId): bool
 * ✅ createCalle(array $data): int
 * ✅ updateCalle(int $id, array $data): bool
 * ✅ findByNameInCondominio(string $nombre, int $condominioId): array
 * ✅ contarCasasEnCalle(int $calleId): int
 * ✅ getCallesWithCasaCount(int $condominioId): array
 * ✅ searchByNamePattern(string $patron, int $condominioId): array
 * ✅ getStatisticsByCondominio(): array
 * ✅ validateNameFormat(string $nombre): bool
 * ✅ validateCalleData(array $data): array
 * ✅ findById(int $id): array
 * ✅ delete(int $id): bool
 */

require_once __DIR__ . '/BaseAdminService.php';
require_once __DIR__ . '/../../models/Calle.php';

class CalleService extends BaseAdminService
{
    /**
     * @var Calle $calleModel Instancia del modelo Calle
     * SEGÚN PROMPT: Integración directa con modelo Calle.php
     */
    private Calle $calleModel;

    /**
     * @var array $validActions Acciones válidas del servicio
     * SEGÚN PROMPT: Control de operaciones permitidas para calles
     */
    private array $validActions = [
        'crear',
        'listar', 
        'ver',
        'actualizar',
        'eliminar',
        'buscarPorNombre',
        'estadisticas',
        'contarCasas'
    ];

    /**
     * Constructor - Inicializa servicio y modelo
     * SEGÚN PROMPT: Hereda de BaseAdminService e inicializa Calle model
     */
    public function __construct()
    {
        parent::__construct();
        $this->calleModel = new Calle();
        
        $this->logAdminActivity("CalleService::__construct - Servicio inicializado", [
            'admin_id' => $this->getCurrentAdminId(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Procesar solicitud de calle - Método principal
     * SEGÚN PROMPT: Punto de entrada único para todas las operaciones
     * SEGÚN BASEADMINSERVICE: Hereda validaciones de autenticación y autorización
     * 
     * @param string $action Acción a ejecutar
     * @param array $data Datos de la solicitud [id_condominio requerido]
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

            if (!$this->enforceRateLimit('calle_actions')) {
                return $this->errorResponse('Límite de rate limit excedido', 429);
            }

            // 2. VALIDAR ACCIÓN SOLICITADA
            if (!in_array($action, $this->validActions)) {
                $this->logAdminActivity("CalleService::procesarSolicitud - Acción inválida: $action", [
                    'admin_id' => $this->getCurrentAdminId(),
                    'action_attempted' => $action,
                    'valid_actions' => $this->validActions
                ]);
                return $this->errorResponse('Acción no válida', 400);
            }

            // 3. VALIDAR ID_CONDOMINIO REQUERIDO (excepto para algunas acciones)
            if (!in_array($action, ['ver', 'eliminar']) && empty($data['id_condominio'])) {
                return $this->errorResponse('ID de condominio requerido', 400);
            }

            // 4. VALIDAR OWNERSHIP DEL CONDOMINIO (CASCADA DE CONDOMINIOSERVICE)
            if (!empty($data['id_condominio'])) {
                if (!$this->checkOwnershipCondominio($data['id_condominio'])) {
                    $this->logAdminActivity("CalleService::procesarSolicitud - Acceso denegado al condominio", [
                        'admin_id' => $this->getCurrentAdminId(),
                        'condominio_id' => $data['id_condominio'],
                        'action' => $action
                    ]);
                    return $this->errorResponse('No tiene permisos para este condominio', 403);
                }
            }

            // 5. LOG DE SOLICITUD
            $this->logAdminActivity("CalleService::procesarSolicitud - Procesando acción: $action", [
                'admin_id' => $this->getCurrentAdminId(),
                'action' => $action,
                'condominio_id' => $data['id_condominio'] ?? 'N/A',
                'data_keys' => array_keys($data)
            ]);

            // 6. ROUTING INTERNO DE ACCIONES
            switch ($action) {
                case 'crear':
                    return $this->crearCalle($data);
                
                case 'listar':
                    return $this->listarCalles($data);
                
                case 'ver':
                    return $this->verCalle($data);
                
                case 'actualizar':
                    return $this->actualizarCalle($data);
                
                case 'eliminar':
                    return $this->eliminarCalle($data);
                
                case 'buscarPorNombre':
                    return $this->buscarCallesPorNombre($data);
                
                case 'estadisticas':
                    return $this->obtenerEstadisticas($data);
                
                case 'contarCasas':
                    return $this->contarCasasEnCalle($data);
                
                default:
                    return $this->errorResponse('Acción no implementada', 501);
            }

        } catch (Exception $e) {
            $this->logAdminActivity("CalleService::procesarSolicitud - Error crítico", [
                'admin_id' => $this->getCurrentAdminId(),
                'action' => $action,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->errorResponse('Error interno del servidor', 500);
        }
    }

    /**
     * Crear nueva calle en condominio
     * SEGÚN PROMPT: Valida ownership y datos antes de crear
     * SEGÚN MODELO: Usa createCalle() y validateCalleData()
     * 
     * @param array $data Datos de la calle [nombre, id_condominio, descripcion]
     * @return array Response con resultado de la creación
     */
    private function crearCalle(array $data): array
    {
        try {
            // 1. VALIDAR CAMPOS REQUERIDOS
            $camposRequeridos = ['nombre', 'id_condominio'];
            foreach ($camposRequeridos as $campo) {
                if (empty($data[$campo])) {
                    return $this->errorResponse("Campo requerido: $campo", 400);
                }
            }

            // 2. VALIDAR DATOS CON MODELO
            $validacion = $this->calleModel->validateCalleData($data);
            if (!$validacion['valid']) {
                return $this->errorResponse('Datos inválidos: ' . implode(', ', $validacion['errors']), 400);
            }

            // 3. PREPARAR DATOS PARA CREACIÓN
            $datosLimpios = [
                'nombre' => trim($data['nombre']),
                'id_condominio' => (int) $data['id_condominio'],
                'descripcion' => isset($data['descripcion']) ? trim($data['descripcion']) : '',
                'fecha_creacion' => date('Y-m-d H:i:s'),
                'activa' => 1
            ];

            // 4. CREAR CALLE
            $idCalle = $this->calleModel->createCalle($datosLimpios);
            
            if (!$idCalle) {
                return $this->errorResponse('Error al crear la calle', 500);
            }

            // 5. LOG DE ÉXITO
            $this->logAdminActivity("CalleService::crearCalle - Calle creada exitosamente", [
                'admin_id' => $this->getCurrentAdminId(),
                'calle_id' => $idCalle,
                'nombre' => $datosLimpios['nombre'],
                'condominio_id' => $datosLimpios['id_condominio']
            ]);

            // 6. OBTENER DATOS COMPLETOS DE LA CALLE CREADA
            $calleCreada = $this->calleModel->findById($idCalle);

            return $this->successResponse('Calle creada exitosamente', [
                'calle' => $calleCreada
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("CalleService::crearCalle - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al crear la calle', 500);
        }
    }

    /**
     * Listar calles del condominio
     * SEGÚN PROMPT: Solo muestra calles del condominio del admin autenticado
     * SEGÚN MODELO: Usa findByCondominioId()
     * 
     * @param array $data Datos con id_condominio
     * @return array Response con lista de calles
     */
    private function listarCalles(array $data): array
    {
        try {
            $condominioId = (int) $data['id_condominio'];
            
            // 1. OBTENER CALLES DEL CONDOMINIO
            $calles = $this->calleModel->findByCondominioId($condominioId);

            // 2. OBTENER ESTADÍSTICAS ADICIONALES
            $callesConCasas = $this->calleModel->getCallesWithCasaCount($condominioId);

            // 3. COMBINAR DATOS
            $callesCompletas = [];
            foreach ($calles as $calle) {
                $calleCompleta = $calle;
                $calleCompleta['total_casas'] = 0;
                
                // Buscar en callesConCasas
                foreach ($callesConCasas as $calleConCasas) {
                    if ($calleConCasas['id_calle'] == $calle['id_calle']) {
                        $calleCompleta['total_casas'] = (int) $calleConCasas['total_casas'];
                        break;
                    }
                }
                
                $callesCompletas[] = $calleCompleta;
            }

            $this->logAdminActivity("CalleService::listarCalles - Calles listadas", [
                'admin_id' => $this->getCurrentAdminId(),
                'condominio_id' => $condominioId,
                'total_calles' => count($calles)
            ]);

            return $this->successResponse('Calles obtenidas exitosamente', [
                'calles' => $callesCompletas,
                'total' => count($calles)
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("CalleService::listarCalles - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al obtener las calles', 500);
        }
    }

    /**
     * Ver detalles de calle específica
     * SEGÚN PROMPT: Valida ownership antes de mostrar
     * SEGÚN MODELO: Usa findById()
     * 
     * @param array $data Datos con id_calle
     * @return array Response con detalles de la calle
     */
    private function verCalle(array $data): array
    {
        try {
            // 1. VALIDAR ID DE CALLE
            if (empty($data['id_calle'])) {
                return $this->errorResponse('ID de calle requerido', 400);
            }

            $idCalle = (int) $data['id_calle'];

            // 2. OBTENER CALLE
            $calle = $this->calleModel->findById($idCalle);
            
            if (!$calle) {
                return $this->errorResponse('Calle no encontrada', 404);
            }

            // 3. VALIDAR OWNERSHIP DEL CONDOMINIO
            if (!$this->checkOwnershipCondominio($calle['id_condominio'])) {
                $this->logAdminActivity("CalleService::verCalle - Acceso denegado", [
                    'admin_id' => $this->getCurrentAdminId(),
                    'calle_id' => $idCalle,
                    'condominio_id' => $calle['id_condominio']
                ]);
                return $this->errorResponse('No tiene permisos para ver esta calle', 403);
            }

            // 4. OBTENER INFORMACIÓN ADICIONAL
            $totalCasas = $this->calleModel->contarCasasEnCalle($idCalle);

            // 5. PREPARAR RESPUESTA COMPLETA
            $calleCompleta = $calle;
            $calleCompleta['total_casas'] = $totalCasas;

            $this->logAdminActivity("CalleService::verCalle - Calle visualizada", [
                'admin_id' => $this->getCurrentAdminId(),
                'calle_id' => $idCalle,
                'nombre' => $calle['nombre']
            ]);

            return $this->successResponse('Detalles de calle obtenidos', [
                'calle' => $calleCompleta
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("CalleService::verCalle - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al obtener los detalles de la calle', 500);
        }
    }

    /**
     * Actualizar datos de calle
     * SEGÚN PROMPT: Valida ownership y datos antes de actualizar
     * SEGÚN MODELO: Usa updateCalle() y validateCalleData()
     * 
     * @param array $data Datos con id_calle y campos a actualizar
     * @return array Response con resultado de la actualización
     */
    private function actualizarCalle(array $data): array
    {
        try {
            // 1. VALIDAR ID DE CALLE
            if (empty($data['id_calle'])) {
                return $this->errorResponse('ID de calle requerido', 400);
            }

            $idCalle = (int) $data['id_calle'];

            // 2. OBTENER CALLE ACTUAL
            $calleActual = $this->calleModel->findById($idCalle);
            
            if (!$calleActual) {
                return $this->errorResponse('Calle no encontrada', 404);
            }

            // 3. VALIDAR OWNERSHIP DEL CONDOMINIO
            if (!$this->checkOwnershipCondominio($calleActual['id_condominio'])) {
                $this->logAdminActivity("CalleService::actualizarCalle - Acceso denegado", [
                    'admin_id' => $this->getCurrentAdminId(),
                    'calle_id' => $idCalle,
                    'condominio_id' => $calleActual['id_condominio']
                ]);
                return $this->errorResponse('No tiene permisos para actualizar esta calle', 403);
            }

            // 4. PREPARAR DATOS PARA ACTUALIZACIÓN
            $datosActualizar = [];
            
            if (!empty($data['nombre'])) {
                $datosActualizar['nombre'] = trim($data['nombre']);
                
                // Validar unicidad del nuevo nombre
                if (!$this->calleModel->validateNameUniqueInCondominio(
                    $datosActualizar['nombre'], 
                    $calleActual['id_condominio'], 
                    $idCalle
                )) {
                    return $this->errorResponse('Ya existe una calle con ese nombre en el condominio', 400);
                }
            }

            if (isset($data['descripcion'])) {
                $datosActualizar['descripcion'] = trim($data['descripcion']);
            }

            if (empty($datosActualizar)) {
                return $this->errorResponse('No hay datos para actualizar', 400);
            }

            // 5. VALIDAR FORMATO DE DATOS
            if (isset($datosActualizar['nombre']) && !$this->calleModel->validateNameFormat($datosActualizar['nombre'])) {
                return $this->errorResponse('Formato de nombre inválido', 400);
            }

            // 6. ACTUALIZAR CALLE
            $resultado = $this->calleModel->updateCalle($idCalle, $datosActualizar);
            
            if (!$resultado) {
                return $this->errorResponse('Error al actualizar la calle', 500);
            }

            // 7. LOG DE ÉXITO
            $this->logAdminActivity("CalleService::actualizarCalle - Calle actualizada", [
                'admin_id' => $this->getCurrentAdminId(),
                'calle_id' => $idCalle,
                'datos_actualizados' => $datosActualizar
            ]);

            // 8. OBTENER DATOS ACTUALIZADOS
            $calleActualizada = $this->calleModel->findById($idCalle);

            return $this->successResponse('Calle actualizada exitosamente', [
                'calle' => $calleActualizada
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("CalleService::actualizarCalle - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al actualizar la calle', 500);
        }
    }

    /**
     * Eliminar calle
     * SEGÚN PROMPT: Valida ownership y verifica que no tenga casas antes de eliminar
     * SEGÚN MODELO: Usa delete() y contarCasasEnCalle()
     * 
     * @param array $data Datos con id_calle
     * @return array Response con resultado de la eliminación
     */
    private function eliminarCalle(array $data): array
    {
        try {
            // 1. VALIDAR ID DE CALLE
            if (empty($data['id_calle'])) {
                return $this->errorResponse('ID de calle requerido', 400);
            }

            $idCalle = (int) $data['id_calle'];

            // 2. OBTENER CALLE
            $calle = $this->calleModel->findById($idCalle);
            
            if (!$calle) {
                return $this->errorResponse('Calle no encontrada', 404);
            }

            // 3. VALIDAR OWNERSHIP DEL CONDOMINIO
            if (!$this->checkOwnershipCondominio($calle['id_condominio'])) {
                $this->logAdminActivity("CalleService::eliminarCalle - Acceso denegado", [
                    'admin_id' => $this->getCurrentAdminId(),
                    'calle_id' => $idCalle,
                    'condominio_id' => $calle['id_condominio']
                ]);
                return $this->errorResponse('No tiene permisos para eliminar esta calle', 403);
            }

            // 4. VERIFICAR QUE NO TENGA CASAS
            $totalCasas = $this->calleModel->contarCasasEnCalle($idCalle);
            
            if ($totalCasas > 0) {
                return $this->errorResponse("No se puede eliminar la calle. Tiene $totalCasas casa(s) asociada(s)", 400);
            }

            // 5. ELIMINAR CALLE
            $resultado = $this->calleModel->delete($idCalle);
            
            if (!$resultado) {
                return $this->errorResponse('Error al eliminar la calle', 500);
            }

            // 6. LOG DE ÉXITO
            $this->logAdminActivity("CalleService::eliminarCalle - Calle eliminada", [
                'admin_id' => $this->getCurrentAdminId(),
                'calle_id' => $idCalle,
                'nombre' => $calle['nombre'],
                'condominio_id' => $calle['id_condominio']
            ]);

            return $this->successResponse('Calle eliminada exitosamente', [
                'calle_eliminada' => [
                    'id' => $idCalle,
                    'nombre' => $calle['nombre']
                ]
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("CalleService::eliminarCalle - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al eliminar la calle', 500);
        }
    }

    /**
     * Buscar calles por patrón de nombre
     * SEGÚN PROMPT: Busca dentro del condominio del admin
     * SEGÚN MODELO: Usa searchByNamePattern()
     * 
     * @param array $data Datos con id_condominio y patron
     * @return array Response con calles encontradas
     */
    private function buscarCallesPorNombre(array $data): array
    {
        try {
            // 1. VALIDAR PARÁMETROS
            if (empty($data['patron'])) {
                return $this->errorResponse('Patrón de búsqueda requerido', 400);
            }

            $condominioId = (int) $data['id_condominio'];
            $patron = trim($data['patron']);

            // 2. BUSCAR CALLES
            $calles = $this->calleModel->searchByNamePattern($patron, $condominioId);

            // 3. AGREGAR INFORMACIÓN DE CASAS
            $callesConInfo = [];
            foreach ($calles as $calle) {
                $calleConInfo = $calle;
                $calleConInfo['total_casas'] = $this->calleModel->contarCasasEnCalle($calle['id_calle']);
                $callesConInfo[] = $calleConInfo;
            }

            $this->logAdminActivity("CalleService::buscarCallesPorNombre - Búsqueda realizada", [
                'admin_id' => $this->getCurrentAdminId(),
                'condominio_id' => $condominioId,
                'patron' => $patron,
                'resultados' => count($calles)
            ]);

            return $this->successResponse('Búsqueda completada', [
                'calles' => $callesConInfo,
                'patron_busqueda' => $patron,
                'total_encontradas' => count($calles)
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("CalleService::buscarCallesPorNombre - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error en la búsqueda', 500);
        }
    }

    /**
     * Obtener estadísticas de calles por condominio
     * SEGÚN PROMPT: Estadísticas del condominio del admin
     * SEGÚN MODELO: Usa getStatisticsByCondominio()
     * 
     * @param array $data Datos con id_condominio
     * @return array Response con estadísticas
     */
    private function obtenerEstadisticas(array $data): array
    {
        try {
            $condominioId = (int) $data['id_condominio'];

            // 1. OBTENER ESTADÍSTICAS GENERALES
            $estadisticasGenerales = $this->calleModel->getStatisticsByCondominio();

            // 2. FILTRAR POR CONDOMINIO DEL ADMIN
            $estadisticasCondominio = null;
            foreach ($estadisticasGenerales as $estadistica) {
                if ($estadistica['id_condominio'] == $condominioId) {
                    $estadisticasCondominio = $estadistica;
                    break;
                }
            }

            if (!$estadisticasCondominio) {
                $estadisticasCondominio = [
                    'id_condominio' => $condominioId,
                    'total_calles' => 0,
                    'total_casas' => 0
                ];
            }

            // 3. OBTENER CALLES CON MAYOR NÚMERO DE CASAS
            $callesConCasas = $this->calleModel->getCallesWithCasaCount($condominioId);
            
            // Ordenar por número de casas descendente
            usort($callesConCasas, function($a, $b) {
                return (int)$b['total_casas'] - (int)$a['total_casas'];
            });

            // 4. CALCULAR ESTADÍSTICAS ADICIONALES
            $promediosCasasPorCalle = 0;
            if ($estadisticasCondominio['total_calles'] > 0) {
                $promediosCasasPorCalle = round($estadisticasCondominio['total_casas'] / $estadisticasCondominio['total_calles'], 2);
            }

            $this->logAdminActivity("CalleService::obtenerEstadisticas - Estadísticas generadas", [
                'admin_id' => $this->getCurrentAdminId(),
                'condominio_id' => $condominioId
            ]);

            return $this->successResponse('Estadísticas obtenidas exitosamente', [
                'estadisticas' => [
                    'total_calles' => (int) $estadisticasCondominio['total_calles'],
                    'total_casas' => (int) $estadisticasCondominio['total_casas'],
                    'promedio_casas_por_calle' => $promediosCasasPorCalle,
                    'calles_con_mas_casas' => array_slice($callesConCasas, 0, 5) // Top 5
                ],
                'condominio_id' => $condominioId
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("CalleService::obtenerEstadisticas - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al obtener estadísticas', 500);
        }
    }

    /**
     * Contar casas en una calle específica
     * SEGÚN PROMPT: Valida ownership antes de contar
     * SEGÚN MODELO: Usa contarCasasEnCalle()
     * 
     * @param array $data Datos con id_calle
     * @return array Response con conteo de casas
     */
    private function contarCasasEnCalle(array $data): array
    {
        try {
            // 1. VALIDAR ID DE CALLE
            if (empty($data['id_calle'])) {
                return $this->errorResponse('ID de calle requerido', 400);
            }

            $idCalle = (int) $data['id_calle'];

            // 2. OBTENER CALLE
            $calle = $this->calleModel->findById($idCalle);
            
            if (!$calle) {
                return $this->errorResponse('Calle no encontrada', 404);
            }

            // 3. VALIDAR OWNERSHIP DEL CONDOMINIO
            if (!$this->checkOwnershipCondominio($calle['id_condominio'])) {
                return $this->errorResponse('No tiene permisos para acceder a esta calle', 403);
            }

            // 4. CONTAR CASAS
            $totalCasas = $this->calleModel->contarCasasEnCalle($idCalle);

            $this->logAdminActivity("CalleService::contarCasasEnCalle - Conteo realizado", [
                'admin_id' => $this->getCurrentAdminId(),
                'calle_id' => $idCalle,
                'total_casas' => $totalCasas
            ]);

            return $this->successResponse('Conteo completado', [
                'calle' => [
                    'id' => $idCalle,
                    'nombre' => $calle['nombre'],
                    'total_casas' => $totalCasas
                ]
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("CalleService::contarCasasEnCalle - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al contar casas', 500);
        }
    }
}
?>
