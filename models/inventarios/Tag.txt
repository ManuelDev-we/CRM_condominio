<?php
/**
 * Tag.php
 * 
 * Modelo para gestión de tags RFID/NFC del sistema Cyberhole Condominios
 * Administra únicamente la tabla 'tags' siguiendo arquitectura 3 capas
 * 
 * ARQUITECTURA: Solo CRUD y validaciones básicas de integridad
 * ENCRIPTACIÓN: Campo codigo_tag encriptado con AES-256-CBC
 * HERENCIA: Extiende BaseModel para funcionalidad común
 * 
 * @author Sistema Cyberhole Condominios
 * @version 2.0
 * @since July 2025
 */

require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/env.php';

class Tag extends BaseModel 
{
    /**
     * Tabla principal administrada por este modelo
     * @var string
     */
    protected string $table = 'tags';

    /**
     * Campos requeridos para crear un tag
     * @var array
     */
    private array $requiredFields = [
        'id_persona',
        'id_casa', 
        'id_condominio',
        'id_calle',
        'codigo_tag'
    ];

    /**
     * Constructor - Inicializa conexión PDO
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * CRUD - Crear nuevo tag
     * Encripta codigo_tag antes de guardar
     * 
     * @param array $data Datos del tag
     * @return int|false ID del tag creado o false en error
     */
    public function create(array $data): int|false
    {
        try {
            // Validar campos requeridos
            if (!$this->validateRequiredFields($data, $this->requiredFields)) {
                $this->logError("Tag::create - Campos requeridos faltantes");
                return false;
            }

            // Sanitizar datos
            $data = $this->sanitizeInput($data);

            // Validar que el código del tag sea único
            if (!$this->validateTagCodeUnique($data['codigo_tag'])) {
                $this->logError("Tag::create - Código de tag ya existe: " . $data['codigo_tag']);
                return false;
            }

            // Validar que la persona existe
            if (!$this->validatePersonaExists($data['id_persona'])) {
                $this->logError("Tag::create - Persona no existe: " . $data['id_persona']);
                return false;
            }

            // Validar que la casa existe
            if (!$this->validateCasaExists($data['id_casa'])) {
                $this->logError("Tag::create - Casa no existe: " . $data['id_casa']);
                return false;
            }

            // Encriptar código del tag
            $data['codigo_tag'] = $this->encryptData($data['codigo_tag']);

            // Preparar consulta SQL
            $sql = "INSERT INTO tags (id_persona, id_casa, id_condominio, id_calle, codigo_tag, activo, creado_en) 
                    VALUES (:id_persona, :id_casa, :id_condominio, :id_calle, :codigo_tag, :activo, NOW())";

            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':id_persona', $data['id_persona'], PDO::PARAM_INT);
            $stmt->bindParam(':id_casa', $data['id_casa'], PDO::PARAM_INT);
            $stmt->bindParam(':id_condominio', $data['id_condominio'], PDO::PARAM_INT);
            $stmt->bindParam(':id_calle', $data['id_calle'], PDO::PARAM_INT);
            $stmt->bindParam(':codigo_tag', $data['codigo_tag'], PDO::PARAM_STR);
            
            $activo = $data['activo'] ?? 1;
            $stmt->bindParam(':activo', $activo, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return $this->connection->lastInsertId();
            }

            $this->logError("Tag::create - Error en consulta SQL");
            return false;

        } catch (Exception $e) {
            $this->logError("Tag::create - Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * CRUD - Buscar tag por ID
     * Desencripta codigo_tag para mostrar
     * 
     * @param int $id ID del tag
     * @return array|null Datos del tag o null si no existe
     */
    public function findById(int $id): array|null
    {
        try {
            $sql = "SELECT * FROM tags WHERE id_tag = :id";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                // Desencriptar código del tag
                $result['codigo_tag'] = $this->decryptData($result['codigo_tag']);
                return $result;
            }

            return null;

        } catch (Exception $e) {
            $this->logError("Tag::findById - Exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * CRUD - Actualizar tag
     * Encripta codigo_tag si se proporciona
     * 
     * @param int $id ID del tag
     * @param array $data Datos a actualizar
     * @return bool true si se actualizó, false en error
     */
    public function update(int $id, array $data): bool
    {
        try {
            // Sanitizar datos
            $data = $this->sanitizeInput($data);

            // Construir consulta dinámicamente
            $setClause = [];
            $params = [];

            foreach ($data as $field => $value) {
                if (in_array($field, ['id_persona', 'id_casa', 'id_condominio', 'id_calle', 'codigo_tag', 'activo'])) {
                    // Encriptar código si se está actualizando
                    if ($field === 'codigo_tag') {
                        $value = $this->encryptData($value);
                    }
                    
                    $setClause[] = "$field = :$field";
                    $params[$field] = $value;
                }
            }

            if (empty($setClause)) {
                $this->logError("Tag::update - No hay campos válidos para actualizar");
                return false;
            }

            $sql = "UPDATE tags SET " . implode(', ', $setClause) . " WHERE id_tag = :id";
            $stmt = $this->connection->prepare($sql);
            
            // Bind parámetros
            foreach ($params as $param => $value) {
                $stmt->bindValue(":$param", $value);
            }
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();

        } catch (Exception $e) {
            $this->logError("Tag::update - Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * CRUD - Eliminar tag
     * 
     * @param int $id ID del tag
     * @return bool true si se eliminó, false en error
     */
    public function delete(int $id): bool
    {
        try {
            $sql = "DELETE FROM tags WHERE id_tag = :id";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();

        } catch (Exception $e) {
            $this->logError("Tag::delete - Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * CRUD - Obtener todos los tags con límite
     * Desencripta codigo_tag en cada resultado
     * 
     * @param int $limit Límite de registros
     * @return array Lista de tags
     */
    public function findAll(int $limit = 100): array
    {
        try {
            $sql = "SELECT * FROM tags ORDER BY creado_en DESC LIMIT :limit";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Desencriptar códigos en todos los resultados
            foreach ($results as &$tag) {
                $tag['codigo_tag'] = $this->decryptData($tag['codigo_tag']);
            }

            return $results;

        } catch (Exception $e) {
            $this->logError("Tag::findAll - Exception: " . $e->getMessage());
            return [];
        }
    }

    /**
     * MÉTODO UML - Buscar tags por ID de persona
     * Requerido por diagrama UML
     * 
     * @param int $personaId ID de la persona
     * @return array Lista de tags de la persona
     */
    public function findByPersonaId(int $personaId): array
    {
        try {
            $sql = "SELECT * FROM tags WHERE id_persona = :persona_id ORDER BY creado_en DESC";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':persona_id', $personaId, PDO::PARAM_INT);
            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Desencriptar códigos en todos los resultados
            foreach ($results as &$tag) {
                $tag['codigo_tag'] = $this->decryptData($tag['codigo_tag']);
            }

            return $results;

        } catch (Exception $e) {
            $this->logError("Tag::findByPersonaId - Exception: " . $e->getMessage());
            return [];
        }
    }

    /**
     * MÉTODO UML - Buscar tag por código
     * Requerido por diagrama UML
     * 
     * @param string $codigo Código del tag (en claro)
     * @return array|null Datos del tag o null si no existe
     */
    public function findByTagCode(string $codigo): array|null
    {
        try {
            // Encriptar el código para buscar en BD
            $codigoEncriptado = $this->encryptData($codigo);
            
            $sql = "SELECT * FROM tags WHERE codigo_tag = :codigo";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':codigo', $codigoEncriptado, PDO::PARAM_STR);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                // Desencriptar código del tag
                $result['codigo_tag'] = $this->decryptData($result['codigo_tag']);
                return $result;
            }

            return null;

        } catch (Exception $e) {
            $this->logError("Tag::findByTagCode - Exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * MÉTODO UML - Validar que código de tag sea único
     * Requerido por diagrama UML
     * 
     * @param string $codigo Código del tag (en claro)
     * @return bool true si es único, false si ya existe
     */
    public function validateTagCodeUnique(string $codigo): bool
    {
        try {
            $existingTag = $this->findByTagCode($codigo);
            return $existingTag === null;

        } catch (Exception $e) {
            $this->logError("Tag::validateTagCodeUnique - Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * MÉTODO UML - Validar que persona existe
     * Requerido por diagrama UML
     * 
     * @param int $personaId ID de la persona
     * @return bool true si existe, false si no existe
     */
    public function validatePersonaExists(int $personaId): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM personas WHERE id_persona = :id";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':id', $personaId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchColumn() > 0;

        } catch (Exception $e) {
            $this->logError("Tag::validatePersonaExists - Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * MÉTODO UML - Validar que casa existe
     * Requerido por diagrama UML
     * 
     * @param int $casaId ID de la casa
     * @return bool true si existe, false si no existe
     */
    public function validateCasaExists(int $casaId): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM casas WHERE id_casa = :id";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':id', $casaId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchColumn() > 0;

        } catch (Exception $e) {
            $this->logError("Tag::validateCasaExists - Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * UTILIDAD - Encriptar datos sensibles con AES
     * 
     * @param string $data Datos en claro
     * @return string Datos encriptados
     */
    private function encryptData(string $data): string
    {
        try {
            $key = $_ENV['AES_KEY'] ?? 'CyberholeProd2025AESKey32CharLong!@#';
            $method = $_ENV['AES_METHOD'] ?? 'AES-256-CBC';
            
            $iv = openssl_random_pseudo_bytes(16);
            $encrypted = openssl_encrypt($data, $method, $key, 0, $iv);
            
            return base64_encode($iv . $encrypted);

        } catch (Exception $e) {
            $this->logError("Tag::encryptData - Exception: " . $e->getMessage());
            return $data; // Fallback: retornar dato original
        }
    }

    /**
     * UTILIDAD - Desencriptar datos sensibles con AES
     * 
     * @param string $encryptedData Datos encriptados
     * @return string Datos en claro
     */
    private function decryptData(string $encryptedData): string
    {
        try {
            $key = $_ENV['AES_KEY'] ?? 'CyberholeProd2025AESKey32CharLong!@#';
            $method = $_ENV['AES_METHOD'] ?? 'AES-256-CBC';
            
            $data = base64_decode($encryptedData);
            $iv = substr($data, 0, 16);
            $encrypted = substr($data, 16);
            
            $decrypted = openssl_decrypt($encrypted, $method, $key, 0, $iv);
            
            return $decrypted !== false ? $decrypted : $encryptedData;

        } catch (Exception $e) {
            $this->logError("Tag::decryptData - Exception: " . $e->getMessage());
            return $encryptedData; // Fallback: retornar dato encriptado
        }
    }

    /**
     * UTILIDAD - Buscar tags activos por condominio
     * 
     * @param int $condominioId ID del condominio
     * @return array Lista de tags activos
     */
    public function findActiveTagsByCondominio(int $condominioId): array
    {
        try {
            $sql = "SELECT * FROM tags WHERE id_condominio = :condominio_id AND activo = 1 ORDER BY creado_en DESC";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':condominio_id', $condominioId, PDO::PARAM_INT);
            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Desencriptar códigos en todos los resultados
            foreach ($results as &$tag) {
                $tag['codigo_tag'] = $this->decryptData($tag['codigo_tag']);
            }

            return $results;

        } catch (Exception $e) {
            $this->logError("Tag::findActiveTagsByCondominio - Exception: " . $e->getMessage());
            return [];
        }
    }

    /**
     * UTILIDAD - Activar/Desactivar tag
     * 
     * @param int $id ID del tag
     * @param bool $activo Estado activo (true/false)
     * @return bool true si se actualizó, false en error
     */
    public function setActiveStatus(int $id, bool $activo): bool
    {
        try {
            $sql = "UPDATE tags SET activo = :activo WHERE id_tag = :id";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':activo', $activo, PDO::PARAM_BOOL);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();

        } catch (Exception $e) {
            $this->logError("Tag::setActiveStatus - Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * UTILIDAD - Obtener estadísticas de tags por condominio
     * 
     * @param int $condominioId ID del condominio
     * @return array Estadísticas de tags
     */
    public function getTagStatistics(int $condominioId): array
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_tags,
                        SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as tags_activos,
                        SUM(CASE WHEN activo = 0 THEN 1 ELSE 0 END) as tags_inactivos
                    FROM tags 
                    WHERE id_condominio = :condominio_id";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':condominio_id', $condominioId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
                'total_tags' => 0,
                'tags_activos' => 0,
                'tags_inactivos' => 0
            ];

        } catch (Exception $e) {
            $this->logError("Tag::getTagStatistics - Exception: " . $e->getMessage());
            return [
                'total_tags' => 0,
                'tags_activos' => 0,
                'tags_inactivos' => 0
            ];
        }
    }
}

?>
