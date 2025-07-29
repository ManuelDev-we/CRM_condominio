# 📋 DOCUMENTACIÓN TÉCNICA DEL SISTEMA CYBERHOLE CONDOMINIOS

## 📄 Configuración del Sistema

### Inventario de Archivos de Configuración

| N° | Archivo | Propósito |
|----|---------|-----------|
| 41 | `/public_html/config/database.php` | Configuración de la conexión a la base de datos |
| 42 | `/public_html/config/env.php` | Carga de variables de entorno desde archivo .env |
| 43 | `/public_html/config/jwt.php` | Opciones de seguridad para la generación y validación JWT |
| 44 | `/public_html/config/security.php` | Políticas generales de seguridad del sistema |
| 45 | `/public_html/config/bootstrap.php` | Inicialización global del entorno y carga de configuraciones |

### Manual de Cada Archivo

#### 41. /public_html/config/database.php
**Propósito:**
Define los parámetros para la conexión a la base de datos MySQL. Incluye datos como host, puerto, nombre de la base, usuario, contraseña y codificación. Este archivo es consultado por el modelo base para obtener la conexión PDO.

**Uso:**
Debe estar correctamente configurado para que cualquier acceso a datos funcione. No expone las credenciales en texto plano; depende de las variables cargadas desde .env.

#### 42. /public_html/config/env.php
**Propósito:**
Lee las variables definidas en el archivo .env y las coloca en el entorno de ejecución del servidor PHP. Permite separar la configuración sensible y dependiente del entorno de la lógica del sistema.

**Uso:**
Debe cargarse siempre al inicio de la ejecución de la aplicación, antes de cualquier acceso a configuración.

#### 43. /public_html/config/jwt.php
**Propósito:**
Define las opciones y parámetros para la autenticación basada en JSON Web Tokens. Incluye la clave secreta de firma, algoritmo, emisor y tiempo de expiración predeterminado.

**Uso:**
El servicio de autenticación lee estos valores para emitir tokens válidos y verificar los recibidos.

#### 44. /public_html/config/security.php
**Propósito:**
Establece políticas de seguridad adicionales para el sistema. Incluye parámetros para generación y expiración de tokens CSRF, requisitos mínimos de contraseñas, reglas de cookies y otras prácticas recomendadas.

**Uso:**
Consultado por los servicios encargados de validar formularios y sesiones, así como al momento de validar contraseñas.

#### 45. /public_html/config/bootstrap.php
**Propósito:**
Actúa como punto central de inicialización del entorno del sistema. Carga las configuraciones básicas (env.php, database.php, jwt.php, security.php), define zona horaria, activa o desactiva errores de acuerdo al modo de ejecución y define cabeceras comunes.

**Uso:**
Debe incluirse siempre al inicio de index.php y en todos los endpoints API para asegurar que el entorno está listo.

### Descripción General
El módulo de configuración es esencial para asegurar que el sistema opere correctamente bajo distintos entornos (desarrollo, pruebas, producción). Centraliza las credenciales, las políticas de seguridad y las opciones críticas para que sean fáciles de mantener y seguras. Se recomienda que todos estos archivos lean sus valores de .env y que dicho archivo nunca sea versionado.

---

## 🔐 Sistema de Servicios

### Inventario de Servicios del Sistema

| N° | Archivo | Propósito |
|----|---------|-----------|
| 46 | `/public_html/services/AuthService.php` | Gestión de autenticación y autorización de usuarios |
| 47 | `/public_html/services/PersonaService.php` | Operaciones CRUD para entidades de personas |
| 48 | `/public_html/services/CondominioService.php` | Gestión de condominios y sus propiedades |
| 49 | `/public_html/services/CalleService.php` | Administración de calles dentro de condominios |
| 50 | `/public_html/services/CasaService.php` | Gestión de casas y su información |
| 51 | `/public_html/services/TagService.php` | Manejo de etiquetas y engomados |
| 52 | `/public_html/services/VisitanteService.php` | Control de acceso de visitantes |
| 53 | `/public_html/services/AdminService.php` | Operaciones administrativas del sistema |
| 54 | `/public_html/services/VehiculoService.php` | Gestión de vehículos registrados |
| 55 | `/public_html/services/ReporteService.php` | Generación y gestión de reportes |
| 56 | `/public_html/services/NotificacionService.php` | Sistema de notificaciones |
| 57 | `/public_html/services/ConfiguracionService.php` | Configuraciones del sistema |
| 58 | `/public_html/services/EmailService.php` | Envío de correos electrónicos |
| 59 | `/public_html/services/EngomadoService.php` | Gestión específica de engomados |

### Manual de Servicios

#### 46. AuthService.php
**Propósito:**
Centraliza toda la lógica de autenticación del sistema. Maneja login, logout, generación de tokens JWT, validación de credenciales y gestión de sesiones.

**Responsabilidades:**
- Validación de credenciales de usuario
- Generación y validación de tokens JWT
- Gestión de sesiones activas
- Renovación automática de tokens

#### 47. PersonaService.php
**Propósito:**
Gestiona las operaciones relacionadas con personas en el sistema, incluyendo residentes, administradores y visitantes.

**Responsabilidades:**
- CRUD completo de personas
- Validación de datos personales
- Gestión de roles y permisos
- Búsqueda y filtrado de usuarios

#### 48. CondominioService.php
**Propósito:**
Administra la información de los condominios registrados en el sistema.

**Responsabilidades:**
- Registro y actualización de condominios
- Gestión de información general del condominio
- Configuración de políticas específicas
- Estadísticas del condominio

#### 49. CalleService.php
**Propósito:**
Maneja la estructura de calles dentro de cada condominio.

**Responsabilidades:**
- Creación y modificación de calles
- Asignación de calles a condominios
- Gestión de numeración y nomenclatura
- Validación de direcciones

#### 50. CasaService.php
**Propósito:**
Gestiona la información de las casas individuales dentro del sistema.

**Responsabilidades:**
- Registro de casas por calle
- Asignación de residentes a casas
- Estado de ocupación
- Historial de residencia

#### 51. TagService.php
**Propósito:**
Administra el sistema de etiquetas y engomados para identificación vehicular.

**Responsabilidades:**
- Generación de códigos de engomados
- Asignación de tags a vehículos
- Control de vigencia
- Reportes de uso

#### 52. VisitanteService.php
**Propósito:**
Controla el acceso y registro de visitantes al condominio.

**Responsabilidades:**
- Registro de visitantes
- Autorización de acceso
- Historial de visitas
- Generación de códigos temporales

#### 53. AdminService.php
**Propósito:**
Proporciona funcionalidades administrativas del sistema.

**Responsabilidades:**
- Gestión de usuarios administrativos
- Configuración del sistema
- Monitoreo de actividades
- Generación de reportes administrativos

#### 54. VehiculoService.php
**Propósito:**
Gestiona el registro y control de vehículos en el condominio.

**Responsabilidades:**
- Registro de vehículos por residente
- Validación de placas
- Asignación de espacios de estacionamiento
- Control de acceso vehicular

#### 55. ReporteService.php
**Propósito:**
Genera reportes estadísticos y de actividad del sistema.

**Responsabilidades:**
- Reportes de acceso
- Estadísticas de uso
- Reportes de seguridad
- Exportación de datos

#### 56. NotificacionService.php
**Propósito:**
Maneja el sistema de notificaciones del condominio.

**Responsabilidades:**
- Envío de notificaciones
- Gestión de avisos
- Alertas de seguridad
- Comunicados generales

#### 57. ConfiguracionService.php
**Propósito:**
Administra las configuraciones dinámicas del sistema.

**Responsabilidades:**
- Configuraciones generales
- Parámetros de seguridad
- Personalización por condominio
- Backup de configuraciones

#### 58. EmailService.php
**Propósito:**
Gestiona el envío de correos electrónicos del sistema.

**Responsabilidades:**
- Envío de correos transaccionales
- Plantillas de email
- Cola de envío
- Logs de email

#### 59. EngomadoService.php
**Propósito:**
Servicio especializado para la gestión de engomados vehiculares.

**Responsabilidades:**
- Generación de códigos únicos
- Asignación a vehículos
- Control de vigencia
- Renovación automática

---

## 🏗️ Arquitectura de Modelos

### Inventario de Modelos de Datos

| N° | Archivo | Propósito |
|----|---------|-----------|
| 60 | `/public_html/models/BaseModel.php` | Modelo base con funcionalidades comunes |
| 61 | `/public_html/models/Persona.php` | Modelo de datos para personas |
| 62 | `/public_html/models/Condominio.php` | Modelo de datos para condominios |
| 63 | `/public_html/models/Calle.php` | Modelo de datos para calles |
| 64 | `/public_html/models/Casa.php` | Modelo de datos para casas |
| 65 | `/public_html/models/Tag.php` | Modelo de datos para tags/engomados |
| 66 | `/public_html/models/Visitante.php` | Modelo de datos para visitantes |
| 67 | `/public_html/models/Admin.php` | Modelo de datos para administradores |
| 68 | `/public_html/models/Vehiculo.php` | Modelo de datos para vehículos |
| 69 | `/public_html/models/Reporte.php` | Modelo de datos para reportes |
| 70 | `/public_html/models/Notificacion.php` | Modelo de datos para notificaciones |
| 71 | `/public_html/models/Configuracion.php` | Modelo de datos para configuraciones |

### Manual de Modelos

#### 60. BaseModel.php
**Propósito:**
Proporciona funcionalidades comunes a todos los modelos del sistema, incluyendo conexión a base de datos, validaciones básicas y operaciones CRUD estándar.

**Características:**
- Conexión PDO centralizada
- Validaciones genéricas
- Logging de operaciones
- Manejo de errores estándar

#### 61-71. Modelos Específicos
**Propósito General:**
Cada modelo representa una entidad específica del sistema de condominios, encapsulando la lógica de datos, validaciones específicas y relaciones entre entidades.

**Estructura Común:**
- Propiedades correspondientes a campos de base de datos
- Métodos de validación específicos
- Relaciones con otros modelos
- Métodos de búsqueda y filtrado

---

## 🌐 Endpoints API

### Inventario de APIs del Sistema

| N° | Archivo | Propósito |
|----|---------|-----------|
| 72 | `/public_html/apis/auth_api.php` | Endpoints de autenticación |
| 73 | `/public_html/apis/personas_api.php` | API para gestión de personas |
| 74 | `/public_html/apis/condominios_api.php` | API para gestión de condominios |
| 75 | `/public_html/apis/calles_api.php` | API para gestión de calles |
| 76 | `/public_html/apis/casas_api.php` | API para gestión de casas |
| 77 | `/public_html/apis/tags_api.php` | API para gestión de tags/engomados |
| 78 | `/public_html/apis/visitantes_api.php` | API para gestión de visitantes |
| 79 | `/public_html/apis/admin_api.php` | API para funciones administrativas |
| 80 | `/public_html/apis/vehiculos_api.php` | API para gestión de vehículos |
| 81 | `/public_html/apis/reportes_api.php` | API para generación de reportes |
| 82 | `/public_html/apis/notificaciones_api.php` | API para sistema de notificaciones |
| 83 | `/public_html/apis/configuracion_api.php` | API para configuración del sistema |

### Manual de APIs

#### Estructura Estándar de Endpoints
Todos los endpoints siguen un patrón RESTful estándar:
- **GET**: Consulta de datos
- **POST**: Creación de nuevos registros
- **PUT**: Actualización completa de registros
- **PATCH**: Actualización parcial de registros
- **DELETE**: Eliminación de registros

#### Autenticación
Todos los endpoints (excepto auth_api.php) requieren autenticación JWT válida en el header Authorization.

#### Respuestas Estándar
Todas las APIs retornan respuestas en formato JSON con estructura consistente:
- `success`: Boolean indicando éxito/fallo
- `data`: Datos solicitados o resultado de la operación
- `message`: Mensaje descriptivo
- `error`: Detalles del error (en caso de fallo)

---

## 🛡️ Sistema de Seguridad

### Inventario de Middlewares

| N° | Archivo | Propósito |
|----|---------|-----------|
| 84 | `/public_html/middlewares/AuthGuard.php` | Middleware de autenticación |
| 85 | `/public_html/middlewares/CsrfGuard.php` | Protección contra ataques CSRF |

### Manual de Middlewares

#### 84. AuthGuard.php
**Propósito:**
Intercepta todas las solicitudes API para validar la autenticación del usuario mediante tokens JWT.

**Funcionalidades:**
- Validación de tokens JWT
- Verificación de expiración
- Extracción de datos de usuario
- Redirección en caso de fallo

#### 85. CsrfGuard.php
**Propósito:**
Protege contra ataques Cross-Site Request Forgery validando tokens CSRF en formularios.

**Funcionalidades:**
- Generación de tokens CSRF
- Validación de tokens en requests
- Integración con formularios HTML
- Logs de intentos de ataque

---

## 🧪 Sistema de Pruebas

### Inventario de Tests

| N° | Archivo | Propósito |
|----|---------|-----------|
| 86-97 | `/public_html/tests/test_*.php` | Pruebas unitarias para cada módulo |

### Manual de Testing

**Estructura de Pruebas:**
Cada archivo de pruebas corresponde a un módulo específico del sistema y contiene:
- Pruebas de funcionalidad básica
- Validación de errores
- Pruebas de integración
- Casos límite

**Ejecución:**
Las pruebas pueden ejecutarse individualmente o como suite completa para validar la integridad del sistema.

---

## 📚 Documentación Adicional

### Archivos de Documentación

| N° | Archivo | Propósito |
|----|---------|-----------|
| 98 | `/public_html/docs/api.md` | Documentación completa de APIs |
| 99 | `/public_html/docs/instalacion.md` | Guía de instalación del sistema |
| 100 | `/public_html/docs/seguridad.md` | Políticas y configuración de seguridad |
| 101 | `/public_html/docs/migracion.md` | Guía para migración de datos |
| 102 | `/public_html/docs/testing.md` | Manual de pruebas del sistema |
| 103 | `/public_html/docs/deployment.md` | Guía de despliegue en producción |

---

## 🎨 Frontend y Recursos

### Inventario de Recursos Frontend

| Categoría | Cantidad | Ubicación | Propósito |
|-----------|----------|-----------|-----------|
| JavaScript | 13 archivos | `/public_html/js/` | Lógica del frontend |
| CSS | 14 archivos | `/public_html/css/` | Estilos y diseño |
| Templates HTML | 20+ archivos | `/public_html/templates/` | Plantillas de interfaz |

### Organización Frontend

**JavaScript:**
Cada archivo JS corresponde a una funcionalidad específica del sistema, proporcionando interactividad y comunicación con las APIs.

**CSS:**
Estilos organizados por componentes y páginas, siguiendo metodología BEM para nomenclatura de clases.

**Templates:**
Plantillas HTML organizadas por tipo de usuario (admin, resident) y funcionalidad, permitiendo reutilización y mantenimiento eficiente.

---

## 📋 Resumen Ejecutivo

**Total de Archivos:** 103+ archivos organizados
**Arquitectura:** MVC con separación clara de responsabilidades
**Seguridad:** JWT + CSRF + Configuraciones seguras
**Testing:** Suite completa de pruebas unitarias
**Documentación:** Manual completo de instalación y uso
**Frontend:** Interfaz moderna y responsive

El sistema Cyberhole Condominios está diseñado para ser escalable, mantenible y seguro, siguiendo las mejores prácticas de desarrollo web moderno.
