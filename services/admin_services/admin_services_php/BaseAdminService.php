<?php
/**
 * BaseAdminService - Clase Base para Servicios Administrativos
 * Sistema Cyberhole Condominios - Arquitectura 3 Capas
 * 
 * @description Clase madre administrativa que hereda de BaseService
 *              Añade funcionalidad específica para administradores como validaciones 
 *              de condominio ownership y métodos de utilidad administrativos
 * @author Sistema Cyberhole
 * @version 1.0
 * @date 2025-07-28
 * 
 * 🔥 FUNCIONALIDADES ADMINISTRATIVAS:
 * - Validación automática de rol ADMIN
 * - Verificación de ownership de condominios
 * - Métodos de utilidad para administradores
 * - Integración con modelos de condominio
 * - Control de acceso granular por condominio
 */

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../../../models/Condominio.php';

abstract class BaseAdminService extends BaseService
{
    /**
     * @var array $adminData Datos específicos del administrador
     */
    protected array $adminData = [];
    
    /**
     * @var array $condominiosAsignados Condominios del administrador
     */
    protected array $condominiosAsignados = [];
    
    /**
     * @var Condominio $condominioModel Instancia del modelo Condominio
     */
    protected Condominio $condominioModel;
    
    /**
     * Constructor - Validación automática de administrador
     */
    public function __construct()
    {
        parent::__construct();
        
        // Inicializar modelo de condominio
        $this->condominioModel = new Condominio();
        
        // Validar que el usuario sea administrador
        $this->adminData = $this->authAdmin();
        
        // Cargar condominios asignados al administrador
        $this->loadCondominiosAsignados();
    }
    
    // ==========================================
    // MÉTODOS DE VALIDACIÓN ADMINISTRATIVA
    // ==========================================
    
    /**
     * Verificar que el administrador tiene ownership sobre un condominio
     * 
     * @param int $condominioId ID del condominio
     * @param int|null $adminId ID del admin (opcional, usa el actual si no se proporciona)
     * @return bool True si tiene ownership
     * @throws UnauthorizedException Si no tiene permisos
     */
    protected function checkOwnershipCondominio(int $condominioId, ?int $adminId = null): bool
    {
        $adminId = $adminId ?? $this->adminData['id'];
        
        try {
            // Verificar si el condominio existe
            $condominio = $this->condominioModel->findCondominioById($condominioId);
            if (!$condominio) {
                throw new UnauthorizedException('Condominio no encontrado');
            }
            
            // Verificar ownership usando el modelo
            $hasOwnership = $this->condominioModel->validateAdminOwnership($adminId, $condominioId);
            
            if (!$hasOwnership) {
                throw new UnauthorizedException(
                    'No tienes permisos para gestionar el condominio: ' . $condominio['nombre']
                );
            }
            
            return true;
            
        } catch (Exception $e) {
            $this->logError('Error verificando ownership de condominio', [
                'admin_id' => $adminId,
                'condominio_id' => $condominioId,
                'error' => $e->getMessage()
            ]);
            
            throw new UnauthorizedException('Error de permisos: ' . $e->getMessage());
        }
    }
    
    /**
     * Validar que el administrador tiene acceso a múltiples condominios
     * 
     * @param array $condominioIds Array de IDs de condominios
     * @return bool True si tiene acceso a todos
     * @throws UnauthorizedException Si no tiene permisos sobre alguno
     */
    protected function checkMultipleCondominioOwnership(array $condominioIds): bool
    {
        foreach ($condominioIds as $condominioId) {
            $this->checkOwnershipCondominio($condominioId);
        }
        
        return true;
    }
    
    /**
     * Obtener condominios del administrador actual
     * 
     * @param bool $activeOnly Solo condominios activos
     * @return array Lista de condominios
     */
    protected function getCondominiosAsignados(bool $activeOnly = true): array
    {
        if (empty($this->condominiosAsignados) || !$activeOnly) {
            $this->loadCondominiosAsignados($activeOnly);
        }
        
        return $this->condominiosAsignados;
    }
    
    /**
     * Verificar si un condominio pertenece al administrador actual
     * 
     * @param int $condominioId ID del condominio
     * @return bool True si pertenece al admin
     */
    protected function adminOwnsCondominio(int $condominioId): bool
    {
        try {
            return $this->condominioModel->validateAdminOwnership($this->adminData['id'], $condominioId);
        } catch (Exception $e) {
            $this->logError('Error verificando ownership simple', [
                'admin_id' => $this->adminData['id'],
                'condominio_id' => $condominioId,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    // ==========================================
    // MÉTODOS DE UTILIDAD ADMINISTRATIVA
    // ==========================================
    
    /**
     * Obtener datos básicos de un condominio si el admin tiene acceso
     * 
     * @param int $condominioId ID del condominio
     * @return array|null Datos del condominio o null si no tiene acceso
     */
    protected function getCondominioIfOwned(int $condominioId): ?array
    {
        try {
            if (!$this->adminOwnsCondominio($condominioId)) {
                return null;
            }
            
            return $this->condominioModel->findCondominioById($condominioId);
            
        } catch (Exception $e) {
            $this->logError('Error obteniendo condominio', [
                'condominio_id' => $condominioId,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }
    
    /**
     * Filtrar array de condominios por ownership del admin
     * 
     * @param array $condominios Lista de condominios
     * @return array Condominios filtrados por ownership
     */
    protected function filterCondominiosByOwnership(array $condominios): array
    {
        $filtered = [];
        
        foreach ($condominios as $condominio) {
            $condominioId = $condominio['id_condominio'] ?? $condominio['id'] ?? null;
            
            if ($condominioId && $this->adminOwnsCondominio($condominioId)) {
                $filtered[] = $condominio;
            }
        }
        
        return $filtered;
    }
    
    /**
     * Validar que un recurso pertenece a un condominio del administrador
     * 
     * @param int $condominioId ID del condominio del recurso
     * @param string $resourceType Tipo de recurso (para logging)
     * @param int|null $resourceId ID del recurso (para logging)
     * @return bool True si es válido
     * @throws UnauthorizedException Si no es válido
     */
    protected function validateResourceBelongsToAdminCondominio(
        int $condominioId, 
        string $resourceType = 'recurso', 
        ?int $resourceId = null
    ): bool {
        $this->checkOwnershipCondominio($condominioId);
        
        $this->logAdminActivity('recurso_validado', [
            'condominio_id' => $condominioId,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId
        ]);
        
        return true;
    }
    
    // ==========================================
    // MÉTODOS DE CONFIGURACIÓN Y PREFERENCIAS
    // ==========================================
    
    /**
     * Obtener configuración específica de administrador
     * 
     * @param string $key Clave de configuración
     * @param mixed $default Valor por defecto
     * @return mixed Valor de configuración
     */
    protected function getAdminSetting(string $key, $default = null)
    {
        $settings = $this->adminData['configuracion'] ?? [];
        
        if (is_string($settings)) {
            $settings = json_decode($settings, true) ?? [];
        }
        
        return $settings[$key] ?? $default;
    }
    
    /**
     * Establecer configuración específica de administrador
     * 
     * @param string $key Clave de configuración
     * @param mixed $value Valor a establecer
     * @return void
     */
    protected function setAdminSetting(string $key, $value): void
    {
        $settings = $this->adminData['configuracion'] ?? [];
        
        if (is_string($settings)) {
            $settings = json_decode($settings, true) ?? [];
        }
        
        $settings[$key] = $value;
        $this->adminData['configuracion'] = $settings;
    }
    
    // ==========================================
    // MÉTODOS INTERNOS
    // ==========================================
    
    /**
     * Cargar condominios asignados al administrador
     * 
     * @param bool $activeOnly Solo condominios activos
     * @return void
     */
    private function loadCondominiosAsignados(bool $activeOnly = true): void
    {
        try {
            $this->condominiosAsignados = $this->condominioModel->getCondominiosByAdmin(
                $this->adminData['id'], 
                $activeOnly
            );
            
            $this->logAdminActivity('condominios_cargados', [
                'total_condominios' => count($this->condominiosAsignados),
                'active_only' => $activeOnly
            ]);
            
        } catch (Exception $e) {
            $this->logError('Error cargando condominios asignados', [
                'admin_id' => $this->adminData['id'],
                'error' => $e->getMessage()
            ]);
            
            $this->condominiosAsignados = [];
        }
    }
    
    // ==========================================
    // MÉTODOS DE VALIDACIÓN ESPECÍFICA
    // ==========================================
    
    /**
     * Validar datos de dirección
     * 
     * @param array $direccion Datos de dirección
     * @return bool True si es válida
     * @throws InvalidArgumentException Si la dirección es inválida
     */
    protected function validateDireccion(array $direccion): bool
    {
        $required = ['calle', 'numero', 'colonia', 'ciudad', 'estado', 'codigo_postal'];
        
        $this->validateRequiredFields($direccion, $required);
        
        // Validar código postal mexicano (5 dígitos)
        if (!preg_match('/^\d{5}$/', $direccion['codigo_postal'])) {
            throw new InvalidArgumentException('Código postal debe tener 5 dígitos');
        }
        
        return true;
    }
    
    /**
     * Validar número telefónico mexicano
     * 
     * @param string $telefono Número telefónico
     * @return bool True si es válido
     * @throws InvalidArgumentException Si el teléfono es inválido
     */
    protected function validateTelefonoMexicano(string $telefono): bool
    {
        // Limpiar número (solo dígitos)
        $clean = preg_replace('/\D/', '', $telefono);
        
        // Validar formato mexicano (10 dígitos)
        if (!preg_match('/^\d{10}$/', $clean)) {
            throw new InvalidArgumentException('Teléfono debe tener 10 dígitos');
        }
        
        return true;
    }
    
    /**
     * Validar rango de fechas
     * 
     * @param string $fechaInicio Fecha de inicio
     * @param string $fechaFin Fecha de fin
     * @return bool True si el rango es válido
     * @throws InvalidArgumentException Si el rango es inválido
     */
    protected function validateDateRange(string $fechaInicio, string $fechaFin): bool
    {
        $inicio = strtotime($fechaInicio);
        $fin = strtotime($fechaFin);
        
        if ($inicio === false || $fin === false) {
            throw new InvalidArgumentException('Formato de fecha inválido');
        }
        
        if ($inicio >= $fin) {
            throw new InvalidArgumentException('La fecha de inicio debe ser anterior a la fecha de fin');
        }
        
        return true;
    }
    
    // ==========================================
    // MÉTODOS DE RESPUESTA ESPECÍFICA
    // ==========================================
    
    /**
     * Respuesta de éxito con datos de administrador
     * 
     * @param mixed $data Datos de respuesta
     * @param string $message Mensaje
     * @return array Respuesta con contexto administrativo
     */
    protected function adminSuccessResponse($data = null, string $message = 'Operación exitosa'): array
    {
        $response = $this->successResponse($data, $message);
        
        // Agregar contexto administrativo
        $response['admin_context'] = [
            'admin_id' => $this->adminData['id'],
            'condominios_count' => count($this->condominiosAsignados),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        return $response;
    }
    
    /**
     * Respuesta de error con contexto administrativo
     * 
     * @param string $message Mensaje de error
     * @param int $code Código HTTP
     * @param mixed $details Detalles adicionales
     * @return array Respuesta con contexto administrativo
     */
    protected function adminErrorResponse(string $message = 'Error administrativo', int $code = 400, $details = null): array
    {
        $response = $this->errorResponse($message, $code, $details);
        
        // Agregar contexto administrativo para debugging
        $response['admin_context'] = [
            'admin_id' => $this->adminData['id'],
            'service' => $this->serviceName,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        return $response;
    }
}
