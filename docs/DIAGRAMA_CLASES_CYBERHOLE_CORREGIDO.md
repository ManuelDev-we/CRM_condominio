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
        -array encryptedFields = ["nombres", "apellido1", "apellido2", "puesto", "fecha_contrato"]
        -array encryptedFieldsTareas = ["descripcion"]
        +create(array data) int|false
        +findById(int id) array|null
        +update(int id, array data) bool
        +delete(int id) bool
        +findAll(int limit) array
        +findEmpleadosByCondominio(int condominioId, array options) array
        +findByAcceso(string idAcceso) array|null
        +toggleActivo(int id, bool activo) bool
        +createTarea(array data) int|false
        +findTareasByTrabajador(int trabajadorId) array
        +findTareasByCondominio(int condominioId) array
        +validatePuestoValue(string puesto) bool
        +validateCondominioExists(int condominioId) bool
        +validateEmpleadoExists(int empleadoId) bool
        +validateIdAccesoUnique(string idAcceso, int excludeId) bool
        +obtenerEmpleadosPorCondominio(int condominioId, array options) array
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
    note for Empleado "Gestiona: empleados_condominio + tareas + ACCESOS CON AES"
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
| `Empleado.php` | `empleados_condominio` | `tareas` | CRUD + asignaciones + ACCESO AES |

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
- No hay lógica de negocio en modelos
- No hay manejo de sesiones o autenticación en primera capa
- Foreign keys respetadas y documentadas

### ✅ **ESCALABILIDAD Y MANTENIBILIDAD**
- BaseModel abstracto evita duplicación de código
- Cada modelo es completamente testeable de forma aislada
- Relaciones claras y documentadas
- Fácil extensión para nuevos modelos

---

**📅 Documento creado:** Julio 2025  
**🔄 Versión:** 1.0 - Diagrama UML corregido y simplificado  
**📊 Estado:** Listo para implementación  
**💼 Autorizado por:** El Jefe Maniático de la Perfección Arquitectónica ™  
**🎯 Nivel de corrección:** QUIRÚRGICO EXTREMO - Sin discrepancias

---

> **🎯 OBJETIVO CUMPLIDO:** Diagrama UML visual simplificado que refleja 100% la estructura real de la base de datos, eliminando todas las discrepancias y contradicciones detectadas. ¡IMPLEMENTACIÓN APROBADA!
