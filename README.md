# Cyberhole Condominios - Sistema de Gestión de Condominios

## Descripción General

Sistema completo de gestión de condominios con autenticación segura JWT, API RESTful, separación de lógica para administradores y residentes, y plantillas HTML dinámicas. Incluye integración completa frontend-backend con operaciones CRUD seguras.

## 🚀 Características Principales

- **Autenticación JWT Segura**: Sistema de login con tokens JWT y refresh tokens
- **API RESTful Completa**: Endpoints estructurados para todas las operaciones CRUD
- **Separación de Roles**: Funcionalidades específicas para administradores y residentes
- **Seguridad Avanzada**: Protección contra SQL injection, XSS, CSRF y otros ataques
- **Frontend Moderno**: JavaScript ES6+ con fetch API y manejo de errores
- **Base de Datos MySQL**: Configuración remota con PDO y prepared statements
- **Configuración por Ambiente**: Variables de entorno para desarrollo y producción

## Estructura del Proyecto

```
c:\Users\farid\OneDrive\Escritorio\Cyberhole_condominios\
├── Public_html\
│   ├── index.html                                     # Página principal
│   ├── api-demo.html                                  # Demo de integración API
│   ├── .env                                           # Variables de entorno (NO subir a repo)
│   ├── .env.example                                   # Ejemplo de configuración
│   ├── .gitignore                                     # Archivos a ignorar en git
│   ├── .htaccess                                      # Configuración seguridad Apache
│   ├── api\
│   │   └── index.php                                  # API backend RESTful
│   ├── config\
│   │   └── EnvManager.php                             # Gestor de variables de entorno
│   ├── db\
│   │   ├── u837350477_Cuestionario.sql                # Estructura base de datos
│   │   └── security_tables.sql                       # Tablas de seguridad y logs
│   ├── js\
│   │   ├── api-client.js                              # Cliente API principal (NUEVO)
│   │   ├── api-demo.js                                # Demos de integración API (NUEVO)
│   │   ├── admin.js                                   # Sistema administrativo integrado
│   │   ├── auth-manager.js                            # Gestión de autenticación JWT
│   │   ├── registro.js                                # Registro con encriptación
│   │   ├── resi.js                                    # Sistema de residentes integrado
│   │   ├── objeto_admin\
│   │   │   ├── calles.js                              # Gestión de calles admin
│   │   │   ├── casas.js                               # Gestión de casas admin
│   │   │   ├── condominios.js                         # Gestión de condominios admin
│   │   │   ├── empleados.js                           # Gestión de empleados admin
│   │   │   ├── engomados.js                           # Gestión de engomados admin
│   │   │   ├── entrance.js                            # Control acceso admin
│   │   │   ├── personas.js                            # Gestión de personas admin
│   │   │   ├── tags.js                                # Gestión de tags admin
│   │   │   └── task.js                                # Gestión de tareas admin
│   │   └── objeto_residente\
│   │       ├── engomados.js                           # Gestión engomados residente
│   │       ├── entrance.js                            # Control acceso residente
│   │       └── tags.js                                # Gestión tags residente
│   ├── php\                                           # Directorio PHP adicional
│   └── templates.html\
│       ├── admin_template.html                        # Panel administrativo completo
│       ├── blog.html                                  # Blog/noticias
│       ├── condominio.html                            # Gestión específica de condominio
│       ├── control.html                               # Panel de control
│       ├── entrace.html                               # Control de acceso
│       ├── footer.html                                # Footer (solo post-login)
│       ├── header.html                                # Header (solo post-login)
│       ├── login.html                                 # Página de login
│       ├── profile.html                               # Perfil de usuario
│       ├── register.html                              # Registro de usuarios
│       ├── street.html                                # Gestión de calles
│       ├── task.html                                  # Gestión de tareas
│       └── style_html\
│           ├── admin_setup.css                        # Estilos configuración admin
│           ├── admin_template.css                     # Estilos panel admin
│           ├── blog.css                               # Estilos blog
│           ├── condominio.css                         # Estilos condominio
│           ├── control.css                            # Estilos panel control
│           ├── entrance.css                           # Estilos control acceso
│           ├── footer.css                             # Estilos footer
│           ├── header.css                             # Estilos header
│           ├── index.css                              # Estilos página principal
│           ├── profile.css                            # Estilos perfil
│           ├── resident.css                           # Estilos panel residente
│           ├── street.css                             # Estilos gestión calles
│           └── task.css                               # Estilos gestión tareas
└── README.md                                          # Documentación del proyecto
```

## Características Principales

### 🔐 Autenticación Segura
- Separación completa entre administradores y residentes
- Tablas independientes: `administradores` y `personas`
- Encriptación de contraseñas con hash seguro
- Encriptación AES para emails
- Gestión de sesiones con AuthManager

### 👨‍💼 Panel Administrativo
- Dashboard con estadísticas en tiempo real
- Gestión completa de condominios
- Administración de tareas y trabajadores
- Control de accesos y permisos
- Generación de reportes

### 🏠 Panel de Residentes
- Dashboard personalizado
- Visualización de avisos del condominio
- Gestión de pagos y estados de cuenta
- Registro de visitantes con QR
- Reserva de amenidades
- Reporte de incidencias

### 🎨 Interfaz Moderna
- Diseño responsive y moderno
- Plantillas HTML dinámicas
- CSS modular por componente
- Experiencia de usuario optimizada

## Configuración e Instalación

### 1. Base de Datos
```bash
# 1. Copiar archivo de configuración
cp .env.example .env

# 2. Editar .env con tus credenciales reales
nano .env

# 3. Ejecutar scripts SQL
# Ejecutar primero el archivo SQL principal
source db/u837350477_Cuestionario.sql

# Luego ejecutar las tablas de seguridad
source db/security_tables.sql
```

### 2. Configuración de Variables de Entorno
```env
# Editar el archivo .env con tus credenciales:
DB_HOST=tu_servidor_mysql
DB_NAME=tu_base_datos
DB_USER=tu_usuario
DB_PASS=tu_contraseña
JWT_SECRET=tu_clave_secreta_muy_segura
SITE_URL=https://tudominio.com
```

### 2. Configuración de Seguridad
- ✅ **Variables de entorno (.env) para credenciales seguras**
- ✅ **Base de datos remota MySQL con conexión segura**
- ✅ **API con autenticación JWT implementada**
- ✅ **Protección contra inyección SQL con PDO prepared statements**
- ✅ **Headers de seguridad configurados (.htaccess)**
- ✅ **Rate limiting implementado**
- ✅ **Validación de origen para prevenir manipulación DOM**
- ✅ **Rutas protegidas con autorización por roles**

### 3. Servidor Web
- Apache/Nginx con soporte PHP 7.4+
- Extensiones requeridas: PDO, OpenSSL
- Configurar DocumentRoot hacia Public_html/
- Asegurar que el archivo .env no sea accesible públicamente

### 3. Configuración del Servidor
```bash
# Subir archivos al servidor cyberhole.net
# Asegurar que el directorio Public_html/ sea el DocumentRoot

# Verificar permisos de archivos
chmod 644 *.html *.css *.js
chmod 755 api/
chmod 600 config/env.php
chmod 644 .htaccess

# Crear directorio de logs
mkdir logs
chmod 755 logs
```

### 4. Pruebas de Conexión
```bash
# Probar conexión a la base de datos
curl -X POST https://cyberhole.net/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@cyberhole.net","password":"admin123","type":"admin"}'

# Verificar headers de seguridad
curl -I https://cyberhole.net/
```

## Uso del Sistema

### Login
1. Acceder a `templates.html/login.html`
2. Seleccionar tipo de usuario (Admin/Residente)
3. Ingresar credenciales
4. Redirección automática según tipo

### Panel Administrativo
- URL: `templates.html/admin_template.html`
- Funciones principales:
  - Gestión de condominios
  - Administración de tareas
  - Control de trabajadores
  - Generación de reportes

### Panel de Residentes
- URL: `templates.html/condominio.html`
- Funciones principales:
  - Ver avisos y noticias
  - Gestionar pagos
  - Registrar visitantes
  - Reservar amenidades

## Arquitectura del Sistema

### Frontend
- **HTML5**: Plantillas semánticas y accesibles
- **CSS3**: Estilos modulares con variables CSS
- **JavaScript ES6+**: Clases y métodos modernos
- **Fetch API**: Comunicación asíncrona con backend

### Backend
- **PHP 7.4+**: API REST con manejo de errores
- **MySQL**: Base de datos relacional
- **PDO**: Acceso seguro a base de datos
- **JSON**: Formato de intercambio de datos

### Seguridad
- **Password Hashing**: bcrypt para contraseñas
- **AES Encryption**: Para datos sensibles
- **SQL Injection**: Prevención con prepared statements
- **CORS**: Configuración de origen cruzado
- **Authentication**: Validación de sesiones

## API Endpoints

### Administrador
```
GET    /api/admin/condominios       # Listar condominios
POST   /api/admin/condominios       # Crear condominio
PUT    /api/admin/condominios/:id   # Actualizar condominio
DELETE /api/admin/condominios/:id   # Eliminar condominio

GET    /api/admin/tareas            # Listar tareas
POST   /api/admin/tareas            # Crear tarea
DELETE /api/admin/tareas/:id        # Eliminar tarea

GET    /api/admin/trabajadores      # Listar trabajadores
POST   /api/admin/trabajadores      # Crear trabajador
DELETE /api/admin/trabajadores/:id  # Eliminar trabajador

GET    /api/admin/dashboard/stats   # Estadísticas dashboard
```

### Residente
```
GET    /api/resident/dashboard/stats    # Estadísticas residente
GET    /api/resident/activity          # Actividad reciente
GET    /api/resident/announcements     # Avisos
GET    /api/resident/payments          # Pagos
POST   /api/resident/payments/:id/pay  # Realizar pago
GET    /api/resident/incidents         # Incidencias
POST   /api/resident/incidents         # Reportar incidencia
GET    /api/resident/visitors          # Visitantes
GET    /api/resident/reservations      # Reservas
POST   /api/resident/reservations      # Crear reserva
```

## 🔗 Integración API Frontend-Backend

### Arquitectura de la API

El sistema cuenta con una arquitectura API RESTful completa que conecta el frontend JavaScript con el backend PHP de manera segura y eficiente.

#### Componentes Principales:

1. **ApiClient (`js/api-client.js`)**: Cliente principal para todas las comunicaciones con la API
2. **AdminSystem (`js/admin.js`)**: Sistema administrativo con métodos CRUD integrados
3. **ResidentSystem (`js/resi.js`)**: Sistema de residentes con funcionalidades específicas
4. **API Backend (`api/index.php`)**: Endpoints RESTful con autenticación y validación

### Características de la Integración:

- **🔐 Autenticación JWT**: Manejo automático de tokens y refresh tokens
- **🛡️ Seguridad**: Headers de seguridad, validación de datos, protección CORS
- **🔄 Reintentos**: Sistema de reintentos automáticos para peticiones fallidas
- **⚡ Interceptores**: Manejo automático de errores y autenticación
- **📊 Validación**: Validación de datos en frontend y backend
- **🚨 Notificaciones**: Sistema de notificaciones de usuario integrado

### Uso Básico:

```javascript
// Inicializar sistemas
await admin.init();
await residente.init();

// Operaciones CRUD para administradores
const condominios = await admin.getAllCondominios();
const nuevoCondominio = await admin.createCondominio(datos);
const actualizado = await admin.updateCondominio(id, datos);
await admin.deleteCondominio(id);

// Operaciones para residentes
const perfil = await residente.getMyProfile();
const pagos = await residente.getMyPayments();
const acceso = await residente.createAccess(datosAcceso);
```

### Demo Interactiva:

Abre `api-demo.html` en tu navegador para ver todas las funcionalidades en acción:

```bash
# Abrir demo (asegúrate de tener el servidor corriendo)
open http://localhost/api-demo.html
```

### Endpoints API Disponibles:

#### Autenticación:
- `POST /api/auth/login` - Iniciar sesión
- `POST /api/auth/logout` - Cerrar sesión
- `POST /api/auth/refresh` - Refrescar token
- `GET /api/auth/check` - Verificar sesión

#### Administración:
- `GET /api/admin/dashboard` - Datos del dashboard
- `GET /api/admin/condominios` - Listar condominios
- `POST /api/admin/condominios` - Crear condominio
- `PUT /api/admin/condominios/{id}` - Actualizar condominio
- `DELETE /api/admin/condominios/{id}` - Eliminar condominio
- `GET /api/admin/trabajadores` - Gestión de trabajadores
- `GET /api/admin/tareas` - Gestión de tareas
- `GET /api/admin/pagos` - Gestión de pagos
- `GET /api/admin/incidencias` - Gestión de incidencias

#### Residentes:
- `GET /api/resident/profile` - Perfil del residente
- `PUT /api/resident/profile` - Actualizar perfil
- `GET /api/resident/accesses` - Accesos del residente
- `POST /api/resident/accesses` - Crear nuevo acceso
- `GET /api/resident/payments` - Pagos del residente
- `GET /api/resident/incidencias` - Incidencias del residente

### Manejo de Errores:

```javascript
try {
    const result = await admin.createCondominio(datos);
    admin.showNotification('Condominio creado exitosamente', 'success');
} catch (error) {
    admin.handleError(error, 'Crear Condominio');
}
```

### Validación de Datos:

```javascript
const validationRules = {
    nombre: { required: true, type: 'string', minLength: 3 },
    email: { required: true, type: 'email' },
    telefono: { required: false, type: 'string', minLength: 10 }
};

const errors = apiClient.validateData(datos, validationRules);
if (errors) {
    throw new Error('Datos inválidos: ' + JSON.stringify(errors));
}
```

## Personalización

### Estilos
- Modificar variables CSS en cada archivo de estilo
- Colores principales en `:root` de cada CSS
- Responsive breakpoints configurables

### Funcionalidades
- Agregar nuevos endpoints en `api/index.php`
- Crear nuevas plantillas en `templates.html/`
- Extender clases JavaScript según necesidades

## Consideraciones de Seguridad

1. **Autenticación**: Implementar JWT para producción
2. **Validación**: Validar todos los inputs en frontend y backend
3. **Sanitización**: Limpiar datos antes de almacenarlos
4. **HTTPS**: Usar conexión segura en producción
5. **Rate Limiting**: Implementar límites de peticiones
6. **Logs**: Registrar accesos y errores importantes

## Mantenimiento

### Actualizaciones
- Revisar dependencias regularmente
- Mantener PHP y MySQL actualizados
- Actualizar bibliotecas JavaScript si se usan

### Monitoreo
- Configurar logs de errores
- Monitorear rendimiento de BD
- Verificar espacio en disco

### Backup
- Respaldar base de datos regularmente
- Mantener copias del código fuente
- Documentar cambios importantes

## Soporte y Desarrollo

Para desarrollo adicional o soporte:
1. Revisar logs de errores
2. Verificar configuración de BD
3. Comprobar permisos de archivos
4. Validar configuración del servidor web

## Licencia

Sistema desarrollado para gestión de condominios. Todos los derechos reservados.
