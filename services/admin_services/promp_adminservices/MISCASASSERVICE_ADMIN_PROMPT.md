# MISCASASSERVICE_ADMIN_PROMPT.md
## Prompt Especializado para MisCasasService.php

### 🎯 PROPÓSITO DEL SERVICIO
Administrar las propiedades/casas específicas del admin logueado dentro de sus condominios. Este servicio permite al admin gestionar únicamente las casas de los condominios que posee o administra, con funcionalidades específicas de propietario.

---

## 🏗️ ARQUITECTURA Y HERENCIA

### Clase Base
```php
class MisCasasService extends BaseAdminService
```

### Dependencias
- **Hereda de:** `BaseAdminService.php`
- **Modelos principales:** `Casa.php`, `Persona.php`, `Condominio.php`
- **Posición en cascada:** Nivel 8 (Propiedades Personales)
- **Servicios relacionados:** CasaService, PersonaCasaService, CalleService
- **Requiere validaciones de:** CondominioService

---

## 📚 MÉTODOS DEL MODELO DISPONIBLES

### Métodos de Gestión de Casas del Admin
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `findCasasByAdminCondominio()` | int $adminId | array | Buscar casas por admin propietario |
| `findCasasByCondominioId()` | int $condominioId | array | Buscar casas por condominio |
| `findCasaById()` | int $id | array | Buscar casa por ID |
| `updateCasa()` | int $id, array $data | bool | Actualizar casa |
| `createCasa()` | array $data | int | Crear casa |
| `deleteCasa()` | int $id | bool | Eliminar casa |
| `activateCasa()` | int $id | bool | Activar casa |
| `deactivateCasa()` | int $id | bool | Desactivar casa |

### Métodos de Gestión de Residentes
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `getPersonasByCasa()` | int $casaId | array | Obtener personas por casa |
| `assignPersonaToCasa()` | int $personaId, int $casaId | bool | Asignar persona a casa |
| `removePersonaFromCasa()` | int $personaId, int $casaId | bool | Remover persona de casa |
| `updatePersonaCasaRelation()` | int $personaId, int $casaId, array $data | bool | Actualizar relación persona-casa |
| `setTipoRelacion()` | int $personaId, int $casaId, string $tipo | bool | Establecer tipo de relación |

### Métodos de Configuración de Casa
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `setCasaPrivacySettings()` | int $casaId, array $settings | bool | Configurar privacidad de casa |
| `setCasaNotifications()` | int $casaId, array $notifications | bool | Configurar notificaciones |
| `setCasaAccessRules()` | int $casaId, array $rules | bool | Configurar reglas de acceso |
| `updateCasaDescription()` | int $casaId, string $description | bool | Actualizar descripción |
| `setCasaImages()` | int $casaId, array $images | bool | Establecer imágenes de casa |

### Métodos de Historial y Actividad
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `getCasaActivityHistory()` | int $casaId, array $periodo | array | Obtener historial de actividad |
| `getCasaAccessHistory()` | int $casaId, array $periodo | array | Obtener historial de accesos |
| `getCasaMaintenanceHistory()` | int $casaId | array | Obtener historial de mantenimiento |
| `getCasaVisitorsHistory()` | int $casaId, array $periodo | array | Obtener historial de visitantes |

### Métodos de Validación
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `validateCasaOwnership()` | int $casaId, int $adminId | bool | Valida ownership de casa |
| `validateCondominioExists()` | int $condominioId | bool | Valida existencia de condominio |
| `validateCasaExists()` | int $casaId | bool | Valida existencia de casa |
| `validatePersonaExists()` | int $personaId | bool | Valida existencia de persona |
| `validateCalleExists()` | int $calleId | bool | Valida existencia de calle |

### Métodos de Reportes del Propietario
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `getMisCasasResumen()` | int $adminId | array | Resumen de todas las casas del admin |
| `getCasaFinancialReport()` | int $casaId, array $periodo | array | Reporte financiero de casa |
| `getCasaOccupancyReport()` | int $casaId | array | Reporte de ocupación |
| `getMisCasasEstadisticas()` | int $adminId | array | Estadísticas generales |

### Métodos de Servicios y Utilidades
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `getCasaServices()` | int $casaId | array | Obtener servicios de casa |
| `updateCasaServices()` | int $casaId, array $services | bool | Actualizar servicios |
| `getCasaInvoices()` | int $casaId, array $periodo | array | Obtener facturas de casa |
| `createCasaInvoice()` | int $casaId, array $data | int | Crear factura |

### Métodos Base Heredados
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `create()` | array $data | int | Crear registro |
| `findById()` | int $id | array | Buscar por ID |
| `update()` | int $id, array $data | bool | Actualizar registro |
| `delete()` | int $id | bool | Eliminar registro |
| `findAll()` | int $limit = 100 | array | Obtener todos los registros |

---

## 🔧 FUNCIONES DE NEGOCIO REQUERIDAS

### 1. **Obtener Mis Casas (Dashboard Principal)**
```php
public function obtenerMisCasas($adminId, $opciones = [])
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Aplicar rate limiting
    $this->enforceRateLimit('mis_casas_' . $adminId);
    
    // Obtener casas del admin
    $misCasas = $this->misCasasModel->findCasasByAdminCondominio($adminId);
    
    // Aplicar filtros si se proporcionan
    if (isset($opciones['condominio_id'])) {
        $misCasas = array_filter($misCasas, function($casa) use ($opciones) {
            return $casa['condominio_id'] == $opciones['condominio_id'];
        });
    }
    
    if (isset($opciones['activas_solamente']) && $opciones['activas_solamente']) {
        $misCasas = array_filter($misCasas, function($casa) {
            return $casa['activa'];
        });
    }
    
    if (isset($opciones['con_residentes']) && $opciones['con_residentes']) {
        $misCasas = array_filter($misCasas, function($casa) {
            return $casa['total_residentes'] > 0;
        });
    }
    
    // Agregar información detallada para cada casa
    foreach ($misCasas as &$casa) {
        // Información básica
        $casa['condominio_info'] = $this->condominioService->obtenerCondominioBasico($casa['condominio_id']);
        $casa['calle_info'] = $this->calleService->obtenerCalleBasica($casa['calle_id']);
        
        // Residentes actuales
        $casa['residentes'] = $this->misCasasModel->getPersonasByCasa($casa['id']);
        $casa['total_residentes_actual'] = count($casa['residentes']);
        
        // Estadísticas recientes
        $casa['actividad_reciente'] = $this->getActividadRecienteCasa($casa['id'], 7); // Últimos 7 días
        $casa['ultimo_acceso'] = $this->getUltimoAccesoCasa($casa['id']);
        
        // Estado financiero básico
        $casa['facturas_pendientes'] = $this->getFacturasPendientesCasa($casa['id']);
        $casa['proximos_pagos'] = $this->getProximosPagosCasa($casa['id']);
    }
    
    return $this->successResponse($misCasas, 'Mis casas obtenidas exitosamente');
}

public function obtenerResumenMisCasas($adminId)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Aplicar rate limiting
    $this->enforceRateLimit('resumen_casas_' . $adminId);
    
    // Obtener resumen completo
    $resumen = $this->misCasasModel->getMisCasasResumen($adminId);
    
    // Agregar estadísticas calculadas
    $resumen['estadisticas_generales'] = [
        'total_casas' => count($resumen['casas']),
        'casas_ocupadas' => count(array_filter($resumen['casas'], function($casa) {
            return $casa['total_residentes'] > 0;
        })),
        'casas_vacias' => count(array_filter($resumen['casas'], function($casa) {
            return $casa['total_residentes'] == 0;
        })),
        'total_residentes' => array_sum(array_column($resumen['casas'], 'total_residentes')),
        'condominios_gestionados' => count(array_unique(array_column($resumen['casas'], 'condominio_id')))
    ];
    
    // Estadísticas por condominio
    $resumen['por_condominio'] = $this->getEstadisticasPorCondominio($adminId);
    
    // Actividad reciente general
    $resumen['actividad_reciente'] = $this->getActividadRecienteGeneral($adminId, 30);
    
    return $this->successResponse($resumen, 'Resumen de mis casas obtenido exitosamente');
}
```

### 2. **Gestionar Casa Específica**
```php
public function gestionarCasa($adminId, $casaId, $accion, $datos = [])
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Obtener casa
    $casa = $this->misCasasModel->findCasaById($casaId);
    if (!$casa) {
        return $this->errorResponse('Casa no encontrada');
    }
    
    // Validar ownership directo
    if (!$this->misCasasModel->validateCasaOwnership($casaId, $adminId)) {
        return $this->errorResponse('No tienes permisos para gestionar esta casa');
    }
    
    $resultado = false;
    $mensaje = '';
    
    switch ($accion) {
        case 'actualizar_info':
            $this->validateRequiredFields($datos, ['numero_casa']);
            $resultado = $this->misCasasModel->updateCasa($casaId, $datos);
            $mensaje = 'Información de casa actualizada exitosamente';
            break;
            
        case 'configurar_privacidad':
            $this->validateRequiredFields($datos, ['configuracion']);
            $resultado = $this->misCasasModel->setCasaPrivacySettings($casaId, $datos['configuracion']);
            $mensaje = 'Configuración de privacidad actualizada';
            break;
            
        case 'configurar_notificaciones':
            $this->validateRequiredFields($datos, ['notificaciones']);
            $resultado = $this->misCasasModel->setCasaNotifications($casaId, $datos['notificaciones']);
            $mensaje = 'Configuración de notificaciones actualizada';
            break;
            
        case 'establecer_reglas_acceso':
            $this->validateRequiredFields($datos, ['reglas']);
            $resultado = $this->misCasasModel->setCasaAccessRules($casaId, $datos['reglas']);
            $mensaje = 'Reglas de acceso establecidas';
            break;
            
        case 'actualizar_descripcion':
            $this->validateRequiredFields($datos, ['descripcion']);
            $resultado = $this->misCasasModel->updateCasaDescription($casaId, $datos['descripcion']);
            $mensaje = 'Descripción actualizada';
            break;
            
        case 'establecer_imagenes':
            $this->validateRequiredFields($datos, ['imagenes']);
            $resultado = $this->misCasasModel->setCasaImages($casaId, $datos['imagenes']);
            $mensaje = 'Imágenes de casa actualizadas';
            break;
            
        case 'activar':
            $resultado = $this->misCasasModel->activateCasa($casaId);
            $mensaje = 'Casa activada exitosamente';
            break;
            
        case 'desactivar':
            $resultado = $this->misCasasModel->deactivateCasa($casaId);
            $mensaje = 'Casa desactivada exitosamente';
            break;
            
        default:
            return $this->errorResponse('Acción no válida');
    }
    
    // Log de actividad
    $this->logAdminActivity('casa_gestionada', [
        'admin_id' => $adminId,
        'casa_id' => $casaId,
        'accion' => $accion,
        'condominio_id' => $casa['condominio_id'],
        'datos_modificados' => array_keys($datos)
    ]);
    
    return $this->successResponse($resultado, $mensaje);
}
```

### 3. **Gestionar Residentes de Mi Casa**
```php
public function gestionarResidentesMiCasa($adminId, $casaId, $accion, $datos = [])
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Validar ownership de la casa
    if (!$this->misCasasModel->validateCasaOwnership($casaId, $adminId)) {
        return $this->errorResponse('No tienes permisos para gestionar residentes en esta casa');
    }
    
    $resultado = false;
    $mensaje = '';
    
    switch ($accion) {
        case 'asignar_residente':
            $this->validateRequiredFields($datos, ['persona_id']);
            
            // Validar que la persona existe
            if (!$this->misCasasModel->validatePersonaExists($datos['persona_id'])) {
                return $this->errorResponse('Persona no encontrada');
            }
            
            // Verificar que no esté ya asignada
            $residentes = $this->misCasasModel->getPersonasByCasa($casaId);
            foreach ($residentes as $residente) {
                if ($residente['id'] == $datos['persona_id']) {
                    return $this->errorResponse('La persona ya está asignada a esta casa');
                }
            }
            
            $tipoRelacion = $datos['tipo_relacion'] ?? 'residente';
            $resultado = $this->misCasasModel->assignPersonaToCasa($datos['persona_id'], $casaId);
            
            if ($resultado && $tipoRelacion) {
                $this->misCasasModel->setTipoRelacion($datos['persona_id'], $casaId, $tipoRelacion);
            }
            
            $mensaje = 'Residente asignado exitosamente';
            break;
            
        case 'remover_residente':
            $this->validateRequiredFields($datos, ['persona_id']);
            
            $resultado = $this->misCasasModel->removePersonaFromCasa($datos['persona_id'], $casaId);
            $mensaje = 'Residente removido exitosamente';
            break;
            
        case 'actualizar_relacion':
            $this->validateRequiredFields($datos, ['persona_id', 'tipo_relacion']);
            
            $resultado = $this->misCasasModel->setTipoRelacion($datos['persona_id'], $casaId, $datos['tipo_relacion']);
            $mensaje = 'Tipo de relación actualizado';
            break;
            
        case 'obtener_residentes':
            $residentes = $this->misCasasModel->getPersonasByCasa($casaId);
            
            // Agregar información adicional de cada residente
            foreach ($residentes as &$residente) {
                $residente['tipo_relacion'] = $this->getTipoRelacionPersonaCasa($residente['id'], $casaId);
                $residente['fecha_asignacion'] = $this->getFechaAsignacionPersonaCasa($residente['id'], $casaId);
                $residente['accesos_recientes'] = $this->getAccesosRecientesPersona($residente['id'], 30);
            }
            
            return $this->successResponse($residentes, 'Residentes obtenidos exitosamente');
            
        default:
            return $this->errorResponse('Acción no válida para gestión de residentes');
    }
    
    // Log de actividad
    $this->logAdminActivity('residentes_gestionados', [
        'admin_id' => $adminId,
        'casa_id' => $casaId,
        'accion' => $accion,
        'persona_id' => $datos['persona_id'] ?? null,
        'tipo_relacion' => $datos['tipo_relacion'] ?? null
    ]);
    
    return $this->successResponse($resultado, $mensaje);
}
```

### 4. **Obtener Reportes de Mi Casa**
```php
public function obtenerReportesCasa($adminId, $casaId, $tipoReporte, $opciones = [])
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar ownership de la casa
    if (!$this->misCasasModel->validateCasaOwnership($casaId, $adminId)) {
        return $this->errorResponse('No tienes permisos para ver reportes de esta casa');
    }
    
    // Aplicar rate limiting
    $this->enforceRateLimit('reportes_casa_' . $adminId);
    
    $reporte = [];
    $mensaje = '';
    
    switch ($tipoReporte) {
        case 'actividad':
            $periodo = $opciones['periodo'] ?? ['desde' => date('Y-m-d', strtotime('-30 days')), 'hasta' => date('Y-m-d')];
            $reporte = $this->misCasasModel->getCasaActivityHistory($casaId, $periodo);
            $mensaje = 'Reporte de actividad obtenido';
            break;
            
        case 'accesos':
            $periodo = $opciones['periodo'] ?? ['desde' => date('Y-m-d', strtotime('-7 days')), 'hasta' => date('Y-m-d')];
            $reporte = $this->misCasasModel->getCasaAccessHistory($casaId, $periodo);
            $mensaje = 'Reporte de accesos obtenido';
            break;
            
        case 'mantenimiento':
            $reporte = $this->misCasasModel->getCasaMaintenanceHistory($casaId);
            $mensaje = 'Historial de mantenimiento obtenido';
            break;
            
        case 'visitantes':
            $periodo = $opciones['periodo'] ?? ['desde' => date('Y-m-d', strtotime('-30 days')), 'hasta' => date('Y-m-d')];
            $reporte = $this->misCasasModel->getCasaVisitorsHistory($casaId, $periodo);
            $mensaje = 'Historial de visitantes obtenido';
            break;
            
        case 'financiero':
            $periodo = $opciones['periodo'] ?? ['desde' => date('Y-m-01'), 'hasta' => date('Y-m-d')];
            $reporte = $this->misCasasModel->getCasaFinancialReport($casaId, $periodo);
            $reporte['facturas'] = $this->misCasasModel->getCasaInvoices($casaId, $periodo);
            $mensaje = 'Reporte financiero obtenido';
            break;
            
        case 'ocupacion':
            $reporte = $this->misCasasModel->getCasaOccupancyReport($casaId);
            $mensaje = 'Reporte de ocupación obtenido';
            break;
            
        default:
            return $this->errorResponse('Tipo de reporte no válido');
    }
    
    // Agregar información contextual de la casa
    $reporte['casa_info'] = $this->misCasasModel->findCasaById($casaId);
    $reporte['fecha_generacion'] = date('Y-m-d H:i:s');
    $reporte['tipo_reporte'] = $tipoReporte;
    
    return $this->successResponse($reporte, $mensaje);
}
```

### 5. **Gestionar Servicios de Casa**
```php
public function gestionarServiciosCasa($adminId, $casaId, $accion, $datos = [])
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Validar ownership de la casa
    if (!$this->misCasasModel->validateCasaOwnership($casaId, $adminId)) {
        return $this->errorResponse('No tienes permisos para gestionar servicios en esta casa');
    }
    
    $resultado = false;
    $mensaje = '';
    
    switch ($accion) {
        case 'obtener_servicios':
            $servicios = $this->misCasasModel->getCasaServices($casaId);
            return $this->successResponse($servicios, 'Servicios de casa obtenidos');
            
        case 'actualizar_servicios':
            $this->validateRequiredFields($datos, ['servicios']);
            $resultado = $this->misCasasModel->updateCasaServices($casaId, $datos['servicios']);
            $mensaje = 'Servicios actualizados exitosamente';
            break;
            
        case 'crear_factura':
            $this->validateRequiredFields($datos, ['concepto', 'monto', 'fecha_vencimiento']);
            
            $datosFactura = [
                'casa_id' => $casaId,
                'concepto' => $datos['concepto'],
                'monto' => $datos['monto'],
                'fecha_vencimiento' => $datos['fecha_vencimiento'],
                'fecha_creacion' => date('Y-m-d H:i:s'),
                'estado' => 'pendiente',
                'creado_por_admin' => $adminId
            ];
            
            $facturaId = $this->misCasasModel->createCasaInvoice($casaId, $datosFactura);
            $resultado = ['factura_id' => $facturaId];
            $mensaje = 'Factura creada exitosamente';
            break;
            
        case 'obtener_facturas':
            $periodo = $datos['periodo'] ?? ['desde' => date('Y-m-01'), 'hasta' => date('Y-m-d')];
            $facturas = $this->misCasasModel->getCasaInvoices($casaId, $periodo);
            return $this->successResponse($facturas, 'Facturas obtenidas');
            
        default:
            return $this->errorResponse('Acción no válida para servicios');
    }
    
    // Log de actividad
    $this->logAdminActivity('servicios_casa_gestionados', [
        'admin_id' => $adminId,
        'casa_id' => $casaId,
        'accion' => $accion,
        'datos_modificados' => array_keys($datos)
    ]);
    
    return $this->successResponse($resultado, $mensaje);
}
```

### 6. **Estadísticas Personalizadas del Propietario**
```php
public function obtenerEstadisticasPersonalizadas($adminId, $opciones = [])
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Aplicar rate limiting
    $this->enforceRateLimit('estadisticas_personalizadas_' . $adminId);
    
    // Obtener estadísticas generales
    $estadisticas = $this->misCasasModel->getMisCasasEstadisticas($adminId);
    
    // Período para análisis
    $periodo = $opciones['periodo'] ?? ['desde' => date('Y-m-01'), 'hasta' => date('Y-m-d')];
    
    // Estadísticas por condominio
    $estadisticas['por_condominio'] = $this->getEstadisticasPorCondominio($adminId);
    
    // Actividad reciente
    $estadisticas['actividad_reciente'] = [
        'total_accesos' => $this->getTotalAccesosRecientes($adminId, 7),
        'nuevos_residentes' => $this->getNuevosResidentes($adminId, 30),
        'facturas_generadas' => $this->getFacturasGeneradasRecientes($adminId, $periodo),
        'mantenimientos_solicitados' => $this->getMantenimientosRecientes($adminId, 30)
    ];
    
    // Métricas financieras
    $estadisticas['metricas_financieras'] = [
        'ingresos_periodo' => $this->getIngresosPeriodo($adminId, $periodo),
        'gastos_periodo' => $this->getGastosPeriodo($adminId, $periodo),
        'facturas_pendientes' => $this->getFacturasPendientesTotal($adminId),
        'promedio_ocupacion' => $this->getPromedioOcupacion($adminId)
    ];
    
    // Alertas y notificaciones
    $estadisticas['alertas'] = [
        'casas_con_problemas' => $this->getCasasConProblemas($adminId),
        'facturas_vencidas' => $this->getFacturasVencidas($adminId),
        'mantenimientos_pendientes' => $this->getMantenimientosPendientes($adminId),
        'accesos_irregulares' => $this->getAccesosIrregulares($adminId, 7)
    ];
    
    return $this->successResponse($estadisticas, 'Estadísticas personalizadas obtenidas');
}
```

---

## 🔒 VALIDACIONES DE SEGURIDAD REQUERIDAS

### Middleware Embebido
```php
private function checkAdminAuth()
{
    if (!$this->authAdmin()) {
        throw new UnauthorizedException('Acceso no autorizado');
    }
}
```

### Validaciones Específicas de Ownership
```php
private function validateCasaOwnershipStrict($casaId, $adminId)
{
    // Verificar que el admin es propietario del condominio donde está la casa
    $casa = $this->misCasasModel->findCasaById($casaId);
    if (!$casa) {
        return false;
    }
    
    return $this->condominioService->validarOwnership($casa['condominio_id'], $adminId);
}

private function getTipoRelacionPersonaCasa($personaId, $casaId)
{
    return $this->misCasasModel->getTipoRelacion($personaId, $casaId);
}

private function getFechaAsignacionPersonaCasa($personaId, $casaId)
{
    return $this->misCasasModel->getFechaAsignacion($personaId, $casaId);
}

private function getActividadRecienteCasa($casaId, $dias)
{
    $periodo = [
        'desde' => date('Y-m-d', strtotime("-$dias days")),
        'hasta' => date('Y-m-d')
    ];
    
    return $this->misCasasModel->getCasaActivityHistory($casaId, $periodo);
}
```

---

## 🔄 INTEGRACIÓN CON OTROS SERVICIOS

### Debe usar servicios en cascada:
```php
// Validar ownership del condominio
if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
    return $this->errorResponse("No tienes permisos");
}

// Obtener información de calles
$calleInfo = $this->calleService->obtenerCalleBasica($calleId);

// Usar CasaService para operaciones complejas
$casaData = $this->casaService->obtenerCasaCompleta($casaId);
```

### Proporciona para otros servicios:
```php
// Para otros servicios que necesiten información de propiedades del admin
public function adminTieneCasas($adminId);
public function obtenerCasasDeAdmin($adminId);
public function validarOwnershipCasa($casaId, $adminId);
```

---

## 🚫 RESTRICCIONES IMPORTANTES

### Lo que NO debe hacer:
- ❌ **NO gestionar casas de otros administradores**
- ❌ **NO modificar condominios directamente** (usar CondominioService)
- ❌ **NO manejar accesos globales** (usar AccesosService)

### Scope específico:
- ✅ **Gestión de propiedades del admin logueado**
- ✅ **CRUD de casas propias**
- ✅ **Gestión de residentes en casas propias**
- ✅ **Reportes personalizados del propietario**
- ✅ **Configuración específica de cada casa**
- ✅ **Servicios y facturación de casas**

---

## 📋 ESTRUCTURA DE RESPUESTAS

### Éxito
```php
return $this->successResponse([
    'casas' => $casasData,
    'mensaje' => 'Mis casas gestionadas exitosamente'
]);
```

### Error de Ownership
```php
return $this->errorResponse(
    'No tienes permisos para gestionar esta casa',
    403
);
```

---

## 🔍 LOGGING REQUERIDO

### Actividades a registrar:
- ✅ Modificación de información de casas
- ✅ Asignación/remoción de residentes
- ✅ Configuración de privacidad y notificaciones
- ✅ Generación de reportes
- ✅ Creación de facturas

---

## 📅 INFORMACIÓN DEL PROMPT
- **Fecha de creación:** 28 de Julio, 2025
- **Servicio:** MisCasasService.php
- **Posición en cascada:** Nivel 8 (Propiedades Personales)
- **Estado:** ✅ Listo para implementación

---

## 🎯 INSTRUCCIONES PARA COPILOT

Al generar código para MisCasasService.php:

1. **SIEMPRE heredar de BaseAdminService**
2. **USAR métodos de Casa.php, Persona.php y Condominio.php**
3. **VALIDAR ownership estricto en TODAS las operaciones**
4. **ENFOCAR en casas del admin logueado únicamente**
5. **PROPORCIONAR funcionalidades de propietario**
6. **GESTIONAR residentes de casas propias**
7. **GENERAR reportes personalizados**
8. **REGISTRAR logs de todas las actividades de gestión**
