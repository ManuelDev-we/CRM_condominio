<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/CryptoModel.php';

/**
 * Modelo Engomado - Gestión de identificadores vehiculares
 * 
 * Responsabilidades:
 * - CRUD completo de la tabla 'engomados'
 * - Validación de placas vehiculares
 * - Gestión de datos vehiculares (modelo, color, año)
 * - Relaciones con personas, casas, calles y condominios
 * - Encriptación de datos sensibles (placa, modelo, color, año)
 * - Validación de unicidad de placas
 */
class Engomado extends BaseModel
{
    protected string $table = 'engomados';
    protected array $fillable = [
        'id_persona',
        'id_casa', 
        'id_condominio',
        'id_calle',
        'placa',
        'modelo',
        'color',
        'anio',
        'foto',
        'activo'
    ];

    protected array $encryptedFields = [
        'placa',
        'modelo', 
        'color',
        'anio'
    ];

    protected array $required = [
        'id_persona',
        'id_casa',
        'id_condominio', 
        'id_calle',
        'placa'
    ];

    /**
     * Crear nuevo engomado vehicular
     */
    public function createEngomado(array $data): int|false
    {
        try {
            // Validar campos requeridos
            if (!$this->validateRequiredFields($data, $this->required)) {
                $this->logError("Campos requeridos faltantes para crear engomado");
                return false;
            }

            // Validar formato de placa
            if (!$this->validatePlacaFormat($data['placa'])) {
                $this->logError("Formato de placa inválido: " . $data['placa']);
                return false;
            }

            // Validar que la persona existe
            if (!$this->validatePersonaExists($data['id_persona'])) {
                $this->logError("ID de persona no existe: " . $data['id_persona']);
                return false;
            }

            // Validar que la casa existe
            if (!$this->validateCasaExists($data['id_casa'])) {
                $this->logError("ID de casa no existe: " . $data['id_casa']);
                return false;
            }

            // Validar unicidad de placa
            if ($this->findByPlaca($data['placa'])) {
                $this->logError("La placa ya existe en el sistema: " . $data['placa']);
                return false;
            }

            // Encriptar campos sensibles
            $encryptedData = $this->encryptSensitiveData($data);

            // Establecer valores por defecto
            $encryptedData['activo'] = $encryptedData['activo'] ?? 1;
            $encryptedData['creado_en'] = date('Y-m-d H:i:s');

            return $this->create($encryptedData);

        } catch (Exception $e) {
            $this->logError("Error al crear engomado: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buscar engomado por placa
     */
    public function findByPlaca(string $placa): array|null
    {
        try {
            $pdo = $this->connect();
            $stmt = $pdo->prepare("SELECT * FROM {$this->table} WHERE placa = ? LIMIT 1");
            $stmt->execute([$placa]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return $this->decryptSensitiveData($result);
            }
            
            return null;

        } catch (Exception $e) {
            $this->logError("Error al buscar engomado por placa: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Buscar engomados por ID de persona
     */
    public function findByPersonaId(int $personaId): array
    {
        try {
            $pdo = $this->connect();
            $stmt = $pdo->prepare("SELECT * FROM {$this->table} WHERE id_persona = ? ORDER BY creado_en DESC");
            $stmt->execute([$personaId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map([$this, 'decryptSensitiveData'], $results);

        } catch (Exception $e) {
            $this->logError("Error al buscar engomados por persona: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar engomados por ID de casa
     */
    public function findByCasaId(int $casaId): array
    {
        try {
            $pdo = $this->connect();
            $stmt = $pdo->prepare("SELECT * FROM {$this->table} WHERE id_casa = ? ORDER BY creado_en DESC");
            $stmt->execute([$casaId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map([$this, 'decryptSensitiveData'], $results);

        } catch (Exception $e) {
            $this->logError("Error al buscar engomados por casa: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar engomados activos
     */
    public function findEngomadosActivos(): array
    {
        try {
            $pdo = $this->connect();
            $stmt = $pdo->prepare("SELECT * FROM {$this->table} WHERE activo = 1 ORDER BY creado_en DESC");
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map([$this, 'decryptSensitiveData'], $results);

        } catch (Exception $e) {
            $this->logError("Error al buscar engomados activos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualizar engomado
     */
    public function updateEngomado(int $id, array $data): bool
    {
        try {
            // Validar que el engomado existe
            $existing = $this->findById($id);
            if (!$existing) {
                $this->logError("Engomado no encontrado para actualizar: ID $id");
                return false;
            }

            // Si se actualiza la placa, validar formato y unicidad
            if (isset($data['placa'])) {
                if (!$this->validatePlacaFormat($data['placa'])) {
                    $this->logError("Formato de placa inválido: " . $data['placa']);
                    return false;
                }

                // Verificar que la placa no esté en uso por otro engomado
                $existingPlaca = $this->findByPlaca($data['placa']);
                if ($existingPlaca && $existingPlaca['id_engomado'] != $id) {
                    $this->logError("La placa ya existe en otro engomado: " . $data['placa']);
                    return false;
                }
            }

            // Validar existencia de relaciones si se actualizan
            if (isset($data['id_persona']) && !$this->validatePersonaExists($data['id_persona'])) {
                $this->logError("ID de persona no existe: " . $data['id_persona']);
                return false;
            }

            if (isset($data['id_casa']) && !$this->validateCasaExists($data['id_casa'])) {
                $this->logError("ID de casa no existe: " . $data['id_casa']);
                return false;
            }

            // Encriptar campos sensibles
            $encryptedData = $this->encryptSensitiveData($data);

            return $this->update($id, $encryptedData);

        } catch (Exception $e) {
            $this->logError("Error al actualizar engomado: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Desactivar engomado (soft delete)
     */
    public function deactivateEngomado(int $id): bool
    {
        try {
            return $this->update($id, ['activo' => 0]);
        } catch (Exception $e) {
            $this->logError("Error al desactivar engomado: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Activar engomado
     */
    public function activateEngomado(int $id): bool
    {
        try {
            return $this->update($id, ['activo' => 1]);
        } catch (Exception $e) {
            $this->logError("Error al activar engomado: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validar formato de placa vehicular
     */
    public function validatePlacaFormat(string $placa): bool
    {
        // Eliminar espacios y convertir a mayúsculas
        $placa = strtoupper(trim($placa));
        
        // Patrones comunes de placas en México
        $patterns = [
            '/^[A-Z]{3}-[0-9]{3}$/',           // ABC-123 (formato viejo)
            '/^[A-Z]{3}[0-9]{3}$/',            // ABC123 (sin guión)
            '/^[A-Z]{3}-[0-9]{2}-[0-9]{2}$/',  // ABC-12-34 (formato nuevo)
            '/^[0-9]{3}-[A-Z]{3}$/',           // 123-ABC (formato invertido)
            '/^[0-9]{3}[A-Z]{3}$/',            // 123ABC (formato invertido sin guión)
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $placa)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validar que la persona existe
     */
    public function validatePersonaExists(int $personaId): bool
    {
        try {
            $pdo = $this->connect();
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM personas WHERE id_persona = ?");
            $stmt->execute([$personaId]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            $this->logError("Error al validar existencia de persona: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validar que la casa existe
     */
    public function validateCasaExists(int $casaId): bool
    {
        try {
            $pdo = $this->connect();
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM casas WHERE id_casa = ?");
            $stmt->execute([$casaId]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            $this->logError("Error al validar existencia de casa: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validar que el condominio existe
     */
    public function validateCondominioExists(int $condominioId): bool
    {
        try {
            $pdo = $this->connect();
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM condominios WHERE id_condominio = ?");
            $stmt->execute([$condominioId]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            $this->logError("Error al validar existencia de condominio: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validar que la calle existe
     */
    public function validateCalleExists(int $calleId): bool
    {
        try {
            $pdo = $this->connect();
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM calles WHERE id_calle = ?");
            $stmt->execute([$calleId]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            $this->logError("Error al validar existencia de calle: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener estadísticas de engomados
     */
    public function getEngomadosStats(): array
    {
        try {
            $pdo = $this->connect();
            
            $stats = [];
            
            // Total de engomados
            $stmt = $pdo->query("SELECT COUNT(*) FROM {$this->table}");
            $stats['total'] = $stmt->fetchColumn();
            
            // Engomados activos
            $stmt = $pdo->query("SELECT COUNT(*) FROM {$this->table} WHERE activo = 1");
            $stats['activos'] = $stmt->fetchColumn();
            
            // Engomados inactivos
            $stmt = $pdo->query("SELECT COUNT(*) FROM {$this->table} WHERE activo = 0");
            $stats['inactivos'] = $stmt->fetchColumn();
            
            return $stats;

        } catch (Exception $e) {
            $this->logError("Error al obtener estadísticas de engomados: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Encriptar campos sensibles
     */
    private function encryptSensitiveData(array $data): array
    {
        $encrypted = $data;
        
        foreach ($this->encryptedFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                $encrypted[$field] = CryptoModel::encryptData($data[$field]);
            }
        }
        
        return $encrypted;
    }

    /**
     * Desencriptar campos sensibles
     */
    private function decryptSensitiveData(array $data): array
    {
        $decrypted = $data;
        
        foreach ($this->encryptedFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                $decrypted[$field] = CryptoModel::decryptData($data[$field]);
            }
        }
        
        return $decrypted;
    }

    /**
     * Buscar engomados con filtros avanzados
     */
    public function searchEngomados(array $filters = []): array
    {
        try {
            $pdo = $this->connect();
            $query = "SELECT * FROM {$this->table} WHERE 1=1";
            $params = [];

            // Filtro por persona
            if (!empty($filters['id_persona'])) {
                $query .= " AND id_persona = ?";
                $params[] = $filters['id_persona'];
            }

            // Filtro por casa
            if (!empty($filters['id_casa'])) {
                $query .= " AND id_casa = ?";
                $params[] = $filters['id_casa'];
            }

            // Filtro por condominio
            if (!empty($filters['id_condominio'])) {
                $query .= " AND id_condominio = ?";
                $params[] = $filters['id_condominio'];
            }

            // Filtro por estado activo
            if (isset($filters['activo'])) {
                $query .= " AND activo = ?";
                $params[] = $filters['activo'];
            }

            $query .= " ORDER BY creado_en DESC";

            // Límite de resultados
            if (!empty($filters['limit'])) {
                $query .= " LIMIT ?";
                $params[] = (int)$filters['limit'];
            }

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map([$this, 'decryptSensitiveData'], $results);

        } catch (Exception $e) {
            $this->logError("Error en búsqueda avanzada de engomados: " . $e->getMessage());
            return [];
        }
    }

    // ==========================================
    // MÉTODOS ABSTRACTOS REQUERIDOS POR BASEMODEL
    // ==========================================

    /**
     * Crear nuevo registro (implementación de BaseModel)
     */
    public function create(array $data): int|false
    {
        return $this->createEngomado($data);
    }

    /**
     * Buscar por ID (implementación de BaseModel)
     */
    public function findById(int $id): array|null
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return null;
            }

            return $this->decryptSensitiveData($result);

        } catch (Exception $e) {
            $this->logError("Error en findById(): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Actualizar registro (implementación de BaseModel)
     */
    public function update(int $id, array $data): bool
    {
        return $this->updateEngomado($id, $data);
    }

    /**
     * Eliminar registro (implementación de BaseModel)
     */
    public function delete(int $id): bool
    {
        return $this->deactivateEngomado($id);
    }

    /**
     * Obtener todos los registros (implementación de BaseModel)
     */
    public function findAll(int $limit = 100): array
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE activo = 1 ORDER BY created_at DESC LIMIT :limit";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return array_map([$this, 'decryptSensitiveData'], $results);

        } catch (Exception $e) {
            $this->logError("Error en findAll(): " . $e->getMessage());
            return [];
        }
    }
}