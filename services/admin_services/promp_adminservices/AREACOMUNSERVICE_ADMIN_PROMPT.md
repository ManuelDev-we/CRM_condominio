# AREACOMUNSERVICE_ADMIN_PROMPT.md
## Prompt Especializado para AreaComunService.php

### 🎯 PROPÓSITO DEL SERVICIO
Administrar áreas comunes dentro de un condominio. Gestiona CRUD de áreas comunes, horarios de acceso, capacidades, reservas y mantenimiento. Coordina con DispositivoService para control de acceso físico.

---

## 🏗️ ARQUITECTURA Y HERENCIA

### Clase Base
```php
class AreaComunService extends BaseAdminService
```

### Dependencias
- **Hereda de:** `BaseAdminService.php`
- **Modelos principales:** `AreaComun.php`
- **Posición en cascada:** Nivel 5 (Instalaciones)
- **Servicios relacionados:** DispositivoService, AccesosService
- **Requiere validaciones de:** CondominioService

---

## 📚 MÉTODOS DEL MODELO AREACOMUN DISPONIBLES

### Métodos de Gestión de Áreas Comunes
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `createAreaComun()` | array $data | int | Crear área común |
| `findAreaComunById()` | int $id | array | Buscar área común por ID |
| `findAreasComunes()` | array $filtros | array | Buscar áreas comunes con filtros |
| `findAreasComunesByCondominio()` | int $condominioId | array | Buscar áreas comunes por condominio |
| `updateAreaComun()` | int $id, array $data | bool | Actualizar área común |
| `deleteAreaComun()` | int $id | bool | Eliminar área común |
| `changeAreaComunStatus()` | int $id, string $status | bool | Cambiar estado del área común |

### Métodos de Horarios y Disponibilidad
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `setHorariosAcceso()` | int $areaId, array $horarios | bool | Establecer horarios de acceso |
| `getHorariosAcceso()` | int $areaId | array | Obtener horarios de acceso |
| `updateHorariosAcceso()` | int $areaId, array $horarios | bool | Actualizar horarios de acceso |
| `validateHorarioAcceso()` | int $areaId, string $datetime | bool | Validar horario de acceso |
| `getDisponibilidad()` | int $areaId, string $fecha | array | Obtener disponibilidad por fecha |

### Métodos de Capacidad y Restricciones
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `setCapacidadMaxima()` | int $areaId, int $capacidad | bool | Establecer capacidad máxima |
| `getCapacidadMaxima()` | int $areaId | int | Obtener capacidad máxima |
| `validateCapacidad()` | int $areaId, int $personas | bool | Validar capacidad disponible |
| `setRestriccionesEdad()` | int $areaId, array $restricciones | bool | Establecer restricciones de edad |
| `validateRestricciones()` | int $areaId, array $persona | bool | Validar restricciones de acceso |

### Métodos de Reservas
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `createReserva()` | array $data | int | Crear reserva |
| `findReservaById()` | int $id | array | Buscar reserva por ID |
| `findReservasByArea()` | int $areaId | array | Buscar reservas por área |
| `findReservasByPersona()` | int $personaId | array | Buscar reservas por persona |
| `updateReserva()` | int $id, array $data | bool | Actualizar reserva |
| `cancelarReserva()` | int $id | bool | Cancelar reserva |
| `validateReserva()` | array $data | bool | Validar datos de reserva |

### Métodos de Mantenimiento
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `createMantenimiento()` | array $data | int | Crear mantenimiento |
| `findMantenimientosByArea()` | int $areaId | array | Buscar mantenimientos por área |
| `updateMantenimiento()` | int $id, array $data | bool | Actualizar mantenimiento |
| `marcarMantenimientoCompleto()` | int $id | bool | Marcar mantenimiento como completo |
| `getHistorialMantenimiento()` | int $areaId | array | Obtener historial de mantenimiento |

### Métodos de Validación
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `validateCondominioExists()` | int $condominioId | bool | Valida existencia de condominio |
| `validateAreaComunExists()` | int $areaId | bool | Valida existencia de área común |
| `validatePersonaExists()` | int $personaId | bool | Valida existencia de persona |
| `validateDisponibilidad()` | int $areaId, string $datetime | bool | Valida disponibilidad de área |

### Métodos de Estadísticas y Reportes
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `getEstadisticasUso()` | int $areaId, array $periodo | array | Estadísticas de uso |
| `getReporteOcupacion()` | int $condominioId, string $mes | array | Reporte de ocupación |
| `getReporteMantenimiento()` | int $areaId, array $periodo | array | Reporte de mantenimiento |

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

### 1. **Crear Área Común**
```php
public function crearAreaComun($adminId, $condominioId, $datos)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para crear áreas comunes en este condominio');
    }
    
    // Validar campos requeridos
    $this->validateRequiredFields($datos, ['nombre', 'tipo', 'capacidad_maxima']);
    
    // Validar que no existe otra área común con el mismo nombre en el condominio
    $areasExistentes = $this->areaComunModel->findAreasComunesByCondominio($condominioId);
    foreach ($areasExistentes as $area) {
        if (strtolower($area['nombre']) == strtolower($datos['nombre'])) {
            return $this->errorResponse('Ya existe un área común con este nombre en el condominio');
        }
    }
    
    // Agregar datos adicionales
    $datos['condominio_id'] = $condominioId;
    $datos['fecha_creacion'] = date('Y-m-d H:i:s');
    $datos['estado'] = 'activa';
    $datos['disponible'] = true;
    
    // Establecer horarios por defecto si no se proporcionan
    if (!isset($datos['horarios_acceso'])) {
        $datos['horarios_acceso'] = json_encode([
            'lunes' => ['inicio' => '06:00', 'fin' => '22:00'],
            'martes' => ['inicio' => '06:00', 'fin' => '22:00'],
            'miercoles' => ['inicio' => '06:00', 'fin' => '22:00'],
            'jueves' => ['inicio' => '06:00', 'fin' => '22:00'],
            'viernes' => ['inicio' => '06:00', 'fin' => '22:00'],
            'sabado' => ['inicio' => '08:00', 'fin' => '20:00'],
            'domingo' => ['inicio' => '08:00', 'fin' => '20:00']
        ]);
    }
    
    // Crear área común
    $areaId = $this->areaComunModel->createAreaComun($datos);
    
    // Log de actividad
    $this->logAdminActivity('area_comun_creada', [
        'admin_id' => $adminId,
        'condominio_id' => $condominioId,
        'area_id' => $areaId,
        'nombre' => $datos['nombre'],
        'tipo' => $datos['tipo']
    ]);
    
    return $this->successResponse(['id' => $areaId], 'Área común creada exitosamente');
}
```

### 2. **Obtener Áreas Comunes del Condominio**
```php
public function obtenerAreasComunesCondominio($adminId, $condominioId, $opciones = [])
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para ver áreas comunes de este condominio');
    }
    
    // Aplicar rate limiting
    $this->enforceRateLimit('areas_comunes_' . $adminId);
    
    // Obtener áreas comunes
    $areas = $this->areaComunModel->findAreasComunesByCondominio($condominioId);
    
    // Aplicar filtros si se proporcionan
    if (isset($opciones['tipo'])) {
        $areas = array_filter($areas, function($area) use ($opciones) {
            return $area['tipo'] == $opciones['tipo'];
        });
    }
    
    if (isset($opciones['disponibles_solamente']) && $opciones['disponibles_solamente']) {
        $areas = array_filter($areas, function($area) {
            return $area['disponible'] && $area['estado'] == 'activa';
        });
    }
    
    // Agregar información adicional de ocupación
    foreach ($areas as &$area) {
        $area['ocupacion_actual'] = $this->getOcupacionActual($area['id']);
        $area['proximas_reservas'] = $this->getProximasReservas($area['id'], 3);
    }
    
    return $this->successResponse($areas, 'Áreas comunes obtenidas exitosamente');
}
```

### 3. **Gestionar Horarios de Acceso**
```php
public function actualizarHorariosAcceso($adminId, $areaId, $horarios)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Obtener área común
    $area = $this->areaComunModel->findAreaComunById($areaId);
    if (!$area) {
        return $this->errorResponse('Área común no encontrada');
    }
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($area['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para modificar esta área común');
    }
    
    // Validar formato de horarios
    $diasValidos = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
    foreach ($horarios as $dia => $horario) {
        if (!in_array($dia, $diasValidos)) {
            return $this->errorResponse("Día inválido: $dia");
        }
        
        if (!isset($horario['inicio']) || !isset($horario['fin'])) {
            return $this->errorResponse("Horario incompleto para $dia");
        }
        
        // Validar formato de hora (HH:MM)
        if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $horario['inicio']) ||
            !preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $horario['fin'])) {
            return $this->errorResponse("Formato de hora inválido para $dia");
        }
        
        // Validar que hora de inicio sea menor que hora de fin
        if (strtotime($horario['inicio']) >= strtotime($horario['fin'])) {
            return $this->errorResponse("Hora de inicio debe ser menor que hora de fin para $dia");
        }
    }
    
    // Actualizar horarios
    $resultado = $this->areaComunModel->setHorariosAcceso($areaId, $horarios);
    
    // Log de actividad
    $this->logAdminActivity('horarios_actualizados', [
        'admin_id' => $adminId,
        'area_id' => $areaId,
        'condominio_id' => $area['condominio_id']
    ]);
    
    return $this->successResponse($resultado, 'Horarios de acceso actualizados exitosamente');
}
```

### 4. **Gestionar Reservas**
```php
public function crearReserva($adminId, $areaId, $datosReserva)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Obtener área común
    $area = $this->areaComunModel->findAreaComunById($areaId);
    if (!$area) {
        return $this->errorResponse('Área común no encontrada');
    }
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($area['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para crear reservas en esta área común');
    }
    
    // Validar campos requeridos
    $this->validateRequiredFields($datosReserva, ['persona_id', 'fecha_inicio', 'fecha_fin']);
    
    // Validar que el área esté disponible
    if (!$area['disponible'] || $area['estado'] != 'activa') {
        return $this->errorResponse('El área común no está disponible para reservas');
    }
    
    // Validar horarios de acceso
    if (!$this->areaComunModel->validateHorarioAcceso($areaId, $datosReserva['fecha_inicio']) ||
        !$this->areaComunModel->validateHorarioAcceso($areaId, $datosReserva['fecha_fin'])) {
        return $this->errorResponse('La reserva está fuera de los horarios de acceso permitidos');
    }
    
    // Validar disponibilidad en las fechas solicitadas
    if (!$this->areaComunModel->validateDisponibilidad($areaId, $datosReserva['fecha_inicio'], $datosReserva['fecha_fin'])) {
        return $this->errorResponse('El área común no está disponible en las fechas solicitadas');
    }
    
    // Validar capacidad si se especifica número de personas
    if (isset($datosReserva['num_personas'])) {
        if (!$this->areaComunModel->validateCapacidad($areaId, $datosReserva['num_personas'])) {
            return $this->errorResponse('El número de personas excede la capacidad máxima del área');
        }
    }
    
    // Agregar datos adicionales
    $datosReserva['area_comun_id'] = $areaId;
    $datosReserva['fecha_creacion'] = date('Y-m-d H:i:s');
    $datosReserva['estado'] = 'confirmada';
    
    // Crear reserva
    $reservaId = $this->areaComunModel->createReserva($datosReserva);
    
    // Log de actividad
    $this->logAdminActivity('reserva_creada', [
        'admin_id' => $adminId,
        'area_id' => $areaId,
        'reserva_id' => $reservaId,
        'persona_id' => $datosReserva['persona_id'],
        'fecha_inicio' => $datosReserva['fecha_inicio']
    ]);
    
    return $this->successResponse(['id' => $reservaId], 'Reserva creada exitosamente');
}

public function obtenerReservasArea($adminId, $areaId, $opciones = [])
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Obtener área común
    $area = $this->areaComunModel->findAreaComunById($areaId);
    if (!$area) {
        return $this->errorResponse('Área común no encontrada');
    }
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($area['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para ver reservas de esta área común');
    }
    
    // Aplicar rate limiting
    $this->enforceRateLimit('reservas_area_' . $adminId);
    
    // Obtener reservas
    $reservas = $this->areaComunModel->findReservasByArea($areaId);
    
    // Aplicar filtros si se proporcionan
    if (isset($opciones['estado'])) {
        $reservas = array_filter($reservas, function($reserva) use ($opciones) {
            return $reserva['estado'] == $opciones['estado'];
        });
    }
    
    if (isset($opciones['fecha_desde'])) {
        $reservas = array_filter($reservas, function($reserva) use ($opciones) {
            return $reserva['fecha_inicio'] >= $opciones['fecha_desde'];
        });
    }
    
    return $this->successResponse($reservas, 'Reservas obtenidas exitosamente');
}
```

### 5. **Gestionar Mantenimiento**
```php
public function programarMantenimiento($adminId, $areaId, $datosMantenimiento)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Obtener área común
    $area = $this->areaComunModel->findAreaComunById($areaId);
    if (!$area) {
        return $this->errorResponse('Área común no encontrada');
    }
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($area['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para programar mantenimiento en esta área común');
    }
    
    // Validar campos requeridos
    $this->validateRequiredFields($datosMantenimiento, ['tipo', 'descripcion', 'fecha_programada']);
    
    // Validar que la fecha de mantenimiento sea futura
    if (strtotime($datosMantenimiento['fecha_programada']) <= time()) {
        return $this->errorResponse('La fecha de mantenimiento debe ser futura');
    }
    
    // Agregar datos adicionales
    $datosMantenimiento['area_comun_id'] = $areaId;
    $datosMantenimiento['fecha_creacion'] = date('Y-m-d H:i:s');
    $datosMantenimiento['estado'] = 'programado';
    $datosMantenimiento['creado_por'] = $adminId;
    
    // Crear mantenimiento
    $mantenimientoId = $this->areaComunModel->createMantenimiento($datosMantenimiento);
    
    // Si el mantenimiento requiere cerrar el área, actualizarla
    if (isset($datosMantenimiento['cierra_area']) && $datosMantenimiento['cierra_area']) {
        $this->areaComunModel->updateAreaComun($areaId, ['disponible' => false]);
    }
    
    // Log de actividad
    $this->logAdminActivity('mantenimiento_programado', [
        'admin_id' => $adminId,
        'area_id' => $areaId,
        'mantenimiento_id' => $mantenimientoId,
        'tipo' => $datosMantenimiento['tipo'],
        'fecha_programada' => $datosMantenimiento['fecha_programada']
    ]);
    
    return $this->successResponse(['id' => $mantenimientoId], 'Mantenimiento programado exitosamente');
}
```

### 6. **Obtener Estadísticas**
```php
public function obtenerEstadisticasUso($adminId, $condominioId, $periodo = [])
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para ver estadísticas de este condominio');
    }
    
    // Aplicar rate limiting
    $this->enforceRateLimit('estadisticas_areas_' . $adminId);
    
    // Obtener áreas del condominio
    $areas = $this->areaComunModel->findAreasComunesByCondominio($condominioId);
    
    $estadisticas = [];
    foreach ($areas as $area) {
        $estadisticasArea = $this->areaComunModel->getEstadisticasUso($area['id'], $periodo);
        $estadisticas[$area['id']] = [
            'nombre' => $area['nombre'],
            'tipo' => $area['tipo'],
            'estadisticas' => $estadisticasArea
        ];
    }
    
    return $this->successResponse($estadisticas, 'Estadísticas obtenidas exitosamente');
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
public function areaPerteneceACondominio($areaId, $condominioId)
{
    $area = $this->areaComunModel->findAreaComunById($areaId);
    return $area && $area['condominio_id'] == $condominioId;
}

private function validarDisponibilidadHorarios($areaId, $fechaInicio, $fechaFin)
{
    // Validar que esté dentro de horarios de acceso
    return $this->areaComunModel->validateHorarioAcceso($areaId, $fechaInicio) &&
           $this->areaComunModel->validateHorarioAcceso($areaId, $fechaFin);
}

private function getOcupacionActual($areaId)
{
    $reservasActivas = $this->areaComunModel->findReservasByArea($areaId);
    $ahora = date('Y-m-d H:i:s');
    
    $ocupadas = array_filter($reservasActivas, function($reserva) use ($ahora) {
        return $reserva['fecha_inicio'] <= $ahora && 
               $reserva['fecha_fin'] >= $ahora && 
               $reserva['estado'] == 'confirmada';
    });
    
    return count($ocupadas);
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

// Coordinar con DispositivoService para control de acceso
$this->dispositivoService->configurarAccesoArea($areaId, $horarios);
```

### Proporciona para otros servicios:
```php
// Para AccesosService, DispositivoService
public function areaPerteneceACondominio($areaId, $condominioId);
public function validarAreaExiste($areaId);
public function validarAccesoPermitido($areaId, $datetime);
```

---

## 🚫 RESTRICCIONES IMPORTANTES

### Lo que NO debe hacer:
- ❌ **NO gestionar dispositivos de acceso directamente** (usar DispositivoService)
- ❌ **NO manejar control de acceso físico** (coordinar con DispositivoService)
- ❌ **NO gestionar personas** (usar PersonaService)

### Scope específico:
- ✅ **CRUD de áreas comunes**
- ✅ **Gestión de horarios y disponibilidad**
- ✅ **Sistema de reservas**
- ✅ **Programación de mantenimiento**
- ✅ **Estadísticas de uso**

---

## 📋 ESTRUCTURA DE RESPUESTAS

### Éxito
```php
return $this->successResponse([
    'area_comun' => $areaData,
    'mensaje' => 'Área común gestionada exitosamente'
]);
```

### Error de Disponibilidad
```php
return $this->errorResponse(
    'El área común no está disponible en las fechas solicitadas',
    400
);
```

---

## 🔍 LOGGING REQUERIDO

### Actividades a registrar:
- ✅ Creación/modificación de áreas comunes
- ✅ Cambios de horarios de acceso
- ✅ Creación/cancelación de reservas
- ✅ Programación de mantenimiento
- ✅ Consultas de estadísticas

---

## 📅 INFORMACIÓN DEL PROMPT
- **Fecha de creación:** 28 de Julio, 2025
- **Servicio:** AreaComunService.php
- **Posición en cascada:** Nivel 5 (Instalaciones)
- **Estado:** ✅ Listo para implementación

---

## 🎯 INSTRUCCIONES PARA COPILOT

Al generar código para AreaComunService.php:

1. **SIEMPRE heredar de BaseAdminService**
2. **USAR métodos de AreaComun.php**
3. **VALIDAR ownership del condominio en TODAS las operaciones**
4. **GESTIONAR horarios de acceso y disponibilidad**
5. **COORDINAR con DispositivoService para control físico**
6. **VALIDAR capacidades y restricciones**
7. **REGISTRAR logs de todas las actividades**
8. **PROPORCIONAR métodos de validación para otros servicios**
