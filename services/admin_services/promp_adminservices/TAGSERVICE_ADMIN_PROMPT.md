# TAGSERVICE_ADMIN_PROMPT.md
## Prompt Especializado para TagService.php

### 🎯 PROPÓSITO DEL SERVICIO
Administrar tags/etiquetas de identificación dentro de un condominio. Gestiona CRUD de tags, asignación a personas, activación/desactivación, tipos de tag y control de acceso. Coordina con DispositivoService y AccesosService para control físico.

---

## 🏗️ ARQUITECTURA Y HERENCIA

### Clase Base
```php
class TagService extends BaseAdminService
```

### Dependencias
- **Hereda de:** `BaseAdminService.php`
- **Modelos principales:** `Tag.php`, `Persona.php`
- **Posición en cascada:** Nivel 6 (Tecnología/Identificación)
- **Servicios relacionados:** DispositivoService, AccesosService, PersonaService
- **Requiere validaciones de:** CondominioService, CasaService

---

## 📚 MÉTODOS DEL MODELO TAG DISPONIBLES

### Métodos de Gestión de Tags
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `createTag()` | array $data | int | Crear tag |
| `findTagById()` | int $id | array | Buscar tag por ID |
| `findTagByCodeOrSerial()` | string $codigo | array | Buscar tag por código o serial |
| `findTagsByCondominio()` | int $condominioId | array | Buscar tags por condominio |
| `findTagsByPersona()` | int $personaId | array | Buscar tags por persona |
| `findTagsByTipo()` | string $tipo | array | Buscar tags por tipo |
| `updateTag()` | int $id, array $data | bool | Actualizar tag |
| `deleteTag()` | int $id | bool | Eliminar tag |

### Métodos de Asignación y Estados
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `assignTagToPersona()` | int $tagId, int $personaId | bool | Asignar tag a persona |
| `unassignTagFromPersona()` | int $tagId | bool | Desasignar tag de persona |
| `activateTag()` | int $tagId | bool | Activar tag |
| `deactivateTag()` | int $tagId | bool | Desactivar tag |
| `blockTag()` | int $tagId, string $razon | bool | Bloquear tag |
| `unblockTag()` | int $tagId | bool | Desbloquear tag |
| `reportTagLost()` | int $tagId | bool | Reportar tag como perdido |
| `reportTagFound()` | int $tagId | bool | Reportar tag como encontrado |

### Métodos de Tipos y Categorías
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `createTipoTag()` | array $data | int | Crear tipo de tag |
| `findTipoTagById()` | int $id | array | Buscar tipo de tag por ID |
| `findTiposTagByCondominio()` | int $condominioId | array | Buscar tipos de tag por condominio |
| `updateTipoTag()` | int $id, array $data | bool | Actualizar tipo de tag |
| `deleteTipoTag()` | int $id | bool | Eliminar tipo de tag |
| `setPermisosAcceso()` | int $tipoId, array $permisos | bool | Establecer permisos de acceso |

### Métodos de Validación de Acceso
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `validateTagAccess()` | string $codigo, string $area | bool | Validar acceso de tag |
| `validateTagActive()` | int $tagId | bool | Validar que tag esté activo |
| `validateTagPermissions()` | int $tagId, string $area | bool | Validar permisos de tag |
| `validateTagNotBlocked()` | int $tagId | bool | Validar que tag no esté bloqueado |
| `validateTagAssigned()` | int $tagId | bool | Validar que tag esté asignado |

### Métodos de Auditoría y Logs
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `logTagAccess()` | int $tagId, string $area, string $result | bool | Registrar acceso de tag |
| `getTagAccessHistory()` | int $tagId, array $periodo | array | Obtener historial de acceso |
| `getTagActivityReport()` | int $condominioId, array $periodo | array | Reporte de actividad de tags |
| `getTagsInactivos()` | int $condominioId, int $dias | array | Obtener tags inactivos |

### Métodos de Validación
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `validateCondominioExists()` | int $condominioId | bool | Valida existencia de condominio |
| `validatePersonaExists()` | int $personaId | bool | Valida existencia de persona |
| `validateTagExists()` | int $tagId | bool | Valida existencia de tag |
| `validateTipoTagExists()` | int $tipoId | bool | Valida existencia de tipo de tag |
| `validateTagUnique()` | string $codigo, int $condominioId | bool | Valida unicidad de tag |

### Métodos de Estadísticas y Reportes
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `getEstadisticasUso()` | int $condominioId, array $periodo | array | Estadísticas de uso |
| `getReporteDistribucion()` | int $condominioId | array | Reporte de distribución |
| `getReporteSeguridad()` | int $condominioId, array $periodo | array | Reporte de seguridad |

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

### 1. **Crear Tag**
```php
public function crearTag($adminId, $condominioId, $datos)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para crear tags en este condominio');
    }
    
    // Validar campos requeridos
    $this->validateRequiredFields($datos, ['codigo', 'tipo_tag_id']);
    
    // Validar que el código sea único en el condominio
    if (!$this->tagModel->validateTagUnique($datos['codigo'], $condominioId)) {
        return $this->errorResponse('El código del tag ya existe en este condominio');
    }
    
    // Validar que el tipo de tag existe
    if (!$this->tagModel->validateTipoTagExists($datos['tipo_tag_id'])) {
        return $this->errorResponse('Tipo de tag no encontrado');
    }
    
    // Agregar datos adicionales
    $datos['condominio_id'] = $condominioId;
    $datos['fecha_creacion'] = date('Y-m-d H:i:s');
    $datos['estado'] = 'inactivo'; // Los tags inician inactivos hasta ser asignados
    $datos['bloqueado'] = false;
    $datos['perdido'] = false;
    
    // Generar número de serie único si no se proporciona
    if (!isset($datos['numero_serie'])) {
        $datos['numero_serie'] = $this->generateUniqueSerial($condominioId);
    }
    
    // Crear tag
    $tagId = $this->tagModel->createTag($datos);
    
    // Log de actividad
    $this->logAdminActivity('tag_creado', [
        'admin_id' => $adminId,
        'condominio_id' => $condominioId,
        'tag_id' => $tagId,
        'codigo' => $datos['codigo'],
        'tipo_tag_id' => $datos['tipo_tag_id']
    ]);
    
    return $this->successResponse(['id' => $tagId], 'Tag creado exitosamente');
}
```

### 2. **Asignar Tag a Persona**
```php
public function asignarTagAPersona($adminId, $tagId, $personaId)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Obtener tag
    $tag = $this->tagModel->findTagById($tagId);
    if (!$tag) {
        return $this->errorResponse('Tag no encontrado');
    }
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($tag['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para asignar este tag');
    }
    
    // Validar que la persona existe
    if (!$this->tagModel->validatePersonaExists($personaId)) {
        return $this->errorResponse('Persona no encontrada');
    }
    
    // Validar que el tag no esté ya asignado
    if ($tag['persona_id']) {
        return $this->errorResponse('El tag ya está asignado a otra persona');
    }
    
    // Validar que el tag no esté bloqueado
    if ($tag['bloqueado']) {
        return $this->errorResponse('El tag está bloqueado y no puede ser asignado');
    }
    
    // Validar que el tag no esté reportado como perdido
    if ($tag['perdido']) {
        return $this->errorResponse('El tag está reportado como perdido');
    }
    
    // Verificar que la persona pertenezca al mismo condominio
    $persona = $this->personaService->obtenerPersona($personaId);
    if (!$this->casaService->personaPerteneceACondominio($personaId, $tag['condominio_id'])) {
        return $this->errorResponse('La persona no pertenece a este condominio');
    }
    
    // Asignar tag
    $resultado = $this->tagModel->assignTagToPersona($tagId, $personaId);
    
    // Activar tag automáticamente al asignarlo
    $this->tagModel->activateTag($tagId);
    
    // Log de actividad
    $this->logAdminActivity('tag_asignado', [
        'admin_id' => $adminId,
        'tag_id' => $tagId,
        'persona_id' => $personaId,
        'condominio_id' => $tag['condominio_id'],
        'codigo' => $tag['codigo']
    ]);
    
    return $this->successResponse($resultado, 'Tag asignado exitosamente');
}
```

### 3. **Gestionar Estados de Tag**
```php
public function cambiarEstadoTag($adminId, $tagId, $nuevoEstado, $razon = null)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Obtener tag
    $tag = $this->tagModel->findTagById($tagId);
    if (!$tag) {
        return $this->errorResponse('Tag no encontrado');
    }
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($tag['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para modificar este tag');
    }
    
    // Validar estado
    $estadosValidos = ['activo', 'inactivo', 'bloqueado', 'perdido', 'encontrado'];
    if (!in_array($nuevoEstado, $estadosValidos)) {
        return $this->errorResponse('Estado de tag inválido');
    }
    
    $resultado = false;
    $mensaje = '';
    
    switch ($nuevoEstado) {
        case 'activo':
            if (!$tag['persona_id']) {
                return $this->errorResponse('No se puede activar un tag sin asignar');
            }
            $resultado = $this->tagModel->activateTag($tagId);
            $mensaje = 'Tag activado exitosamente';
            break;
            
        case 'inactivo':
            $resultado = $this->tagModel->deactivateTag($tagId);
            $mensaje = 'Tag desactivado exitosamente';
            break;
            
        case 'bloqueado':
            if (!$razon) {
                return $this->errorResponse('Se requiere una razón para bloquear el tag');
            }
            $resultado = $this->tagModel->blockTag($tagId, $razon);
            $mensaje = 'Tag bloqueado exitosamente';
            break;
            
        case 'perdido':
            $resultado = $this->tagModel->reportTagLost($tagId);
            // Desactivar automáticamente al reportar como perdido
            $this->tagModel->deactivateTag($tagId);
            $mensaje = 'Tag reportado como perdido';
            break;
            
        case 'encontrado':
            if (!$tag['perdido']) {
                return $this->errorResponse('El tag no está reportado como perdido');
            }
            $resultado = $this->tagModel->reportTagFound($tagId);
            $mensaje = 'Tag reportado como encontrado';
            break;
    }
    
    // Log de actividad
    $this->logAdminActivity('tag_estado_cambiado', [
        'admin_id' => $adminId,
        'tag_id' => $tagId,
        'estado_anterior' => $tag['estado'],
        'estado_nuevo' => $nuevoEstado,
        'razon' => $razon,
        'condominio_id' => $tag['condominio_id']
    ]);
    
    return $this->successResponse($resultado, $mensaje);
}
```

### 4. **Obtener Tags del Condominio**
```php
public function obtenerTagsCondominio($adminId, $condominioId, $opciones = [])
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para ver tags de este condominio');
    }
    
    // Aplicar rate limiting
    $this->enforceRateLimit('tags_' . $adminId);
    
    // Obtener tags
    $tags = $this->tagModel->findTagsByCondominio($condominioId);
    
    // Aplicar filtros si se proporcionan
    if (isset($opciones['estado'])) {
        $tags = array_filter($tags, function($tag) use ($opciones) {
            return $tag['estado'] == $opciones['estado'];
        });
    }
    
    if (isset($opciones['tipo_tag_id'])) {
        $tags = array_filter($tags, function($tag) use ($opciones) {
            return $tag['tipo_tag_id'] == $opciones['tipo_tag_id'];
        });
    }
    
    if (isset($opciones['asignados_solamente']) && $opciones['asignados_solamente']) {
        $tags = array_filter($tags, function($tag) {
            return $tag['persona_id'] != null;
        });
    }
    
    if (isset($opciones['sin_asignar']) && $opciones['sin_asignar']) {
        $tags = array_filter($tags, function($tag) {
            return $tag['persona_id'] == null;
        });
    }
    
    // Agregar información adicional
    foreach ($tags as &$tag) {
        if ($tag['persona_id']) {
            $tag['persona_info'] = $this->personaService->obtenerPersonaBasica($tag['persona_id']);
        }
        $tag['ultimo_acceso'] = $this->getUltimoAccesoTag($tag['id']);
    }
    
    return $this->successResponse($tags, 'Tags obtenidos exitosamente');
}
```

### 5. **Gestionar Tipos de Tag**
```php
public function crearTipoTag($adminId, $condominioId, $datos)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para crear tipos de tag en este condominio');
    }
    
    // Validar campos requeridos
    $this->validateRequiredFields($datos, ['nombre', 'descripcion']);
    
    // Verificar que no existe otro tipo con el mismo nombre
    $tiposExistentes = $this->tagModel->findTiposTagByCondominio($condominioId);
    foreach ($tiposExistentes as $tipo) {
        if (strtolower($tipo['nombre']) == strtolower($datos['nombre'])) {
            return $this->errorResponse('Ya existe un tipo de tag con este nombre');
        }
    }
    
    // Agregar datos adicionales
    $datos['condominio_id'] = $condominioId;
    $datos['fecha_creacion'] = date('Y-m-d H:i:s');
    $datos['activo'] = true;
    
    // Establecer permisos por defecto
    if (!isset($datos['permisos_acceso'])) {
        $datos['permisos_acceso'] = json_encode([
            'entrada_principal' => true,
            'areas_comunes' => true,
            'estacionamiento' => true
        ]);
    }
    
    // Crear tipo de tag
    $tipoId = $this->tagModel->createTipoTag($datos);
    
    // Log de actividad
    $this->logAdminActivity('tipo_tag_creado', [
        'admin_id' => $adminId,
        'condominio_id' => $condominioId,
        'tipo_tag_id' => $tipoId,
        'nombre' => $datos['nombre']
    ]);
    
    return $this->successResponse(['id' => $tipoId], 'Tipo de tag creado exitosamente');
}
```

### 6. **Validar Acceso de Tag**
```php
public function validarAccesoTag($codigo, $area, $dispositivo = null)
{
    // Esta función puede ser llamada desde dispositivos externos
    // por lo que no requiere autenticación de admin
    
    // Buscar tag por código
    $tag = $this->tagModel->findTagByCodeOrSerial($codigo);
    if (!$tag) {
        $this->logTagAccess(null, $area, 'tag_no_encontrado', $codigo);
        return $this->errorResponse('Tag no encontrado');
    }
    
    // Validar que el tag esté activo
    if (!$this->tagModel->validateTagActive($tag['id'])) {
        $this->logTagAccess($tag['id'], $area, 'tag_inactivo');
        return $this->errorResponse('Tag inactivo');
    }
    
    // Validar que el tag no esté bloqueado
    if (!$this->tagModel->validateTagNotBlocked($tag['id'])) {
        $this->logTagAccess($tag['id'], $area, 'tag_bloqueado');
        return $this->errorResponse('Tag bloqueado');
    }
    
    // Validar que el tag esté asignado
    if (!$this->tagModel->validateTagAssigned($tag['id'])) {
        $this->logTagAccess($tag['id'], $area, 'tag_sin_asignar');
        return $this->errorResponse('Tag sin asignar');
    }
    
    // Validar permisos para el área específica
    if (!$this->tagModel->validateTagPermissions($tag['id'], $area)) {
        $this->logTagAccess($tag['id'], $area, 'sin_permisos');
        return $this->errorResponse('Sin permisos para acceder a esta área');
    }
    
    // Registrar acceso exitoso
    $this->tagModel->logTagAccess($tag['id'], $area, 'acceso_permitido');
    
    return $this->successResponse([
        'tag_id' => $tag['id'],
        'persona_id' => $tag['persona_id'],
        'acceso_permitido' => true
    ], 'Acceso permitido');
}
```

### 7. **Obtener Reportes**
```php
public function obtenerReporteSeguridad($adminId, $condominioId, $periodo = [])
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para ver reportes de este condominio');
    }
    
    // Aplicar rate limiting
    $this->enforceRateLimit('reporte_seguridad_' . $adminId);
    
    // Obtener reporte
    $reporte = $this->tagModel->getReporteSeguridad($condominioId, $periodo);
    
    // Agregar información adicional
    $reporte['tags_bloqueados'] = count(array_filter($reporte['tags'], function($tag) {
        return $tag['bloqueado'];
    }));
    
    $reporte['tags_perdidos'] = count(array_filter($reporte['tags'], function($tag) {
        return $tag['perdido'];
    }));
    
    $reporte['accesos_denegados_recientes'] = $this->getAccesosDenegadosRecientes($condominioId, 7);
    
    return $this->successResponse($reporte, 'Reporte de seguridad obtenido exitosamente');
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
public function tagPerteneceACondominio($tagId, $condominioId)
{
    $tag = $this->tagModel->findTagById($tagId);
    return $tag && $tag['condominio_id'] == $condominioId;
}

private function generateUniqueSerial($condominioId)
{
    do {
        $serial = 'TAG' . $condominioId . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $existing = $this->tagModel->findTagByCodeOrSerial($serial);
    } while ($existing);
    
    return $serial;
}

private function logTagAccess($tagId, $area, $resultado, $codigo = null)
{
    $data = [
        'tag_id' => $tagId,
        'area' => $area,
        'resultado' => $resultado,
        'timestamp' => date('Y-m-d H:i:s'),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    if ($codigo) {
        $data['codigo_intentado'] = $codigo;
    }
    
    $this->tagModel->logTagAccess($tagId, $area, $resultado);
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

// Verificar personas en casas
if (!$this->casaService->personaPerteneceACondominio($personaId, $condominioId)) {
    return $this->errorResponse("Persona no pertenece al condominio");
}

// Coordinar con DispositivoService para control físico
$this->dispositivoService->sincronizarTagsActivos($condominioId);
```

### Proporciona para otros servicios:
```php
// Para AccesosService, DispositivoService
public function tagPerteneceACondominio($tagId, $condominioId);
public function validarTagActivo($tagId);
public function validarAccesoTag($codigo, $area);
```

---

## 🚫 RESTRICCIONES IMPORTANTES

### Lo que NO debe hacer:
- ❌ **NO gestionar dispositivos físicos directamente** (usar DispositivoService)
- ❌ **NO manejar personas directamente** (usar PersonaService)
- ❌ **NO controlar acceso físico** (coordinar con DispositivoService)

### Scope específico:
- ✅ **CRUD de tags y tipos de tag**
- ✅ **Asignación de tags a personas**
- ✅ **Gestión de estados (activo/inactivo/bloqueado/perdido)**
- ✅ **Validación de permisos de acceso**
- ✅ **Auditoría y logs de acceso**
- ✅ **Reportes de seguridad**

---

## 📋 ESTRUCTURA DE RESPUESTAS

### Éxito
```php
return $this->successResponse([
    'tag' => $tagData,
    'mensaje' => 'Tag gestionado exitosamente'
]);
```

### Error de Acceso
```php
return $this->errorResponse(
    'Tag bloqueado - Acceso denegado',
    403
);
```

---

## 🔍 LOGGING REQUERIDO

### Actividades a registrar:
- ✅ Creación/modificación de tags
- ✅ Asignación/desasignación de tags
- ✅ Cambios de estado
- ✅ Intentos de acceso (exitosos y fallidos)
- ✅ Reportes de tags perdidos/encontrados

---

## 📅 INFORMACIÓN DEL PROMPT
- **Fecha de creación:** 28 de Julio, 2025
- **Servicio:** TagService.php
- **Posición en cascada:** Nivel 6 (Tecnología/Identificación)
- **Estado:** ✅ Listo para implementación

---

## 🎯 INSTRUCCIONES PARA COPILOT

Al generar código para TagService.php:

1. **SIEMPRE heredar de BaseAdminService**
2. **USAR métodos de Tag.php y Persona.php**
3. **VALIDAR ownership del condominio en TODAS las operaciones**
4. **GESTIONAR estados de tags apropiadamente**
5. **COORDINAR con DispositivoService para control físico**
6. **VALIDAR permisos de acceso por área**
7. **REGISTRAR TODOS los intentos de acceso**
8. **PROPORCIONAR métodos de validación para otros servicios**
