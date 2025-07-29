# INVENTARIO DE MODELOS - SISTEMA CYBERHOLE CONDOMINIOS

## 🎯 Propósito del Documento
Este documento presenta el inventario completo de todos los modelos del sistema Cyberhole Condominios, organizados por funcionalidad y con detalles específicos de cada método disponible.

### 📊 Resumen Estadístico
- **Total de Modelos:** 12
- **Total de Métodos:** 246
- **Modelos Base:** 2 (BaseModel, CryptoModel)
- **Modelos de Negocio:** 10

---

## 📋 ÍNDICE DE MODELOS

| Modelo | Propósito | Métodos | Categoría |
|--------|-----------|---------|-----------|
| [BaseModel](#basemodel) | Funcionalidad base para todos los modelos | 18 | 🔧 Base |
| [CryptoModel](#cryptomodel) | Encriptación y seguridad | 18 | 🔐 Seguridad |
| [Admin](#admin) | Gestión de administradores | 17 | 👨‍💼 Usuarios |
| [Persona](#persona) | Gestión de residentes | 22 | 👥 Usuarios |
| [Condominio](#condominio) | Gestión de condominios | 19 | 🏢 Infraestructura |
| [Calle](#calle) | Gestión de calles | 16 | 🛣️ Infraestructura |
| [Casa](#casa) | Gestión de casas y relaciones | 29 | 🏠 Propiedades |
| [AreaComun](#areacomun) | Áreas comunes y reservas | 16 | 🏊‍♂️ Servicios |
| [Empleado](#empleado) | Gestión de empleados | 17 | 👷‍♂️ Personal |
| [Acceso](#acceso) | Control de accesos | 14 | 🚪 Control |
| [Tag](#tag) | Identificadores RFID/NFC | 13 | 🏷️ Tecnología |
| [Engomado](#engomado) | Gestión vehicular | 20 | 🚗 Vehículos |
| [Dispositivo](#dispositivo) | Dispositivos y unidades | 15 | 📱 Tecnología |
| [Blog](#blog) | Publicaciones internas | 17 | 📝 Comunicación |

---

## 🔧 MODELOS BASE Y SEGURIDAD

### BaseModel
**Propósito:** Clase base que proporciona funcionalidad común para todos los modelos del sistema.

| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `__construct()` | - | unknown | Constructor de la clase |
| `connect()` | - | ?PDO | Establece conexión a la base de datos |
| `validateRequiredFields()` | array $data, array $required | bool | Valida campos requeridos |
| `logError()` | string $message | void | Registra errores del sistema |
| `sanitizeInput()` | mixed $input | mixed | Sanitiza datos de entrada |
| `exists()` | int $id | bool | Verifica existencia de registro |
| `buildWhereClause()` | array $conditions | array | Construye cláusulas WHERE |
| `getLastInsertId()` | - | int | Obtiene último ID insertado |
| `executeQuery()` | string $sql, array $params = [] | PDOStatement | Ejecuta consulta SQL |
| `buildInsertQuery()` | array $data | array | Construye consulta INSERT |
| `buildUpdateQuery()` | int $id, array $data | array | Construye consulta UPDATE |
| `getTableInfo()` | - | array | Obtiene información de tabla |
| `count()` | array $conditions = [] | int | Cuenta registros |
| `isValidEmail()` | string $email | bool | Valida formato de email |
| `isValidLength()` | string $value, int $minLength, int $maxLength | bool | Valida longitud de cadena |
| `__destruct()` | - | void | Destructor de la clase |
| `create()` | array $data | int | Crear nuevo registro |
| `findById()` | int $id | array | Buscar por ID |
| `update()` | int $id, array $data | bool | Actualizar registro |
| `delete()` | int $id | bool | Eliminar registro |
| `findAll()` | int $limit = 100 | array | Obtener todos los registros |

---

### CryptoModel
**Propósito:** Manejo de encriptación y funciones de seguridad del sistema.

| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `__construct()` | - | unknown | Constructor de la clase |
| `loadEncryptionConfig()` | - | void | Carga configuración de encriptación |
| `validateEncryptionConfig()` | - | void | Valida configuración de encriptación |
| `encryptDataInstance()` | string $data | string | Encripta datos |
| `decryptDataInstance()` | string $encryptedData | string | Desencripta datos |
| `hashPasswordWithPepperInstance()` | string $password | string | Hash de contraseña con pepper |
| `verifyPasswordWithPepperInstance()` | string $password, string $hash | bool | Verifica contraseña con pepper |
| `validatePasswordStrength()` | string $password | bool | Valida fortaleza de contraseña |
| `isValidEncryptedData()` | string $encryptedData | bool | Valida datos encriptados |
| `generateSecureKey()` | int $length = 32 | string | Genera clave segura |
| `generateUniqueCode()` | int $length = 12 | string | Genera código único |
| `getEncryptionInfo()` | - | array | Obtiene información de encriptación |
| `validateEncryptionConfiguration()` | - | bool | Valida configuración de encriptación |
| **Métodos heredados de BaseModel:** | | |
| `create()` | array $data | int | Crear registro |
| `findById()` | int $id | array | Buscar por ID |
| `update()` | int $id, array $data | bool | Actualizar registro |
| `delete()` | int $id | bool | Eliminar registro |
| `findAll()` | int $limit = 100 | array | Obtener todos los registros |

---

## 👥 MODELOS DE USUARIOS

### Admin
**Propósito:** Gestión completa de administradores del sistema.

| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `__construct()` | - | unknown | Constructor de la clase |
| `adminLogin()` | string $email, string $password | array | Login de administrador |
| `adminRegister()` | array $data | int | Registro de administrador |
| `findByEmail()` | string $email | array | Buscar por email |
| `findByEmailWithPassword()` | string $email | array | Buscar por email con contraseña |
| `hashPassword()` | string $password | string | Hash de contraseña |
| `validateEmailFormat()` | string $email | bool | Valida formato de email |
| `validatePasswordLength()` | string $password | bool | Valida longitud de contraseña |
| `getAllAdmins()` | - | array | Obtiene todos los administradores |
| `assignAdminRole()` | int $adminId | bool | Asigna rol de administrador |
| `getAdminRole()` | - | string | Obtiene rol de administrador |
| `validateAdminCredentials()` | string $email, string $password | bool | Valida credenciales |
| `encryptSensitiveFields()` | array $data | array | Encripta campos sensibles |
| `decryptSensitiveFields()` | array $data | array | Desencripta campos sensibles |
| **Métodos heredados de BaseModel:** | | |
| `create()` | array $data | int | Crear administrador |
| `findById()` | int $id | array | Buscar por ID |
| `update()` | int $id, array $data | bool | Actualizar administrador |
| `delete()` | int $id | bool | Eliminar administrador |
| `findAll()` | int $limit = 100 | array | Obtener todos los administradores |

---

### Persona
**Propósito:** Gestión de residentes y personas del sistema.

| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `__construct()` | - | unknown | Constructor de la clase |
| `personaLogin()` | string $email, string $password | array | Login de persona |
| `personaRegister()` | array $data | int | Registro de persona |
| `findByCURP()` | string $curp | array | Buscar por CURP |
| `findByEmail()` | string $email | array | Buscar por email |
| `hashPassword()` | string $password | string | Hash de contraseña |
| `validateCURPFormat()` | string $curp | bool | Valida formato de CURP |
| `validateEmailFormat()` | string $email | bool | Valida formato de email |
| `validateCURPUnique()` | string $curp | bool | Valida CURP único |
| `validateEmailUnique()` | string $email | bool | Valida email único |
| `assignResidenteRole()` | int $personaId | bool | Asigna rol de residente |
| `getResidenteRole()` | - | string | Obtiene rol de residente |
| `validatePersonaCredentials()` | string $email, string $password | bool | Valida credenciales |
| `decryptPersonaData()` | array $persona | array | Desencripta datos de persona |
| `getRawPersonaData()` | int $id | array | Obtiene datos sin encriptar |
| **Métodos heredados de BaseModel:** | | |
| `create()` | array $data | int | Crear persona |
| `findById()` | int $id | array | Buscar por ID |
| `update()` | int $id, array $data | bool | Actualizar persona |
| `delete()` | int $id | bool | Eliminar persona |
| `findAll()` | int $limit = 100 | array | Obtener todas las personas |

---

## 🏢 MODELOS DE INFRAESTRUCTURA

### Condominio
**Propósito:** Gestión de condominios y relaciones con administradores.

| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `__construct()` | - | unknown | Constructor de la clase |
| `createCondominio()` | array $data | int | Crear condominio |
| `assignAdminToCondominio()` | int $adminId, int $condominioId | bool | Asignar admin a condominio |
| `removeAdminFromCondominio()` | int $adminId, int $condominioId | bool | Remover admin de condominio |
| `getAdminsByCondominio()` | int $condominioId | array | Obtener admins por condominio |
| `getCondominiosByAdmin()` | int $adminId | array | Obtener condominios por admin |
| `validateAdminExists()` | int $adminId | bool | Valida existencia de admin |
| `validateCondominioExists()` | int $condominioId | bool | Valida existencia de condominio |
| `updateCondominio()` | int $id, array $data | bool | Actualizar condominio |
| `deleteCondominio()` | int $id | bool | Eliminar condominio |
| `existsCondominioByNombre()` | string $nombre, ?int $excludeId | bool | Verifica nombre único |
| `existsAdminCondRelation()` | int $adminId, int $condominioId | bool | Verifica relación admin-condominio |
| `findCondominioById()` | int $id | array | Buscar condominio por ID |
| `findCondominiosByAdmin()` | int $adminId | array | Buscar condominios por admin |
| `getAllCondominios()` | int $limit = 100 | array | Obtener todos los condominios |
| `findCondominiosByNombre()` | string $nombre | array | Buscar por nombre |
| `getAdminCondominioStats()` | - | array | Obtener estadísticas |
| `getModelInfo()` | - | void | Información del modelo |
| **Métodos heredados de BaseModel:** | | |
| `create()` | array $data | int | Crear registro |
| `findById()` | int $id | array | Buscar por ID |
| `update()` | int $id, array $data | bool | Actualizar registro |
| `delete()` | int $id | bool | Eliminar registro |
| `findAll()` | int $limit = 100 | array | Obtener todos los registros |

---

### Calle
**Propósito:** Gestión de calles dentro de condominios.

| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `__construct()` | - | unknown | Constructor de la clase |
| `findByCondominioId()` | int $condominioId | mixed | Buscar calles por condominio |
| `validateCondominioExists()` | int $condominioId | mixed | Valida existencia de condominio |
| `validateNameUniqueInCondominio()` | string $nombre, int $condominioId, int $excludeId | mixed | Valida nombre único en condominio |
| `createCalle()` | array $data | mixed | Crear calle |
| `updateCalle()` | int $id, array $data | mixed | Actualizar calle |
| `getAllCallesActivas()` | - | mixed | Obtener calles activas |
| `findByNameInCondominio()` | string $nombre, int $condominioId | mixed | Buscar por nombre en condominio |
| `contarCasasEnCalle()` | int $calleId | mixed | Contar casas en calle |
| `getCallesWithCasaCount()` | int $condominioId | mixed | Calles con conteo de casas |
| `searchByNamePattern()` | string $patron, int $condominioId | mixed | Buscar por patrón de nombre |
| `getStatisticsByCondominio()` | - | mixed | Estadísticas por condominio |
| `validateNameFormat()` | string $nombre | mixed | Valida formato de nombre |
| `validateCalleData()` | array $data | void | Valida datos de calle |
| **Métodos heredados de BaseModel:** | | |
| `create()` | array $data | int | Crear registro |
| `findById()` | int $id | array | Buscar por ID |
| `update()` | int $id, array $data | bool | Actualizar registro |
| `delete()` | int $id | bool | Eliminar registro |
| `findAll()` | int $limit = 100 | array | Obtener todos los registros |

---

## 🏠 MODELOS DE PROPIEDADES

### Casa
**Propósito:** Gestión completa de casas, claves de registro y relaciones con personas.

| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `__construct()` | - | unknown | Constructor de la clase |
| `createCasa()` | array $data | int | Crear casa |
| `findCasaById()` | int $id | array | Buscar casa por ID |
| `findCasasByCalleId()` | int $calleId | array | Buscar casas por calle |
| `findCasasByCondominioId()` | int $condominioId | array | Buscar casas por condominio |
| `updateCasa()` | int $id, array $data | bool | Actualizar casa |
| `deleteCasa()` | int $id | bool | Eliminar casa |
| `createClaveRegistro()` | array $data | bool | Crear clave de registro |
| `findClaveRegistro()` | string $codigo | array | Buscar clave de registro |
| `markClaveAsUsed()` | string $codigo | bool | Marcar clave como usada |
| `getClavesByCasa()` | int $casaId | array | Obtener claves por casa |
| `deleteClaveRegistro()` | string $codigo | bool | Eliminar clave de registro |
| `limpiarClavesExpiradas()` | int $diasExpiracion = 30 | int | Limpiar claves expiradas |
| `assignPersonaToCasa()` | int $personaId, int $casaId | bool | Asignar persona a casa |
| `removePersonaFromCasa()` | int $personaId, int $casaId | bool | Remover persona de casa |
| `getPersonasByCasa()` | int $casaId | array | Obtener personas por casa |
| `getCasasByPersona()` | int $personaId | array | Obtener casas por persona |
| `validateCondominioExists()` | int $condominioId | bool | Valida existencia de condominio |
| `validateCalleExists()` | int $calleId | bool | Valida existencia de calle |
| `validateCasaExists()` | int $casaId | bool | Valida existencia de casa |
| `validatePersonaExists()` | int $personaId | bool | Valida existencia de persona |
| `validateCalleInCondominio()` | int $calleId, int $condominioId | bool | Valida calle en condominio |
| `isPersonaAssignedToCasa()` | int $personaId, int $casaId | bool | Verifica asignación persona-casa |
| `getEstadisticasByCondominio()` | int $condominioId | array | Estadísticas por condominio |
| `getReporteCompleto()` | int $casaId | array | Reporte completo de casa |
| **Métodos heredados de BaseModel:** | | |
| `create()` | array $data | int | Crear registro |
| `findById()` | int $id | array | Buscar por ID |
| `update()` | int $id, array $data | bool | Actualizar registro |
| `delete()` | int $id | bool | Eliminar registro |
| `findAll()` | int $limit = 100 | array | Obtener todos los registros |

---

## 🏊‍♂️ MODELOS DE SERVICIOS

### AreaComun
**Propósito:** Gestión de áreas comunes y sistema de reservas.

| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `__construct()` | - | unknown | Constructor de la clase |
| `createAreaComun()` | array $data | int | Crear área común |
| `findAreasComunesByCondominio()` | int $condominioId | array | Buscar áreas por condominio |
| `findAreasActivasByCondominio()` | int $condominioId | array | Buscar áreas activas |
| `cambiarEstadoArea()` | int $areaId, int $estado | bool | Cambiar estado de área |
| `createReserva()` | array $data | int | Crear reserva |
| `findReservasByAreaComun()` | int $areaId | array | Buscar reservas por área |
| `findReservasByCondominio()` | int $condominioId | array | Buscar reservas por condominio |
| `validateCondominioExists()` | int $condominioId | bool | Valida existencia de condominio |
| `validateTimeFormat()` | string $time | bool | Valida formato de tiempo |
| `validateCalleExists()` | int $calleId | bool | Valida existencia de calle |
| `validateCasaExists()` | int $casaId | bool | Valida existencia de casa |
| **Métodos heredados de BaseModel:** | | |
| `create()` | array $data | int | Crear registro |
| `findById()` | int $id | array | Buscar por ID |
| `update()` | int $id, array $data | bool | Actualizar registro |
| `delete()` | int $id | bool | Eliminar registro |
| `findAll()` | int $limit = 100 | array | Obtener todos los registros |

---

## 👷‍♂️ MODELOS DE PERSONAL

### Empleado
**Propósito:** Gestión de empleados y sistema de tareas.

| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `__construct()` | - | unknown | Constructor de la clase |
| `findEmpleadosByCondominio()` | int $id_condominio, array $options | array | Buscar empleados por condominio |
| `findByAcceso()` | string $id_acceso | array | Buscar por ID de acceso |
| `toggleActivo()` | int $id, bool $activo | bool | Activar/desactivar empleado |
| `createTarea()` | array $data | int | Crear tarea |
| `findTareasByTrabajador()` | int $id_trabajador | array | Buscar tareas por trabajador |
| `findTareasByCondominio()` | int $id_condominio | array | Buscar tareas por condominio |
| `validatePuestoValue()` | string $puesto | bool | Valida valor de puesto |
| `validateCondominioExists()` | int $id_condominio | bool | Valida existencia de condominio |
| `validateEmpleadoExists()` | int $id_empleado | bool | Valida existencia de empleado |
| `validateIdAccesoUnique()` | string $id_acceso, ?int $exclude_id | bool | Valida ID de acceso único |
| `decryptEmployeeData()` | array $data | array | Desencripta datos de empleado |
| `decryptTaskData()` | array $data | array | Desencripta datos de tarea |
| **Métodos heredados de BaseModel:** | | |
| `create()` | array $data | int | Crear empleado |
| `findById()` | int $id | array | Buscar por ID |
| `update()` | int $id, array $data | bool | Actualizar empleado |
| `delete()` | int $id | bool | Eliminar empleado |
| `findAll()` | int $limit = 100 | array | Obtener todos los empleados |

---

## 🚪 MODELOS DE CONTROL

### Acceso
**Propósito:** Sistema completo de control de accesos y registro de entradas/salidas.

| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `__construct()` | - | unknown | Constructor de la clase |
| `registrarAccesoResidente()` | array $data | int | Registrar acceso de residente |
| `registrarAccesoEmpleado()` | array $data | int | Registrar acceso de empleado |
| `registrarAccesoVisitante()` | array $data | int | Registrar acceso de visitante |
| `registrarSalidaResidente()` | int $id | bool | Registrar salida de residente |
| `registrarSalidaEmpleado()` | int $id | bool | Registrar salida de empleado |
| `registrarSalidaVisitante()` | int $id | bool | Registrar salida de visitante |
| `historialResidente()` | int $id_persona, int $limite, int $offset | array | Historial de accesos de residente |
| `historialEmpleado()` | int $id_empleado, int $limite, int $offset | array | Historial de accesos de empleado |
| `historialVisitante()` | int $id_visitante | array | Historial de accesos de visitante |
| `estadisticasPorCondominio()` | int $id_condominio, array $options | array | Estadísticas por condominio |
| **Métodos heredados de BaseModel:** | | |
| `create()` | array $data | int | Crear registro de acceso |
| `findById()` | int $id | array | Buscar por ID |
| `update()` | int $id, array $data | bool | Actualizar registro |
| `delete()` | int $id | bool | Eliminar registro |
| `findAll()` | int $limit = 100 | array | Obtener todos los accesos |

---

## 🏷️ MODELOS DE TECNOLOGÍA

### Tag
**Propósito:** Gestión de identificadores RFID/NFC y control de acceso por tags.

| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `__construct()` | - | unknown | Constructor de la clase |
| `findByPersonaId()` | int $personaId | array | Buscar tags por persona |
| `findByTagCode()` | string $codigo | array | Buscar por código de tag |
| `validateTagCodeUnique()` | string $codigo | bool | Valida código único |
| `validatePersonaExists()` | int $personaId | bool | Valida existencia de persona |
| `validateCasaExists()` | int $casaId | bool | Valida existencia de casa |
| `encryptData()` | string $data | string | Encripta datos |
| `decryptData()` | string $encryptedData | string | Desencripta datos |
| `findActiveTagsByCondominio()` | int $condominioId | array | Buscar tags activos por condominio |
| `setActiveStatus()` | int $id, bool $activo | bool | Establecer estado activo |
| `getTagStatistics()` | int $condominioId | void | Obtener estadísticas de tags |
| **Métodos heredados de BaseModel:** | | |
| `create()` | array $data | int | Crear tag |
| `findById()` | int $id | array | Buscar por ID |
| `update()` | int $id, array $data | bool | Actualizar tag |
| `delete()` | int $id | bool | Eliminar tag |
| `findAll()` | int $limit = 100 | array | Obtener todos los tags |

---

### Dispositivo
**Propósito:** Gestión de dispositivos tecnológicos y unidades complementarias.

| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `__construct()` | - | unknown | Constructor de la clase |
| `createUnidad()` | array $data | int | Crear unidad |
| `findUnidadByCURP()` | string $curp | array | Buscar unidad por CURP |
| `associateDispositivo()` | int $unidadId, string $tipo, int $dispositivoId | bool | Asociar dispositivo |
| `getDispositivosByUnidad()` | int $unidadId | array | Obtener dispositivos por unidad |
| `validateCURPUnique()` | string $curp | bool | Valida CURP único |
| `validateTipoDispositivo()` | string $tipo | bool | Valida tipo de dispositivo |
| `encryptSensitiveFields()` | array $data | array | Encripta campos sensibles |
| `decryptSensitiveFields()` | array $data | array | Desencripta campos sensibles |
| `getUnidadesWithDispositivos()` | int $limit = 50 | array | Obtener unidades con dispositivos |
| `searchByNombre()` | string $nombre | array | Buscar por nombre |
| `removeDispositivoAssociation()` | int $unidadId, string $tipo, int $dispositivoId | bool | Remover asociación |
| `countUnidades()` | - | int | Contar unidades |
| **Métodos heredados de BaseModel:** | | |
| `create()` | array $data | int | Crear dispositivo |
| `findById()` | int $id | array | Buscar por ID |
| `update()` | int $id, array $data | bool | Actualizar dispositivo |
| `delete()` | int $id | bool | Eliminar dispositivo |
| `findAll()` | int $limit = 100 | array | Obtener todos los dispositivos |

---

## 🚗 MODELOS DE VEHÍCULOS

### Engomado
**Propósito:** Sistema completo de gestión vehicular y engomados.

| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `createEngomado()` | array $data | int | Crear engomado |
| `findByPlaca()` | string $placa | array | Buscar por placa |
| `findByPersonaId()` | int $personaId | array | Buscar por persona |
| `findByCasaId()` | int $casaId | array | Buscar por casa |
| `findEngomadosActivos()` | - | array | Buscar engomados activos |
| `updateEngomado()` | int $id, array $data | bool | Actualizar engomado |
| `deactivateEngomado()` | int $id | bool | Desactivar engomado |
| `activateEngomado()` | int $id | bool | Activar engomado |
| `validatePlacaFormat()` | string $placa | bool | Valida formato de placa |
| `validatePersonaExists()` | int $personaId | bool | Valida existencia de persona |
| `validateCasaExists()` | int $casaId | bool | Valida existencia de casa |
| `validateCondominioExists()` | int $condominioId | bool | Valida existencia de condominio |
| `validateCalleExists()` | int $calleId | bool | Valida existencia de calle |
| `getEngomadosStats()` | - | array | Obtener estadísticas |
| `encryptSensitiveData()` | array $data | array | Encripta datos sensibles |
| `decryptSensitiveData()` | array $data | array | Desencripta datos sensibles |
| `searchEngomados()` | array $filters = [] | array | Buscar engomados con filtros |
| **Métodos heredados de BaseModel:** | | |
| `create()` | array $data | int | Crear registro |
| `findById()` | int $id | array | Buscar por ID |
| `update()` | int $id, array $data | bool | Actualizar registro |
| `delete()` | int $id | bool | Eliminar registro |
| `findAll()` | int $limit = 100 | array | Obtener todos los registros |

---

## 📝 MODELOS DE COMUNICACIÓN

### Blog
**Propósito:** Sistema de publicaciones internas y comunicación del condominio.

| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `__construct()` | - | unknown | Constructor de la clase |
| `findByAuthor()` | int $adminId | array | Buscar por autor |
| `validateAdminExists()` | int $adminId | bool | Valida existencia de admin |
| `validateVisibilityValue()` | string $visibility | bool | Valida valor de visibilidad |
| `createPost()` | array $data | int | Crear publicación |
| `getPostsByCondominio()` | int $condominioId | array | Obtener posts por condominio |
| `validateCondominioExists()` | int $condominioId | bool | Valida existencia de condominio |
| `postExistsWithTitle()` | string $titulo, int $condominioId | bool | Verifica título único |
| `getPublicPostsByCondominio()` | int $condominioId | array | Obtener posts públicos |
| `searchPosts()` | string $searchText, int $condominioId | array | Buscar posts |
| `getBlogStatistics()` | int $condominioId | array | Obtener estadísticas |
| `getPostsRecientes()` | int $condominioId, int $limite = 5 | array | Obtener posts recientes |
| **Métodos heredados de BaseModel:** | | |
| `create()` | array $data | int | Crear registro |
| `findById()` | int $id | array | Buscar por ID |
| `update()` | int $id, array $data | bool | Actualizar registro |
| `delete()` | int $id | bool | Eliminar registro |
| `findAll()` | int $limit = 100 | array | Obtener todos los registros |
| `exists()` | int $id | bool | Verificar existencia |

---

## 📊 ESTADÍSTICAS DETALLADAS POR MODELO

### Distribución de Métodos por Categoría

| Categoría | Modelos | Total Métodos | Promedio |
|-----------|---------|---------------|----------|
| 🔧 Base y Seguridad | 2 | 36 | 18 |
| 👥 Usuarios | 2 | 39 | 19.5 |
| 🏢 Infraestructura | 2 | 35 | 17.5 |
| 🏠 Propiedades | 1 | 29 | 29 |
| 🏊‍♂️ Servicios | 1 | 16 | 16 |
| 👷‍♂️ Personal | 1 | 17 | 17 |
| 🚪 Control | 1 | 14 | 14 |
| 🏷️ Tecnología | 2 | 28 | 14 |
| 🚗 Vehículos | 1 | 20 | 20 |
| 📝 Comunicación | 1 | 17 | 17 |

### Funcionalidades Especializadas

| Funcionalidad | Modelos Involucrados | Métodos Clave |
|---------------|---------------------|---------------|
| **Encriptación** | CryptoModel, Admin, Persona, Empleado, Tag, Engomado, Dispositivo | 14 métodos de encrypt/decrypt |
| **Validaciones** | Todos los modelos | 35+ métodos de validación |
| **Relaciones** | Casa, Condominio, Admin | 8 métodos de asignación |
| **Búsquedas Avanzadas** | Casa, Calle, Blog, Engomado | 12 métodos de búsqueda |
| **Estadísticas** | Acceso, Casa, Condominio, Tag, Blog | 6 métodos de estadísticas |

---

## 🔒 CONSIDERACIONES DE SEGURIDAD

### Modelos con Encriptación
- ✅ **Admin:** Campos sensibles encriptados
- ✅ **Persona:** Datos personales encriptados 
- ✅ **Empleado:** Información laboral encriptada
- ✅ **Tag:** Códigos encriptados
- ✅ **Engomado:** Datos vehiculares encriptados
- ✅ **Dispositivo:** Información sensible encriptada

### Validaciones de Seguridad
- ✅ **Formatos:** Email, CURP, placas vehiculares
- ✅ **Unicidad:** Emails, CURP, códigos de tag
- ✅ **Existencia:** Validación de relaciones entre modelos
- ✅ **Longitud:** Contraseñas y campos de texto
- ✅ **Fortaleza:** Validación de contraseñas seguras

---

## 📅 Información del Documento
- **Fecha de creación:** 28 de Julio, 2025
- **Total de métodos documentados:** 246
- **Modelos analizados:** 12
- **Estado:** ✅ Completo y actualizado

---

## 🎯 Uso del Inventario
Este inventario debe utilizarse como:
1. **Referencia rápida** para desarrollo
2. **Guía de métodos disponibles** por modelo
3. **Documentación de APIs internas**
4. **Base para generación de servicios** en capas superiores
5. **Referencia para validaciones** y encriptación
