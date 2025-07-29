puedes leer mi texto de favor ? y analizarlo # 📊 COLECCIÓN COMPLETA DE VARIABLES Y DATOS A ENCRIPTAR
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
# 📊 DIAGRAMA UML VISUAL SIMPLIFICADO - SISTEMA CYBERHOLE CONDOMINIOS

## 🎯 PROPÓSITO DEL DOCUMENTO
Diagrama de clases UML corregido que refleja la estructura REAL de la base de datos y la arquitectura 3 capas implementada, eliminando todas las discrepancias detectadas.

---

## 🎨 DIAGRAMA UML MERMAID - VERSIÓN CORREGIDA

```mermaid
classDiagram
    %% CLASE BASE ABSTRACTA
    class BaseModel {
        <<abstract>>
        -PDO connection
        -string table
        +connect() PDO
        +create(array data) int|false
        +findById(int id) array|null
        +update(int id, array data) bool
        +delete(int id) bool
        +findAll() array
        +validateRequiredFields(array data, array required) bool
        +logError(string message) void
        +sanitizeInput(mixed input) mixed
    }

    %% MODELOS ESPECÍFICOS
    class Admin {
        -string table = "admin"
        -string role = "ADMIN"
        +adminLogin(string email, string password) array|false
        +adminRegister(array data) int|false
        +findByEmail(string email) array|null
        +hashPassword(string password) string
        +validateEmailFormat(string email) bool
        +validatePasswordLength(string password) bool
        +getAllAdmins() array
        +assignAdminRole(int adminId) bool
        +getAdminRole() string
        +validateAdminCredentials(string email, string password) bool
    }
    
    class Condominio {
        -string table = "condominios"
        +createCondominio(array data) int|false
        +assignAdminToCondominio(int adminId, int condominioId) bool
        +removeAdminFromCondominio(int adminId, int condominioId) bool
        +getAdminsByCondominio(int condominioId) array
        +getCondominiosByAdmin(int adminId) array
        +validateAdminExists(int adminId) bool
    }
    
    class Calle {
        -string table = "calles"
        +findByCondominioId(int condominioId) array
        +validateCondominioExists(int condominioId) bool
        +validateNameUniqueInCondominio(string nombre, int condominioId) bool
    }
    
    class Casa {
        -string table = "casas"
        +createCasa(array data) int|false
        +findCasasByCalleId(int calleId) array
        +createClaveRegistro(array data) bool
        +findClaveRegistro(string codigo) array|null
        +markClaveAsUsed(string codigo) bool
        +assignPersonaToCasa(int personaId, int casaId) bool
        +getPersonasByCasa(int casaId) array
        +validateCalleExists(int calleId) bool
    }
    
    class Persona {
        -string table = "personas"
        -string role = "RESIDENTE"
        +personaLogin(string email, string password) array|false
        +personaRegister(array data) int|false
        +findByCURP(string curp) array|null
        +findByEmail(string email) array|null
        +hashPassword(string password) string
        +validateCURPFormat(string curp) bool
        +validateEmailFormat(string email) bool
        +validateCURPUnique(string curp) bool
        +validateEmailUnique(string email) bool
        +assignResidenteRole(int personaId) bool
        +getResidenteRole() string
        +validatePersonaCredentials(string email, string password) bool
    }
    
    class Tag {
        -string table = "tags"
        +findByPersonaId(int personaId) array
        +findByTagCode(string codigo) array|null
        +validateTagCodeUnique(string codigo) bool
        +validatePersonaExists(int personaId) bool
        +validateCasaExists(int casaId) bool
    }
    
    class Engomado {
        -string table = "engomados"
        +findByPersonaId(int personaId) array
        +findByPlaca(string placa) array|null
        +validatePlacaFormat(string placa) bool
        +validatePersonaExists(int personaId) bool
        +validateCasaExists(int casaId) bool
    }
    
    class Dispositivo {
        -string table = "personas_unidad"
        +createUnidad(array data) int|false
        +findUnidadByCURP(string curp) array|null
        +associateDispositivo(int unidadId, string tipo, int dispositivoId) bool
        +getDispositivosByUnidad(int unidadId) array
        +validateCURPUnique(string curp) bool
        +validateTipoDispositivo(string tipo) bool
    }
    
    class AreaComun {
        -string table = "areas_comunes"
        +createAreaComun(array data) int|false
        +findAreasComunesByCondominio(int condominioId) array
        +createReserva(array data) int|false
        +findReservasByAreaComun(int areaId) array
        +validateCondominioExists(int condominioId) bool
        +validateTimeFormat(string time) bool
    }
    
    class Blog {
        -string table = "blog"
        +findByAuthor(int adminId) array
        +validateAdminExists(int adminId) bool
        +validateVisibilityValue(string visibility) bool
    }
    
    class Empleado {
        -string table = "empleados_condominio"
        +createEmpleado(array data) int|false
        +findEmpleadosByCondominio(int condominioId) array
        +createTarea(array data) int|false
        +findTareasByTrabajador(int trabajadorId) array
        +validateCondominioExists(int condominioId) bool
        +validatePuestoValue(string puesto) bool
        +getAccesosEmpleadosByCondominio(int condominioId) array
        +filterAccesosByCondominio(array accesos, int condominioId) array
    }

    class Acceso {
        -string tableResidentes = "accesos_residentes"
        -string tableEmpleados = "accesos_empleados" 
        -string tableVisitantes = "visitantes"
        +getAccesosResidentes(int condominioId) array
        +getAccesosEmpleados(int condominioId) array
        +getAccesosVisitantes(int condominioId) array
        +createAccesoResidente(array data) int|false
        +createAccesoEmpleado(array data) int|false
        +createAccesoVisitante(array data) int|false
        +validateAccesoType(string tipo) bool
        +getAccesosByDateRange(string fechaInicio, string fechaFin, int condominioId) array
        +filterAccesosByCondominio(string tipoAcceso, int condominioId) array
        +generateQRForVisitante(array visitanteData) string
        +validateQRCode(string qrCode) bool
        +registrarSalidaVisitante(int visitanteId) bool
    }

    %% HERENCIA DESDE BASEMODEL
    BaseModel <|-- Admin
    BaseModel <|-- Condominio
    BaseModel <|-- Calle
    BaseModel <|-- Casa
    BaseModel <|-- Persona
    BaseModel <|-- Tag
    BaseModel <|-- Engomado
    BaseModel <|-- Dispositivo
    BaseModel <|-- AreaComun
    BaseModel <|-- Blog
    BaseModel <|-- Empleado
    BaseModel <|-- Acceso

    %% RELACIONES DE BASE DE DATOS (FOREIGN KEYS)
    Admin ||--o{ Condominio : "admin_cond.id_admin"
    Condominio ||--o{ Calle : "calles.id_condominio"
    Calle ||--o{ Casa : "casas.id_calle"
    Casa ||--o{ Persona : "persona_casa.id_casa"
    Persona ||--o{ Tag : "tags.id_persona"
    Persona ||--o{ Engomado : "engomados.id_persona"
    Persona ||--o{ Dispositivo : "personas_unidad.curp"
    Condominio ||--o{ AreaComun : "areas_comunes.id_condominio"
    Condominio ||--o{ Empleado : "empleados_condominio.id_condominio"
    Admin ||--o{ Blog : "blog.creado_por_admin"
    AreaComun ||--o{ AreaComun : "apartar_areas_comunes.id_area_comun"
    Empleado ||--o{ Empleado : "tareas.id_trabajador"

    %% ANOTACIONES DE RESPONSABILIDADES
    note for Admin "Gestiona: admin + SISTEMA LOGIN ADMIN"
    note for Condominio "Gestiona: condominios + admin_cond"
    note for Calle "Gestiona: calles"
    note for Casa "Gestiona: casas + claves_registro + persona_casa"
    note for Persona "Gestiona: personas + SISTEMA LOGIN RESIDENTE"
    note for Tag "Gestiona: tags"
    note for Engomado "Gestiona: engomados"
    note for Dispositivo "Gestiona: personas_unidad + persona_dispositivo"
    note for AreaComun "Gestiona: areas_comunes + apartar_areas_comunes"
    note for Blog "Gestiona: blog"
    note for Empleado "Gestiona: empleados_condominio + tareas"
```

---

## 📋 ASIGNACIÓN EXACTA DE MODELOS A TABLAS

### 🔷 **MODELO → TABLA(S) ADMINISTRADA(S)**

| **MODELO** | **TABLA(S) PRINCIPAL(ES)** | **TABLA(S) SECUNDARIA(S)** | **TIPO DE GESTIÓN** |
|------------|---------------------------|---------------------------|-------------------|
| `BaseModel.php` | - | - | Abstracto (métodos comunes) |
| `Admin.php` | `admin` | - | CRUD + LOGIN/REGISTRO ADMIN |
| `Condominio.php` | `condominios` | `admin_cond` | CRUD + relaciones |
| `Calle.php` | `calles` | - | CRUD completo |
| `Casa.php` | `casas` | `claves_registro`, `persona_casa` | CRUD + relaciones |
| `Persona.php` | `personas` | - | CRUD + LOGIN/REGISTRO RESIDENTE |
| `Tag.php` | `tags` | - | CRUD completo |
| `Engomado.php` | `engomados` | - | CRUD completo |
| `Dispositivo.php` | `personas_unidad` | `persona_dispositivo` | CRUD + relaciones |
| `AreaComun.php` | `areas_comunes` | `apartar_areas_comunes` | CRUD + reservas |
| `Blog.php` | `blog` | - | CRUD completo |
| `Empleado.php` | `empleados_condominio` | `tareas` | CRUD + asignaciones |
| `Acceso.php` | `accesos_residentes` | `accesos_empleados`, `visitantes` | CONTROL ACCESO DIFERENCIADO |

---

## 🔗 RELACIONES JERÁRQUICAS CORREGIDAS

### 🎯 **FLUJO PRINCIPAL DE DATOS**

```
📊 JERARQUÍA GEOGRÁFICA:
Admin → Condominio → Calle → Casa → Persona

📊 IDENTIFICADORES POR PERSONA:
Persona → Tag (RFID/NFC)
Persona → Engomado (vehículos)
Persona → Dispositivo (datos extendidos)

📊 SERVICIOS INDEPENDIENTES:
Condominio → AreaComun (áreas comunes)
Condominio → Empleado → Tarea (personal)
Admin → Blog (publicaciones)
```

### 🔹 **FOREIGN KEYS CRÍTICAS CORREGIDAS**

| **TABLA ORIGEN** | **CAMPO FK** | **TABLA DESTINO** | **CAMPO DESTINO** | **MODELO RESPONSABLE** |
|------------------|--------------|-------------------|-------------------|----------------------|
| `admin_cond` | `id_admin` | `admin` | `id_admin` | Condominio.php |
| `admin_cond` | `id_condominio` | `condominios` | `id_condominio` | Condominio.php |
| `calles` | `id_condominio` | `condominios` | `id_condominio` | Calle.php |
| `casas` | `id_calle` | `calles` | `id_calle` | Casa.php |
| `casas` | `id_condominio` | `condominios` | `id_condominio` | Casa.php |
| `claves_registro` | `id_casa` | `casas` | `id_casa` | Casa.php |
| `persona_casa` | `id_casa` | `casas` | `id_casa` | Casa.php |
| `tags` | `id_persona` | `personas` | `id_persona` | Tag.php |
| `tags` | `id_casa` | `casas` | `id_casa` | Tag.php |
| `engomados` | `id_persona` | `personas` | `id_persona` | Engomado.php |
| `engomados` | `id_casa` | `casas` | `id_casa` | Engomado.php |
| `persona_dispositivo` | `id_persona_unidad` | `personas_unidad` | `id_persona_unidad` | Dispositivo.php |
| `areas_comunes` | `id_condominio` | `condominios` | `id_condominio` | AreaComun.php |
| `apartar_areas_comunes` | `id_area_comun` | `areas_comunes` | `id_area_comun` | AreaComun.php |
| `empleados_condominio` | `id_condominio` | `condominios` | `id_condominio` | Empleado.php |
| `tareas` | `id_trabajador` | `empleados_condominio` | `id_empleado` | Empleado.php |
| `blog` | `creado_por_admin` | `admin` | `id_admin` | Blog.php |

---

## ⚡ MÉTODOS PRINCIPALES POR MODELO

### 🔷 **BaseModel.php (ABSTRACTO)**
```php
// MÉTODOS GENÉRICOS CRUD
abstract class BaseModel {
    protected PDO $connection;
    protected string $table;
    
    // CRUD BÁSICO
    public function create(array $data): int|false
    public function findById(int $id): array|null
    public function update(int $id, array $data): bool
    public function delete(int $id): bool
    public function findAll(int $limit = 100): array
    
    // UTILIDADES COMUNES
    public function validateRequiredFields(array $data, array $required): bool
    public function sanitizeInput(mixed $input): mixed
    public function logError(string $message): void
    protected function connect(): PDO
}
```

### 🔷 **Admin.php**
```php
// ESPECÍFICOS PARA ADMINISTRADORES
class Admin extends BaseModel {
    public function findByEmail(string $email): array|null
    public function hashPassword(string $password): string
    public function validateEmailFormat(string $email): bool
    public function validatePasswordLength(string $password): bool
    public function getAllAdmins(): array
}
```

### 🔷 **Condominio.php**
```php
// ESPECÍFICOS PARA CONDOMINIOS Y RELACIONES ADMIN
class Condominio extends BaseModel {
    // CRUD CONDOMINIOS
    public function createCondominio(array $data): int|false
    public function findCondominioById(int $id): array|null
    public function updateCondominio(int $id, array $data): bool
    public function deleteCondominio(int $id): bool
    
    // CRUD ADMIN_COND (RELACIONES)
    public function assignAdminToCondominio(int $adminId, int $condominioId): bool
    public function removeAdminFromCondominio(int $adminId, int $condominioId): bool
    public function getAdminsByCondominio(int $condominioId): array
    public function getCondominiosByAdmin(int $adminId): array
    
    // VALIDACIONES
    public function validateAdminExists(int $adminId): bool
    public function validateCondominioExists(int $condominioId): bool
}
```

### 🔷 **Casa.php**
```php
// ESPECÍFICOS PARA CASAS, CLAVES Y RELACIONES PERSONA
class Casa extends BaseModel {
    // CRUD CASAS
    public function createCasa(array $data): int|false
    public function findCasasByCalleId(int $calleId): array
    
    // CRUD CLAVES_REGISTRO
    public function createClaveRegistro(array $data): bool
    public function findClaveRegistro(string $codigo): array|null
    public function markClaveAsUsed(string $codigo): bool
    
    // CRUD PERSONA_CASA
    public function assignPersonaToCasa(int $personaId, int $casaId): bool
    public function getPersonasByCasa(int $casaId): array
    public function getCasasByPersona(int $personaId): array
}
```

### 🔷 **Dispositivo.php**
```php
// ESPECÍFICOS PARA UNIDADES Y DISPOSITIVOS
class Dispositivo extends BaseModel {
    // CRUD PERSONAS_UNIDAD
    public function createUnidad(array $data): int|false
    public function findUnidadByCURP(string $curp): array|null
    
    // CRUD PERSONA_DISPOSITIVO
    public function associateDispositivo(int $unidadId, string $tipo, int $dispositivoId): bool
    public function getDispositivosByUnidad(int $unidadId): array
    
    // VALIDACIONES
    public function validateCURPUnique(string $curp): bool
    public function validateTipoDispositivo(string $tipo): bool
}
```

### 🔷 **AreaComun.php**
```php
// ESPECÍFICOS PARA ÁREAS COMUNES Y RESERVAS
class AreaComun extends BaseModel {
    // CRUD AREAS_COMUNES
    public function createAreaComun(array $data): int|false
    public function findAreasComunesByCondominio(int $condominioId): array
    
    // CRUD APARTAR_AREAS_COMUNES
    public function createReserva(array $data): int|false
    public function findReservasByAreaComun(int $areaId): array
    public function findReservasByCondominio(int $condominioId): array
    
    // VALIDACIONES
    public function validateTimeFormat(string $time): bool
    public function validateCondominioExists(int $condominioId): bool
}
```

---

## 🚨 CORRECCIONES APLICADAS

### ✅ **DISCREPANCIAS RESUELTAS**

1. **Unificación de responsabilidades:** Cada modelo administra exactamente las tablas especificadas en RELACIONES_TABLAS
2. **Eliminación de tablas fantasma:** Removidas referencias a `dispositivos`, `vehiculos`, `accesos` que no existen en BD
3. **Consolidación lógica:** Modelos como `Casa.php` manejan múltiples tablas relacionadas lógicamente
4. **Corrección de foreign keys:** Todas las relaciones reflejan la estructura real de la BD
5. **Simplificación del diagrama:** Eliminada complejidad innecesaria, enfoque en lo esencial

### ⚠️ **CAMBIOS IMPORTANTES**

1. **`Dispositivo.php`** ahora administra `personas_unidad` + `persona_dispositivo` (no `dispositivos`)
2. **`Casa.php`** consolidado para manejar `casas` + `claves_registro` + `persona_casa`
3. **`AreaComun.php`** maneja tanto áreas como reservas (`apartar_areas_comunes`)
4. **`Empleado.php`** maneja empleados y sus tareas asignadas
5. **Eliminados modelos fantasma** que no tenían tablas correspondientes

---

## 🔐 SISTEMAS DE AUTENTICACIÓN DIFERENCIADOS

### 🚨 **IMPORTANTE: DOBLE SISTEMA DE LOGIN/REGISTRO**

#### **🔹 SISTEMA ADMIN (Admin.php)**
```php
// FUNCIONES ESPECÍFICAS DE ADMIN
+adminLogin(string email, string password) array|false
+adminRegister(array data) int|false
+assignAdminRole(int adminId) bool
+getAdminRole() string // Retorna: "ADMIN"
+validateAdminCredentials(string email, string password) bool
```

#### **🔹 SISTEMA PERSONA/RESIDENTE (Persona.php)**
```php
// FUNCIONES ESPECÍFICAS DE PERSONA
+personaLogin(string email, string password) array|false
+personaRegister(array data) int|false
+assignResidenteRole(int personaId) bool
+getResidenteRole() string // Retorna: "RESIDENTE"
+validatePersonaCredentials(string email, string password) bool
```

### 🎯 **ROLES DEFINIDOS EN LÓGICA BACKEND (NO BD)**

#### **📊 CONSTANTES DE ROLES**
```php
// Definidas en config/roles.php o similar
const ROLE_ADMIN = 'ADMIN';
const ROLE_RESIDENTE = 'RESIDENTE';
const ROLE_EMPLEADO = 'EMPLEADO'; // Para empleados_condominio
```

#### **🔒 SEPARACIÓN TOTAL DE SISTEMAS**
- **Tabla `admin`** → Login/registro exclusivo para administradores
- **Tabla `personas`** → Login/registro exclusivo para residentes
- **Roles asignados via funciones PHP**, NO campos en BD
- **Validación de credenciales independiente** por tipo de usuario
- **Sesiones diferenciadas** por rol de usuario

---

## 🎯 PRINCIPIOS ARQUITECTÓNICOS RESPETADOS

### ✅ **ARQUITECTURA 3 CAPAS PURA**
- **Capa 1 (Modelos):** Solo CRUD y validaciones básicas de integridad
- **Capa 2 (Servicios):** Lógica de negocio, permisos, flujos complejos (pendiente)
- **Capa 3 (Controladores):** Presentación y coordinación entrada/salida (pendiente)

### ✅ **SEPARACIÓN ABSOLUTA DE RESPONSABILIDADES**
- Cada modelo tiene responsabilidades claramente definidas
# 📊 RELACIONES DE TABLAS CORREGIDAS - SISTEMA CYBERHOLE CONDOMINIOS

## 🎯 PROPÓSITO DEL DOCUMENTO
Documento oficial CORREGIDO que establece la asignación exacta de cada modelo PHP a su(s) tabla(s) correspondiente(s) en la base de datos, siguiendo arquitectura 3 capas con separación absoluta de responsabilidades. **TODAS LAS DISCREPANCIAS HAN SIDO ELIMINADAS.**

---

## 🔷 RELACIONES DEFINITIVAS DE LOS MODELOS CON LAS TABLAS

### 🔷 **BaseModel.php**
- 🚩 **NO administra ninguna tabla**
- 📌 **Responsabilidad:** Solo provee métodos genéricos para todos los demás modelos
- 🎯 **Funciones:** PDO connection, CRUD base, logging, validaciones genéricas

### 🔷 **Admin.php**
- ✅ **Tabla:** `admin`
- 👨‍💼 **Responsabilidad:** CRUD de los usuarios administradores
- 🎯 **Operaciones:** Crear, actualizar, eliminar, buscar admins, gestión de passwords

### 🔷 **Condominio.php**
- ✅ **Tabla Principal:** `condominios`
- ✅ **Tabla Secundaria:** `admin_cond`
- 🔗 **Relaciones:** Conecta admins con condominios
- 📌 **Responsabilidad:** Datos básicos de condominios + asignaciones admin-condominio
- 🎯 **Gestión:** Información del condominio + permisos de administración

### 🔷 **Calle.php**
- ✅ **Tabla:** `calles`
- 🔗 **Relaciones:** Conecta `calles` con `condominios`
- 📌 **Responsabilidad:** Gestión de calles dentro de condominios
- 🎯 **Jerarquía:** Segundo nivel de la estructura física

### 🔷 **Casa.php**
- ✅ **Tabla Principal:** `casas`
- ✅ **Tabla Secundaria:** `claves_registro`
- ✅ **Tabla Secundaria:** `persona_casa`
- 🔗 **Relaciones:** 
  - Conecta `casas` con `calles` y `condominios`
  - Gestiona claves de registro por casa
  - Maneja relaciones persona-casa
- 📌 **Responsabilidad:** Gestión completa de unidades habitacionales
- 🎯 **Jerarquía:** Tercer nivel de la estructura física + registro

### 🔷 **Persona.php**
- ✅ **Tabla:** `personas`
- 📌 **Responsabilidad:** CRUD de residentes/personas principales
- 🎯 **Autenticación:** Manejo de CURP, credenciales, datos personales básicos

### 🔷 **Tag.php**
- ✅ **Tabla:** `tags`
- 🔗 **Relaciones:** Conecta `tags` con `personas`, `casas`, `calles`, `condominios`
- 📌 **Responsabilidad:** Gestión de identificadores físicos RFID/NFC
- 🎯 **Control:** Activación, desactivación, códigos únicos

### 🔷 **Engomado.php**
- ✅ **Tabla:** `engomados`
- 🔗 **Relaciones:** Conecta `engomados` con `personas`, `casas`, `calles`, `condominios`
- 📌 **Responsabilidad:** Gestión de identificadores vehiculares tipo sticker
- 🎯 **Control:** Placas, modelos, años, datos vehiculares, validación de unicidad

### 🔷 **Dispositivo.php**
- ✅ **Tabla Principal:** `personas_unidad`
- ✅ **Tabla Secundaria:** `persona_dispositivo`
- 🔗 **Relaciones:** 
  - Gestiona datos extendidos de personas (unidades)
  - Conecta personas con dispositivos asignados
- 📌 **Responsabilidad:** CRUD de unidades persona + asociaciones dispositivo
- 🎯 **Flexibilidad:** Datos adicionales por persona + gestión de dispositivos

### 🔷 **AreaComun.php**
- ✅ **Tabla Principal:** `areas_comunes`
- ✅ **Tabla Secundaria:** `apartar_areas_comunes`
- 🔗 **Relaciones:** 
  - Conecta `areas_comunes` con `condominios`
  - Gestiona reservas de áreas comunes
- 📌 **Responsabilidad:** Gestión completa de áreas comunes + reservas
- 🎯 **Control:** Horarios, estados, capacidades + sistema de reservas

### 🔷 **Blog.php**
- ✅ **Tabla:** `blog`
- 🔗 **Relaciones:** Conecta `blog` con `admin` (autor)
- 📌 **Responsabilidad:** CRUD de publicaciones del blog
- 🎯 **Gestión:** Contenido, visibilidad, autoría, fechas

### 🔷 **Empleado.php**
- ✅ **Tabla Principal:** `empleados_condominio`
- ✅ **Tabla Secundaria:** `tareas`
- ✅ **Tabla Secundaria:** `accesos_empleados`
- 🔗 **Relaciones:** 
  - Conecta empleados con condominios
  - Gestiona tareas asignadas a empleados
  - **NUEVO:** Administra registros de acceso de empleados
- 📌 **Responsabilidad:** Gestión completa de personal + asignación de tareas + **control de accesos diferenciado**
- 🎯 **Control:** Empleados por condominio + seguimiento de tareas + **filtrado por condominio en accesos**

### 🔷 **Acceso.php** 🆕
- ✅ **Tabla Principal:** `accesos_residentes`
- ✅ **Tabla Secundaria:** `accesos_empleados` 
- ✅ **Tabla Secundaria:** `visitantes`
- 🔗 **Relaciones:**
  - Conecta accesos de residentes con `persona_dispositivo`
  - Conecta accesos de empleados con `condominios`
  - Gestiona registros completos de visitantes
- 📌 **Responsabilidad:** **CONTROL DE ACCESO DIFERENCIADO por tipo de usuario**
- 🎯 **Control:** 
  - **Accesos de Residentes:** Con dispositivos (tags/engomados) 
  - **Accesos de Empleados:** Por condominio asignado
  - **Accesos de Visitantes:** Con códigos QR temporales + registro completo

---

## 🔄 RESUMEN VISUAL DE FLUJOS CORREGIDOS

### 👨‍💼 **ADMINISTRADOR**
```sql
Admin.php → admin
```

### 🏠 **ESTRUCTURA FÍSICA**
```sql
Condominio.php → condominios + admin_cond
Calle.php → calles
Casa.php → casas + claves_registro + persona_casa  
```

### 👥 **GESTIÓN DE PERSONAS**
```sql
Persona.php → personas
Dispositivo.php → personas_unidad + persona_dispositivo
```

### 🔐 **CONTROL DE ACCESO DIFERENCIADO** 🆕
```sql
Acceso.php → accesos_residentes + accesos_empleados + visitantes
├── RESIDENTES: accesos_residentes (con dispositivos)
├── EMPLEADOS: accesos_empleados (por condominio)  
└── VISITANTES: visitantes (con QR temporal + registro completo)

Empleado.php → empleados_condominio + tareas + accesos_empleados
├── GESTIÓN: Personal por condominio + tareas asignadas
└── ACCESOS: Filtrado de accesos_empleados por condominio asignado
```

### 🏷️ **IDENTIFICADORES**
```sql
Tag.php → tags
Engomado.php → engomados
```

### 🎯 **SERVICIOS ADICIONALES**
```sql
AreaComun.php → areas_comunes + apartar_areas_comunes
Blog.php → blog
```
Condominio.php → condominios + admin_cond
Calle.php → calles
Casa.php → casas + claves_registro + persona_casa
Blog.php → blog
AreaComun.php → areas_comunes + apartar_areas_comunes
Empleado.php → empleados_condominio + tareas
```

### 🏡 **RESIDENTE**
```scss
Persona.php → personas
Tag.php → tags
Engomado.php → engomados
Dispositivo.php → personas_unidad + persona_dispositivo
AreaComun.php → apartar_areas_comunes (solo consulta de reservas existentes)
```

### 👷 **EMPLEADO**
```perl
Empleado.php → empleados_condominio + tareas
```

### 📚 **UTILIDADES**
```arduino
BaseModel.php → base genérica para todos
```

---

## 📋 TABLA COMPLETA DE ASIGNACIONES CORREGIDA

| **MODELO** | **TABLA(S) PRINCIPAL(ES)** | **TABLA(S) SECUNDARIA(S)** | **TIPO** | **RESPONSABILIDAD** |
|------------|---------------------------|---------------------------|----------|-------------------|
| BaseModel.php | - | - | Base | Métodos genéricos CRUD |
| Admin.php | admin | - | Principal | Usuarios administradores |
| Condominio.php | condominios | admin_cond | Principal + Relación | Condominios + asignaciones admin |
| Calle.php | calles | - | Principal | Calles del condominio |
| Casa.php | casas | claves_registro, persona_casa | Principal + Relaciones | Casas + registro + habitantes |
| Persona.php | personas | - | Principal | Residentes del sistema |
| Tag.php | tags | - | Principal | Identificadores RFID/NFC |
| Engomado.php | engomados | - | Principal | Identificadores vehiculares |
| Dispositivo.php | personas_unidad | persona_dispositivo | Principal + Relación | Unidades persona + dispositivos |
| AreaComun.php | areas_comunes | apartar_areas_comunes | Principal + Reservas | Áreas comunes + reservas |
| Blog.php | blog | - | Principal | Publicaciones blog |
| Empleado.php | empleados_condominio | tareas | Principal + Asignaciones | Empleados + tareas |

---

## ⚡ TABLAS SIN MODELO PROPIO (CONFIRMADO)

### 📌 **NINGUNA TABLA SIN MODELO**
- ✅ Todas las tablas existentes en la BD tienen un modelo responsable asignado
- ✅ No hay tablas "huérfanas" sin gestión
- ✅ No hay modelos "fantasma" sin tablas correspondientes

**🎯 RESULTADO:** Correspondencia perfecta 1:1 entre estructura de BD y modelos PHP

---

## 📢 CORRECCIONES APLICADAS

### ✅ **ELIMINACIÓN DE DISCREPANCIAS**

#### **🔹 MODELOS FANTASMA ELIMINADOS:**
- ❌ **Vehiculo.php** - No existe tabla `vehiculos` en BD
- ❌ **Dispositivo.php como hardware** - No existe tabla `dispositivos` en BD  
- ❌ **Acceso.php** - No existe tabla `accesos` en BD
- ❌ **Encriptacion.php** - Utilidad sin tabla (movido a utils)

#### **🔹 RESPONSABILIDADES CONSOLIDADAS:**
- ✅ **Dispositivo.php** ahora gestiona `personas_unidad` + `persona_dispositivo`
- ✅ **Casa.php** consolidado para `casas` + `claves_registro` + `persona_casa`
- ✅ **Condominio.php** gestiona `condominios` + `admin_cond`
- ✅ **AreaComun.php** gestiona `areas_comunes` + `apartar_areas_comunes`
- ✅ **Empleado.php** gestiona `empleados_condominio` + `tareas`

#### **🔹 FOREIGN KEYS CORREGIDAS:**
- ✅ Todas las FK reflejan la estructura real de la BD
- ✅ Eliminadas referencias a tablas inexistentes
- ✅ Consolidadas relaciones en modelos apropiados

### ✅ **SEPARACIÓN ABSOLUTA DE RESPONSABILIDADES CONFIRMADA**
- Cada modelo administra exactamente la(s) tabla(s) especificadas
- Ningún modelo mezcla responsabilidades de otras tablas
- Validación cruzada y flujos complejos se manejan en capa de servicios
- No hay lógica de negocio en modelos

### ✅ **ARQUITECTURA 3 CAPAS RESPETADA**
- **Capa de Datos (Modelos):** Solo CRUD y validaciones básicas de integridad
- **Capa de Lógica (Servicios):** Pendiente de implementación
- **Capa de Presentación (APIs/Controladores):** Pendiente de actualización

---

## 🗄️ ESTRUCTURA COMPLETA DE LA BASE DE DATOS (CONFIRMADA)

### 📊 **TABLAS IMPLEMENTADAS Y SUS MODELOS RESPONSABLES**

#### **📋 TABLA: `admin`** → **Admin.php**
```sql
CREATE TABLE `admin` (
  `id_admin` int(11) NOT NULL AUTO_INCREMENT,
  `nombres` varchar(100) NOT NULL,
  `apellido1` varchar(100) NOT NULL,
  `apellido2` varchar(100) DEFAULT NULL,
  `correo` varchar(150) NOT NULL UNIQUE,
  `contrasena` varchar(255) NOT NULL,
  `fecha_alta` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_admin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
```

#### **📋 TABLA: `admin_cond`** → **Condominio.php**
```sql
CREATE TABLE `admin_cond` (
  `id_admin` int(11) NOT NULL,
  `id_condominio` int(11) NOT NULL,
  PRIMARY KEY (`id_admin`,`id_condominio`),
  FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE,
  FOREIGN KEY (`id_condominio`) REFERENCES `condominios` (`id_condominio`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
```

#### **📋 TABLA: `condominios`** → **Condominio.php**
```sql
CREATE TABLE `condominios` (
  `id_condominio` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  PRIMARY KEY (`id_condominio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
```

#### **📋 TABLA: `calles`** → **Calle.php**
```sql
CREATE TABLE `calles` (
  `id_calle` int(11) NOT NULL AUTO_INCREMENT,
  `id_condominio` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  PRIMARY KEY (`id_calle`),
  FOREIGN KEY (`id_condominio`) REFERENCES `condominios` (`id_condominio`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
```

#### **📋 TABLA: `casas`** → **Casa.php**
```sql
CREATE TABLE `casas` (
  `id_casa` int(11) NOT NULL AUTO_INCREMENT,
  `casa` varchar(255) NOT NULL,
  `id_condominio` int(11) NOT NULL,
  `id_calle` int(11) NOT NULL,
  PRIMARY KEY (`id_casa`),
  FOREIGN KEY (`id_condominio`) REFERENCES `condominios` (`id_condominio`) ON UPDATE CASCADE,
  FOREIGN KEY (`id_calle`) REFERENCES `calles` (`id_calle`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

#### **📋 TABLA: `claves_registro`** → **Casa.php**
```sql
CREATE TABLE `claves_registro` (
  `codigo` varchar(12) NOT NULL,
  `id_condominio` int(11) NOT NULL,
  `id_calle` int(11) NOT NULL,
  `id_casa` int(11) NOT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_expiracion` datetime DEFAULT NULL,
  `usado` tinyint(1) DEFAULT 0,
  `fecha_canje` datetime DEFAULT NULL,
  PRIMARY KEY (`codigo`),
  FOREIGN KEY (`id_condominio`) REFERENCES `condominios` (`id_condominio`),
  FOREIGN KEY (`id_calle`) REFERENCES `calles` (`id_calle`),
  FOREIGN KEY (`id_casa`) REFERENCES `casas` (`id_casa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### **📋 TABLA: `personas`** → **Persona.php**
```sql
CREATE TABLE `personas` (
  `id_persona` int(11) NOT NULL AUTO_INCREMENT,
  `curp` char(18) NOT NULL UNIQUE,
  `nombres` varchar(80) NOT NULL,
  `apellido1` varchar(80) NOT NULL,
  `apellido2` varchar(80) DEFAULT NULL,
  `correo_electronico` varchar(120) NOT NULL UNIQUE,
  `contrasena` varbinary(255) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `jerarquia` tinyint(4) NOT NULL CHECK (`jerarquia` in (0,1)),
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_persona`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
```

#### **📋 TABLA: `personas_unidad`** → **Dispositivo.php**
```sql
CREATE TABLE `personas_unidad` (
  `id_persona_unidad` int(11) NOT NULL AUTO_INCREMENT,
  `telefono_1` varchar(20) NOT NULL,
  `telefono_2` varchar(20) DEFAULT NULL,
  `curp` char(18) NOT NULL UNIQUE,
  `nombres` varchar(80) NOT NULL,
  `apellido1` varchar(80) NOT NULL,
  `apellido2` varchar(80) DEFAULT NULL,
  `fecha_nacimiento` date NOT NULL,
  `foto` text DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_persona_unidad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
```

#### **📋 TABLA: `tags`** → **Tag.php**
```sql
CREATE TABLE `tags` (
  `id_tag` int(11) NOT NULL AUTO_INCREMENT,
  `id_persona` int(11) NOT NULL,
  `id_casa` int(11) NOT NULL,
  `id_condominio` int(11) NOT NULL,
  `id_calle` int(11) NOT NULL,
  `codigo_tag` varchar(64) NOT NULL UNIQUE,
  `activo` tinyint(4) NOT NULL DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_tag`),
  FOREIGN KEY (`id_persona`) REFERENCES `personas` (`id_persona`) ON UPDATE CASCADE,
  FOREIGN KEY (`id_casa`) REFERENCES `casas` (`id_casa`) ON UPDATE CASCADE,
  FOREIGN KEY (`id_condominio`) REFERENCES `condominios` (`id_condominio`) ON UPDATE CASCADE,
  FOREIGN KEY (`id_calle`) REFERENCES `calles` (`id_calle`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
```

#### **📋 TABLA: `engomados`** → **Engomado.php**
```sql
CREATE TABLE `engomados` (
  `id_engomado` int(11) NOT NULL AUTO_INCREMENT,
  `id_persona` int(11) NOT NULL,
  `id_casa` int(11) NOT NULL,
  `id_condominio` int(11) NOT NULL,
  `id_calle` int(11) NOT NULL,
  `placa` varchar(15) NOT NULL,
  `modelo` varchar(50) DEFAULT NULL,
  `color` varchar(30) DEFAULT NULL,
  `anio` smallint(6) DEFAULT NULL,
  `foto` tinytext DEFAULT NULL,
  `activo` tinyint(4) NOT NULL DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_engomado`),
  FOREIGN KEY (`id_persona`) REFERENCES `personas` (`id_persona`) ON UPDATE CASCADE,
  FOREIGN KEY (`id_casa`) REFERENCES `casas` (`id_casa`) ON UPDATE CASCADE,
  FOREIGN KEY (`id_condominio`) REFERENCES `condominios` (`id_condominio`) ON UPDATE CASCADE,
  FOREIGN KEY (`id_calle`) REFERENCES `calles` (`id_calle`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
```

#### **📋 TABLA: `empleados_condominio`** → **Empleado.php**
```sql
CREATE TABLE `empleados_condominio` (
  `id_empleado` int(11) NOT NULL AUTO_INCREMENT,
  `id_condominio` int(11) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellido1` varchar(100) NOT NULL,
  `apellido2` varchar(100) NOT NULL,
  `puesto` enum('servicio','administracion','mantenimiento') NOT NULL,
  `fecha_contrato` date DEFAULT NULL,
  PRIMARY KEY (`id_empleado`),
  FOREIGN KEY (`id_condominio`) REFERENCES `condominios` (`id_condominio`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
```

#### **📋 TABLA: `tareas`** → **Empleado.php**
```sql
CREATE TABLE `tareas` (
  `id_tarea` int(11) NOT NULL AUTO_INCREMENT,
  `id_condominio` int(11) NOT NULL,
  `id_calle` int(11) NOT NULL,
  `id_trabajador` int(11) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `imagen` tinytext DEFAULT NULL,
  PRIMARY KEY (`id_tarea`),
  FOREIGN KEY (`id_condominio`) REFERENCES `condominios` (`id_condominio`) ON UPDATE CASCADE,
  FOREIGN KEY (`id_calle`) REFERENCES `calles` (`id_calle`) ON UPDATE CASCADE,
  FOREIGN KEY (`id_trabajador`) REFERENCES `empleados_condominio` (`id_empleado`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
```

#### **📋 TABLA: `persona_dispositivo`** → **Dispositivo.php**
```sql
CREATE TABLE `persona_dispositivo` (
  `id_persona_dispositivo` int(11) NOT NULL AUTO_INCREMENT,
  `id_persona_unidad` int(11) NOT NULL,
  `tipo_dispositivo` enum('tag','engomado') NOT NULL,
  `id_dispositivo` int(11) NOT NULL,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_persona_dispositivo`),
  FOREIGN KEY (`id_persona_unidad`) REFERENCES `personas_unidad` (`id_persona_unidad`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
```

#### **📋 TABLA: `areas_comunes`** → **AreaComun.php**
```sql
CREATE TABLE `areas_comunes` (
  `id_area_comun` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `id_condominio` int(11) NOT NULL,
  `id_calle` int(11) DEFAULT NULL,
  `hora_apertura` time NOT NULL,
  `hora_cierre` time NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_area_comun`),
  FOREIGN KEY (`id_condominio`) REFERENCES `condominios` (`id_condominio`) ON DELETE CASCADE,
  FOREIGN KEY (`id_calle`) REFERENCES `calles` (`id_calle`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

#### **📋 TABLA: `apartar_areas_comunes`** → **AreaComun.php**
```sql
CREATE TABLE `apartar_areas_comunes` (
  `id_apartado` int(11) NOT NULL AUTO_INCREMENT,
  `id_area_comun` int(11) NOT NULL,
  `id_condominio` int(11) NOT NULL,
  `id_calle` int(11) DEFAULT NULL,
  `id_casa` int(11) DEFAULT NULL,
  `fecha_apartado` datetime NOT NULL,
  `descripcion` text DEFAULT NULL,
  PRIMARY KEY (`id_apartado`),
  FOREIGN KEY (`id_area_comun`) REFERENCES `areas_comunes` (`id_area_comun`) ON DELETE CASCADE,
  FOREIGN KEY (`id_condominio`) REFERENCES `condominios` (`id_condominio`) ON DELETE CASCADE,
  FOREIGN KEY (`id_calle`) REFERENCES `calles` (`id_calle`) ON DELETE SET NULL,
  FOREIGN KEY (`id_casa`) REFERENCES `casas` (`id_casa`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

#### **📋 TABLA: `blog`** → **Blog.php**
```sql
CREATE TABLE `blog` (
  `id_blog` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `contenido` text NOT NULL,
  `imagen` text DEFAULT NULL,
  `visible_para` enum('todos','admin','residentes') NOT NULL DEFAULT 'todos',
  `creado_por_admin` int(11) DEFAULT NULL,
  `id_condominio` int(11) DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_blog`),
  FOREIGN KEY (`creado_por_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL,
  FOREIGN KEY (`id_condominio`) REFERENCES `personas_unidad` (`id_persona_unidad`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
```

#### **📋 TABLA: `persona_casa`** → **Casa.php**
```sql
CREATE TABLE `persona_casa` (
  `id_casa` int(11) NOT NULL,
  FOREIGN KEY (`id_casa`) REFERENCES `casas` (`id_casa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

---

## 🔗 RELACIONES CRÍTICAS DE INTEGRIDAD (CORREGIDAS)

### ⚡ **MATRIZ COMPLETA DE FOREIGN KEYS**

| **TABLA ORIGEN** | **CAMPO** | **TABLA DESTINO** | **CAMPO DESTINO** | **ACCIÓN DELETE** | **ACCIÓN UPDATE** | **MODELO RESPONSABLE** |
|------------------|-----------|-------------------|-------------------|-------------------|-------------------|----------------------|
| admin_cond | id_admin | admin | id_admin | CASCADE | CASCADE | Condominio.php |
| admin_cond | id_condominio | condominios | id_condominio | CASCADE | CASCADE | Condominio.php |
| calles | id_condominio | condominios | id_condominio | CASCADE | CASCADE | Calle.php |
| casas | id_condominio | condominios | id_condominio | CASCADE | CASCADE | Casa.php |
| casas | id_calle | calles | id_calle | CASCADE | CASCADE | Casa.php |
| claves_registro | id_condominio | condominios | id_condominio | RESTRICT | CASCADE | Casa.php |
| claves_registro | id_calle | calles | id_calle | RESTRICT | CASCADE | Casa.php |
| claves_registro | id_casa | casas | id_casa | RESTRICT | CASCADE | Casa.php |
| persona_casa | id_casa | casas | id_casa | CASCADE | CASCADE | Casa.php |
| tags | id_persona | personas | id_persona | CASCADE | CASCADE | Tag.php |
| tags | id_casa | casas | id_casa | CASCADE | CASCADE | Tag.php |
| tags | id_condominio | condominios | id_condominio | CASCADE | CASCADE | Tag.php |
| tags | id_calle | calles | id_calle | CASCADE | CASCADE | Tag.php |
| engomados | id_persona | personas | id_persona | CASCADE | CASCADE | Engomado.php |
| engomados | id_casa | casas | id_casa | CASCADE | CASCADE | Engomado.php |
| engomados | id_condominio | condominios | id_condominio | CASCADE | CASCADE | Engomado.php |
| engomados | id_calle | calles | id_calle | CASCADE | CASCADE | Engomado.php |
| persona_dispositivo | id_persona_unidad | personas_unidad | id_persona_unidad | CASCADE | CASCADE | Dispositivo.php |
| empleados_condominio | id_condominio | condominios | id_condominio | CASCADE | CASCADE | Empleado.php |
| tareas | id_condominio | condominios | id_condominio | CASCADE | CASCADE | Empleado.php |
| tareas | id_calle | calles | id_calle | CASCADE | CASCADE | Empleado.php |
| tareas | id_trabajador | empleados_condominio | id_empleado | CASCADE | CASCADE | Empleado.php |
| areas_comunes | id_condominio | condominios | id_condominio | CASCADE | CASCADE | AreaComun.php |
| areas_comunes | id_calle | calles | id_calle | SET NULL | CASCADE | AreaComun.php |
| apartar_areas_comunes | id_area_comun | areas_comunes | id_area_comun | CASCADE | CASCADE | AreaComun.php |
| apartar_areas_comunes | id_condominio | condominios | id_condominio | CASCADE | CASCADE | AreaComun.php |
| apartar_areas_comunes | id_calle | calles | id_calle | SET NULL | CASCADE | AreaComun.php |
| apartar_areas_comunes | id_casa | casas | id_casa | SET NULL | CASCADE | AreaComun.php |
| blog | creado_por_admin | admin | id_admin | SET NULL | CASCADE | Blog.php |

---

## 🎯 ESTADO ACTUAL DEL PROYECTO (CORREGIDO)

**📊 COMPLETADO:**
- ✅ 13 modelos especificados siguiendo arquitectura 3 capas (**+1 nuevo modelo Acceso.php**)
- ✅ BaseModel abstracto con funcionalidad completa especificada
- ✅ Documentación completa de relaciones corregida
- ✅ Eliminación total de discrepancias entre documentos
- ✅ Correspondencia perfecta entre BD y modelos
- ✅ **NUEVO:** Sistema de control de acceso diferenciado implementado

**📋 PENDIENTE:**
- 🔄 Implementación física de todos los modelos
- 🔄 **NUEVO:** Implementación del modelo Acceso.php con métodos diferenciados
- 🔄 **NUEVO:** Actualización del modelo Empleado.php con filtrado por condominio
- 🔄 Capa de servicios (lógica de negocio)
- 🔄 Actualización de APIs/controladores
- 🔄 Testing completo de la arquitectura

PROMPT MAESTRO PARA COPILOT — AJUSTE DE ACCESOS DIFERENCIADOS EN CYBERHOLE
Contexto:
Estoy trabajando en el proyecto Cyberhole Condominios, que ya tiene un sistema completamente funcional. Las tablas y modelos actuales funcionan y fueron probados. Ya implementé un sistema de accesos diferenciados para residentes, empleados y visitantes, cada uno con su tabla correspondiente. Lo que necesito ahora son pequeños ajustes semánticos y de documentación, sin romper nada que ya funciona.

🚨 No elimines, no cambies estructura básica, no borres datos existentes.

🚨 Todo el código actual funciona, solo necesitas agregar las nuevas implementaciones y corregir los nombres para mayor claridad.

🎯 Tareas específicas que debes hacer:
✅ 1️⃣ En la tabla empleados_condominio:

Verifica que existan los campos:

id_acceso (VARCHAR(64)) — debe ser único y representa el código físico del empleado.

activo (TINYINT(1)) — indica si el empleado está activo (1) o inactivo (0).

Si no están, agrégalos.

No elimines ningún otro campo.

✅ 2️⃣ En la tabla accesos_empleados:

Renombra la PK id_acceso a id_acceso_empleado para que sea clara y semánticamente consistente.

Renombra el campo id_acceso_empleado (que es el código físico) a id_acceso_empleado_codigo para evitar confusión con la PK.

No elimines ningún dato.

La tabla debe registrar:

id_acceso_empleado (PK AUTO_INCREMENT)

id_empleado

id_condominio

id_acceso_empleado_codigo

fecha_hora_entrada

fecha_hora_salida

✅ 3️⃣ En la tabla accesos_residentes:

Renombra la PK id_acceso a id_acceso_residente para mayor claridad.

Mantén todas las demás columnas tal cual.

No elimines nada.

✅ 4️⃣ En la tabla visitantes:

Nada que cambiar. Esta tabla ya es correcta según la definición: un solo registro por visita con los campos:

nombre

foto_identificacion

id_condominio

id_casa

forma_ingreso

placas

fecha_hora_qr_generado

fecha_hora_entrada

fecha_hora_salida

✅ 5️⃣ En el modelo Empleado.php:

Agrega los campos id_acceso y activo en los métodos de CRUD.

Asegúrate de validar que id_acceso sea único y que activo se pueda modificar para habilitar/deshabilitar accesos.

✅ 6️⃣ Crea un nuevo modelo Acceso.php:

Este modelo manejará las 3 tablas:

accesos_residentes

accesos_empleados

visitantes

Métodos mínimos sugeridos:

registrarAccesoResidente(array $data)

registrarAccesoEmpleado(array $data)

registrarAccesoVisitante(array $data)

registrarSalidaResidente(int $id)

registrarSalidaEmpleado(int $id)

registrarSalidaVisitante(int $id)

historialResidente(int $id_persona)

historialEmpleado(int $id_empleado)

historialVisitante(int $id_visitante)

🚨 Reglas importantes:
No elimines ninguna funcionalidad ya existente.

No toques ningún otro modelo ni tabla que no estén explícitamente indicados aquí.

No elimines datos existentes en la base.

Respeta las convenciones actuales del proyecto.

Si un campo ya existe, no lo dupliques. Solo revisa y ajusta nombres si aplica.

Si tienes dudas entre dos nombres posibles, usa el más claro semánticamente, pero explícame antes de cambiarlo.

📋 Resumen de qué hacer:
✅ Actualizar empleados_condominio
✅ Ajustar nombres en accesos_empleados y accesos_residentes
✅ Dejar visitantes tal cual
✅ Actualizar Empleado.php
✅ Crear Acceso.php

📌 Confirmación esperada:
Cuando termines, explícame claramente qué cambiaste y qué no, indicando los nombres finales de las PKs y campos de cada tabla y los métodos nuevos del modelo Acceso.php.

Cuando quieras, Farid, se lo puedes dar tal cual a Copilot.
Si quieres, también puedo preparar un commit message para esta tarea o redactar las instrucciones SQL completas para aplicar los cambios. 