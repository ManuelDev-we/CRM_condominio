<?php
/**
 * 🚗 ENGOMADO SERVICE - GESTIÓN DE IDENTIFICADORES VEHICULARES
 * Sistema Cyberhole Condominios - Arquitectura 3 Capas
 * 
 * @description Servicio administrativo para gestionar engomados/stickers de identificación vehicular
 *              Implementación RELIGIOSA según ENGOMADOSERVICE_ADMIN_PROMPT.md
 * @author Sistema Cyberhole - Fanático Religioso de la Documentación
 * @version 1.0 - CREADO DESDE CERO CON MÉTODOS REALES DE MODELOS
 * @date 2025-07-28
 * 
 * 🔥 CUMPLIMIENTO RELIGIOSO DE ENGOMADOSERVICE_ADMIN_PROMPT.md:
 * ✅ Propósito: Administrar engomados/stickers de identificación vehicular
 * ✅ Modelos usados: Engomado.php, Persona.php
 * ✅ Funciones esperadas: CRUD engomados, asignación vehículos, vigencias, control acceso vehicular
 * ✅ Restricciones: Solo admin autenticado, solo su condominio, logs de auditoría
 * 
 * 🔥 ESTRUCTURA DE CASCADA FUNCIONAL SEGUIDA:
 * AdminService → CondominioService → EmpleadoService → CasaService → PersonaCasaService → TagService → EngomadoService (Nivel 7)
 * 
 * 🔥 MÉTODOS REALES USADOS DE LOS MODELOS:
 * 📁 Engomado.php:
 * ✅ createEngomado(array $data): int|false
 * ✅ findById(int $id): array|null
 * ✅ update(int $id, array $data): bool
 * ✅ updateEngomado(int $id, array $data): bool
 * ✅ delete(int $id): bool
 * ✅ findAll(int $limit = 100): array
 * ✅ findByPersonaId(int $personaId): array
 * ✅ findByCasaId(int $casaId): array
 * ✅ findByPlaca(string $placa): array|null
 * ✅ findEngomadosActivos(): array
 * ✅ activateEngomado(int $id): bool
 * ✅ deactivateEngomado(int $id): bool
 * ✅ validatePlacaFormat(string $placa): bool
 * ✅ validatePersonaExists(int $personaId): bool
 * ✅ validateCasaExists(int $casaId): bool
 * ✅ validateCondominioExists(int $condominioId): bool
 * ✅ validateCalleExists(int $calleId): bool
 * ✅ getEngomadosStats(): array
 * ✅ searchEngomados(array $filters = []): array
 * 
 * 📁 Persona.php:
 * ✅ findById(int $id): array|null
 * ✅ findAll(int $limit = 100): array
 */

require_once __DIR__ . '/BaseAdminService.php';
require_once __DIR__ . '/../../../models/Engomado.php';
require_once __DIR__ . '/../../../models/Persona.php';

class EngomadoService extends BaseAdminService
{
    /**
     * @var Engomado $engomadoModel Modelo de engomados
     */
    private Engomado $engomadoModel;
    
    /**
     * @var Persona $personaModel Modelo de personas
     */
    private Persona $personaModel;
    
    /**
     * @var string $serviceName Nombre del servicio para logs
     */
    protected string $serviceName = 'EngomadoService';
    
    /**
     * Constructor del servicio
     */
    public function __construct()
    {
        parent::__construct();
        $this->engomadoModel = new Engomado();
        $this->personaModel = new Persona();
    }
    
    // ==========================================
    // MÉTODOS PRINCIPALES DE GESTIÓN DE ENGOMADOS
    // ==========================================
    
    /**
     * 🚗 CREAR NUEVO ENGOMADO
     * Método principal para registrar un nuevo engomado vehicular en el sistema
     * 
     * @param array $data ['id_persona', 'id_casa', 'id_condominio', 'id_calle', 'placa', 'modelo', 'color', 'anio', 'foto', 'activo']
     * @return array Respuesta estructurada
     */
    public function crearEngomado(array $data): array
    {
        try {
            // Validar autenticación admin
            if (!$this->authAdmin()) {
                return $this->errorResponse("Acceso denegado", 401);
            }
            
            // Validar CSRF
            if (!$this->checkCSRF('POST')) {
                return $this->errorResponse("Token CSRF inválido", 403);
            }
            
            // Rate limiting
            if (!$this->enforceRateLimit('create_engomado_' . $this->getAdminId(), 15, 300)) {
                return $this->errorResponse("Demasiadas solicitudes. Intenta más tarde.", 429);
            }
            
            // Validar campos requeridos según el modelo Engomado
            $requiredFields = ['id_persona', 'id_casa', 'id_condominio', 'id_calle', 'placa'];
            if (!$this->validateRequiredFields($data, $requiredFields)) {
                return $this->errorResponse("Campos requeridos: " . implode(', ', $requiredFields), 400);
            }
            
            $condominioId = (int)$data['id_condominio'];
            $personaId = (int)$data['id_persona'];
            $casaId = (int)$data['id_casa'];
            $calleId = (int)$data['id_calle'];
            $placa = strtoupper(trim($data['placa']));
            
            // Validar ownership del condominio
            if (!$this->checkOwnershipCondominio($this->getAdminId(), $condominioId)) {
                return $this->errorResponse("No tienes permisos para gestionar engomados en este condominio", 403);
            }
            
            // Validar formato de placa usando el método real del modelo
            if (!$this->engomadoModel->validatePlacaFormat($placa)) {
                return $this->errorResponse("Formato de placa inválido. Debe seguir el formato mexicano estándar.", 400);
            }
            
            // Validar que la placa no exista (unicidad)
            $placaExistente = $this->engomadoModel->findByPlaca($placa);
            if ($placaExistente) {
                return $this->errorResponse("La placa {$placa} ya está registrada en el sistema", 409);
            }
            
            // Validar que la persona existe usando el método real del modelo
            if (!$this->engomadoModel->validatePersonaExists($personaId)) {
                return $this->errorResponse("La persona con ID {$personaId} no existe", 404);
            }
            
            // Validar que la casa existe usando el método real del modelo
            if (!$this->engomadoModel->validateCasaExists($casaId)) {
                return $this->errorResponse("La casa con ID {$casaId} no existe", 404);
            }
            
            // Validar que el condominio existe usando el método real del modelo
            if (!$this->engomadoModel->validateCondominioExists($condominioId)) {
                return $this->errorResponse("El condominio con ID {$condominioId} no existe", 404);
            }
            
            // Validar que la calle existe usando el método real del modelo
            if (!$this->engomadoModel->validateCalleExists($calleId)) {
                return $this->errorResponse("La calle con ID {$calleId} no existe", 404);
            }
            
            // Preparar datos para crear el engomado
            $engomadoData = [
                'id_persona' => $personaId,
                'id_casa' => $casaId,
                'id_condominio' => $condominioId,
                'id_calle' => $calleId,
                'placa' => $placa,
                'modelo' => $data['modelo'] ?? '',
                'color' => $data['color'] ?? '',
                'anio' => isset($data['anio']) ? (int)$data['anio'] : null,
                'foto' => $data['foto'] ?? '',
                'activo' => isset($data['activo']) ? (int)$data['activo'] : 1
            ];
            
            // Crear engomado usando el método real del modelo
            $engomadoId = $this->engomadoModel->createEngomado($engomadoData);
            
            if ($engomadoId) {
                // Log de actividad
                $this->logAdminActivity(
                    $this->getAdminId(),
                    'CREAR_ENGOMADO',
                    "Nuevo engomado creado: Placa {$placa} para persona ID {$personaId}",
                    [
                        'engomado_id' => $engomadoId,
                        'persona_id' => $personaId,
                        'casa_id' => $casaId,
                        'condominio_id' => $condominioId,
                        'placa' => $placa,
                        'modelo' => $engomadoData['modelo'],
                        'color' => $engomadoData['color']
                    ]
                );
                
                // Obtener datos del engomado creado
                $engomadoCreado = $this->engomadoModel->findById($engomadoId);
                
                return $this->successResponse(
                    "Engomado vehicular creado correctamente",
                    [
                        'engomado' => $engomadoCreado,
                        'id' => $engomadoId
                    ]
                );
            } else {
                return $this->errorResponse("Error al crear engomado. Verifica los datos proporcionados.", 400);
            }
            
        } catch (Exception $e) {
            $this->logError("EngomadoService::crearEngomado - Error: " . $e->getMessage());
            return $this->errorResponse("Error interno del servidor", 500);
        }
    }
    
    /**
     * 📝 ACTUALIZAR ENGOMADO
     * Método para modificar datos de un engomado existente
     * 
     * @param int $engomadoId ID del engomado
     * @param array $data Datos a actualizar
     * @return array Respuesta estructurada
     */
    public function actualizarEngomado(int $engomadoId, array $data): array
    {
        try {
            // Validar autenticación admin
            if (!$this->authAdmin()) {
                return $this->errorResponse("Acceso denegado", 401);
            }
            
            // Validar CSRF
            if (!$this->checkCSRF('POST')) {
                return $this->errorResponse("Token CSRF inválido", 403);
            }
            
            // Validar que el engomado existe usando el método real del modelo
            $engomado = $this->engomadoModel->findById($engomadoId);
            if (!$engomado) {
                return $this->errorResponse("Engomado no encontrado", 404);
            }
            
            // Validar ownership del condominio
            if (!$this->checkOwnershipCondominio($this->getAdminId(), $engomado['id_condominio'])) {
                return $this->errorResponse("No tienes permisos para gestionar este engomado", 403);
            }
            
            // Si se está actualizando la placa, validar formato y unicidad
            if (isset($data['placa']) && $data['placa'] !== $engomado['placa']) {
                $nuevaPlaca = strtoupper(trim($data['placa']));
                
                // Validar formato
                if (!$this->engomadoModel->validatePlacaFormat($nuevaPlaca)) {
                    return $this->errorResponse("Formato de placa inválido", 400);
                }
                
                // Validar unicidad
                $placaExistente = $this->engomadoModel->findByPlaca($nuevaPlaca);
                if ($placaExistente) {
                    return $this->errorResponse("La placa {$nuevaPlaca} ya está registrada en el sistema", 409);
                }
                
                $data['placa'] = $nuevaPlaca;
            }
            
            // Si se está actualizando la persona, validar que existe
            if (isset($data['id_persona']) && $data['id_persona'] !== $engomado['id_persona']) {
                if (!$this->engomadoModel->validatePersonaExists($data['id_persona'])) {
                    return $this->errorResponse("La persona con ID {$data['id_persona']} no existe", 404);
                }
            }
            
            // Si se está actualizando la casa, validar que existe
            if (isset($data['id_casa']) && $data['id_casa'] !== $engomado['id_casa']) {
                if (!$this->engomadoModel->validateCasaExists($data['id_casa'])) {
                    return $this->errorResponse("La casa con ID {$data['id_casa']} no existe", 404);
                }
            }
            
            // Actualizar usando el método real del modelo
            $success = $this->engomadoModel->updateEngomado($engomadoId, $data);
            
            if ($success) {
                // Log de actividad
                $this->logAdminActivity(
                    $this->getAdminId(),
                    'ACTUALIZAR_ENGOMADO',
                    "Engomado actualizado: ID {$engomadoId}, Placa: {$engomado['placa']}",
                    [
                        'engomado_id' => $engomadoId,
                        'campos_actualizados' => array_keys($data),
                        'condominio_id' => $engomado['id_condominio']
                    ]
                );
                
                // Obtener datos actualizados
                $engomadoActualizado = $this->engomadoModel->findById($engomadoId);
                
                return $this->successResponse(
                    "Engomado actualizado correctamente",
                    ['engomado' => $engomadoActualizado]
                );
            } else {
                return $this->errorResponse("Error al actualizar engomado", 500);
            }
            
        } catch (Exception $e) {
            $this->logError("EngomadoService::actualizarEngomado - Error: " . $e->getMessage());
            return $this->errorResponse("Error interno del servidor", 500);
        }
    }
    
    /**
     * 🗑️ ELIMINAR ENGOMADO
     * Método para eliminar un engomado del sistema
     * 
     * @param int $engomadoId ID del engomado
     * @return array Respuesta estructurada
     */
    public function eliminarEngomado(int $engomadoId): array
    {
        try {
            // Validar autenticación admin
            if (!$this->authAdmin()) {
                return $this->errorResponse("Acceso denegado", 401);
            }
            
            // Validar CSRF
            if (!$this->checkCSRF('POST')) {
                return $this->errorResponse("Token CSRF inválido", 403);
            }
            
            // Validar que el engomado existe
            $engomado = $this->engomadoModel->findById($engomadoId);
            if (!$engomado) {
                return $this->errorResponse("Engomado no encontrado", 404);
            }
            
            // Validar ownership del condominio
            if (!$this->checkOwnershipCondominio($this->getAdminId(), $engomado['id_condominio'])) {
                return $this->errorResponse("No tienes permisos para eliminar este engomado", 403);
            }
            
            // Eliminar usando el método real del modelo
            $success = $this->engomadoModel->delete($engomadoId);
            
            if ($success) {
                // Log de actividad
                $this->logAdminActivity(
                    $this->getAdminId(),
                    'ELIMINAR_ENGOMADO',
                    "Engomado eliminado: ID {$engomadoId}, Placa: {$engomado['placa']}",
                    [
                        'engomado_id' => $engomadoId,
                        'placa' => $engomado['placa'],
                        'persona_id' => $engomado['id_persona'],
                        'condominio_id' => $engomado['id_condominio']
                    ]
                );
                
                return $this->successResponse(
                    "Engomado eliminado correctamente",
                    ['engomado_id' => $engomadoId]
                );
            } else {
                return $this->errorResponse("Error al eliminar engomado", 500);
            }
            
        } catch (Exception $e) {
            $this->logError("EngomadoService::eliminarEngomado - Error: " . $e->getMessage());
            return $this->errorResponse("Error interno del servidor", 500);
        }
    }
    
    /**
     * 🔄 CAMBIAR ESTADO DE ENGOMADO
     * Método para activar/desactivar un engomado
     * 
     * @param int $engomadoId ID del engomado
     * @param bool $activo Estado activo (true/false)
     * @param string|null $razon Razón del cambio de estado
     * @return array Respuesta estructurada
     */
    public function cambiarEstadoEngomado(int $engomadoId, bool $activo, ?string $razon = null): array
    {
        try {
            // Validar autenticación admin
            if (!$this->authAdmin()) {
                return $this->errorResponse("Acceso denegado", 401);
            }
            
            // Validar CSRF
            if (!$this->checkCSRF('POST')) {
                return $this->errorResponse("Token CSRF inválido", 403);
            }
            
            // Validar que el engomado existe
            $engomado = $this->engomadoModel->findById($engomadoId);
            if (!$engomado) {
                return $this->errorResponse("Engomado no encontrado", 404);
            }
            
            // Validar ownership del condominio
            if (!$this->checkOwnershipCondominio($this->getAdminId(), $engomado['id_condominio'])) {
                return $this->errorResponse("No tienes permisos para modificar este engomado", 403);
            }
            
            // Cambiar estado usando el método real del modelo
            $success = $activo ? 
                $this->engomadoModel->activateEngomado($engomadoId) : 
                $this->engomadoModel->deactivateEngomado($engomadoId);
            
            if ($success) {
                $estado = $activo ? 'activado' : 'desactivado';
                
                // Log de actividad
                $this->logAdminActivity(
                    $this->getAdminId(),
                    'CAMBIAR_ESTADO_ENGOMADO',
                    "Engomado {$estado}: ID {$engomadoId}, Placa: {$engomado['placa']}" . ($razon ? " - Razón: {$razon}" : ""),
                    [
                        'engomado_id' => $engomadoId,
                        'placa' => $engomado['placa'],
                        'estado_anterior' => $engomado['activo'],
                        'estado_nuevo' => $activo,
                        'razon' => $razon,
                        'condominio_id' => $engomado['id_condominio']
                    ]
                );
                
                return $this->successResponse(
                    "Engomado {$estado} correctamente",
                    [
                        'engomado_id' => $engomadoId,
                        'estado' => $activo ? 'activo' : 'inactivo',
                        'razon' => $razon
                    ]
                );
            } else {
                return $this->errorResponse("Error al cambiar estado del engomado", 500);
            }
            
        } catch (Exception $e) {
            $this->logError("EngomadoService::cambiarEstadoEngomado - Error: " . $e->getMessage());
            return $this->errorResponse("Error interno del servidor", 500);
        }
    }
    
    /**
     * ✅ VALIDAR ACCESO VEHICULAR
     * Método para validar si un vehículo puede acceder basado en su placa
     * NOTA: Este método puede ser llamado desde dispositivos externos
     * 
     * @param string $placa Placa del vehículo
     * @param string $area Área de acceso
     * @param string $tipoAcceso Tipo de acceso ('ingreso' o 'salida')
     * @return array Respuesta estructurada
     */
    public function validarAccesoVehicular(string $placa, string $area, string $tipoAcceso = 'ingreso'): array
    {
        try {
            $placa = strtoupper(trim($placa));
            
            // Rate limiting para validaciones de acceso
            if (!$this->enforceRateLimit('validate_vehicle_access_' . $placa, 20, 60)) {
                $this->logAccesoVehicular(null, $area, 'rate_limit_exceeded', $placa);
                return $this->errorResponse("Demasiadas validaciones. Intenta más tarde.", 429);
            }
            
            // Buscar engomado por placa usando el método real del modelo
            $engomado = $this->engomadoModel->findByPlaca($placa);
            if (!$engomado) {
                $this->logAccesoVehicular(null, $area, 'engomado_no_encontrado', $placa);
                return $this->errorResponse("Vehículo no registrado", 404);
            }
            
            // Validar que el engomado esté activo
            if (!$engomado['activo']) {
                $this->logAccesoVehicular($engomado['id_engomado'], $area, 'engomado_inactivo');
                return $this->errorResponse("Engomado inactivo", 403);
            }
            
            // Validar que la persona asociada existe
            $persona = $this->personaModel->findById($engomado['id_persona']);
            if (!$persona) {
                $this->logAccesoVehicular($engomado['id_engomado'], $area, 'persona_no_encontrada');
                return $this->errorResponse("Propietario no encontrado", 404);
            }
            
            // Log de acceso exitoso
            $this->logAccesoVehicular($engomado['id_engomado'], $area, 'acceso_permitido', $placa, $tipoAcceso);
            
            return $this->successResponse(
                "Acceso vehicular permitido",
                [
                    'engomado_id' => $engomado['id_engomado'],
                    'placa' => $engomado['placa'],
                    'persona_id' => $engomado['id_persona'],
                    'persona_nombre' => $persona['nombres'] . ' ' . $persona['apellido1'],
                    'casa_id' => $engomado['id_casa'],
                    'condominio_id' => $engomado['id_condominio'],
                    'vehiculo_info' => [
                        'modelo' => $engomado['modelo'],
                        'color' => $engomado['color'],
                        'anio' => $engomado['anio']
                    ],
                    'acceso_permitido' => true,
                    'area' => $area,
                    'tipo_acceso' => $tipoAcceso,
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            );
            
        } catch (Exception $e) {
            $this->logError("EngomadoService::validarAccesoVehicular - Error: " . $e->getMessage());
            $this->logAccesoVehicular(null, $area, 'error_sistema', $placa);
            return $this->errorResponse("Error interno del servidor", 500);
        }
    }
    
    // ==========================================
    // MÉTODOS DE BÚSQUEDA Y CONSULTA
    // ==========================================
    
    /**
     * 🔍 OBTENER ENGOMADOS POR PERSONA
     * Método para listar todos los engomados de una persona específica
     * 
     * @param int $personaId ID de la persona
     * @return array Respuesta estructurada
     */
    public function obtenerEngomadosPorPersona(int $personaId): array
    {
        try {
            // Validar autenticación admin
            if (!$this->authAdmin()) {
                return $this->errorResponse("Acceso denegado", 401);
            }
            
            // Validar que la persona existe
            $persona = $this->personaModel->findById($personaId);
            if (!$persona) {
                return $this->errorResponse("Persona no encontrada", 404);
            }
            
            // Obtener engomados usando el método real del modelo
            $engomados = $this->engomadoModel->findByPersonaId($personaId);
            
            // Filtrar engomados por ownership del condominio del admin
            $adminId = $this->getAdminId();
            $engomadosFiltrados = [];
            
            foreach ($engomados as $engomado) {
                if ($this->checkOwnershipCondominio($adminId, $engomado['id_condominio'])) {
                    $engomadosFiltrados[] = $engomado;
                }
            }
            
            return $this->successResponse(
                "Engomados de la persona obtenidos correctamente",
                [
                    'persona' => [
                        'id' => $persona['id_persona'],
                        'nombre_completo' => $persona['nombres'] . ' ' . $persona['apellido1'] . ' ' . ($persona['apellido2'] ?? ''),
                        'correo' => $persona['correo_electronico']
                    ],
                    'engomados' => $engomadosFiltrados,
                    'total' => count($engomadosFiltrados)
                ]
            );
            
        } catch (Exception $e) {
            $this->logError("EngomadoService::obtenerEngomadosPorPersona - Error: " . $e->getMessage());
            return $this->errorResponse("Error interno del servidor", 500);
        }
    }
    
    /**
     * 🏠 OBTENER ENGOMADOS POR CASA
     * Método para listar todos los engomados de una casa específica
     * 
     * @param int $casaId ID de la casa
     * @return array Respuesta estructurada
     */
    public function obtenerEngomadosPorCasa(int $casaId): array
    {
        try {
            // Validar autenticación admin
            if (!$this->authAdmin()) {
                return $this->errorResponse("Acceso denegado", 401);
            }
            
            // Obtener engomados usando el método real del modelo
            $engomados = $this->engomadoModel->findByCasaId($casaId);
            
            // Validar ownership del condominio (usando el primer engomado para obtener el condominio)
            if (!empty($engomados)) {
                $primerEngomado = $engomados[0];
                if (!$this->checkOwnershipCondominio($this->getAdminId(), $primerEngomado['id_condominio'])) {
                    return $this->errorResponse("No tienes permisos para ver engomados de esta casa", 403);
                }
            }
            
            // Enriquecer datos con información de personas
            foreach ($engomados as &$engomado) {
                $persona = $this->personaModel->findById($engomado['id_persona']);
                if ($persona) {
                    $engomado['persona_nombre'] = $persona['nombres'] . ' ' . $persona['apellido1'] . ' ' . ($persona['apellido2'] ?? '');
                    $engomado['persona_correo'] = $persona['correo_electronico'];
                }
            }
            
            return $this->successResponse(
                "Engomados de la casa obtenidos correctamente",
                [
                    'casa_id' => $casaId,
                    'engomados' => $engomados,
                    'total' => count($engomados)
                ]
            );
            
        } catch (Exception $e) {
            $this->logError("EngomadoService::obtenerEngomadosPorCasa - Error: " . $e->getMessage());
            return $this->errorResponse("Error interno del servidor", 500);
        }
    }
    
    /**
     * 🚗 BUSCAR ENGOMADO POR PLACA
     * Método para buscar un engomado específico por su placa
     * 
     * @param string $placa Placa del vehículo
     * @return array Respuesta estructurada
     */
    public function buscarEngomadoPorPlaca(string $placa): array
    {
        try {
            // Validar autenticación admin
            if (!$this->authAdmin()) {
                return $this->errorResponse("Acceso denegado", 401);
            }
            
            $placa = strtoupper(trim($placa));
            
            // Buscar engomado usando el método real del modelo
            $engomado = $this->engomadoModel->findByPlaca($placa);
            if (!$engomado) {
                return $this->errorResponse("Engomado no encontrado", 404);
            }
            
            // Validar ownership del condominio
            if (!$this->checkOwnershipCondominio($this->getAdminId(), $engomado['id_condominio'])) {
                return $this->errorResponse("No tienes permisos para ver este engomado", 403);
            }
            
            // Enriquecer con datos de la persona
            $persona = $this->personaModel->findById($engomado['id_persona']);
            if ($persona) {
                $engomado['persona_nombre'] = $persona['nombres'] . ' ' . $persona['apellido1'] . ' ' . ($persona['apellido2'] ?? '');
                $engomado['persona_correo'] = $persona['correo_electronico'];
            }
            
            return $this->successResponse(
                "Engomado encontrado correctamente",
                ['engomado' => $engomado]
            );
            
        } catch (Exception $e) {
            $this->logError("EngomadoService::buscarEngomadoPorPlaca - Error: " . $e->getMessage());
            return $this->errorResponse("Error interno del servidor", 500);
        }
    }
    
    /**
     * 🔍 BUSCAR ENGOMADOS CON FILTROS
     * Método para buscar engomados con múltiples criterios
     * 
     * @param array $filtros Criterios de búsqueda
     * @return array Respuesta estructurada
     */
    public function buscarEngomados(array $filtros = []): array
    {
        try {
            // Validar autenticación admin
            if (!$this->authAdmin()) {
                return $this->errorResponse("Acceso denegado", 401);
            }
            
            // Obtener engomados usando el método real del modelo
            $engomados = $this->engomadoModel->searchEngomados($filtros);
            
            // Filtrar por ownership del condominio del admin
            $adminId = $this->getAdminId();
            $engomadosFiltrados = [];
            
            foreach ($engomados as $engomado) {
                if ($this->checkOwnershipCondominio($adminId, $engomado['id_condominio'])) {
                    // Enriquecer con datos de la persona
                    $persona = $this->personaModel->findById($engomado['id_persona']);
                    if ($persona) {
                        $engomado['persona_nombre'] = $persona['nombres'] . ' ' . $persona['apellido1'] . ' ' . ($persona['apellido2'] ?? '');
                        $engomado['persona_correo'] = $persona['correo_electronico'];
                    }
                    $engomadosFiltrados[] = $engomado;
                }
            }
            
            return $this->successResponse(
                "Búsqueda de engomados completada",
                [
                    'engomados' => $engomadosFiltrados,
                    'total' => count($engomadosFiltrados),
                    'filtros_aplicados' => $filtros
                ]
            );
            
        } catch (Exception $e) {
            $this->logError("EngomadoService::buscarEngomados - Error: " . $e->getMessage());
            return $this->errorResponse("Error interno del servidor", 500);
        }
    }
    
    /**
     * 📊 OBTENER ESTADÍSTICAS DE ENGOMADOS
     * Método para obtener estadísticas generales de engomados
     * 
     * @param int|null $condominioId ID del condominio (opcional)
     * @return array Respuesta estructurada
     */
    public function obtenerEstadisticasEngomados(?int $condominioId = null): array
    {
        try {
            // Validar autenticación admin
            if (!$this->authAdmin()) {
                return $this->errorResponse("Acceso denegado", 401);
            }
            
            // Si se especifica condominio, validar ownership
            if ($condominioId && !$this->checkOwnershipCondominio($this->getAdminId(), $condominioId)) {
                return $this->errorResponse("No tienes permisos para ver estadísticas de este condominio", 403);
            }
            
            // Obtener estadísticas usando el método real del modelo
            $estadisticasGenerales = $this->engomadoModel->getEngomadosStats();
            
            // Obtener engomados activos
            $engomadosActivos = $this->engomadoModel->findEngomadosActivos();
            $todoEngomados = $this->engomadoModel->findAll(1000);
            
            // Filtrar por condominio si se especifica y por ownership del admin
            $adminId = $this->getAdminId();
            if ($condominioId) {
                $engomadosActivos = array_filter($engomadosActivos, function($eng) use ($condominioId) {
                    return $eng['id_condominio'] == $condominioId;
                });
                $todoEngomados = array_filter($todoEngomados, function($eng) use ($condominioId) {
                    return $eng['id_condominio'] == $condominioId;
                });
            } else {
                // Filtrar por ownership del admin
                $engomadosActivos = array_filter($engomadosActivos, function($eng) use ($adminId) {
                    return $this->checkOwnershipCondominio($adminId, $eng['id_condominio']);
                });
                $todoEngomados = array_filter($todoEngomados, function($eng) use ($adminId) {
                    return $this->checkOwnershipCondominio($adminId, $eng['id_condominio']);
                });
            }
            
            $estadisticasCompletas = [
                'total_engomados' => count($todoEngomados),
                'engomados_activos' => count($engomadosActivos),
                'engomados_inactivos' => count($todoEngomados) - count($engomadosActivos),
                'porcentaje_activos' => count($todoEngomados) > 0 ? round((count($engomadosActivos) / count($todoEngomados)) * 100, 2) : 0,
                'estadisticas_generales' => $estadisticasGenerales,
                'fecha_consulta' => date('Y-m-d H:i:s')
            ];
            
            return $this->successResponse(
                "Estadísticas de engomados obtenidas correctamente",
                [
                    'condominio_id' => $condominioId,
                    'estadisticas' => $estadisticasCompletas
                ]
            );
            
        } catch (Exception $e) {
            $this->logError("EngomadoService::obtenerEstadisticasEngomados - Error: " . $e->getMessage());
            return $this->errorResponse("Error interno del servidor", 500);
        }
    }
    
    // ==========================================
    // MÉTODOS DE VALIDACIÓN ESPECÍFICOS
    // ==========================================
    
    /**
     * ✅ VERIFICAR FORMATO DE PLACA
     * Método para verificar si una placa tiene formato válido
     * 
     * @param string $placa Placa a verificar
     * @return array Respuesta estructurada
     */
    public function verificarFormatoPlaca(string $placa): array
    {
        try {
            // Validar autenticación admin
            if (!$this->authAdmin()) {
                return $this->errorResponse("Acceso denegado", 401);
            }
            
            $placa = strtoupper(trim($placa));
            
            // Verificar formato usando el método real del modelo
            $formatoValido = $this->engomadoModel->validatePlacaFormat($placa);
            
            return $this->successResponse(
                "Verificación de formato completada",
                [
                    'placa' => $placa,
                    'formato_valido' => $formatoValido,
                    'mensaje' => $formatoValido ? 'Formato válido' : 'Formato inválido - debe seguir el estándar mexicano'
                ]
            );
            
        } catch (Exception $e) {
            $this->logError("EngomadoService::verificarFormatoPlaca - Error: " . $e->getMessage());
            return $this->errorResponse("Error interno del servidor", 500);
        }
    }
    
    // ==========================================
    // MÉTODOS DE AUDITORÍA Y LOGS
    // ==========================================
    
    /**
     * 📝 LOG DE ACCESO VEHICULAR
     * Método interno para registrar intentos de acceso vehicular
     * 
     * @param int|null $engomadoId ID del engomado (null si no se encontró)
     * @param string $area Área de acceso
     * @param string $resultado Resultado del acceso
     * @param string|null $placa Placa intentada (si el engomado no se encontró)
     * @param string $tipoAcceso Tipo de acceso (ingreso/salida)
     * @return void
     */
    private function logAccesoVehicular(?int $engomadoId, string $area, string $resultado, ?string $placa = null, string $tipoAcceso = 'ingreso'): void
    {
        try {
            $logData = [
                'engomado_id' => $engomadoId,
                'area' => $area,
                'resultado' => $resultado,
                'tipo_acceso' => $tipoAcceso,
                'timestamp' => date('Y-m-d H:i:s'),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ];
            
            if ($placa) {
                $logData['placa_intentada'] = $placa;
            }
            
            // Log interno del servicio
            $this->logInfo("VehicleAccess: " . json_encode($logData));
            
            // Si existe el engomado, también hacer log de actividad admin
            if ($engomadoId) {
                $engomado = $this->engomadoModel->findById($engomadoId);
                if ($engomado) {
                    $this->logAdminActivity(
                        null, // Sin admin específico para accesos de dispositivos
                        'ENGOMADO_ACCESO',
                        "Intento de acceso vehicular - Placa: {$engomado['placa']} en área {$area}: {$resultado}",
                        $logData
                    );
                }
            }
            
        } catch (Exception $e) {
            $this->logError("EngomadoService::logAccesoVehicular - Error al registrar log: " . $e->getMessage());
        }
    }
    
    /**
     * 📋 GENERAR REPORTE DE VEHICULOS ACTIVOS
     * Método para generar reporte de vehículos activos en el condominio
     * 
     * @param int $condominioId ID del condominio
     * @return array Respuesta estructurada
     */
    public function generarReporteVehiculosActivos(int $condominioId): array
    {
        try {
            // Validar autenticación admin
            if (!$this->authAdmin()) {
                return $this->errorResponse("Acceso denegado", 401);
            }
            
            // Validar ownership del condominio
            if (!$this->checkOwnershipCondominio($this->getAdminId(), $condominioId)) {
                return $this->errorResponse("No tienes permisos para generar reportes de este condominio", 403);
            }
            
            // Rate limiting para reportes
            if (!$this->enforceRateLimit('reporte_vehiculos_' . $this->getAdminId(), 5, 300)) {
                return $this->errorResponse("Demasiadas solicitudes de reportes. Intenta más tarde.", 429);
            }
            
            // Obtener engomados activos
            $engomadosActivos = $this->engomadoModel->findEngomadosActivos();
            $engomadosCondominio = array_filter($engomadosActivos, function($eng) use ($condominioId) {
                return $eng['id_condominio'] == $condominioId;
            });
            
            // Enriquecer con datos de personas
            foreach ($engomadosCondominio as &$engomado) {
                $persona = $this->personaModel->findById($engomado['id_persona']);
                if ($persona) {
                    $engomado['persona_nombre'] = $persona['nombres'] . ' ' . $persona['apellido1'];
                    $engomado['persona_correo'] = $persona['correo_electronico'];
                }
            }
            
            $reporte = [
                'condominio_id' => $condominioId,
                'fecha_reporte' => date('Y-m-d H:i:s'),
                'total_vehiculos_activos' => count($engomadosCondominio),
                'vehiculos_detalle' => $engomadosCondominio
            ];
            
            return $this->successResponse(
                "Reporte de vehículos activos generado correctamente",
                ['reporte' => $reporte]
            );
            
        } catch (Exception $e) {
            $this->logError("EngomadoService::generarReporteVehiculosActivos - Error: " . $e->getMessage());
            return $this->errorResponse("Error interno del servidor", 500);
        }
    }
}
