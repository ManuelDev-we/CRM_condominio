# CONDOMINIOSERVICE_ADMIN_PROMPT.md
## Prompt Especializado para CondominioService.php

### 🎯 PROPÓSITO DEL SERVICIO
Gestionar condominios del administrador autenticado. Este servicio es el punto de entrada de administración lógica y controla la relación entre administradores y condominios, validando ownership en todas las operaciones.

---

## 🏗️ ARQUITECTURA Y HERENCIA

### Clase Base
```php
class CondominioService extends BaseAdminService
```

### Dependencias
- **Hereda de:** `BaseAdminService.php`
- **Modelos principales:** `Condominio.php`, `Admin.php`
- **Posición en cascada:** Nivel 2 (Servicio principal de administración)
- **Servicios dependientes:** CalleService, AreaComunService, BlogService, EmpleadoService

---

## 📚 MÉTODOS DEL MODELO CONDOMINIO DISPONIBLES

### Métodos de Gestión de Condominios
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `createCondominio()` | array $data | int | Crear condominio |
| `updateCondominio()` | int $id, array $data | bool | Actualizar condominio |
| `deleteCondominio()` | int $id | bool | Eliminar condominio |
| `findCondominioById()` | int $id | array | Buscar condominio por ID |
| `findCondominiosByAdmin()` | int $adminId | array | Buscar condominios por admin |
| `getAllCondominios()` | int $limit = 100 | array | Obtener todos los condominios |

### Métodos de Relaciones Admin-Condominio
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `assignAdminToCondominio()` | int $adminId, int $condominioId | bool | Asignar admin a condominio |
| `removeAdminFromCondominio()` | int $adminId, int $condominioId | bool | Remover admin de condominio |
| `getAdminsByCondominio()` | int $condominioId | array | Obtener admins por condominio |
| `getCondominiosByAdmin()` | int $adminId | array | Obtener condominios por admin |

### Métodos de Validación
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `validateAdminExists()` | int $adminId | bool | Valida existencia de admin |
| `validateCondominioExists()` | int $condominioId | bool | Valida existencia de condominio |
| `existsCondominioByNombre()` | string $nombre, ?int $excludeId | bool | Verifica nombre único |
| `existsAdminCondRelation()` | int $adminId, int $condominioId | bool | Verifica relación admin-condominio |

### Métodos de Búsqueda y Estadísticas
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `findCondominiosByNombre()` | string $nombre | array | Buscar por nombre |
| `getAdminCondominioStats()` | - | array | Obtener estadísticas |
| `getModelInfo()` | - | void | Información del modelo |

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

### 1. **Crear Condominio**
```php
public function crearCondominio($adminId, $datos)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Validar campos requeridos
    $this->validateRequiredFields($datos, ['nombre', 'direccion', 'telefono']);
    
    // Validar que el nombre sea único
    if ($this->condominioModel->existsCondominioByNombre($datos['nombre'])) {
        return $this->errorResponse('Ya existe un condominio con este nombre');
    }
    
    // Validar que el admin existe
    if (!$this->condominioModel->validateAdminExists($adminId)) {
        return $this->errorResponse('Administrador no encontrado');
    }
    
    // Crear condominio
    $condominioId = $this->condominioModel->createCondominio($datos);
    
    // Asignar administrador al condominio
    $this->condominioModel->assignAdminToCondominio($adminId, $condominioId);
    
    // Log de actividad
    $this->logAdminActivity('condominio_creado', [
        'admin_id' => $adminId,
        'condominio_id' => $condominioId,
        'nombre' => $datos['nombre']
    ]);
    
    return $this->successResponse(['id' => $condominioId], 'Condominio creado exitosamente');
}
```

### 2. **Validar Ownership**
```php
public function validarOwnership($condominioId, $adminId)
{
    // Verificar que el condominio existe
    if (!$this->condominioModel->validateCondominioExists($condominioId)) {
        return false;
    }
    
    // Verificar que el admin tiene acceso al condominio
    return $this->condominioModel->existsAdminCondRelation($adminId, $condominioId);
}
```

### 3. **Obtener Condominios del Admin**
```php
public function obtenerCondominiosAdmin($adminId, $opciones = [])
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Aplicar rate limiting
    $this->enforceRateLimit('condominios_' . $adminId);
    
    // Validar que el admin existe
    if (!$this->condominioModel->validateAdminExists($adminId)) {
        return $this->errorResponse('Administrador no encontrado');
    }
    
    // Obtener condominios
    $condominios = $this->condominioModel->findCondominiosByAdmin($adminId);
    
    // Aplicar filtros si se proporcionan
    if (isset($opciones['buscar'])) {
        $condominios = array_filter($condominios, function($condominio) use ($opciones) {
            return stripos($condominio['nombre'], $opciones['buscar']) !== false;
        });
    }
    
    return $this->successResponse($condominios, 'Condominios obtenidos exitosamente');
}
```

### 4. **Actualizar Condominio**
```php
public function actualizarCondominio($condominioId, $adminId, $datos)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Validar ownership
    if (!$this->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para editar este condominio');
    }
    
    // Validar campos requeridos
    $this->validateRequiredFields($datos, ['nombre']);
    
    // Validar nombre único (excluyendo el actual)
    if ($this->condominioModel->existsCondominioByNombre($datos['nombre'], $condominioId)) {
        return $this->errorResponse('Ya existe otro condominio con este nombre');
    }
    
    // Actualizar condominio
    $resultado = $this->condominioModel->updateCondominio($condominioId, $datos);
    
    // Log de actividad
    $this->logAdminActivity('condominio_actualizado', [
        'admin_id' => $adminId,
        'condominio_id' => $condominioId,
        'cambios' => array_keys($datos)
    ]);
    
    return $this->successResponse($resultado, 'Condominio actualizado exitosamente');
}
```

### 5. **Eliminar Condominio**
```php
public function eliminarCondominio($condominioId, $adminId)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Validar ownership
    if (!$this->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para eliminar este condominio');
    }
    
    // Verificar que no tenga dependencias (calles, casas, etc.)
    if ($this->tieneDependencias($condominioId)) {
        return $this->errorResponse('No se puede eliminar el condominio porque tiene calles y casas asociadas');
    }
    
    // Eliminar relaciones admin-condominio
    $this->condominioModel->removeAdminFromCondominio($adminId, $condominioId);
    
    // Eliminar condominio
    $resultado = $this->condominioModel->deleteCondominio($condominioId);
    
    // Log de actividad
    $this->logAdminActivity('condominio_eliminado', [
        'admin_id' => $adminId,
        'condominio_id' => $condominioId
    ]);
    
    return $this->successResponse($resultado, 'Condominio eliminado exitosamente');
}
```

### 6. **Gestionar Administradores del Condominio**
```php
public function asignarAdministrador($condominioId, $adminId, $nuevoAdminId)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Validar ownership del admin actual
    if (!$this->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para gestionar este condominio');
    }
    
    // Validar que el nuevo admin existe
    if (!$this->condominioModel->validateAdminExists($nuevoAdminId)) {
        return $this->errorResponse('El administrador a asignar no existe');
    }
    
    // Verificar que no esté ya asignado
    if ($this->condominioModel->existsAdminCondRelation($nuevoAdminId, $condominioId)) {
        return $this->errorResponse('El administrador ya está asignado a este condominio');
    }
    
    // Asignar administrador
    $resultado = $this->condominioModel->assignAdminToCondominio($nuevoAdminId, $condominioId);
    
    // Log de actividad
    $this->logAdminActivity('admin_asignado', [
        'admin_id' => $adminId,
        'condominio_id' => $condominioId,
        'nuevo_admin_id' => $nuevoAdminId
    ]);
    
    return $this->successResponse($resultado, 'Administrador asignado exitosamente');
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

private function checkOwnershipCondominio($adminId, $condominioId)
{
    if (!$this->validarOwnership($condominioId, $adminId)) {
        throw new ForbiddenException('No tienes permisos para acceder a este condominio');
    }
}
```

### Validaciones Específicas
```php
private function tieneDependencias($condominioId)
{
    // Verificar si tiene calles asociadas
    // Verificar si tiene empleados asociados
    // Verificar si tiene áreas comunes asociadas
    // Etc.
    return false; // Implementar lógica específica
}
```

---

## 🔄 INTEGRACIÓN CON SERVICIOS DEPENDIENTES

### Uso en otros servicios:
```php
// En CalleService.php
if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
    return $this->errorResponse("No tienes permisos para gestionar este condominio");
}

// En BlogService.php
if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
    return $this->errorResponse("No tienes permisos para gestionar este condominio");
}
```

---

## 🚫 RESTRICCIONES IMPORTANTES

### Lo que NO debe hacer este servicio:
- ❌ **NO gestionar calles directamente** (usar CalleService)
- ❌ **NO gestionar casas directamente** (usar CasaService)
- ❌ **NO gestionar empleados directamente** (usar EmpleadoService)
- ❌ **NO registrar accesos** (usar AccesosService)

### Scope específico:
- ✅ **Gestión de condominios únicamente**
- ✅ **Relaciones admin-condominio**
- ✅ **Validación de ownership**
- ✅ **Configuraciones del condominio**

---

## 📋 ESTRUCTURA DE RESPUESTAS

### Éxito
```php
return $this->successResponse([
    'condominio' => $condominioData,
    'mensaje' => 'Operación completada exitosamente'
]);
```

### Error de Ownership
```php
return $this->errorResponse(
    'No tienes permisos para acceder a este condominio',
    403
);
```

---

## 🔍 LOGGING REQUERIDO

### Actividades a registrar:
- ✅ Creación de condominios
- ✅ Modificaciones de condominios
- ✅ Eliminación de condominios
- ✅ Asignación de administradores
- ✅ Validaciones de ownership

### Formato de log:
```php
$this->logAdminActivity('accion', [
    'admin_id' => $adminId,
    'condominio_id' => $condominioId,
    'detalles' => $detalles
]);
```

---

## 📅 INFORMACIÓN DEL PROMPT
- **Fecha de creación:** 28 de Julio, 2025
- **Servicio:** CondominioService.php
- **Posición en cascada:** Nivel 2 (Servicio principal de administración)
- **Estado:** ✅ Listo para implementación

---

## 🎯 INSTRUCCIONES PARA COPILOT

Al generar código para CondominioService.php:

1. **SIEMPRE heredar de BaseAdminService**
2. **USAR métodos de Condominio.php y Admin.php**
3. **VALIDAR ownership en TODAS las operaciones**
4. **APLICAR todas las validaciones de seguridad**
5. **REGISTRAR logs de todas las actividades**
6. **MANTENER como punto de entrada de administración lógica**
7. **PROPORCIONAR métodos de validación para otros servicios**
8. **NO duplicar funcionalidades de servicios dependientes**
