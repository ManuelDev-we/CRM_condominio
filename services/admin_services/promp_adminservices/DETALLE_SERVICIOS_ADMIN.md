# DETALLE_SERVICIOS_ADMIN.md
## Complemento del Índice Maestro - Servicios Administrativos Cyberhole

### 🎯 Propósito del Documento
Este documento detalla las responsabilidades administrativas de cada archivo de servicio ubicado en `admin_services/`. Funciona como guía oficial de referencia para asegurar que cada servicio maneje únicamente su dominio correspondiente, sin solaparse con otros.

Cada entrada indica qué modelos utiliza, qué funciones de negocio debe implementar, y qué restricciones debe cumplir. Este documento también sirve como prompt de referencia para herramientas como Copilot.

### ⚠ Importante:
- **Todos los servicios aquí descritos están restringidos exclusivamente al rol administrador.**
- **No pueden ser accedidos por residentes ni personal no autenticado.**
- **Toda operación se ejecuta sobre datos del condominio al que pertenece el administrador.**

---

## 📚 Servicios y sus responsabilidades

### AdminService.php
**Propósito:** Administrar el perfil del administrador.

**Modelos usados:** `Admin.php`

**Funciones esperadas:**
- Registro y login de administradores.
- Actualización de contraseña y datos del perfil.
- Preferencias administrativas y notificaciones.

**Notas:** Valida autenticación, CSRF, rate limiting y logs.

---

### CondominioService.php
**Propósito:** Gestionar condominios del administrador autenticado.

**Modelos usados:** `Condominio.php`, `Admin.php` (para validación de ownership)

**Funciones esperadas:**
- Crear, editar, ver y eliminar condominios propios.
- Listado de condominios por admin.
- Validar relación con admin (ownership).

**Notas:** No gestiona cuentas de admin ni calles ni casas directamente.

---

### CalleService.php
**Propósito:** Gestionar las calles de un condominio.

**Modelos usados:** `Calle.php`

**Funciones esperadas:**
- CRUD de calles.
- Validación de nombre.
- Relación calle-condominio.

**Restricciones:**
- Solo permite calles del condominio del admin autenticado.

---

### CasaService.php
**Propósito:** Administrar casas dentro de un condominio.

**Modelos usados:** `Casa.php`, `Calle.php`

**Funciones esperadas:**
- CRUD de casas.
- Asignación de claves de acceso.
- Validación de ubicación y calle.

**Notas:** No gestiona personas ni relaciones, eso lo hace `PersonaCasaService.php`.

---

### MisCasasService.php
**Propósito:** Vista agregada de casas en condominios propios.

**Modelos usados:** `Casa.php`

**Funciones esperadas:**
- Listar casas por condominio.
- Obtener información rápida de casas activas/inactivas.

**Notas:** Es solo un servicio de visualización y resumen.

---

### AreaComunService.php
**Propósito:** Administrar áreas comunes del condominio.

**Modelos usados:** `AreaComun.php`

**Funciones esperadas:**
- CRUD de áreas.
- Control de disponibilidad y horarios.
- Validaciones de conflictos de horario.

---

### EmpleadoService.php
**Propósito:** Gestión de empleados del condominio.

**Modelos usados:** `Empleado.php`

**Funciones esperadas:**
- CRUD de empleados (con encriptación ya manejada en primera capa).
- Asignar áreas o tareas.
- Control de asistencia o accesos.

**Notas:** El administrador solo puede ver/editar empleados de sus propios condominios.

---

### TagService.php
**Propósito:** Administración de identificadores RFID/NFC.

**Modelos usados:** `Tag.php`, `Persona.php`, `Casa.php`

**Funciones esperadas:**
- Crear, editar y eliminar tags.
- Asignar tags a residentes (validando que vivan en el condominio).
- Control de estado de tags (activos/inactivos).

**Restricciones:**
- No crear tags para personas que no vivan en el condominio.

---

### EngomadoService.php
**Propósito:** Gestión de engomados vehiculares.

**Modelos usados:** `Engomado.php`, `Persona.php`

**Funciones esperadas:**
- Alta/baja de engomados.
- Validación de placas.
- Asignación a residentes.

**Notas:** Solo se asignan engomados a personas del condominio actual.

---

### DispositivoService.php
**Propósito:** Control y gestión de dispositivos vinculados a residentes o casas.

**Modelos usados:** `Dispositivo.php`, `Persona.php`

**Funciones esperadas:**
- CRUD de dispositivos.
- Validación de asignación.
- Estado de conexión o permisos.

---

### AccesosService.php
**Propósito:** Registro y monitoreo de accesos.

**Modelos usados:** `Acceso.php`, `Persona.php`, `Empleado.php`

**Funciones esperadas:**
- Registrar accesos de residentes, visitantes y empleados.
- Filtros por fecha, persona, tipo de acceso.
- Estadísticas de entradas/salidas.

**Notas:**
- Verifica que el acceso pertenezca al condominio del administrador.
- No debe permitir registrar accesos de personas externas.

---

### BlogService.php
**Propósito:** Administración de publicaciones internas.

**Modelos usados:** `Blog.php`

**Funciones esperadas:**
- Publicar, editar y eliminar posts.
- Control de visibilidad.
- Filtrado por condominio.

**Notas:** Solo visible a residentes del condominio.

---

### PersonaCasaService.php
**Propósito:** Gestión de relaciones entre personas y casas.

**Modelos usados:** `Persona.php`, `Casa.php`

**Funciones esperadas:**
- Asignación de personas a casas.
- Validación de relaciones.
- CRUD de ocupantes.

**Notas:** Este servicio permite al admin ver qué personas viven en cada casa, con base en su condominio.

---

### PersonaUnidadService.php
**Propósito:** Gestión de unidades secundarias (ej. bodegas o cocheras).

**Modelos usados:** `Persona.php`, `Dispositivo.php`

**Funciones esperadas:**
- Crear unidades complementarias.
- Asociarlas con personas o casas.
- Controlar dispositivos vinculados.

---

## 🔒 Reglas de Seguridad
Todos los servicios descritos:

- ✅ Están protegidos por autenticación de administrador (`admin_required()`).
- ✅ Solo operan dentro del contexto del condominio que administra el `admin_id`.
- ✅ Registran logs para auditoría interna.
- ✅ Tienen validación CSRF y limitación de frecuencia (rate limit).

### 📅 Actualizado: 28 de Julio, 2025
### 📌 Estado: ✅ Listo para generación de prompts especializados.
### 🛡️ Acceso: Solo vistas y endpoints administrativas.

**¿Deseas que ahora generemos el prompt especializado de uno de estos servicios? Puedo comenzar con cualquiera, por ejemplo: EmpleadoService.php o AccesosService.php.**

---

## PROMPT COMPLEMENTARIO - CASCADA DE SERVICIOS CYBERHOLE (HERENCIA Y ENCADENAMIENTO)

### 🎯 PROPÓSITO
Este documento establece la lógica de herencia y dependencia funcional en cascada para la arquitectura de servicios administrativos en el sistema Cyberhole Condominios, organizando los servicios de manera ascendente según sus relaciones funcionales. Además, define cómo deben comportarse las clases padre, madre e hijas, a fin de evitar duplicidad de código, maximizar la reutilización de funciones comunes y mantener una arquitectura limpia y escalable.

### 🧱 ESTRUCTURA EN CASCADA FUNCIONAL
La estructura de los servicios debe funcionar desde AdminService hacia abajo, según la necesidad lógica de interacción:

```
AdminService
  └── CondominioService
        ├── CalleService
        ├── AreaComunService
        ├── BlogService
        └── EmpleadoService
              └── CasaService
                    └── PersonaCasaService
                          └── PersonaService
                                ├── TagService
                                ├── EngomadoService
                                ├── DispositivoService
                                └── PersonaUnidadService
                                      └── AccesosService
```

💡 **Esta estructura implica que los servicios de mayor jerarquía pueden utilizar métodos de los servicios subordinados mediante herencia o composición, pero no al revés (ascendencia unidireccional).**

### 👪 CLASES PADRE, MADRE E HIJAS

#### 🔹 BaseService.php → Clase Padre de todas
Define los métodos comunes reutilizables como:
- `successResponse`, `errorResponse`
- `checkCSRF`, `validateRequiredFields`
- `logAdminActivity`, `enforceRateLimit`

**Esta clase no debe modificarse para funciones de dominio específico, solo para reglas compartidas por todos los servicios.**

#### 🔸 BaseAdminService.php → Clase Madre administrativa
Hereda de `BaseService.php`.

Incorpora middleware embebido exclusivo para administradores:
- `authAdmin()`
- `checkOwnershipCondominio()`

Establece el control de flujo para que cualquier servicio hijo:
- Verifique pertenencia a condominio.
- Responda solo a vistas administrativas.

#### 🧬 Servicios Hijos
- Cada uno representa un módulo funcional específico (ej. `CasaService.php` o `TagService.php`).
- Deben heredar de `BaseAdminService.php` directamente.
- Si varios servicios comparten lógica funcional (ej. validaciones de placas en `EngomadoService` y `AccesosService`), pueden crear una clase intermedia abstracta o una función compartida en `BaseAdminService`.

### 🔄 REGLAS DE USO DE CASCADA

#### Herencia Lógica:
Los servicios que dependen de otros nunca deben duplicar validaciones o consultas que ya estén encapsuladas en sus servicios ascendentes.

#### Llamadas encadenadas:
- Si `CasaService` necesita obtener residentes, debe usar funciones internas de `PersonaCasaService` o `PersonaService`, no replicarlas.
- `AccesosService` debe reutilizar validaciones de placas de `EngomadoService`, usuarios de `PersonaService`, y relaciones de `CasaService`.
- `BlogService` debe validar pertenencia de condominio usando funciones de `CondominioService`.

#### Reutilización de lógica de seguridad y validaciones:
Todos los `checkCSRF`, `checkOwnershipCondominio`, `validatePlacaFormat`, etc., deben centralizarse en `BaseAdminService` si son comunes.

### 📌 FLUJO FUNCIONAL POR BLOQUES

| Bloque Funcional | Servicios Relacionados | Notas Clave |
|------------------|------------------------|-------------|
| 🧑‍💼 Admin | AdminService | Login, registro, perfil, preferencia |
| 🏢 Condominio | CondominioService | Punto de entrada de administración lógica |
| 📍 Infraestructura | CalleService, AreaComunService, EmpleadoService | Submódulos ligados al condominio |
| 📝 Comunicación | BlogService | Gestión de publicaciones internas del condominio |
| 🏠 Propiedades | CasaService, PersonaCasaService | Aquí se administran las viviendas y los residentes |
| 👤 Residentes/Personas | PersonaService, TagService, EngomadoService | Asociaciones individuales por casa o unidad |
| 🔌 Tecnología y control | DispositivoService, PersonaUnidadService, AccesosService | Control técnico, RFID, NFC, tarjetas, QR, etc. |

### 🛑 ACLARACIONES Y LÍMITES
- **Ningún servicio debe romper la jerarquía definida aquí.** Ej. `EngomadoService` no puede realizar lógica de condominio sin pasar por `CasaService`.
- **Todas las vistas están protegidas por middleware administrativo:** solo usuarios autenticados con rol admin y ownership pueden ejecutar las funciones.
- **`BlogService` está integrado en la segunda capa** como servicio dependiente de `CondominioService`, manejando comunicación interna del condominio.
- **`AccesosService` es el último eslabón** y debe poder acceder a la información de:
  - Empleados (`EmpleadoService`)
  - Visitantes (`PersonaService`)
  - Residentes (`PersonaCasaService`)
  - Vehículos (`EngomadoService`)
  - Dispositivos (`DispositivoService`)

### ✅ EJEMPLO DE USO REUTILIZADO
```php
// CasaService.php
$residentes = $this->personaCasaService->obtenerResidentesPorCasa($casaId);

// EngomadoService.php
if (!$this->personaService->existePersona($personaId)) {
    return $this->errorResponse("Persona no encontrada");
}

// BlogService.php
if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
    return $this->errorResponse("No tienes permisos para gestionar este condominio");
}
```

### 🧠 Esta arquitectura garantiza:
- 🔁 **Reutilización sin redundancia**
- ⚙️ **Modularidad entre servicios**
- 🔐 **Seguridad estricta por capa**
- 🧼 **Separación de responsabilidades**
- 🪜 **Escalabilidad jerárquica y descendente**
- 📝 **Comunicación interna integrada (BlogService)**

---

### 📅 Actualizado: 28 de Julio, 2025
### 📌 Estado: ✅ Listo para generación de prompts especializados.
### 🛡️ Acceso: Solo vistas y endpoints administrativas.

**¿Deseas que ahora generemos el prompt especializado de uno de estos servicios? Puedo comenzar con cualquiera, por ejemplo: EmpleadoService.php, AccesosService.php o BlogService.php.**
