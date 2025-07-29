<?php
/**
 * CondominioOwnershipMiddleware - Middleware de Propiedad de Condominio
 * Sistema Cyberhole Condominios
 * 
 * Verifica que el usuario tenga acceso únicamente a los recursos del condominio
 * al que pertenece, evitando accesos cruzados entre condominios.
 * 
 * @version 1.0.0
 * @author Sistema Cyberhole
 */

require_once __DIR__ . '/../models/BaseModel.php';

class CondominioOwnershipMiddleware {
    
    // Rutas que requieren verificación de propiedad de condominio
    private static $protectedRoutes = [
        '/api/empleados',
        '/api/tareas', 
        '/api/accesos',
        '/api/personas',
        '/api/vehiculos',
        '/api/tags',
        '/api/engomados',
        '/api/dispositivos',
        '/api/areas-comunes',
        '/api/calles',
        '/api/casas'
    ];
    
    // Administradores tienen acceso a todos los condominios
    private static $adminBypassRoles = ['ADMIN'];
    
    /**
     * Verificar propiedad de condominio para un recurso
     * 
     * @param array $user Datos del usuario autenticado
     * @param int|null $condominioId ID del condominio del recurso
     * @param string $route Ruta solicitada
     * @param array $requestData Datos de la solicitud (opcional)
     * @return array Resultado de la verificación
     */
    public static function verify(array $user, ?int $condominioId, string $route = '', array $requestData = []): array {
        try {
            // Verificar si la ruta requiere verificación
            if (!self::requiresVerification($route)) {
                return [
                    'success' => true,
                    'message' => 'Ruta no requiere verificación de condominio'
                ];
            }
            
            // Verificar datos básicos del usuario
            if (empty($user) || !isset($user['type'])) {
                return [
                    'success' => false,
                    'message' => 'Datos de usuario no válidos',
                    'error_code' => 401
                ];
            }
            
            // Los administradores tienen acceso a todos los condominios
            if (self::isAdminRole($user['type'])) {
                return [
                    'success' => true,
                    'message' => 'Acceso administrativo - Sin restricciones de condominio'
                ];
            }
            
            // Verificar que el usuario tenga condominio asignado
            $userCondominioId = $user['condominio_id'] ?? null;
            if (empty($userCondominioId)) {
                return [
                    'success' => false,
                    'message' => 'Usuario sin condominio asignado',
                    'error_code' => 403
                ];
            }
            
            // Si no se especifica condominio del recurso, intentar extraerlo
            if ($condominioId === null) {
                $condominioId = self::extractCondominioFromRequest($requestData, $route);
            }
            
            // Si aún no hay condominio del recurso, usar el del usuario
            if ($condominioId === null) {
                return [
                    'success' => true,
                    'message' => 'No se especifica condominio - Usando condominio del usuario',
                    'user_condominio' => $userCondominioId
                ];
            }
            
            // Verificar que coincidan los condominios
            if ((int)$userCondominioId !== (int)$condominioId) {
                return [
                    'success' => false,
                    'message' => 'Acceso denegado - Recurso pertenece a otro condominio',
                    'error_code' => 403,
                    'user_condominio' => $userCondominioId,
                    'resource_condominio' => $condominioId
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Acceso autorizado al condominio',
                'condominio_id' => $condominioId
            ];
            
        } catch (Exception $e) {
            error_log("CondominioOwnershipMiddleware Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno de verificación de condominio',
                'error_code' => 500
            ];
        }
    }
    
    /**
     * Verificar si la ruta requiere verificación de condominio
     * 
     * @param string $route Ruta solicitada
     * @return bool True si requiere verificación
     */
    private static function requiresVerification(string $route): bool {
        foreach (self::$protectedRoutes as $protectedRoute) {
            if (str_starts_with($route, $protectedRoute)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Verificar si el rol es de administrador
     * 
     * @param string $role Rol del usuario
     * @return bool True si es administrador
     */
    private static function isAdminRole(string $role): bool {
        return in_array(strtoupper($role), self::$adminBypassRoles);
    }
    
    /**
     * Extraer ID de condominio de los datos de la solicitud
     * 
     * @param array $requestData Datos de la solicitud
     * @param string $route Ruta solicitada
     * @return int|null ID del condominio o null si no se encuentra
     */
    private static function extractCondominioFromRequest(array $requestData, string $route): ?int {
        // Buscar en diferentes campos comunes
        $condominioFields = [
            'condominio_id',
            'id_condominio', 
            'condominioId',
            'condominio'
        ];
        
        foreach ($condominioFields as $field) {
            if (isset($requestData[$field]) && is_numeric($requestData[$field])) {
                return (int)$requestData[$field];
            }
        }
        
        // Extraer de parámetros URL si es posible
        if (preg_match('/\/condominios\/(\d+)/', $route, $matches)) {
            return (int)$matches[1];
        }
        
        // Buscar en parámetros GET
        if (isset($_GET['condominio_id']) && is_numeric($_GET['condominio_id'])) {
            return (int)$_GET['condominio_id'];
        }
        
        return null;
    }
    
    /**
     * Verificar propiedad de múltiples recursos
     * 
     * @param array $user Datos del usuario
     * @param array $condominioIds Array de IDs de condominios
     * @param string $route Ruta solicitada
     * @return array Resultado de la verificación
     */
    public static function verifyMultiple(array $user, array $condominioIds, string $route = ''): array {
        if (empty($condominioIds)) {
            return [
                'success' => true,
                'message' => 'No hay condominios que verificar'
            ];
        }
        
        // Los administradores tienen acceso a todo
        if (self::isAdminRole($user['type'] ?? '')) {
            return [
                'success' => true,
                'message' => 'Acceso administrativo completo'
            ];
        }
        
        $userCondominioId = $user['condominio_id'] ?? null;
        if (empty($userCondominioId)) {
            return [
                'success' => false,
                'message' => 'Usuario sin condominio asignado',
                'error_code' => 403
            ];
        }
        
        // Verificar que todos los condominios coincidan con el del usuario
        foreach ($condominioIds as $condominioId) {
            if ((int)$userCondominioId !== (int)$condominioId) {
                return [
                    'success' => false,
                    'message' => 'Acceso denegado - Algunos recursos pertenecen a otros condominios',
                    'error_code' => 403,
                    'user_condominio' => $userCondominioId,
                    'invalid_condominio' => $condominioId
                ];
            }
        }
        
        return [
            'success' => true,
            'message' => 'Acceso autorizado a todos los condominios solicitados'
        ];
    }
    
    /**
     * Verificar propiedad de condominio por ID de recurso
     * 
     * @param array $user Datos del usuario
     * @param int $resourceId ID del recurso
     * @param string $resourceType Tipo de recurso (empleado, tarea, etc.)
     * @return array Resultado de la verificación
     */
    public static function verifyByResourceId(array $user, int $resourceId, string $resourceType): array {
        try {
            $condominioId = self::getResourceCondominioId($resourceId, $resourceType);
            
            if ($condominioId === null) {
                return [
                    'success' => false,
                    'message' => 'No se pudo determinar el condominio del recurso',
                    'error_code' => 404
                ];
            }
            
            return self::verify($user, $condominioId);
            
        } catch (Exception $e) {
            error_log("Error verifying resource ownership: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al verificar propiedad del recurso',
                'error_code' => 500
            ];
        }
    }
    
    /**
     * Obtener ID de condominio de un recurso específico
     * 
     * @param int $resourceId ID del recurso
     * @param string $resourceType Tipo de recurso
     * @return int|null ID del condominio o null si no se encuentra
     */
    private static function getResourceCondominioId(int $resourceId, string $resourceType): ?int {
        $db = BaseModel::getConnection();
        
        $queries = [
            'empleado' => "SELECT id_condominio FROM empleados_condominio WHERE id_empleado = ?",
            'tarea' => "SELECT id_condominio FROM tareas WHERE id_tarea = ?",
            'acceso_residente' => "SELECT c.id_condominio FROM accesos_residentes ar 
                                   JOIN personas p ON ar.id_persona = p.id_persona 
                                   JOIN casas ca ON p.id_casa = ca.id_casa 
                                   JOIN condominios c ON ca.id_condominio = c.id_condominio 
                                   WHERE ar.id_acceso = ?",
            'acceso_empleado' => "SELECT e.id_condominio FROM accesos_empleados ae 
                                  JOIN empleados_condominio e ON ae.id_empleado = e.id_empleado 
                                  WHERE ae.id_acceso = ?",
            'persona' => "SELECT c.id_condominio FROM personas p 
                          JOIN casas ca ON p.id_casa = ca.id_casa 
                          JOIN condominios c ON ca.id_condominio = c.id_condominio 
                          WHERE p.id_persona = ?",
            'vehiculo' => "SELECT c.id_condominio FROM engomados e 
                           JOIN personas p ON e.id_persona = p.id_persona 
                           JOIN casas ca ON p.id_casa = ca.id_casa 
                           JOIN condominios c ON ca.id_condominio = c.id_condominio 
                           WHERE e.id_engomado = ?",
            'casa' => "SELECT id_condominio FROM casas WHERE id_casa = ?",
            'calle' => "SELECT id_condominio FROM calles WHERE id_calle = ?"
        ];
        
        $query = $queries[$resourceType] ?? null;
        if (!$query) {
            return null;
        }
        
        try {
            $stmt = $db->prepare($query);
            $stmt->execute([$resourceId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? (int)$result['id_condominio'] : null;
            
        } catch (PDOException $e) {
            error_log("Database error in getResourceCondominioId: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Ejecutar middleware y detener ejecución si no tiene acceso
     * 
     * @param array $user Datos del usuario
     * @param int|null $condominioId ID del condominio
     * @param string $route Ruta solicitada
     * @param array $requestData Datos de la solicitud
     * @return void
     */
    public static function execute(array $user, ?int $condominioId, string $route = '', array $requestData = []): void {
        $result = self::verify($user, $condominioId, $route, $requestData);
        
        if (!$result['success']) {
            http_response_code($result['error_code'] ?? 403);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $result['message'],
                'error_code' => $result['error_code'] ?? 403
            ]);
            exit;
        }
    }
    
    /**
     * Middleware para APIs - No detiene ejecución, solo retorna resultado
     * 
     * @param array $user Datos del usuario
     * @param int|null $condominioId ID del condominio
     * @param string $route Ruta solicitada
     * @param array $requestData Datos de la solicitud
     * @return array Resultado de verificación
     */
    public static function check(array $user, ?int $condominioId, string $route = '', array $requestData = []): array {
        return self::verify($user, $condominioId, $route, $requestData);
    }
    
    /**
     * Filtrar lista de recursos por condominio del usuario
     * 
     * @param array $user Datos del usuario
     * @param array $resources Lista de recursos con campo condominio_id
     * @return array Recursos filtrados
     */
    public static function filterByOwnership(array $user, array $resources): array {
        // Los administradores ven todo
        if (self::isAdminRole($user['type'] ?? '')) {
            return $resources;
        }
        
        $userCondominioId = $user['condominio_id'] ?? null;
        if (empty($userCondominioId)) {
            return [];
        }
        
        return array_filter($resources, function($resource) use ($userCondominioId) {
            $resourceCondominioId = $resource['condominio_id'] ?? 
                                   $resource['id_condominio'] ?? 
                                   null;
            
            return $resourceCondominioId && (int)$resourceCondominioId === (int)$userCondominioId;
        });
    }
    
    /**
     * Agregar filtro de condominio a query SQL
     * 
     * @param array $user Datos del usuario
     * @param string $condominioField Nombre del campo condominio en la consulta
     * @return array [condition, params] para agregar a WHERE
     */
    public static function getCondominioFilter(array $user, string $condominioField = 'id_condominio'): array {
        // Los administradores no necesitan filtro
        if (self::isAdminRole($user['type'] ?? '')) {
            return ['', []];
        }
        
        $userCondominioId = $user['condominio_id'] ?? null;
        if (empty($userCondominioId)) {
            return ['1 = 0', []]; // Condición que siempre es falsa
        }
        
        return [
            "{$condominioField} = ?",
            [$userCondominioId]
        ];
    }
}
