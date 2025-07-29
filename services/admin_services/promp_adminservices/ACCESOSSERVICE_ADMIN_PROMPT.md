# ACCESOSSERVICE_ADMIN_PROMPT.md
## Prompt Especializado para AccesosService.php

### 🎯 PROPÓSITO DEL SERVICIO
Registro y monitoreo de accesos del sistema Cyberhole Condominios. Este servicio es el último eslabón en la cascada y debe poder acceder a información de empleados, visitantes, residentes, vehículos y dispositivos para un control completo de accesos.

---

## 🏗️ ARQUITECTURA Y HERENCIA

### Clase Base
```php
class AccesosService extends BaseAdminService
```

### Dependencias
- **Hereda de:** `BaseAdminService.php`
- **Modelos principales:** `Acceso.php`, `Persona.php`, `Empleado.php`
- **Posición en cascada:** Nivel 6 (Último eslabón - acceso a todos los servicios)
- **Servicios que puede usar:** EmpleadoService, PersonaService, PersonaCasaService, EngomadoService, DispositivoService

---

## 📚 MÉTODOS DEL MODELO ACCESO DISPONIBLES

### Métodos de Registro de Accesos
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `registrarAccesoResidente()` | array $data | int | Registrar acceso de residente |
| `registrarAccesoEmpleado()` | array $data | int | Registrar acceso de empleado |
| `registrarAccesoVisitante()` | array $data | int | Registrar acceso de visitante |

### Métodos de Registro de Salidas
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `registrarSalidaResidente()` | int $id | bool | Registrar salida de residente |
| `registrarSalidaEmpleado()` | int $id | bool | Registrar salida de empleado |
| `registrarSalidaVisitante()` | int $id | bool | Registrar salida de visitante |

### Métodos de Historial
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `historialResidente()` | int $id_persona, int $limite, int $offset | array | Historial de accesos de residente |
| `historialEmpleado()` | int $id_empleado, int $limite, int $offset | array | Historial de accesos de empleado |
| `historialVisitante()` | int $id_visitante | array | Historial de accesos de visitante |

### Métodos de Estadísticas
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `estadisticasPorCondominio()` | int $id_condominio, array $options | array | Estadísticas por condominio |

### Métodos Base Heredados
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `create()` | array $data | int | Crear registro de acceso |
| `findById()` | int $id | array | Buscar por ID |
| `update()` | int $id, array $data | bool | Actualizar registro |
| `delete()` | int $id | bool | Eliminar registro |
| `findAll()` | int $limit = 100 | array | Obtener todos los accesos |

---

## 🔧 FUNCIONES DE NEGOCIO REQUERIDAS

### 1. **Registrar Acceso de Residente**
```php
public function registrarAccesoResidente($adminId, $datos)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Validar campos requeridos
    $this->validateRequiredFields($datos, ['persona_id', 'condominio_id', 'tipo_acceso']);
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($datos['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para registrar accesos en este condominio');
    }
    
    // Validar que la persona existe y pertenece al condominio
    if (!$this->personaService->existePersona($datos['persona_id'])) {
        return $this->errorResponse('Persona no encontrada');
    }
    
    // Verificar que la persona vive en el condominio
    if (!$this->personaCasaService->personaViveEnCondominio($datos['persona_id'], $datos['condominio_id'])) {
        return $this->errorResponse('La persona no reside en este condominio');
    }
    
    // Registrar acceso
    $accesoId = $this->accesoModel->registrarAccesoResidente($datos);
    
    // Log de actividad
    $this->logAdminActivity('acceso_residente_registrado', [
        'admin_id' => $adminId,
        'condominio_id' => $datos['condominio_id'],
        'persona_id' => $datos['persona_id'],
        'acceso_id' => $accesoId
    ]);
    
    return $this->successResponse(['id' => $accesoId], 'Acceso de residente registrado exitosamente');
}
```

### 2. **Registrar Acceso de Empleado**
```php
public function registrarAccesoEmpleado($adminId, $datos)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Validar campos requeridos
    $this->validateRequiredFields($datos, ['empleado_id', 'condominio_id', 'tipo_acceso']);
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($datos['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para registrar accesos en este condominio');
    }
    
    // Validar que el empleado existe y pertenece al condominio
    if (!$this->empleadoService->empleadoPerteneceACondominio($datos['empleado_id'], $datos['condominio_id'])) {
        return $this->errorResponse('El empleado no pertenece a este condominio');
    }
    
    // Verificar que el empleado está activo
    $empleado = $this->empleadoService->obtenerEmpleado($datos['empleado_id']);
    if (!$empleado['activo']) {
        return $this->errorResponse('El empleado no está activo');
    }
    
    // Registrar acceso
    $accesoId = $this->accesoModel->registrarAccesoEmpleado($datos);
    
    // Log de actividad
    $this->logAdminActivity('acceso_empleado_registrado', [
        'admin_id' => $adminId,
        'condominio_id' => $datos['condominio_id'],
        'empleado_id' => $datos['empleado_id'],
        'acceso_id' => $accesoId
    ]);
    
    return $this->successResponse(['id' => $accesoId], 'Acceso de empleado registrado exitosamente');
}
```

### 3. **Registrar Acceso de Visitante**
```php
public function registrarAccesoVisitante($adminId, $datos)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Validar campos requeridos
    $this->validateRequiredFields($datos, ['nombre_visitante', 'condominio_id', 'casa_destino', 'tipo_acceso']);
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($datos['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para registrar accesos en este condominio');
    }
    
    // Validar que la casa destino existe y pertenece al condominio
    if (!$this->casaService->casaPerteneceACondominio($datos['casa_destino'], $datos['condominio_id'])) {
        return $this->errorResponse('La casa destino no pertenece a este condominio');
    }
    
    // Validar placa vehicular si se proporciona
    if (isset($datos['placa_vehiculo']) && !empty($datos['placa_vehiculo'])) {
        if (!$this->engomadoService->validarFormatoPlaca($datos['placa_vehiculo'])) {
            return $this->errorResponse('Formato de placa inválido');
        }
    }
    
    // Registrar acceso
    $accesoId = $this->accesoModel->registrarAccesoVisitante($datos);
    
    // Log de actividad
    $this->logAdminActivity('acceso_visitante_registrado', [
        'admin_id' => $adminId,
        'condominio_id' => $datos['condominio_id'],
        'casa_destino' => $datos['casa_destino'],
        'acceso_id' => $accesoId
    ]);
    
    return $this->successResponse(['id' => $accesoId], 'Acceso de visitante registrado exitosamente');
}
```

### 4. **Obtener Historial de Accesos**
```php
public function obtenerHistorialAccesos($adminId, $condominioId, $filtros = [])
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para ver accesos de este condominio');
    }
    
    // Aplicar rate limiting
    $this->enforceRateLimit('historial_accesos_' . $adminId);
    
    // Preparar opciones de filtrado
    $opciones = array_merge([
        'limite' => 100,
        'offset' => 0,
        'fecha_desde' => null,
        'fecha_hasta' => null,
        'tipo_acceso' => null,
        'tipo_persona' => null
    ], $filtros);
    
    // Obtener estadísticas con filtros
    $accesos = $this->accesoModel->estadisticasPorCondominio($condominioId, $opciones);
    
    return $this->successResponse($accesos, 'Historial de accesos obtenido exitosamente');
}
```

### 5. **Obtener Estadísticas de Accesos**
```php
public function obtenerEstadisticasAccesos($adminId, $condominioId, $periodo = 'mes')
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para ver estadísticas de este condominio');
    }
    
    // Aplicar rate limiting
    $this->enforceRateLimit('estadisticas_accesos_' . $adminId);
    
    // Preparar opciones según el período
    $opciones = $this->prepararOpcionesPeriodo($periodo);
    
    // Obtener estadísticas
    $estadisticas = $this->accesoModel->estadisticasPorCondominio($condominioId, $opciones);
    
    // Procesar estadísticas para dashboard
    $estadisticasProcesadas = $this->procesarEstadisticas($estadisticas);
    
    return $this->successResponse($estadisticasProcesadas, 'Estadísticas obtenidas exitosamente');
}
```

### 6. **Registrar Salida**
```php
public function registrarSalida($adminId, $accesoId, $tipoPersona)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Obtener información del acceso
    $acceso = $this->accesoModel->findById($accesoId);
    if (!$acceso) {
        return $this->errorResponse('Acceso no encontrado');
    }
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($acceso['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para registrar salidas en este condominio');
    }
    
    // Registrar salida según el tipo
    $resultado = false;
    switch ($tipoPersona) {
        case 'residente':
            $resultado = $this->accesoModel->registrarSalidaResidente($accesoId);
            break;
        case 'empleado':
            $resultado = $this->accesoModel->registrarSalidaEmpleado($accesoId);
            break;
        case 'visitante':
            $resultado = $this->accesoModel->registrarSalidaVisitante($accesoId);
            break;
        default:
            return $this->errorResponse('Tipo de persona inválido');
    }
    
    // Log de actividad
    $this->logAdminActivity('salida_registrada', [
        'admin_id' => $adminId,
        'acceso_id' => $accesoId,
        'tipo_persona' => $tipoPersona
    ]);
    
    return $this->successResponse($resultado, 'Salida registrada exitosamente');
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
private function validarDatosAcceso($datos, $tipoAcceso)
{
    $required = ['condominio_id', 'tipo_acceso'];
    
    switch ($tipoAcceso) {
        case 'residente':
            $required[] = 'persona_id';
            break;
        case 'empleado':
            $required[] = 'empleado_id';
            break;
        case 'visitante':
            $required = array_merge($required, ['nombre_visitante', 'casa_destino']);
            break;
    }
    
    return $this->validateRequiredFields($datos, $required);
}
```

---

## 🔄 INTEGRACIÓN CON OTROS SERVICIOS

### Uso de servicios en cascada:
```php
// Validar que la persona existe
if (!$this->personaService->existePersona($personaId)) {
    return $this->errorResponse("Persona no encontrada");
}

// Validar empleado activo
if (!$this->empleadoService->empleadoActivo($empleadoId)) {
    return $this->errorResponse("Empleado no activo");
}

// Validar placa de vehículo
if (!$this->engomadoService->validarFormatoPlaca($placa)) {
    return $this->errorResponse("Formato de placa inválido");
}

// Validar dispositivo de acceso
if (!$this->dispositivoService->dispositivoActivo($dispositivoId)) {
    return $this->errorResponse("Dispositivo no activo");
}
```

---

## 🚫 RESTRICCIONES IMPORTANTES

### Verificaciones obligatorias:
- ✅ **Validar ownership del condominio**
- ✅ **Verificar pertenencia al condominio**
- ✅ **Validar estados activos**
- ✅ **No permitir accesos de personas externas**

### Lo que NO debe hacer:
- ❌ **NO registrar accesos sin validar ownership**
- ❌ **NO permitir accesos de personas que no pertenezcan al condominio**
- ❌ **NO omitir validaciones de seguridad**

---

## 📋 ESTRUCTURA DE RESPUESTAS

### Éxito
```php
return $this->successResponse([
    'acceso' => $accesoData,
    'mensaje' => 'Acceso registrado exitosamente'
]);
```

### Error de Permisos
```php
return $this->errorResponse(
    'No tienes permisos para registrar accesos en este condominio',
    403
);
```

---

## 🔍 LOGGING REQUERIDO

### Actividades a registrar:
- ✅ Todos los accesos registrados
- ✅ Todas las salidas registradas
- ✅ Consultas de historial
- ✅ Intentos de acceso fallidos
- ✅ Validaciones de permisos

---

## 📅 INFORMACIÓN DEL PROMPT
- **Fecha de creación:** 28 de Julio, 2025
- **Servicio:** AccesosService.php
- **Posición en cascada:** Nivel 6 (Último eslabón)
- **Estado:** ✅ Listo para implementación

---

## 🎯 INSTRUCCIONES PARA COPILOT

Al generar código para AccesosService.php:

1. **SIEMPRE heredar de BaseAdminService**
2. **USAR métodos de Acceso.php, Persona.php, Empleado.php**
3. **VALIDAR ownership en TODAS las operaciones**
4. **REUTILIZAR validaciones de otros servicios**
5. **REGISTRAR logs de todas las actividades**
6. **VERIFICAR pertenencia al condominio**
7. **NO permitir accesos de personas externas**
8. **USAR servicios en cascada para validaciones**
