<?php
/**
 * Modelos para el Sistema de Condominios
 */

require_once 'BaseModel.php';
require_once __DIR__ . '/../config/crypto.php';

/**
 * Modelo para Administradores
 */
class Admin extends BaseModel {
    protected $table = 'admin';
    protected $primaryKey = 'id_admin';
    protected $fillable = [
        'nombres', 'apellido1', 'apellido2', 'correo', 'contrasena'
    ];
    
    /**
     * Buscar administrador por correo
     */
    public static function findByEmail($email) {
        $db = Database::getConnection();
        
        // Primero intentar buscar el email sin cifrar (para compatibilidad con datos antiguos)
        $sql = "SELECT * FROM admin WHERE correo = :email LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch();
        
        // Si no se encuentra, intentar con email cifrado (para datos nuevos)
        if (!$data) {
            try {
                $encryptedEmail = CryptoUtils::encryptEmail($email);
                $stmt = $db->prepare($sql);
                $stmt->execute(['email' => $encryptedEmail]);
                $data = $stmt->fetch();
            } catch (Exception $e) {
                error_log("Error al cifrar email para búsqueda: " . $e->getMessage());
            }
        }
        
        // También intentar búsqueda por comparación directa de todos los registros
        // (menos eficiente pero garantiza encontrar el usuario)
        if (!$data) {
            $sql = "SELECT * FROM admin";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $allUsers = $stmt->fetchAll();
            
            foreach ($allUsers as $user) {
                // Intentar desencriptar cada email
                try {
                    $decryptedEmail = CryptoUtils::decryptEmail($user['correo']);
                    if ($decryptedEmail === $email) {
                        $data = $user;
                        break;
                    }
                } catch (Exception $e) {
                    // Si no se puede desencriptar, comparar directamente
                    if ($user['correo'] === $email) {
                        $data = $user;
                        break;
                    }
                }
            }
        }
        
        if ($data) {
            $admin = new static();
            $admin->attributes = $data;
            return $admin;
        }
        
        return null;
    }
    
    /**
     * Autenticar administrador
     */
    public static function authenticate($email, $password) {
        // Log para debug
        if (SecurityConfig::isDebugMode()) {
            error_log("Debug Auth: Intentando autenticar email: $email");
        }
        
        $admin = static::findByEmail($email);
        
        if (SecurityConfig::isDebugMode()) {
            if ($admin) {
                error_log("Debug Auth: Usuario encontrado con ID: " . $admin->getAttribute('id_admin'));
            } else {
                error_log("Debug Auth: Usuario NO encontrado para email: $email");
            }
        }
        
        if ($admin && $admin->verifyPassword($password)) {
            if (SecurityConfig::isDebugMode()) {
                error_log("Debug Auth: Contraseña verificada correctamente");
            }
            return $admin;
        }
        
        if (SecurityConfig::isDebugMode() && $admin) {
            error_log("Debug Auth: Usuario encontrado pero contraseña incorrecta");
        }
        
        return null;
    }
    
    /**
     * Verificar contraseña
     */
    public function verifyPassword($password) {
        $hashedPassword = $this->getAttribute('contrasena');
        
        if (SecurityConfig::isDebugMode()) {
            error_log("Debug Password: Verificando contraseña para usuario ID: " . $this->getAttribute('id_admin'));
            error_log("Debug Password: Hash en BD: " . substr($hashedPassword, 0, 50) . "...");
        }
        
        // Intentar primero con verificación tradicional (para contraseñas creadas antes del pepper)
        if (password_verify($password, $hashedPassword)) {
            if (SecurityConfig::isDebugMode()) {
                error_log("Debug Password: Verificación tradicional exitosa");
            }
            return true;
        }
        
        // Si falla, intentar con el nuevo sistema (pepper)
        try {
            $result = CryptoUtils::verifyPassword($password, $hashedPassword);
            if (SecurityConfig::isDebugMode()) {
                error_log("Debug Password: Verificación con pepper: " . ($result ? "exitosa" : "fallida"));
            }
            return $result;
        } catch (Exception $e) {
            if (SecurityConfig::isDebugMode()) {
                error_log("Debug Password: Error en verificación con pepper: " . $e->getMessage());
            }
            return false;
        }
    }
    
    /**
     * Crear nuevo administrador
     */
    public static function createAdmin($data) {
        $admin = new static();
        
        // Validar datos requeridos
        $required = ['nombres', 'apellido1', 'correo', 'contrasena'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("El campo {$field} es requerido");
            }
        }
        
        // Validar email único
        if (static::findByEmail($data['correo'])) {
            throw new Exception("El correo electrónico ya está registrado");
        }
        
        // Cifrar email
        $data['correo'] = CryptoUtils::encryptEmail($data['correo']);
        
        // Hash de la contraseña con pepper
        $data['contrasena'] = CryptoUtils::hashPassword($data['contrasena']);
        
        $admin->fill($data);
        
        if ($admin->save()) {
            return $admin;
        }
        
        throw new Exception("Error al crear el administrador");
    }
    
    /**
     * Obtener condominios asignados al admin
     */
    public function getCondominios() {
        $sql = "SELECT c.* FROM condominios c 
                INNER JOIN admin_cond ac ON c.id_condominio = ac.id_condominio 
                WHERE ac.id_admin = :id_admin";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id_admin' => $this->getAttribute('id_admin')]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener condominios: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Convertir a array desencriptando datos sensibles
     */
    public function toArray() {
        $data = $this->attributes;
        
        // Desencriptar email si está cifrado
        if (isset($data['correo'])) {
            try {
                // Intentar desencriptar
                $decryptedEmail = CryptoUtils::decryptEmail($data['correo']);
                // Verificar si el resultado es un email válido
                if (filter_var($decryptedEmail, FILTER_VALIDATE_EMAIL)) {
                    $data['correo'] = $decryptedEmail;
                }
                // Si no es un email válido después del descifrado, mantener el original
            } catch (Exception $e) {
                // Si no se puede desencriptar, asumir que ya está en texto plano
                // Esto maneja la compatibilidad con datos no cifrados
            }
        }
        
        // Nunca devolver la contraseña
        unset($data['contrasena']);
        
        return $data;
    }
}

/**
 * Modelo para Residentes/Personas
 */
class Persona extends BaseModel {
    protected $table = 'personas';
    protected $primaryKey = 'id_persona';
    protected $fillable = [
        'id_casa', 'id_condominio', 'id_calle', 'nombres', 'apellido1', 
        'apellido2', 'contrasena', 'correo_electronico', 'fecha_nacimiento', 
        'jerarquia', 'curp'
    ];
    
    /**
     * Buscar persona por correo
     */
    public static function findByEmail($email) {
        return static::where('correo_electronico', $email);
    }
    
    /**
     * Autenticar residente
     */
    public static function authenticate($email, $password) {
        $persona = static::findByEmail($email);
        
        if ($persona && $persona->verifyPassword($password)) {
            return $persona;
        }
        
        return null;
    }
    
    /**
     * Crear nuevo residente
     */
    public static function createResident($data) {
        $persona = new static();
        
        // Validar datos requeridos
        $required = ['nombres', 'apellido1', 'correo_electronico', 'contrasena', 'curp'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("El campo {$field} es requerido");
            }
        }
        
        // Validar email único
        if (static::findByEmail($data['correo_electronico'])) {
            throw new Exception("El correo electrónico ya está registrado");
        }
        
        // Hash de la contraseña
        $data['contrasena'] = password_hash($data['contrasena'], PASSWORD_DEFAULT);
        
        // Establecer jerarquía por defecto (0 = residente)
        if (!isset($data['jerarquia'])) {
            $data['jerarquia'] = 0;
        }
        
        $persona->fill($data);
        
        if ($persona->save()) {
            return $persona;
        }
        
        throw new Exception("Error al crear el residente");
    }
    
    /**
     * Verificar si es administrador
     */
    public function isAdmin() {
        return $this->getAttribute('jerarquia') == 1;
    }
    
    /**
     * Obtener información de la casa
     */
    public function getCasa() {
        $sql = "SELECT cas.*, cal.nombre as nombre_calle, con.nombre as nombre_condominio
                FROM casas cas
                INNER JOIN calles cal ON cas.id_calle = cal.id_calle
                INNER JOIN condominios con ON cas.id_condominio = con.id_condominio
                WHERE cas.id_casa = :id_casa";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id_casa' => $this->getAttribute('id_casa')]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error al obtener casa: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Convertir a array desencriptando datos sensibles
     */
    public function toArray() {
        $data = $this->attributes;
        
        // Desencriptar email si está cifrado
        if (isset($data['correo_electronico'])) {
            try {
                $data['correo_electronico'] = CryptoUtils::decryptEmail($data['correo_electronico']);
            } catch (Exception $e) {
                // Si no se puede desencriptar, mantener el valor original
                // Esto maneja la compatibilidad con datos no cifrados
            }
        }
        
        // Nunca devolver la contraseña
        unset($data['contrasena']);
        
        return $data;
    }
}

/**
 * Modelo para Condominios
 */
class Condominio extends BaseModel {
    protected $table = 'condominios';
    protected $primaryKey = 'id_condominio';
    protected $fillable = ['nombre', 'direccion'];
    
    /**
     * Crear nuevo condominio y asignarlo automáticamente al administrador
     */
    public function create($data, $id_admin = null) {
        // Validar datos requeridos
        if (empty($data['nombre']) || empty($data['direccion'])) {
            throw new Exception("Nombre y dirección son requeridos");
        }
        
        // Verificar que no exista un condominio con el mismo nombre
        $existing = $this->findByName($data['nombre']);
        if ($existing) {
            throw new Exception("Ya existe un condominio con ese nombre");
        }
        
        // Llenar los datos
        $this->fill($data);
        
        // Guardar en la base de datos
        if ($this->save()) {
            $condominioId = $this->getAttribute($this->primaryKey);
            
            // Si se proporciona id_admin, asignar automáticamente el condominio
            if ($id_admin) {
                $permissions = new AdminPermissions();
                if (!$permissions->assignCondominioToAdmin($id_admin, $condominioId)) {
                    error_log("Warning: No se pudo asignar condominio $condominioId al admin $id_admin");
                }
            }
            
            return $condominioId;
        }
        
        throw new Exception("Error al crear el condominio");
    }
    
    /**
     * Obtener condominios accesibles por un administrador
     */
    public static function getForAdmin($id_admin) {
        $permissions = new AdminPermissions();
        return $permissions->getCondominiosForAdmin($id_admin);
    }
    
    /**
     * Buscar condominio por nombre
     */
    public function findByName($nombre) {
        $sql = "SELECT * FROM {$this->table} WHERE nombre = :nombre LIMIT 1";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['nombre' => $nombre]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error al buscar condominio: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtener todas las calles del condominio
     */
    public function getCalles() {
        $sql = "SELECT * FROM calles WHERE id_condominio = :id_condominio";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id_condominio' => $this->getAttribute('id_condominio')]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener calles: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener todas las casas del condominio
     */
    public function getCasas() {
        $sql = "SELECT cas.*, cal.nombre as nombre_calle
                FROM casas cas
                INNER JOIN calles cal ON cas.id_calle = cal.id_calle
                WHERE cas.id_condominio = :id_condominio";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id_condominio' => $this->getAttribute('id_condominio')]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener casas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener todos los condominios
     */
    public static function getAll() {
        $instance = new static();
        $sql = "SELECT * FROM condominios ORDER BY nombre";
        
        try {
            $stmt = $instance->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener condominios: " . $e->getMessage());
            return [];
        }
    }
}

/**
 * Modelo para Calles
 */
class Calle extends BaseModel {
    protected $table = 'calles';
    protected $primaryKey = 'id_calle';
    protected $fillable = ['id_condominio', 'nombre', 'descripcion'];
    
    /**
     * Crear nueva calle con validación de permisos
     */
    public function create($data, $id_admin = null) {
        // Validar datos requeridos
        if (empty($data['id_condominio']) || empty($data['nombre'])) {
            throw new Exception("ID de condominio y nombre son requeridos");
        }
        
        // Validar permisos del administrador
        if ($id_admin) {
            $permissions = new AdminPermissions();
            if (!$permissions->hasAccessToCondominio($id_admin, $data['id_condominio'])) {
                throw new Exception("No tienes permisos para agregar calles a este condominio");
            }
        }
        
        // Llenar los datos
        $this->fill($data);
        
        // Guardar en la base de datos
        if ($this->save()) {
            return $this->getAttribute($this->primaryKey);
        }
        
        throw new Exception("Error al crear la calle");
    }
    
    /**
     * Obtener calles accesibles por un administrador
     */
    public static function getForAdmin($id_admin, $id_condominio = null) {
        $permissions = new AdminPermissions();
        return $permissions->getCallesForAdmin($id_admin, $id_condominio);
    }
    
    /**
     * Obtener condominio al que pertenece
     */
    public function getCondominio() {
        $sql = "SELECT * FROM condominios WHERE id_condominio = :id_condominio";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id_condominio' => $this->getAttribute('id_condominio')]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error al obtener condominio: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtener casas de la calle
     */
    public function getCasas() {
        $sql = "SELECT * FROM casas WHERE id_calle = :id_calle";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id_calle' => $this->getAttribute('id_calle')]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener casas: " . $e->getMessage());
            return [];
        }
    }
}

/**
 * Modelo para Casas
 */
class Casa extends BaseModel {
    protected $table = 'casas';
    protected $primaryKey = 'id_casa';
    protected $fillable = ['id_condominio', 'id_calle', 'casa'];
    
    /**
     * Crear nueva casa con validación de permisos
     */
    public function create($data, $id_admin = null) {
        // Validar datos requeridos
        if (empty($data['id_condominio']) || empty($data['id_calle']) || empty($data['casa'])) {
            throw new Exception("ID de condominio, ID de calle y nombre de casa son requeridos");
        }
        
        // Validar permisos del administrador
        if ($id_admin) {
            $permissions = new AdminPermissions();
            if (!$permissions->hasAccessToCondominio($id_admin, $data['id_condominio'])) {
                throw new Exception("No tienes permisos para agregar casas a este condominio");
            }
            
            if (!$permissions->hasAccessToCalle($id_admin, $data['id_calle'])) {
                throw new Exception("No tienes permisos para agregar casas a esta calle");
            }
        }
        
        // Verificar que no exista una casa con el mismo nombre en la misma calle
        if ($this->existsInCalle($data['casa'], $data['id_calle'])) {
            throw new Exception("Ya existe una casa con ese nombre en esta calle");
        }
        
        // Llenar los datos
        $this->fill($data);
        
        // Guardar en la base de datos
        if ($this->save()) {
            return $this->getAttribute($this->primaryKey);
        }
        
        throw new Exception("Error al crear la casa");
    }
    
    /**
     * Verificar si existe una casa con el mismo nombre en la calle
     */
    private function existsInCalle($nombreCasa, $idCalle) {
        $sql = "SELECT 1 FROM {$this->table} WHERE casa = :casa AND id_calle = :id_calle LIMIT 1";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['casa' => $nombreCasa, 'id_calle' => $idCalle]);
            return $stmt->fetchColumn() !== false;
        } catch (PDOException $e) {
            error_log("Error verificando casa existente: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener condominio al que pertenece
     */
    public function getCondominio() {
        $sql = "SELECT * FROM condominios WHERE id_condominio = :id_condominio";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id_condominio' => $this->getAttribute('id_condominio')]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error al obtener condominio: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtener calle a la que pertenece
     */
    public function getCalle() {
        $sql = "SELECT * FROM calles WHERE id_calle = :id_calle";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id_calle' => $this->getAttribute('id_calle')]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error al obtener calle: " . $e->getMessage());
            return null;
        }
    }
}

/**
 * Clase para gestionar permisos de administradores
 */
class AdminPermissions {
    private $db;
    
    public function __construct() {
        $this->db = Database::getConnection();
    }
    
    /**
     * Verificar si un administrador tiene acceso a un condominio
     */
    public function hasAccessToCondominio($id_admin, $id_condominio) {
        try {
            $stmt = $this->db->prepare("
                SELECT 1 FROM admin_cond 
                WHERE id_admin = ? AND id_condominio = ?
            ");
            $stmt->execute([$id_admin, $id_condominio]);
            return $stmt->fetchColumn() !== false;
        } catch (Exception $e) {
            error_log("Error verificando acceso a condominio: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener todos los condominios de un administrador
     */
    public function getCondominiosForAdmin($id_admin) {
        try {
            $stmt = $this->db->prepare("
                SELECT c.* FROM condominios c
                INNER JOIN admin_cond ac ON c.id_condominio = ac.id_condominio
                WHERE ac.id_admin = ?
                ORDER BY c.nombre
            ");
            $stmt->execute([$id_admin]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error obteniendo condominios para admin: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener calles de condominios accesibles por el administrador
     */
    public function getCallesForAdmin($id_admin, $id_condominio = null) {
        try {
            $sql = "
                SELECT ca.* FROM calles ca
                INNER JOIN admin_cond ac ON ca.id_condominio = ac.id_condominio
                WHERE ac.id_admin = ?
            ";
            $params = [$id_admin];
            
            if ($id_condominio) {
                $sql .= " AND ca.id_condominio = ?";
                $params[] = $id_condominio;
            }
            
            $sql .= " ORDER BY ca.nombre";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error obteniendo calles para admin: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Asignar condominio a administrador
     */
    public function assignCondominioToAdmin($id_admin, $id_condominio) {
        try {
            // Verificar si ya existe la relación
            if ($this->hasAccessToCondominio($id_admin, $id_condominio)) {
                return true;
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO admin_cond (id_admin, id_condominio) 
                VALUES (?, ?)
            ");
            return $stmt->execute([$id_admin, $id_condominio]);
        } catch (Exception $e) {
            error_log("Error asignando condominio a admin: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar si un administrador puede acceder a una calle
     */
    public function hasAccessToCalle($id_admin, $id_calle) {
        try {
            $stmt = $this->db->prepare("
                SELECT 1 FROM calles ca
                INNER JOIN admin_cond ac ON ca.id_condominio = ac.id_condominio
                WHERE ac.id_admin = ? AND ca.id_calle = ?
            ");
            $stmt->execute([$id_admin, $id_calle]);
            return $stmt->fetchColumn() !== false;
        } catch (Exception $e) {
            error_log("Error verificando acceso a calle: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar si un administrador puede acceder a una casa
     */
    public function hasAccessToCasa($id_admin, $id_casa) {
        try {
            $stmt = $this->db->prepare("
                SELECT 1 FROM casas cs
                INNER JOIN admin_cond ac ON cs.id_condominio = ac.id_condominio
                WHERE ac.id_admin = ? AND cs.id_casa = ?
            ");
            $stmt->execute([$id_admin, $id_casa]);
            return $stmt->fetchColumn() !== false;
        } catch (Exception $e) {
            error_log("Error verificando acceso a casa: " . $e->getMessage());
            return false;
        }
    }
}
?>
