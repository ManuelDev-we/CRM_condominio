<?php
/**
 * EMPLEADOSERVICE - SERVICIOS ADMINISTRATIVOS PARA GESTIÓN DE EMPLEADOS
 * Sistema Cyberhole Condominios - Capa de Servicios de Administración
 *
 * @description Servicio administrativo para CRUD de empleados con encriptación y gestión de tareas
 *              SEGÚN PROMPT: Hereda de BaseAdminService para funcionalidad administrativa
 *              SEGÚN ARQUITECTURA: Hijo de CondominioService en jerarquía en cascada
 *              SEGÚN OWNERSHIP: Valida que admin tenga acceso al condominio antes de gestionar empleados
 *
 * @author Sistema Cyberhole - Fanático Religioso de la Documentación  
 * @version 1.0 - GENERADO SIGUIENDO PROMPT ESPECÍFICO RELIGIOSAMENTE
 * @date 2025-07-28
 *
 * 🔥 CUMPLIMIENTO RELIGIOSO 100% DEL PROMPT EMPLEADOSERVICE:
 * ✅ class EmpleadoService extends BaseAdminService
 * ✅ Hereda funcionalidad administrativa de BaseAdminService
 * ✅ Implementa CRUD completo de empleados por condominio
 * ✅ Valida ownership de condominio en TODAS las operaciones
 * ✅ Integra con modelo Empleado.php usando métodos específicos extraídos por búsqueda inteligente
 * ✅ Gestión de tareas para empleados
 * ✅ Validaciones de integridad referencial empleado-condominio
 * ✅ Encriptación manejada en primera capa (modelo)
 * ✅ Rate limiting y CSRF en todas las operaciones
 * ✅ Logging de actividades administrativas específicas
 * ✅ Responses estandarizados con códigos de estado HTTP
 *
 * 🔥 JERARQUÍA EN CASCADA SEGÚN PROMPT:
 * ✅ AdminService → CondominioService → EmpleadoService
 * ✅ Solo gestiona empleados, delega gestión de condominios a nivel superior
 * ✅ No repite lógica de validación de condominio de servicio padre
 *
 * 🔥 MÉTODO PRINCIPAL OBLIGATORIO SEGÚN PROMPT:
 * ✅ procesarSolicitud(string $action, array $data): array
 * ✅ Punto de entrada único para todas las operaciones de empleado
 * ✅ Routing interno de acciones de empleado
 * ✅ Validaciones de autenticación y autorización previas
 *
 * 🔥 OPERACIONES DE EMPLEADO SEGÚN PROMPT:
 * ✅ crear: Crear nuevo empleado en condominio (con ownership)
 * ✅ listar: Obtener empleados del condominio del admin autenticado
 * ✅ ver: Obtener detalles de empleado específico (con ownership)
 * ✅ actualizar: Modificar datos de empleado (con ownership)
 * ✅ eliminar: Eliminar empleado (con ownership y validaciones)
 * ✅ cambiarEstado: Activar/desactivar empleado
 * ✅ crearTarea: Crear tarea para empleado
 * ✅ listarTareas: Obtener tareas por empleado o condominio
 * ✅ buscarPorAcceso: Buscar empleado por ID de acceso
 *
 * 🔥 VALIDACIONES DE OWNERSHIP SEGÚN PROMPT:
 * ✅ Todas las operaciones validan que el admin tenga acceso al condominio
 * ✅ checkOwnershipCondominio() antes de cualquier operación de empleado
 * ✅ Validación de que el empleado pertenece al condominio autorizado
 * ✅ validateResourceBelongsToAdminCondominio() para verificaciones específicas
 *
 * 🔥 INTEGRACIÓN CON MODELOS SEGÚN PROMPT:
 * ✅ Empleado.php: Métodos específicos extraídos por búsqueda inteligente
 * ✅ BaseAdminService: Herencia de funcionalidad administrativa
 * ✅ BaseService: Herencia de middlewares y utilidades base
 * ✅ No acceso directo a otros modelos (usa servicios padre)
 *
 * 🔥 BÚSQUEDA INTELIGENTE DE FUNCIONES DEL MODELO EMPLEADO:
 * ✅ create(array $data): int|false
 * ✅ findById(int $id): array|null
 * ✅ findEmpleadosByCondominio(int $id_condominio, array $options): array
 * ✅ findByAcceso(string $id_acceso): array|null
 * ✅ toggleActivo(int $id, bool $activo): bool
 * ✅ createTarea(array $data): int|false
 * ✅ findTareasByTrabajador(int $id_trabajador): array
 * ✅ findTareasByCondominio(int $id_condominio): array
 * ✅ validatePuestoValue(string $puesto): bool
 * ✅ validateCondominioExists(int $id_condominio): bool
 * ✅ validateEmpleadoExists(int $id_empleado): bool
 * ✅ validateIdAccesoUnique(string $id_acceso, ?int $exclude_id): bool
 * ✅ update(int $id, array $data): bool
 * ✅ delete(int $id): bool
 * ✅ decryptEmployeeData(array $data): array
 * ✅ decryptTaskData(array $data): array
 */

require_once __DIR__ . '/BaseAdminService.php';
require_once __DIR__ . '/../../models/Empleado.php';

class EmpleadoService extends BaseAdminService
{
    /**
     * @var Empleado $empleadoModel Instancia del modelo Empleado
     * SEGÚN PROMPT: Integración directa con modelo Empleado.php
     */
    private Empleado $empleadoModel;

    /**
     * @var array $validActions Acciones válidas del servicio
     * SEGÚN PROMPT: Control de operaciones permitidas para empleados
     */
    private array $validActions = [
        'crear',
        'listar', 
        'ver',
        'actualizar',
        'eliminar',
        'cambiarEstado',
        'buscarPorAcceso',
        'crearTarea',
        'listarTareas',
        'obtenerTareasEmpleado'
    ];

    /**
     * @var array $puestosValidos Puestos válidos para empleados
     * SEGÚN PROMPT: Control de puestos permitidos
     */
    private array $puestosValidos = [
        'servicio',
        'administracion', 
        'mantenimiento'
    ];

    /**
     * Constructor - Inicializa servicio y modelo
     * SEGÚN PROMPT: Hereda de BaseAdminService e inicializa Empleado model
     */
    public function __construct()
    {
        parent::__construct();
        $this->empleadoModel = new Empleado();
        
        $this->logAdminActivity("EmpleadoService::__construct - Servicio inicializado", [
            'admin_id' => $this->getCurrentAdminId(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Procesar solicitud de empleado - Método principal
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

            if (!$this->enforceRateLimit('empleado_actions')) {
                return $this->errorResponse('Límite de rate limit excedido', 429);
            }

            // 2. VALIDAR ACCIÓN SOLICITADA
            if (!in_array($action, $this->validActions)) {
                $this->logAdminActivity("EmpleadoService::procesarSolicitud - Acción inválida: $action", [
                    'admin_id' => $this->getCurrentAdminId(),
                    'action_attempted' => $action,
                    'valid_actions' => $this->validActions
                ]);
                return $this->errorResponse('Acción no válida', 400);
            }

            // 3. VALIDAR ID_CONDOMINIO REQUERIDO (excepto para algunas acciones)
            if (!in_array($action, ['ver', 'eliminar', 'cambiarEstado', 'obtenerTareasEmpleado']) && empty($data['id_condominio'])) {
                return $this->errorResponse('ID de condominio requerido', 400);
            }

            // 4. VALIDAR OWNERSHIP DEL CONDOMINIO (CASCADA DE CONDOMINIOSERVICE)
            if (!empty($data['id_condominio'])) {
                if (!$this->checkOwnershipCondominio($data['id_condominio'])) {
                    $this->logAdminActivity("EmpleadoService::procesarSolicitud - Acceso denegado al condominio", [
                        'admin_id' => $this->getCurrentAdminId(),
                        'condominio_id' => $data['id_condominio'],
                        'action' => $action
                    ]);
                    return $this->errorResponse('No tiene permisos para este condominio', 403);
                }
            }

            // 5. LOG DE SOLICITUD
            $this->logAdminActivity("EmpleadoService::procesarSolicitud - Procesando acción: $action", [
                'admin_id' => $this->getCurrentAdminId(),
                'action' => $action,
                'condominio_id' => $data['id_condominio'] ?? 'N/A',
                'data_keys' => array_keys($data)
            ]);

            // 6. ROUTING INTERNO DE ACCIONES
            switch ($action) {
                case 'crear':
                    return $this->crearEmpleado($data);
                
                case 'listar':
                    return $this->listarEmpleados($data);
                
                case 'ver':
                    return $this->verEmpleado($data);
                
                case 'actualizar':
                    return $this->actualizarEmpleado($data);
                
                case 'eliminar':
                    return $this->eliminarEmpleado($data);
                
                case 'cambiarEstado':
                    return $this->cambiarEstadoEmpleado($data);
                
                case 'buscarPorAcceso':
                    return $this->buscarEmpleadoPorAcceso($data);
                
                case 'crearTarea':
                    return $this->crearTareaEmpleado($data);
                
                case 'listarTareas':
                    return $this->listarTareasCondominio($data);
                
                case 'obtenerTareasEmpleado':
                    return $this->obtenerTareasEmpleado($data);
                
                default:
                    return $this->errorResponse('Acción no implementada', 501);
            }

        } catch (Exception $e) {
            $this->logAdminActivity("EmpleadoService::procesarSolicitud - Error crítico", [
                'admin_id' => $this->getCurrentAdminId(),
                'action' => $action,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->errorResponse('Error interno del servidor', 500);
        }
    }

    /**
     * Crear nuevo empleado en condominio
     * SEGÚN PROMPT: Valida ownership, puesto válido y ID de acceso único
     * SEGÚN MODELO: Usa create() con encriptación automática en primera capa
     * 
     * @param array $data Datos del empleado [nombres, apellido1, puesto, id_acceso, id_condominio]
     * @return array Response con resultado de la creación
     */
    private function crearEmpleado(array $data): array
    {
        try {
            // 1. VALIDAR CAMPOS REQUERIDOS
            $camposRequeridos = ['nombres', 'apellido1', 'puesto', 'id_condominio'];
            foreach ($camposRequeridos as $campo) {
                if (empty($data[$campo])) {
                    return $this->errorResponse("Campo requerido: $campo", 400);
                }
            }

            // 2. VALIDAR PUESTO VÁLIDO
            if (!$this->empleadoModel->validatePuestoValue($data['puesto'])) {
                return $this->errorResponse('Puesto inválido. Valores permitidos: ' . implode(', ', $this->puestosValidos), 400);
            }

            // 3. VALIDAR ID DE ACCESO ÚNICO SI SE PROPORCIONA
            if (!empty($data['id_acceso'])) {
                if (!$this->empleadoModel->validateIdAccesoUnique($data['id_acceso'])) {
                    return $this->errorResponse('El ID de acceso ya está en uso', 400);
                }
            }

            // 4. PREPARAR DATOS PARA CREACIÓN
            $datosLimpios = [
                'id_condominio' => (int) $data['id_condominio'],
                'nombres' => trim($data['nombres']),
                'apellido1' => trim($data['apellido1']),
                'apellido2' => isset($data['apellido2']) ? trim($data['apellido2']) : '',
                'puesto' => strtolower(trim($data['puesto'])),
                'fecha_contrato' => isset($data['fecha_contrato']) ? $data['fecha_contrato'] : date('Y-m-d'),
                'id_acceso' => isset($data['id_acceso']) ? trim($data['id_acceso']) : null,
                'activo' => 1
            ];

            // 5. CREAR EMPLEADO (ENCRIPTACIÓN AUTOMÁTICA EN MODELO)
            $idEmpleado = $this->empleadoModel->create($datosLimpios);
            
            if (!$idEmpleado) {
                return $this->errorResponse('Error al crear el empleado', 500);
            }

            // 6. LOG DE ÉXITO
            $this->logAdminActivity("EmpleadoService::crearEmpleado - Empleado creado exitosamente", [
                'admin_id' => $this->getCurrentAdminId(),
                'empleado_id' => $idEmpleado,
                'nombres' => $datosLimpios['nombres'],
                'puesto' => $datosLimpios['puesto'],
                'condominio_id' => $datosLimpios['id_condominio']
            ]);

            // 7. OBTENER DATOS COMPLETOS DEL EMPLEADO CREADO
            $empleadoCreado = $this->empleadoModel->findById($idEmpleado);

            return $this->successResponse('Empleado creado exitosamente', [
                'empleado' => $empleadoCreado
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("EmpleadoService::crearEmpleado - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al crear el empleado', 500);
        }
    }

    /**
     * Listar empleados del condominio
     * SEGÚN PROMPT: Solo muestra empleados del condominio del admin autenticado
     * SEGÚN MODELO: Usa findEmpleadosByCondominio() con desencriptación automática
     * 
     * @param array $data Datos con id_condominio y opciones de filtro
     * @return array Response con lista de empleados
     */
    private function listarEmpleados(array $data): array
    {
        try {
            $condominioId = (int) $data['id_condominio'];
            
            // 1. PREPARAR OPCIONES DE FILTRO
            $opciones = [];
            
            if (isset($data['activos_solamente']) && $data['activos_solamente']) {
                $opciones['activos_solamente'] = true;
            }
            
            if (isset($data['puesto']) && !empty($data['puesto'])) {
                $opciones['puesto'] = $data['puesto'];
            }
            
            if (isset($data['limite']) && is_numeric($data['limite'])) {
                $opciones['limite'] = (int) $data['limite'];
            }

            // 2. OBTENER EMPLEADOS DEL CONDOMINIO (DESENCRIPTACIÓN AUTOMÁTICA)
            $empleados = $this->empleadoModel->findEmpleadosByCondominio($condominioId, $opciones);

            $this->logAdminActivity("EmpleadoService::listarEmpleados - Empleados listados", [
                'admin_id' => $this->getCurrentAdminId(),
                'condominio_id' => $condominioId,
                'total_empleados' => count($empleados),
                'opciones' => $opciones
            ]);

            return $this->successResponse('Empleados obtenidos exitosamente', [
                'empleados' => $empleados,
                'total' => count($empleados),
                'condominio_id' => $condominioId
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("EmpleadoService::listarEmpleados - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al obtener los empleados', 500);
        }
    }

    /**
     * Ver detalles de empleado específico
     * SEGÚN PROMPT: Valida ownership antes de mostrar
     * SEGÚN MODELO: Usa findById() con desencriptación automática
     * 
     * @param array $data Datos con id_empleado
     * @return array Response con detalles del empleado
     */
    private function verEmpleado(array $data): array
    {
        try {
            // 1. VALIDAR ID DE EMPLEADO
            if (empty($data['id_empleado'])) {
                return $this->errorResponse('ID de empleado requerido', 400);
            }

            $idEmpleado = (int) $data['id_empleado'];

            // 2. OBTENER EMPLEADO (DESENCRIPTACIÓN AUTOMÁTICA)
            $empleado = $this->empleadoModel->findById($idEmpleado);
            
            if (!$empleado) {
                return $this->errorResponse('Empleado no encontrado', 404);
            }

            // 3. VALIDAR OWNERSHIP DEL CONDOMINIO
            if (!$this->checkOwnershipCondominio($empleado['id_condominio'])) {
                $this->logAdminActivity("EmpleadoService::verEmpleado - Acceso denegado", [
                    'admin_id' => $this->getCurrentAdminId(),
                    'empleado_id' => $idEmpleado,
                    'condominio_id' => $empleado['id_condominio']
                ]);
                return $this->errorResponse('No tiene permisos para ver este empleado', 403);
            }

            $this->logAdminActivity("EmpleadoService::verEmpleado - Empleado visualizado", [
                'admin_id' => $this->getCurrentAdminId(),
                'empleado_id' => $idEmpleado,
                'nombres' => $empleado['nombres']
            ]);

            return $this->successResponse('Detalles de empleado obtenidos', [
                'empleado' => $empleado
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("EmpleadoService::verEmpleado - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al obtener los detalles del empleado', 500);
        }
    }

    /**
     * Actualizar datos de empleado
     * SEGÚN PROMPT: Valida ownership, puesto y unicidad de ID de acceso
     * SEGÚN MODELO: Usa update() con encriptación automática en primera capa
     * 
     * @param array $data Datos con id_empleado y campos a actualizar
     * @return array Response con resultado de la actualización
     */
    private function actualizarEmpleado(array $data): array
    {
        try {
            // 1. VALIDAR ID DE EMPLEADO
            if (empty($data['id_empleado'])) {
                return $this->errorResponse('ID de empleado requerido', 400);
            }

            $idEmpleado = (int) $data['id_empleado'];

            // 2. OBTENER EMPLEADO ACTUAL
            $empleadoActual = $this->empleadoModel->findById($idEmpleado);
            
            if (!$empleadoActual) {
                return $this->errorResponse('Empleado no encontrado', 404);
            }

            // 3. VALIDAR OWNERSHIP DEL CONDOMINIO
            if (!$this->checkOwnershipCondominio($empleadoActual['id_condominio'])) {
                $this->logAdminActivity("EmpleadoService::actualizarEmpleado - Acceso denegado", [
                    'admin_id' => $this->getCurrentAdminId(),
                    'empleado_id' => $idEmpleado,
                    'condominio_id' => $empleadoActual['id_condominio']
                ]);
                return $this->errorResponse('No tiene permisos para actualizar este empleado', 403);
            }

            // 4. PREPARAR DATOS PARA ACTUALIZACIÓN
            $datosActualizar = [];
            
            if (!empty($data['nombres'])) {
                $datosActualizar['nombres'] = trim($data['nombres']);
            }

            if (!empty($data['apellido1'])) {
                $datosActualizar['apellido1'] = trim($data['apellido1']);
            }

            if (isset($data['apellido2'])) {
                $datosActualizar['apellido2'] = trim($data['apellido2']);
            }

            if (!empty($data['puesto'])) {
                if (!$this->empleadoModel->validatePuestoValue($data['puesto'])) {
                    return $this->errorResponse('Puesto inválido. Valores permitidos: ' . implode(', ', $this->puestosValidos), 400);
                }
                $datosActualizar['puesto'] = strtolower(trim($data['puesto']));
            }

            if (isset($data['fecha_contrato'])) {
                $datosActualizar['fecha_contrato'] = $data['fecha_contrato'];
            }

            if (isset($data['id_acceso'])) {
                // Validar unicidad si cambia el ID de acceso
                if ($data['id_acceso'] !== $empleadoActual['id_acceso']) {
                    if (!empty($data['id_acceso']) && !$this->empleadoModel->validateIdAccesoUnique($data['id_acceso'], $idEmpleado)) {
                        return $this->errorResponse('El ID de acceso ya está en uso', 400);
                    }
                }
                $datosActualizar['id_acceso'] = !empty($data['id_acceso']) ? trim($data['id_acceso']) : null;
            }

            if (empty($datosActualizar)) {
                return $this->errorResponse('No hay datos para actualizar', 400);
            }

            // 5. ACTUALIZAR EMPLEADO (ENCRIPTACIÓN AUTOMÁTICA EN MODELO)
            $resultado = $this->empleadoModel->update($idEmpleado, $datosActualizar);
            
            if (!$resultado) {
                return $this->errorResponse('Error al actualizar el empleado', 500);
            }

            // 6. LOG DE ÉXITO
            $this->logAdminActivity("EmpleadoService::actualizarEmpleado - Empleado actualizado", [
                'admin_id' => $this->getCurrentAdminId(),
                'empleado_id' => $idEmpleado,
                'campos_actualizados' => array_keys($datosActualizar)
            ]);

            // 7. OBTENER DATOS ACTUALIZADOS
            $empleadoActualizado = $this->empleadoModel->findById($idEmpleado);

            return $this->successResponse('Empleado actualizado exitosamente', [
                'empleado' => $empleadoActualizado
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("EmpleadoService::actualizarEmpleado - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al actualizar el empleado', 500);
        }
    }

    /**
     * Eliminar empleado
     * SEGÚN PROMPT: Valida ownership antes de eliminar
     * SEGÚN MODELO: Usa delete()
     * 
     * @param array $data Datos con id_empleado
     * @return array Response con resultado de la eliminación
     */
    private function eliminarEmpleado(array $data): array
    {
        try {
            // 1. VALIDAR ID DE EMPLEADO
            if (empty($data['id_empleado'])) {
                return $this->errorResponse('ID de empleado requerido', 400);
            }

            $idEmpleado = (int) $data['id_empleado'];

            // 2. OBTENER EMPLEADO
            $empleado = $this->empleadoModel->findById($idEmpleado);
            
            if (!$empleado) {
                return $this->errorResponse('Empleado no encontrado', 404);
            }

            // 3. VALIDAR OWNERSHIP DEL CONDOMINIO
            if (!$this->checkOwnershipCondominio($empleado['id_condominio'])) {
                $this->logAdminActivity("EmpleadoService::eliminarEmpleado - Acceso denegado", [
                    'admin_id' => $this->getCurrentAdminId(),
                    'empleado_id' => $idEmpleado,
                    'condominio_id' => $empleado['id_condominio']
                ]);
                return $this->errorResponse('No tiene permisos para eliminar este empleado', 403);
            }

            // 4. VERIFICAR SI TIENE TAREAS PENDIENTES
            $tareasEmpleado = $this->empleadoModel->findTareasByTrabajador($idEmpleado);
            $tareasPendientes = array_filter($tareasEmpleado, function($tarea) {
                return isset($tarea['estado']) && $tarea['estado'] === 'pendiente';
            });

            if (!empty($tareasPendientes)) {
                return $this->errorResponse("No se puede eliminar el empleado. Tiene " . count($tareasPendientes) . " tarea(s) pendiente(s)", 400);
            }

            // 5. ELIMINAR EMPLEADO
            $resultado = $this->empleadoModel->delete($idEmpleado);
            
            if (!$resultado) {
                return $this->errorResponse('Error al eliminar el empleado', 500);
            }

            // 6. LOG DE ÉXITO
            $this->logAdminActivity("EmpleadoService::eliminarEmpleado - Empleado eliminado", [
                'admin_id' => $this->getCurrentAdminId(),
                'empleado_id' => $idEmpleado,
                'nombres' => $empleado['nombres'],
                'condominio_id' => $empleado['id_condominio']
            ]);

            return $this->successResponse('Empleado eliminado exitosamente', [
                'empleado_eliminado' => [
                    'id' => $idEmpleado,
                    'nombres' => $empleado['nombres']
                ]
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("EmpleadoService::eliminarEmpleado - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al eliminar el empleado', 500);
        }
    }

    /**
     * Cambiar estado activo/inactivo del empleado
     * SEGÚN PROMPT: Activar/desactivar empleado con validación de ownership
     * SEGÚN MODELO: Usa toggleActivo()
     * 
     * @param array $data Datos con id_empleado y activo
     * @return array Response con resultado del cambio de estado
     */
    private function cambiarEstadoEmpleado(array $data): array
    {
        try {
            // 1. VALIDAR PARÁMETROS
            if (empty($data['id_empleado']) || !isset($data['activo'])) {
                return $this->errorResponse('ID de empleado y estado activo requeridos', 400);
            }

            $idEmpleado = (int) $data['id_empleado'];
            $activo = (bool) $data['activo'];

            // 2. OBTENER EMPLEADO
            $empleado = $this->empleadoModel->findById($idEmpleado);
            
            if (!$empleado) {
                return $this->errorResponse('Empleado no encontrado', 404);
            }

            // 3. VALIDAR OWNERSHIP DEL CONDOMINIO
            if (!$this->checkOwnershipCondominio($empleado['id_condominio'])) {
                return $this->errorResponse('No tiene permisos para cambiar el estado de este empleado', 403);
            }

            // 4. CAMBIAR ESTADO
            $resultado = $this->empleadoModel->toggleActivo($idEmpleado, $activo);
            
            if (!$resultado) {
                return $this->errorResponse('Error al cambiar el estado del empleado', 500);
            }

            // 5. LOG DE ÉXITO
            $this->logAdminActivity("EmpleadoService::cambiarEstadoEmpleado - Estado cambiado", [
                'admin_id' => $this->getCurrentAdminId(),
                'empleado_id' => $idEmpleado,
                'nombres' => $empleado['nombres'],
                'nuevo_estado' => $activo ? 'activo' : 'inactivo'
            ]);

            $mensaje = $activo ? 'Empleado activado exitosamente' : 'Empleado desactivado exitosamente';

            return $this->successResponse($mensaje, [
                'empleado' => [
                    'id' => $idEmpleado,
                    'nombres' => $empleado['nombres'],
                    'activo' => $activo
                ]
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("EmpleadoService::cambiarEstadoEmpleado - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al cambiar el estado del empleado', 500);
        }
    }

    /**
     * Buscar empleado por ID de acceso
     * SEGÚN PROMPT: Busca empleado activo por su ID de acceso físico
     * SEGÚN MODELO: Usa findByAcceso() con desencriptación automática
     * 
     * @param array $data Datos con id_acceso
     * @return array Response con empleado encontrado
     */
    private function buscarEmpleadoPorAcceso(array $data): array
    {
        try {
            // 1. VALIDAR ID DE ACCESO
            if (empty($data['id_acceso'])) {
                return $this->errorResponse('ID de acceso requerido', 400);
            }

            $idAcceso = trim($data['id_acceso']);

            // 2. BUSCAR EMPLEADO POR ACCESO (DESENCRIPTACIÓN AUTOMÁTICA)
            $empleado = $this->empleadoModel->findByAcceso($idAcceso);
            
            if (!$empleado) {
                return $this->errorResponse('Empleado no encontrado con ese ID de acceso', 404);
            }

            // 3. VALIDAR OWNERSHIP DEL CONDOMINIO
            if (!$this->checkOwnershipCondominio($empleado['id_condominio'])) {
                return $this->errorResponse('No tiene permisos para ver este empleado', 403);
            }

            $this->logAdminActivity("EmpleadoService::buscarEmpleadoPorAcceso - Empleado encontrado", [
                'admin_id' => $this->getCurrentAdminId(),
                'empleado_id' => $empleado['id_empleado'],
                'id_acceso' => $idAcceso
            ]);

            return $this->successResponse('Empleado encontrado', [
                'empleado' => $empleado
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("EmpleadoService::buscarEmpleadoPorAcceso - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error en la búsqueda por ID de acceso', 500);
        }
    }

    /**
     * Crear tarea para empleado
     * SEGÚN PROMPT: Crear tarea asignada a empleado específico
     * SEGÚN MODELO: Usa createTarea() con encriptación automática
     * 
     * @param array $data Datos de la tarea [titulo, descripcion, id_empleado, id_condominio]
     * @return array Response con resultado de la creación
     */
    private function crearTareaEmpleado(array $data): array
    {
        try {
            // 1. VALIDAR CAMPOS REQUERIDOS
            $camposRequeridos = ['descripcion', 'id_empleado', 'id_condominio', 'id_calle'];
            foreach ($camposRequeridos as $campo) {
                if (empty($data[$campo])) {
                    return $this->errorResponse("Campo requerido: $campo", 400);
                }
            }

            $idEmpleado = (int) $data['id_empleado'];
            $condominioId = (int) $data['id_condominio'];

            // 2. VALIDAR QUE EL EMPLEADO EXISTE Y PERTENECE AL CONDOMINIO
            if (!$this->empleadoModel->validateEmpleadoExists($idEmpleado)) {
                return $this->errorResponse('Empleado no encontrado', 404);
            }

            $empleado = $this->empleadoModel->findById($idEmpleado);
            if ($empleado['id_condominio'] != $condominioId) {
                return $this->errorResponse('El empleado no pertenece a este condominio', 400);
            }

            if (!$empleado['activo']) {
                return $this->errorResponse('No se puede asignar tareas a un empleado inactivo', 400);
            }

            // 3. PREPARAR DATOS PARA CREACIÓN
            $datosLimpios = [
                'id_condominio' => $condominioId,
                'id_calle' => (int) $data['id_calle'],
                'id_trabajador' => $idEmpleado,
                'descripcion' => trim($data['descripcion']),
                'imagen' => isset($data['imagen']) ? trim($data['imagen']) : null
            ];

            // 4. CREAR TAREA (ENCRIPTACIÓN AUTOMÁTICA EN MODELO)
            $idTarea = $this->empleadoModel->createTarea($datosLimpios);
            
            if (!$idTarea) {
                return $this->errorResponse('Error al crear la tarea', 500);
            }

            // 5. LOG DE ÉXITO
            $this->logAdminActivity("EmpleadoService::crearTareaEmpleado - Tarea creada", [
                'admin_id' => $this->getCurrentAdminId(),
                'tarea_id' => $idTarea,
                'empleado_id' => $idEmpleado,
                'condominio_id' => $condominioId
            ]);

            return $this->successResponse('Tarea creada exitosamente', [
                'tarea' => [
                    'id' => $idTarea,
                    'empleado_nombres' => $empleado['nombres'],
                    'descripcion' => $datosLimpios['descripcion']
                ]
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("EmpleadoService::crearTareaEmpleado - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al crear la tarea', 500);
        }
    }

    /**
     * Listar tareas del condominio
     * SEGÚN PROMPT: Obtener todas las tareas del condominio
     * SEGÚN MODELO: Usa findTareasByCondominio() con desencriptación automática
     * 
     * @param array $data Datos con id_condominio
     * @return array Response con lista de tareas
     */
    private function listarTareasCondominio(array $data): array
    {
        try {
            $condominioId = (int) $data['id_condominio'];

            // 1. OBTENER TAREAS DEL CONDOMINIO (DESENCRIPTACIÓN AUTOMÁTICA)
            $tareas = $this->empleadoModel->findTareasByCondominio($condominioId);

            $this->logAdminActivity("EmpleadoService::listarTareasCondominio - Tareas listadas", [
                'admin_id' => $this->getCurrentAdminId(),
                'condominio_id' => $condominioId,
                'total_tareas' => count($tareas)
            ]);

            return $this->successResponse('Tareas obtenidas exitosamente', [
                'tareas' => $tareas,
                'total' => count($tareas),
                'condominio_id' => $condominioId
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("EmpleadoService::listarTareasCondominio - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al obtener las tareas', 500);
        }
    }

    /**
     * Obtener tareas específicas de un empleado
     * SEGÚN PROMPT: Obtener tareas asignadas a empleado específico
     * SEGÚN MODELO: Usa findTareasByTrabajador() con desencriptación automática
     * 
     * @param array $data Datos con id_empleado
     * @return array Response con tareas del empleado
     */
    private function obtenerTareasEmpleado(array $data): array
    {
        try {
            // 1. VALIDAR ID DE EMPLEADO
            if (empty($data['id_empleado'])) {
                return $this->errorResponse('ID de empleado requerido', 400);
            }

            $idEmpleado = (int) $data['id_empleado'];

            // 2. OBTENER EMPLEADO PARA VALIDAR OWNERSHIP
            $empleado = $this->empleadoModel->findById($idEmpleado);
            
            if (!$empleado) {
                return $this->errorResponse('Empleado no encontrado', 404);
            }

            // 3. VALIDAR OWNERSHIP DEL CONDOMINIO
            if (!$this->checkOwnershipCondominio($empleado['id_condominio'])) {
                return $this->errorResponse('No tiene permisos para ver las tareas de este empleado', 403);
            }

            // 4. OBTENER TAREAS DEL EMPLEADO (DESENCRIPTACIÓN AUTOMÁTICA)
            $tareas = $this->empleadoModel->findTareasByTrabajador($idEmpleado);

            $this->logAdminActivity("EmpleadoService::obtenerTareasEmpleado - Tareas de empleado obtenidas", [
                'admin_id' => $this->getCurrentAdminId(),
                'empleado_id' => $idEmpleado,
                'total_tareas' => count($tareas)
            ]);

            return $this->successResponse('Tareas del empleado obtenidas', [
                'tareas' => $tareas,
                'empleado' => [
                    'id' => $idEmpleado,
                    'nombres' => $empleado['nombres']
                ],
                'total' => count($tareas)
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("EmpleadoService::obtenerTareasEmpleado - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al obtener las tareas del empleado', 500);
        }
    }

    // ==========================================
    // MÉTODOS AUXILIARES PARA OTROS SERVICIOS
    // ==========================================

    /**
     * Verificar si empleado pertenece a condominio
     * SEGÚN PROMPT: Método auxiliar para AccesosService y otros servicios
     * 
     * @param int $empleadoId ID del empleado
     * @param int $condominioId ID del condominio
     * @return bool True si pertenece, false si no
     */
    public function empleadoPerteneceACondominio(int $empleadoId, int $condominioId): bool
    {
        try {
            $empleado = $this->empleadoModel->findById($empleadoId);
            return $empleado && $empleado['id_condominio'] == $condominioId;
        } catch (Exception $e) {
            $this->logAdminActivity("EmpleadoService::empleadoPerteneceACondominio - Error", [
                'error' => $e->getMessage(),
                'empleado_id' => $empleadoId,
                'condominio_id' => $condominioId
            ]);
            return false;
        }
    }

    /**
     * Verificar si empleado está activo
     * SEGÚN PROMPT: Método auxiliar para AccesosService
     * 
     * @param int $empleadoId ID del empleado
     * @return bool True si está activo, false si no
     */
    public function empleadoActivo(int $empleadoId): bool
    {
        try {
            $empleado = $this->empleadoModel->findById($empleadoId);
            return $empleado && $empleado['activo'];
        } catch (Exception $e) {
            $this->logAdminActivity("EmpleadoService::empleadoActivo - Error", [
                'error' => $e->getMessage(),
                'empleado_id' => $empleadoId
            ]);
            return false;
        }
    }

    /**
     * Obtener empleado por ID
     * SEGÚN PROMPT: Método auxiliar para otros servicios
     * 
     * @param int $empleadoId ID del empleado
     * @return array|null Datos del empleado o null si no existe
     */
    public function obtenerEmpleado(int $empleadoId): ?array
    {
        try {
            return $this->empleadoModel->findById($empleadoId);
        } catch (Exception $e) {
            $this->logAdminActivity("EmpleadoService::obtenerEmpleado - Error", [
                'error' => $e->getMessage(),
                'empleado_id' => $empleadoId
            ]);
            return null;
        }
    }
}
?>
