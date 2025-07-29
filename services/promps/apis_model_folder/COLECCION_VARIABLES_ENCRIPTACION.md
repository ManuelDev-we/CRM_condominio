# 📊 COLECCIÓN COMPLETA DE VARIABLES Y DATOS A ENCRIPTAR
**Sistema Cyberhole Condominios - Análisis de Encriptación**

## 🎯 PROPÓSITO DEL DOCUMENTO
Análisis completo de todas las variables del sistema para determinar qué datos requieren **ENCRIPTACIÓN AES** vs **HASH BCRYPT + PEPPER** vs **DATOS EN CLARO**, excluyendo IDs, fechas, fotos, blog, condominios, calles y casas.

---

## 🔐 CLASIFICACIÓN DE DATOS POR TIPO DE PROTECCIÓN

### 🟥 **HASH BCRYPT + PEPPER** (Contraseñas únicamente)

#### **📋 TABLA: `admin`** → **Admin.php**
- ✅ `contrasena` - **HASH BCRYPT + PEPPER**

#### **📋 TABLA: `personas`** → **Persona.php**
- ✅ `contrasena` - **HASH BCRYPT + PEPPER**

### 🟨 **ENCRIPTACIÓN AES** (Datos sensibles personales)

#### **📋 TABLA: `admin`** → **Admin.php**
- 🔒 `nombres` - **ENCRIPTAR AES**
- 🔒 `apellido1` - **ENCRIPTAR AES**
- 🔒 `apellido2` - **ENCRIPTAR AES**
- 🔒 `correo` - **ENCRIPTAR AES**

#### **📋 TABLA: `personas`** → **Persona.php**
- 🔒 `curp` - **ENCRIPTAR AES** (dato muy sensible)
- 🔒 `nombres` - **ENCRIPTAR AES**
- 🔒 `apellido1` - **ENCRIPTAR AES**
- 🔒 `apellido2` - **ENCRIPTAR AES**
- 🔒 `correo_electronico` - **ENCRIPTAR AES**
- 🔒 `fecha_nacimiento` - **ENCRIPTAR AES** (dato sensible)

#### **📋 TABLA: `personas_unidad`** → **Dispositivo.php**
- 🔒 `telefono_1` - **ENCRIPTAR AES**
- 🔒 `telefono_2` - **ENCRIPTAR AES**
- 🔒 `curp` - **ENCRIPTAR AES** (dato muy sensible)
- 🔒 `nombres` - **ENCRIPTAR AES**
- 🔒 `apellido1` - **ENCRIPTAR AES**
- 🔒 `apellido2` - **ENCRIPTAR AES**
- 🔒 `fecha_nacimiento` - **ENCRIPTAR AES** (dato sensible)

#### **📋 TABLA: `tags`** → **Tag.php**
- 🔒 `codigo_tag` - **ENCRIPTAR AES** (identificador sensible)

#### **📋 TABLA: `engomados`** → **Engomado.php**
- 🔒 `placa` - **ENCRIPTAR AES** (dato sensible vehicular)
- 🔒 `modelo` - **ENCRIPTAR AES**
- 🔒 `color` - **ENCRIPTAR AES**
- 🔒 `anio` - **ENCRIPTAR AES**

#### **📋 TABLA: `empleados_condominio`** → **Empleado.php**
- 🔒 `nombres` - **ENCRIPTAR AES**
- 🔒 `apellido1` - **ENCRIPTAR AES**
- 🔒 `apellido2` - **ENCRIPTAR AES**
- 🔒 `puesto` - **ENCRIPTAR AES** (información laboral sensible)
- 🔒 `fecha_contrato` - **ENCRIPTAR AES** (información laboral sensible)

#### **📋 TABLA: `tareas`** → **Empleado.php**
- 🔒 `descripcion` - **ENCRIPTAR AES** (puede contener información sensible)

#### **📋 TABLA: `claves_registro`** → **Casa.php**
- 🔒 `codigo` - **ENCRIPTAR AES** (código de acceso sensible)

### 🟢 **DATOS EN CLARO** (Sin encriptación - Excluidos por solicitud)

#### **📋 DATOS EXCLUIDOS EXPLÍCITAMENTE:**
- ❌ **IDs:** Todos los campos `id_*` - NO ENCRIPTAR
- ❌ **Fechas:** `fecha_*`, `creado_en`, timestamps - NO ENCRIPTAR
- ❌ **Fotos:** `foto`, `imagen` - NO ENCRIPTAR
- ❌ **Blog:** Toda la tabla `blog` - NO ENCRIPTAR
- ❌ **Condominios:** Toda la tabla `condominios` - NO ENCRIPTAR
- ❌ **Calles:** Toda la tabla `calles` - NO ENCRIPTAR
- ❌ **Casas:** Toda la tabla `casas` - NO ENCRIPTAR

#### **📋 DATOS DE CONTROL EN CLARO:**
- ✅ `activo` - Campos booleanos de control
- ✅ `usado` - Estados de proceso
- ✅ `jerarquia` - Niveles de acceso
- ✅ `tipo_dispositivo` - Enums/categorías

---

## 📊 RESUMEN ESTADÍSTICO DE ENCRIPTACIÓN

### 🟥 **HASH BCRYPT + PEPPER:** 2 campos
- `admin.contrasena`
- `personas.contrasena`

### 🟨 **ENCRIPTACIÓN AES:** 25 campos
- **Admin (4):** nombres, apellido1, apellido2, correo
- **Personas (6):** curp, nombres, apellido1, apellido2, correo_electronico, fecha_nacimiento
- **Personas Unidad (7):** telefono_1, telefono_2, curp, nombres, apellido1, apellido2, fecha_nacimiento
- **Tags (1):** codigo_tag
- **Engomados (4):** placa, modelo, color, anio
- **Empleados (5):** nombres, apellido1, apellido2, puesto, fecha_contrato
- **Tareas (1):** descripcion
- **Claves Registro (1):** codigo

### 🟢 **DATOS EN CLARO:** 50+ campos
- Todos los IDs, fechas, fotos, estados, enums
- Todas las tablas excluidas (blog, condominios, calles, casas)

---

## 🔧 IMPLEMENTACIÓN TÉCNICA REQUERIDA

### 🔩 **CONFIGURACIÓN .ENV (YA DISPONIBLE)**
```properties
# Configuración de encriptación
ENCRYPTION_ALGORITHM=AES-256-CBC
AES_KEY=CyberholeProd2025AESKey32CharLong!@#
AES_METHOD=AES-256-CBC
BCRYPT_ROUNDS=14
PEPPER_SECRET=CyberholeProdCondominios2025PepperSecretKey!@#$%
```

### 🛠️ **MODELOS A MODIFICAR**
1. **Crear:** `CryptoModel.php` - Modelo maestro de encriptación
2. **Modificar:** `Admin.php` - Implementar encriptación de campos sensibles
3. **Modificar:** `Persona.php` - Implementar encriptación de campos sensibles
4. **Modificar:** `Dispositivo.php` - Implementar encriptación (personas_unidad)
5. **Modificar:** `Tag.php` - Implementar encriptación de código_tag
6. **Modificar:** `Engomado.php` - Implementar encriptación de datos vehiculares
7. **Modificar:** `Empleado.php` - Implementar encriptación de datos laborales
8. **Modificar:** `Casa.php` - Implementar encriptación de claves_registro

### 🔄 **FLUJO DE ENCRIPTACIÓN**
```php
// HASH CONTRASEÑAS
$hashedPassword = CryptoModel::hashPasswordWithPepper($password);

// ENCRIPTAR DATOS SENSIBLES
$encryptedName = CryptoModel::encryptData($nombres);
$encryptedEmail = CryptoModel::encryptData($correo);

// DESENCRIPTAR PARA MOSTRAR
$decryptedName = CryptoModel::decryptData($encryptedName);
$decryptedEmail = CryptoModel::decryptData($encryptedEmail);

// VERIFICAR CONTRASEÑAS
$isValid = CryptoModel::verifyPasswordWithPepper($password, $hashedPassword);
```

---

## 🎯 CAMPOS ESPECÍFICOS POR MODELO

### 🔴 **Admin.php - 5 campos a proteger**
```php
// HASH BCRYPT + PEPPER
'contrasena' => CryptoModel::hashPasswordWithPepper($data['contrasena'])

// ENCRIPTACIÓN AES
'nombres' => CryptoModel::encryptData($data['nombres'])
'apellido1' => CryptoModel::encryptData($data['apellido1'])
'apellido2' => CryptoModel::encryptData($data['apellido2'])
'correo' => CryptoModel::encryptData($data['correo'])
```

### 🔴 **Persona.php - 7 campos a proteger**
```php
// HASH BCRYPT + PEPPER
'contrasena' => CryptoModel::hashPasswordWithPepper($data['contrasena'])

// ENCRIPTACIÓN AES
'curp' => CryptoModel::encryptData($data['curp'])
'nombres' => CryptoModel::encryptData($data['nombres'])
'apellido1' => CryptoModel::encryptData($data['apellido1'])
'apellido2' => CryptoModel::encryptData($data['apellido2'])
'correo_electronico' => CryptoModel::encryptData($data['correo_electronico'])
'fecha_nacimiento' => CryptoModel::encryptData($data['fecha_nacimiento'])
```

### 🔴 **Dispositivo.php (personas_unidad) - 7 campos a proteger**
```php
// ENCRIPTACIÓN AES
'telefono_1' => CryptoModel::encryptData($data['telefono_1'])
'telefono_2' => CryptoModel::encryptData($data['telefono_2'])
'curp' => CryptoModel::encryptData($data['curp'])
'nombres' => CryptoModel::encryptData($data['nombres'])
'apellido1' => CryptoModel::encryptData($data['apellido1'])
'apellido2' => CryptoModel::encryptData($data['apellido2'])
'fecha_nacimiento' => CryptoModel::encryptData($data['fecha_nacimiento'])
```

### 🔴 **Tag.php - 1 campo a proteger**
```php
// ENCRIPTACIÓN AES
'codigo_tag' => CryptoModel::encryptData($data['codigo_tag'])
```

### 🔴 **Engomado.php - 4 campos a proteger**
```php
// ENCRIPTACIÓN AES
'placa' => CryptoModel::encryptData($data['placa'])
'modelo' => CryptoModel::encryptData($data['modelo'])
'color' => CryptoModel::encryptData($data['color'])
'anio' => CryptoModel::encryptData($data['anio'])
```

### 🔴 **Empleado.php - 5 campos a proteger**
```php
// ENCRIPTACIÓN AES
'nombres' => CryptoModel::encryptData($data['nombres'])
'apellido1' => CryptoModel::encryptData($data['apellido1'])
'apellido2' => CryptoModel::encryptData($data['apellido2'])
'puesto' => CryptoModel::encryptData($data['puesto'])
'fecha_contrato' => CryptoModel::encryptData($data['fecha_contrato'])
```

### 🔴 **Casa.php (claves_registro) - 1 campo a proteger**
```php
// ENCRIPTACIÓN AES
'codigo' => CryptoModel::encryptData($data['codigo'])
```

---

## 🚨 CONSIDERACIONES CRÍTICAS

### ⚠️ **RENDIMIENTO**
- Encriptación/desencriptación añade overhead computacional
- Considerar cache para datos frecuentemente accedidos
- Búsquedas por campos encriptados requieren desencriptación completa

### ⚠️ **BÚSQUEDAS**
- Búsquedas LIKE no funcionarán en campos encriptados
- Implementar hash adicional para búsquedas si es necesario
- Índices tradicionales no funcionarán en campos encriptados

### ⚠️ **MIGRACIÓN**
- Planificar migración de datos existentes
- Scripts de conversión para datos ya almacenados
- Backup completo antes de implementar encriptación

### ⚠️ **BACKUP Y RECOVERY**
- Las claves de encriptación deben respaldarse por separado
- Sin las claves, los datos encriptados son irrecuperables
- Implementar rotación de claves periódica

---

**📋 Análisis generado:** 10 de Julio, 2025  
**🔍 Total campos analizados:** 75+  
**🔐 Campos a encriptar:** 27 (25 AES + 2 HASH)  
**📊 Estado:** LISTO PARA IMPLEMENTACIÓN
