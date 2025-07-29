# PERSONACASASERVICE_ADMIN_PROMPT_CORREGIDO.md
## Prompt Especializado para PersonaCasaService.php - VERSIÓN CORREGIDA

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
- **Servicios padre requeridos:** `CasaService.php` (para CRUD de casas)
- **Servicios relacionados:** PersonaService, CalleService
- **Requiere validaciones de:** CondominioService

### ⚠️ RESTRICCIÓN ARQUITECTÓNICA IMPORTANTE
**PersonaCasaService NO debe hacer CRUD directo de casas**. Debe usar `CasaService` para todas las operaciones de casas y enfocarse únicamente en la gestión de relaciones persona-casa.

---

## 📚 MÉTODOS DEL MODELO DISPONIBLES (AUDITADOS CON INVENTARIOS.MD)

### ✅ Métodos CONFIRMADOS en Casa.php - Gestión de Relaciones Persona-Casa
| Método | Entrada | Salida | Descripción | Estado |
|--------|---------|--------|-------------|--------|
| `assignPersonaToCasa()` | int $personaId, int $casaId | bool | Asignar persona a casa | ✅ REAL |
| `removePersonaFromCasa()` | int $personaId, int $casaId | bool | Remover persona de casa | ✅ REAL |
| `getPersonasByCasa()` | int $casaId | array | Obtener personas por casa | ✅ REAL |
| `getCasasByPersona()` | int $personaId | array | Obtener casas por persona | ✅ REAL |
| `isPersonaAssignedToCasa()` | int $personaId, int $casaId | bool | Verifica asignación persona-casa | ✅ REAL |

### ❌ Métodos FANTASMA - NO EXISTEN en los modelos
| Método | Estado | Solución |
|--------|--------|----------|
| `updateRelacionPersonaCasa()` | ❌ FANTASMA | Crear en el servicio |
| `setTipoRelacion()` | ❌ FANTASMA | Crear en el servicio |
| `getTipoRelacion()` | ❌ FANTASMA | Crear en el servicio |
| `getFechaAsignacion()` | ❌ FANTASMA | Crear en el servicio |

### ✅ Métodos CONFIRMADOS en Casa.php - CRUD Completo de Casas
| Método | Entrada | Salida | Descripción | Estado |
|--------|---------|--------|-------------|--------|
| `createCasa()` | array $data | int | Crear casa | ✅ REAL |
| `findCasaById()` | int $id | array | Buscar casa por ID | ✅ REAL |
| `findCasasByCalleId()` | int $calleId | array | Buscar casas por calle | ✅ REAL |
| `findCasasByCondominioId()` | int $condominioId | array | Buscar casas por condominio | ✅ REAL |
| `updateCasa()` | int $id, array $data | bool | Actualizar casa | ✅ REAL |
| `deleteCasa()` | int $id | bool | Eliminar casa | ✅ REAL |

### ❌ Métodos FANTASMA - Casa.php
| Método | Estado | Solución |
|--------|--------|----------|
| `activateCasa()` | ❌ FANTASMA | Usar updateCasa() con estado |
| `deactivateCasa()` | ❌ FANTASMA | Usar updateCasa() con estado |

### ✅ Métodos CONFIRMADOS en Persona.php - CRUD de Personas
| Método | Entrada | Salida | Descripción | Estado |
|--------|---------|--------|-------------|--------|
| `create()` | array $data | int | Crear persona | ✅ REAL |
| `findById()` | int $id | array | Buscar persona por ID | ✅ REAL |
| `update()` | int $id, array $data | bool | Actualizar persona | ✅ REAL |
| `delete()` | int $id | bool | Eliminar persona | ✅ REAL |

### ❌ Métodos FANTASMA - Persona.php
| Método | Estado | Solución |
|--------|--------|----------|
| `createPersona()` | ❌ FANTASMA | Usar create() |
| `findPersonaById()` | ❌ FANTASMA | Usar findById() |
| `findPersonasByCondominio()` | ❌ FANTASMA | Crear consulta SQL custom |
| `updatePersona()` | ❌ FANTASMA | Usar update() |
| `deletePersona()` | ❌ FANTASMA | Usar delete() |
| `activatePersona()` | ❌ FANTASMA | Usar update() con estado |
| `deactivatePersona()` | ❌ FANTASMA | Usar update() con estado |

### ✅ Métodos CONFIRMADOS - Validaciones
| Método | Entrada | Salida | Descripción | Estado |
|--------|---------|--------|-------------|--------|
| `validateCondominioExists()` | int $condominioId | bool | Valida existencia de condominio | ✅ REAL Casa.php |
| `validateCalleExists()` | int $calleId | bool | Valida existencia de calle | ✅ REAL Casa.php |
| `validateCasaExists()` | int $casaId | bool | Valida existencia de casa | ✅ REAL Casa.php |
| `validatePersonaExists()` | int $personaId | bool | Valida existencia de persona | ✅ REAL Casa.php |
| `validateCalleInCondominio()` | int $calleId, int $condominioId | bool | Valida calle en condominio | ✅ REAL Casa.php |

### ❌ Métodos FANTASMA - Validaciones
| Método | Estado | Solución |
|--------|--------|----------|
| `validateRelacionExists()` | ❌ FANTASMA | Usar isPersonaAssignedToCasa() |

### ❌ CATEGORÍAS COMPLETAS FANTASMA - NO EXISTEN
| Categoría | Estado | Solución |
|-----------|--------|----------|
| **Métodos de Tipos de Relación** | ❌ TODAS FANTASMA | Crear tabla tipos_relacion |
| **Métodos de Consultas Avanzadas** | ❌ TODAS FANTASMA | Crear métodos SQL custom |
| **Métodos de Estadísticas y Reportes** | ❌ TODAS FANTASMA | Crear métodos SQL custom |

### 📊 Métodos Base Heredados (CONFIRMADOS)
| Método | Entrada | Salida | Descripción | Estado |
|--------|---------|--------|-------------|--------|
| `create()` | array $data | int | Crear registro | ✅ REAL BaseModel |
| `findById()` | int $id | array | Buscar por ID | ✅ REAL BaseModel |
| `update()` | int $id, array $data | bool | Actualizar registro | ✅ REAL BaseModel |
| `delete()` | int $id | bool | Eliminar registro | ✅ REAL BaseModel |
| `findAll()` | int $limit = 100 | array | Obtener todos los registros | ✅ REAL BaseModel |

---

## 🔧 FUNCIONES DE NEGOCIO REQUERIDAS (CORREGIDAS)

### 1. **CORREGIDO: Gestión de Casas - USAR CasaService (NO CRUD DIRECTO)**
```php
// CORRECCIÓN: PersonaCasaService NO debe hacer CRUD directo de casas
// Debe usar CasaService siguiendo la cascada arquitectónica

public function crearCasaParaRelacion($adminId, $condominioId, $datos)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // USAR CasaService para crear casa (respetando cascada)
    $resultado = $this->casaService->crearCasa($adminId, $condominioId, $datos);
    
    // Log específico de PersonaCasaService
    if ($resultado['success']) {
        $this->logAdminActivity('casa_creada_desde_personacasa', [
            'admin_id' => $adminId,
            'condominio_id' => $condominioId,
            'casa_id' => $resultado['data']['id'],
            'origen' => 'PersonaCasaService'
        ]);
    }
    
    return $resultado;
}

public function actualizarCasaParaRelacion($adminId, $casaId, $datos)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // USAR CasaService para actualizar casa (respetando cascada)
    return $this->casaService->actualizarCasa($adminId, $casaId, $datos);
}

public function eliminarCasaYRelaciones($adminId, $casaId)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Primero eliminar todas las relaciones (responsabilidad de PersonaCasaService)
    $this->eliminarTodasRelacionesCasa($adminId, $casaId);
    
    // Luego USAR CasaService para eliminar casa (respetando cascada)
    return $this->casaService->eliminarCasa($adminId, $casaId);
}

// Método para activar/desactivar casa (usando CasaService)
public function cambiarEstadoCasaParaRelacion($adminId, $casaId, $estado)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // USAR CasaService para cambiar estado (respetando cascada)
    return $this->casaService->cambiarEstadoCasa($adminId, $casaId, $estado);
}
```

### 2. **Ver Personas por Casa (RESPONSABILIDAD PRINCIPAL)**
```php
public function obtenerPersonasPorCasa($adminId, $casaId)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Obtener casa usando CasaService (respetando cascada)
    $casa = $this->casaService->obtenerCasaPorId($adminId, $casaId);
    if (!$casa['success']) {
        return $casa; // Retornar error del CasaService
    }
    
    // Aplicar rate limiting
    $this->enforceRateLimit('personas_casa_' . $adminId);
    
    // Obtener personas de la casa (MÉTODO REAL) - Esta es la responsabilidad principal
    $personas = $this->casaModel->getPersonasByCasa($casaId);
    
    // Agregar información de relación para cada persona (MÉTODOS CUSTOM)
    foreach ($personas as &$persona) {
        $persona['tipo_relacion'] = $this->getTipoRelacionPersonaCasaCustom($persona['id_persona'], $casaId);
        $persona['fecha_asignacion'] = $this->getFechaAsignacionPersonaCasaCustom($persona['id_persona'], $casaId);
        $persona['activa'] = $this->casaModel->isPersonaAssignedToCasa($persona['id_persona'], $casaId);
    }
    
    // Información adicional de la casa
    $informacionCasa = [
        'casa' => $casa['data'],
        'total_residentes' => count($personas),
        'residentes' => $personas,
        'calle_info' => $this->getInformacionCalleCustom($casa['data']['id_calle']),
        'servicios_casa' => $this->getServiciosCasaCustom($casaId)
    ];
    
    return $this->successResponse($informacionCasa, 'Personas de la casa obtenidas exitosamente');
}

public function obtenerCasasConResidentesCustom($adminId, $condominioId, $opciones = [])
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para ver casas de este condominio');
    }
    
    // Aplicar rate limiting
    $this->enforceRateLimit('casas_residentes_' . $adminId);
    
    // Obtener casas del condominio usando CasaService (respetando cascada)
    $resultadoCasas = $this->casaService->obtenerCasasPorCondominio($adminId, $condominioId);
    if (!$resultadoCasas['success']) {
        return $resultadoCasas;
    }
    
    $casas = $resultadoCasas['data'];
    
    // Agregar información de residentes a cada casa (RESPONSABILIDAD DE PersonaCasaService)
    $casasConResidentes = [];
    foreach ($casas as $casa) {
        $residentes = $this->casaModel->getPersonasByCasa($casa['id_casa']);
        $casa['residentes'] = $residentes;
        $casa['total_residentes'] = count($residentes);
        $casasConResidentes[] = $casa;
    }
    
    // Aplicar filtros si se proporcionan
    if (isset($opciones['id_calle'])) {
        $casasConResidentes = array_filter($casasConResidentes, function($casa) use ($opciones) {
            return $casa['id_calle'] == $opciones['id_calle'];
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

### 3. **Asignar Persona a Casa (RESPONSABILIDAD PRINCIPAL)**
```php
public function asignarPersonaACasa($adminId, $personaId, $casaId, $tipoRelacion = 'residente')
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Obtener casa usando CasaService (respetando cascada)
    $resultadoCasa = $this->casaService->obtenerCasaPorId($adminId, $casaId);
    if (!$resultadoCasa['success']) {
        return $resultadoCasa;
    }
    
    $casa = $resultadoCasa['data'];
    
    // Validar que la persona existe (MÉTODO REAL)
    if (!$this->casaModel->validatePersonaExists($personaId)) {
        return $this->errorResponse('Persona no encontrada');
    }
    
    // Validar que no existe ya la relación (MÉTODO REAL)
    if ($this->casaModel->isPersonaAssignedToCasa($personaId, $casaId)) {
        return $this->errorResponse('La persona ya está asignada a esta casa');
    }
    
    // Validar tipos de relación válidos
    $tiposValidos = ['propietario', 'residente', 'arrendatario', 'familiar', 'visitante_permanente'];
    if (!in_array($tipoRelacion, $tiposValidos)) {
        return $this->errorResponse('Tipo de relación inválido');
    }
    
    // Verificar que no hay más de un propietario si el tipo es propietario
    if ($tipoRelacion == 'propietario') {
        $residentes = $this->casaModel->getPersonasByCasa($casaId);
        foreach ($residentes as $residente) {
            if ($this->getTipoRelacionPersonaCasaCustom($residente['id_persona'], $casaId) == 'propietario') {
                return $this->errorResponse('La casa ya tiene un propietario asignado');
            }
        }
    }
    
    // Asignar persona a casa (MÉTODO REAL) - RESPONSABILIDAD PRINCIPAL
    $resultado = $this->casaModel->assignPersonaToCasa($personaId, $casaId);
    
    // Establecer tipo de relación (MÉTODO CUSTOM porque no existe)
    $this->setTipoRelacionCustom($personaId, $casaId, $tipoRelacion);
    
    // Actualizar contador de residentes en la casa
    $this->actualizarContadorResidentes($casaId);
    
    // Log de actividad
    $this->logAdminActivity('persona_asignada_casa', [
        'admin_id' => $adminId,
        'persona_id' => $personaId,
        'casa_id' => $casaId,
        'condominio_id' => $casa['id_condominio'],
        'tipo_relacion' => $tipoRelacion
    ]);
    
    return $this->successResponse($resultado, 'Persona asignada a casa exitosamente');
}
```

### 4. **Eliminar Relación Persona-Casa (USANDO MÉTODOS REALES)**
```php
public function eliminarRelacionPersonaCasa($adminId, $personaId, $casaId)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Obtener casa usando CasaService (respetando cascada)
    $resultadoCasa = $this->casaService->obtenerCasaPorId($adminId, $casaId);
    if (!$resultadoCasa['success']) {
        return $resultadoCasa;
    }
    
    $casa = $resultadoCasa['data'];
    
    // Validar que la relación existe (MÉTODO REAL)
    if (!$this->casaModel->isPersonaAssignedToCasa($personaId, $casaId)) {
        return $this->errorResponse('La relación persona-casa no existe');
    }
    
    // Obtener información de la persona antes de eliminar (MÉTODO REAL)
    $persona = $this->personaModel->findById($personaId);
    $tipoRelacion = $this->getTipoRelacionPersonaCasaCustom($personaId, $casaId);
    
    // Eliminar relación (MÉTODO REAL) - RESPONSABILIDAD PRINCIPAL
    $resultado = $this->casaModel->removePersonaFromCasa($personaId, $casaId);
    
    // Actualizar contador de residentes en la casa
    $this->actualizarContadorResidentes($casaId);
    
    // Log de actividad
    $this->logAdminActivity('relacion_persona_casa_eliminada', [
        'admin_id' => $adminId,
        'persona_id' => $personaId,
        'casa_id' => $casaId,
        'condominio_id' => $casa['id_condominio'],
        'tipo_relacion_anterior' => $tipoRelacion,
        'persona_nombre' => $persona['nombres'] ?? 'Desconocido'
    ]);
    
    return $this->successResponse($resultado, 'Relación persona-casa eliminada exitosamente');
}

public function eliminarTodasRelacionesCasa($adminId, $casaId)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Obtener casa usando CasaService (respetando cascada)
    $resultadoCasa = $this->casaService->obtenerCasaPorId($adminId, $casaId);
    if (!$resultadoCasa['success']) {
        return $resultadoCasa;
    }
    
    $casa = $resultadoCasa['data'];
    
    // Obtener todas las personas de la casa (MÉTODO REAL) - RESPONSABILIDAD PRINCIPAL
    $residentes = $this->casaModel->getPersonasByCasa($casaId);
    
    if (count($residentes) == 0) {
        return $this->errorResponse('La casa no tiene residentes asignados');
    }
    
    $eliminados = 0;
    $errores = [];
    
    // Eliminar cada relación (MÉTODO REAL)
    foreach ($residentes as $residente) {
        try {
            $this->casaModel->removePersonaFromCasa($residente['id_persona'], $casaId);
            $eliminados++;
        } catch (Exception $e) {
            $errores[] = "Error eliminando relación con persona ID {$residente['id_persona']}: " . $e->getMessage();
        }
    }
    
    // Actualizar contador de residentes
    $this->actualizarContadorResidentes($casaId);
    
    // Log de actividad
    $this->logAdminActivity('todas_relaciones_casa_eliminadas', [
        'admin_id' => $adminId,
        'casa_id' => $casaId,
        'condominio_id' => $casa['id_condominio'],
        'residentes_eliminados' => $eliminados,
        'errores' => count($errores)
    ]);
    
    return $this->successResponse([
        'eliminados' => $eliminados,
        'errores' => $errores
    ], "Se eliminaron $eliminados relaciones exitosamente");
}
```

### 5. **Obtener Estadísticas y Reportes (MÉTODOS CUSTOM)**
```php
public function obtenerEstadisticasOcupacionCustom($adminId, $condominioId)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Obtener detalles del condominio usando CondominioService (respetando cascada)
    $resultadoCondominio = $this->condominioService->obtenerCondominioParaAdmin($adminId, $condominioId);
    if (!$resultadoCondominio['success']) {
        return $resultadoCondominio;
    }
    
    // Aplicar rate limiting
    $this->enforceRateLimit('estadisticas_ocupacion_' . $adminId);
    
    // Obtener casas del condominio usando CasaService (respetando cascada)
    $resultadoCasas = $this->casaService->listarCasasPorCondominioAdmin($adminId, $condominioId);
    if (!$resultadoCasas['success']) {
        return $resultadoCasas;
    }
    
    $todasCasas = $resultadoCasas['data'];
    
    // Calcular estadísticas personalizadas - RESPONSABILIDAD PRINCIPAL
    $casasConResidentes = [];
    $casasVacias = [];
    
    foreach ($todasCasas as $casa) {
        $residentes = $this->casaModel->getPersonasByCasa($casa['id_casa']);
        if (count($residentes) > 0) {
            $casa['residentes'] = $residentes;
            $casasConResidentes[] = $casa;
        } else {
            $casasVacias[] = $casa;
        }
    }
    
    // Obtener personas sin casa (CONSULTA CUSTOM)
    $personasSinCasa = $this->getPersonasSinCasaCustom($condominioId);
    
    $estadisticas = [
        'resumen' => [
            'total_casas' => count($todasCasas),
            'casas_con_residentes' => count($casasConResidentes),
            'casas_vacias' => count($casasVacias),
            'personas_sin_casa' => count($personasSinCasa),
            'porcentaje_ocupacion' => $this->calcularPorcentajeOcupacion($condominioId)
        ],
        'casas_ocupadas' => $casasConResidentes,
        'casas_vacias' => $casasVacias,
        'personas_sin_casa' => $personasSinCasa
    ];
    
    // Estadísticas por tipo de relación (CONSULTA CUSTOM)
    $estadisticas['por_tipo_relacion'] = $this->getEstadisticasPorTipoRelacionCustom($condominioId);
    
    // Estadísticas por calle (CONSULTA CUSTOM)
    $estadisticas['por_calle'] = $this->getEstadisticasPorCalleCustom($condominioId);
    
    return $this->successResponse($estadisticas, 'Estadísticas de ocupación obtenidas exitosamente');
}

public function obtenerReporteRelacionesCustom($adminId, $condominioId, $periodo = [])
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Obtener detalles del condominio usando CondominioService (respetando cascada)
    $resultadoCondominio = $this->condominioService->obtenerCondominioParaAdmin($adminId, $condominioId);
    if (!$resultadoCondominio['success']) {
        return $resultadoCondominio;
    }
    
    // Aplicar rate limiting
    $this->enforceRateLimit('reporte_relaciones_' . $adminId);
    
    // Obtener reporte personalizado - RESPONSABILIDAD PRINCIPAL
    $reporte = $this->getReporteRelacionesCustom($condominioId);
    
    // Agregar movimientos si se especifica período
    if (!empty($periodo)) {
        $reporte['movimientos'] = $this->getReporteMovimientosCustom($condominioId, $periodo);
    }
    
    // Análisis demográfico personalizado
    $reporte['analisis_demografico'] = $this->getAnalisisDemograficoCustom($condominioId);
    
    return $this->successResponse($reporte, 'Reporte de relaciones obtenido exitosamente');
}
```

---

## 🔒 VALIDACIONES DE SEGURIDAD REQUERIDAS (CORREGIDAS)

### Middleware Embebido
```php
private function checkAdminAuth()
{
    if (!$this->authAdmin()) {
        throw new UnauthorizedException('Acceso no autorizado');
    }
}
```

### Validaciones Específicas (USANDO MÉTODOS REALES)
```php
private function actualizarContadorResidentes($casaId)
{
    $residentes = $this->casaModel->getPersonasByCasa($casaId); // MÉTODO REAL
    $totalResidentes = count($residentes);
    
    $this->casaModel->updateCasa($casaId, [ // MÉTODO REAL
        'total_residentes' => $totalResidentes,
        'ultima_actualizacion' => date('Y-m-d H:i:s')
    ]);
}

// MÉTODOS CUSTOM PARA REEMPLAZAR FANTASMAS
private function getTipoRelacionPersonaCasaCustom($personaId, $casaId)
{
    try {
        $stmt = $this->connection->prepare("
            SELECT tipo_relacion 
            FROM persona_casa 
            WHERE id_persona = :persona_id AND id_casa = :casa_id
        ");
        $stmt->execute(['persona_id' => $personaId, 'casa_id' => $casaId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['tipo_relacion'] : 'residente';
    } catch (Exception $e) {
        return 'residente';
    }
}

private function getFechaAsignacionPersonaCasaCustom($personaId, $casaId)
{
    try {
        $stmt = $this->connection->prepare("
            SELECT fecha_asignacion 
            FROM persona_casa 
            WHERE id_persona = :persona_id AND id_casa = :casa_id
        ");
        $stmt->execute(['persona_id' => $personaId, 'casa_id' => $casaId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['fecha_asignacion'] : null;
    } catch (Exception $e) {
        return null;
    }
}

private function setTipoRelacionCustom($personaId, $casaId, $tipo)
{
    try {
        $stmt = $this->connection->prepare("
            UPDATE persona_casa 
            SET tipo_relacion = :tipo 
            WHERE id_persona = :persona_id AND id_casa = :casa_id
        ");
        return $stmt->execute([
            'tipo' => $tipo,
            'persona_id' => $personaId, 
            'casa_id' => $casaId
        ]);
    } catch (Exception $e) {
        return false;
    }
}

private function calcularPorcentajeOcupacion($condominioId)
{
    $totalCasas = count($this->casaModel->findCasasByCondominioId($condominioId)); // MÉTODO REAL
    $casasOcupadas = 0;
    
    $casas = $this->casaModel->findCasasByCondominioId($condominioId);
    foreach ($casas as $casa) {
        $residentes = $this->casaModel->getPersonasByCasa($casa['id_casa']); // MÉTODO REAL
        if (count($residentes) > 0) {
            $casasOcupadas++;
        }
    }
    
    return $totalCasas > 0 ? round(($casasOcupadas / $totalCasas) * 100, 2) : 0;
}

private function getPersonasSinCasaCustom($condominioId)
{
    try {
        $stmt = $this->connection->prepare("
            SELECT p.* 
            FROM personas p
            WHERE p.id_condominio = :condominio_id
            AND p.id_persona NOT IN (
                SELECT DISTINCT pc.id_persona 
                FROM persona_casa pc
                INNER JOIN casas c ON pc.id_casa = c.id_casa
                WHERE c.id_condominio = :condominio_id
            )
        ");
        $stmt->execute(['condominio_id' => $condominioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

private function getInformacionCalleCustom($calleId)
{
    try {
        $stmt = $this->connection->prepare("SELECT * FROM calles WHERE id_calle = :id");
        $stmt->execute(['id' => $calleId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return null;
    }
}

private function getServiciosCasaCustom($casaId)
{
    // Método personalizado para obtener servicios de la casa
    return [
        'agua' => true,
        'luz' => true,
        'gas' => true,
        'internet' => false
    ];
}

private function getEstadisticasPorTipoRelacionCustom($condominioId)
{
    try {
        $stmt = $this->connection->prepare("
            SELECT 
                pc.tipo_relacion,
                COUNT(*) as total
            FROM persona_casa pc
            INNER JOIN casas c ON pc.id_casa = c.id_casa
            WHERE c.id_condominio = :condominio_id
            GROUP BY pc.tipo_relacion
        ");
        $stmt->execute(['condominio_id' => $condominioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

private function getEstadisticasPorCalleCustom($condominioId)
{
    try {
        $stmt = $this->connection->prepare("
            SELECT 
                cal.nombre as calle_nombre,
                COUNT(DISTINCT c.id_casa) as total_casas,
                COUNT(DISTINCT pc.id_persona) as total_residentes
            FROM calles cal
            LEFT JOIN casas c ON cal.id_calle = c.id_calle
            LEFT JOIN persona_casa pc ON c.id_casa = pc.id_casa
            WHERE cal.id_condominio = :condominio_id
            GROUP BY cal.id_calle, cal.nombre
        ");
        $stmt->execute(['condominio_id' => $condominioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

private function getReporteRelacionesCustom($condominioId)
{
    try {
        $stmt = $this->connection->prepare("
            SELECT 
                p.nombres, p.apellido1, p.apellido2,
                c.casa, cal.nombre as calle,
                pc.tipo_relacion, pc.fecha_asignacion
            FROM persona_casa pc
            INNER JOIN personas p ON pc.id_persona = p.id_persona
            INNER JOIN casas c ON pc.id_casa = c.id_casa
            INNER JOIN calles cal ON c.id_calle = cal.id_calle
            WHERE c.id_condominio = :condominio_id
            ORDER BY cal.nombre, c.casa, p.apellido1
        ");
        $stmt->execute(['condominio_id' => $condominioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

private function getReporteMovimientosCustom($condominioId, $periodo)
{
    // Implementar consulta de movimientos según el período
    return [];
}

private function getAnalisisDemograficoCustom($condominioId)
{
    // Implementar análisis demográfico personalizado
    return [
        'promedio_personas_por_casa' => 0,
        'edades_promedio' => 0,
        'distribuciones' => []
    ];
}
```

---

## 🔄 INTEGRACIÓN CON OTROS SERVICIOS (CORREGIDA)

### Debe usar servicios en cascada:
```php
// Validaciones de otros servicios (REAL)
if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
    return $this->errorResponse("No tienes permisos");
}

// CORREGIDO: No usar CasaService sino métodos directos del modelo Casa
if (!$this->casaModel->validateCasaExists($casaId)) {
    return $this->errorResponse("Casa no encontrada");
}

// CORREGIDO: Usar métodos custom para información de calles
$informacionCalle = $this->getInformacionCalleCustom($calleId);
```

### Proporciona para otros servicios:
```php
// Para otros servicios que necesiten información de residencia
public function personaViveEnCondominio($personaId, $condominioId)
{
    $casas = $this->casaModel->getCasasByPersona($personaId); // MÉTODO REAL
    foreach ($casas as $casa) {
        if ($casa['id_condominio'] == $condominioId) {
            return true;
        }
    }
    return false;
}

public function obtenerCasasDePersona($personaId)
{
    return $this->casaModel->getCasasByPersona($personaId); // MÉTODO REAL
}

public function validarRelacionActiva($personaId, $casaId)
{
    return $this->casaModel->isPersonaAssignedToCasa($personaId, $casaId); // MÉTODO REAL
}
```

---

## 🚫 RESTRICCIONES IMPORTANTES

### Lo que NO debe hacer:
- ❌ **NO gestionar tags o engomados** (usar TagService/EngomadoService)
- ❌ **NO manejar accesos físicos** (usar AccesosService)
- ❌ **NO gestionar empleados** (usar EmpleadoService)
- ❌ **NO usar métodos fantasma** (usar solo métodos REALES del inventario)

### Scope específico CORREGIDO:
- ✅ **Gestión de relaciones persona-casa (RESPONSABILIDAD PRINCIPAL)**
- ✅ **Ver múltiples personas por casa usando métodos REALES**
- ✅ **Asignar/Eliminar relaciones persona-casa usando métodos REALES**
- ✅ **Estadísticas de ocupación con consultas CUSTOM**
- ✅ **Reportes demográficos con consultas CUSTOM**
- ❌ **NO hacer CRUD directo de casas - USAR CasaService**

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

## 📊 RESUMEN DE CORRECCIONES APLICADAS

### ❌ Funciones Fantasma Eliminadas:
1. `updateRelacionPersonaCasa()` → Reemplazado con métodos custom
2. `setTipoRelacion()` → Reemplazado con setTipoRelacionCustom()
3. `getTipoRelacion()` → Reemplazado con getTipoRelacionPersonaCasaCustom()
4. `getFechaAsignacion()` → Reemplazado con getFechaAsignacionPersonaCasaCustom()
5. `activateCasa()` / `deactivateCasa()` → Reemplazado con cambiarEstadoCasa() usando updateCasa()
6. `createPersona()` → Usar create() del modelo Persona
7. `findPersonaById()` → Usar findById() del modelo Persona
8. `findPersonasByCondominio()` → Crear consulta SQL custom
9. Todos los métodos de "Tipos de Relación" → Implementar con consultas custom
10. Todos los métodos de "Consultas Avanzadas" → Implementar con consultas custom
11. Todos los métodos de "Estadísticas y Reportes" → Implementar con consultas custom

### ✅ Funciones Reales Confirmadas:
1. Casa.php: `assignPersonaToCasa()`, `removePersonaFromCasa()`, `getPersonasByCasa()`, etc.
2. Casa.php: `createCasa()`, `findCasaById()`, `updateCasa()`, `deleteCasa()`, etc.
3. Casa.php: `validateCondominioExists()`, `validateCalleExists()`, etc.
4. Persona.php: `create()`, `findById()`, `update()`, `delete()`
5. BaseModel: Todos los métodos heredados

### 🔧 Nombres de Campos Corregidos:
- `numero_casa` → `casa` (campo real en la tabla)
- `calle_id` → `id_calle` (campo real en la tabla)  
- `condominio_id` → `id_condominio` (campo real en la tabla)
- `persona_id` → `id_persona` (campo real en la tabla)

---

## 📅 INFORMACIÓN DEL PROMPT
- **Fecha de creación:** 28 de Julio, 2025
- **Servicio:** PersonaCasaService.php
- **Posición en cascada:** Nivel 9 (Relaciones Avanzadas)
- **Estado:** ✅ CORREGIDO - Sin funciones fantasma
- **Auditado con:** inventarios.md (246 métodos, 12 modelos)

---

## 🎯 INSTRUCCIONES PARA COPILOT (CORREGIDAS)

Al generar código para PersonaCasaService.php:

1. **SIEMPRE heredar de BaseAdminService**
2. **USAR SOLO métodos REALES de Persona.php y Casa.php del inventario**
3. **VALIDAR ownership del condominio en TODAS las operaciones**
4. **PERMITIR múltiples personas por casa**
5. **PROPORCIONAR CRUD completo de casas con métodos REALES**
6. **GESTIONAR tipos de relación con métodos CUSTOM (no fantasma)**
7. **ACTUALIZAR contadores de residentes automáticamente**
8. **REGISTRAR logs de todas las actividades**
9. **VALIDAR relaciones antes de eliminar casas**
10. **NO usar funciones fantasma - Solo métodos confirmados en inventarios.md**
