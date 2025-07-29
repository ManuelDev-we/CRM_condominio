<?php
/**
 * 🏠 PERSONA CASA SERVICE - GESTIÓN DE RELACIONES PERSONA-CASA
 * Sistema Cyberhole Condominios - Arquitectura 3 Capas
 * 
 * @description Servicio administrativo para gestionar relaciones entre personas y casas
 *              Implementación RELIGIOSA según DETALLE_SERVICIOS_ADMIN.md
 * @author Sistema Cyberhole - Fanático Religioso de la Documentación
 * @version 1.0 - CREADO DESDE CERO CON MÉTODOS REALES DE MODELOS
 * @date 2025-07-28
 * 
 * 🔥 CUMPLIMIENTO RELIGIOSO DE DETALLE_SERVICIOS_ADMIN.md:
 * ✅ Propósito: Gestión de relaciones entre personas y casas
 * ✅ Modelos usados: Persona.php, Casa.php
 * ✅ Funciones esperadas: Asignación de personas a casas, validación de relaciones, CRUD de ocupantes
 * ✅ Restricciones: Solo admin autenticado, solo su condominio, logs de auditoría
 * 
 * 🔥 ESTRUCTURA DE CASCADA FUNCIONAL SEGUIDA:
 * AdminService → CondominioService → EmpleadoService → CasaService → PersonaCasaService
 * 
 * 🔥 MÉTODOS REALES USADOS DE LOS MODELOS:
 * 📁 Casa.php:
 * ✅ assignPersonaToCasa(int $personaId, int $casaId): bool
 * ✅ removePersonaFromCasa(int $personaId, int $casaId): bool
 * ✅ getPersonasByCasa(int $casaId): array
 * ✅ getCasasByPersona(int $personaId): array
 * ✅ isPersonaAssignedToCasa(int $personaId, int $casaId): bool
 * ✅ validatePersonaExists(int $personaId): bool
 * ✅ validateCasaExists(int $casaId): bool
 * ✅ findCasaById(int $id): array|null
 * 
 * 📁 Persona.php:
 * ✅ findById(int $id): array|null
 * ✅ findByEmail(string $email): array|null
 * ✅ findByCURP(string $curp): array|null
 * ✅ create(array $data): int|false
 * ✅ update(int $id, array $data): bool
 * ✅ delete(int $id): bool
 * ✅ findAll(int $limit = 100): array
 */

require_once __DIR__ . '/BaseAdminService.php';
require_once __DIR__ . '/../../../models/Persona.php';
require_once __DIR__ . '/../../../models/Casa.php';

class PersonaCasaService extends BaseAdminService
{
    /**
     * @var Persona $personaModel Modelo de personas
     */
    private Persona $personaModel;
    
    /**
     * @var Casa $casaModel Modelo de casas
     */
    private Casa $casaModel;
    
    /**
     * @var string $serviceName Nombre del servicio para logs
     */
    protected string $serviceName = 'PersonaCasaService';
    
    /**
     * Constructor del servicio
     */
    public function __construct()
    {
        parent::__construct();
        $this->personaModel = new Persona();
        $this->casaModel = new Casa();
    }
    
    // ==========================================
    // MÉTODOS PRINCIPALES DE GESTIÓN DE RELACIONES
    // ==========================================
    
    /**
     * 🔗 ASIGNAR PERSONA A CASA
     * Método principal para crear relación persona-casa
     * 
     * @param array $data ['persona_id', 'casa_id']
     * @return array Respuesta estructurada
     */
    public function asignarPersonaACasa(array $data): array
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
            
            // Validar campos requeridos
            $requiredFields = ['persona_id', 'casa_id'];
            if (!$this->validateRequiredFields($data, $requiredFields)) {
                return $this->errorResponse("Campos requeridos: " . implode(', ', $requiredFields), 400);
            }
            
            $personaId = (int)$data['persona_id'];
            $casaId = (int)$data['casa_id'];
            
            // Validar que la persona existe
            if (!$this->casaModel->validatePersonaExists($personaId)) {
                return $this->errorResponse("La persona con ID {$personaId} no existe", 404);
            }
            
            // Validar que la casa existe
            if (!$this->casaModel->validateCasaExists($casaId)) {
                return $this->errorResponse("La casa con ID {$casaId} no existe", 404);
            }
            
            // Validar ownership de la casa
            $casa = $this->casaModel->findCasaById($casaId);
            if (!$casa) {
                return $this->errorResponse("No se pudo obtener información de la casa", 500);
            }
            
            if (!$this->checkOwnershipCondominio($this->getAdminId(), $casa['id_condominio'])) {
                return $this->errorResponse("No tienes permisos para gestionar esta casa", 403);
            }
            
            // Verificar que la relación no existe ya
            if ($this->casaModel->isPersonaAssignedToCasa($personaId, $casaId)) {
                return $this->errorResponse("La persona ya está asignada a esta casa", 409);
            }
            
            // Realizar la asignación usando el método real del modelo
            $success = $this->casaModel->assignPersonaToCasa($personaId, $casaId);
            
            if ($success) {
                // Log de actividad
                $this->logAdminActivity(
                    $this->getAdminId(),
                    'ASIGNAR_PERSONA_CASA',
                    "Persona ID {$personaId} asignada a Casa ID {$casaId}",
                    ['persona_id' => $personaId, 'casa_id' => $casaId]
                );
                
                return $this->successResponse(
                    "Persona asignada a casa correctamente",
                    [
                        'persona_id' => $personaId,
                        'casa_id' => $casaId,
                        'relacion' => 'activa'
                    ]
                );
            } else {
                return $this->errorResponse("Error al asignar persona a casa", 500);
            }
            
        } catch (Exception $e) {
            $this->logError("PersonaCasaService::asignarPersonaACasa - Error: " . $e->getMessage());
            return $this->errorResponse("Error interno del servidor", 500);
        }
    }
    
    /**
     * ❌ REMOVER PERSONA DE CASA
     * Método para eliminar relación persona-casa
     * 
     * @param array $data ['persona_id', 'casa_id']
     * @return array Respuesta estructurada
     */
    public function removerPersonaDeCasa(array $data): array
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
            
            // Validar campos requeridos
            $requiredFields = ['persona_id', 'casa_id'];
            if (!$this->validateRequiredFields($data, $requiredFields)) {
                return $this->errorResponse("Campos requeridos: " . implode(', ', $requiredFields), 400);
            }
            
            $personaId = (int)$data['persona_id'];
            $casaId = (int)$data['casa_id'];
            
            // Validar ownership de la casa
            $casa = $this->casaModel->findCasaById($casaId);
            if (!$casa) {
                return $this->errorResponse("Casa no encontrada", 404);
            }
            
            if (!$this->checkOwnershipCondominio($this->getAdminId(), $casa['id_condominio'])) {
                return $this->errorResponse("No tienes permisos para gestionar esta casa", 403);
            }
            
            // Verificar que la relación existe
            if (!$this->casaModel->isPersonaAssignedToCasa($personaId, $casaId)) {
                return $this->errorResponse("La persona no está asignada a esta casa", 404);
            }
            
            // Realizar la remoción usando el método real del modelo
            $success = $this->casaModel->removePersonaFromCasa($personaId, $casaId);
            
            if ($success) {
                // Log de actividad
                $this->logAdminActivity(
                    $this->getAdminId(),
                    'REMOVER_PERSONA_CASA',
                    "Persona ID {$personaId} removida de Casa ID {$casaId}",
                    ['persona_id' => $personaId, 'casa_id' => $casaId]
                );
                
                return $this->successResponse(
                    "Persona removida de casa correctamente",
                    [
                        'persona_id' => $personaId,
                        'casa_id' => $casaId,
                        'relacion' => 'eliminada'
                    ]
                );
            } else {
                return $this->errorResponse("Error al remover persona de casa", 500);
            }
            
        } catch (Exception $e) {
            $this->logError("PersonaCasaService::removerPersonaDeCasa - Error: " . $e->getMessage());
            return $this->errorResponse("Error interno del servidor", 500);
        }
    }
    
    /**
     * 👥 OBTENER PERSONAS POR CASA
     * Método para listar residentes de una casa específica
     * 
     * @param int $casaId ID de la casa
     * @param array $options Opciones de filtrado ['limite', 'buscar']
     * @return array Respuesta estructurada
     */
    public function obtenerPersonasPorCasa(int $casaId, array $options = []): array
    {
        try {
            // Validar autenticación admin
            if (!$this->authAdmin()) {
                return $this->errorResponse("Acceso denegado", 401);
            }
            
            // Validar que la casa existe
            $casa = $this->casaModel->findCasaById($casaId);
            if (!$casa) {
                return $this->errorResponse("Casa no encontrada", 404);
            }
            
            // Validar ownership
            if (!$this->checkOwnershipCondominio($this->getAdminId(), $casa['id_condominio'])) {
                return $this->errorResponse("No tienes permisos para ver esta casa", 403);
            }
            
            // Obtener personas usando el método real del modelo
            $personas = $this->casaModel->getPersonasByCasa($casaId);
            
            // Aplicar filtros si se especifican
            if (isset($options['buscar']) && !empty($options['buscar'])) {
                $termino = strtolower($options['buscar']);
                $personas = array_filter($personas, function($persona) use ($termino) {
                    $nombreCompleto = strtolower($persona['nombres'] . ' ' . $persona['apellido1'] . ' ' . ($persona['apellido2'] ?? ''));
                    $email = strtolower($persona['correo_electronico'] ?? '');
                    return strpos($nombreCompleto, $termino) !== false || strpos($email, $termino) !== false;
                });
            }
            
            // Aplicar límite si se especifica
            if (isset($options['limite']) && is_numeric($options['limite'])) {
                $personas = array_slice($personas, 0, (int)$options['limite']);
            }
            
            // Limpiar datos sensibles para respuesta
            foreach ($personas as &$persona) {
                unset($persona['contrasena']);
                // Solo mostrar información necesaria
                $persona['nombre_completo'] = $persona['nombres'] . ' ' . $persona['apellido1'] . ' ' . ($persona['apellido2'] ?? '');
            }
            
            return $this->successResponse(
                "Personas de la casa obtenidas correctamente",
                [
                    'casa' => [
                        'id' => $casa['id_casa'],
                        'numero' => $casa['casa'],
                        'calle' => $casa['calle_nombre'],
                        'condominio' => $casa['condominio_nombre']
                    ],
                    'personas' => $personas,
                    'total' => count($personas)
                ]
            );
            
        } catch (Exception $e) {
            $this->logError("PersonaCasaService::obtenerPersonasPorCasa - Error: " . $e->getMessage());
            return $this->errorResponse("Error interno del servidor", 500);
        }
    }
    
    /**
     * 🏠 OBTENER CASAS POR PERSONA
     * Método para listar casas asignadas a una persona específica
     * 
     * @param int $personaId ID de la persona
     * @return array Respuesta estructurada
     */
    public function obtenerCasasPorPersona(int $personaId): array
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
            
            // Obtener casas usando el método real del modelo
            $casas = $this->casaModel->getCasasByPersona($personaId);
            
            // Filtrar solo casas del condominio del admin
            $adminId = $this->getAdminId();
            $casasFiltradas = [];
            
            foreach ($casas as $casa) {
                if ($this->checkOwnershipCondominio($adminId, $casa['id_condominio'])) {
                    $casasFiltradas[] = $casa;
                }
            }
            
            // Limpiar datos de la persona para respuesta
            unset($persona['contrasena']);
            $persona['nombre_completo'] = $persona['nombres'] . ' ' . $persona['apellido1'] . ' ' . ($persona['apellido2'] ?? '');
            
            return $this->successResponse(
                "Casas de la persona obtenidas correctamente",
                [
                    'persona' => $persona,
                    'casas' => $casasFiltradas,
                    'total' => count($casasFiltradas)
                ]
            );
            
        } catch (Exception $e) {
            $this->logError("PersonaCasaService::obtenerCasasPorPersona - Error: " . $e->getMessage());
            return $this->errorResponse("Error interno del servidor", 500);
        }
    }
    
    // ==========================================
    // MÉTODOS DE GESTIÓN DE PERSONAS
    // ==========================================
    
    /**
     * 👤 CREAR NUEVA PERSONA
     * Método para registrar una nueva persona en el sistema
     * 
     * @param array $data Datos de la persona
     * @return array Respuesta estructurada
     */
    public function crearPersona(array $data): array
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
            if (!$this->enforceRateLimit('create_persona_' . $this->getAdminId(), 10, 300)) {
                return $this->errorResponse("Demasiadas solicitudes. Intenta más tarde.", 429);
            }
            
            // Validar campos requeridos del modelo Persona
            $requiredFields = ['curp', 'nombres', 'apellido1', 'correo_electronico', 'contrasena', 'fecha_nacimiento'];
            if (!$this->validateRequiredFields($data, $requiredFields)) {
                return $this->errorResponse("Campos requeridos: " . implode(', ', $requiredFields), 400);
            }
            
            // Crear persona usando el método real del modelo
            $personaId = $this->personaModel->create($data);
            
            if ($personaId) {
                // Log de actividad
                $this->logAdminActivity(
                    $this->getAdminId(),
                    'CREAR_PERSONA',
                    "Nueva persona creada: {$data['nombres']} {$data['apellido1']}",
                    ['persona_id' => $personaId, 'email' => $data['correo_electronico']]
                );
                
                // Obtener datos de la persona creada (sin contraseña)
                $personaCreada = $this->personaModel->findById($personaId);
                unset($personaCreada['contrasena']);
                
                return $this->successResponse(
                    "Persona creada correctamente",
                    [
                        'persona' => $personaCreada,
                        'id' => $personaId
                    ]
                );
            } else {
                return $this->errorResponse("Error al crear persona. Verifica los datos proporcionados.", 400);
            }
            
        } catch (Exception $e) {
            $this->logError("PersonaCasaService::crearPersona - Error: " . $e->getMessage());
            return $this->errorResponse("Error interno del servidor", 500);
        }
    }
    
    /**
     * 📝 ACTUALIZAR PERSONA
     * Método para modificar datos de una persona existente
     * 
     * @param int $personaId ID de la persona
     * @param array $data Datos a actualizar
     * @return array Respuesta estructurada
     */
    public function actualizarPersona(int $personaId, array $data): array
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
            
            // Validar que la persona existe
            $persona = $this->personaModel->findById($personaId);
            if (!$persona) {
                return $this->errorResponse("Persona no encontrada", 404);
            }
            
            // Validar que el admin puede gestionar esta persona (debe estar en alguna casa de su condominio)
            $casasPersona = $this->casaModel->getCasasByPersona($personaId);
            $adminId = $this->getAdminId();
            $puedeGestionar = false;
            
            foreach ($casasPersona as $casa) {
                if ($this->checkOwnershipCondominio($adminId, $casa['id_condominio'])) {
                    $puedeGestionar = true;
                    break;
                }
            }
            
            if (!$puedeGestionar) {
                return $this->errorResponse("No tienes permisos para gestionar esta persona", 403);
            }
            
            // Actualizar usando el método real del modelo
            $success = $this->personaModel->update($personaId, $data);
            
            if ($success) {
                // Log de actividad
                $this->logAdminActivity(
                    $this->getAdminId(),
                    'ACTUALIZAR_PERSONA',
                    "Persona actualizada: {$persona['nombres']} {$persona['apellido1']}",
                    ['persona_id' => $personaId, 'campos_actualizados' => array_keys($data)]
                );
                
                // Obtener datos actualizados (sin contraseña)
                $personaActualizada = $this->personaModel->findById($personaId);
                unset($personaActualizada['contrasena']);
                
                return $this->successResponse(
                    "Persona actualizada correctamente",
                    ['persona' => $personaActualizada]
                );
            } else {
                return $this->errorResponse("Error al actualizar persona", 500);
            }
            
        } catch (Exception $e) {
            $this->logError("PersonaCasaService::actualizarPersona - Error: " . $e->getMessage());
            return $this->errorResponse("Error interno del servidor", 500);
        }
    }
    
    /**
     * 🗑️ ELIMINAR PERSONA
     * Método para eliminar una persona del sistema
     * 
     * @param int $personaId ID de la persona
     * @return array Respuesta estructurada
     */
    public function eliminarPersona(int $personaId): array
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
            
            // Validar que la persona existe
            $persona = $this->personaModel->findById($personaId);
            if (!$persona) {
                return $this->errorResponse("Persona no encontrada", 404);
            }
            
            // Validar ownership (persona debe estar en condominio del admin)
            $casasPersona = $this->casaModel->getCasasByPersona($personaId);
            $adminId = $this->getAdminId();
            $puedeEliminar = false;
            
            foreach ($casasPersona as $casa) {
                if ($this->checkOwnershipCondominio($adminId, $casa['id_condominio'])) {
                    $puedeEliminar = true;
                    break;
                }
            }
            
            if (!$puedeEliminar && !empty($casasPersona)) {
                return $this->errorResponse("No tienes permisos para eliminar esta persona", 403);
            }
            
            // Eliminar usando el método real del modelo (maneja automáticamente relaciones)
            $success = $this->personaModel->delete($personaId);
            
            if ($success) {
                // Log de actividad
                $this->logAdminActivity(
                    $this->getAdminId(),
                    'ELIMINAR_PERSONA',
                    "Persona eliminada: {$persona['nombres']} {$persona['apellido1']}",
                    ['persona_id' => $personaId, 'email' => $persona['correo_electronico']]
                );
                
                return $this->successResponse(
                    "Persona eliminada correctamente",
                    ['persona_id' => $personaId]
                );
            } else {
                return $this->errorResponse("Error al eliminar persona", 500);
            }
            
        } catch (Exception $e) {
            $this->logError("PersonaCasaService::eliminarPersona - Error: " . $e->getMessage());
            return $this->errorResponse("Error interno del servidor", 500);
        }
    }
    
    // ==========================================
    // MÉTODOS DE BÚSQUEDA Y CONSULTA
    // ==========================================
    
    /**
     * 🔍 BUSCAR PERSONAS
     * Método para buscar personas con diversos criterios
     * 
     * @param array $filtros ['buscar', 'limite', 'offset', 'ordenar_por', 'orden']
     * @return array Respuesta estructurada
     */
    public function buscarPersonas(array $filtros = []): array
    {
        try {
            // Validar autenticación admin
            if (!$this->authAdmin()) {
                return $this->errorResponse("Acceso denegado", 401);
            }
            
            // Configurar filtros por defecto
            $limite = isset($filtros['limite']) ? (int)$filtros['limite'] : 50;
            $limite = min($limite, 100); // Máximo 100
            
            // Obtener todas las personas usando el método real del modelo
            $todasPersonas = $this->personaModel->findAll($limite * 3); // Obtener más para filtrar
            
            // Obtener IDs de personas que están en condominios del admin
            $adminId = $this->getAdminId();
            $personasPermitidas = [];
            
            foreach ($todasPersonas as $persona) {
                $casasPersona = $this->casaModel->getCasasByPersona($persona['id_persona']);
                
                foreach ($casasPersona as $casa) {
                    if ($this->checkOwnershipCondominio($adminId, $casa['id_condominio'])) {
                        $personasPermitidas[] = $persona;
                        break; // Ya encontramos una casa válida
                    }
                }
            }
            
            // Aplicar filtro de búsqueda si se especifica
            if (isset($filtros['buscar']) && !empty($filtros['buscar'])) {
                $termino = strtolower($filtros['buscar']);
                $personasPermitidas = array_filter($personasPermitidas, function($persona) use ($termino) {
                    $nombreCompleto = strtolower($persona['nombres'] . ' ' . $persona['apellido1'] . ' ' . ($persona['apellido2'] ?? ''));
                    $email = strtolower($persona['correo_electronico'] ?? '');
                    $curp = strtolower($persona['curp'] ?? '');
                    
                    return strpos($nombreCompleto, $termino) !== false || 
                           strpos($email, $termino) !== false || 
                           strpos($curp, $termino) !== false;
                });
            }
            
            // Aplicar límite final
            $personasPermitidas = array_slice($personasPermitidas, 0, $limite);
            
            // Limpiar datos sensibles
            foreach ($personasPermitidas as &$persona) {
                unset($persona['contrasena']);
                $persona['nombre_completo'] = $persona['nombres'] . ' ' . $persona['apellido1'] . ' ' . ($persona['apellido2'] ?? '');
            }
            
            return $this->successResponse(
                "Búsqueda de personas completada",
                [
                    'personas' => $personasPermitidas,
                    'total' => count($personasPermitidas),
                    'filtros_aplicados' => $filtros
                ]
            );
            
        } catch (Exception $e) {
            $this->logError("PersonaCasaService::buscarPersonas - Error: " . $e->getMessage());
            return $this->errorResponse("Error interno del servidor", 500);
        }
    }
    
    /**
     * 👤 OBTENER DETALLE DE PERSONA
     * Método para obtener información completa de una persona
     * 
     * @param int $personaId ID de la persona
     * @return array Respuesta estructurada
     */
    public function obtenerDetallePersona(int $personaId): array
    {
        try {
            // Validar autenticación admin
            if (!$this->authAdmin()) {
                return $this->errorResponse("Acceso denegado", 401);
            }
            
            // Obtener persona usando el método real del modelo
            $persona = $this->personaModel->findById($personaId);
            if (!$persona) {
                return $this->errorResponse("Persona no encontrada", 404);
            }
            
            // Validar ownership
            $casasPersona = $this->casaModel->getCasasByPersona($personaId);
            $adminId = $this->getAdminId();
            $puedeVer = false;
            
            foreach ($casasPersona as $casa) {
                if ($this->checkOwnershipCondominio($adminId, $casa['id_condominio'])) {
                    $puedeVer = true;
                    break;
                }
            }
            
            if (!$puedeVer && !empty($casasPersona)) {
                return $this->errorResponse("No tienes permisos para ver esta persona", 403);
            }
            
            // Limpiar datos sensibles
            unset($persona['contrasena']);
            $persona['nombre_completo'] = $persona['nombres'] . ' ' . $persona['apellido1'] . ' ' . ($persona['apellido2'] ?? '');
            
            return $this->successResponse(
                "Detalle de persona obtenido correctamente",
                [
                    'persona' => $persona,
                    'casas' => $casasPersona,
                    'total_casas' => count($casasPersona)
                ]
            );
            
        } catch (Exception $e) {
            $this->logError("PersonaCasaService::obtenerDetallePersona - Error: " . $e->getMessage());
            return $this->errorResponse("Error interno del servidor", 500);
        }
    }
    
    // ==========================================
    // MÉTODOS DE VALIDACIÓN ESPECÍFICOS
    // ==========================================
    
    /**
     * ✅ VERIFICAR RELACIÓN PERSONA-CASA
     * Método para verificar si existe relación entre persona y casa
     * 
     * @param int $personaId ID de la persona
     * @param int $casaId ID de la casa
     * @return array Respuesta estructurada
     */
    public function verificarRelacionPersonaCasa(int $personaId, int $casaId): array
    {
        try {
            // Validar autenticación admin
            if (!$this->authAdmin()) {
                return $this->errorResponse("Acceso denegado", 401);
            }
            
            // Validar que la casa existe y pertenece al admin
            $casa = $this->casaModel->findCasaById($casaId);
            if (!$casa) {
                return $this->errorResponse("Casa no encontrada", 404);
            }
            
            if (!$this->checkOwnershipCondominio($this->getAdminId(), $casa['id_condominio'])) {
                return $this->errorResponse("No tienes permisos para ver esta casa", 403);
            }
            
            // Verificar relación usando el método real del modelo
            $existe = $this->casaModel->isPersonaAssignedToCasa($personaId, $casaId);
            
            return $this->successResponse(
                "Verificación de relación completada",
                [
                    'persona_id' => $personaId,
                    'casa_id' => $casaId,
                    'relacion_existe' => $existe,
                    'estado' => $existe ? 'asignada' : 'no_asignada'
                ]
            );
            
        } catch (Exception $e) {
            $this->logError("PersonaCasaService::verificarRelacionPersonaCasa - Error: " . $e->getMessage());
            return $this->errorResponse("Error interno del servidor", 500);
        }
    }
    
    // ==========================================
    // MÉTODOS DE REPORTE Y ESTADÍSTICAS
    // ==========================================
    
    /**
     * 📊 OBTENER ESTADÍSTICAS GENERALES
     * Método para obtener estadísticas de personas y casas del condominio
     * 
     * @return array Respuesta estructurada
     */
    public function obtenerEstadisticasGenerales(): array
    {
        try {
            // Validar autenticación admin
            if (!$this->authAdmin()) {
                return $this->errorResponse("Acceso denegado", 401);
            }
            
            $adminId = $this->getAdminId();
            $condominiosAdmin = $this->getCondominiosAdmin($adminId);
            
            $estadisticas = [
                'total_condominios' => count($condominiosAdmin),
                'total_casas' => 0,
                'total_personas' => 0,
                'total_relaciones' => 0,
                'casas_ocupadas' => 0,
                'casas_vacias' => 0,
                'promedio_habitantes_por_casa' => 0
            ];
            
            foreach ($condominiosAdmin as $condominio) {
                // Obtener casas del condominio
                $casasCondominio = $this->casaModel->findCasasByCondominioId($condominio['id_condominio']);
                $estadisticas['total_casas'] += count($casasCondominio);
                
                foreach ($casasCondominio as $casa) {
                    // Obtener personas de cada casa
                    $personasCasa = $this->casaModel->getPersonasByCasa($casa['id_casa']);
                    $numPersonas = count($personasCasa);
                    
                    $estadisticas['total_relaciones'] += $numPersonas;
                    
                    if ($numPersonas > 0) {
                        $estadisticas['casas_ocupadas']++;
                    } else {
                        $estadisticas['casas_vacias']++;
                    }
                }
            }
            
            // Calcular promedio
            if ($estadisticas['total_casas'] > 0) {
                $estadisticas['promedio_habitantes_por_casa'] = round($estadisticas['total_relaciones'] / $estadisticas['total_casas'], 2);
            }
            
            return $this->successResponse(
                "Estadísticas generales obtenidas correctamente",
                ['estadisticas' => $estadisticas]
            );
            
        } catch (Exception $e) {
            $this->logError("PersonaCasaService::obtenerEstadisticasGenerales - Error: " . $e->getMessage());
            return $this->errorResponse("Error interno del servidor", 500);
        }
    }
    
    /**
     * 📋 GENERAR REPORTE COMPLETO DE CONDOMINIO
     * Método para generar reporte detallado de personas y casas
     * 
     * @param int $condominioId ID del condominio
     * @return array Respuesta estructurada
     */
    public function generarReporteCondominio(int $condominioId): array
    {
        try {
            // Validar autenticación admin
            if (!$this->authAdmin()) {
                return $this->errorResponse("Acceso denegado", 401);
            }
            
            // Validar ownership del condominio
            if (!$this->checkOwnershipCondominio($this->getAdminId(), $condominioId)) {
                return $this->errorResponse("No tienes permisos para ver este condominio", 403);
            }
            
            // Obtener casas del condominio
            $casas = $this->casaModel->findCasasByCondominioId($condominioId);
            
            $reporte = [
                'condominio_id' => $condominioId,
                'fecha_reporte' => date('Y-m-d H:i:s'),
                'total_casas' => count($casas),
                'casas_detalle' => []
            ];
            
            foreach ($casas as $casa) {
                // Obtener personas de cada casa
                $personas = $this->casaModel->getPersonasByCasa($casa['id_casa']);
                
                // Limpiar datos sensibles de personas
                foreach ($personas as &$persona) {
                    unset($persona['contrasena']);
                    $persona['nombre_completo'] = $persona['nombres'] . ' ' . $persona['apellido1'] . ' ' . ($persona['apellido2'] ?? '');
                }
                
                $reporte['casas_detalle'][] = [
                    'casa' => $casa,
                    'personas' => $personas,
                    'total_habitantes' => count($personas),
                    'estado' => count($personas) > 0 ? 'ocupada' : 'vacia'
                ];
            }
            
            return $this->successResponse(
                "Reporte de condominio generado correctamente",
                ['reporte' => $reporte]
            );
            
        } catch (Exception $e) {
            $this->logError("PersonaCasaService::generarReporteCondominio - Error: " . $e->getMessage());
            return $this->errorResponse("Error interno del servidor", 500);
        }
    }
}
