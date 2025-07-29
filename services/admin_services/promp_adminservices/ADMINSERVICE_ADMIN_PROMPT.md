# ADMINSERVICE_ADMIN_PROMPT.md
## Prompt Especializado para AdminService.php

### 🎯 PROPÓSITO DEL SERVICIO
Administrar el perfil del administrador del sistema Cyberhole Condominios. Este servicio maneja exclusivamente las funcionalidades relacionadas con la cuenta administrativa, sin gestionar condominios ni otros recursos.

---

## 🏗️ ARQUITECTURA Y HERENCIA

### Clase Base
```php
class AdminService extends BaseAdminService
```

### Dependencias
- **Hereda de:** `BaseAdminService.php`
- **Modelo principal:** `Admin.php`
- **Posición en cascada:** Nivel 1 (Servicio base administrativo)

---

## 📚 MÉTODOS DEL MODELO ADMIN DISPONIBLES

### Métodos de Autenticación
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `adminLogin()` | string $email, string $password | array | Login de administrador |
| `adminRegister()` | array $data | int | Registro de administrador |
| `validateAdminCredentials()` | string $email, string $password | bool | Valida credenciales |

### Métodos de Gestión de Datos
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `findByEmail()` | string $email | array | Buscar por email |
| `findByEmailWithPassword()` | string $email | array | Buscar por email con contraseña |
| `hashPassword()` | string $password | string | Hash de contraseña |
| `getAllAdmins()` | - | array | Obtiene todos los administradores |

### Métodos de Validación
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `validateEmailFormat()` | string $email | bool | Valida formato de email |
| `validatePasswordLength()` | string $password | bool | Valida longitud de contraseña |

### Métodos de Roles y Permisos
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `assignAdminRole()` | int $adminId | bool | Asigna rol de administrador |
| `getAdminRole()` | - | string | Obtiene rol de administrador |

### Métodos de Seguridad
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `encryptSensitiveFields()` | array $data | array | Encripta campos sensibles |
| `decryptSensitiveFields()` | array $data | array | Desencripta campos sensibles |

### Métodos Base Heredados
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `create()` | array $data | int | Crear administrador |
| `findById()` | int $id | array | Buscar por ID |
| `update()` | int $id, array $data | bool | Actualizar administrador |
| `delete()` | int $id | bool | Eliminar administrador |
| `findAll()` | int $limit = 100 | array | Obtener todos los administradores |

---

## 🔧 FUNCIONES DE NEGOCIO REQUERIDAS

### 1. **Gestión de Perfil**
```php
public function actualizarPerfil($adminId, $datos)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Validar campos requeridos
    $this->validateRequiredFields($datos, ['nombre', 'email']);
    
    // Validar formato de email
    if (!$this->adminModel->validateEmailFormat($datos['email'])) {
        return $this->errorResponse('Formato de email inválido');
    }
    
    // Encriptar campos sensibles
    $datosEncriptados = $this->adminModel->encryptSensitiveFields($datos);
    
    // Actualizar perfil
    $resultado = $this->adminModel->update($adminId, $datosEncriptados);
    
    // Log de actividad
    $this->logAdminActivity('perfil_actualizado', ['admin_id' => $adminId]);
    
    return $this->successResponse($resultado, 'Perfil actualizado exitosamente');
}
```

### 2. **Cambio de Contraseña**
```php
public function cambiarContrasena($adminId, $contrasenaActual, $contrasenaNueva)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Validar contraseña actual
    $admin = $this->adminModel->findById($adminId);
    if (!$this->adminModel->validateAdminCredentials($admin['email'], $contrasenaActual)) {
        return $this->errorResponse('Contraseña actual incorrecta');
    }
    
    // Validar nueva contraseña
    if (!$this->adminModel->validatePasswordLength($contrasenaNueva)) {
        return $this->errorResponse('La contraseña debe tener al menos 8 caracteres');
    }
    
    // Hash de nueva contraseña
    $hashNueva = $this->adminModel->hashPassword($contrasenaNueva);
    
    // Actualizar contraseña
    $resultado = $this->adminModel->update($adminId, ['password' => $hashNueva]);
    
    // Log de actividad
    $this->logAdminActivity('contrasena_cambiada', ['admin_id' => $adminId]);
    
    return $this->successResponse($resultado, 'Contraseña actualizada exitosamente');
}
```

### 3. **Gestión de Preferencias**
```php
public function actualizarPreferencias($adminId, $preferencias)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Validar estructura de preferencias
    $this->validateRequiredFields($preferencias, ['notificaciones', 'tema', 'idioma']);
    
    // Encriptar preferencias si contienen datos sensibles
    $preferenciasEncriptadas = $this->adminModel->encryptSensitiveFields($preferencias);
    
    // Actualizar preferencias
    $resultado = $this->adminModel->update($adminId, ['preferencias' => json_encode($preferenciasEncriptadas)]);
    
    // Log de actividad
    $this->logAdminActivity('preferencias_actualizadas', ['admin_id' => $adminId]);
    
    return $this->successResponse($resultado, 'Preferencias actualizadas exitosamente');
}
```

### 4. **Gestión de Notificaciones**
```php
public function obtenerNotificaciones($adminId, $opciones = [])
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Aplicar rate limiting
    $this->enforceRateLimit('notificaciones_' . $adminId);
    
    // Obtener notificaciones (esto podría ser una tabla separada)
    $admin = $this->adminModel->findById($adminId);
    $preferencias = json_decode($admin['preferencias'], true);
    
    // Filtrar notificaciones según preferencias
    $notificaciones = $this->filtrarNotificacionesPorPreferencias($preferencias, $opciones);
    
    return $this->successResponse($notificaciones, 'Notificaciones obtenidas exitosamente');
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
private function validateProfileData($data)
{
    $required = ['nombre', 'email'];
    
    if (!$this->validateRequiredFields($data, $required)) {
        return false;
    }
    
    if (!$this->adminModel->validateEmailFormat($data['email'])) {
        return false;
    }
    
    return true;
}
```

---

## 🚫 RESTRICCIONES IMPORTANTES

### Lo que NO debe hacer este servicio:
- ❌ **NO gestionar condominios** (usar CondominioService)
- ❌ **NO manejar empleados** (usar EmpleadoService)
- ❌ **NO gestionar casas o calles** (usar CasaService/CalleService)
- ❌ **NO registrar accesos** (usar AccesosService)

### Scope limitado a:
- ✅ **Perfil administrativo únicamente**
- ✅ **Configuraciones de cuenta**
- ✅ **Preferencias personales**
- ✅ **Notificaciones del admin**

---

## 📋 ESTRUCTURA DE RESPUESTAS

### Éxito
```php
return $this->successResponse([
    'admin' => $adminData,
    'mensaje' => 'Operación completada exitosamente'
]);
```

### Error
```php
return $this->errorResponse(
    'Mensaje de error específico',
    400,
    ['campo' => 'detalle del error']
);
```

---

## 🔍 LOGGING REQUERIDO

### Actividades a registrar:
- ✅ Login/logout de administrador
- ✅ Cambios de contraseña
- ✅ Actualizaciones de perfil
- ✅ Cambios de preferencias
- ✅ Intentos de acceso fallidos

### Formato de log:
```php
$this->logAdminActivity('accion', [
    'admin_id' => $adminId,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'timestamp' => date('Y-m-d H:i:s'),
    'detalles' => $detalles
]);
```

---

## 📅 INFORMACIÓN DEL PROMPT
- **Fecha de creación:** 28 de Julio, 2025
- **Servicio:** AdminService.php
- **Posición en cascada:** Nivel 1 (Base administrativa)
- **Estado:** ✅ Listo para implementación

---

## 🎯 INSTRUCCIONES PARA COPILOT

Al generar código para AdminService.php:

1. **SIEMPRE heredar de BaseAdminService**
2. **USAR únicamente métodos del modelo Admin**
3. **APLICAR todas las validaciones de seguridad**
4. **REGISTRAR logs de todas las actividades**
5. **MANTENER scope limitado a gestión de perfil administrativo**
6. **NO duplicar funcionalidades de otros servicios**
7. **USAR encriptación para datos sensibles**
