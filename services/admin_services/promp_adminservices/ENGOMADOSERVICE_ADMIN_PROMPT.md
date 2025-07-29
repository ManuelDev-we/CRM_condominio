# ENGOMADOSERVICE_ADMIN_PROMPT.md
## Prompt Especializado para EngomadoService.php

### 🎯 PROPÓSITO DEL SERVICIO
Administrar engomados/stickers de identificación vehicular dentro de un condominio. Gestiona CRUD de engomados, asignación a vehículos, tipos, vigencias, renovaciones y control de acceso vehicular. Coordina con VehiculoService y AccesosService.

---

## 🏗️ ARQUITECTURA Y HERENCIA

### Clase Base
```php
class EngomadoService extends BaseAdminService
```

### Dependencias
- **Hereda de:** `BaseAdminService.php`
- **Modelos principales:** `Engomado.php`, `Vehiculo.php`
- **Posición en cascada:** Nivel 7 (Identificación Vehicular)
- **Servicios relacionados:** VehiculoService, AccesosService, PersonaService
- **Requiere validaciones de:** CondominioService, CasaService

---

## 📚 MÉTODOS DEL MODELO ENGOMADO DISPONIBLES

### Métodos de Gestión de Engomados
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `createEngomado()` | array $data | int | Crear engomado |
| `findEngomadoById()` | int $id | array | Buscar engomado por ID |
| `findEngomadoByCodigo()` | string $codigo | array | Buscar engomado por código |
| `findEngomadosByCondominio()` | int $condominioId | array | Buscar engomados por condominio |
| `findEngomadosByVehiculo()` | int $vehiculoId | array | Buscar engomados por vehículo |
| `findEngomadosByTipo()` | string $tipo | array | Buscar engomados por tipo |
| `updateEngomado()` | int $id, array $data | bool | Actualizar engomado |
| `deleteEngomado()` | int $id | bool | Eliminar engomado |

### Métodos de Asignación y Estados
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `assignEngomadoToVehiculo()` | int $engomadoId, int $vehiculoId | bool | Asignar engomado a vehículo |
| `unassignEngomadoFromVehiculo()` | int $engomadoId | bool | Desasignar engomado de vehículo |
| `activateEngomado()` | int $engomadoId | bool | Activar engomado |
| `deactivateEngomado()` | int $engomadoId | bool | Desactivar engomado |
| `suspendEngomado()` | int $engomadoId, string $razon | bool | Suspender engomado |
| `reactivateEngomado()` | int $engomadoId | bool | Reactivar engomado |
| `reportEngomadoLost()` | int $engomadoId | bool | Reportar engomado perdido |
| `reportEngomadoStolen()` | int $engomadoId | bool | Reportar engomado robado |

### Métodos de Vigencia y Renovación
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `setFechaVencimiento()` | int $engomadoId, string $fecha | bool | Establecer fecha de vencimiento |
| `renovarEngomado()` | int $engomadoId, string $nuevaFecha | bool | Renovar engomado |
| `getEngomadosVencidos()` | int $condominioId | array | Obtener engomados vencidos |
| `getEngomadosPorVencer()` | int $condominioId, int $dias | array | Obtener engomados por vencer |
| `validateVigencia()` | int $engomadoId | bool | Validar vigencia |
| `extenderVigencia()` | int $engomadoId, int $meses | bool | Extender vigencia |

### Métodos de Tipos y Categorías
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `createTipoEngomado()` | array $data | int | Crear tipo de engomado |
| `findTipoEngomadoById()` | int $id | array | Buscar tipo por ID |
| `findTiposEngomadoByCondominio()` | int $condominioId | array | Buscar tipos por condominio |
| `updateTipoEngomado()` | int $id, array $data | bool | Actualizar tipo |
| `deleteTipoEngomado()` | int $id | bool | Eliminar tipo |
| `setPermisosAcceso()` | int $tipoId, array $permisos | bool | Establecer permisos |

### Métodos de Validación de Acceso
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `validateEngomadoAccess()` | string $codigo, string $area | bool | Validar acceso vehicular |
| `validateEngomadoActive()` | int $engomadoId | bool | Validar engomado activo |
| `validateEngomadoVigent()` | int $engomadoId | bool | Validar vigencia |
| `validateEngomadoNotSuspended()` | int $engomadoId | bool | Validar no suspendido |
| `validateEngomadoPermissions()` | int $engomadoId, string $area | bool | Validar permisos |

### Métodos de Control de Acceso
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `registrarIngresoVehiculo()` | string $codigo, string $area | bool | Registrar ingreso |
| `registrarSalidaVehiculo()` | string $codigo, string $area | bool | Registrar salida |
| `getVehiculosEnCondominio()` | int $condominioId | array | Vehículos presentes |
| `getHistorialAccesos()` | int $engomadoId, array $periodo | array | Historial de accesos |
| `validateEspacioDisponible()` | int $condominioId, string $area | bool | Validar espacio disponible |

### Métodos de Validación
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `validateCondominioExists()` | int $condominioId | bool | Valida existencia de condominio |
| `validateVehiculoExists()` | int $vehiculoId | bool | Valida existencia de vehículo |
| `validateEngomadoExists()` | int $engomadoId | bool | Valida existencia de engomado |
| `validateTipoEngomadoExists()` | int $tipoId | bool | Valida existencia de tipo |
| `validateEngomadoUnique()` | string $codigo, int $condominioId | bool | Valida unicidad |

### Métodos de Estadísticas y Reportes
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `getEstadisticasUso()` | int $condominioId, array $periodo | array | Estadísticas de uso |
| `getReporteOcupacion()` | int $condominioId, string $fecha | array | Reporte de ocupación |
| `getReporteVencimientos()` | int $condominioId, int $meses | array | Reporte de vencimientos |
| `getReporteIncidencias()` | int $condominioId, array $periodo | array | Reporte de incidencias |

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

### 1. **Crear Engomado**
```php
public function crearEngomado($adminId, $condominioId, $datos)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para crear engomados en este condominio');
    }
    
    // Validar campos requeridos
    $this->validateRequiredFields($datos, ['codigo', 'tipo_engomado_id', 'fecha_vencimiento']);
    
    // Validar que el código sea único en el condominio
    if (!$this->engomadoModel->validateEngomadoUnique($datos['codigo'], $condominioId)) {
        return $this->errorResponse('El código del engomado ya existe en este condominio');
    }
    
    // Validar que el tipo de engomado existe
    if (!$this->engomadoModel->validateTipoEngomadoExists($datos['tipo_engomado_id'])) {
        return $this->errorResponse('Tipo de engomado no encontrado');
    }
    
    // Validar fecha de vencimiento
    if (strtotime($datos['fecha_vencimiento']) <= time()) {
        return $this->errorResponse('La fecha de vencimiento debe ser futura');
    }
    
    // Agregar datos adicionales
    $datos['condominio_id'] = $condominioId;
    $datos['fecha_creacion'] = date('Y-m-d H:i:s');
    $datos['estado'] = 'inactivo'; // Inicia inactivo hasta ser asignado
    $datos['suspendido'] = false;
    $datos['perdido'] = false;
    $datos['robado'] = false;
    
    // Generar número de serie único si no se proporciona
    if (!isset($datos['numero_serie'])) {
        $datos['numero_serie'] = $this->generateUniqueSerial($condominioId);
    }
    
    // Crear engomado
    $engomadoId = $this->engomadoModel->createEngomado($datos);
    
    // Log de actividad
    $this->logAdminActivity('engomado_creado', [
        'admin_id' => $adminId,
        'condominio_id' => $condominioId,
        'engomado_id' => $engomadoId,
        'codigo' => $datos['codigo'],
        'tipo_engomado_id' => $datos['tipo_engomado_id'],
        'fecha_vencimiento' => $datos['fecha_vencimiento']
    ]);
    
    return $this->successResponse(['id' => $engomadoId], 'Engomado creado exitosamente');
}
```

### 2. **Asignar Engomado a Vehículo**
```php
public function asignarEngomadoAVehiculo($adminId, $engomadoId, $vehiculoId)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Obtener engomado
    $engomado = $this->engomadoModel->findEngomadoById($engomadoId);
    if (!$engomado) {
        return $this->errorResponse('Engomado no encontrado');
    }
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($engomado['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para asignar este engomado');
    }
    
    // Validar que el vehículo existe
    if (!$this->engomadoModel->validateVehiculoExists($vehiculoId)) {
        return $this->errorResponse('Vehículo no encontrado');
    }
    
    // Validar que el engomado no esté ya asignado
    if ($engomado['vehiculo_id']) {
        return $this->errorResponse('El engomado ya está asignado a otro vehículo');
    }
    
    // Validar que el engomado no esté suspendido
    if ($engomado['suspendido']) {
        return $this->errorResponse('El engomado está suspendido y no puede ser asignado');
    }
    
    // Validar que el engomado no esté vencido
    if (strtotime($engomado['fecha_vencimiento']) <= time()) {
        return $this->errorResponse('El engomado está vencido');
    }
    
    // Verificar que el vehículo pertenezca al mismo condominio
    $vehiculo = $this->vehiculoService->obtenerVehiculo($vehiculoId);
    if (!$this->vehiculoService->vehiculoPerteneceACondominio($vehiculoId, $engomado['condominio_id'])) {
        return $this->errorResponse('El vehículo no pertenece a este condominio');
    }
    
    // Verificar que el vehículo no tenga ya un engomado activo del mismo tipo
    $engomadosVehiculo = $this->engomadoModel->findEngomadosByVehiculo($vehiculoId);
    foreach ($engomadosVehiculo as $eng) {
        if ($eng['tipo_engomado_id'] == $engomado['tipo_engomado_id'] && 
            $eng['estado'] == 'activo' && 
            strtotime($eng['fecha_vencimiento']) > time()) {
            return $this->errorResponse('El vehículo ya tiene un engomado activo de este tipo');
        }
    }
    
    // Asignar engomado
    $resultado = $this->engomadoModel->assignEngomadoToVehiculo($engomadoId, $vehiculoId);
    
    // Activar engomado automáticamente al asignarlo
    $this->engomadoModel->activateEngomado($engomadoId);
    
    // Log de actividad
    $this->logAdminActivity('engomado_asignado', [
        'admin_id' => $adminId,
        'engomado_id' => $engomadoId,
        'vehiculo_id' => $vehiculoId,
        'condominio_id' => $engomado['condominio_id'],
        'codigo' => $engomado['codigo']
    ]);
    
    return $this->successResponse($resultado, 'Engomado asignado exitosamente');
}
```

### 3. **Validar Acceso Vehicular**
```php
public function validarAccesoVehicular($codigo, $area, $tipoAcceso = 'ingreso')
{
    // Esta función puede ser llamada desde dispositivos externos
    // por lo que no requiere autenticación de admin
    
    // Buscar engomado por código
    $engomado = $this->engomadoModel->findEngomadoByCodigo($codigo);
    if (!$engomado) {
        $this->logAccesoVehicular(null, $area, 'engomado_no_encontrado', $codigo);
        return $this->errorResponse('Engomado no encontrado');
    }
    
    // Validar que el engomado esté activo
    if (!$this->engomadoModel->validateEngomadoActive($engomado['id'])) {
        $this->logAccesoVehicular($engomado['id'], $area, 'engomado_inactivo');
        return $this->errorResponse('Engomado inactivo');
    }
    
    // Validar que el engomado no esté suspendido
    if (!$this->engomadoModel->validateEngomadoNotSuspended($engomado['id'])) {
        $this->logAccesoVehicular($engomado['id'], $area, 'engomado_suspendido');
        return $this->errorResponse('Engomado suspendido');
    }
    
    // Validar vigencia
    if (!$this->engomadoModel->validateEngomadoVigent($engomado['id'])) {
        $this->logAccesoVehicular($engomado['id'], $area, 'engomado_vencido');
        return $this->errorResponse('Engomado vencido');
    }
    
    // Validar que el engomado esté asignado a un vehículo
    if (!$engomado['vehiculo_id']) {
        $this->logAccesoVehicular($engomado['id'], $area, 'engomado_sin_asignar');
        return $this->errorResponse('Engomado sin asignar');
    }
    
    // Validar permisos para el área específica
    if (!$this->engomadoModel->validateEngomadoPermissions($engomado['id'], $area)) {
        $this->logAccesoVehicular($engomado['id'], $area, 'sin_permisos');
        return $this->errorResponse('Sin permisos para acceder a esta área');
    }
    
    // Validar espacio disponible si es ingreso
    if ($tipoAcceso == 'ingreso') {
        if (!$this->engomadoModel->validateEspacioDisponible($engomado['condominio_id'], $area)) {
            $this->logAccesoVehicular($engomado['id'], $area, 'sin_espacio');
            return $this->errorResponse('No hay espacios disponibles');
        }
    }
    
    // Registrar movimiento
    if ($tipoAcceso == 'ingreso') {
        $this->engomadoModel->registrarIngresoVehiculo($codigo, $area);
    } else {
        $this->engomadoModel->registrarSalidaVehiculo($codigo, $area);
    }
    
    // Registrar acceso exitoso
    $this->logAccesoVehicular($engomado['id'], $area, 'acceso_permitido');
    
    return $this->successResponse([
        'engomado_id' => $engomado['id'],
        'vehiculo_id' => $engomado['vehiculo_id'],
        'acceso_permitido' => true,
        'tipo_acceso' => $tipoAcceso
    ], 'Acceso vehicular permitido');
}
```

### 4. **Gestionar Vencimientos**
```php
public function procesarVencimientos($adminId, $condominioId)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para procesar vencimientos en este condominio');
    }
    
    // Obtener engomados vencidos
    $vencidos = $this->engomadoModel->getEngomadosVencidos($condominioId);
    
    // Obtener engomados por vencer (próximos 30 días)
    $porVencer = $this->engomadoModel->getEngomadosPorVencer($condominioId, 30);
    
    // Desactivar engomados vencidos
    $desactivados = 0;
    foreach ($vencidos as $engomado) {
        if ($engomado['estado'] == 'activo') {
            $this->engomadoModel->deactivateEngomado($engomado['id']);
            $desactivados++;
            
            // Log de actividad
            $this->logAdminActivity('engomado_vencido_desactivado', [
                'admin_id' => $adminId,
                'engomado_id' => $engomado['id'],
                'condominio_id' => $condominioId,
                'fecha_vencimiento' => $engomado['fecha_vencimiento']
            ]);
        }
    }
    
    return $this->successResponse([
        'vencidos' => count($vencidos),
        'desactivados' => $desactivados,
        'por_vencer' => count($porVencer),
        'detalles' => [
            'vencidos' => $vencidos,
            'por_vencer' => $porVencer
        ]
    ], 'Vencimientos procesados exitosamente');
}

public function renovarEngomado($adminId, $engomadoId, $mesesExtension)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Obtener engomado
    $engomado = $this->engomadoModel->findEngomadoById($engomadoId);
    if (!$engomado) {
        return $this->errorResponse('Engomado no encontrado');
    }
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($engomado['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para renovar este engomado');
    }
    
    // Validar meses de extensión
    if ($mesesExtension < 1 || $mesesExtension > 24) {
        return $this->errorResponse('Los meses de extensión deben estar entre 1 y 24');
    }
    
    // Calcular nueva fecha de vencimiento
    $fechaActual = strtotime($engomado['fecha_vencimiento']);
    $nuevaFecha = date('Y-m-d', strtotime("+{$mesesExtension} months", $fechaActual));
    
    // Renovar engomado
    $resultado = $this->engomadoModel->renovarEngomado($engomadoId, $nuevaFecha);
    
    // Si estaba vencido, reactivarlo
    if (strtotime($engomado['fecha_vencimiento']) <= time() && $engomado['vehiculo_id']) {
        $this->engomadoModel->activateEngomado($engomadoId);
    }
    
    // Log de actividad
    $this->logAdminActivity('engomado_renovado', [
        'admin_id' => $adminId,
        'engomado_id' => $engomadoId,
        'condominio_id' => $engomado['condominio_id'],
        'fecha_vencimiento_anterior' => $engomado['fecha_vencimiento'],
        'fecha_vencimiento_nueva' => $nuevaFecha,
        'meses_extension' => $mesesExtension
    ]);
    
    return $this->successResponse([
        'fecha_vencimiento_nueva' => $nuevaFecha,
        'meses_extension' => $mesesExtension
    ], 'Engomado renovado exitosamente');
}
```

### 5. **Obtener Reportes**
```php
public function obtenerReporteOcupacion($adminId, $condominioId, $fecha = null)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para ver reportes de este condominio');
    }
    
    // Aplicar rate limiting
    $this->enforceRateLimit('reporte_ocupacion_' . $adminId);
    
    // Usar fecha actual si no se proporciona
    if (!$fecha) {
        $fecha = date('Y-m-d');
    }
    
    // Obtener reporte
    $reporte = $this->engomadoModel->getReporteOcupacion($condominioId, $fecha);
    
    // Agregar información adicional
    $vehiculosPresentes = $this->engomadoModel->getVehiculosEnCondominio($condominioId);
    $reporte['vehiculos_presentes'] = count($vehiculosPresentes);
    $reporte['detalle_vehiculos'] = $vehiculosPresentes;
    
    return $this->successResponse($reporte, 'Reporte de ocupación obtenido exitosamente');
}

public function obtenerReporteVencimientos($adminId, $condominioId, $meses = 3)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para ver reportes de este condominio');
    }
    
    // Aplicar rate limiting
    $this->enforceRateLimit('reporte_vencimientos_' . $adminId);
    
    // Obtener reporte
    $reporte = $this->engomadoModel->getReporteVencimientos($condominioId, $meses);
    
    return $this->successResponse($reporte, 'Reporte de vencimientos obtenido exitosamente');
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

### Validaciones Específicas
```php
public function engomadoPerteneceACondominio($engomadoId, $condominioId)
{
    $engomado = $this->engomadoModel->findEngomadoById($engomadoId);
    return $engomado && $engomado['condominio_id'] == $condominioId;
}

private function generateUniqueSerial($condominioId)
{
    do {
        $serial = 'ENG' . $condominioId . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $existing = $this->engomadoModel->findEngomadoByCodigo($serial);
    } while ($existing);
    
    return $serial;
}

private function logAccesoVehicular($engomadoId, $area, $resultado, $codigo = null)
{
    $data = [
        'engomado_id' => $engomadoId,
        'area' => $area,
        'resultado' => $resultado,
        'timestamp' => date('Y-m-d H:i:s'),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    if ($codigo) {
        $data['codigo_intentado'] = $codigo;
    }
    
    // Log en base de datos y archivo
    $this->engomadoModel->logEngomadoAccess($engomadoId, $area, $resultado);
}
```

---

## 🔄 INTEGRACIÓN CON OTROS SERVICIOS

### Debe usar servicios en cascada:
```php
// Validaciones de otros servicios
if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
    return $this->errorResponse("No tienes permisos");
}

// Verificar vehículos
if (!$this->vehiculoService->vehiculoPerteneceACondominio($vehiculoId, $condominioId)) {
    return $this->errorResponse("Vehículo no pertenece al condominio");
}

// Coordinar con AccesosService para control de ingreso/salida
$this->accesosService->registrarMovimientoVehicular($engomadoId, $tipoAcceso);
```

### Proporciona para otros servicios:
```php
// Para AccesosService, DispositivoService
public function engomadoPerteneceACondominio($engomadoId, $condominioId);
public function validarEngomadoActivo($engomadoId);
public function validarAccesoVehicular($codigo, $area);
```

---

## 🚫 RESTRICCIONES IMPORTANTES

### Lo que NO debe hacer:
- ❌ **NO gestionar vehículos directamente** (usar VehiculoService)
- ❌ **NO manejar personas directamente** (usar PersonaService)
- ❌ **NO controlar dispositivos físicos** (coordinar con DispositivoService)

### Scope específico:
- ✅ **CRUD de engomados y tipos**
- ✅ **Asignación de engomados a vehículos**
- ✅ **Gestión de vigencias y renovaciones**
- ✅ **Validación de acceso vehicular**
- ✅ **Control de ocupación de estacionamientos**
- ✅ **Reportes de vencimientos e incidencias**

---

## 📋 ESTRUCTURA DE RESPUESTAS

### Éxito
```php
return $this->successResponse([
    'engomado' => $engomadoData,
    'mensaje' => 'Engomado gestionado exitosamente'
]);
```

### Error de Acceso
```php
return $this->errorResponse(
    'Engomado vencido - Acceso vehicular denegado',
    403
);
```

---

## 🔍 LOGGING REQUERIDO

### Actividades a registrar:
- ✅ Creación/modificación de engomados
- ✅ Asignación/desasignación a vehículos
- ✅ Cambios de estado y suspensiones
- ✅ Renovaciones y extensiones
- ✅ Intentos de acceso vehicular (exitosos y fallidos)
- ✅ Ingresos y salidas de vehículos

---

## 📅 INFORMACIÓN DEL PROMPT
- **Fecha de creación:** 28 de Julio, 2025
- **Servicio:** EngomadoService.php
- **Posición en cascada:** Nivel 7 (Identificación Vehicular)
- **Estado:** ✅ Listo para implementación

---

## 🎯 INSTRUCCIONES PARA COPILOT

Al generar código para EngomadoService.php:

1. **SIEMPRE heredar de BaseAdminService**
2. **USAR métodos de Engomado.php y Vehiculo.php**
3. **VALIDAR ownership del condominio en TODAS las operaciones**
4. **GESTIONAR vigencias y renovaciones apropiadamente**
5. **COORDINAR con VehiculoService y AccesosService**
6. **VALIDAR permisos de acceso por área**
7. **CONTROLAR ocupación de estacionamientos**
8. **REGISTRAR TODOS los movimientos vehiculares**
9. **PROPORCIONAR métodos de validación para otros servicios**
