# 🚀 Guía de Despliegue en Hostinger - Cyberhole Condominios

## 📋 Checklist Pre-Despliegue

### ✅ Archivos Listos para Subir:
- [x] **Frontend completo** con AJAX integrado
- [x] **Backend PHP** con APIs RESTful
- [x] **Base de datos** configurada (NO subir el archivo .sql)
- [x] **Configuración de seguridad** implementada
- [x] **Sistema de autenticación** funcional
- [x] **Redirecciones correctas** configuradas

## 🌐 Pasos para Despliegue en Hostinger

### 1. Preparación de Archivos
```bash
# Comprimir la carpeta public_html (sin el archivo .sql)
# Excluir estos archivos/carpetas:
- /db/u837350477_Cuestionario.sql (NO SUBIR)
- /logs/* (se crearán automáticamente)
- /cache/* (se crearán automáticamente)
```

### 2. Configuración de Base de Datos en Hostinger

#### 2.1 Crear Base de Datos
1. Ir a **Panel de Control > Bases de Datos MySQL**
2. Crear nueva base de datos: `u837350477_Cuestionario`
3. Crear usuario: `u837350477_DEV`
4. Asignar todos los privilegios

#### 2.2 Importar Estructura
1. Ir a **phpMyAdmin**
2. Seleccionar la base de datos creada
3. Importar el archivo `db/u837350477_Cuestionario.sql`
4. Verificar que todas las tablas se crearon correctamente

### 3. Configuración de Archivos

#### 3.1 Verificar .env
```bash
# Editar public_html/.env con los datos reales de Hostinger:
DB_HOST=localhost                    # O el host que proporcione Hostinger
DB_NAME=u837350477_Cuestionario     # Nombre de tu base de datos
DB_USER=u837350477_DEV              # Tu usuario de BD
DB_PASS=TU_CONTRASEÑA_REAL          # Tu contraseña real
```

#### 3.2 Subir Archivos
1. Acceder a **Panel de Control > Administrador de Archivos**
2. Ir a `public_html/`
3. Subir todos los archivos EXCEPTO:
   - `db/u837350477_Cuestionario.sql`
   - Carpetas `logs/` y `cache/` (se crearán automáticamente)

### 4. Configuración de Permisos

#### 4.1 Permisos de Directorio
```bash
uploads/ → 755 (lectura/escritura)
config/ → 755 (lectura/escritura)
apis/ → 755 (lectura/ejecución)
```

#### 4.2 Permisos de Archivos
```bash
.env → 644 (lectura)
.htaccess → 644 (lectura)
*.php → 644 (lectura/ejecución)
```

### 5. Verificación del Sistema

#### 5.1 Ejecutar Verificador
1. Ir a: `https://tu-dominio.com/system_check.php`
2. Verificar que todos los checks sean exitosos
3. Si hay errores, corregirlos antes de continuar

#### 5.2 Probar Funcionalidades
1. **Registro de Administrador**: `/templates.html/register_admin/register.html`
2. **Login de Administrador**: `/templates.html/register_admin/login.html`
3. **Registro de Residente**: `/templates.html/register_resi/register.html`
4. **Login de Residente**: `/templates.html/register_resi/login.html`

## 🔧 Configuración Específica de Hostinger

### SSL/HTTPS
```bash
# En .htaccess, descomentar estas líneas si tienes SSL:
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### PHP Version
- Asegurar PHP 7.4 o superior en Panel de Control

### Error Reporting
- En producción está configurado para NO mostrar errores
- Los errores se guardan en `/logs/php_errors.log`

## 🎯 URLs del Sistema Desplegado

### Páginas Principales:
- **Inicio**: `https://tu-dominio.com/`
- **Verificador**: `https://tu-dominio.com/system_check.php`

### Administradores:
- **Registro**: `https://tu-dominio.com/templates.html/register_admin/register.html`
- **Login**: `https://tu-dominio.com/templates.html/register_admin/login.html`
- **Dashboard**: `https://tu-dominio.com/templates.html/admin_template/blog.html`

### Residentes:
- **Registro**: `https://tu-dominio.com/templates.html/register_resi/register.html`
- **Login**: `https://tu-dominio.com/templates.html/register_resi/login.html`
- **Panel**: `https://tu-dominio.com/templates.html/resi_template/acces.html`

### APIs:
- **Autenticación**: `https://tu-dominio.com/apis/auth.php`
- **Tareas**: `https://tu-dominio.com/apis/tasks.php`

## 🛠️ Solución de Problemas Comunes

### Error de Conexión a BD
```bash
# Verificar en .env:
1. Host correcto (localhost o el que proporcione Hostinger)
2. Nombre de BD correcto
3. Usuario y contraseña correctos
4. Puerto correcto (usualmente 3306)
```

### Error 500
```bash
# Revisar:
1. Permisos de archivos
2. Sintaxis en .htaccess
3. Logs de PHP en /logs/php_errors.log
```

### Formularios no funcionan
```bash
# Verificar:
1. JavaScript está cargando correctamente
2. APIs responden correctamente
3. CORS está configurado si es necesario
```

## 📞 Soporte Post-Despliegue

### Logs de Sistema
- **PHP Errors**: `/logs/php_errors.log`
- **App Logs**: `/logs/app.log`
- **Apache Logs**: Panel de Control > Logs

### Monitoreo
- Verificar `/system_check.php` periódicamente
- Revisar logs por errores
- Probar funcionalidades críticas

## 🔒 Seguridad en Producción

### Archivos Protegidos
- `.env` → No accesible via web
- `/logs/` → Protegido por .htaccess
- `.sql` files → Bloqueados por .htaccess

### Configuración de Seguridad
- Rate limiting activado
- Validación de entrada implementada
- Encriptación AES para datos sensibles
- Hash seguro de contraseñas

## 📈 Optimizaciones Incluidas

### Performance
- Compresión gzip activada
- Cache headers configurados
- Optimización de consultas DB

### SEO & UX
- Meta tags configurados
- Responsive design
- Páginas de error personalizadas

---

## ✨ Sistema Listo para Producción

**Estado**: ✅ **COMPLETAMENTE FUNCIONAL**

**Funcionalidades Verificadas**:
- ✅ Autenticación segura
- ✅ Redirecciones correctas
- ✅ CRUD de tareas
- ✅ Gestión de usuarios
- ✅ APIs RESTful
- ✅ Frontend-Backend integrado
- ✅ Configuración de seguridad
- ✅ Base de datos optimizada

**¡Listo para subir a Hostinger! 🚀**
