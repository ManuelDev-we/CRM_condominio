# 🏠 Sistema de Permisos - Condominios

## 📋 Resumen de Implementación

Se implementó el sistema de permisos para que cada administrador solo pueda gestionar los condominios que él mismo haya creado o le hayan sido asignados.

## 🔧 Cambios Realizados

### 1. Función `createCondominio()` - MODIFICADA ✅

**Antes:** Solo creaba el condominio en la tabla `condominios`
**Ahora:** 
- Crea el condominio en la tabla `condominios`
- Automáticamente inserta la relación en `admin_cond` (id_admin + id_condominio)
- Usa transacciones para garantizar consistencia

```php
// 1. Crear el condominio
$sql = "INSERT INTO condominios (nombre, direccion) VALUES (:nombre, :direccion)";

// 2. Asignar condominio al administrador en admin_cond
$sql_assign = "INSERT INTO admin_cond (id_admin, id_condominio) VALUES (:id_admin, :id_condominio)";
```

### 2. Función `getCondominios()` - MODIFICADA ✅

**Antes:** Devolvía todos los condominios
**Ahora:** 
- Si hay sesión de admin: Solo devuelve condominios del admin actual
- Si no hay sesión: Devuelve todos (para formularios públicos)

```sql
SELECT c.* FROM condominios c 
INNER JOIN admin_cond ac ON c.id_condominio = ac.id_condominio 
WHERE ac.id_admin = :id_admin 
ORDER BY c.nombre
```

### 3. Función `createCalle()` - MODIFICADA ✅

**Antes:** No verificaba permisos
**Ahora:** 
- Verifica que el admin tenga permisos sobre el condominio antes de crear calles
- Bloquea la acción si no tiene permisos

```php
// Verificar permisos sobre el condominio
$sql_check = "SELECT 1 FROM admin_cond WHERE id_admin = :id_admin AND id_condominio = :id_condominio";
if (!$stmt_check->fetchColumn()) {
    throw new Exception('No tienes permisos para agregar calles a este condominio', 403);
}
```

### 4. Función `createCasa()` - MODIFICADA ✅

**Antes:** No verificaba permisos
**Ahora:** 
- Verifica que el admin tenga permisos sobre el condominio antes de crear casas
- Bloquea la acción si no tiene permisos

```php
// Verificar permisos sobre el condominio
$sql_check = "SELECT 1 FROM admin_cond WHERE id_admin = :id_admin AND id_condominio = :id_condominio";
if (!$stmt_check->fetchColumn()) {
    throw new Exception('No tienes permisos para agregar casas a este condominio', 403);
}
```

## 🎯 Flujo de Trabajo

### Registro de Condominio
1. Admin se loguea
2. Crea condominio con nombre y dirección
3. Sistema crea registro en `condominios`
4. Sistema automáticamente crea relación en `admin_cond`
5. Admin ahora puede gestionar ese condominio

### Gestión de Calles/Casas
1. Admin selecciona condominio
2. Sistema verifica en `admin_cond` si tiene permisos
3. Si tiene permisos: Permite crear calles/casas
4. Si no tiene permisos: Bloquea con error 403

### Listado de Condominios
1. Si admin está logueado: Solo ve SUS condominios
2. Si no hay sesión admin: Ve todos (para formularios públicos)

## 🔒 Seguridad Implementada

- ✅ Verificación de sesión admin en operaciones críticas
- ✅ Validación de permisos en admin_cond antes de operaciones
- ✅ Transacciones para consistencia de datos
- ✅ Filtrado automático por permisos en listados
- ✅ Códigos de error HTTP apropiados (403 Forbidden)

## 🧪 Archivo de Pruebas

Se creó `test/test_permisos.html` para verificar:
- Login de administrador
- Creación de condominios con asignación automática
- Verificación de permisos en calles y casas
- Filtrado de condominios por admin

## 🎨 Sin Cambios en Login

- No se modificó el sistema de login existente
- No se cambió la estructura de sesiones
- Solo se agregaron validaciones de permisos en operaciones específicas

## 📊 Estructura de Tabla admin_cond

```sql
CREATE TABLE admin_cond (
  id_admin int(11) NOT NULL,
  id_condominio int(11) NOT NULL,
  PRIMARY KEY (id_admin, id_condominio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
```

## ✅ Objetivos Cumplidos

1. ✅ Al registrar condominio → Automáticamente se asigna al admin
2. ✅ Al listar condominios → Solo los del admin actual
3. ✅ Al registrar calles/casas → Verifica permisos del admin
4. ✅ Bloqueo de operaciones sin permisos → Error 403

## 🚀 Uso

1. Abrir `test/test_permisos.html`
2. Hacer login como administrador
3. Crear condominios (se asignan automáticamente)
4. Intentar crear calles/casas (solo en condominios propios)
5. Verificar que solo aparecen condominios del admin logueado
