# CALLESERVICE_ADMIN_PROMPT.md
## Prompt Especializado para CalleService.php

### 🎯 PROPÓSITO DEL SERVICIO
Administrar calles dentro de un condominio. Gestiona CRUD de calles, organización territorial, validación de ubicaciones y coordinación con CasaService. Es la base organizacional para la estructura de propiedades.

---

## 🏗️ ARQUITECTURA Y HERENCIA

### Clase Base
```php
class CalleService extends BaseAdminService
```

### Dependencias
- **Hereda de:** `BaseAdminService.php`
- **Modelos principales:** `Calle.php`, `Condominio.php`
- **Posición en cascada:** Nivel 3 (Infraestructura Base)
- **Servicios dependientes:** CasaService, AreaComunService
- **Requiere validaciones de:** CondominioService

---

## 📚 MÉTODOS DEL MODELO CALLE DISPONIBLES

### Métodos de Gestión de Calles
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `createCalle()` | array $data | int | Crear calle |
| `findCalleById()` | int $id | array | Buscar calle por ID |
| `findCallesByCondominio()` | int $condominioId | array | Buscar calles por condominio |
| `findCalleByNombre()` | string $nombre, int $condominioId | array | Buscar calle por nombre |
| `updateCalle()` | int $id, array $data | bool | Actualizar calle |
| `deleteCalle()` | int $id | bool | Eliminar calle |
| `activateCalle()` | int $id | bool | Activar calle |
| `deactivateCalle()` | int $id | bool | Desactivar calle |

### Métodos de Organización Territorial
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `setSector()` | int $calleId, string $sector | bool | Establecer sector |
| `setCodigoPostal()` | int $calleId, string $codigo | bool | Establecer código postal |
| `setTipoCalle()` | int $calleId, string $tipo | bool | Establecer tipo de calle |
| `getCallesBySector()` | int $condominioId, string $sector | array | Obtener calles por sector |
| `getCallesByTipo()` | int $condominioId, string $tipo | array | Obtener calles por tipo |
| `organizarPorSector()` | int $condominioId | array | Organizar calles por sector |

### Métodos de Casas Relacionadas
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `getCasasByCalle()` | int $calleId | array | Obtener casas de la calle |
| `countCasasEnCalle()` | int $calleId | int | Contar casas en calle |
| `getCapacidadMaximaCalle()` | int $calleId | int | Obtener capacidad máxima |
| `getOcupacionActualCalle()` | int $calleId | int | Obtener ocupación actual |
| `validateCapacidadDisponible()` | int $calleId | bool | Validar capacidad disponible |

### Métodos de Validación
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `validateCondominioExists()` | int $condominioId | bool | Valida existencia de condominio |
| `validateCalleExists()` | int $calleId | bool | Valida existencia de calle |
| `validateCalleUnique()` | string $nombre, int $condominioId | bool | Valida unicidad de nombre |
| `validateCalleInCondominio()` | int $calleId, int $condominioId | bool | Valida calle en condominio |
| `validateCalleActive()` | int $calleId | bool | Valida calle activa |

### Métodos de Configuración
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `setNumeracionInicio()` | int $calleId, int $numero | bool | Establecer numeración inicial |
| `setNumeracionFin()` | int $calleId, int $numero | bool | Establecer numeración final |
| `setTipoNumeracion()` | int $calleId, string $tipo | bool | Establecer tipo de numeración |
| `validateNumeroCasa()` | int $calleId, int $numero | bool | Validar número de casa |
| `getSiguienteNumero()` | int $calleId | int | Obtener siguiente número disponible |

### Métodos de Servicios y Características
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `setServicios()` | int $calleId, array $servicios | bool | Establecer servicios disponibles |
| `getServicios()` | int $calleId | array | Obtener servicios de la calle |
| `addServicio()` | int $calleId, string $servicio | bool | Agregar servicio |
| `removeServicio()` | int $calleId, string $servicio | bool | Remover servicio |
| `validateServicioDisponible()` | int $calleId, string $servicio | bool | Validar servicio disponible |

### Métodos de Estadísticas y Reportes
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `getEstadisticasCalle()` | int $calleId | array | Estadísticas de la calle |
| `getReporteOcupacion()` | int $condominioId | array | Reporte de ocupación |
| `getReporteServicios()` | int $condominioId | array | Reporte de servicios |
| `getCallesConMayorOcupacion()` | int $condominioId, int $limit | array | Calles con mayor ocupación |

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

### 1. **Crear Calle**
```php
public function crearCalle($adminId, $condominioId, $datos)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para crear calles en este condominio');
    }
    
    // Validar campos requeridos
    $this->validateRequiredFields($datos, ['nombre']);
    
    // Validar que el nombre sea único en el condominio
    if (!$this->calleModel->validateCalleUnique($datos['nombre'], $condominioId)) {
        return $this->errorResponse('Ya existe una calle con este nombre en el condominio');
    }
    
    // Validar y normalizar datos
    $datos['nombre'] = trim($datos['nombre']);
    
    // Validar tipo de calle
    $tiposValidos = ['calle', 'avenida', 'boulevard', 'pasaje', 'callejon', 'privada'];
    if (isset($datos['tipo']) && !in_array(strtolower($datos['tipo']), $tiposValidos)) {
        return $this->errorResponse('Tipo de calle inválido');
    }
    
    // Validar numeración si se proporciona
    if (isset($datos['numeracion_inicio']) && isset($datos['numeracion_fin'])) {
        if ($datos['numeracion_inicio'] >= $datos['numeracion_fin']) {
            return $this->errorResponse('La numeración inicial debe ser menor que la final');
        }
    }
    
    // Agregar datos adicionales
    $datos['condominio_id'] = $condominioId;
    $datos['fecha_creacion'] = date('Y-m-d H:i:s');
    $datos['activa'] = true;
    
    // Establecer valores por defecto
    if (!isset($datos['tipo'])) {
        $datos['tipo'] = 'calle';
    }
    
    if (!isset($datos['capacidad_maxima'])) {
        $datos['capacidad_maxima'] = 50; // Capacidad por defecto
    }
    
    if (!isset($datos['tipo_numeracion'])) {
        $datos['tipo_numeracion'] = 'secuencial'; // secuencial, par-impar
    }
    
    // Servicios básicos por defecto
    if (!isset($datos['servicios'])) {
        $datos['servicios'] = json_encode([
            'alumbrado' => true,
            'agua' => true,
            'drenaje' => true,
            'internet' => false,
            'gas' => false
        ]);
    }
    
    // Crear calle
    $calleId = $this->calleModel->createCalle($datos);
    
    // Log de actividad
    $this->logAdminActivity('calle_creada', [
        'admin_id' => $adminId,
        'condominio_id' => $condominioId,
        'calle_id' => $calleId,
        'nombre' => $datos['nombre'],
        'tipo' => $datos['tipo'],
        'sector' => $datos['sector'] ?? null
    ]);
    
    return $this->successResponse(['id' => $calleId], 'Calle creada exitosamente');
}
```

### 2. **Obtener Calles del Condominio**
```php
public function obtenerCallesCondominio($adminId, $condominioId, $opciones = [])
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para ver calles de este condominio');
    }
    
    // Aplicar rate limiting
    $this->enforceRateLimit('calles_' . $adminId);
    
    // Obtener calles
    $calles = $this->calleModel->findCallesByCondominio($condominioId);
    
    // Aplicar filtros si se proporcionan
    if (isset($opciones['sector'])) {
        $calles = array_filter($calles, function($calle) use ($opciones) {
            return $calle['sector'] == $opciones['sector'];
        });
    }
    
    if (isset($opciones['tipo'])) {
        $calles = array_filter($calles, function($calle) use ($opciones) {
            return $calle['tipo'] == $opciones['tipo'];
        });
    }
    
    if (isset($opciones['activas_solamente']) && $opciones['activas_solamente']) {
        $calles = array_filter($calles, function($calle) {
            return $calle['activa'];
        });
    }
    
    // Agregar información adicional
    foreach ($calles as &$calle) {
        $calle['casas_count'] = $this->calleModel->countCasasEnCalle($calle['id']);
        $calle['ocupacion_porcentaje'] = ($calle['capacidad_maxima'] > 0) 
            ? round(($calle['casas_count'] / $calle['capacidad_maxima']) * 100, 2) 
            : 0;
        $calle['servicios'] = json_decode($calle['servicios'], true);
    }
    
    return $this->successResponse($calles, 'Calles obtenidas exitosamente');
}
```

### 3. **Actualizar Calle**
```php
public function actualizarCalle($adminId, $calleId, $datos)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Obtener calle actual
    $calle = $this->calleModel->findCalleById($calleId);
    if (!$calle) {
        return $this->errorResponse('Calle no encontrada');
    }
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($calle['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para editar esta calle');
    }
    
    // Validar nombre único si se cambia
    if (isset($datos['nombre']) && $datos['nombre'] != $calle['nombre']) {
        if (!$this->calleModel->validateCalleUnique($datos['nombre'], $calle['condominio_id'])) {
            return $this->errorResponse('Ya existe una calle con este nombre en el condominio');
        }
        $datos['nombre'] = trim($datos['nombre']);
    }
    
    // Validar tipo de calle si se cambia
    if (isset($datos['tipo'])) {
        $tiposValidos = ['calle', 'avenida', 'boulevard', 'pasaje', 'callejon', 'privada'];
        if (!in_array(strtolower($datos['tipo']), $tiposValidos)) {
            return $this->errorResponse('Tipo de calle inválido');
        }
    }
    
    // Validar numeración si se cambia
    if (isset($datos['numeracion_inicio']) && isset($datos['numeracion_fin'])) {
        if ($datos['numeracion_inicio'] >= $datos['numeracion_fin']) {
            return $this->errorResponse('La numeración inicial debe ser menor que la final');
        }
    }
    
    // Validar capacidad máxima si se reduce
    if (isset($datos['capacidad_maxima'])) {
        $casasActuales = $this->calleModel->countCasasEnCalle($calleId);
        if ($datos['capacidad_maxima'] < $casasActuales) {
            return $this->errorResponse("No se puede reducir la capacidad por debajo del número de casas actuales ($casasActuales)");
        }
    }
    
    // Procesar servicios si se envían
    if (isset($datos['servicios']) && is_array($datos['servicios'])) {
        $datos['servicios'] = json_encode($datos['servicios']);
    }
    
    // Actualizar calle
    $resultado = $this->calleModel->updateCalle($calleId, $datos);
    
    // Log de actividad
    $this->logAdminActivity('calle_actualizada', [
        'admin_id' => $adminId,
        'calle_id' => $calleId,
        'condominio_id' => $calle['condominio_id'],
        'cambios' => array_keys($datos)
    ]);
    
    return $this->successResponse($resultado, 'Calle actualizada exitosamente');
}
```

### 4. **Gestionar Numeración**
```php
public function configurarNumeracion($adminId, $calleId, $configuracion)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Obtener calle
    $calle = $this->calleModel->findCalleById($calleId);
    if (!$calle) {
        return $this->errorResponse('Calle no encontrada');
    }
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($calle['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para configurar esta calle');
    }
    
    // Validar configuración
    $this->validateRequiredFields($configuracion, ['tipo_numeracion']);
    
    $tiposValidos = ['secuencial', 'par-impar', 'personalizado'];
    if (!in_array($configuracion['tipo_numeracion'], $tiposValidos)) {
        return $this->errorResponse('Tipo de numeración inválido');
    }
    
    // Validar rangos si se especifican
    if (isset($configuracion['numeracion_inicio']) && isset($configuracion['numeracion_fin'])) {
        if ($configuracion['numeracion_inicio'] >= $configuracion['numeracion_fin']) {
            return $this->errorResponse('La numeración inicial debe ser menor que la final');
        }
        
        // Verificar que no hay casas con números fuera del nuevo rango
        $casasExistentes = $this->calleModel->getCasasByCalle($calleId);
        foreach ($casasExistentes as $casa) {
            if ($casa['numero_casa'] < $configuracion['numeracion_inicio'] || 
                $casa['numero_casa'] > $configuracion['numeracion_fin']) {
                return $this->errorResponse("La casa #{$casa['numero_casa']} está fuera del nuevo rango de numeración");
            }
        }
    }
    
    // Actualizar configuración
    $resultado = $this->calleModel->setTipoNumeracion($calleId, $configuracion['tipo_numeracion']);
    
    if (isset($configuracion['numeracion_inicio'])) {
        $this->calleModel->setNumeracionInicio($calleId, $configuracion['numeracion_inicio']);
    }
    
    if (isset($configuracion['numeracion_fin'])) {
        $this->calleModel->setNumeracionFin($calleId, $configuracion['numeracion_fin']);
    }
    
    // Log de actividad
    $this->logAdminActivity('numeracion_configurada', [
        'admin_id' => $adminId,
        'calle_id' => $calleId,
        'condominio_id' => $calle['condominio_id'],
        'tipo_numeracion' => $configuracion['tipo_numeracion']
    ]);
    
    return $this->successResponse($resultado, 'Numeración configurada exitosamente');
}

public function obtenerSiguienteNumero($adminId, $calleId)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Obtener calle
    $calle = $this->calleModel->findCalleById($calleId);
    if (!$calle) {
        return $this->errorResponse('Calle no encontrada');
    }
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($calle['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para consultar esta calle');
    }
    
    // Obtener siguiente número
    $siguienteNumero = $this->calleModel->getSiguienteNumero($calleId);
    
    return $this->successResponse([
        'siguiente_numero' => $siguienteNumero,
        'tipo_numeracion' => $calle['tipo_numeracion']
    ], 'Siguiente número obtenido exitosamente');
}
```

### 5. **Gestionar Servicios**
```php
public function actualizarServicios($adminId, $calleId, $servicios)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Obtener calle
    $calle = $this->calleModel->findCalleById($calleId);
    if (!$calle) {
        return $this->errorResponse('Calle no encontrada');
    }
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($calle['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para actualizar servicios de esta calle');
    }
    
    // Validar servicios
    $serviciosValidos = ['alumbrado', 'agua', 'drenaje', 'internet', 'gas', 'cable', 'telefono', 'seguridad'];
    foreach ($servicios as $servicio => $disponible) {
        if (!in_array($servicio, $serviciosValidos)) {
            return $this->errorResponse("Servicio inválido: $servicio");
        }
        
        if (!is_bool($disponible)) {
            return $this->errorResponse("El valor para $servicio debe ser verdadero o falso");
        }
    }
    
    // Actualizar servicios
    $resultado = $this->calleModel->setServicios($calleId, $servicios);
    
    // Log de actividad
    $this->logAdminActivity('servicios_actualizados', [
        'admin_id' => $adminId,
        'calle_id' => $calleId,
        'condominio_id' => $calle['condominio_id'],
        'servicios' => array_keys(array_filter($servicios))
    ]);
    
    return $this->successResponse($resultado, 'Servicios actualizados exitosamente');
}
```

### 6. **Obtener Estadísticas**
```php
public function obtenerEstadisticasCalles($adminId, $condominioId)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para ver estadísticas de este condominio');
    }
    
    // Aplicar rate limiting
    $this->enforceRateLimit('estadisticas_calles_' . $adminId);
    
    // Obtener estadísticas generales
    $estadisticas = $this->calleModel->getReporteOcupacion($condominioId);
    
    // Agregar información detallada
    $calles = $this->calleModel->findCallesByCondominio($condominioId);
    $estadisticas['total_calles'] = count($calles);
    $estadisticas['calles_activas'] = count(array_filter($calles, function($c) { return $c['activa']; }));
    
    // Calles por sector
    $estadisticas['por_sector'] = [];
    foreach ($calles as $calle) {
        $sector = $calle['sector'] ?? 'Sin sector';
        if (!isset($estadisticas['por_sector'][$sector])) {
            $estadisticas['por_sector'][$sector] = 0;
        }
        $estadisticas['por_sector'][$sector]++;
    }
    
    // Calles por tipo
    $estadisticas['por_tipo'] = [];
    foreach ($calles as $calle) {
        $tipo = $calle['tipo'];
        if (!isset($estadisticas['por_tipo'][$tipo])) {
            $estadisticas['por_tipo'][$tipo] = 0;
        }
        $estadisticas['por_tipo'][$tipo]++;
    }
    
    return $this->successResponse($estadisticas, 'Estadísticas obtenidas exitosamente');
}

public function obtenerCallesConMayorOcupacion($adminId, $condominioId, $limite = 5)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para ver este reporte');
    }
    
    // Obtener calles con mayor ocupación
    $calles = $this->calleModel->getCallesConMayorOcupacion($condominioId, $limite);
    
    return $this->successResponse($calles, 'Calles con mayor ocupación obtenidas exitosamente');
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
public function callePerteneceACondominio($calleId, $condominioId)
{
    $calle = $this->calleModel->findCalleById($calleId);
    return $calle && $calle['condominio_id'] == $condominioId;
}

private function validarRangoNumeracion($inicio, $fin, $casasExistentes)
{
    foreach ($casasExistentes as $casa) {
        if ($casa['numero_casa'] < $inicio || $casa['numero_casa'] > $fin) {
            return false;
        }
    }
    return true;
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
```

### Proporciona para otros servicios:
```php
// Para CasaService, AreaComunService
public function callePerteneceACondominio($calleId, $condominioId);
public function validarCalleActiva($calleId);
public function validarCapacidadDisponible($calleId);
public function obtenerSiguienteNumeroCasa($calleId);
```

---

## 🚫 RESTRICCIONES IMPORTANTES

### Lo que NO debe hacer:
- ❌ **NO gestionar casas directamente** (usar CasaService)
- ❌ **NO crear propiedades** (solo estructura organizacional)
- ❌ **NO gestionar residentes** (usar PersonaService)

### Scope específico:
- ✅ **CRUD de calles**
- ✅ **Organización territorial (sectores)**
- ✅ **Configuración de numeración**
- ✅ **Gestión de servicios de la calle**
- ✅ **Estadísticas de ocupación**

---

## 📋 ESTRUCTURA DE RESPUESTAS

### Éxito
```php
return $this->successResponse([
    'calle' => $calleData,
    'mensaje' => 'Calle gestionada exitosamente'
]);
```

### Error de Capacity
```php
return $this->errorResponse(
    'La capacidad no puede ser menor al número de casas existentes',
    400
);
```

---

## 🔍 LOGGING REQUERIDO

### Actividades a registrar:
- ✅ Creación/modificación de calles
- ✅ Cambios de configuración de numeración
- ✅ Actualizaciones de servicios
- ✅ Cambios de estado (activar/desactivar)
- ✅ Consultas de estadísticas

---

## 📅 INFORMACIÓN DEL PROMPT
- **Fecha de creación:** 28 de Julio, 2025
- **Servicio:** CalleService.php
- **Posición en cascada:** Nivel 3 (Infraestructura Base)
- **Estado:** ✅ Listo para implementación

---

## 🎯 INSTRUCCIONES PARA COPILOT

Al generar código para CalleService.php:

1. **SIEMPRE heredar de BaseAdminService**
2. **USAR métodos de Calle.php y Condominio.php**
3. **VALIDAR ownership del condominio en TODAS las operaciones**
4. **GESTIONAR numeración de casas apropiadamente**
5. **VALIDAR capacidades antes de permitir cambios**
6. **ORGANIZAR por sectores y tipos**
7. **REGISTRAR logs de todas las actividades**
8. **PROPORCIONAR métodos de validación para CasaService**
