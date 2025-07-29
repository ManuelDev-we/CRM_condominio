<?php
/**
 * BaseService - Clase Base para Todos los Servicios
 * Sistema Cyberhole Condominios - Arquitectura 3 Capas
 * 
 * @description Clase padre que define métodos comunes reutilizables para todos los servicios
 *              Incluye middleware embebido, respuestas estándar, validaciones y logs
 * @author Sistema Cyberhole
 * @version 1.0
 * @date 2025-07-28
 * 
 * 🔥 FUNCIONALIDADES CLAVE:
 * - Integración completa con MiddlewareManager
 * - Respuestas estándar JSON (successResponse/errorResponse)
 * - Validaciones comunes (CSRF, campos requeridos, formatos)
 * - Sistema de logs y auditoría centralizado
 * - Rate limiting embebido
 * - Manejo de excepciones estandarizado
 */

require_once __DIR__ . '/../middlewares/MiddlewareManager.php';
require_once __DIR__ . '/../middlewares/CsrfMiddleware.php';
require_once __DIR__ . '/../middlewares/RateLimitMiddleware.php';

abstract class BaseService
{
    /**
     * @var array $user Datos del usuario autenticado
     */
    protected array $user = [];
    
    /**
     * @var string $serviceName Nombre del servicio para logging
     */
    protected string $serviceName;
    
    /**
     * Constructor base
     */
    public function __construct()
    {
        $this->serviceName = get_class($this);
    }
    
    // ==========================================
    // MÉTODOS DE AUTENTICACIÓN Y AUTORIZACIÓN
    // ==========================================
    
    /**
     * Validar autenticación del usuario
     * 
     * @param string $route Ruta actual (opcional)
     * @return array Datos del usuario autenticado
     * @throws UnauthorizedException Si no está autenticado
     */
    protected function checkAuth(string $route = ''): array
    {
        try {
            $result = MiddlewareManager::getCurrentUser();
            
            if (!$result) {
                throw new UnauthorizedException('Usuario no autenticado');
            }
            
            $this->user = $result;
            return $result;
            
        } catch (Exception $e) {
            throw new UnauthorizedException('Error de autenticación: ' . $e->getMessage());
        }
    }
    
    /**
     * Validar que el usuario actual es administrador
     * 
     * @return array Datos del administrador autenticado
     * @throws UnauthorizedException Si no es admin
     */
    protected function authAdmin(): array
    {
        try {
            $user = $this->checkAuth();
            
            if (!isset($user['role']) || $user['role'] !== 'ADMIN') {
                throw new UnauthorizedException('Acceso restringido a administradores');
            }
            
            return $user;
            
        } catch (Exception $e) {
            throw new UnauthorizedException('Error de autorización admin: ' . $e->getMessage());
        }
    }
    
    // ==========================================
    // MÉTODOS DE VALIDACIÓN
    // ==========================================
    
    /**
     * Validar token CSRF para operaciones de modificación
     * 
     * @param string $method Método HTTP
     * @param string $route Ruta actual (opcional)
     * @return bool True si es válido
     * @throws SecurityException Si el token es inválido
     */
    protected function checkCSRF(string $method = 'POST', string $route = ''): bool
    {
        try {
            $result = CsrfMiddleware::check($method, $route);
            
            if (!$result['success']) {
                throw new SecurityException($result['message']);
            }
            
            return true;
            
        } catch (Exception $e) {
            throw new SecurityException('Error de validación CSRF: ' . $e->getMessage());
        }
    }
    
    /**
     * Validar campos requeridos en un array de datos
     * 
     * @param array $data Datos a validar
     * @param array $requiredFields Campos requeridos
     * @return bool True si todos los campos están presentes
     * @throws InvalidArgumentException Si faltan campos
     */
    protected function validateRequiredFields(array $data, array $requiredFields): bool
    {
        $missingFields = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missingFields[] = $field;
            }
        }
        
        if (!empty($missingFields)) {
            throw new InvalidArgumentException(
                'Campos requeridos faltantes: ' . implode(', ', $missingFields)
            );
        }
        
        return true;
    }
    
    /**
     * Validar formato de email
     * 
     * @param string $email Email a validar
     * @return bool True si es válido
     * @throws InvalidArgumentException Si el formato es inválido
     */
    protected function validateEmailFormat(string $email): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Formato de email inválido: ' . $email);
        }
        
        return true;
    }
    
    /**
     * Validar formato de placa vehicular (México)
     * 
     * @param string $placa Placa a validar
     * @return bool True si es válida
     * @throws InvalidArgumentException Si el formato es inválido
     */
    protected function validatePlacaFormat(string $placa): bool
    {
        // Formato mexicano: ABC-123 o ABC-1234
        $pattern = '/^[A-Z]{3}-[0-9]{3,4}$/';
        
        if (!preg_match($pattern, strtoupper($placa))) {
            throw new InvalidArgumentException('Formato de placa inválido: ' . $placa);
        }
        
        return true;
    }
    
    // ==========================================
    // MÉTODOS DE RESPUESTA ESTÁNDAR
    // ==========================================
    
    /**
     * Respuesta exitosa estándar
     * 
     * @param mixed $data Datos de respuesta
     * @param string $message Mensaje opcional
     * @param int $code Código HTTP (por defecto 200)
     * @return array Respuesta estructurada
     */
    protected function successResponse($data = null, string $message = 'Operación exitosa', int $code = 200): array
    {
        http_response_code($code);
        
        $response = [
            'success' => true,
            'message' => $message,
            'code' => $code,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        return $response;
    }
    
    /**
     * Respuesta de error estándar
     * 
     * @param string $message Mensaje de error
     * @param int $code Código HTTP (por defecto 400)
     * @param mixed $details Detalles adicionales del error
     * @return array Respuesta estructurada
     */
    protected function errorResponse(string $message = 'Error en la operación', int $code = 400, $details = null): array
    {
        http_response_code($code);
        
        $response = [
            'success' => false,
            'message' => $message,
            'code' => $code,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        if ($details !== null) {
            $response['details'] = $details;
        }
        
        // Log del error para auditoría
        $this->logError($message, $details);
        
        return $response;
    }
    
    // ==========================================
    // MÉTODOS DE RATE LIMITING
    // ==========================================
    
    /**
     * Aplicar rate limiting a una operación
     * 
     * @param string $identifier Identificador único para el límite
     * @param int $maxRequests Máximo número de requests (por defecto 60)
     * @param int $windowMinutes Ventana de tiempo en minutos (por defecto 1)
     * @return bool True si está dentro del límite
     * @throws TooManyRequestsException Si excede el límite
     */
    protected function enforceRateLimit(string $identifier, int $maxRequests = 60, int $windowMinutes = 1): bool
    {
        try {
            $result = RateLimitMiddleware::check($identifier, $maxRequests, $windowMinutes);
            
            if (!$result['success']) {
                throw new TooManyRequestsException($result['message']);
            }
            
            return true;
            
        } catch (Exception $e) {
            throw new TooManyRequestsException('Rate limit excedido: ' . $e->getMessage());
        }
    }
    
    // ==========================================
    // MÉTODOS DE LOGGING Y AUDITORÍA
    // ==========================================
    
    /**
     * Registrar actividad para auditoría
     * 
     * @param string $action Acción realizada
     * @param array $details Detalles de la acción
     * @param string $level Nivel de log (info, warning, error)
     * @return void
     */
    protected function logAdminActivity(string $action, array $details = [], string $level = 'info'): void
    {
        $logData = [
            'service' => $this->serviceName,
            'action' => $action,
            'user_id' => $this->user['id'] ?? null,
            'user_role' => $this->user['role'] ?? null,
            'details' => $details,
            'timestamp' => date('Y-m-d H:i:s'),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
        
        $logMessage = json_encode($logData);
        
        // Escribir al log del sistema
        error_log("[$level] " . $logMessage, 3, __DIR__ . '/../../logs/app.log');
    }
    
    /**
     * Registrar error para debugging
     * 
     * @param string $message Mensaje de error
     * @param mixed $context Contexto adicional
     * @return void
     */
    protected function logError(string $message, $context = null): void
    {
        $errorData = [
            'service' => $this->serviceName,
            'message' => $message,
            'context' => $context,
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $this->user['id'] ?? null
        ];
        
        $logMessage = json_encode($errorData);
        error_log("[ERROR] " . $logMessage, 3, __DIR__ . '/../../logs/app.log');
    }
    
    // ==========================================
    // MÉTODOS DE UTILIDAD
    // ==========================================
    
    /**
     * Sanear texto para prevenir XSS
     * 
     * @param string $text Texto a sanear
     * @return string Texto saneado
     */
    protected function sanitizeText(string $text): string
    {
        return htmlspecialchars(strip_tags(trim($text)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Sanear HTML manteniendo tags seguros
     * 
     * @param string $html HTML a sanear
     * @return string HTML saneado
     */
    protected function sanitizeHtml(string $html): string
    {
        $allowedTags = '<p><br><strong><em><u><ul><ol><li><a><img><h1><h2><h3><h4><h5><h6>';
        return strip_tags($html, $allowedTags);
    }
    
    /**
     * Calcular tiempo transcurrido desde una fecha
     * 
     * @param string $datetime Fecha en formato Y-m-d H:i:s
     * @return string Tiempo transcurrido en formato legible
     */
    protected function calculateTimeAgo(string $datetime): string
    {
        $time = time() - strtotime($datetime);
        
        if ($time < 60) return 'hace unos segundos';
        if ($time < 3600) return 'hace ' . floor($time/60) . ' minutos';
        if ($time < 86400) return 'hace ' . floor($time/3600) . ' horas';
        if ($time < 2592000) return 'hace ' . floor($time/86400) . ' días';
        if ($time < 31536000) return 'hace ' . floor($time/2592000) . ' meses';
        
        return 'hace ' . floor($time/31536000) . ' años';
    }
}

/**
 * Excepciones personalizadas para el sistema
 */
class UnauthorizedException extends Exception
{
    public function __construct($message = "Acceso no autorizado", $code = 401, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

class SecurityException extends Exception
{
    public function __construct($message = "Violación de seguridad", $code = 403, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

class TooManyRequestsException extends Exception
{
    public function __construct($message = "Demasiadas solicitudes", $code = 429, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
