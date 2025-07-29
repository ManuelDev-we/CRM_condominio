# PERSONAUNIDADSERVICE_ADMIN_PROMPT.md
## Prompt Especializado para PersonaUnidadService.php

### 🎯 PROPÓSITO DEL SERVICIO
Administrar las relaciones entre personas y unidades habitacionales/comerciales dentro de un condominio. Gestiona la asignación de personas a diferentes tipos de unidades (apartamentos, locales comerciales, oficinas), tipos de tenencia (propietario, arrendatario, ocupante) y permisos específicos por unidad.

---

## 🏗️ ARQUITECTURA Y HERENCIA

### Clase Base
```php
class PersonaUnidadService extends BaseAdminService
```

### Dependencias
- **Hereda de:** `BaseAdminService.php`
- **Modelos principales:** `Persona.php`, `Casa.php` (como unidad base)
- **Posición en cascada:** Nivel 10 (Relaciones Complejas)
- **Servicios relacionados:** PersonaService, CasaService, PersonaCasaService
- **Requiere validaciones de:** CondominioService, CalleService

---

## 📚 MÉTODOS DEL MODELO DISPONIBLES

### Métodos de Gestión de Relaciones Persona-Unidad
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `assignPersonaToUnidad()` | int $personaId, int $unidadId | bool | Asignar persona a unidad |
| `removePersonaFromUnidad()` | int $personaId, int $unidadId | bool | Remover persona de unidad |
| `getPersonasByUnidad()` | int $unidadId | array | Obtener personas por unidad |
| `getUnidadesByPersona()` | int $personaId | array | Obtener unidades por persona |
| `updateRelacionPersonaUnidad()` | int $personaId, int $unidadId, array $data | bool | Actualizar relación |
| `isPersonaAssignedToUnidad()` | int $personaId, int $unidadId | bool | Verificar asignación |

### Métodos de Tipos de Tenencia
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `setTipoTenencia()` | int $personaId, int $unidadId, string $tipo | bool | Establecer tipo de tenencia |
| `getTipoTenencia()` | int $personaId, int $unidadId | string | Obtener tipo de tenencia |
| `updateTenenciaInfo()` | int $personaId, int $unidadId, array $info | bool | Actualizar información de tenencia |
| `setFechaInicioTenencia()` | int $personaId, int $unidadId, date $fecha | bool | Establecer fecha inicio |
| `setFechaFinTenencia()` | int $personaId, int $unidadId, date $fecha | bool | Establecer fecha fin |
| `validateTenenciaActiva()` | int $personaId, int $unidadId | bool | Validar tenencia activa |

### Métodos de Gestión de Unidades
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `createUnidad()` | array $data | int | Crear unidad |
| `findUnidadById()` | int $id | array | Buscar unidad por ID |
| `findUnidadesByCondominio()` | int $condominioId | array | Buscar unidades por condominio |
| `findUnidadesByTipo()` | string $tipo, int $condominioId | array | Buscar unidades por tipo |
| `updateUnidad()` | int $id, array $data | bool | Actualizar unidad |
| `deleteUnidad()` | int $id | bool | Eliminar unidad |
| `activateUnidad()` | int $id | bool | Activar unidad |
| `deactivateUnidad()` | int $id | bool | Desactivar unidad |

### Métodos de Tipos de Unidad
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `createTipoUnidad()` | array $data | int | Crear tipo de unidad |
| `getTiposUnidad()` | int $condominioId | array | Obtener tipos de unidad |
| `updateTipoUnidad()` | int $id, array $data | bool | Actualizar tipo de unidad |
| `deleteTipoUnidad()` | int $id | bool | Eliminar tipo de unidad |
| `setPermisosUnidad()` | int $tipoId, array $permisos | bool | Establecer permisos por tipo |
| `getPermisosUnidad()` | int $tipoId | array | Obtener permisos de tipo |

### Métodos de Contratos y Documentos
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `createContrato()` | array $data | int | Crear contrato |
| `getContratosByUnidad()` | int $unidadId | array | Obtener contratos por unidad |
| `getContratosByPersona()` | int $personaId | array | Obtener contratos por persona |
| `updateContrato()` | int $contratoId, array $data | bool | Actualizar contrato |
| `expireContrato()` | int $contratoId | bool | Expirar contrato |
| `renewContrato()` | int $contratoId, array $data | bool | Renovar contrato |

### Métodos de Validación
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `validateCondominioExists()` | int $condominioId | bool | Valida existencia de condominio |
| `validatePersonaExists()` | int $personaId | bool | Valida existencia de persona |
| `validateUnidadExists()` | int $unidadId | bool | Valida existencia de unidad |
| `validateTipoUnidadExists()` | int $tipoId | bool | Valida existencia de tipo unidad |
| `validateRelacionExists()` | int $personaId, int $unidadId | bool | Valida existencia de relación |
| `validateUnidadDisponible()` | int $unidadId, string $tipoTenencia | bool | Valida disponibilidad de unidad |

### Métodos de Consultas Avanzadas
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `getUnidadesConOcupantes()` | int $condominioId | array | Unidades con sus ocupantes |
| `getUnidadesDisponibles()` | int $condominioId, string $tipo | array | Unidades disponibles |
| `getPersonasSinUnidad()` | int $condominioId | array | Personas sin unidad |
| `getOcupacionByTipoUnidad()` | int $condominioId | array | Ocupación por tipo de unidad |
| `getHistorialOcupacion()` | int $unidadId | array | Historial de ocupación |

### Métodos de Estadísticas y Reportes
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `getEstadisticasOcupacion()` | int $condominioId | array | Estadísticas de ocupación |
| `getReporteIngresos()` | int $condominioId, array $periodo | array | Reporte de ingresos |
| `getReporteVencimientos()` | int $condominioId | array | Reporte de vencimientos |
| `getAnalisisDemografico()` | int $condominioId | array | Análisis demográfico |
| `getRotacionUnidades()` | int $condominioId, array $periodo | array | Rotación de unidades |

### Métodos de Notificaciones
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `notifyTenenciaExpiring()` | int $unidadId, int $diasAntelacion | bool | Notificar vencimiento próximo |
| `notifyNewOccupant()` | int $unidadId, int $personaId | bool | Notificar nuevo ocupante |
| `notifyOccupantLeft()` | int $unidadId, int $personaId | bool | Notificar salida de ocupante |
| `sendRenewalReminder()` | int $contratoId | bool | Enviar recordatorio renovación |

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

### 1. **Crear y Gestionar Unidades**
```php
public function crearUnidad($adminId, $condominioId, $datos)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para crear unidades en este condominio');
    }
    
    // Validar campos requeridos
    $this->validateRequiredFields($datos, ['numero_unidad', 'tipo_unidad_id', 'calle_id']);
    
    // Validar que el tipo de unidad existe
    if (!$this->personaUnidadModel->validateTipoUnidadExists($datos['tipo_unidad_id'])) {
        return $this->errorResponse('Tipo de unidad no encontrado');
    }
    
    // Validar que la calle existe y pertenece al condominio
    if (!$this->calleService->validarCalleEnCondominio($datos['calle_id'], $condominioId)) {
        return $this->errorResponse('La calle no pertenece a este condominio');
    }
    
    // Verificar que no existe otra unidad con el mismo número en la calle
    $unidadesExistentes = $this->personaUnidadModel->findUnidadesByCondominio($condominioId);
    foreach ($unidadesExistentes as $unidad) {
        if ($unidad['numero_unidad'] == $datos['numero_unidad'] && $unidad['calle_id'] == $datos['calle_id']) {
            return $this->errorResponse('Ya existe una unidad con este número en la calle');
        }
    }
    
    // Preparar datos de la unidad
    $unidadData = [
        'condominio_id' => $condominioId,
        'numero_unidad' => $datos['numero_unidad'],
        'tipo_unidad_id' => $datos['tipo_unidad_id'],
        'calle_id' => $datos['calle_id'],
        'area_m2' => $datos['area_m2'] ?? null,
        'nivel_piso' => $datos['nivel_piso'] ?? 1,
        'estado' => 'disponible',
        'activa' => true,
        'fecha_creacion' => date('Y-m-d H:i:s'),
        'descripcion' => $datos['descripcion'] ?? '',
        'capacidad_maxima' => $datos['capacidad_maxima'] ?? 10,
        'total_ocupantes' => 0
    ];
    
    // Crear unidad
    $unidadId = $this->personaUnidadModel->createUnidad($unidadData);
    
    // Log de actividad
    $this->logAdminActivity('unidad_creada', [
        'admin_id' => $adminId,
        'condominio_id' => $condominioId,
        'unidad_id' => $unidadId,
        'numero_unidad' => $datos['numero_unidad'],
        'tipo_unidad_id' => $datos['tipo_unidad_id']
    ]);
    
    return $this->successResponse(['id' => $unidadId], 'Unidad creada exitosamente');
}
```

### 2. **Asignar Persona a Unidad con Tipo de Tenencia**
```php
public function asignarPersonaAUnidad($adminId, $personaId, $unidadId, $tipoTenencia, $datosAdicionales = [])
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Obtener unidad
    $unidad = $this->personaUnidadModel->findUnidadById($unidadId);
    if (!$unidad) {
        return $this->errorResponse('Unidad no encontrada');
    }
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($unidad['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para asignar personas en esta unidad');
    }
    
    // Validar que la persona existe
    if (!$this->personaUnidadModel->validatePersonaExists($personaId)) {
        return $this->errorResponse('Persona no encontrada');
    }
    
    // Validar tipos de tenencia válidos
    $tiposValidos = ['propietario', 'arrendatario', 'ocupante', 'familiar', 'empleado', 'visitante_permanente'];
    if (!in_array($tipoTenencia, $tiposValidos)) {
        return $this->errorResponse('Tipo de tenencia inválido');
    }
    
    // Validar que no existe ya la relación
    if ($this->personaUnidadModel->isPersonaAssignedToUnidad($personaId, $unidadId)) {
        return $this->errorResponse('La persona ya está asignada a esta unidad');
    }
    
    // Verificar capacidad máxima de la unidad
    $ocupantesActuales = $this->personaUnidadModel->getPersonasByUnidad($unidadId);
    if (count($ocupantesActuales) >= $unidad['capacidad_maxima']) {
        return $this->errorResponse('La unidad ha alcanzado su capacidad máxima de ocupantes');
    }
    
    // Validar restricciones de tipo de tenencia
    if ($tipoTenencia == 'propietario') {
        foreach ($ocupantesActuales as $ocupante) {
            if ($this->personaUnidadModel->getTipoTenencia($ocupante['id'], $unidadId) == 'propietario') {
                return $this->errorResponse('La unidad ya tiene un propietario asignado');
            }
        }
    }
    
    // Asignar persona a unidad
    $resultado = $this->personaUnidadModel->assignPersonaToUnidad($personaId, $unidadId);
    
    if ($resultado) {
        // Establecer tipo de tenencia
        $this->personaUnidadModel->setTipoTenencia($personaId, $unidadId, $tipoTenencia);
        
        // Establecer fechas si se proporcionan
        if (isset($datosAdicionales['fecha_inicio'])) {
            $this->personaUnidadModel->setFechaInicioTenencia($personaId, $unidadId, $datosAdicionales['fecha_inicio']);
        }
        
        if (isset($datosAdicionales['fecha_fin'])) {
            $this->personaUnidadModel->setFechaFinTenencia($personaId, $unidadId, $datosAdicionales['fecha_fin']);
        }
        
        // Crear contrato si se proporciona información
        if (isset($datosAdicionales['crear_contrato']) && $datosAdicionales['crear_contrato']) {
            $contratoData = [
                'persona_id' => $personaId,
                'unidad_id' => $unidadId,
                'tipo_tenencia' => $tipoTenencia,
                'fecha_inicio' => $datosAdicionales['fecha_inicio'] ?? date('Y-m-d'),
                'fecha_fin' => $datosAdicionales['fecha_fin'] ?? null,
                'monto_mensual' => $datosAdicionales['monto_mensual'] ?? 0,
                'deposito_garantia' => $datosAdicionales['deposito_garantia'] ?? 0,
                'observaciones' => $datosAdicionales['observaciones'] ?? '',
                'estado' => 'activo',
                'fecha_creacion' => date('Y-m-d H:i:s')
            ];
            
            $contratoId = $this->personaUnidadModel->createContrato($contratoData);
        }
        
        // Actualizar contador de ocupantes
        $this->actualizarContadorOcupantes($unidadId);
        
        // Actualizar estado de la unidad
        $this->actualizarEstadoUnidad($unidadId);
        
        // Notificar nuevo ocupante
        $this->personaUnidadModel->notifyNewOccupant($unidadId, $personaId);
    }
    
    // Log de actividad
    $this->logAdminActivity('persona_asignada_unidad', [
        'admin_id' => $adminId,
        'persona_id' => $personaId,
        'unidad_id' => $unidadId,
        'condominio_id' => $unidad['condominio_id'],
        'tipo_tenencia' => $tipoTenencia,
        'contrato_creado' => isset($contratoId)
    ]);
    
    return $this->successResponse([
        'resultado' => $resultado,
        'contrato_id' => $contratoId ?? null
    ], 'Persona asignada a unidad exitosamente');
}
```

### 3. **Gestionar Contratos y Tenencias**
```php
public function gestionarContrato($adminId, $accion, $datos)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    $resultado = false;
    $mensaje = '';
    
    switch ($accion) {
        case 'crear_contrato':
            $this->validateRequiredFields($datos, ['persona_id', 'unidad_id', 'tipo_tenencia']);
            
            // Validar ownership
            $unidad = $this->personaUnidadModel->findUnidadById($datos['unidad_id']);
            if (!$this->condominioService->validarOwnership($unidad['condominio_id'], $adminId)) {
                return $this->errorResponse('No tienes permisos para crear contratos en esta unidad');
            }
            
            $contratoData = [
                'persona_id' => $datos['persona_id'],
                'unidad_id' => $datos['unidad_id'],
                'tipo_tenencia' => $datos['tipo_tenencia'],
                'fecha_inicio' => $datos['fecha_inicio'] ?? date('Y-m-d'),
                'fecha_fin' => $datos['fecha_fin'] ?? null,
                'monto_mensual' => $datos['monto_mensual'] ?? 0,
                'deposito_garantia' => $datos['deposito_garantia'] ?? 0,
                'observaciones' => $datos['observaciones'] ?? '',
                'estado' => 'activo',
                'fecha_creacion' => date('Y-m-d H:i:s'),
                'creado_por_admin' => $adminId
            ];
            
            $contratoId = $this->personaUnidadModel->createContrato($contratoData);
            $resultado = ['contrato_id' => $contratoId];
            $mensaje = 'Contrato creado exitosamente';
            break;
            
        case 'renovar_contrato':
            $this->validateRequiredFields($datos, ['contrato_id', 'nueva_fecha_fin']);
            
            // Validar que el contrato existe y el admin tiene permisos
            $contrato = $this->getContratoConValidacion($datos['contrato_id'], $adminId);
            if (!$contrato) {
                return $this->errorResponse('Contrato no encontrado o sin permisos');
            }
            
            $datosRenovacion = [
                'fecha_fin' => $datos['nueva_fecha_fin'],
                'monto_mensual' => $datos['nuevo_monto'] ?? $contrato['monto_mensual'],
                'observaciones' => $datos['observaciones'] ?? $contrato['observaciones'],
                'fecha_renovacion' => date('Y-m-d H:i:s')
            ];
            
            $resultado = $this->personaUnidadModel->renewContrato($datos['contrato_id'], $datosRenovacion);
            $mensaje = 'Contrato renovado exitosamente';
            break;
            
        case 'expirar_contrato':
            $this->validateRequiredFields($datos, ['contrato_id']);
            
            $contrato = $this->getContratoConValidacion($datos['contrato_id'], $adminId);
            if (!$contrato) {
                return $this->errorResponse('Contrato no encontrado o sin permisos');
            }
            
            $resultado = $this->personaUnidadModel->expireContrato($datos['contrato_id']);
            
            // Remover persona de unidad si es necesario
            if (isset($datos['remover_ocupante']) && $datos['remover_ocupante']) {
                $this->personaUnidadModel->removePersonaFromUnidad($contrato['persona_id'], $contrato['unidad_id']);
                $this->actualizarContadorOcupantes($contrato['unidad_id']);
                $this->actualizarEstadoUnidad($contrato['unidad_id']);
            }
            
            $mensaje = 'Contrato expirado exitosamente';
            break;
            
        case 'obtener_contratos_unidad':
            $this->validateRequiredFields($datos, ['unidad_id']);
            
            $unidad = $this->personaUnidadModel->findUnidadById($datos['unidad_id']);
            if (!$this->condominioService->validarOwnership($unidad['condominio_id'], $adminId)) {
                return $this->errorResponse('No tienes permisos para ver contratos de esta unidad');
            }
            
            $contratos = $this->personaUnidadModel->getContratosByUnidad($datos['unidad_id']);
            
            // Agregar información adicional
            foreach ($contratos as &$contrato) {
                $contrato['persona_info'] = $this->personaService->obtenerPersonaBasica($contrato['persona_id']);
                $contrato['dias_hasta_vencimiento'] = $this->calcularDiasHastaVencimiento($contrato['fecha_fin']);
                $contrato['estado_tenencia'] = $this->evaluarEstadoTenencia($contrato);
            }
            
            return $this->successResponse($contratos, 'Contratos obtenidos exitosamente');
            
        default:
            return $this->errorResponse('Acción de contrato no válida');
    }
    
    // Log de actividad
    $this->logAdminActivity('contrato_gestionado', [
        'admin_id' => $adminId,
        'accion' => $accion,
        'datos' => $datos
    ]);
    
    return $this->successResponse($resultado, $mensaje);
}
```

### 4. **Obtener Reportes de Ocupación y Estadísticas**
```php
public function obtenerReporteOcupacion($adminId, $condominioId, $opciones = [])
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para ver reportes de este condominio');
    }
    
    // Aplicar rate limiting
    $this->enforceRateLimit('reporte_ocupacion_' . $adminId);
    
    // Obtener estadísticas base
    $estadisticas = $this->personaUnidadModel->getEstadisticasOcupacion($condominioId);
    
    // Unidades con ocupantes
    $unidadesConOcupantes = $this->personaUnidadModel->getUnidadesConOcupantes($condominioId);
    
    // Unidades disponibles por tipo
    $unidadesDisponibles = [];
    $tiposUnidad = $this->personaUnidadModel->getTiposUnidad($condominioId);
    foreach ($tiposUnidad as $tipo) {
        $unidadesDisponibles[$tipo['nombre']] = $this->personaUnidadModel->getUnidadesDisponibles($condominioId, $tipo['id']);
    }
    
    // Ocupación por tipo de unidad
    $ocupacionPorTipo = $this->personaUnidadModel->getOcupacionByTipoUnidad($condominioId);
    
    // Personas sin unidad asignada
    $personasSinUnidad = $this->personaUnidadModel->getPersonasSinUnidad($condominioId);
    
    // Estadísticas calculadas
    $totalUnidades = count($unidadesConOcupantes) + array_sum(array_map('count', $unidadesDisponibles));
    $unidadesOcupadas = count($unidadesConOcupantes);
    $porcentajeOcupacion = $totalUnidades > 0 ? round(($unidadesOcupadas / $totalUnidades) * 100, 2) : 0;
    
    // Análisis demográfico
    $analisisDemografico = $this->personaUnidadModel->getAnalisisDemografico($condominioId);
    
    // Contratos próximos a vencer (30 días)
    $contratosProximosVencer = $this->getContratosProximosVencer($condominioId, 30);
    
    // Rotación de unidades (último trimestre)
    $periodo = [
        'desde' => date('Y-m-d', strtotime('-3 months')),
        'hasta' => date('Y-m-d')
    ];
    $rotacionUnidades = $this->personaUnidadModel->getRotacionUnidades($condominioId, $periodo);
    
    $reporte = [
        'resumen_general' => [
            'total_unidades' => $totalUnidades,
            'unidades_ocupadas' => $unidadesOcupadas,
            'unidades_disponibles' => $totalUnidades - $unidadesOcupadas,
            'porcentaje_ocupacion' => $porcentajeOcupacion,
            'total_ocupantes' => array_sum(array_column($unidadesConOcupantes, 'total_ocupantes')),
            'personas_sin_unidad' => count($personasSinUnidad)
        ],
        'ocupacion_por_tipo' => $ocupacionPorTipo,
        'unidades_disponibles_por_tipo' => array_map('count', $unidadesDisponibles),
        'analisis_demografico' => $analisisDemografico,
        'contratos_proximos_vencer' => $contratosProximosVencer,
        'rotacion_unidades' => $rotacionUnidades,
        'alertas' => [
            'unidades_sobrepobladas' => $this->getUnidadesSobrepobladas($condominioId),
            'contratos_vencidos' => $this->getContratosVencidos($condominioId),
            'unidades_sin_actividad' => $this->getUnidadesSinActividad($condominioId, 90)
        ]
    ];
    
    // Agregar detalles si se solicitan
    if (isset($opciones['incluir_detalles']) && $opciones['incluir_detalles']) {
        $reporte['unidades_con_ocupantes'] = $unidadesConOcupantes;
        $reporte['unidades_disponibles'] = $unidadesDisponibles;
        $reporte['personas_sin_unidad'] = $personasSinUnidad;
    }
    
    return $this->successResponse($reporte, 'Reporte de ocupación obtenido exitosamente');
}
```

### 5. **Gestionar Tipos de Unidad y Configuraciones**
```php
public function gestionarTiposUnidad($adminId, $condominioId, $accion, $datos = [])
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para gestionar tipos de unidad en este condominio');
    }
    
    $resultado = false;
    $mensaje = '';
    
    switch ($accion) {
        case 'crear_tipo':
            $this->validateRequiredFields($datos, ['nombre', 'descripcion']);
            
            $tipoData = [
                'condominio_id' => $condominioId,
                'nombre' => $this->sanitizeText($datos['nombre']),
                'descripcion' => $this->sanitizeText($datos['descripcion']),
                'capacidad_maxima_defecto' => $datos['capacidad_maxima'] ?? 10,
                'area_minima' => $datos['area_minima'] ?? null,
                'area_maxima' => $datos['area_maxima'] ?? null,
                'permite_comercial' => $datos['permite_comercial'] ?? false,
                'permite_residencial' => $datos['permite_residencial'] ?? true,
                'activo' => true,
                'fecha_creacion' => date('Y-m-d H:i:s')
            ];
            
            $tipoId = $this->personaUnidadModel->createTipoUnidad($tipoData);
            
            // Establecer permisos por defecto
            if (isset($datos['permisos'])) {
                $this->personaUnidadModel->setPermisosUnidad($tipoId, $datos['permisos']);
            }
            
            $resultado = ['tipo_id' => $tipoId];
            $mensaje = 'Tipo de unidad creado exitosamente';
            break;
            
        case 'actualizar_tipo':
            $this->validateRequiredFields($datos, ['tipo_id']);
            
            $resultado = $this->personaUnidadModel->updateTipoUnidad($datos['tipo_id'], $datos);
            
            if (isset($datos['permisos'])) {
                $this->personaUnidadModel->setPermisosUnidad($datos['tipo_id'], $datos['permisos']);
            }
            
            $mensaje = 'Tipo de unidad actualizado exitosamente';
            break;
            
        case 'eliminar_tipo':
            $this->validateRequiredFields($datos, ['tipo_id']);
            
            // Verificar que no hay unidades usando este tipo
            $unidades = $this->personaUnidadModel->findUnidadesByTipo($datos['tipo_id'], $condominioId);
            if (count($unidades) > 0) {
                return $this->errorResponse('No se puede eliminar un tipo de unidad que tiene unidades asociadas');
            }
            
            $resultado = $this->personaUnidadModel->deleteTipoUnidad($datos['tipo_id']);
            $mensaje = 'Tipo de unidad eliminado exitosamente';
            break;
            
        case 'obtener_tipos':
            $tipos = $this->personaUnidadModel->getTiposUnidad($condominioId);
            
            // Agregar estadísticas para cada tipo
            foreach ($tipos as &$tipo) {
                $unidadesTipo = $this->personaUnidadModel->findUnidadesByTipo($tipo['id'], $condominioId);
                $tipo['total_unidades'] = count($unidadesTipo);
                $tipo['unidades_ocupadas'] = count(array_filter($unidadesTipo, function($u) {
                    return $u['total_ocupantes'] > 0;
                }));
                $tipo['permisos'] = $this->personaUnidadModel->getPermisosUnidad($tipo['id']);
            }
            
            return $this->successResponse($tipos, 'Tipos de unidad obtenidos exitosamente');
            
        default:
            return $this->errorResponse('Acción no válida para tipos de unidad');
    }
    
    // Log de actividad
    $this->logAdminActivity('tipos_unidad_gestionados', [
        'admin_id' => $adminId,
        'condominio_id' => $condominioId,
        'accion' => $accion,
        'datos' => $datos
    ]);
    
    return $this->successResponse($resultado, $mensaje);
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
private function actualizarContadorOcupantes($unidadId)
{
    $ocupantes = $this->personaUnidadModel->getPersonasByUnidad($unidadId);
    $totalOcupantes = count($ocupantes);
    
    $this->personaUnidadModel->updateUnidad($unidadId, [
        'total_ocupantes' => $totalOcupantes,
        'ultima_actualizacion' => date('Y-m-d H:i:s')
    ]);
}

private function actualizarEstadoUnidad($unidadId)
{
    $ocupantes = $this->personaUnidadModel->getPersonasByUnidad($unidadId);
    $unidad = $this->personaUnidadModel->findUnidadById($unidadId);
    
    if (count($ocupantes) == 0) {
        $estado = 'disponible';
    } elseif (count($ocupantes) >= $unidad['capacidad_maxima']) {
        $estado = 'ocupada_completa';
    } else {
        $estado = 'ocupada_parcial';
    }
    
    $this->personaUnidadModel->updateUnidad($unidadId, ['estado' => $estado]);
}

private function getContratoConValidacion($contratoId, $adminId)
{
    $contratos = $this->personaUnidadModel->getContratosByUnidad($unidadId);
    foreach ($contratos as $contrato) {
        if ($contrato['id'] == $contratoId) {
            $unidad = $this->personaUnidadModel->findUnidadById($contrato['unidad_id']);
            if ($this->condominioService->validarOwnership($unidad['condominio_id'], $adminId)) {
                return $contrato;
            }
        }
    }
    return null;
}

private function calcularDiasHastaVencimiento($fechaFin)
{
    if (!$fechaFin) return null;
    
    $hoy = new DateTime();
    $fin = new DateTime($fechaFin);
    $diferencia = $hoy->diff($fin);
    
    return $fin > $hoy ? $diferencia->days : -$diferencia->days;
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

// Validar calles
if (!$this->calleService->validarCalleEnCondominio($calleId, $condominioId)) {
    return $this->errorResponse("Calle no válida");
}

// Información de personas
$personaInfo = $this->personaService->obtenerPersonaBasica($personaId);
```

### Proporciona para otros servicios:
```php
// Para otros servicios que necesiten información de unidades
public function personaTieneUnidades($personaId);
public function obtenerUnidadesDePersona($personaId);
public function validarOcupacionUnidad($unidadId);
```

---

## 🚫 RESTRICCIONES IMPORTANTES

### Lo que NO debe hacer:
- ❌ **NO gestionar tags o dispositivos** (usar TagService/DispositivoService)
- ❌ **NO manejar condominios directamente** (usar CondominioService)
- ❌ **NO gestionar empleados** (usar EmpleadoService)

### Scope específico:
- ✅ **CRUD de unidades habitacionales/comerciales**
- ✅ **Gestión de relaciones persona-unidad**
- ✅ **Tipos de tenencia y contratos**
- ✅ **Reportes de ocupación**
- ✅ **Tipos de unidad y configuraciones**
- ✅ **Estadísticas demográficas**

---

## 📋 ESTRUCTURA DE RESPUESTAS

### Éxito
```php
return $this->successResponse([
    'unidad' => $unidadData,
    'mensaje' => 'Unidad gestionada exitosamente'
]);
```

### Error de Capacidad
```php
return $this->errorResponse(
    'La unidad ha alcanzado su capacidad máxima',
    400
);
```

---

## 🔍 LOGGING REQUERIDO

### Actividades a registrar:
- ✅ Creación/modificación de unidades
- ✅ Asignación de personas a unidades
- ✅ Gestión de contratos y tenencias
- ✅ Cambios en tipos de unidad
- ✅ Generación de reportes

---

## 📅 INFORMACIÓN DEL PROMPT
- **Fecha de creación:** 28 de Julio, 2025
- **Servicio:** PersonaUnidadService.php
- **Posición en cascada:** Nivel 10 (Relaciones Complejas)
- **Estado:** ✅ Listo para implementación

---

## 🎯 INSTRUCCIONES PARA COPILOT

Al generar código para PersonaUnidadService.php:

1. **SIEMPRE heredar de BaseAdminService**
2. **USAR métodos de Persona.php y Casa.php (como unidad)**
3. **VALIDAR ownership del condominio en TODAS las operaciones**
4. **GESTIONAR tipos de tenencia apropiadamente**
5. **MANEJAR contratos y fechas de vencimiento**
6. **VALIDAR capacidades de unidades**
7. **PROPORCIONAR reportes detallados de ocupación**
8. **NOTIFICAR cambios importantes**
9. **REGISTRAR logs de todas las actividades**
