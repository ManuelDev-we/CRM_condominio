# DISPOSITIVOSERVICE_ADMIN_PROMPT.md
## Prompt Especializado para DispositivoService.php

### 🎯 PROPÓSITO DEL SERVICIO
Administrar dispositivos de control de acceso dentro de un condominio. Gestiona CRUD de dispositivos, configuración de acceso, sincronización con tags/engomados, estados operacionales y mantenimiento. Es el puente entre el sistema digital y el control físico.

---

## 🏗️ ARQUITECTURA Y HERENCIA

### Clase Base
```php
class DispositivoService extends BaseAdminService
```

### Dependencias
- **Hereda de:** `BaseAdminService.php`
- **Modelos principales:** `Dispositivo.php`, `AreaComun.php`
- **Posición en cascada:** Nivel 8 (Control Físico)
- **Servicios relacionados:** TagService, EngomadoService, AreaComunService, AccesosService
- **Requiere validaciones de:** CondominioService

---

## 📚 MÉTODOS DEL MODELO DISPOSITIVO DISPONIBLES

### Métodos de Gestión de Dispositivos
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `createDispositivo()` | array $data | int | Crear dispositivo |
| `findDispositivoById()` | int $id | array | Buscar dispositivo por ID |
| `findDispositivoBySerie()` | string $serie | array | Buscar dispositivo por serie |
| `findDispositivosByCondominio()` | int $condominioId | array | Buscar dispositivos por condominio |
| `findDispositivosByTipo()` | string $tipo | array | Buscar dispositivos por tipo |
| `findDispositivosByUbicacion()` | string $ubicacion | array | Buscar dispositivos por ubicación |
| `updateDispositivo()` | int $id, array $data | bool | Actualizar dispositivo |
| `deleteDispositivo()` | int $id | bool | Eliminar dispositivo |

### Métodos de Estados y Control
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `activateDispositivo()` | int $id | bool | Activar dispositivo |
| `deactivateDispositivo()` | int $id | bool | Desactivar dispositivo |
| `enableDispositivo()` | int $id | bool | Habilitar dispositivo |
| `disableDispositivo()` | int $id | bool | Deshabilitar dispositivo |
| `setEstadoMantenimiento()` | int $id, bool $estado | bool | Establecer estado de mantenimiento |
| `reportarFalla()` | int $id, string $descripcion | bool | Reportar falla |
| `resolverFalla()` | int $id | bool | Resolver falla |

### Métodos de Configuración
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `setConfiguracion()` | int $id, array $config | bool | Establecer configuración |
| `getConfiguracion()` | int $id | array | Obtener configuración |
| `updateConfiguracion()` | int $id, array $config | bool | Actualizar configuración |
| `resetConfiguracion()` | int $id | bool | Resetear configuración |
| `backupConfiguracion()` | int $id | array | Respaldar configuración |
| `restoreConfiguracion()` | int $id, array $backup | bool | Restaurar configuración |

### Métodos de Sincronización
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `sincronizarTags()` | int $dispositivoId | bool | Sincronizar tags autorizados |
| `sincronizarEngomados()` | int $dispositivoId | bool | Sincronizar engomados autorizados |
| `sincronizarHorarios()` | int $dispositivoId | bool | Sincronizar horarios de acceso |
| `actualizarListaNegra()` | int $dispositivoId | bool | Actualizar lista negra |
| `sincronizacionCompleta()` | int $dispositivoId | bool | Sincronización completa |
| `getEstadoSincronizacion()` | int $dispositivoId | array | Estado de sincronización |

### Métodos de Comunicación
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `enviarComando()` | int $id, string $comando | bool | Enviar comando al dispositivo |
| `recibirEstado()` | int $id | array | Recibir estado del dispositivo |
| `testConexion()` | int $id | bool | Probar conexión |
| `reiniciarDispositivo()` | int $id | bool | Reiniciar dispositivo |
| `actualizarFirmware()` | int $id, string $version | bool | Actualizar firmware |
| `getLogDispositivo()` | int $id, int $lineas | array | Obtener log del dispositivo |

### Métodos de Monitoreo
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `getEstadoOperacional()` | int $id | array | Estado operacional actual |
| `getUltimaActividad()` | int $id | array | Última actividad registrada |
| `getContadores()` | int $id | array | Contadores de acceso |
| `resetContadores()` | int $id | bool | Resetear contadores |
| `getAlarmasActivas()` | int $id | array | Alarmas activas |
| `clearAlarmas()` | int $id | bool | Limpiar alarmas |

### Métodos de Validación
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `validateCondominioExists()` | int $condominioId | bool | Valida existencia de condominio |
| `validateDispositivoExists()` | int $dispositivoId | bool | Valida existencia de dispositivo |
| `validateSerieUnique()` | string $serie, int $condominioId | bool | Valida unicidad de serie |
| `validateDispositivoActive()` | int $dispositivoId | bool | Valida dispositivo activo |
| `validateUbicacionDisponible()` | string $ubicacion, int $condominioId | bool | Valida ubicación disponible |

### Métodos de Mantenimiento
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `programarMantenimiento()` | int $id, array $data | int | Programar mantenimiento |
| `ejecutarMantenimiento()` | int $mantenimientoId | bool | Ejecutar mantenimiento |
| `getHistorialMantenimiento()` | int $id | array | Historial de mantenimiento |
| `getProximosMantenimientos()` | int $condominioId | array | Próximos mantenimientos |
| `generarReporteMantenimiento()` | int $condominioId, array $periodo | array | Reporte de mantenimiento |

### Métodos de Estadísticas y Reportes
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `getEstadisticasUso()` | int $dispositivoId, array $periodo | array | Estadísticas de uso |
| `getReporteOperacional()` | int $condominioId | array | Reporte operacional |
| `getReporteIncidencias()` | int $condominioId, array $periodo | array | Reporte de incidencias |
| `getDispositivosConFallas()` | int $condominioId | array | Dispositivos con fallas |

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

### 1. **Crear Dispositivo**
```php
public function crearDispositivo($adminId, $condominioId, $datos)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para crear dispositivos en este condominio');
    }
    
    // Validar campos requeridos
    $this->validateRequiredFields($datos, ['nombre', 'tipo', 'numero_serie', 'ubicacion']);
    
    // Validar que el número de serie sea único en el condominio
    if (!$this->dispositivoModel->validateSerieUnique($datos['numero_serie'], $condominioId)) {
        return $this->errorResponse('El número de serie ya existe en este condominio');
    }
    
    // Validar tipo de dispositivo
    $tiposValidos = ['lector_tag', 'camara_acceso', 'barrera_vehicular', 'puerta_automatica', 'torniquete', 'intercomunicador'];
    if (!in_array($datos['tipo'], $tiposValidos)) {
        return $this->errorResponse('Tipo de dispositivo inválido');
    }
    
    // Validar ubicación
    if (isset($datos['ubicacion']) && !$this->dispositivoModel->validateUbicacionDisponible($datos['ubicacion'], $condominioId)) {
        return $this->errorResponse('Ya existe un dispositivo en esta ubicación');
    }
    
    // Agregar datos adicionales
    $datos['condominio_id'] = $condominioId;
    $datos['fecha_instalacion'] = date('Y-m-d H:i:s');
    $datos['estado'] = 'inactivo'; // Inicia inactivo hasta configurar
    $datos['habilitado'] = false;
    $datos['en_mantenimiento'] = false;
    $datos['con_fallas'] = false;
    
    // Configuración inicial por defecto según tipo
    $configuracionInicial = $this->getConfiguracionInicialPorTipo($datos['tipo']);
    $datos['configuracion'] = json_encode($configuracionInicial);
    
    // Estadísticas iniciales
    $datos['ultima_sincronizacion'] = null;
    $datos['total_accesos_permitidos'] = 0;
    $datos['total_accesos_denegados'] = 0;
    $datos['ultima_actividad'] = null;
    
    // Crear dispositivo
    $dispositivoId = $this->dispositivoModel->createDispositivo($datos);
    
    // Log de actividad
    $this->logAdminActivity('dispositivo_creado', [
        'admin_id' => $adminId,
        'condominio_id' => $condominioId,
        'dispositivo_id' => $dispositivoId,
        'nombre' => $datos['nombre'],
        'tipo' => $datos['tipo'],
        'numero_serie' => $datos['numero_serie'],
        'ubicacion' => $datos['ubicacion']
    ]);
    
    return $this->successResponse(['id' => $dispositivoId], 'Dispositivo creado exitosamente');
}
```

### 2. **Configurar Dispositivo**
```php
public function configurarDispositivo($adminId, $dispositivoId, $configuracion)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Obtener dispositivo
    $dispositivo = $this->dispositivoModel->findDispositivoById($dispositivoId);
    if (!$dispositivo) {
        return $this->errorResponse('Dispositivo no encontrado');
    }
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($dispositivo['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para configurar este dispositivo');
    }
    
    // Validar configuración según tipo de dispositivo
    $resultado = $this->validarConfiguracionPorTipo($dispositivo['tipo'], $configuracion);
    if (!$resultado['valido']) {
        return $this->errorResponse($resultado['mensaje']);
    }
    
    // Hacer backup de configuración actual si existe
    $configuracionActual = json_decode($dispositivo['configuracion'], true);
    if ($configuracionActual) {
        $this->dispositivoModel->backupConfiguracion($dispositivoId);
    }
    
    // Actualizar configuración
    $configuracionCompleta = array_merge($configuracionActual ?: [], $configuracion);
    $resultadoUpdate = $this->dispositivoModel->setConfiguracion($dispositivoId, $configuracionCompleta);
    
    // Enviar configuración al dispositivo si está activo
    if ($dispositivo['estado'] == 'activo') {
        $this->sincronizarConfiguracion($dispositivoId);
    }
    
    // Log de actividad
    $this->logAdminActivity('dispositivo_configurado', [
        'admin_id' => $adminId,
        'dispositivo_id' => $dispositivoId,
        'condominio_id' => $dispositivo['condominio_id'],
        'configuracion_keys' => array_keys($configuracion)
    ]);
    
    return $this->successResponse($resultadoUpdate, 'Dispositivo configurado exitosamente');
}
```

### 3. **Sincronizar Dispositivo**
```php
public function sincronizarDispositivo($adminId, $dispositivoId, $tipoSincronizacion = 'completa')
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Obtener dispositivo
    $dispositivo = $this->dispositivoModel->findDispositivoById($dispositivoId);
    if (!$dispositivo) {
        return $this->errorResponse('Dispositivo no encontrado');
    }
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($dispositivo['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para sincronizar este dispositivo');
    }
    
    // Validar que el dispositivo esté activo
    if (!$this->dispositivoModel->validateDispositivoActive($dispositivoId)) {
        return $this->errorResponse('El dispositivo debe estar activo para sincronizar');
    }
    
    // Probar conexión primero
    if (!$this->dispositivoModel->testConexion($dispositivoId)) {
        return $this->errorResponse('No se puede conectar con el dispositivo');
    }
    
    $resultados = [];
    $errores = [];
    
    try {
        // Sincronización según tipo
        switch ($tipoSincronizacion) {
            case 'tags':
                if ($dispositivo['tipo'] == 'lector_tag') {
                    $resultados['tags'] = $this->dispositivoModel->sincronizarTags($dispositivoId);
                }
                break;
                
            case 'engomados':
                if ($dispositivo['tipo'] == 'barrera_vehicular') {
                    $resultados['engomados'] = $this->dispositivoModel->sincronizarEngomados($dispositivoId);
                }
                break;
                
            case 'horarios':
                $resultados['horarios'] = $this->dispositivoModel->sincronizarHorarios($dispositivoId);
                break;
                
            case 'lista_negra':
                $resultados['lista_negra'] = $this->dispositivoModel->actualizarListaNegra($dispositivoId);
                break;
                
            case 'completa':
            default:
                // Sincronización completa
                if ($dispositivo['tipo'] == 'lector_tag') {
                    $resultados['tags'] = $this->dispositivoModel->sincronizarTags($dispositivoId);
                }
                if ($dispositivo['tipo'] == 'barrera_vehicular') {
                    $resultados['engomados'] = $this->dispositivoModel->sincronizarEngomados($dispositivoId);
                }
                $resultados['horarios'] = $this->dispositivoModel->sincronizarHorarios($dispositivoId);
                $resultados['lista_negra'] = $this->dispositivoModel->actualizarListaNegra($dispositivoId);
                $resultados['configuracion'] = $this->sincronizarConfiguracion($dispositivoId);
                break;
        }
        
        // Actualizar timestamp de sincronización
        $this->dispositivoModel->updateDispositivo($dispositivoId, [
            'ultima_sincronizacion' => date('Y-m-d H:i:s')
        ]);
        
        // Log de actividad
        $this->logAdminActivity('dispositivo_sincronizado', [
            'admin_id' => $adminId,
            'dispositivo_id' => $dispositivoId,
            'condominio_id' => $dispositivo['condominio_id'],
            'tipo_sincronizacion' => $tipoSincronizacion,
            'resultados' => $resultados
        ]);
        
        return $this->successResponse([
            'resultados' => $resultados,
            'errores' => $errores,
            'fecha_sincronizacion' => date('Y-m-d H:i:s')
        ], 'Sincronización completada exitosamente');
        
    } catch (Exception $e) {
        $this->logAdminActivity('dispositivo_sincronizacion_error', [
            'admin_id' => $adminId,
            'dispositivo_id' => $dispositivoId,
            'error' => $e->getMessage()
        ]);
        
        return $this->errorResponse('Error en la sincronización: ' . $e->getMessage());
    }
}
```

### 4. **Monitorear Estado**
```php
public function obtenerEstadoDispositivo($adminId, $dispositivoId)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Obtener dispositivo
    $dispositivo = $this->dispositivoModel->findDispositivoById($dispositivoId);
    if (!$dispositivo) {
        return $this->errorResponse('Dispositivo no encontrado');
    }
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($dispositivo['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para monitorear este dispositivo');
    }
    
    // Aplicar rate limiting
    $this->enforceRateLimit('estado_dispositivo_' . $adminId);
    
    // Obtener estado completo
    $estado = [
        'informacion_basica' => $dispositivo,
        'estado_operacional' => $this->dispositivoModel->getEstadoOperacional($dispositivoId),
        'ultima_actividad' => $this->dispositivoModel->getUltimaActividad($dispositivoId),
        'contadores' => $this->dispositivoModel->getContadores($dispositivoId),
        'alarmas_activas' => $this->dispositivoModel->getAlarmasActivas($dispositivoId),
        'configuracion' => json_decode($dispositivo['configuracion'], true),
        'estado_sincronizacion' => $this->dispositivoModel->getEstadoSincronizacion($dispositivoId)
    ];
    
    // Probar conexión en tiempo real
    $estado['conexion_activa'] = $this->dispositivoModel->testConexion($dispositivoId);
    
    return $this->successResponse($estado, 'Estado del dispositivo obtenido exitosamente');
}

public function obtenerDispositivosCondominio($adminId, $condominioId, $opciones = [])
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para ver dispositivos de este condominio');
    }
    
    // Aplicar rate limiting
    $this->enforceRateLimit('dispositivos_' . $adminId);
    
    // Obtener dispositivos
    $dispositivos = $this->dispositivoModel->findDispositivosByCondominio($condominioId);
    
    // Aplicar filtros si se proporcionan
    if (isset($opciones['tipo'])) {
        $dispositivos = array_filter($dispositivos, function($d) use ($opciones) {
            return $d['tipo'] == $opciones['tipo'];
        });
    }
    
    if (isset($opciones['estado'])) {
        $dispositivos = array_filter($dispositivos, function($d) use ($opciones) {
            return $d['estado'] == $opciones['estado'];
        });
    }
    
    if (isset($opciones['ubicacion'])) {
        $dispositivos = array_filter($dispositivos, function($d) use ($opciones) {
            return strpos(strtolower($d['ubicacion']), strtolower($opciones['ubicacion'])) !== false;
        });
    }
    
    // Agregar información de estado para cada dispositivo
    foreach ($dispositivos as &$dispositivo) {
        $dispositivo['conexion_activa'] = $this->dispositivoModel->testConexion($dispositivo['id']);
        $dispositivo['alarmas_count'] = count($this->dispositivoModel->getAlarmasActivas($dispositivo['id']));
        $dispositivo['dias_sin_mantenimiento'] = $this->calcularDiasSinMantenimiento($dispositivo['id']);
    }
    
    return $this->successResponse($dispositivos, 'Dispositivos obtenidos exitosamente');
}
```

### 5. **Gestionar Mantenimiento**
```php
public function programarMantenimiento($adminId, $dispositivoId, $datosMantenimiento)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Obtener dispositivo
    $dispositivo = $this->dispositivoModel->findDispositivoById($dispositivoId);
    if (!$dispositivo) {
        return $this->errorResponse('Dispositivo no encontrado');
    }
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($dispositivo['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para programar mantenimiento en este dispositivo');
    }
    
    // Validar campos requeridos
    $this->validateRequiredFields($datosMantenimiento, ['tipo', 'descripcion', 'fecha_programada']);
    
    // Validar que la fecha sea futura
    if (strtotime($datosMantenimiento['fecha_programada']) <= time()) {
        return $this->errorResponse('La fecha de mantenimiento debe ser futura');
    }
    
    // Tipos de mantenimiento válidos
    $tiposValidos = ['preventivo', 'correctivo', 'actualizacion', 'limpieza', 'calibracion'];
    if (!in_array($datosMantenimiento['tipo'], $tiposValidos)) {
        return $this->errorResponse('Tipo de mantenimiento inválido');
    }
    
    // Agregar datos adicionales
    $datosMantenimiento['dispositivo_id'] = $dispositivoId;
    $datosMantenimiento['fecha_creacion'] = date('Y-m-d H:i:s');
    $datosMantenimiento['estado'] = 'programado';
    $datosMantenimiento['programado_por'] = $adminId;
    
    // Programar mantenimiento
    $mantenimientoId = $this->dispositivoModel->programarMantenimiento($dispositivoId, $datosMantenimiento);
    
    // Si es mantenimiento inmediato, marcar dispositivo
    if (isset($datosMantenimiento['requiere_parada']) && $datosMantenimiento['requiere_parada']) {
        $this->dispositivoModel->setEstadoMantenimiento($dispositivoId, true);
    }
    
    // Log de actividad
    $this->logAdminActivity('mantenimiento_programado', [
        'admin_id' => $adminId,
        'dispositivo_id' => $dispositivoId,
        'mantenimiento_id' => $mantenimientoId,
        'tipo' => $datosMantenimiento['tipo'],
        'fecha_programada' => $datosMantenimiento['fecha_programada']
    ]);
    
    return $this->successResponse(['id' => $mantenimientoId], 'Mantenimiento programado exitosamente');
}
```

### 6. **Obtener Reportes**
```php
public function obtenerReporteOperacional($adminId, $condominioId, $periodo = [])
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para ver reportes de este condominio');
    }
    
    // Aplicar rate limiting
    $this->enforceRateLimit('reporte_operacional_' . $adminId);
    
    // Obtener reporte
    $reporte = $this->dispositivoModel->getReporteOperacional($condominioId);
    
    // Agregar información adicional
    $dispositivos = $this->dispositivoModel->findDispositivosByCondominio($condominioId);
    $reporte['total_dispositivos'] = count($dispositivos);
    $reporte['dispositivos_activos'] = count(array_filter($dispositivos, function($d) { 
        return $d['estado'] == 'activo'; 
    }));
    $reporte['dispositivos_con_fallas'] = count(array_filter($dispositivos, function($d) { 
        return $d['con_fallas']; 
    }));
    $reporte['dispositivos_en_mantenimiento'] = count(array_filter($dispositivos, function($d) { 
        return $d['en_mantenimiento']; 
    }));
    
    // Estadísticas por tipo
    $reporte['por_tipo'] = [];
    foreach ($dispositivos as $dispositivo) {
        $tipo = $dispositivo['tipo'];
        if (!isset($reporte['por_tipo'][$tipo])) {
            $reporte['por_tipo'][$tipo] = ['total' => 0, 'activos' => 0, 'con_fallas' => 0];
        }
        $reporte['por_tipo'][$tipo]['total']++;
        if ($dispositivo['estado'] == 'activo') $reporte['por_tipo'][$tipo]['activos']++;
        if ($dispositivo['con_fallas']) $reporte['por_tipo'][$tipo]['con_fallas']++;
    }
    
    return $this->successResponse($reporte, 'Reporte operacional obtenido exitosamente');
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
public function dispositivoPerteneceACondominio($dispositivoId, $condominioId)
{
    $dispositivo = $this->dispositivoModel->findDispositivoById($dispositivoId);
    return $dispositivo && $dispositivo['condominio_id'] == $condominioId;
}

private function getConfiguracionInicialPorTipo($tipo)
{
    $configuraciones = [
        'lector_tag' => [
            'distancia_lectura' => 5,
            'tiempo_espera' => 3,
            'sonido_habilitado' => true,
            'led_habilitado' => true
        ],
        'barrera_vehicular' => [
            'tiempo_apertura' => 10,
            'sensor_vehiculo' => true,
            'apertura_manual' => false
        ],
        'camara_acceso' => [
            'resolucion' => '1080p',
            'fps' => 30,
            'deteccion_movimiento' => true
        ]
    ];
    
    return $configuraciones[$tipo] ?? [];
}

private function validarConfiguracionPorTipo($tipo, $configuracion)
{
    // Validaciones específicas por tipo de dispositivo
    switch ($tipo) {
        case 'lector_tag':
            if (isset($configuracion['distancia_lectura']) && 
                ($configuracion['distancia_lectura'] < 1 || $configuracion['distancia_lectura'] > 10)) {
                return ['valido' => false, 'mensaje' => 'Distancia de lectura debe estar entre 1 y 10 cm'];
            }
            break;
        case 'barrera_vehicular':
            if (isset($configuracion['tiempo_apertura']) && 
                ($configuracion['tiempo_apertura'] < 5 || $configuracion['tiempo_apertura'] > 60)) {
                return ['valido' => false, 'mensaje' => 'Tiempo de apertura debe estar entre 5 y 60 segundos'];
            }
            break;
    }
    
    return ['valido' => true];
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

// Sincronizar con TagService
$tagsActivos = $this->tagService->obtenerTagsActivos($condominioId);
$this->sincronizarTagsConDispositivo($dispositivoId, $tagsActivos);

// Sincronizar con EngomadoService
$engomadosVigentes = $this->engomadoService->obtenerEngomadosVigentes($condominioId);
$this->sincronizarEngomadosConDispositivo($dispositivoId, $engomadosVigentes);
```

### Proporciona para otros servicios:
```php
// Para AccesosService, TagService, EngomadoService
public function dispositivoPerteneceACondominio($dispositivoId, $condominioId);
public function validarDispositivoActivo($dispositivoId);
public function notificarAcceso($dispositivoId, $resultado);
```

---

## 🚫 RESTRICCIONES IMPORTANTES

### Lo que NO debe hacer:
- ❌ **NO gestionar tags o engomados directamente** (usar TagService/EngomadoService)
- ❌ **NO manejar lógica de acceso** (usar AccesosService)
- ❌ **NO gestionar personas** (usar PersonaService)

### Scope específico:
- ✅ **CRUD de dispositivos físicos**
- ✅ **Configuración y sincronización**
- ✅ **Monitoreo y mantenimiento**
- ✅ **Comunicación con hardware**
- ✅ **Reportes operacionales**

---

## 📋 ESTRUCTURA DE RESPUESTAS

### Éxito
```php
return $this->successResponse([
    'dispositivo' => $dispositivoData,
    'mensaje' => 'Dispositivo gestionado exitosamente'
]);
```

### Error de Conexión
```php
return $this->errorResponse(
    'No se puede conectar con el dispositivo',
    503
);
```

---

## 🔍 LOGGING REQUERIDO

### Actividades a registrar:
- ✅ Creación/modificación de dispositivos
- ✅ Cambios de configuración
- ✅ Sincronizaciones
- ✅ Cambios de estado
- ✅ Mantenimientos programados
- ✅ Fallas y resoluciones

---

## 📅 INFORMACIÓN DEL PROMPT
- **Fecha de creación:** 28 de Julio, 2025
- **Servicio:** DispositivoService.php
- **Posición en cascada:** Nivel 8 (Control Físico)
- **Estado:** ✅ Listo para implementación

---

## 🎯 INSTRUCCIONES PARA COPILOT

Al generar código para DispositivoService.php:

1. **SIEMPRE heredar de BaseAdminService**
2. **USAR métodos de Dispositivo.php**
3. **VALIDAR ownership del condominio en TODAS las operaciones**
4. **GESTIONAR configuraciones específicas por tipo**
5. **COORDINAR sincronización con TagService y EngomadoService**
6. **MONITOREAR estado operacional constantemente**
7. **REGISTRAR logs de todas las actividades**
8. **PROPORCIONAR métodos de validación para otros servicios**
