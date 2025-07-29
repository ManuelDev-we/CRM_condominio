
## 🎯 PROPÓSITO DEL DOCUMENTO
Este documento sirve como índice maestro de todos los servicios administrativos disponibles en la capa de servicios del sistema Cyberhole Condominios. Define la estructura, responsabilidades y flujos de trabajo para el rol de administrador.

---

## 🏗️ ESTRUCTURA DE SERVICIOS ADMINISTRATIVOS

### 📂 **Jerarquía de Clases**
```
BaseService.php
    └── BaseAdminService.php
        ├── AccesosService.php
        ├── AdminService.php
        ├── AreaComunService.php
        ├── BlogService.php
        ├── CalleService.php
        ├── CasaService.php
        ├── CondominioService.php
        ├── EmpleadoService.php
        ├── EngomadoService.php
        ├── TagService.php
        ├── DispositivoService.php
        ├── MisCasasService.php
        ├── PersonaUnidadService.php
        └── PersonaCasaService.php
```

---

## 🔐 SERVICIOS PRINCIPALES

### 1️⃣ **CondominioService.php**
- 🎯 **Propósito**: Gestión completa de condominios
- 📋 **Funciones principales**:
  - Crear/editar condominios
  - Asignar administradores
  - Validar ownership
  - Gestionar configuraciones

### 2️⃣ **AdminService.php**
- 🎯 **Propósito**: Gestión de cuenta administrativa
- 📋 **Funciones principales**:
  - Perfil administrativo
  - Cambio de contraseña
  - Preferencias de cuenta
  - Notificaciones

### 3️⃣ **AccesosService.php**
- 🎯 **Propósito**: Control de accesos diferenciado
- 📋 **Funciones principales**:
  - Monitorear accesos por condominio
  - Gestionar tipos de acceso
  - Estadísticas y reportes
  - Control de visitantes

### 4️⃣ **EmpleadoService.php**
- 🎯 **Propósito**: Gestión de personal con encriptación
- 📋 **Funciones principales**:
  - CRUD empleados (AES)
  - Asignación de tareas
  - Control de acceso físico
  - Estado laboral

---

## 🏠 SERVICIOS DE PROPIEDAD

### 5️⃣ **CalleService.php**
- 🎯 **Propósito**: Gestión de calles del condominio
- 📋 **Funciones principales**:
  - CRUD de calles
  - Validaciones de nombre
  - Relación con condominio

### 6️⃣ **CasaService.php**
- 🎯 **Propósito**: Administración de propiedades
- 📋 **Funciones principales**:
  - CRUD de casas
  - Asignación de residentes
  - Claves de registro
  - Validaciones de ownership

### 7️⃣ **MisCasasService.php**
- 🎯 **Propósito**: Vista general de propiedades
- 📋 **Funciones principales**:
  - Listar casas por condominio
  - Ver residentes asignados
  - Gestionar claves de registro

---

## 🔑 SERVICIOS DE ACCESO

### 8️⃣ **TagService.php**
- 🎯 **Propósito**: Gestión de identificadores RFID/NFC
- 📋 **Funciones principales**:
  - CRUD de tags
  - Asignación a residentes
  - Control de estado
  - Validaciones

### 9️⃣ **EngomadoService.php**
- 🎯 **Propósito**: Gestión vehicular
- 📋 **Funciones principales**:
  - CRUD de engomados
  - Validación de placas
  - Control de estado
  - Asignación a residentes

### 🔟 **DispositivoService.php**
- 🎯 **Propósito**: Gestión de dispositivos
- 📋 **Funciones principales**:
  - Asociación de dispositivos
  - Control de permisos
  - Estado de conexión

---

## 🏊 SERVICIOS ADICIONALES

### 1️⃣1️⃣ **AreaComunService.php**
- 🎯 **Propósito**: Gestión de áreas comunes
- 📋 **Funciones principales**:
  - CRUD de áreas
  - Sistema de reservas
  - Control de horarios
  - Validaciones

### 1️⃣2️⃣ **BlogService.php**
- 🎯 **Propósito**: Gestión de publicaciones
- 📋 **Funciones principales**:
  - CRUD de posts
  - Control de visibilidad
  - Asignación por condominio

### 1️⃣3️⃣ **PersonaUnidadService.php**
- 🎯 **Propósito**: Gestión de unidades adicionales
- 📋 **Funciones principales**:
  - CRUD de unidades
  - Asociación de dispositivos
  - Datos extendidos

### 1️⃣4️⃣ **PersonaCasaService.php**
- 🎯 **Propósito**: Gestión de relaciones
- 📋 **Funciones principales**:
  - Asignación persona-casa
  - Control de relaciones
  - Validaciones

---

## 🔒 VALIDACIONES COMUNES

### **🛡️ Validaciones de Seguridad**
```php
// Validar ownership de condominio
$this->checkOwnershipCondominio($adminId, $condominioId);

// Validar CSRF en modificaciones
$this->checkCSRF('POST');

// Aplicar rate limiting
$this->enforceRateLimit($identifier);
```

### **📋 Validaciones de Datos**
```php
// Campos requeridos
$this->validateRequiredFields($data, ['campo1', 'campo2']);

// Formatos específicos
$this->validateEmailFormat($email);
$this->validatePlacaFormat($placa);
```

---

## 📊 FILTROS ESTÁNDAR

### **Para Consultas**
```php
$options = [
    'limite' => 100,
    'offset' => 0,
    'ordenar_por' => 'fecha',
    'orden' => 'DESC',
    'buscar' => 'término',
    'filtros' => [
        'campo' => 'valor'
    ]
];
```

### **Para Accesos**
```php
$options = [
    'fecha_desde' => '2025-01-01',
    'fecha_hasta' => '2025-12-31',
    'tipo_acceso' => 'entrada',
    'activos_solamente' => true
];
```

---

## 🔄 FLUJOS DE TRABAJO COMUNES

### **🆕 Crear Nuevo Registro**
1. Validar autenticación y rol
2. Verificar CSRF token
3. Validar campos requeridos
4. Aplicar reglas de negocio
5. Crear registro
6. Log de actividad

### **✏️ Actualizar Registro**
1. Validar autenticación y rol
2. Verificar ownership
3. Validar CSRF token
4. Aplicar cambios
5. Log de actividad

### **🗑️ Eliminar Registro**
1. Validar autenticación y rol
2. Verificar ownership
3. Validar dependencias
4. Eliminar registro
5. Log de actividad

---

## 📝 FORMATO DE RESPUESTAS

### **✅ Éxito**
```php
return $this->successResponse(
    $data,
    'Operación completada exitosamente'
);
```

### **❌ Error**
```php
return $this->errorResponse(
    'Mensaje de error',
    $codigoError,
    $detallesOpcionales
);
```

---

## 📊 LOGGING Y AUDITORÍA

### **📝 Log de Actividad**
```php
$this->logAdminActivity('accion_realizada', [
    'condominio_id' => $condominioId,
    'detalles' => $detalles
]);
```

### **🔍 Tipos de Log**
- ✅ Creación de registros
- 📝 Modificaciones
- 🗑️ Eliminaciones
- 🚪 Control de accesos
- 🔐 Cambios de seguridad

---

**📅 Actualizado:** 27 de Julio, 2025  
**🔄 Versión:** 1.0 - Índice Maestro de Servicios Administrativos  
**✅ Estado:** DOCUMENTACIÓN COMPLETA - Lista para implementación
