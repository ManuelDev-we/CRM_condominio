# EMPLEADOSERVICE_ADMIN_PROMPT.md
## Prompt Especializado para EmpleadoService.php

### 🎯 PROPÓSITO DEL SERVICIO
Gestión de empleados del condominio con encriptación. Este servicio maneja el CRUD de empleados, asignación de tareas, control de acceso físico y estado laboral, con encriptación ya manejada en la primera capa.

---

## 🏗️ ARQUITECTURA Y HERENCIA

### Clase Base
```php
class EmpleadoService extends BaseAdminService
```

### Dependencias
- **Hereda de:** `BaseAdminService.php`
- **Modelo principal:** `Empleado.php`
- **Posición en cascada:** Nivel 3 (Submódulo ligado al condominio)
- **Servicios dependientes:** CasaService (para relaciones)

---

## 📚 MÉTODOS DEL MODELO EMPLEADO DISPONIBLES

### Métodos de Gestión de Empleados
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `findEmpleadosByCondominio()` | int $id_condominio, array $options | array | Buscar empleados por condominio |
| `findByAcceso()` | string $id_acceso | array | Buscar por ID de acceso |
| `toggleActivo()` | int $id, bool $activo | bool | Activar/desactivar empleado |

### Métodos de Gestión de Tareas
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `createTarea()` | array $data | int | Crear tarea |
| `findTareasByTrabajador()` | int $id_trabajador | array | Buscar tareas por trabajador |
| `findTareasByCondominio()` | int $id_condominio | array | Buscar tareas por condominio |

### Métodos de Validación
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `validatePuestoValue()` | string $puesto | bool | Valida valor de puesto |
| `validateCondominioExists()` | int $id_condominio | bool | Valida existencia de condominio |
| `validateEmpleadoExists()` | int $id_empleado | bool | Valida existencia de empleado |
| `validateIdAccesoUnique()` | string $id_acceso, ?int $exclude_id | bool | Valida ID de acceso único |

### Métodos de Encriptación
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `decryptEmployeeData()` | array $data | array | Desencripta datos de empleado |
| `decryptTaskData()` | array $data | array | Desencripta datos de tarea |

### Métodos Base Heredados
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `create()` | array $data | int | Crear empleado |
| `findById()` | int $id | array | Buscar por ID |
| `update()` | int $id, array $data | bool | Actualizar empleado |
| `delete()` | int $id | bool | Eliminar empleado |
| `findAll()` | int $limit = 100 | array | Obtener todos los empleados |

---

## 🔧 FUNCIONES DE NEGOCIO REQUERIDAS

### 1. **Crear Empleado**
```php
public function crearEmpleado($adminId, $condominioId, $datos)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para gestionar empleados en este condominio');
    }
    
    // Validar campos requeridos
    $this->validateRequiredFields($datos, ['nombre', 'puesto', 'telefono', 'id_acceso']);
    
    // Validar que el condominio existe
    if (!$this->empleadoModel->validateCondominioExists($condominioId)) {
        return $this->errorResponse('Condominio no encontrado');
    }
    
    // Validar valor del puesto
    if (!$this->empleadoModel->validatePuestoValue($datos['puesto'])) {
        return $this->errorResponse('Puesto inválido');
    }
    
    // Validar que el ID de acceso sea único
    if (!$this->empleadoModel->validateIdAccesoUnique($datos['id_acceso'])) {
        return $this->errorResponse('El ID de acceso ya está en uso');
    }
    
    // Agregar condominio_id a los datos
    $datos['condominio_id'] = $condominioId;
    $datos['activo'] = true; // Por defecto activo
    
    // Crear empleado (la encriptación se maneja en primera capa)
    $empleadoId = $this->empleadoModel->create($datos);
    
    // Log de actividad
    $this->logAdminActivity('empleado_creado', [
        'admin_id' => $adminId,
        'condominio_id' => $condominioId,
        'empleado_id' => $empleadoId,
        'puesto' => $datos['puesto']
    ]);
    
    return $this->successResponse(['id' => $empleadoId], 'Empleado creado exitosamente');
}
```

### 2. **Obtener Empleados del Condominio**
```php
public function obtenerEmpleadosCondominio($adminId, $condominioId, $opciones = [])
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para ver empleados de este condominio');
    }
    
    // Aplicar rate limiting
    $this->enforceRateLimit('empleados_' . $adminId);
    
    // Preparar opciones de filtrado
    $opcionesFiltro = array_merge([
        'activos_solamente' => false,
        'puesto' => null,
        'limite' => 100,
        'offset' => 0
    ], $opciones);
    
    // Obtener empleados
    $empleados = $this->empleadoModel->findEmpleadosByCondominio($condominioId, $opcionesFiltro);
    
    // Desencriptar datos para visualización
    $empleadosDesencriptados = array_map(function($empleado) {
        return $this->empleadoModel->decryptEmployeeData($empleado);
    }, $empleados);
    
    return $this->successResponse($empleadosDesencriptados, 'Empleados obtenidos exitosamente');
}
```

### 3. **Actualizar Empleado**
```php
public function actualizarEmpleado($adminId, $empleadoId, $datos)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Obtener empleado actual
    $empleado = $this->empleadoModel->findById($empleadoId);
    if (!$empleado) {
        return $this->errorResponse('Empleado no encontrado');
    }
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($empleado['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para editar este empleado');
    }
    
    // Validar puesto si se proporciona
    if (isset($datos['puesto']) && !$this->empleadoModel->validatePuestoValue($datos['puesto'])) {
        return $this->errorResponse('Puesto inválido');
    }
    
    // Validar ID de acceso único si se cambia
    if (isset($datos['id_acceso']) && $datos['id_acceso'] !== $empleado['id_acceso']) {
        if (!$this->empleadoModel->validateIdAccesoUnique($datos['id_acceso'], $empleadoId)) {
            return $this->errorResponse('El ID de acceso ya está en uso');
        }
    }
    
    // Actualizar empleado (la encriptación se maneja en primera capa)
    $resultado = $this->empleadoModel->update($empleadoId, $datos);
    
    // Log de actividad
    $this->logAdminActivity('empleado_actualizado', [
        'admin_id' => $adminId,
        'empleado_id' => $empleadoId,
        'condominio_id' => $empleado['condominio_id'],
        'cambios' => array_keys($datos)
    ]);
    
    return $this->successResponse($resultado, 'Empleado actualizado exitosamente');
}
```

### 4. **Activar/Desactivar Empleado**
```php
public function cambiarEstadoEmpleado($adminId, $empleadoId, $activo)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Obtener empleado actual
    $empleado = $this->empleadoModel->findById($empleadoId);
    if (!$empleado) {
        return $this->errorResponse('Empleado no encontrado');
    }
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($empleado['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para cambiar el estado de este empleado');
    }
    
    // Cambiar estado
    $resultado = $this->empleadoModel->toggleActivo($empleadoId, $activo);
    
    // Log de actividad
    $this->logAdminActivity('empleado_estado_cambiado', [
        'admin_id' => $adminId,
        'empleado_id' => $empleadoId,
        'condominio_id' => $empleado['condominio_id'],
        'nuevo_estado' => $activo ? 'activo' : 'inactivo'
    ]);
    
    $mensaje = $activo ? 'Empleado activado exitosamente' : 'Empleado desactivado exitosamente';
    return $this->successResponse($resultado, $mensaje);
}
```

### 5. **Crear Tarea**
```php
public function crearTarea($adminId, $condominioId, $datos)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para crear tareas en este condominio');
    }
    
    // Validar campos requeridos
    $this->validateRequiredFields($datos, ['titulo', 'descripcion', 'empleado_id', 'fecha_limite']);
    
    // Validar que el empleado existe y pertenece al condominio
    if (!$this->empleadoModel->validateEmpleadoExists($datos['empleado_id'])) {
        return $this->errorResponse('Empleado no encontrado');
    }
    
    $empleado = $this->empleadoModel->findById($datos['empleado_id']);
    if ($empleado['condominio_id'] != $condominioId) {
        return $this->errorResponse('El empleado no pertenece a este condominio');
    }
    
    // Agregar datos adicionales
    $datos['condominio_id'] = $condominioId;
    $datos['estado'] = 'pendiente';
    $datos['fecha_creacion'] = date('Y-m-d H:i:s');
    
    // Crear tarea
    $tareaId = $this->empleadoModel->createTarea($datos);
    
    // Log de actividad
    $this->logAdminActivity('tarea_creada', [
        'admin_id' => $adminId,
        'condominio_id' => $condominioId,
        'tarea_id' => $tareaId,
        'empleado_id' => $datos['empleado_id']
    ]);
    
    return $this->successResponse(['id' => $tareaId], 'Tarea creada exitosamente');
}
```

### 6. **Obtener Tareas**
```php
public function obtenerTareasCondominio($adminId, $condominioId, $opciones = [])
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para ver tareas de este condominio');
    }
    
    // Aplicar rate limiting
    $this->enforceRateLimit('tareas_' . $adminId);
    
    // Obtener tareas
    $tareas = $this->empleadoModel->findTareasByCondominio($condominioId);
    
    // Desencriptar datos de tareas
    $tareasDesencriptadas = array_map(function($tarea) {
        return $this->empleadoModel->decryptTaskData($tarea);
    }, $tareas);
    
    // Filtrar por empleado si se especifica
    if (isset($opciones['empleado_id'])) {
        $tareasDesencriptadas = array_filter($tareasDesencriptadas, function($tarea) use ($opciones) {
            return $tarea['empleado_id'] == $opciones['empleado_id'];
        });
    }
    
    return $this->successResponse($tareasDesencriptadas, 'Tareas obtenidas exitosamente');
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
private function validarDatosEmpleado($datos)
{
    $required = ['nombre', 'puesto', 'telefono', 'id_acceso'];
    
    if (!$this->validateRequiredFields($datos, $required)) {
        return false;
    }
    
    if (!$this->empleadoModel->validatePuestoValue($datos['puesto'])) {
        return false;
    }
    
    return true;
}

public function empleadoPerteneceACondominio($empleadoId, $condominioId)
{
    $empleado = $this->empleadoModel->findById($empleadoId);
    return $empleado && $empleado['condominio_id'] == $condominioId;
}

public function empleadoActivo($empleadoId)
{
    $empleado = $this->empleadoModel->findById($empleadoId);
    return $empleado && $empleado['activo'];
}
```

---

## 🔄 INTEGRACIÓN CON OTROS SERVICIOS

### Validaciones que proporciona:
```php
// Para AccesosService
public function empleadoPerteneceACondominio($empleadoId, $condominioId);
public function empleadoActivo($empleadoId);

// Para otros servicios
public function obtenerEmpleado($empleadoId);
```

---

## 🚫 RESTRICCIONES IMPORTANTES

### Verificaciones obligatorias:
- ✅ **Validar ownership del condominio**
- ✅ **Solo empleados del condominio del admin**
- ✅ **Verificar unicidad de ID de acceso**
- ✅ **Validar puestos válidos**

### Lo que NO debe hacer:
- ❌ **NO gestionar empleados de otros condominios**
- ❌ **NO omitir validaciones de ownership**
- ❌ **NO duplicar IDs de acceso**

---

## 📋 ESTRUCTURA DE RESPUESTAS

### Éxito
```php
return $this->successResponse([
    'empleado' => $empleadoData,
    'mensaje' => 'Empleado gestionado exitosamente'
]);
```

### Error de Ownership
```php
return $this->errorResponse(
    'No tienes permisos para gestionar empleados en este condominio',
    403
);
```

---

## 🔍 LOGGING REQUERIDO

### Actividades a registrar:
- ✅ Creación de empleados
- ✅ Modificación de empleados
- ✅ Cambios de estado
- ✅ Creación de tareas
- ✅ Asignación de tareas

---

## 📅 INFORMACIÓN DEL PROMPT
- **Fecha de creación:** 28 de Julio, 2025
- **Servicio:** EmpleadoService.php
- **Posición en cascada:** Nivel 3 (Submódulo de condominio)
- **Estado:** ✅ Listo para implementación

---

## 🎯 INSTRUCCIONES PARA COPILOT

Al generar código para EmpleadoService.php:

1. **SIEMPRE heredar de BaseAdminService**
2. **USAR métodos del modelo Empleado.php**
3. **VALIDAR ownership del condominio en TODAS las operaciones**
4. **USAR encriptación ya manejada en primera capa**
5. **REGISTRAR logs de todas las actividades**
6. **PROPORCIONAR métodos de validación para AccesosService**
7. **MANTENER scope limitado a empleados del condominio**
8. **VALIDAR IDs de acceso únicos**
