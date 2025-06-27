# Guía de Instalación Rápida - Cyberhole Condominios

## 🚀 Instalación en 5 pasos

### 1. Clonar/Subir archivos
```bash
# Subir todos los archivos de Public_html/ a tu servidor web
# Asegurar que Public_html/ sea el DocumentRoot
```

### 2. Configurar variables de entorno
```bash
# Copiar archivo de ejemplo
cp .env.example .env

# Editar con tus credenciales reales
nano .env
```

### 3. Configurar base de datos
```sql
-- Conectar a tu servidor MySQL
-- Ejecutar archivo principal
source db/u837350477_Cuestionario.sql

-- Ejecutar tablas de seguridad
source db/security_tables.sql
```

### 4. Configurar permisos
```bash
# Permisos de archivos
chmod 644 *.html *.css *.js *.php
chmod 755 api/ config/ templates.html/
chmod 600 .env
chmod 644 .htaccess

# Crear directorio de logs
mkdir logs
chmod 755 logs
```

### 5. Probar instalación
```bash
# Probar API
curl -X POST https://tudominio.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@cyberhole.net","password":"admin123","type":"admin"}'

# Verificar headers de seguridad
curl -I https://tudominio.com/
```

## 📋 Lista de verificación

- [ ] Archivos subidos al servidor
- [ ] Archivo .env configurado con credenciales reales
- [ ] Base de datos creada y poblada
- [ ] Permisos de archivos configurados
- [ ] .htaccess activo y funcionando
- [ ] API respondiendo correctamente
- [ ] Headers de seguridad habilitados
- [ ] SSL/HTTPS configurado
- [ ] Archivo .env protegido (no accesible públicamente)

## 🔐 Credenciales de prueba

**Administrador:**
- Email: admin@cyberhole.net
- Password: admin123

**Residente:**
- Email: residente@cyberhole.net  
- Password: resident123

## ⚠️ Importante para producción

1. **Cambiar contraseñas por defecto**
2. **Generar JWT_SECRET único y seguro**
3. **Configurar SSL/HTTPS obligatorio**
4. **Verificar que .env no sea accesible públicamente**
5. **Configurar respaldos automáticos de BD**
6. **Habilitar logging de errores**
7. **Implementar monitoreo de seguridad**

## 🆘 Solución de problemas

### Error de conexión a BD
```bash
# Verificar credenciales en .env
# Verificar que el servidor MySQL esté corriendo
# Verificar permisos de usuario en MySQL
```

### Error 500 en API
```bash
# Verificar logs de PHP
tail -f logs/app.log

# Verificar que EnvManager.php sea accesible
# Verificar permisos de directorio config/
```

### Headers de seguridad no funcionan
```bash
# Verificar que mod_headers esté habilitado en Apache
# Verificar que .htaccess sea procesado
# Verificar sintaxis de .htaccess
```

## 📞 Soporte

Para problemas técnicos:
1. Revisar logs de error
2. Verificar configuración .env
3. Comprobar permisos de archivos
4. Validar conexión a base de datos
