# CASASERVICE_ADMIN_PROMPT.md
## Prompt Especializado para CasaService.php

### 🎯 PROPÓSITO DEL SERVICIO
Administrar casas dentro de un condominio. Este servicio maneja CRUD de casas, asignación de claves de acceso, validación de ubicación y calle. No gestiona personas ni relaciones directamente (eso lo hace PersonaCasaService.php).

---

## 🏗️ ARQUITECTURA Y HERENCIA

### Clase Base
```php
class CasaService extends BaseAdminService
```

### Dependencias
- **Hereda de:** `BaseAdminService.php`
- **Modelos principales:** `Casa.php`, `Calle.php`
- **Posición en cascada:** Nivel 4 (Propiedades)
- **Servicios dependientes:** PersonaCasaService
- **Requiere validaciones de:** CondominioService, CalleService

---

## 📚 MÉTODOS DEL MODELO CASA DISPONIBLES

### Métodos de Gestión de Casas
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `createCasa()` | array $data | int | Crear casa |
| `findCasaById()` | int $id | array | Buscar casa por ID |
| `findCasasByCalleId()` | int $calleId | array | Buscar casas por calle |
| `findCasasByCondominioId()` | int $condominioId | array | Buscar casas por condominio |
| `updateCasa()` | int $id, array $data | bool | Actualizar casa |
| `deleteCasa()` | int $id | bool | Eliminar casa |

### Métodos de Claves de Registro
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `createClaveRegistro()` | array $data | bool | Crear clave de registro |
| `findClaveRegistro()` | string $codigo | array | Buscar clave de registro |
| `markClaveAsUsed()` | string $codigo | bool | Marcar clave como usada |
| `getClavesByCasa()` | int $casaId | array | Obtener claves por casa |
| `deleteClaveRegistro()` | string $codigo | bool | Eliminar clave de registro |
| `limpiarClavesExpiradas()` | int $diasExpiracion = 30 | int | Limpiar claves expiradas |

### Métodos de Relaciones Persona-Casa
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `assignPersonaToCasa()` | int $personaId, int $casaId | bool | Asignar persona a casa |
| `removePersonaFromCasa()` | int $personaId, int $casaId | bool | Remover persona de casa |
| `getPersonasByCasa()` | int $casaId | array | Obtener personas por casa |
| `getCasasByPersona()` | int $personaId | array | Obtener casas por persona |
| `isPersonaAssignedToCasa()` | int $personaId, int $casaId | bool | Verifica asignación persona-casa |

### Métodos de Validación
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `validateCondominioExists()` | int $condominioId | bool | Valida existencia de condominio |
| `validateCalleExists()` | int $calleId | bool | Valida existencia de calle |
| `validateCasaExists()` | int $casaId | bool | Valida existencia de casa |
| `validatePersonaExists()` | int $personaId | bool | Valida existencia de persona |
| `validateCalleInCondominio()` | int $calleId, int $condominioId | bool | Valida calle en condominio |

### Métodos de Estadísticas y Reportes
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `getEstadisticasByCondominio()` | int $condominioId | array | Estadísticas por condominio |
| `getReporteCompleto()` | int $casaId | array | Reporte completo de casa |

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

### 1. **Crear Casa**
```php
public function crearCasa($adminId, $condominioId, $datos)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para crear casas en este condominio');
    }
    
    // Validar campos requeridos
    $this->validateRequiredFields($datos, ['numero_casa', 'calle_id']);
    
    // Validar que la calle existe y pertenece al condominio
    if (!$this->casaModel->validateCalleExists($datos['calle_id'])) {
        return $this->errorResponse('Calle no encontrada');
    }
    
    if (!$this->casaModel->validateCalleInCondominio($datos['calle_id'], $condominioId)) {
        return $this->errorResponse('La calle no pertenece a este condominio');
    }
    
    // Verificar que no existe otra casa con el mismo número en la calle
    $casasExistentes = $this->casaModel->findCasasByCalleId($datos['calle_id']);
    foreach ($casasExistentes as $casa) {
        if ($casa['numero_casa'] == $datos['numero_casa']) {
            return $this->errorResponse('Ya existe una casa con este número en la calle');
        }
    }
    
    // Agregar datos adicionales
    $datos['condominio_id'] = $condominioId;
    $datos['fecha_creacion'] = date('Y-m-d H:i:s');
    $datos['activa'] = true;
    
    // Crear casa
    $casaId = $this->casaModel->createCasa($datos);
    
    // Log de actividad
    $this->logAdminActivity('casa_creada', [
        'admin_id' => $adminId,
        'condominio_id' => $condominioId,
        'casa_id' => $casaId,
        'numero_casa' => $datos['numero_casa'],
        'calle_id' => $datos['calle_id']
    ]);
    
    return $this->successResponse(['id' => $casaId], 'Casa creada exitosamente');
}
```

### 2. **Obtener Casas del Condominio**
```php
public function obtenerCasasCondominio($adminId, $condominioId, $opciones = [])
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para ver casas de este condominio');
    }
    
    // Aplicar rate limiting
    $this->enforceRateLimit('casas_' . $adminId);
    
    // Obtener casas
    $casas = $this->casaModel->findCasasByCondominioId($condominioId);
    
    // Aplicar filtros si se proporcionan
    if (isset($opciones['calle_id'])) {
        $casas = array_filter($casas, function($casa) use ($opciones) {
            return $casa['calle_id'] == $opciones['calle_id'];
        });
    }
    
    if (isset($opciones['activas_solamente']) && $opciones['activas_solamente']) {
        $casas = array_filter($casas, function($casa) {
            return $casa['activa'];
        });
    }
    
    return $this->successResponse($casas, 'Casas obtenidas exitosamente');
}
```

### 3. **Actualizar Casa**
```php
public function actualizarCasa($adminId, $casaId, $datos)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Obtener casa actual
    $casa = $this->casaModel->findCasaById($casaId);
    if (!$casa) {
        return $this->errorResponse('Casa no encontrada');
    }
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($casa['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para editar esta casa');
    }
    
    // Validar calle si se cambia
    if (isset($datos['calle_id']) && $datos['calle_id'] != $casa['calle_id']) {
        if (!$this->casaModel->validateCalleExists($datos['calle_id'])) {
            return $this->errorResponse('Calle no encontrada');
        }
        
        if (!$this->casaModel->validateCalleInCondominio($datos['calle_id'], $casa['condominio_id'])) {
            return $this->errorResponse('La calle no pertenece a este condominio');
        }
    }
    
    // Validar número de casa único en la calle
    if (isset($datos['numero_casa']) && isset($datos['calle_id'])) {
        $calleId = $datos['calle_id'] ?? $casa['calle_id'];
        $casasExistentes = $this->casaModel->findCasasByCalleId($calleId);
        foreach ($casasExistentes as $casaExistente) {
            if ($casaExistente['id'] != $casaId && $casaExistente['numero_casa'] == $datos['numero_casa']) {
                return $this->errorResponse('Ya existe una casa con este número en la calle');
            }
        }
    }
    
    // Actualizar casa
    $resultado = $this->casaModel->updateCasa($casaId, $datos);
    
    // Log de actividad
    $this->logAdminActivity('casa_actualizada', [
        'admin_id' => $adminId,
        'casa_id' => $casaId,
        'condominio_id' => $casa['condominio_id'],
        'cambios' => array_keys($datos)
    ]);
    
    return $this->successResponse($resultado, 'Casa actualizada exitosamente');
}
```

### 4. **Crear Clave de Registro**
```php
public function crearClaveRegistro($adminId, $casaId, $datos)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Obtener casa
    $casa = $this->casaModel->findCasaById($casaId);
    if (!$casa) {
        return $this->errorResponse('Casa no encontrada');
    }
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($casa['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para crear claves para esta casa');
    }
    
    // Validar campos requeridos
    $this->validateRequiredFields($datos, ['codigo', 'fecha_expiracion']);
    
    // Verificar que el código sea único
    $claveExistente = $this->casaModel->findClaveRegistro($datos['codigo']);
    if ($claveExistente) {
        return $this->errorResponse('El código ya existe');
    }
    
    // Agregar datos adicionales
    $datos['casa_id'] = $casaId;
    $datos['usado'] = false;
    $datos['fecha_creacion'] = date('Y-m-d H:i:s');
    
    // Crear clave de registro
    $resultado = $this->casaModel->createClaveRegistro($datos);
    
    // Log de actividad
    $this->logAdminActivity('clave_registro_creada', [
        'admin_id' => $adminId,
        'casa_id' => $casaId,
        'condominio_id' => $casa['condominio_id'],
        'codigo' => $datos['codigo']
    ]);
    
    return $this->successResponse($resultado, 'Clave de registro creada exitosamente');
}
```

### 5. **Gestionar Claves de Registro**
```php
public function obtenerClavesRegistroCasa($adminId, $casaId)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Obtener casa
    $casa = $this->casaModel->findCasaById($casaId);
    if (!$casa) {
        return $this->errorResponse('Casa no encontrada');
    }
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($casa['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para ver claves de esta casa');
    }
    
    // Aplicar rate limiting
    $this->enforceRateLimit('claves_casa_' . $adminId);
    
    // Obtener claves
    $claves = $this->casaModel->getClavesByCasa($casaId);
    
    return $this->successResponse($claves, 'Claves de registro obtenidas exitosamente');
}

public function eliminarClaveRegistro($adminId, $codigo)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Obtener clave
    $clave = $this->casaModel->findClaveRegistro($codigo);
    if (!$clave) {
        return $this->errorResponse('Clave de registro no encontrada');
    }
    
    // Obtener casa para validar ownership
    $casa = $this->casaModel->findCasaById($clave['casa_id']);
    if (!$this->condominioService->validarOwnership($casa['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para eliminar esta clave');
    }
    
    // Eliminar clave
    $resultado = $this->casaModel->deleteClaveRegistro($codigo);
    
    // Log de actividad
    $this->logAdminActivity('clave_registro_eliminada', [
        'admin_id' => $adminId,
        'casa_id' => $clave['casa_id'],
        'condominio_id' => $casa['condominio_id'],
        'codigo' => $codigo
    ]);
    
    return $this->successResponse($resultado, 'Clave de registro eliminada exitosamente');
}
```

### 6. **Obtener Estadísticas**
```php
public function obtenerEstadisticasCasas($adminId, $condominioId)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para ver estadísticas de este condominio');
    }
    
    // Aplicar rate limiting
    $this->enforceRateLimit('estadisticas_casas_' . $adminId);
    
    // Obtener estadísticas
    $estadisticas = $this->casaModel->getEstadisticasByCondominio($condominioId);
    
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
public function casaPerteneceACondominio($casaId, $condominioId)
{
    $casa = $this->casaModel->findCasaById($casaId);
    return $casa && $casa['condominio_id'] == $condominioId;
}

private function validarUnicidadNumeroCasa($numeroCasa, $calleId, $casaIdExcluir = null)
{
    $casas = $this->casaModel->findCasasByCalleId($calleId);
    foreach ($casas as $casa) {
        if ($casa['numero_casa'] == $numeroCasa && $casa['id'] != $casaIdExcluir) {
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
// Usar PersonaCasaService para relaciones
$residentes = $this->personaCasaService->obtenerResidentesPorCasa($casaId);

// Validaciones de otros servicios
if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
    return $this->errorResponse("No tienes permisos");
}
```

### Proporciona para otros servicios:
```php
// Para AccesosService, TagService, etc.
public function casaPerteneceACondominio($casaId, $condominioId);
public function validarCasaExiste($casaId);
```

---

## 🚫 RESTRICCIONES IMPORTANTES

### Lo que NO debe hacer:
- ❌ **NO gestionar personas directamente** (usar PersonaCasaService)
- ❌ **NO gestionar relaciones persona-casa** (usar PersonaCasaService)
- ❌ **NO crear casas en calles de otros condominios**

### Scope específico:
- ✅ **CRUD de casas únicamente**
- ✅ **Gestión de claves de registro**
- ✅ **Validación de ubicación y calle**
- ✅ **Estadísticas de propiedades**

---

## 📋 ESTRUCTURA DE RESPUESTAS

### Éxito
```php
return $this->successResponse([
    'casa' => $casaData,
    'mensaje' => 'Casa gestionada exitosamente'
]);
```

### Error de Ownership
```php
return $this->errorResponse(
    'No tienes permisos para gestionar casas en este condominio',
    403
);
```

---

## 🔍 LOGGING REQUERIDO

### Actividades a registrar:
- ✅ Creación de casas
- ✅ Modificación de casas
- ✅ Eliminación de casas
- ✅ Gestión de claves de registro
- ✅ Consultas de estadísticas

---

## 📅 INFORMACIÓN DEL PROMPT
- **Fecha de creación:** 28 de Julio, 2025
- **Servicio:** CasaService.php
- **Posición en cascada:** Nivel 4 (Propiedades)
- **Estado:** ✅ Listo para implementación

---

## 🎯 INSTRUCCIONES PARA COPILOT

Al generar código para CasaService.php:

1. **SIEMPRE heredar de BaseAdminService**
2. **USAR métodos de Casa.php y Calle.php**
3. **VALIDAR ownership del condominio en TODAS las operaciones**
4. **NO gestionar personas directamente**
5. **USAR PersonaCasaService para relaciones persona-casa**
6. **VALIDAR que calles pertenezcan al condominio**
7. **REGISTRAR logs de todas las actividades**
8. **PROPORCIONAR métodos de validación para otros servicios**
