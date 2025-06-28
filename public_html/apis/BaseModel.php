<?php
/**
 * Clase Base para Modelos - Sistema de Condominios
 */

if (!defined('SECURE_ACCESS')) {
    define('SECURE_ACCESS', true);
}
require_once __DIR__ . '/../config/database.php';

abstract class BaseModel {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $attributes = [];
    protected $fillable = [];
    protected $hidden = ['contrasena'];
    
    public function __construct() {
        $this->db = Database::getConnection();
    }
    
    /**
     * Llenar el modelo con datos
     */
    public function fill(array $data) {
        foreach ($data as $key => $value) {
            if (in_array($key, $this->fillable) || empty($this->fillable)) {
                $this->attributes[$key] = $value;
            }
        }
        return $this;
    }
    
    /**
     * Obtener atributo
     */
    public function getAttribute($key) {
        return $this->attributes[$key] ?? null;
    }
    
    /**
     * Establecer atributo
     */
    public function setAttribute($key, $value) {
        $this->attributes[$key] = $value;
        return $this;
    }
    
    /**
     * Magic getter
     */
    public function __get($key) {
        return $this->getAttribute($key);
    }
    
    /**
     * Magic setter
     */
    public function __set($key, $value) {
        $this->setAttribute($key, $value);
    }
    
    /**
     * Convertir a array (excluyendo campos ocultos)
     */
    public function toArray() {
        $data = $this->attributes;
        foreach ($this->hidden as $hidden) {
            unset($data[$hidden]);
        }
        return $data;
    }
    
    /**
     * Guardar en base de datos
     */
    public function save() {
        if (isset($this->attributes[$this->primaryKey])) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }
    
    /**
     * Insertar nuevo registro
     */
    protected function insert() {
        $fields = array_keys($this->attributes);
        $placeholders = ':' . implode(', :', $fields);
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") VALUES ({$placeholders})";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($this->attributes);
            
            $this->attributes[$this->primaryKey] = $this->db->lastInsertId();
            return true;
        } catch (PDOException $e) {
            error_log("Error al insertar: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar registro existente
     */
    protected function update() {
        $fields = [];
        foreach ($this->attributes as $key => $value) {
            if ($key !== $this->primaryKey) {
                $fields[] = "{$key} = :{$key}";
            }
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE {$this->primaryKey} = :{$this->primaryKey}";
        
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($this->attributes);
        } catch (PDOException $e) {
            error_log("Error al actualizar: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Buscar por ID
     */
    public static function find($id) {
        $instance = new static();
        $sql = "SELECT * FROM {$instance->table} WHERE {$instance->primaryKey} = :id";
        
        try {
            $stmt = $instance->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            $data = $stmt->fetch();
            
            if ($data) {
                $instance->fill($data);
                return $instance;
            }
            
            return null;
        } catch (PDOException $e) {
            error_log("Error al buscar: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Buscar por campo específico
     */
    public static function where($field, $value) {
        $instance = new static();
        $sql = "SELECT * FROM {$instance->table} WHERE {$field} = :value";
        
        try {
            $stmt = $instance->db->prepare($sql);
            $stmt->execute(['value' => $value]);
            $data = $stmt->fetch();
            
            if ($data) {
                $instance->fill($data);
                return $instance;
            }
            
            return null;
        } catch (PDOException $e) {
            error_log("Error en consulta where: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Crear nuevo registro
     */
    public function create(array $data) {
        $this->fill($data);
        
        if ($this->save()) {
            return $this->getAttribute($this->primaryKey);
        }
        
        return false;
    }
    
    /**
     * Verificar contraseña
     */
    public function verifyPassword($password) {
        return password_verify($password, $this->getAttribute('contrasena'));
    }
    
    /**
     * Hash de contraseña
     */
    protected function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
?>
