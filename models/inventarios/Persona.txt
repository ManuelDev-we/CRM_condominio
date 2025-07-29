<?php
/**
 * 🏠 PERSONA MODEL - GESTIÓN DE RESIDENTES DEL SISTEMA
 * Sistema Cyberhole Condominios - Arquitectura 3 Capas
 * 
 * @description Modelo para CRUD de personas/residentes ÚNICAMENTE tabla `personas`
 *              Implementación RELIGIOSA según documentación sagrada corregida
 * @author Sistema Cyberhole - Fanático Religioso de la Documentación
 * @version 6.0 - RECREADO DESDE CERO SIGUIENDO DOCUMENTACIÓN RELIGIOSAMENTE
 * @date 2025-07-16
 * 
 * 🔥 CUMPLIMIENTO RELIGIOSO DEL DIAGRAMA UML CORREGIDO - 14 MÉTODOS EXACTOS:
 * ✅ personaLogin(string email, string password) array|false
 * ✅ personaRegister(array data) int|false
 * ✅ findByCURP(string curp) array|null
 * ✅ findByEmail(string email) array|null
 * ✅ hashPassword(string password) string
 * ✅ validateCURPFormat(string curp) bool
 * ✅ validateEmailFormat(string email) bool
 * ✅ validateCURPUnique(string curp) bool
 * ✅ validateEmailUnique(string email) bool
 * ✅ assignResidenteRole(int personaId) bool
 * ✅ getResidenteRole() string
 * ✅ validatePersonaCredentials(string email, string password) bool
 * ✅ create(array data) int|false (BaseModel override)
 * ✅ findById(int id) array|null (BaseModel override)
 * ✅ update(int id, array data) bool (BaseModel override)
 * ✅ delete(int id) bool (BaseModel override)
 * ✅ findAll(int limit = 100) array (BaseModel override)
 * 
 * 🔥 CUMPLIMIENTO RELIGIOSO DE COLECCIÓN_VARIABLES_ENCRIPTACIÓN:
 * ✅ 6 campos AES: curp, nombres, apellido1, apellido2, correo_electronico, fecha_nacimiento
 * ✅ 1 campo BCRYPT+PEPPER: contrasena
 * ✅ 3 campos en claro: id_persona, jerarquia, creado_en
 */

require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/CryptoModel.php';

class Persona extends BaseModel
{
    /**
     * @var string $table Tabla administrada por este modelo
     * SEGÚN RELACIONES_TABLAS_CORREGIDO: ÚNICAMENTE tabla `personas`
     */
    protected string $table = 'personas';
    
    /**
     * @var string $role Rol de las personas
     * SEGÚN DIAGRAMA UML CORREGIDO: "RESIDENTE"
     */
    private string $role = 'RESIDENTE';
    
    /**
     * @var CryptoModel $crypto Sistema de encriptación
     */
    private CryptoModel $crypto;
    
    /**
     * @var array $encryptedFields Campos que se encriptan con AES
     * SEGÚN COLECCIÓN_VARIABLES_ENCRIPTACIÓN: 6 campos exactos
     */
    private array $encryptedFields = [
        'curp',
        'nombres', 
        'apellido1',
        'apellido2',
        'correo_electronico',
        'fecha_nacimiento'
    ];
    
    /**
     * @var array $fillableFields Campos permitidos para inserción/actualización
     */
    private array $fillableFields = [
        'curp',
        'nombres',
        'apellido1', 
        'apellido2',
        'correo_electronico',
        'contrasena',
        'fecha_nacimiento',
        'jerarquia'
    ];
    
    /**
     * @var array $requiredFields Campos obligatorios
     */
    private array $requiredFields = [
        'curp',
        'nombres',
        'apellido1',
        'correo_electronico',
        'contrasena',
        'fecha_nacimiento'
    ];
    
    /**
     * Constructor - Inicializar crypto y conexión
     */
    public function __construct()
    {
        parent::__construct();
        $this->crypto = new CryptoModel();
    }
    
    // ==========================================
    // MÉTODOS CRUD SOBRESCRITOS DE BASEMODEL
    // ==========================================
    
    /**
     * ✅ CREAR PERSONA CON ENCRIPTACIÓN
     * MÉTODO SOBRESCRITO: +create(array data) int|false
     * SEGÚN COLECCIÓN_VARIABLES_ENCRIPTACIÓN: Encriptar campos sensibles antes de insertar
     * 
     * @param array $data Datos de la persona
     * @return int|false ID de la persona creada o false si falla
     */
    public function create(array $data): int|false
    {
        try {
            // Validar campos requeridos
            if (!$this->validateRequiredFields($data, $this->requiredFields)) {
                $this->logError("Faltan campos requeridos para crear persona");
                return false;
            }
            
            // Validar formato de CURP
            if (!$this->validateCURPFormat($data['curp'])) {
                $this->logError("Formato de CURP inválido: " . $data['curp']);
                return false;
            }
            
            // Validar formato de email
            if (!$this->validateEmailFormat($data['correo_electronico'])) {
                $this->logError("Formato de email inválido: " . $data['correo_electronico']);
                return false;
            }
            
            // Verificar que el CURP no exista
            if (!$this->validateCURPUnique($data['curp'])) {
                $this->logError("CURP ya existe: " . $data['curp']);
                return false;
            }
            
            // Verificar que el email no exista
            if (!$this->validateEmailUnique($data['correo_electronico'])) {
                $this->logError("Email ya existe: " . $data['correo_electronico']);
                return false;
            }
            
            // Encriptar campos sensibles ANTES de insertar
            $encryptedData = [];
            foreach ($this->fillableFields as $field) {
                if (isset($data[$field])) {
                    if (in_array($field, $this->encryptedFields)) {
                        // Encriptar con AES
                        $encryptedData[$field] = $this->crypto->encryptDataInstance($data[$field]);
                    } elseif ($field === 'contrasena') {
                        // Hash con BCRYPT + PEPPER
                        $encryptedData[$field] = $this->crypto->hashPasswordWithPepperInstance($data[$field]);
                    } else {
                        // Campo normal
                        $encryptedData[$field] = $data[$field];
                    }
                }
            }
            
            // Construir query de inserción
            $fields = implode(', ', array_keys($encryptedData));
            $placeholders = ':' . implode(', :', array_keys($encryptedData));
            
            $sql = "INSERT INTO {$this->table} ({$fields}) VALUES ({$placeholders})";
            $stmt = $this->connection->prepare($sql);
            
            if ($stmt->execute($encryptedData)) {
                return (int)$this->connection->lastInsertId();
            }
            
            return false;
            
        } catch (Exception $e) {
            $this->logError("Error creando persona: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * ✅ BUSCAR PERSONA POR ID CON DESENCRIPTACIÓN AUTOMÁTICA
     * MÉTODO SOBRESCRITO: +findById(int id) array|null
     * SEGÚN COLECCIÓN_VARIABLES_ENCRIPTACIÓN: Desencriptar campos automáticamente
     * 
     * @param int $id ID de la persona
     * @return array|null Datos de la persona desencriptados o null si no existe
     */
    public function findById(int $id): array|null
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id_persona = :id LIMIT 1";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            $persona = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($persona) {
                // Desencriptar campos sensibles automáticamente
                return $this->decryptPersonaData($persona);
            }
            
            return null;
            
        } catch (Exception $e) {
            $this->logError("Error buscando persona por ID: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * ✅ ACTUALIZAR PERSONA CON RE-ENCRIPTACIÓN
     * MÉTODO SOBRESCRITO: +update(int id, array data) bool
     * 
     * @param int $id ID de la persona
     * @param array $data Datos a actualizar
     * @return bool True si se actualizó correctamente
     */
    public function update(int $id, array $data): bool
    {
        try {
            // Verificar que la persona existe
            if (!$this->findById($id)) {
                $this->logError("Persona con ID {$id} no existe");
                return false;
            }
            
            // Filtrar solo campos permitidos
            $updateData = [];
            foreach ($data as $field => $value) {
                if (in_array($field, $this->fillableFields)) {
                    if (in_array($field, $this->encryptedFields)) {
                        // Re-encriptar campos sensibles
                        $updateData[$field] = $this->crypto->encryptDataInstance($value);
                    } elseif ($field === 'contrasena') {
                        // Re-hashear contraseña
                        $updateData[$field] = $this->crypto->hashPasswordWithPepperInstance($value);
                    } else {
                        $updateData[$field] = $value;
                    }
                }
            }
            
            if (empty($updateData)) {
                $this->logError("No hay datos válidos para actualizar");
                return false;
            }
            
            // Construir query de actualización
            $setParts = [];
            foreach (array_keys($updateData) as $field) {
                $setParts[] = "{$field} = :{$field}";
            }
            $setClause = implode(', ', $setParts);
            
            $sql = "UPDATE {$this->table} SET {$setClause} WHERE id_persona = :id";
            $updateData['id'] = $id;
            
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($updateData);
            
        } catch (Exception $e) {
            $this->logError("Error actualizando persona: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * ✅ ELIMINAR PERSONA
     * MÉTODO SOBRESCRITO: +delete(int id) bool
     * 
     * @param int $id ID de la persona
     * @return bool True si se eliminó correctamente
     */
    public function delete(int $id): bool
    {
        try {
            // Deshabilitar foreign key checks temporalmente para evitar problemas con constraints
            $this->connection->exec("SET FOREIGN_KEY_CHECKS = 0");
            
            // Primero eliminar relaciones en persona_casa (si existen)
            $deleteCasaSql = "DELETE FROM persona_casa WHERE id_persona = :id";
            $deleteCasaStmt = $this->connection->prepare($deleteCasaSql);
            $deleteCasaStmt->execute(['id' => $id]);
            
            // Luego eliminar la persona
            $sql = "DELETE FROM {$this->table} WHERE id_persona = :id";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            // Reactivar foreign key checks
            $this->connection->exec("SET FOREIGN_KEY_CHECKS = 1");
            
            // Verificar que efectivamente se eliminó al menos 1 fila
            return $stmt->rowCount() > 0;
            
        } catch (Exception $e) {
            // Reactivar foreign key checks en caso de error
            try {
                $this->connection->exec("SET FOREIGN_KEY_CHECKS = 1");
            } catch (Exception $e2) {
                // Ignorar errores al reactivar
            }
            
            $this->logError("Error eliminando persona: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * ✅ OBTENER TODAS LAS PERSONAS CON DESENCRIPTACIÓN
     * MÉTODO SOBRESCRITO: +findAll(int limit = 100) array
     * CORREGIDO: Signature compatible con BaseModel
     * 
     * @param int $limit Límite de registros
     * @return array Lista de personas desencriptadas
     */
    public function findAll(int $limit = 100): array
    {
        try {
            $sql = "SELECT * FROM {$this->table} ORDER BY id_persona DESC LIMIT :limit";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $personas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Desencriptar cada persona
            $personasDesencriptadas = [];
            foreach ($personas as $persona) {
                $personasDesencriptadas[] = $this->decryptPersonaData($persona);
            }
            
            return $personasDesencriptadas;
            
        } catch (Exception $e) {
            $this->logError("Error obteniendo todas las personas: " . $e->getMessage());
            return [];
        }
    }
    
    // ==========================================
    // MÉTODOS ESPECÍFICOS DEL DIAGRAMA UML
    // ==========================================
    
    /**
     * ✅ LOGIN DE PERSONA/RESIDENTE
     * SEGÚN DIAGRAMA_UML_CORREGIDO: +personaLogin(string email, string password) array|false
     * 
     * @param string $email Email del residente
     * @param string $password Contraseña en claro
     * @return array|false Datos del usuario logueado o false si falla
     */
    public function personaLogin(string $email, string $password): array|false
    {
        try {
            // Buscar persona por email
            $persona = $this->findByEmail($email);
            
            if (!$persona) {
                $this->logError("Email no encontrado para login: {$email}");
                return false;
            }
            
            // Verificar contraseña
            if (!$this->validatePersonaCredentials($email, $password)) {
                $this->logError("Contraseña incorrecta para email: {$email}");
                return false;
            }
            
            // Login exitoso - retornar datos sin contraseña
            unset($persona['contrasena']);
            return $persona;
            
        } catch (Exception $e) {
            $this->logError("Error en login de persona: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * ✅ REGISTRO DE PERSONA/RESIDENTE
     * SEGÚN DIAGRAMA_UML_CORREGIDO: +personaRegister(array data) int|false
     * 
     * @param array $data Datos del residente a registrar
     * @return int|false ID de la persona registrada o false si falla
     */
    public function personaRegister(array $data): int|false
    {
        try {
            // Validar que todos los campos obligatorios estén presentes
            if (!$this->validateRequiredFields($data, $this->requiredFields)) {
                $this->logError("Faltan campos requeridos para registro");
                return false;
            }
            
            // Validar formato de CURP
            if (!$this->validateCURPFormat($data['curp'])) {
                $this->logError("Formato de CURP inválido en registro: " . $data['curp']);
                return false;
            }
            
            // Validar formato de email
            if (!$this->validateEmailFormat($data['correo_electronico'])) {
                $this->logError("Formato de email inválido en registro: " . $data['correo_electronico']);
                return false;
            }
            
            // Verificar unicidad de CURP
            if (!$this->validateCURPUnique($data['curp'])) {
                $this->logError("CURP ya registrado: " . $data['curp']);
                return false;
            }
            
            // Verificar unicidad de email
            if (!$this->validateEmailUnique($data['correo_electronico'])) {
                $this->logError("Email ya registrado: " . $data['correo_electronico']);
                return false;
            }
            
            // Establecer jerarquía por defecto para residentes
            if (!isset($data['jerarquia'])) {
                $data['jerarquia'] = 0; // 0 = residente normal
            }
            
            // Crear la persona usando el método create
            return $this->create($data);
            
        } catch (Exception $e) {
            $this->logError("Error en registro de persona: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * ✅ BUSCAR PERSONA POR CURP
     * SEGÚN DIAGRAMA_UML_CORREGIDO: +findByCURP(string curp) array|null
     * 
     * @param string $curp CURP a buscar
     * @return array|null Datos de la persona o null si no existe
     */
    public function findByCURP(string $curp): array|null
    {
        try {
            // Buscar en todos los registros y desencriptar para comparar
            $sql = "SELECT * FROM {$this->table}";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            
            $personas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($personas as $persona) {
                try {
                    // Desencriptar CURP y comparar
                    $curpDesencriptado = $this->crypto->decryptDataInstance($persona['curp']);
                    if ($curpDesencriptado === $curp) {
                        // Desencriptar toda la persona y retornar
                        return $this->decryptPersonaData($persona);
                    }
                } catch (Exception $e) {
                    // Si falla desencriptación de un registro, continuar con siguiente
                    $this->logError("Error desencriptando CURP para comparación: " . $e->getMessage());
                    continue;
                }
            }
            
            return null;
            
        } catch (Exception $e) {
            $this->logError("Error buscando persona por CURP: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * ✅ BUSCAR PERSONA POR EMAIL
     * SEGÚN DIAGRAMA_UML_CORREGIDO: +findByEmail(string email) array|null
     * 
     * @param string $email Email a buscar
     * @return array|null Datos de la persona o null si no existe
     */
    public function findByEmail(string $email): array|null
    {
        try {
            // Buscar en todos los registros y desencriptar para comparar
            $sql = "SELECT * FROM {$this->table}";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            
            $personas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($personas as $persona) {
                try {
                    // Desencriptar email y comparar
                    $emailDesencriptado = $this->crypto->decryptDataInstance($persona['correo_electronico']);
                    if ($emailDesencriptado === $email) {
                        // Desencriptar toda la persona y retornar
                        return $this->decryptPersonaData($persona);
                    }
                } catch (Exception $e) {
                    // Si falla desencriptación de un registro, continuar con siguiente
                    $this->logError("Error desencriptando email para comparación: " . $e->getMessage());
                    continue;
                }
            }
            
            return null;
            
        } catch (Exception $e) {
            $this->logError("Error buscando persona por email: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * ✅ HASH DE CONTRASEÑA
     * SEGÚN DIAGRAMA_UML_CORREGIDO: +hashPassword(string password) string
     * 
     * @param string $password Contraseña en claro
     * @return string Hash BCRYPT + PEPPER
     */
    public function hashPassword(string $password): string
    {
        return $this->crypto->hashPasswordWithPepperInstance($password);
    }
    
    /**
     * ✅ VALIDAR FORMATO DE CURP
     * SEGÚN DIAGRAMA_UML_CORREGIDO: +validateCURPFormat(string curp) bool
     * CORREGIDO: Regex actualizado para CURP mexicano válido
     * 
     * @param string $curp CURP a validar
     * @return bool True si es válido
     */
    public function validateCURPFormat(string $curp): bool
    {
        // CURP debe tener exactamente 18 caracteres alfanuméricos
        // Formato básico mexicano corregido
        return strlen($curp) === 18 && 
               preg_match('/^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[A-Z0-9]{2}$/', strtoupper($curp));
    }
    
    /**
     * ✅ VALIDAR FORMATO DE EMAIL
     * SEGÚN DIAGRAMA_UML_CORREGIDO: +validateEmailFormat(string email) bool
     * 
     * @param string $email Email a validar
     * @return bool True si es válido
     */
    public function validateEmailFormat(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * ✅ VALIDAR QUE CURP SEA ÚNICO
     * SEGÚN DIAGRAMA_UML_CORREGIDO: +validateCURPUnique(string curp) bool
     * 
     * @param string $curp CURP a verificar
     * @return bool True si es único (no existe)
     */
    public function validateCURPUnique(string $curp): bool
    {
        return $this->findByCURP($curp) === null;
    }
    
    /**
     * ✅ VALIDAR QUE EMAIL SEA ÚNICO
     * SEGÚN DIAGRAMA_UML_CORREGIDO: +validateEmailUnique(string email) bool
     * 
     * @param string $email Email a verificar
     * @return bool True si es único (no existe)
     */
    public function validateEmailUnique(string $email): bool
    {
        return $this->findByEmail($email) === null;
    }
    
    /**
     * ✅ ASIGNAR ROL DE RESIDENTE
     * SEGÚN DIAGRAMA_UML_CORREGIDO: +assignResidenteRole(int personaId) bool
     * 
     * @param int $personaId ID de la persona
     * @return bool True si se asignó correctamente
     */
    public function assignResidenteRole(int $personaId): bool
    {
        try {
            // En este sistema, el rol se maneja en la lógica, no en BD
            // Solo verificamos que la persona existe
            return $this->findById($personaId) !== null;
            
        } catch (Exception $e) {
            $this->logError("Error asignando rol de residente: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * ✅ OBTENER ROL DE RESIDENTE
     * SEGÚN DIAGRAMA_UML_CORREGIDO: +getResidenteRole() string
     * 
     * @return string El rol "RESIDENTE"
     */
    public function getResidenteRole(): string
    {
        return $this->role;
    }
    
    /**
     * ✅ VALIDAR CREDENCIALES DE PERSONA
     * SEGÚN DIAGRAMA_UML_CORREGIDO: +validatePersonaCredentials(string email, string password) bool
     * 
     * @param string $email Email de la persona
     * @param string $password Contraseña en claro
     * @return bool True si las credenciales son válidas
     */
    public function validatePersonaCredentials(string $email, string $password): bool
    {
        try {
            // Buscar persona por email
            $persona = $this->findByEmail($email);
            
            if (!$persona) {
                return false;
            }
            
            // Obtener hash de contraseña de la BD (sin desencriptar)
            $sql = "SELECT * FROM {$this->table}";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            
            $personas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($personas as $personaBD) {
                try {
                    // Buscar la persona correcta comparando email desencriptado
                    $emailDesencriptado = $this->crypto->decryptDataInstance($personaBD['correo_electronico']);
                    if ($emailDesencriptado === $email) {
                        // Verificar contraseña usando el hash de la BD
                        return $this->crypto->verifyPasswordWithPepper($password, $personaBD['contrasena']);
                    }
                } catch (Exception $e) {
                    continue;
                }
            }
            
            return false;
            
        } catch (Exception $e) {
            $this->logError("Error validando credenciales: " . $e->getMessage());
            return false;
        }
    }
    
    // ==========================================
    // MÉTODOS AUXILIARES PRIVADOS
    // ==========================================
    
    /**
     * Desencriptar datos de persona automáticamente
     * 
     * @param array $persona Datos encriptados de la persona
     * @return array Datos desencriptados
     */
    private function decryptPersonaData(array $persona): array
    {
        foreach ($this->encryptedFields as $field) {
            if (isset($persona[$field]) && !empty($persona[$field])) {
                try {
                    $decrypted = $this->crypto->decryptDataInstance($persona[$field]);
                    if ($decrypted !== false && $decrypted !== null && $decrypted !== '') {
                        $persona[$field] = $decrypted;
                    } else {
                        // Para el CURP, intentar obtener desde BD RAW para comparación
                        if ($field === 'curp' && isset($persona['id_persona'])) {
                            $rawData = $this->getRawPersonaData($persona['id_persona']);
                            if ($rawData && isset($rawData['curp'])) {
                                try {
                                    $curpDecrypted = $this->crypto->decryptDataInstance($rawData['curp']);
                                    if (!empty($curpDecrypted)) {
                                        $persona[$field] = $curpDecrypted;
                                    }
                                } catch (Exception $e2) {
                                    $this->logError("Fallo recuperación RAW para CURP: " . $e2->getMessage());
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    // Si falla la desencriptación de un campo específico, intentar métodos alternativos
                    $this->logError("Error desencriptando campo {$field}: " . $e->getMessage());
                    
                    // Para campos críticos como CURP, intentar desde BD RAW
                    if ($field === 'curp' && isset($persona['id_persona'])) {
                        $rawData = $this->getRawPersonaData($persona['id_persona']);
                        if ($rawData && isset($rawData['curp'])) {
                            try {
                                $curpDecrypted = $this->crypto->decryptDataInstance($rawData['curp']);
                                if (!empty($curpDecrypted)) {
                                    $persona[$field] = $curpDecrypted;
                                }
                            } catch (Exception $e2) {
                                $this->logError("Segundo intento fallido para CURP: " . $e2->getMessage());
                            }
                        }
                    }
                }
            }
        }
        
        return $persona;
    }

    /**
     * Obtener datos RAW de persona desde BD sin desencriptar
     * 
     * @param int $id ID de la persona
     * @return array|null Datos RAW o null si no existe
     */
    private function getRawPersonaData(int $id): array|null
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id_persona = :id LIMIT 1";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            
        } catch (Exception $e) {
            $this->logError("Error obteniendo datos RAW: " . $e->getMessage());
            return null;
        }
    }
}
