# 📁 CONFIG - Configuración Central del Sistema
**Sistema Cyberhole Condominios - Version 2**  
*Directorio de configuración limpio y optimizado*

---

## 🎯 **ESTRUCTURA OPTIMIZADA**

La carpeta `config/` contiene únicamente los archivos esenciales para el funcionamiento del sistema:

### ✅ **ARCHIVOS PRINCIPALES**

| Archivo | Propósito | Estado |
|---------|-----------|---------|
| `SecurityConfig.php` | **🔐 Configuración de Seguridad Central** | PRINCIPAL |
| `bootstrap.php` | **🚀 Inicialización del Sistema** | ESENCIAL |  
| `database.php` | **💾 Configuración de Base de Datos** | ESENCIAL |
| `env.php` | **⚙️ Variables de Entorno** | ESENCIAL |
| `.jwt_secret` | **🔑 Clave JWT (Auto-generada)** | AUTO |
| `.encryption_key` | **🔐 Clave de Encriptación (Auto-generada)** | AUTO |

---

## 📋 **DESCRIPCIÓN DETALLADA**

### 🔐 `SecurityConfig.php`
**Configuración centralizada de seguridad que reemplaza múltiples archivos anteriores**

**Funcionalidades incluidas:**
- ✅ Configuración de autenticación (JWT + Sesiones)
- ✅ Sistema de roles y permisos jerárquico
- ✅ Protección CSRF completa
- ✅ Rate limiting por IP y tipo de acción
- ✅ Control de propiedad multi-tenant
- ✅ Sistema de logging y auditoría
- ✅ Configuración de base de datos
- ✅ Configuración de entorno

**Métodos principales:**
```php
SecurityConfig::getAuthConfig()      // Configuración de autenticación
SecurityConfig::getRoleConfig()      // Roles y permisos
SecurityConfig::getCsrfConfig()      // Protección CSRF
SecurityConfig::getRateLimitConfig() // Límites de velocidad
SecurityConfig::getOwnershipConfig() // Control multi-tenant
SecurityConfig::getAllConfig()       // Toda la configuración
SecurityConfig::initialize()         // Inicializar sistema
```

### 🚀 `bootstrap.php`
**Sistema de inicialización central**

**Responsabilidades:**
- ✅ Verificación de versión PHP
- ✅ Configuración de zona horaria
- ✅ Manejo de errores personalizado
- ✅ Configuración de sesiones seguras
- ✅ Headers de seguridad
- ✅ Configuración JWT
- ✅ Configuración de base de datos
- ✅ Autoloader del sistema

### 💾 `database.php`
**Configuración de conexión a base de datos**

**Características:**
- ✅ Configuración PDO optimizada
- ✅ Manejo de errores avanzado
- ✅ Pool de conexiones
- ✅ Configuración de charset UTF-8
- ✅ Opciones de seguridad PDO

### ⚙️ `env.php`
**Cargador de variables de entorno**

**Funciones:**
- ✅ Carga archivo `.env` desde la raíz
- ✅ Validación de variables críticas
- ✅ Configuración de variables por defecto
- ✅ Logging de configuración

### 🔑 `.jwt_secret` y `.encryption_key`
**Claves de seguridad auto-generadas**

**Características:**
- ✅ Generación automática en primera ejecución
- ✅ Claves de 64 bytes para JWT
- ✅ Claves de 32 bytes para encriptación AES-256-CBC
- ✅ Base64 encoded para portabilidad
- ✅ Archivos ocultos para seguridad

---

## 🧹 **ARCHIVOS ELIMINADOS**

Los siguientes archivos fueron eliminados durante la limpieza por ser **obsoletos o redundantes**:

| Archivo Eliminado | Razón | Reemplazado Por |
|-------------------|-------|-----------------|
| `security.php` | ❌ Clase duplicada `SecurityConfig` | `SecurityConfig.php` |
| `jwt.php` | ❌ Funcionalidad duplicada | `SecurityConfig.php` |
| `audit_config.php` | ❌ Archivo de test no productivo | Tests externos |
| `DOCUMENTACION_SISTEMA.md` | ❌ Documentación obsoleta | Este README |
| `MANUAL_IMPLEMENTACION.md` | ❌ Manual desactualizado | Este README |

---

## 🚀 **USO DEL SISTEMA**

### Inicialización Básica
```php
<?php
// Cargar bootstrap (incluye todo lo necesario)
require_once __DIR__ . '/config/bootstrap.php';

// O cargar configuración específica
require_once __DIR__ . '/config/SecurityConfig.php';
SecurityConfig::initialize();
```

### Obtener Configuraciones
```php
// Configuración completa
$config = SecurityConfig::getAllConfig();

// Configuraciones específicas
$authConfig = SecurityConfig::getAuthConfig();
$roleConfig = SecurityConfig::getRoleConfig();
$csrfConfig = SecurityConfig::getCsrfConfig();
```

### Uso con Middlewares
```php
require_once __DIR__ . '/middlewares/MiddlewareManager.php';

// Verificar autenticación + roles
$result = MiddlewareManager::check(['auth', 'role']);

// Verificar todo el stack de seguridad
$result = MiddlewareManager::check(['auth', 'role', 'csrf', 'rate_limit', 'ownership']);
```

---

## ✅ **BENEFICIOS DE LA LIMPIEZA**

### 🎯 **Simplicidad**
- ✅ Solo 6 archivos esenciales
- ✅ Sin duplicación de código
- ✅ Configuración centralizada

### 🔒 **Seguridad**
- ✅ Una sola fuente de verdad para configuración
- ✅ Claves auto-generadas y seguras
- ✅ Sin archivos obsoletos que puedan causar conflictos

### 🚀 **Performance**
- ✅ Menos archivos que cargar
- ✅ Sin includes redundantes
- ✅ Configuración optimizada

### 🛠️ **Mantenibilidad**
- ✅ Estructura clara y documentada
- ✅ Fácil de entender y modificar
- ✅ Sin archivos de test en producción

---

## 📞 **SOPORTE**

Para cualquier duda sobre la configuración:

1. **Revisar este README**
2. **Consultar documentación de middlewares**: `middlewares/MIDDLEWARES_CYBERHOLE_COMPLETO.md`
3. **Ejecutar tests de verificación**: `test_middlewares_completo_integrado.php`

---

**Sistema Cyberhole Condominios - Config Limpio ✅**  
*Configuración optimizada y lista para producción*
