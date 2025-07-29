# PERSONACASASERVICE_ADMIN_PROMPT.md
## Prompt Especializado para PersonaCasaService.php

### 🎯 PROPÓSITO DEL SERVICIO
Administrar las relaciones entre personas y casas dentro de un condominio. Este servicio permite al admin ver qué personas viven en cada casa (puede ser más de una persona por casa), eliminar relaciones persona-casa, y acceder a la información completa de las casas incluyendo crear, editar o borrar las propiedades.

---

## 🏗️ ARQUITECTURA Y HERENCIA

### Clase Base
```php
class PersonaCasaService extends BaseAdminService
```

### Dependencias
- **Hereda de:** `BaseAdminService.php`
- **Modelos principales:** `Persona.php`, `Casa.php`
- **Posición en cascada:** Nivel 9 (Relaciones Avanzadas)
- **Servicios relacionados:** CasaService, PersonaService, CalleService
- **Requiere validaciones de:** CondominioService

---

## 📚 MÉTODOS DEL MODELO DISPONIBLES

### Métodos de Gestión de Relaciones Persona-Casa
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `assignPersonaToCasa()` | int $personaId, int $casaId | bool | Asignar persona a casa |
| `removePersonaFromCasa()` | int $personaId, int $casaId | bool | Remover persona de casa |
| `getPersonasByCasa()` | int $casaId | array | Obtener personas por casa |
| `getCasasByPersona()` | int $personaId | array | Obtener casas por persona |
| `isPersonaAssignedToCasa()` | int $personaId, int $casaId | bool | Verifica asignación persona-casa |
| `updateRelacionPersonaCasa()` | int $personaId, int $casaId, array $data | bool | Actualizar datos de relación |
| `setTipoRelacion()` | int $personaId, int $casaId, string $tipo | bool | Establecer tipo de relación |

### Métodos de Gestión de Casas (CRUD Completo)
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `createCasa()` | array $data | int | Crear casa |
| `findCasaById()` | int $id | array | Buscar casa por ID |
| `findCasasByCalleId()` | int $calleId | array | Buscar casas por calle |
| `findCasasByCondominioId()` | int $condominioId | array | Buscar casas por condominio |
| `updateCasa()` | int $id, array $data | bool | Actualizar casa |
| `deleteCasa()` | int $id | bool | Eliminar casa |
| `activateCasa()` | int $id | bool | Activar casa |
| `deactivateCasa()` | int $id | bool | Desactivar casa |

### Métodos de Gestión de Personas
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `createPersona()` | array $data | int | Crear persona |
| `findPersonaById()` | int $id | array | Buscar persona por ID |
| `findPersonasByCondominio()` | int $condominioId | array | Buscar personas por condominio |
| `updatePersona()` | int $id, array $data | bool | Actualizar persona |
| `deletePersona()` | int $id | bool | Eliminar persona |
| `activatePersona()` | int $id | bool | Activar persona |
| `deactivatePersona()` | int $id | bool | Desactivar persona |

### Métodos de Tipos de Relación
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `createTipoRelacion()` | array $data | int | Crear tipo de relación |
| `getTiposRelacion()` | int $condominioId | array | Obtener tipos de relación |
| `updateTipoRelacion()` | int $id, array $data | bool | Actualizar tipo de relación |
| `deleteTipoRelacion()` | int $id | bool | Eliminar tipo de relación |
| `setPermisosRelacion()` | int $tipoId, array $permisos | bool | Establecer permisos por tipo |

### Métodos de Validación
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `validateCondominioExists()` | int $condominioId | bool | Valida existencia de condominio |
| `validatePersonaExists()` | int $personaId | bool | Valida existencia de persona |
| `validateCasaExists()` | int $casaId | bool | Valida existencia de casa |
| `validateRelacionExists()` | int $personaId, int $casaId | bool | Valida existencia de relación |
| `validateCalleExists()` | int $calleId | bool | Valida existencia de calle |
| `validateCalleInCondominio()` | int $calleId, int $condominioId | bool | Valida calle en condominio |

### Métodos de Consultas Avanzadas
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `getCasasConResidentes()` | int $condominioId | array | Casas con sus residentes |
| `getCasasVacias()` | int $condominioId | array | Casas sin residentes |
| `getPersonasSinCasa()` | int $condominioId | array | Personas sin casa asignada |
| `getRelacionesCompletas()` | int $condominioId | array | Todas las relaciones del condominio |
| `getCasasPorCalle()` | int $calleId | array | Casas organizadas por calle |

### Métodos de Estadísticas y Reportes
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `getEstadisticasOcupacion()` | int $condominioId | array | Estadísticas de ocupación |
| `getReporteRelaciones()` | int $condominioId | array | Reporte de relaciones |
| `getReporteMovimientos()` | int $condominioId, array $periodo | array | Reporte de movimientos |
| `getAnalisisDemografico()` | int $condominioId | array | Análisis demográfico |

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

### 1. **Crear Casa (CRUD Completo)**
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
    if (!$this->personaCasaModel->validateCalleExists($datos['calle_id'])) {
        return $this->errorResponse('Calle no encontrada');
    }
    
    if (!$this->personaCasaModel->validateCalleInCondominio($datos['calle_id'], $condominioId)) {
        return $this->errorResponse('La calle no pertenece a este condominio');
    }
    
    // Verificar que no existe otra casa con el mismo número en la calle
    $casasExistentes = $this->personaCasaModel->findCasasByCalleId($datos['calle_id']);
    foreach ($casasExistentes as $casa) {
        if ($casa['numero_casa'] == $datos['numero_casa']) {
            return $this->errorResponse('Ya existe una casa con este número en la calle');
        }
    }
    
    // Agregar datos adicionales
    $datos['condominio_id'] = $condominioId;
    $datos['fecha_creacion'] = date('Y-m-d H:i:s');
    $datos['activa'] = true;
    $datos['total_residentes'] = 0;
    
    // Crear casa
    $casaId = $this->personaCasaModel->createCasa($datos);
    
    // Log de actividad
    $this->logAdminActivity('casa_creada_personacasa', [
        'admin_id' => $adminId,
        'condominio_id' => $condominioId,
        'casa_id' => $casaId,
        'numero_casa' => $datos['numero_casa'],
        'calle_id' => $datos['calle_id']
    ]);
    
    return $this->successResponse(['id' => $casaId], 'Casa creada exitosamente');
}

public function actualizarCasa($adminId, $casaId, $datos)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Obtener casa actual
    $casa = $this->personaCasaModel->findCasaById($casaId);
    if (!$casa) {
        return $this->errorResponse('Casa no encontrada');
    }
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($casa['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para editar esta casa');
    }
    
    // Actualizar casa
    $resultado = $this->personaCasaModel->updateCasa($casaId, $datos);
    
    // Log de actividad
    $this->logAdminActivity('casa_actualizada_personacasa', [
        'admin_id' => $adminId,
        'casa_id' => $casaId,
        'condominio_id' => $casa['condominio_id'],
        'cambios' => array_keys($datos)
    ]);
    
    return $this->successResponse($resultado, 'Casa actualizada exitosamente');
}

public function eliminarCasa($adminId, $casaId)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Obtener casa
    $casa = $this->personaCasaModel->findCasaById($casaId);
    if (!$casa) {
        return $this->errorResponse('Casa no encontrada');
    }
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($casa['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para eliminar esta casa');
    }
    
    // Verificar que no tenga residentes activos
    $residentes = $this->personaCasaModel->getPersonasByCasa($casaId);
    if (count($residentes) > 0) {
        return $this->errorResponse('No se puede eliminar una casa con residentes asignados. Primero remueve todas las relaciones.');
    }
    
    // Eliminar casa
    $resultado = $this->personaCasaModel->deleteCasa($casaId);
    
    // Log de actividad
    $this->logAdminActivity('casa_eliminada_personacasa', [
        'admin_id' => $adminId,
        'casa_id' => $casaId,
        'condominio_id' => $casa['condominio_id'],
        'numero_casa' => $casa['numero_casa']
    ]);
    
    return $this->successResponse($resultado, 'Casa eliminada exitosamente');
}
```

### 2. **Ver Personas por Casa (Puede ser más de una)**
```php
public function obtenerPersonasPorCasa($adminId, $casaId)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Obtener casa
    $casa = $this->personaCasaModel->findCasaById($casaId);
    if (!$casa) {
        return $this->errorResponse('Casa no encontrada');
    }
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($casa['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para ver información de esta casa');
    }
    
    // Aplicar rate limiting
    $this->enforceRateLimit('personas_casa_' . $adminId);
    
    // Obtener personas de la casa
    $personas = $this->personaCasaModel->getPersonasByCasa($casaId);
    
    // Agregar información de relación para cada persona
    foreach ($personas as &$persona) {
        $persona['tipo_relacion'] = $this->getTipoRelacionPersonaCasa($persona['id'], $casaId);
        $persona['fecha_asignacion'] = $this->getFechaAsignacionPersonaCasa($persona['id'], $casaId);
        $persona['activa'] = $this->isRelacionActiva($persona['id'], $casaId);
    }
    
    // Información adicional de la casa
    $informacionCasa = [
        'casa' => $casa,
        'total_residentes' => count($personas),
        'residentes' => $personas,
        'calle_info' => $this->getInformacionCalle($casa['calle_id']),
        'servicios_casa' => $this->getServiciosCasa($casaId)
    ];
    
    return $this->successResponse($informacionCasa, 'Personas de la casa obtenidas exitosamente');
}

public function obtenerCasasConResidentes($adminId, $condominioId, $opciones = [])
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para ver casas de este condominio');
    }
    
    // Aplicar rate limiting
    $this->enforceRateLimit('casas_residentes_' . $adminId);
    
    // Obtener casas con sus residentes
    $casasConResidentes = $this->personaCasaModel->getCasasConResidentes($condominioId);
    
    // Aplicar filtros si se proporcionan
    if (isset($opciones['calle_id'])) {
        $casasConResidentes = array_filter($casasConResidentes, function($casa) use ($opciones) {
            return $casa['calle_id'] == $opciones['calle_id'];
        });
    }
    
    if (isset($opciones['solo_con_residentes']) && $opciones['solo_con_residentes']) {
        $casasConResidentes = array_filter($casasConResidentes, function($casa) {
            return count($casa['residentes']) > 0;
        });
    }
    
    if (isset($opciones['solo_vacias']) && $opciones['solo_vacias']) {
        $casasConResidentes = array_filter($casasConResidentes, function($casa) {
            return count($casa['residentes']) == 0;
        });
    }
    
    return $this->successResponse($casasConResidentes, 'Casas con residentes obtenidas exitosamente');
}
```

### 3. **Asignar Persona a Casa**
```php
public function asignarPersonaACasa($adminId, $personaId, $casaId, $tipoRelacion = 'residente')
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Obtener casa
    $casa = $this->personaCasaModel->findCasaById($casaId);
    if (!$casa) {
        return $this->errorResponse('Casa no encontrada');
    }
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($casa['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para asignar personas en esta casa');
    }
    
    // Validar que la persona existe
    if (!$this->personaCasaModel->validatePersonaExists($personaId)) {
        return $this->errorResponse('Persona no encontrada');
    }
    
    // Validar que no existe ya la relación
    if ($this->personaCasaModel->isPersonaAssignedToCasa($personaId, $casaId)) {
        return $this->errorResponse('La persona ya está asignada a esta casa');
    }
    
    // Validar tipos de relación válidos
    $tiposValidos = ['propietario', 'residente', 'arrendatario', 'familiar', 'visitante_permanente'];
    if (!in_array($tipoRelacion, $tiposValidos)) {
        return $this->errorResponse('Tipo de relación inválido');
    }
    
    // Verificar que no hay más de un propietario si el tipo es propietario
    if ($tipoRelacion == 'propietario') {
        $residentes = $this->personaCasaModel->getPersonasByCasa($casaId);
        foreach ($residentes as $residente) {
            if ($this->getTipoRelacionPersonaCasa($residente['id'], $casaId) == 'propietario') {
                return $this->errorResponse('La casa ya tiene un propietario asignado');
            }
        }
    }
    
    // Asignar persona a casa
    $resultado = $this->personaCasaModel->assignPersonaToCasa($personaId, $casaId);
    
    // Establecer tipo de relación
    $this->personaCasaModel->setTipoRelacion($personaId, $casaId, $tipoRelacion);
    
    // Actualizar contador de residentes en la casa
    $this->actualizarContadorResidentes($casaId);
    
    // Log de actividad
    $this->logAdminActivity('persona_asignada_casa', [
        'admin_id' => $adminId,
        'persona_id' => $personaId,
        'casa_id' => $casaId,
        'condominio_id' => $casa['condominio_id'],
        'tipo_relacion' => $tipoRelacion
    ]);
    
    return $this->successResponse($resultado, 'Persona asignada a casa exitosamente');
}
```

### 4. **Eliminar Relación Persona-Casa**
```php
public function eliminarRelacionPersonaCasa($adminId, $personaId, $casaId)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Obtener casa
    $casa = $this->personaCasaModel->findCasaById($casaId);
    if (!$casa) {
        return $this->errorResponse('Casa no encontrada');
    }
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($casa['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para eliminar relaciones en esta casa');
    }
    
    // Validar que la relación existe
    if (!$this->personaCasaModel->validateRelacionExists($personaId, $casaId)) {
        return $this->errorResponse('La relación persona-casa no existe');
    }
    
    // Obtener información de la persona antes de eliminar
    $persona = $this->personaCasaModel->findPersonaById($personaId);
    $tipoRelacion = $this->getTipoRelacionPersonaCasa($personaId, $casaId);
    
    // Eliminar relación
    $resultado = $this->personaCasaModel->removePersonaFromCasa($personaId, $casaId);
    
    // Actualizar contador de residentes en la casa
    $this->actualizarContadorResidentes($casaId);
    
    // Log de actividad
    $this->logAdminActivity('relacion_persona_casa_eliminada', [
        'admin_id' => $adminId,
        'persona_id' => $personaId,
        'casa_id' => $casaId,
        'condominio_id' => $casa['condominio_id'],
        'tipo_relacion_anterior' => $tipoRelacion,
        'persona_nombre' => $persona['nombre'] ?? 'Desconocido'
    ]);
    
    return $this->successResponse($resultado, 'Relación persona-casa eliminada exitosamente');
}

public function eliminarTodasRelacionesCasa($adminId, $casaId)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Obtener casa
    $casa = $this->personaCasaModel->findCasaById($casaId);
    if (!$casa) {
        return $this->errorResponse('Casa no encontrada');
    }
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($casa['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para eliminar relaciones en esta casa');
    }
    
    // Obtener todas las personas de la casa
    $residentes = $this->personaCasaModel->getPersonasByCasa($casaId);
    
    if (count($residentes) == 0) {
        return $this->errorResponse('La casa no tiene residentes asignados');
    }
    
    $eliminados = 0;
    $errores = [];
    
    // Eliminar cada relación
    foreach ($residentes as $residente) {
        try {
            $this->personaCasaModel->removePersonaFromCasa($residente['id'], $casaId);
            $eliminados++;
        } catch (Exception $e) {
            $errores[] = "Error eliminando relación con persona ID {$residente['id']}: " . $e->getMessage();
        }
    }
    
    // Actualizar contador de residentes
    $this->actualizarContadorResidentes($casaId);
    
    // Log de actividad
    $this->logAdminActivity('todas_relaciones_casa_eliminadas', [
        'admin_id' => $adminId,
        'casa_id' => $casaId,
        'condominio_id' => $casa['condominio_id'],
        'residentes_eliminados' => $eliminados,
        'errores' => count($errores)
    ]);
    
    return $this->successResponse([
        'eliminados' => $eliminados,
        'errores' => $errores
    ], "Se eliminaron $eliminados relaciones exitosamente");
}
```

### 5. **Obtener Estadísticas y Reportes**
```php
public function obtenerEstadisticasOcupacion($adminId, $condominioId)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para ver estadísticas de este condominio');
    }
    
    // Aplicar rate limiting
    $this->enforceRateLimit('estadisticas_ocupacion_' . $adminId);
    
    // Obtener estadísticas
    $estadisticas = $this->personaCasaModel->getEstadisticasOcupacion($condominioId);
    
    // Agregar información adicional
    $casasConResidentes = $this->personaCasaModel->getCasasConResidentes($condominioId);
    $casasVacias = $this->personaCasaModel->getCasasVacias($condominioId);
    $personasSinCasa = $this->personaCasaModel->getPersonasSinCasa($condominioId);
    
    $estadisticas['resumen'] = [
        'total_casas' => count($casasConResidentes) + count($casasVacias),
        'casas_con_residentes' => count($casasConResidentes),
        'casas_vacias' => count($casasVacias),
        'personas_sin_casa' => count($personasSinCasa),
        'porcentaje_ocupacion' => $this->calcularPorcentajeOcupacion($condominioId)
    ];
    
    // Estadísticas por tipo de relación
    $estadisticas['por_tipo_relacion'] = $this->getEstadisticasPorTipoRelacion($condominioId);
    
    // Estadísticas por calle
    $estadisticas['por_calle'] = $this->getEstadisticasPorCalle($condominioId);
    
    return $this->successResponse($estadisticas, 'Estadísticas de ocupación obtenidas exitosamente');
}

public function obtenerReporteRelaciones($adminId, $condominioId, $periodo = [])
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para ver reportes de este condominio');
    }
    
    // Aplicar rate limiting
    $this->enforceRateLimit('reporte_relaciones_' . $adminId);
    
    // Obtener reporte
    $reporte = $this->personaCasaModel->getReporteRelaciones($condominioId);
    
    // Agregar movimientos si se especifica período
    if (!empty($periodo)) {
        $reporte['movimientos'] = $this->personaCasaModel->getReporteMovimientos($condominioId, $periodo);
    }
    
    // Análisis demográfico
    $reporte['analisis_demografico'] = $this->personaCasaModel->getAnalisisDemografico($condominioId);
    
    return $this->successResponse($reporte, 'Reporte de relaciones obtenido exitosamente');
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
private function actualizarContadorResidentes($casaId)
{
    $residentes = $this->personaCasaModel->getPersonasByCasa($casaId);
    $totalResidentes = count($residentes);
    
    $this->personaCasaModel->updateCasa($casaId, [
        'total_residentes' => $totalResidentes,
        'ultima_actualizacion' => date('Y-m-d H:i:s')
    ]);
}

private function getTipoRelacionPersonaCasa($personaId, $casaId)
{
    // Obtener tipo de relación de la tabla de relaciones
    return $this->personaCasaModel->getTipoRelacion($personaId, $casaId);
}

private function getFechaAsignacionPersonaCasa($personaId, $casaId)
{
    // Obtener fecha de asignación de la tabla de relaciones
    return $this->personaCasaModel->getFechaAsignacion($personaId, $casaId);
}

private function isRelacionActiva($personaId, $casaId)
{
    return $this->personaCasaModel->isPersonaAssignedToCasa($personaId, $casaId);
}

private function calcularPorcentajeOcupacion($condominioId)
{
    $totalCasas = count($this->personaCasaModel->findCasasByCondominioId($condominioId));
    $casasOcupadas = count($this->personaCasaModel->getCasasConResidentes($condominioId));
    
    return $totalCasas > 0 ? round(($casasOcupadas / $totalCasas) * 100, 2) : 0;
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

// Usar CasaService para validaciones de casa
if (!$this->casaService->validarCasaExiste($casaId)) {
    return $this->errorResponse("Casa no encontrada");
}

// Usar CalleService para información de calles
$informacionCalle = $this->calleService->obtenerInformacionCalle($calleId);
```

### Proporciona para otros servicios:
```php
// Para otros servicios que necesiten información de residencia
public function personaViveEnCondominio($personaId, $condominioId);
public function obtenerCasasDePersona($personaId);
public function validarRelacionActiva($personaId, $casaId);
```

---

## 🚫 RESTRICCIONES IMPORTANTES

### Lo que NO debe hacer:
- ❌ **NO gestionar tags o engomados** (usar TagService/EngomadoService)
- ❌ **NO manejar accesos físicos** (usar AccesosService)
- ❌ **NO gestionar empleados** (usar EmpleadoService)

### Scope específico:
- ✅ **CRUD completo de casas**
- ✅ **Gestión de relaciones persona-casa**
- ✅ **Ver múltiples personas por casa**
- ✅ **Eliminar relaciones específicas o todas**
- ✅ **Estadísticas de ocupación**
- ✅ **Reportes demográficos**

---

## 📋 ESTRUCTURA DE RESPUESTAS

### Éxito
```php
return $this->successResponse([
    'relacion' => $relacionData,
    'mensaje' => 'Relación persona-casa gestionada exitosamente'
]);
```

### Error de Relación
```php
return $this->errorResponse(
    'La persona ya está asignada a esta casa',
    400
);
```

---

## 🔍 LOGGING REQUERIDO

### Actividades a registrar:
- ✅ Creación/modificación/eliminación de casas
- ✅ Asignación de personas a casas
- ✅ Eliminación de relaciones persona-casa
- ✅ Consultas de estadísticas
- ✅ Cambios en tipos de relación

---

## 📅 INFORMACIÓN DEL PROMPT
- **Fecha de creación:** 28 de Julio, 2025
- **Servicio:** PersonaCasaService.php
- **Posición en cascada:** Nivel 9 (Relaciones Avanzadas)
- **Estado:** ✅ Listo para implementación

---

## 🎯 INSTRUCCIONES PARA COPILOT

Al generar código para PersonaCasaService.php:

1. **SIEMPRE heredar de BaseAdminService**
2. **USAR métodos de Persona.php y Casa.php**
3. **VALIDAR ownership del condominio en TODAS las operaciones**
4. **PERMITIR múltiples personas por casa**
5. **PROPORCIONAR CRUD completo de casas**
6. **GESTIONAR tipos de relación apropiadamente**
7. **ACTUALIZAR contadores de residentes automáticamente**
8. **REGISTRAR logs de todas las actividades**
9. **VALIDAR relaciones antes de eliminar casas**
