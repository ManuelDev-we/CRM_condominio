<?php
/**
 * Modelo Casa - Sistema Cyberhole Condominios
 * 
 * RESPONSABILIDADES SEGÚN DOCUMENTACIÓN OFICIAL:
 * - TABLA PRINCIPAL: casas
 * - TABLA SECUNDARIA: claves_registro (con encriptación AES para 'codigo')
 * - TABLA SECUNDARIA: persona_casa
 * 
 * ARQUITECTURA 3 CAPAS:
 * - Capa 1 (Esta): Solo CRUD y validaciones básicas de integridad
 * - Capa 2 (Servicios): Lógica de negocio (pendiente)
 * - Capa 3 (Controladores): Presentación (pendiente)
 * 
 * RELACIONES DE BD:
 * - casas.id_condominio -> condominios.id_condominio
 * - casas.id_calle -> calles.id_calle
 * - claves_registro.id_casa -> casas.id_casa
 * - persona_casa.id_casa -> casas.id_casa
 * 
 * ENCRIPTACIÓN IMPLEMENTADA:
 * - claves_registro.codigo: ENCRIPTACIÓN AES
 * 
 * @author Sistema Cyberhole Condominios
 * @version 1.0 - Implementación según documentación religiosa
 * @since Julio 2025
 */

require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/CryptoModel.php';

class Casa extends BaseModel {
    
    /**
     * Tabla principal que administra este modelo
     * @var string
     */
    protected string $table = 'casas';
    
    /**
     * Tablas secundarias que administra este modelo
     * @var array
     */
    protected array $secondaryTables = [
        'claves_registro',
        'persona_casa'
    ];
    
    /**
     * Campos requeridos para crear una casa
     * @var array
     */
    protected array $requiredFields = [
        'casa',
        'id_condominio', 
        'id_calle'
    ];
    
    /**
     * Campos requeridos para crear una clave de registro
     * @var array
     */
    protected array $requiredFieldsClaveRegistro = [
        'codigo',
        'id_condominio',
        'id_calle', 
        'id_casa'
    ];
    
    /**
     * Campos que requieren encriptación AES
     * @var array
     */
    protected array $encryptedFields = [
        'claves_registro' => ['codigo']
    ];
    
    /**
     * Constructor del modelo Casa
     */
    public function __construct() {
        parent::__construct();
    }
    
    // ===============================================
    // MÉTODOS CRUD PARA TABLA PRINCIPAL: casas
    // ===============================================
    
    /**
     * Crear una nueva casa
     * 
     * @param array $data Datos de la casa ['casa', 'id_condominio', 'id_calle']
     * @return int|false ID de la casa creada o false en error
     */
    public function createCasa(array $data): int|false {
        try {
            // Validar campos requeridos
            if (!$this->validateRequiredFields($data, $this->requiredFields)) {
                $this->logError("Casa::createCasa - Campos requeridos faltantes");
                return false;
            }
            
            // Validar que el condominio existe
            if (!$this->validateCondominioExists($data['id_condominio'])) {
                $this->logError("Casa::createCasa - Condominio no existe: " . $data['id_condominio']);
                return false;
            }
            
            // Validar que la calle existe
            if (!$this->validateCalleExists($data['id_calle'])) {
                $this->logError("Casa::createCasa - Calle no existe: " . $data['id_calle']);
                return false;
            }
            
            // Validar que la calle pertenece al condominio
            if (!$this->validateCalleInCondominio($data['id_calle'], $data['id_condominio'])) {
                $this->logError("Casa::createCasa - Calle no pertenece al condominio");
                return false;
            }
            
            // Sanitizar datos
            $cleanData = [
                'casa' => $this->sanitizeInput($data['casa']),
                'id_condominio' => (int)$data['id_condominio'],
                'id_calle' => (int)$data['id_calle']
            ];
            
            // Ejecutar inserción
            $stmt = $this->connection->prepare("
                INSERT INTO casas (casa, id_condominio, id_calle) 
                VALUES (:casa, :id_condominio, :id_calle)
            ");
            
            $result = $stmt->execute($cleanData);
            
            if ($result) {
                return (int)$this->connection->lastInsertId();
            }
            
            return false;
            
        } catch (Exception $e) {
            $this->logError("Casa::createCasa - Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Buscar casa por ID
     * 
     * @param int $id ID de la casa
     * @return array|null Datos de la casa o null si no existe
     */
    public function findCasaById(int $id): array|null {
        try {
            $stmt = $this->connection->prepare("
                SELECT c.*, 
                       cond.nombre as condominio_nombre,
                       calle.nombre as calle_nombre
                FROM casas c
                LEFT JOIN condominios cond ON c.id_condominio = cond.id_condominio
                LEFT JOIN calles calle ON c.id_calle = calle.id_calle
                WHERE c.id_casa = :id
            ");
            
            $stmt->execute(['id' => $id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ?: null;
            
        } catch (Exception $e) {
            $this->logError("Casa::findCasaById - Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Buscar casas por ID de calle
     * 
     * @param int $calleId ID de la calle
     * @return array Lista de casas en la calle
     */
    public function findCasasByCalleId(int $calleId): array {
        try {
            $stmt = $this->connection->prepare("
                SELECT c.*, 
                       cond.nombre as condominio_nombre,
                       calle.nombre as calle_nombre
                FROM casas c
                LEFT JOIN condominios cond ON c.id_condominio = cond.id_condominio
                LEFT JOIN calles calle ON c.id_calle = calle.id_calle
                WHERE c.id_calle = :calle_id
                ORDER BY c.casa
            ");
            
            $stmt->execute(['calle_id' => $calleId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $this->logError("Casa::findCasasByCalleId - Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar casas por ID de condominio
     * 
     * @param int $condominioId ID del condominio
     * @return array Lista de casas en el condominio
     */
    public function findCasasByCondominioId(int $condominioId): array {
        try {
            $stmt = $this->connection->prepare("
                SELECT c.*, 
                       cond.nombre as condominio_nombre,
                       calle.nombre as calle_nombre
                FROM casas c
                LEFT JOIN condominios cond ON c.id_condominio = cond.id_condominio
                LEFT JOIN calles calle ON c.id_calle = calle.id_calle
                WHERE c.id_condominio = :condominio_id
                ORDER BY calle.nombre, c.casa
            ");
            
            $stmt->execute(['condominio_id' => $condominioId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $this->logError("Casa::findCasasByCondominioId - Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Actualizar una casa
     * 
     * @param int $id ID de la casa
     * @param array $data Datos a actualizar
     * @return bool True si se actualiza correctamente
     */
    public function updateCasa(int $id, array $data): bool {
        try {
            // Verificar que la casa existe
            if (!$this->findCasaById($id)) {
                $this->logError("Casa::updateCasa - Casa no existe: " . $id);
                return false;
            }
            
            $updateFields = [];
            $params = ['id' => $id];
            
            // Campos actualizables
            $allowedFields = ['casa', 'id_condominio', 'id_calle'];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateFields[] = "$field = :$field";
                    $params[$field] = $this->sanitizeInput($data[$field]);
                }
            }
            
            if (empty($updateFields)) {
                $this->logError("Casa::updateCasa - No hay campos para actualizar");
                return false;
            }
            
            // Validaciones adicionales si se cambian IDs
            if (isset($data['id_condominio']) && !$this->validateCondominioExists($data['id_condominio'])) {
                $this->logError("Casa::updateCasa - Condominio no existe: " . $data['id_condominio']);
                return false;
            }
            
            if (isset($data['id_calle']) && !$this->validateCalleExists($data['id_calle'])) {
                $this->logError("Casa::updateCasa - Calle no existe: " . $data['id_calle']);
                return false;
            }
            
            $sql = "UPDATE casas SET " . implode(', ', $updateFields) . " WHERE id_casa = :id";
            $stmt = $this->connection->prepare($sql);
            
            return $stmt->execute($params);
            
        } catch (Exception $e) {
            $this->logError("Casa::updateCasa - Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar una casa
     * 
     * @param int $id ID de la casa
     * @return bool True si se elimina correctamente
     */
    public function deleteCasa(int $id): bool {
        try {
            // Verificar que la casa existe
            if (!$this->findCasaById($id)) {
                $this->logError("Casa::deleteCasa - Casa no existe: " . $id);
                return false;
            }
            
            $stmt = $this->connection->prepare("DELETE FROM casas WHERE id_casa = :id");
            return $stmt->execute(['id' => $id]);
            
        } catch (Exception $e) {
            $this->logError("Casa::deleteCasa - Error: " . $e->getMessage());
            return false;
        }
    }
    
    // ===============================================
    // MÉTODOS CRUD PARA TABLA SECUNDARIA: claves_registro
    // ===============================================
    
    /**
     * Crear una nueva clave de registro (CON ENCRIPTACIÓN AES)
     * 
     * @param array $data Datos de la clave ['codigo', 'id_condominio', 'id_calle', 'id_casa']
     * @return bool True si se crea correctamente
     */
    public function createClaveRegistro(array $data): bool {
        try {
            // Validar campos requeridos
            if (!$this->validateRequiredFields($data, $this->requiredFieldsClaveRegistro)) {
                $this->logError("Casa::createClaveRegistro - Campos requeridos faltantes");
                return false;
            }
            
            // Validar que la casa existe
            if (!$this->validateCasaExists($data['id_casa'])) {
                $this->logError("Casa::createClaveRegistro - Casa no existe: " . $data['id_casa']);
                return false;
            }
            
            // Validar que el condominio existe
            if (!$this->validateCondominioExists($data['id_condominio'])) {
                $this->logError("Casa::createClaveRegistro - Condominio no existe: " . $data['id_condominio']);
                return false;
            }
            
            // Validar que la calle existe
            if (!$this->validateCalleExists($data['id_calle'])) {
                $this->logError("Casa::createClaveRegistro - Calle no existe: " . $data['id_calle']);
                return false;
            }
            
            // ENCRIPTAR EL CÓDIGO SEGÚN DOCUMENTACIÓN
            $codigoEncriptado = CryptoModel::encryptData($data['codigo']);
            if (!$codigoEncriptado) {
                $this->logError("Casa::createClaveRegistro - Error al encriptar código");
                return false;
            }
            
            // Preparar datos limpios
            $cleanData = [
                'codigo' => $codigoEncriptado, // CÓDIGO ENCRIPTADO
                'id_condominio' => (int)$data['id_condominio'],
                'id_calle' => (int)$data['id_calle'],
                'id_casa' => (int)$data['id_casa'],
                'fecha_expiracion' => isset($data['fecha_expiracion']) ? $data['fecha_expiracion'] : null,
                'usado' => 0 // Por defecto no usado
            ];
            
            // Ejecutar inserción
            $stmt = $this->connection->prepare("
                INSERT INTO claves_registro (codigo, id_condominio, id_calle, id_casa, fecha_expiracion, usado) 
                VALUES (:codigo, :id_condominio, :id_calle, :id_casa, :fecha_expiracion, :usado)
            ");
            
            return $stmt->execute($cleanData);
            
        } catch (Exception $e) {
            $this->logError("Casa::createClaveRegistro - Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Buscar clave de registro por código (CON DESENCRIPTACIÓN AES)
     * 
     * @param string $codigo Código a buscar (en texto plano)
     * @return array|null Datos de la clave o null si no existe
     */
    public function findClaveRegistro(string $codigo): array|null {
        try {
            // Obtener todas las claves de registro para desencriptar y comparar
            $stmt = $this->connection->prepare("
                SELECT cr.*, 
                       c.casa,
                       calle.nombre as calle_nombre,
                       cond.nombre as condominio_nombre
                FROM claves_registro cr
                LEFT JOIN casas c ON cr.id_casa = c.id_casa
                LEFT JOIN calles calle ON cr.id_calle = calle.id_calle
                LEFT JOIN condominios cond ON cr.id_condominio = cond.id_condominio
            ");
            
            $stmt->execute();
            $claves = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Buscar el código desencriptando cada uno
            foreach ($claves as $clave) {
                $codigoDesencriptado = CryptoModel::decryptData($clave['codigo']);
                if ($codigoDesencriptado === $codigo) {
                    // Reemplazar el código encriptado por el desencriptado para retorno
                    $clave['codigo'] = $codigoDesencriptado;
                    return $clave;
                }
            }
            
            return null;
            
        } catch (Exception $e) {
            $this->logError("Casa::findClaveRegistro - Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Marcar clave de registro como usada
     * 
     * @param string $codigo Código a marcar (en texto plano)
     * @return bool True si se marca correctamente
     */
    public function markClaveAsUsed(string $codigo): bool {
        try {
            // Buscar la clave primero
            $clave = $this->findClaveRegistro($codigo);
            if (!$clave) {
                $this->logError("Casa::markClaveAsUsed - Clave no encontrada: " . $codigo);
                return false;
            }
            
            // Encriptar el código para la búsqueda en BD
            $codigoEncriptado = CryptoModel::encryptData($codigo);
            if (!$codigoEncriptado) {
                $this->logError("Casa::markClaveAsUsed - Error al encriptar código para búsqueda");
                return false;
            }
            
            // Actualizar el estado
            $stmt = $this->connection->prepare("
                UPDATE claves_registro 
                SET usado = 1, fecha_canje = NOW() 
                WHERE codigo = :codigo
            ");
            
            return $stmt->execute(['codigo' => $codigoEncriptado]);
            
        } catch (Exception $e) {
            $this->logError("Casa::markClaveAsUsed - Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener claves de registro por casa (CON DESENCRIPTACIÓN)
     * 
     * @param int $casaId ID de la casa
     * @return array Lista de claves de registro
     */
    public function getClavesByCasa(int $casaId): array {
        try {
            $stmt = $this->connection->prepare("
                SELECT cr.*, 
                       c.casa,
                       calle.nombre as calle_nombre,
                       cond.nombre as condominio_nombre
                FROM claves_registro cr
                LEFT JOIN casas c ON cr.id_casa = c.id_casa
                LEFT JOIN calles calle ON cr.id_calle = calle.id_calle
                LEFT JOIN condominios cond ON cr.id_condominio = cond.id_condominio
                WHERE cr.id_casa = :casa_id
                ORDER BY cr.fecha_creacion DESC
            ");
            
            $stmt->execute(['casa_id' => $casaId]);
            $claves = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Desencriptar códigos para el retorno
            foreach ($claves as &$clave) {
                $clave['codigo'] = CryptoModel::decryptData($clave['codigo']);
            }
            
            return $claves;
            
        } catch (Exception $e) {
            $this->logError("Casa::getClavesByCasa - Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Eliminar clave de registro - OPTIMIZADO
     * 
     * @param string $codigo Código de la clave
     * @return bool True si se eliminó correctamente
     */
    public function deleteClaveRegistro(string $codigo): bool {
        try {
            // Operación directa optimizada
            $sql = "DELETE FROM claves_registro WHERE codigo = ?";
            $stmt = $this->connection->prepare($sql);
            $result = $stmt->execute([$codigo]);
            
            if ($result && $stmt->rowCount() > 0) {
                $this->logInfo("Casa::deleteClaveRegistro - Clave eliminada directamente: $codigo");
                return true;
            }
            
            // Fallback: buscar con encriptación legacy para eliminación
            $sql = "SELECT id_clave_registro, codigo FROM claves_registro WHERE codigo IS NOT NULL";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            $claves = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($claves as $clave) {
                try {
                    if ($this->cryptoModel) {
                        $decodedCodigo = $this->cryptoModel->decryptData($clave['codigo'] ?? '');
                        if ($decodedCodigo === $codigo) {
                            $deleteSql = "DELETE FROM claves_registro WHERE id_clave_registro = ?";
                            $deleteStmt = $this->connection->prepare($deleteSql);
                            if ($deleteStmt->execute([$clave['id_clave_registro']])) {
                                $this->logInfo("Casa::deleteClaveRegistro - Clave eliminada (legacy): $codigo");
                                return true;
                            }
                        }
                    }
                } catch (Exception $e) {
                    // Continuar con siguiente clave
                    continue;
                }
            }
            
            $this->logInfo("Casa::deleteClaveRegistro - Clave no encontrada: $codigo");
            return false;
            
        } catch (Exception $e) {
            $this->logError("Casa::deleteClaveRegistro - Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Limpiar claves expiradas
     * 
     * @param int $diasExpiracion Días para considerar expirada (default: 30)
     * @return int Número de claves eliminadas
     */
    public function limpiarClavesExpiradas(int $diasExpiracion = 30): int {
        try {
            $fechaLimite = date('Y-m-d H:i:s', strtotime("-$diasExpiracion days"));
            
            $sql = "DELETE FROM claves_registro 
                   WHERE fecha_registro < ? 
                   AND utilizada = 1";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$fechaLimite]);
            $eliminated = $stmt->rowCount();
            
            $this->logInfo("Casa::limpiarClavesExpiradas - $eliminated claves eliminadas");
            return $eliminated;
            
        } catch (Exception $e) {
            $this->logError("Casa::limpiarClavesExpiradas - Error: " . $e->getMessage());
            return 0;
        }
    }
    
    // ===============================================
    // MÉTODOS CRUD PARA TABLA SECUNDARIA: persona_casa
    // ===============================================
    
    /**
     * Asignar persona a casa
     * 
     * @param int $personaId ID de la persona
     * @param int $casaId ID de la casa
     * @return bool True si se asigna correctamente
     */
    public function assignPersonaToCasa(int $personaId, int $casaId): bool {
        try {
            // Validar que la persona existe
            if (!$this->validatePersonaExists($personaId)) {
                $this->logError("Casa::assignPersonaToCasa - Persona no existe: " . $personaId);
                return false;
            }
            
            // Validar que la casa existe
            if (!$this->validateCasaExists($casaId)) {
                $this->logError("Casa::assignPersonaToCasa - Casa no existe: " . $casaId);
                return false;
            }
            
            // Verificar que la relación no existe ya
            if ($this->isPersonaAssignedToCasa($personaId, $casaId)) {
                $this->logError("Casa::assignPersonaToCasa - Relación ya existe");
                return false;
            }
            
            $stmt = $this->connection->prepare("
                INSERT INTO persona_casa (id_persona, id_casa) 
                VALUES (:id_persona, :id_casa)
            ");
            
            return $stmt->execute([
                'id_persona' => $personaId,
                'id_casa' => $casaId
            ]);
            
        } catch (Exception $e) {
            $this->logError("Casa::assignPersonaToCasa - Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remover persona de casa
     * 
     * @param int $personaId ID de la persona
     * @param int $casaId ID de la casa
     * @return bool True si se remueve correctamente
     */
    public function removePersonaFromCasa(int $personaId, int $casaId): bool {
        try {
            $stmt = $this->connection->prepare("
                DELETE FROM persona_casa 
                WHERE id_persona = :id_persona AND id_casa = :id_casa
            ");
            
            return $stmt->execute([
                'id_persona' => $personaId,
                'id_casa' => $casaId
            ]);
            
        } catch (Exception $e) {
            $this->logError("Casa::removePersonaFromCasa - Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener personas por casa
     * 
     * @param int $casaId ID de la casa
     * @return array Lista de personas en la casa
     */
    public function getPersonasByCasa(int $casaId): array {
        try {
            $stmt = $this->connection->prepare("
                SELECT p.*, pc.id_casa
                FROM personas p
                INNER JOIN persona_casa pc ON p.id_persona = pc.id_persona
                WHERE pc.id_casa = :casa_id
                ORDER BY p.nombres, p.apellido1
            ");
            
            $stmt->execute(['casa_id' => $casaId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $this->logError("Casa::getPersonasByCasa - Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener casas por persona
     * 
     * @param int $personaId ID de la persona
     * @return array Lista de casas de la persona
     */
    public function getCasasByPersona(int $personaId): array {
        try {
            $stmt = $this->connection->prepare("
                SELECT c.*, pc.id_persona,
                       cond.nombre as condominio_nombre,
                       calle.nombre as calle_nombre
                FROM casas c
                INNER JOIN persona_casa pc ON c.id_casa = pc.id_casa
                LEFT JOIN condominios cond ON c.id_condominio = cond.id_condominio
                LEFT JOIN calles calle ON c.id_calle = calle.id_calle
                WHERE pc.id_persona = :persona_id
                ORDER BY cond.nombre, calle.nombre, c.casa
            ");
            
            $stmt->execute(['persona_id' => $personaId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $this->logError("Casa::getCasasByPersona - Error: " . $e->getMessage());
            return [];
        }
    }
    
    // ===============================================
    // MÉTODOS DE VALIDACIÓN
    // ===============================================
    
    /**
     * Validar que un condominio existe
     * 
     * @param int $condominioId ID del condominio
     * @return bool True si existe
     */
    public function validateCondominioExists(int $condominioId): bool {
        try {
            $stmt = $this->connection->prepare("SELECT id_condominio FROM condominios WHERE id_condominio = :id");
            $stmt->execute(['id' => $condominioId]);
            return $stmt->fetch() !== false;
        } catch (Exception $e) {
            $this->logError("Casa::validateCondominioExists - Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validar que una calle existe
     * 
     * @param int $calleId ID de la calle
     * @return bool True si existe
     */
    public function validateCalleExists(int $calleId): bool {
        try {
            $stmt = $this->connection->prepare("SELECT id_calle FROM calles WHERE id_calle = :id");
            $stmt->execute(['id' => $calleId]);
            return $stmt->fetch() !== false;
        } catch (Exception $e) {
            $this->logError("Casa::validateCalleExists - Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validar que una casa existe
     * 
     * @param int $casaId ID de la casa
     * @return bool True si existe
     */
    public function validateCasaExists(int $casaId): bool {
        try {
            $stmt = $this->connection->prepare("SELECT id_casa FROM casas WHERE id_casa = :id");
            $stmt->execute(['id' => $casaId]);
            return $stmt->fetch() !== false;
        } catch (Exception $e) {
            $this->logError("Casa::validateCasaExists - Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validar que una persona existe
     * 
     * @param int $personaId ID de la persona
     * @return bool True si existe
     */
    public function validatePersonaExists(int $personaId): bool {
        try {
            $stmt = $this->connection->prepare("SELECT id_persona FROM personas WHERE id_persona = :id");
            $stmt->execute(['id' => $personaId]);
            return $stmt->fetch() !== false;
        } catch (Exception $e) {
            $this->logError("Casa::validatePersonaExists - Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validar que una calle pertenece a un condominio
     * 
     * @param int $calleId ID de la calle
     * @param int $condominioId ID del condominio
     * @return bool True si la calle pertenece al condominio
     */
    public function validateCalleInCondominio(int $calleId, int $condominioId): bool {
        try {
            $stmt = $this->connection->prepare("
                SELECT id_calle FROM calles 
                WHERE id_calle = :calle_id AND id_condominio = :condominio_id
            ");
            $stmt->execute([
                'calle_id' => $calleId,
                'condominio_id' => $condominioId
            ]);
            return $stmt->fetch() !== false;
        } catch (Exception $e) {
            $this->logError("Casa::validateCalleInCondominio - Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar si una persona ya está asignada a una casa
     * 
     * @param int $personaId ID de la persona
     * @param int $casaId ID de la casa
     * @return bool True si ya está asignada
     */
    public function isPersonaAssignedToCasa(int $personaId, int $casaId): bool {
        try {
            $stmt = $this->connection->prepare("
                SELECT 1 FROM persona_casa 
                WHERE id_persona = :persona_id AND id_casa = :casa_id
            ");
            $stmt->execute([
                'persona_id' => $personaId,
                'casa_id' => $casaId
            ]);
            return $stmt->fetch() !== false;
        } catch (Exception $e) {
            $this->logError("Casa::isPersonaAssignedToCasa - Error: " . $e->getMessage());
            return false;
        }
    }
    
    // ===============================================
    // MÉTODOS HEREDADOS DE BASEMODEL (OVERRIDE)
    // ===============================================
    
    /**
     * Implementación específica del create() heredado
     * Redirige al método createCasa()
     * 
     * @param array $data Datos para crear
     * @return int|false
     */
    public function create(array $data): int|false {
        return $this->createCasa($data);
    }
    
    /**
     * Implementación específica del findById() heredado
     * Redirige al método findCasaById()
     * 
     * @param int $id ID a buscar
     * @return array|null
     */
    public function findById(int $id): array|null {
        return $this->findCasaById($id);
    }
    
    /**
     * Implementación específica del update() heredado
     * Redirige al método updateCasa()
     * 
     * @param int $id ID a actualizar
     * @param array $data Datos a actualizar
     * @return bool
     */
    public function update(int $id, array $data): bool {
        return $this->updateCasa($id, $data);
    }
    
    /**
     * Implementación específica del delete() heredado
     * Redirige al método deleteCasa()
     * 
     * @param int $id ID a eliminar
     * @return bool
     */
    public function delete(int $id): bool {
        return $this->deleteCasa($id);
    }
    
    /**
     * Implementación específica del findAll() heredado
     * Obtiene todas las casas con información relacionada
     * 
     * @param int $limit Límite de resultados
     * @return array
     */
    public function findAll(int $limit = 100): array {
        try {
            $stmt = $this->connection->prepare("
                SELECT c.*, 
                       cond.nombre as condominio_nombre,
                       calle.nombre as calle_nombre
                FROM casas c
                LEFT JOIN condominios cond ON c.id_condominio = cond.id_condominio
                LEFT JOIN calles calle ON c.id_calle = calle.id_calle
                ORDER BY cond.nombre, calle.nombre, c.casa
                LIMIT :limit
            ");
            
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $this->logError("Casa::findAll - Error: " . $e->getMessage());
            return [];
        }
    }
    
    // ===============================================
    // MÉTODOS ESTADÍSTICOS Y DE REPORTE
    // ===============================================
    
    /**
     * Obtener estadísticas de casas por condominio
     * 
     * @param int $condominioId ID del condominio
     * @return array Estadísticas
     */
    public function getEstadisticasByCondominio(int $condominioId): array {
        try {
            $stmt = $this->connection->prepare("
                SELECT 
                    COUNT(c.id_casa) as total_casas,
                    COUNT(DISTINCT c.id_calle) as total_calles,
                    COUNT(pc.id_persona) as total_habitantes,
                    COUNT(cr.codigo) as total_claves_registro
                FROM casas c
                LEFT JOIN persona_casa pc ON c.id_casa = pc.id_casa
                LEFT JOIN claves_registro cr ON c.id_casa = cr.id_casa
                WHERE c.id_condominio = :condominio_id
            ");
            
            $stmt->execute(['condominio_id' => $condominioId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            
        } catch (Exception $e) {
            $this->logError("Casa::getEstadisticasByCondominio - Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener reporte completo de una casa
     * 
     * @param int $casaId ID de la casa
     * @return array Reporte completo
     */
    public function getReporteCompleto(int $casaId): array {
        try {
            $casa = $this->findCasaById($casaId);
            if (!$casa) {
                return [];
            }
            
            $personas = $this->getPersonasByCasa($casaId);
            $claves = $this->getClavesByCasa($casaId);
            
            return [
                'casa' => $casa,
                'personas' => $personas,
                'claves_registro' => $claves,
                'estadisticas' => [
                    'total_personas' => count($personas),
                    'total_claves' => count($claves),
                    'claves_usadas' => array_filter($claves, fn($c) => $c['usado'] == 1),
                    'claves_disponibles' => array_filter($claves, fn($c) => $c['usado'] == 0)
                ]
            ];
            
        } catch (Exception $e) {
            $this->logError("Casa::getReporteCompleto - Error: " . $e->getMessage());
            return [];
        }
    }
}
?>
