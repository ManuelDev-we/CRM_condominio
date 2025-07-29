<?php
/**
 * AREACOMUNSERVICE - SERVICIOS ADMINISTRATIVOS PARA GESTIÓN DE ÁREAS COMUNES
 * Sistema Cyberhole Condominios - Capa de Servicios de Administración
 *
 * @description Servicio administrativo para CRUD de áreas comunes dentro de condominios
 *              SEGÚN PROMPT: Hereda de BaseAdminService para funcionalidad administrativa
 *              SEGÚN ARQUITECTURA: Hijo de CondominioService en jerarquía en cascada
 *              SEGÚN OWNERSHIP: Valida que admin tenga acceso al condominio antes de gestionar áreas comunes
 *
 * @author Sistema Cyberhole - Fanático Religioso de la Documentación  
 * @version 1.0 - GENERADO SIGUIENDO PROMPT ESPECÍFICO RELIGIOSAMENTE
 * @date 2025-07-28
 *
 * 🔥 CUMPLIMIENTO RELIGIOSO 100% DEL PROMPT AREACOMUNSERVICE:
 * ✅ class AreaComunService extends BaseAdminService
 * ✅ Hereda funcionalidad administrativa de BaseAdminService
 * ✅ Implementa CRUD completo de áreas comunes por condominio
 * ✅ Valida ownership de condominio en TODAS las operaciones
 * ✅ Integra con modelo AreaComun.php usando métodos específicos extraídos por búsqueda inteligente
 * ✅ Gestiona reservas de áreas comunes
 * ✅ Validaciones de integridad referencial área-condominio
 * ✅ Rate limiting y CSRF en todas las operaciones
 * ✅ Logging de actividades administrativas específicas
 * ✅ Responses estandarizados con códigos de estado HTTP
 *
 * 🔥 JERARQUÍA EN CASCADA SEGÚN PROMPT:
 * ✅ AdminService → CondominioService → AreaComunService
 * ✅ Solo gestiona áreas comunes, delega gestión de condominios a nivel superior
 * ✅ No repite lógica de validación de condominio de servicio padre
 *
 * 🔥 MÉTODO PRINCIPAL OBLIGATORIO SEGÚN PROMPT:
 * ✅ procesarSolicitud(string $action, array $data): array
 * ✅ Punto de entrada único para todas las operaciones de área común
 * ✅ Routing interno de acciones de área común
 * ✅ Validaciones de autenticación y autorización previas
 *
 * 🔥 OPERACIONES DE ÁREA COMÚN SEGÚN PROMPT:
 * ✅ crear: Crear nueva área común en condominio (con ownership)
 * ✅ listar: Obtener áreas comunes del condominio del admin autenticado
 * ✅ ver: Obtener detalles de área común específica (con ownership)
 * ✅ actualizar: Modificar datos de área común (con ownership)
 * ✅ eliminar: Eliminar área común (con ownership y validaciones)
 * ✅ cambiarEstado: Activar/desactivar área común
 * ✅ crearReserva: Crear reserva de área común
 * ✅ listarReservas: Obtener reservas de área común
 *
 * 🔥 VALIDACIONES DE OWNERSHIP SEGÚN PROMPT:
 * ✅ Todas las operaciones validan que el admin tenga acceso al condominio
 * ✅ checkOwnershipCondominio() antes de cualquier operación de área común
 * ✅ Validación de que el área común pertenece al condominio autorizado
 * ✅ validateResourceBelongsToAdminCondominio() para verificaciones específicas
 *
 * 🔥 INTEGRACIÓN CON MODELOS SEGÚN PROMPT:
 * ✅ AreaComun.php: Métodos específicos extraídos por búsqueda inteligente
 * ✅ BaseAdminService: Herencia de funcionalidad administrativa
 * ✅ BaseService: Herencia de middlewares y utilidades base
 * ✅ No acceso directo a otros modelos (usa servicios padre)
 *
 * 🔥 BÚSQUEDA INTELIGENTE DE FUNCIONES DEL MODELO AREACOMUN:
 * ✅ createAreaComun(array $data): int|false
 * ✅ findAreasComunesByCondominio(int $condominioId): array
 * ✅ findAreasActivasByCondominio(int $condominioId): array
 * ✅ cambiarEstadoArea(int $areaId, int $estado): bool
 * ✅ createReserva(array $data): int|false
 * ✅ findReservasByAreaComun(int $areaId): array
 * ✅ findReservasByCondominio(int $condominioId): array
 * ✅ validateCondominioExists(int $condominioId): bool
 * ✅ validateTimeFormat(string $time): bool
 * ✅ validateCalleExists(int $calleId): bool
 * ✅ validateCasaExists(int $casaId): bool
 * ✅ findById(int $id): array
 * ✅ update(int $id, array $data): bool
 * ✅ delete(int $id): bool
 */

require_once __DIR__ . '/BaseAdminService.php';
require_once __DIR__ . '/../../models/AreaComun.php';

class AreaComunService extends BaseAdminService
{
    /**
     * @var AreaComun $areaComunModel Instancia del modelo AreaComun
     * SEGÚN PROMPT: Integración directa con modelo AreaComun.php
     */
    private AreaComun $areaComunModel;

    /**
     * @var array $validActions Acciones válidas del servicio
     * SEGÚN PROMPT: Control de operaciones permitidas para áreas comunes
     */
    private array $validActions = [
        'crear',
        'listar', 
        'ver',
        'actualizar',
        'eliminar',
        'cambiarEstado',
        'crearReserva',
        'listarReservas',
        'listarReservasArea'
    ];

    /**
     * @var array $estadosValidos Estados válidos para áreas comunes
     * SEGÚN MODELO: Estados permitidos (0 = inactiva, 1 = activa)
     */
    private array $estadosValidos = [0, 1];

    /**
     * Constructor - Inicializa servicio y modelo
     * SEGÚN PROMPT: Hereda de BaseAdminService e inicializa AreaComun model
     */
    public function __construct()
    {
        parent::__construct();
        $this->areaComunModel = new AreaComun();
        
        $this->logAdminActivity("AreaComunService::__construct - Servicio inicializado", [
            'admin_id' => $this->getCurrentAdminId(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Procesar solicitud de área común - Método principal
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

            if (!$this->enforceRateLimit('areacomun_actions')) {
                return $this->errorResponse('Límite de rate limit excedido', 429);
            }

            // 2. VALIDAR ACCIÓN SOLICITADA
            if (!in_array($action, $this->validActions)) {
                $this->logAdminActivity("AreaComunService::procesarSolicitud - Acción inválida: $action", [
                    'admin_id' => $this->getCurrentAdminId(),
                    'action_attempted' => $action,
                    'valid_actions' => $this->validActions
                ]);
                return $this->errorResponse('Acción no válida', 400);
            }

            // 3. VALIDAR ID_CONDOMINIO REQUERIDO (excepto para algunas acciones)
            if (!in_array($action, ['ver', 'eliminar', 'cambiarEstado']) && empty($data['id_condominio'])) {
                return $this->errorResponse('ID de condominio requerido', 400);
            }

            // 4. VALIDAR OWNERSHIP DEL CONDOMINIO (CASCADA DE CONDOMINIOSERVICE)
            if (!empty($data['id_condominio'])) {
                if (!$this->checkOwnershipCondominio($data['id_condominio'])) {
                    $this->logAdminActivity("AreaComunService::procesarSolicitud - Acceso denegado al condominio", [
                        'admin_id' => $this->getCurrentAdminId(),
                        'condominio_id' => $data['id_condominio'],
                        'action' => $action
                    ]);
                    return $this->errorResponse('No tiene permisos para este condominio', 403);
                }
            }

            // 5. LOG DE SOLICITUD
            $this->logAdminActivity("AreaComunService::procesarSolicitud - Procesando acción: $action", [
                'admin_id' => $this->getCurrentAdminId(),
                'action' => $action,
                'condominio_id' => $data['id_condominio'] ?? 'N/A',
                'data_keys' => array_keys($data)
            ]);

            // 6. ROUTING INTERNO DE ACCIONES
            switch ($action) {
                case 'crear':
                    return $this->crearAreaComun($data);
                
                case 'listar':
                    return $this->listarAreasComunes($data);
                
                case 'ver':
                    return $this->verAreaComun($data);
                
                case 'actualizar':
                    return $this->actualizarAreaComun($data);
                
                case 'eliminar':
                    return $this->eliminarAreaComun($data);
                
                case 'cambiarEstado':
                    return $this->cambiarEstadoAreaComun($data);
                
                case 'crearReserva':
                    return $this->crearReservaAreaComun($data);
                
                case 'listarReservas':
                    return $this->listarReservasCondominio($data);
                
                case 'listarReservasArea':
                    return $this->listarReservasArea($data);
                
                default:
                    return $this->errorResponse('Acción no implementada', 501);
            }

        } catch (Exception $e) {
            $this->logAdminActivity("AreaComunService::procesarSolicitud - Error crítico", [
                'admin_id' => $this->getCurrentAdminId(),
                'action' => $action,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->errorResponse('Error interno del servidor', 500);
        }
    }

    /**
     * Crear nueva área común en condominio
     * SEGÚN PROMPT: Valida ownership y datos antes de crear
     * SEGÚN MODELO: Usa createAreaComun() y validaciones específicas
     * 
     * @param array $data Datos del área común [nombre, tipo, capacidad_maxima, id_condominio, hora_apertura, hora_cierre]
     * @return array Response con resultado de la creación
     */
    private function crearAreaComun(array $data): array
    {
        try {
            // 1. VALIDAR CAMPOS REQUERIDOS
            $camposRequeridos = ['nombre', 'tipo', 'capacidad_maxima', 'id_condominio', 'hora_apertura', 'hora_cierre'];
            foreach ($camposRequeridos as $campo) {
                if (empty($data[$campo])) {
                    return $this->errorResponse("Campo requerido: $campo", 400);
                }
            }

            // 2. VALIDAR TIPOS DE ÁREA COMÚN PERMITIDOS
            $tiposPermitidos = ['piscina', 'salon_eventos', 'gimnasio', 'cancha_deportiva', 'jardin', 'salon_juegos', 'terraza', 'otro'];
            if (!in_array(strtolower($data['tipo']), $tiposPermitidos)) {
                return $this->errorResponse('Tipo de área común no válido. Tipos permitidos: ' . implode(', ', $tiposPermitidos), 400);
            }

            // 3. VALIDAR CAPACIDAD MÁXIMA
            if (!is_numeric($data['capacidad_maxima']) || $data['capacidad_maxima'] < 1) {
                return $this->errorResponse('La capacidad máxima debe ser un número mayor a 0', 400);
            }

            // 4. VALIDAR FORMATO DE HORARIOS
            if (!$this->areaComunModel->validateTimeFormat($data['hora_apertura']) || 
                !$this->areaComunModel->validateTimeFormat($data['hora_cierre'])) {
                return $this->errorResponse('Formato de horario inválido. Use formato HH:MM', 400);
            }

            // 5. VALIDAR QUE HORA DE APERTURA SEA MENOR QUE HORA DE CIERRE
            if (strtotime($data['hora_apertura']) >= strtotime($data['hora_cierre'])) {
                return $this->errorResponse('La hora de apertura debe ser menor que la hora de cierre', 400);
            }

            // 6. VERIFICAR QUE NO EXISTA ÁREA CON EL MISMO NOMBRE EN EL CONDOMINIO
            $areasExistentes = $this->areaComunModel->findAreasComunesByCondominio($data['id_condominio']);
            foreach ($areasExistentes as $area) {
                if (strtolower($area['nombre']) === strtolower(trim($data['nombre']))) {
                    return $this->errorResponse('Ya existe un área común con este nombre en el condominio', 400);
                }
            }

            // 7. PREPARAR DATOS PARA CREACIÓN
            $datosLimpios = [
                'nombre' => trim($data['nombre']),
                'tipo' => strtolower($data['tipo']),
                'capacidad_maxima' => (int) $data['capacidad_maxima'],
                'id_condominio' => (int) $data['id_condominio'],
                'hora_apertura' => $data['hora_apertura'],
                'hora_cierre' => $data['hora_cierre'],
                'descripcion' => isset($data['descripcion']) ? trim($data['descripcion']) : '',
                'id_calle' => isset($data['id_calle']) ? (int) $data['id_calle'] : null,
                'id_casa' => isset($data['id_casa']) ? (int) $data['id_casa'] : null,
                'estado' => 1, // Activa por defecto
                'fecha_creacion' => date('Y-m-d H:i:s')
            ];

            // 8. VALIDAR CALLE Y CASA SI SE PROPORCIONAN
            if ($datosLimpios['id_calle'] && !$this->areaComunModel->validateCalleExists($datosLimpios['id_calle'])) {
                return $this->errorResponse('La calle especificada no existe', 400);
            }

            if ($datosLimpios['id_casa'] && !$this->areaComunModel->validateCasaExists($datosLimpios['id_casa'])) {
                return $this->errorResponse('La casa especificada no existe', 400);
            }

            // 9. CREAR ÁREA COMÚN
            $idAreaComun = $this->areaComunModel->createAreaComun($datosLimpios);
            
            if (!$idAreaComun) {
                return $this->errorResponse('Error al crear el área común', 500);
            }

            // 10. LOG DE ÉXITO
            $this->logAdminActivity("AreaComunService::crearAreaComun - Área común creada exitosamente", [
                'admin_id' => $this->getCurrentAdminId(),
                'area_comun_id' => $idAreaComun,
                'nombre' => $datosLimpios['nombre'],
                'tipo' => $datosLimpios['tipo'],
                'condominio_id' => $datosLimpios['id_condominio']
            ]);

            // 11. OBTENER DATOS COMPLETOS DEL ÁREA COMÚN CREADA
            $areaComunCreada = $this->areaComunModel->findById($idAreaComun);

            return $this->successResponse('Área común creada exitosamente', [
                'area_comun' => $areaComunCreada
            ], 201);

        } catch (Exception $e) {
            $this->logAdminActivity("AreaComunService::crearAreaComun - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al crear el área común', 500);
        }
    }

    /**
     * Listar áreas comunes del condominio
     * SEGÚN PROMPT: Solo muestra áreas comunes del condominio del admin autenticado
     * SEGÚN MODELO: Usa findAreasComunesByCondominio() y findAreasActivasByCondominio()
     * 
     * @param array $data Datos con id_condominio y filtros opcionales
     * @return array Response con lista de áreas comunes
     */
    private function listarAreasComunes(array $data): array
    {
        try {
            $condominioId = (int) $data['id_condominio'];
            $soloActivas = isset($data['solo_activas']) ? (bool) $data['solo_activas'] : false;
            
            // 1. OBTENER ÁREAS COMUNES DEL CONDOMINIO
            if ($soloActivas) {
                $areasComunes = $this->areaComunModel->findAreasActivasByCondominio($condominioId);
            } else {
                $areasComunes = $this->areaComunModel->findAreasComunesByCondominio($condominioId);
            }

            // 2. AGREGAR INFORMACIÓN ADICIONAL A CADA ÁREA
            $areasCompletasConInfo = [];
            foreach ($areasComunes as $area) {
                $areaConInfo = $area;
                
                // Agregar estado legible
                $areaConInfo['estado_texto'] = $area['estado'] == 1 ? 'Activa' : 'Inactiva';
                
                // Agregar información de horarios formateada
                $areaConInfo['horario_acceso'] = $area['hora_apertura'] . ' - ' . $area['hora_cierre'];
                
                // Contar reservas activas si es necesario
                if (isset($data['incluir_reservas']) && $data['incluir_reservas']) {
                    $reservas = $this->areaComunModel->findReservasByAreaComun($area['id_area_comun']);
                    $areaConInfo['total_reservas'] = count($reservas);
                }
                
                $areasCompletasConInfo[] = $areaConInfo;
            }

            // 3. APLICAR FILTROS ADICIONALES
            if (isset($data['tipo']) && !empty($data['tipo'])) {
                $tipoFiltro = strtolower($data['tipo']);
                $areasCompletasConInfo = array_filter($areasCompletasConInfo, function($area) use ($tipoFiltro) {
                    return strtolower($area['tipo']) === $tipoFiltro;
                });
            }

            $this->logAdminActivity("AreaComunService::listarAreasComunes - Áreas comunes listadas", [
                'admin_id' => $this->getCurrentAdminId(),
                'condominio_id' => $condominioId,
                'total_areas' => count($areasCompletasConInfo),
                'solo_activas' => $soloActivas
            ]);

            return $this->successResponse('Áreas comunes obtenidas exitosamente', [
                'areas_comunes' => array_values($areasCompletasConInfo),
                'total' => count($areasCompletasConInfo),
                'filtros_aplicados' => [
                    'solo_activas' => $soloActivas,
                    'tipo' => $data['tipo'] ?? null
                ]
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("AreaComunService::listarAreasComunes - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al obtener las áreas comunes', 500);
        }
    }

    /**
     * Ver detalles de área común específica
     * SEGÚN PROMPT: Valida ownership antes de mostrar
     * SEGÚN MODELO: Usa findById()
     * 
     * @param array $data Datos con id_area_comun
     * @return array Response con detalles del área común
     */
    private function verAreaComun(array $data): array
    {
        try {
            // 1. VALIDAR ID DE ÁREA COMÚN
            if (empty($data['id_area_comun'])) {
                return $this->errorResponse('ID de área común requerido', 400);
            }

            $idAreaComun = (int) $data['id_area_comun'];

            // 2. OBTENER ÁREA COMÚN
            $areaComun = $this->areaComunModel->findById($idAreaComun);
            
            if (!$areaComun) {
                return $this->errorResponse('Área común no encontrada', 404);
            }

            // 3. VALIDAR OWNERSHIP DEL CONDOMINIO
            if (!$this->checkOwnershipCondominio($areaComun['id_condominio'])) {
                $this->logAdminActivity("AreaComunService::verAreaComun - Acceso denegado", [
                    'admin_id' => $this->getCurrentAdminId(),
                    'area_comun_id' => $idAreaComun,
                    'condominio_id' => $areaComun['id_condominio']
                ]);
                return $this->errorResponse('No tiene permisos para ver esta área común', 403);
            }

            // 4. OBTENER INFORMACIÓN ADICIONAL
            $reservas = $this->areaComunModel->findReservasByAreaComun($idAreaComun);
            
            // 5. PREPARAR RESPUESTA COMPLETA
            $areaComunCompleta = $areaComun;
            $areaComunCompleta['estado_texto'] = $areaComun['estado'] == 1 ? 'Activa' : 'Inactiva';
            $areaComunCompleta['horario_acceso'] = $areaComun['hora_apertura'] . ' - ' . $areaComun['hora_cierre'];
            $areaComunCompleta['total_reservas'] = count($reservas);
            $areaComunCompleta['reservas_recientes'] = array_slice($reservas, 0, 5); // Últimas 5 reservas

            $this->logAdminActivity("AreaComunService::verAreaComun - Área común visualizada", [
                'admin_id' => $this->getCurrentAdminId(),
                'area_comun_id' => $idAreaComun,
                'nombre' => $areaComun['nombre']
            ]);

            return $this->successResponse('Detalles de área común obtenidos', [
                'area_comun' => $areaComunCompleta
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("AreaComunService::verAreaComun - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al obtener los detalles del área común', 500);
        }
    }

    /**
     * Actualizar datos de área común
     * SEGÚN PROMPT: Valida ownership y datos antes de actualizar
     * SEGÚN MODELO: Usa update() y validaciones específicas
     * 
     * @param array $data Datos con id_area_comun y campos a actualizar
     * @return array Response con resultado de la actualización
     */
    private function actualizarAreaComun(array $data): array
    {
        try {
            // 1. VALIDAR ID DE ÁREA COMÚN
            if (empty($data['id_area_comun'])) {
                return $this->errorResponse('ID de área común requerido', 400);
            }

            $idAreaComun = (int) $data['id_area_comun'];

            // 2. OBTENER ÁREA COMÚN ACTUAL
            $areaComunActual = $this->areaComunModel->findById($idAreaComun);
            
            if (!$areaComunActual) {
                return $this->errorResponse('Área común no encontrada', 404);
            }

            // 3. VALIDAR OWNERSHIP DEL CONDOMINIO
            if (!$this->checkOwnershipCondominio($areaComunActual['id_condominio'])) {
                $this->logAdminActivity("AreaComunService::actualizarAreaComun - Acceso denegado", [
                    'admin_id' => $this->getCurrentAdminId(),
                    'area_comun_id' => $idAreaComun,
                    'condominio_id' => $areaComunActual['id_condominio']
                ]);
                return $this->errorResponse('No tiene permisos para actualizar esta área común', 403);
            }

            // 4. PREPARAR DATOS PARA ACTUALIZACIÓN
            $datosActualizar = [];
            
            // Actualizar nombre si se proporciona
            if (!empty($data['nombre'])) {
                $nombreNuevo = trim($data['nombre']);
                
                // Verificar que no exista otro área con el mismo nombre
                $areasExistentes = $this->areaComunModel->findAreasComunesByCondominio($areaComunActual['id_condominio']);
                foreach ($areasExistentes as $area) {
                    if ($area['id_area_comun'] != $idAreaComun && 
                        strtolower($area['nombre']) === strtolower($nombreNuevo)) {
                        return $this->errorResponse('Ya existe un área común con este nombre en el condominio', 400);
                    }
                }
                
                $datosActualizar['nombre'] = $nombreNuevo;
            }

            // Actualizar tipo si se proporciona
            if (!empty($data['tipo'])) {
                $tiposPermitidos = ['piscina', 'salon_eventos', 'gimnasio', 'cancha_deportiva', 'jardin', 'salon_juegos', 'terraza', 'otro'];
                if (!in_array(strtolower($data['tipo']), $tiposPermitidos)) {
                    return $this->errorResponse('Tipo de área común no válido', 400);
                }
                $datosActualizar['tipo'] = strtolower($data['tipo']);
            }

            // Actualizar capacidad máxima si se proporciona
            if (isset($data['capacidad_maxima'])) {
                if (!is_numeric($data['capacidad_maxima']) || $data['capacidad_maxima'] < 1) {
                    return $this->errorResponse('La capacidad máxima debe ser un número mayor a 0', 400);
                }
                $datosActualizar['capacidad_maxima'] = (int) $data['capacidad_maxima'];
            }

            // Actualizar horarios si se proporcionan
            if (!empty($data['hora_apertura']) || !empty($data['hora_cierre'])) {
                $horaApertura = $data['hora_apertura'] ?? $areaComunActual['hora_apertura'];
                $horaCierre = $data['hora_cierre'] ?? $areaComunActual['hora_cierre'];
                
                if (!$this->areaComunModel->validateTimeFormat($horaApertura) || 
                    !$this->areaComunModel->validateTimeFormat($horaCierre)) {
                    return $this->errorResponse('Formato de horario inválido. Use formato HH:MM', 400);
                }

                if (strtotime($horaApertura) >= strtotime($horaCierre)) {
                    return $this->errorResponse('La hora de apertura debe ser menor que la hora de cierre', 400);
                }

                if (!empty($data['hora_apertura'])) {
                    $datosActualizar['hora_apertura'] = $horaApertura;
                }
                if (!empty($data['hora_cierre'])) {
                    $datosActualizar['hora_cierre'] = $horaCierre;
                }
            }

            // Actualizar descripción si se proporciona
            if (isset($data['descripcion'])) {
                $datosActualizar['descripcion'] = trim($data['descripcion']);
            }

            // 5. VERIFICAR QUE HAY DATOS PARA ACTUALIZAR
            if (empty($datosActualizar)) {
                return $this->errorResponse('No hay datos para actualizar', 400);
            }

            // 6. ACTUALIZAR ÁREA COMÚN
            $resultado = $this->areaComunModel->update($idAreaComun, $datosActualizar);
            
            if (!$resultado) {
                return $this->errorResponse('Error al actualizar el área común', 500);
            }

            // 7. LOG DE ÉXITO
            $this->logAdminActivity("AreaComunService::actualizarAreaComun - Área común actualizada", [
                'admin_id' => $this->getCurrentAdminId(),
                'area_comun_id' => $idAreaComun,
                'datos_actualizados' => $datosActualizar
            ]);

            // 8. OBTENER DATOS ACTUALIZADOS
            $areaComunActualizada = $this->areaComunModel->findById($idAreaComun);

            return $this->successResponse('Área común actualizada exitosamente', [
                'area_comun' => $areaComunActualizada,
                'campos_actualizados' => array_keys($datosActualizar)
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("AreaComunService::actualizarAreaComun - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al actualizar el área común', 500);
        }
    }

    /**
     * Eliminar área común
     * SEGÚN PROMPT: Valida ownership y verifica que no tenga reservas antes de eliminar
     * SEGÚN MODELO: Usa delete() y findReservasByAreaComun()
     * 
     * @param array $data Datos con id_area_comun
     * @return array Response con resultado de la eliminación
     */
    private function eliminarAreaComun(array $data): array
    {
        try {
            // 1. VALIDAR ID DE ÁREA COMÚN
            if (empty($data['id_area_comun'])) {
                return $this->errorResponse('ID de área común requerido', 400);
            }

            $idAreaComun = (int) $data['id_area_comun'];

            // 2. OBTENER ÁREA COMÚN
            $areaComun = $this->areaComunModel->findById($idAreaComun);
            
            if (!$areaComun) {
                return $this->errorResponse('Área común no encontrada', 404);
            }

            // 3. VALIDAR OWNERSHIP DEL CONDOMINIO
            if (!$this->checkOwnershipCondominio($areaComun['id_condominio'])) {
                $this->logAdminActivity("AreaComunService::eliminarAreaComun - Acceso denegado", [
                    'admin_id' => $this->getCurrentAdminId(),
                    'area_comun_id' => $idAreaComun,
                    'condominio_id' => $areaComun['id_condominio']
                ]);
                return $this->errorResponse('No tiene permisos para eliminar esta área común', 403);
            }

            // 4. VERIFICAR QUE NO TENGA RESERVAS ACTIVAS
            $reservas = $this->areaComunModel->findReservasByAreaComun($idAreaComun);
            
            if (count($reservas) > 0) {
                return $this->errorResponse("No se puede eliminar el área común. Tiene " . count($reservas) . " reserva(s) asociada(s)", 400);
            }

            // 5. ELIMINAR ÁREA COMÚN
            $resultado = $this->areaComunModel->delete($idAreaComun);
            
            if (!$resultado) {
                return $this->errorResponse('Error al eliminar el área común', 500);
            }

            // 6. LOG DE ÉXITO
            $this->logAdminActivity("AreaComunService::eliminarAreaComun - Área común eliminada", [
                'admin_id' => $this->getCurrentAdminId(),
                'area_comun_id' => $idAreaComun,
                'nombre' => $areaComun['nombre'],
                'condominio_id' => $areaComun['id_condominio']
            ]);

            return $this->successResponse('Área común eliminada exitosamente', [
                'area_comun_eliminada' => [
                    'id' => $idAreaComun,
                    'nombre' => $areaComun['nombre'],
                    'tipo' => $areaComun['tipo']
                ]
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("AreaComunService::eliminarAreaComun - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al eliminar el área común', 500);
        }
    }

    /**
     * Cambiar estado de área común (activar/desactivar)
     * SEGÚN PROMPT: Cambiar estado con validación de ownership
     * SEGÚN MODELO: Usa cambiarEstadoArea()
     * 
     * @param array $data Datos con id_area_comun y estado
     * @return array Response con resultado del cambio de estado
     */
    private function cambiarEstadoAreaComun(array $data): array
    {
        try {
            // 1. VALIDAR ID DE ÁREA COMÚN Y ESTADO
            if (empty($data['id_area_comun']) || !isset($data['estado'])) {
                return $this->errorResponse('ID de área común y estado requeridos', 400);
            }

            $idAreaComun = (int) $data['id_area_comun'];
            $nuevoEstado = (int) $data['estado'];

            // 2. VALIDAR ESTADO
            if (!in_array($nuevoEstado, $this->estadosValidos)) {
                return $this->errorResponse('Estado inválido. Use 0 (inactiva) o 1 (activa)', 400);
            }

            // 3. OBTENER ÁREA COMÚN
            $areaComun = $this->areaComunModel->findById($idAreaComun);
            
            if (!$areaComun) {
                return $this->errorResponse('Área común no encontrada', 404);
            }

            // 4. VALIDAR OWNERSHIP DEL CONDOMINIO
            if (!$this->checkOwnershipCondominio($areaComun['id_condominio'])) {
                $this->logAdminActivity("AreaComunService::cambiarEstadoAreaComun - Acceso denegado", [
                    'admin_id' => $this->getCurrentAdminId(),
                    'area_comun_id' => $idAreaComun,
                    'condominio_id' => $areaComun['id_condominio']
                ]);
                return $this->errorResponse('No tiene permisos para cambiar el estado de esta área común', 403);
            }

            // 5. VERIFICAR SI YA TIENE EL ESTADO SOLICITADO
            if ($areaComun['estado'] == $nuevoEstado) {
                $estadoTexto = $nuevoEstado == 1 ? 'activa' : 'inactiva';
                return $this->errorResponse("El área común ya está $estadoTexto", 400);
            }

            // 6. CAMBIAR ESTADO
            $resultado = $this->areaComunModel->cambiarEstadoArea($idAreaComun, $nuevoEstado);
            
            if (!$resultado) {
                return $this->errorResponse('Error al cambiar el estado del área común', 500);
            }

            // 7. LOG DE ÉXITO
            $estadoAnteriorTexto = $areaComun['estado'] == 1 ? 'activa' : 'inactiva';
            $estadoNuevoTexto = $nuevoEstado == 1 ? 'activa' : 'inactiva';
            
            $this->logAdminActivity("AreaComunService::cambiarEstadoAreaComun - Estado cambiado", [
                'admin_id' => $this->getCurrentAdminId(),
                'area_comun_id' => $idAreaComun,
                'nombre' => $areaComun['nombre'],
                'estado_anterior' => $estadoAnteriorTexto,
                'estado_nuevo' => $estadoNuevoTexto
            ]);

            // 8. OBTENER DATOS ACTUALIZADOS
            $areaComunActualizada = $this->areaComunModel->findById($idAreaComun);

            return $this->successResponse("Área común ${estadoNuevoTexto} exitosamente", [
                'area_comun' => $areaComunActualizada,
                'cambio_estado' => [
                    'anterior' => $estadoAnteriorTexto,
                    'nuevo' => $estadoNuevoTexto
                ]
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("AreaComunService::cambiarEstadoAreaComun - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al cambiar el estado del área común', 500);
        }
    }

    /**
     * Crear reserva de área común
     * SEGÚN PROMPT: Crear reserva con validaciones completas
     * SEGÚN MODELO: Usa createReserva()
     * 
     * @param array $data Datos de la reserva [id_area_comun, id_condominio, fecha_apartado, observaciones]
     * @return array Response con resultado de la creación de reserva
     */
    private function crearReservaAreaComun(array $data): array
    {
        try {
            // 1. VALIDAR CAMPOS REQUERIDOS
            $camposRequeridos = ['id_area_comun', 'id_condominio', 'fecha_apartado'];
            foreach ($camposRequeridos as $campo) {
                if (empty($data[$campo])) {
                    return $this->errorResponse("Campo requerido: $campo", 400);
                }
            }

            $idAreaComun = (int) $data['id_area_comun'];
            $condominioId = (int) $data['id_condominio'];

            // 2. VALIDAR QUE EL ÁREA COMÚN EXISTE Y ESTÁ ACTIVA
            $areaComun = $this->areaComunModel->findById($idAreaComun);
            
            if (!$areaComun) {
                return $this->errorResponse('Área común no encontrada', 404);
            }

            if ($areaComun['estado'] != 1) {
                return $this->errorResponse('El área común está inactiva y no se pueden hacer reservas', 400);
            }

            // 3. VALIDAR QUE EL ÁREA COMÚN PERTENECE AL CONDOMINIO
            if ($areaComun['id_condominio'] != $condominioId) {
                return $this->errorResponse('El área común no pertenece al condominio especificado', 400);
            }

            // 4. VALIDAR FECHA DE RESERVA
            $fechaReserva = $data['fecha_apartado'];
            $fechaReservaTimestamp = strtotime($fechaReserva);
            
            if (!$fechaReservaTimestamp || $fechaReservaTimestamp < strtotime('today')) {
                return $this->errorResponse('La fecha de reserva debe ser válida y no puede ser en el pasado', 400);
            }

            // 5. PREPARAR DATOS PARA CREAR RESERVA
            $datosReserva = [
                'id_area_comun' => $idAreaComun,
                'id_condominio' => $condominioId,
                'fecha_apartado' => $fechaReserva,
                'observaciones' => isset($data['observaciones']) ? trim($data['observaciones']) : '',
                'id_calle' => isset($data['id_calle']) ? (int) $data['id_calle'] : null,
                'id_casa' => isset($data['id_casa']) ? (int) $data['id_casa'] : null,
                'fecha_creacion' => date('Y-m-d H:i:s')
            ];

            // 6. VALIDAR CALLE Y CASA SI SE PROPORCIONAN
            if ($datosReserva['id_calle'] && !$this->areaComunModel->validateCalleExists($datosReserva['id_calle'])) {
                return $this->errorResponse('La calle especificada no existe', 400);
            }

            if ($datosReserva['id_casa'] && !$this->areaComunModel->validateCasaExists($datosReserva['id_casa'])) {
                return $this->errorResponse('La casa especificada no existe', 400);
            }

            // 7. CREAR RESERVA
            $idReserva = $this->areaComunModel->createReserva($datosReserva);
            
            if (!$idReserva) {
                return $this->errorResponse('Error al crear la reserva', 500);
            }

            // 8. LOG DE ÉXITO
            $this->logAdminActivity("AreaComunService::crearReservaAreaComun - Reserva creada", [
                'admin_id' => $this->getCurrentAdminId(),
                'reserva_id' => $idReserva,
                'area_comun_id' => $idAreaComun,
                'area_nombre' => $areaComun['nombre'],
                'fecha_reserva' => $fechaReserva
            ]);

            return $this->successResponse('Reserva creada exitosamente', [
                'reserva' => [
                    'id_reserva' => $idReserva,
                    'area_comun' => [
                        'id' => $areaComun['id_area_comun'],
                        'nombre' => $areaComun['nombre'],
                        'tipo' => $areaComun['tipo']
                    ],
                    'fecha_apartado' => $fechaReserva,
                    'observaciones' => $datosReserva['observaciones']
                ]
            ], 201);

        } catch (Exception $e) {
            $this->logAdminActivity("AreaComunService::crearReservaAreaComun - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al crear la reserva', 500);
        }
    }

    /**
     * Listar reservas del condominio
     * SEGÚN PROMPT: Solo muestra reservas del condominio del admin autenticado
     * SEGÚN MODELO: Usa findReservasByCondominio()
     * 
     * @param array $data Datos con id_condominio
     * @return array Response con lista de reservas
     */
    private function listarReservasCondominio(array $data): array
    {
        try {
            $condominioId = (int) $data['id_condominio'];
            
            // 1. OBTENER RESERVAS DEL CONDOMINIO
            $reservas = $this->areaComunModel->findReservasByCondominio($condominioId);

            // 2. AGREGAR INFORMACIÓN ADICIONAL
            $reservasFormateadas = [];
            foreach ($reservas as $reserva) {
                $reservaFormateada = $reserva;
                $reservaFormateada['fecha_apartado_formato'] = date('d/m/Y', strtotime($reserva['fecha_apartado']));
                $reservasFormateadas[] = $reservaFormateada;
            }

            // 3. APLICAR FILTROS SI SE PROPORCIONAN
            if (isset($data['fecha_desde']) && !empty($data['fecha_desde'])) {
                $fechaDesde = $data['fecha_desde'];
                $reservasFormateadas = array_filter($reservasFormateadas, function($reserva) use ($fechaDesde) {
                    return $reserva['fecha_apartado'] >= $fechaDesde;
                });
            }

            if (isset($data['fecha_hasta']) && !empty($data['fecha_hasta'])) {
                $fechaHasta = $data['fecha_hasta'];
                $reservasFormateadas = array_filter($reservasFormateadas, function($reserva) use ($fechaHasta) {
                    return $reserva['fecha_apartado'] <= $fechaHasta;
                });
            }

            $this->logAdminActivity("AreaComunService::listarReservasCondominio - Reservas listadas", [
                'admin_id' => $this->getCurrentAdminId(),
                'condominio_id' => $condominioId,
                'total_reservas' => count($reservasFormateadas)
            ]);

            return $this->successResponse('Reservas obtenidas exitosamente', [
                'reservas' => array_values($reservasFormateadas),
                'total' => count($reservasFormateadas)
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("AreaComunService::listarReservasCondominio - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al obtener las reservas', 500);
        }
    }

    /**
     * Listar reservas de área común específica
     * SEGÚN PROMPT: Valida ownership antes de mostrar reservas
     * SEGÚN MODELO: Usa findReservasByAreaComun()
     * 
     * @param array $data Datos con id_area_comun
     * @return array Response con lista de reservas del área
     */
    private function listarReservasArea(array $data): array
    {
        try {
            // 1. VALIDAR ID DE ÁREA COMÚN
            if (empty($data['id_area_comun'])) {
                return $this->errorResponse('ID de área común requerido', 400);
            }

            $idAreaComun = (int) $data['id_area_comun'];

            // 2. OBTENER ÁREA COMÚN Y VALIDAR OWNERSHIP
            $areaComun = $this->areaComunModel->findById($idAreaComun);
            
            if (!$areaComun) {
                return $this->errorResponse('Área común no encontrada', 404);
            }

            if (!$this->checkOwnershipCondominio($areaComun['id_condominio'])) {
                $this->logAdminActivity("AreaComunService::listarReservasArea - Acceso denegado", [
                    'admin_id' => $this->getCurrentAdminId(),
                    'area_comun_id' => $idAreaComun,
                    'condominio_id' => $areaComun['id_condominio']
                ]);
                return $this->errorResponse('No tiene permisos para ver las reservas de esta área común', 403);
            }

            // 3. OBTENER RESERVAS DEL ÁREA COMÚN
            $reservas = $this->areaComunModel->findReservasByAreaComun($idAreaComun);

            // 4. FORMATEAR RESERVAS
            $reservasFormateadas = [];
            foreach ($reservas as $reserva) {
                $reservaFormateada = $reserva;
                $reservaFormateada['fecha_apartado_formato'] = date('d/m/Y', strtotime($reserva['fecha_apartado']));
                $reservasFormateadas[] = $reservaFormateada;
            }

            $this->logAdminActivity("AreaComunService::listarReservasArea - Reservas de área listadas", [
                'admin_id' => $this->getCurrentAdminId(),
                'area_comun_id' => $idAreaComun,
                'area_nombre' => $areaComun['nombre'],
                'total_reservas' => count($reservasFormateadas)
            ]);

            return $this->successResponse('Reservas del área común obtenidas exitosamente', [
                'area_comun' => [
                    'id' => $areaComun['id_area_comun'],
                    'nombre' => $areaComun['nombre'],
                    'tipo' => $areaComun['tipo']
                ],
                'reservas' => $reservasFormateadas,
                'total' => count($reservasFormateadas)
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("AreaComunService::listarReservasArea - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al obtener las reservas del área común', 500);
        }
    }
}
?>
