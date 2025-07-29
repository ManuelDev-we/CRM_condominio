<?php
/**
 * BLOG MODEL - RED SOCIAL DEL CONDOMINIO
 * Sistema Cyberhole Condominios - Arquitectura 3 Capas
 * 
 * @description Modelo para gestión completa de posts de red social por condominio.
 *              Los administradores pueden crear, editar y eliminar posts.
 *              Los residentes pueden ver posts de su condominio.
 * @author Sistema Cyberhole - Desarrollado según documentación oficial
 * @version 3.0 - IMPLEMENTADO SEGÚN ESPECIFICACIÓN OFICIAL
 * @date 2025-07-12
 * 
 * 🔥 RESPONSABILIDADES SEGÚN RELACIONES_TABLAS_CYBERHOLE_CORREGIDO:
 * - ✅ Tabla Principal: blog
 * - 🔗 Relaciones: Conecta blog con admin (autor)
 * 
 * 🔥 ESTRUCTURA REAL DE LA TABLA:
 * 
 * TABLA: blog
 * - id_blog (int, PK, AUTO_INCREMENT)
 * - titulo (varchar 255, NOT NULL)
 * - contenido (text, NOT NULL)
 * - imagen (text, nullable)
 * - visible_para (enum: 'todos','admin','residentes', default 'todos')
 * - creado_por_admin (int, FK a admin.id_admin, nullable)
 * - id_condominio (int, FK a condominios.id_condominio, nullable)
 * - fecha_creacion (timestamp, default CURRENT_TIMESTAMP)
 * 
 * 🔥 CUMPLIMIENTO DEL DIAGRAMA UML OFICIAL:
 * - +findByAuthor(int adminId) array ✅ IMPLEMENTADO
 * - +validateAdminExists(int adminId) bool ✅ IMPLEMENTADO
 * - +validateVisibilityValue(string visibility) bool ✅ IMPLEMENTADO
 */

require_once __DIR__ . '/BaseModel.php';

class Blog extends BaseModel
{
    /**
     * @var string $table Nombre de la tabla principal
     */
    protected string $table = 'blog';
    
    /**
     * @var array $fillable Campos permitidos para mass assignment
     */
    protected array $fillable = [
        'titulo', 'contenido', 'imagen', 'visible_para', 
        'creado_por_admin', 'id_condominio'
    ];
    
    /**
     * @var array $required_fields Campos obligatorios
     */
    protected array $required_fields = [
        'titulo', 'contenido', 'creado_por_admin', 'id_condominio'
    ];
    
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    // ==========================================
    // MÉTODOS REQUERIDOS POR DIAGRAMA UML OFICIAL
    // ==========================================
    
    /**
     * Buscar posts por autor (admin)
     * MÉTODO REQUERIDO POR DIAGRAMA UML OFICIAL: +findByAuthor(int adminId) array
     * @param int $adminId ID del administrador autor
     * @return array Lista de posts del autor
     */
    public function findByAuthor(int $adminId): array
    {
        try {
            $sql = "SELECT b.*, a.nombres as admin_nombres, a.apellido1 as admin_apellido, co.nombre as condominio_nombre
                    FROM {$this->table} b
                    LEFT JOIN admin a ON b.creado_por_admin = a.id_admin
                    LEFT JOIN condominios co ON b.id_condominio = co.id_condominio
                    WHERE b.creado_por_admin = :admin_id
                    ORDER BY b.fecha_creacion DESC";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(['admin_id' => $adminId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $this->logError("Error en findByAuthor(): " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Validar que existe administrador
     * MÉTODO REQUERIDO POR DIAGRAMA UML OFICIAL: +validateAdminExists(int adminId) bool
     * @param int $adminId ID del administrador
     * @return bool True si existe el administrador
     */
    public function validateAdminExists(int $adminId): bool
    {
        try {
            $sql = "SELECT 1 FROM admin WHERE id_admin = :id LIMIT 1";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(['id' => $adminId]);
            
            return $stmt->fetchColumn() !== false;
            
        } catch (Exception $e) {
            $this->logError("Error en validateAdminExists(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validar valor de visibilidad
     * MÉTODO REQUERIDO POR DIAGRAMA UML OFICIAL: +validateVisibilityValue(string visibility) bool
     * @param string $visibility Valor de visibilidad a validar
     * @return bool True si es un valor válido
     */
    public function validateVisibilityValue(string $visibility): bool
    {
        $validValues = ['todos', 'admin', 'residentes'];
        return in_array($visibility, $validValues, true);
    }
    
    // ==========================================
    // MÉTODOS PRINCIPALES DEL BLOG
    // ==========================================
    
    /**
     * Crear nuevo post en el blog
     * MÉTODO PRINCIPAL MEJORADO CON VALIDACIONES UML OFICIALES
     * @param array $data Datos del post
     * @return int|false ID del post creado o false si falla
     */
    public function createPost(array $data): int|false
    {
        try {
            // Validar campos requeridos
            if (!$this->validateRequiredFields($data, $this->required_fields)) {
                $this->logError("Campos requeridos faltantes");
                return false;
            }
            
            // Validar que existe el administrador usando método UML OFICIAL
            if (!$this->validateAdminExists((int)$data['creado_por_admin'])) {
                $this->logError("Administrador no existe: {$data['creado_por_admin']}");
                return false;
            }
            
            // Validar que existe el condominio
            if (!$this->validateCondominioExists((int)$data['id_condominio'])) {
                $this->logError("Condominio no existe: {$data['id_condominio']}");
                return false;
            }
            
            // Validar valor de visibilidad usando método UML OFICIAL
            if (isset($data['visible_para']) && !$this->validateVisibilityValue($data['visible_para'])) {
                $this->logError("Valor de visibilidad inválido: {$data['visible_para']}");
                return false;
            }
            
            // Establecer valor por defecto para visible_para
            $data['visible_para'] = $data['visible_para'] ?? 'todos';
            
            // Validar longitud del título
            if (!$this->isValidLength($data['titulo'], 5, 255)) {
                $this->logError("Título inválido: {$data['titulo']}");
                return false;
            }
            
            // Validar longitud del contenido
            if (!$this->isValidLength($data['contenido'], 10, 65535)) {
                $this->logError("Contenido inválido (muy corto o muy largo)");
                return false;
            }
            
            // Validar unicidad del título en el condominio
            if ($this->postExistsWithTitle($data['titulo'], (int)$data['id_condominio'])) {
                $this->logError("Ya existe un post con el título '{$data['titulo']}' en este condominio");
                return false;
            }
            
            return $this->create($data);
            
        } catch (Exception $e) {
            $this->logError("Error en createPost(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener posts por condominio
     * @param int $condominioId ID del condominio
     * @return array Lista de posts del condominio
     */
    public function getPostsByCondominio(int $condominioId): array
    {
        try {
            $sql = "SELECT b.*, a.nombres as admin_nombres, a.apellido1 as admin_apellido
                    FROM {$this->table} b
                    LEFT JOIN admin a ON b.creado_por_admin = a.id_admin
                    WHERE b.id_condominio = :condominio_id
                    ORDER BY b.fecha_creacion DESC";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(['condominio_id' => $condominioId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $this->logError("Error en getPostsByCondominio(): " . $e->getMessage());
            return [];
        }
    }
    
    // ==========================================
    // MÉTODOS CRUD BÁSICOS
    // ==========================================
    
    /**
     * Crear post con validaciones completas
     * @param array $data Datos del post
     * @return int|false ID del post creado o false si falla
     */
    public function create(array $data): int|false
    {
        try {
            $query = $this->buildInsertQuery($data);
            $stmt = $this->executeQuery($query['sql'], $query['params']);
            
            if (!$stmt) {
                return false;
            }
            
            return $this->getLastInsertId();
            
        } catch (Exception $e) {
            $this->logError("Error en create(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Buscar post por ID
     * @param int $id ID del post
     * @return array|null Datos del post
     */
    public function findById(int $id): array|null
    {
        try {
            $sql = "SELECT b.*, a.nombres as admin_nombres, a.apellido1 as admin_apellido, co.nombre as condominio_nombre
                    FROM {$this->table} b
                    LEFT JOIN admin a ON b.creado_por_admin = a.id_admin
                    LEFT JOIN condominios co ON b.id_condominio = co.id_condominio
                    WHERE b.id_blog = :id 
                    LIMIT 1";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
            
        } catch (Exception $e) {
            $this->logError("Error en findById(): " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Actualizar post
     * @param int $id ID del post
     * @param array $data Datos a actualizar
     * @return bool True si se actualizó correctamente
     */
    public function update(int $id, array $data): bool
    {
        try {
            // Validaciones si se actualizan campos críticos
            if (isset($data['creado_por_admin']) && !$this->validateAdminExists((int)$data['creado_por_admin'])) {
                $this->logError("Administrador no existe: {$data['creado_por_admin']}");
                return false;
            }
            
            if (isset($data['visible_para']) && !$this->validateVisibilityValue($data['visible_para'])) {
                $this->logError("Valor de visibilidad inválido: {$data['visible_para']}");
                return false;
            }
            
            if (isset($data['id_condominio']) && !$this->validateCondominioExists((int)$data['id_condominio'])) {
                $this->logError("Condominio no existe: {$data['id_condominio']}");
                return false;
            }
            
            $sql = "UPDATE {$this->table} SET ";
            $setParts = [];
            foreach (array_keys($data) as $field) {
                $setParts[] = "{$field} = :{$field}";
            }
            $sql .= implode(', ', $setParts) . " WHERE id_blog = :id";
            
            $data['id'] = $id;
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($data);
            
        } catch (Exception $e) {
            $this->logError("Error en update(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar post
     * @param int $id ID del post
     * @return bool True si se eliminó correctamente
     */
    public function delete(int $id): bool
    {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id_blog = :id";
            $stmt = $this->executeQuery($sql, ['id' => $id]);
            
            return $stmt !== false && $stmt->rowCount() > 0;
            
        } catch (Exception $e) {
            $this->logError("Error en delete(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener todos los posts
     * @param int $limit Límite de resultados
     * @return array Lista de posts
     */
    public function findAll(int $limit = 100): array
    {
        try {
            $sql = "SELECT b.*, a.nombres as admin_nombres, a.apellido1 as admin_apellido, co.nombre as condominio_nombre
                    FROM {$this->table} b
                    LEFT JOIN admin a ON b.creado_por_admin = a.id_admin
                    LEFT JOIN condominios co ON b.id_condominio = co.id_condominio
                    ORDER BY b.fecha_creacion DESC
                    LIMIT :limit";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $this->logError("Error en findAll(): " . $e->getMessage());
            return [];
        }
    }
    
    // ==========================================
    // MÉTODOS AUXILIARES Y VALIDACIONES
    // ==========================================
    
    /**
     * Verificar si existe post por ID
     * @param int $id ID del post
     * @return bool True si existe
     */
    protected function exists(int $id): bool
    {
        try {
            $sql = "SELECT 1 FROM {$this->table} WHERE id_blog = :id LIMIT 1";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            return $stmt->fetchColumn() !== false;
            
        } catch (PDOException $e) {
            $this->logError("Error en exists(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validar que existe condominio
     * @param int $condominioId ID del condominio
     * @return bool True si existe el condominio
     */
    private function validateCondominioExists(int $condominioId): bool
    {
        try {
            $sql = "SELECT 1 FROM condominios WHERE id_condominio = :id LIMIT 1";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(['id' => $condominioId]);
            
            return $stmt->fetchColumn() !== false;
            
        } catch (Exception $e) {
            $this->logError("Error en validateCondominioExists(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar si ya existe post con el mismo título en el condominio
     * @param string $titulo Título del post
     * @param int $condominioId ID del condominio
     * @return bool True si ya existe
     */
    private function postExistsWithTitle(string $titulo, int $condominioId): bool
    {
        try {
            $sql = "SELECT 1 FROM {$this->table} WHERE titulo = :titulo AND id_condominio = :condominio_id LIMIT 1";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(['titulo' => $titulo, 'condominio_id' => $condominioId]);
            
            return $stmt->fetchColumn() !== false;
            
        } catch (Exception $e) {
            $this->logError("Error en postExistsWithTitle(): " . $e->getMessage());
            return false;
        }
    }
    
    // ==========================================
    // MÉTODOS ADICIONALES DE UTILIDAD
    // ==========================================
    
    /**
     * Obtener posts públicos por condominio
     * @param int $condominioId ID del condominio
     * @return array Lista de posts públicos
     */
    public function getPublicPostsByCondominio(int $condominioId): array
    {
        try {
            $sql = "SELECT b.*, a.nombres as admin_nombres, a.apellido1 as admin_apellido
                    FROM {$this->table} b
                    LEFT JOIN admin a ON b.creado_por_admin = a.id_admin
                    WHERE b.id_condominio = :condominio_id 
                    AND b.visible_para IN ('todos', 'residentes')
                    ORDER BY b.fecha_creacion DESC";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(['condominio_id' => $condominioId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $this->logError("Error en getPublicPostsByCondominio(): " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar posts por texto
     * @param string $searchText Texto a buscar
     * @param int $condominioId ID del condominio
     * @return array Posts encontrados
     */
    public function searchPosts(string $searchText, int $condominioId): array
    {
        try {
            $sql = "SELECT b.*, a.usuario as admin_usuario, a.nombre as admin_nombre
                    FROM {$this->table} b
                    LEFT JOIN admin a ON b.creado_por_admin = a.id_admin
                    WHERE b.id_condominio = :condominio_id 
                    AND (b.titulo LIKE :search OR b.contenido LIKE :search)
                    ORDER BY b.fecha_creacion DESC";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([
                'condominio_id' => $condominioId,
                'search' => "%{$searchText}%"
            ]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $this->logError("Error en searchPosts(): " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener estadísticas del blog
     * @param int $condominioId ID del condominio
     * @return array Estadísticas del blog
     */
    public function getBlogStatistics(int $condominioId): array
    {
        try {
            $stats = [];
            
            // Total de posts
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE id_condominio = :condominio_id";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(['condominio_id' => $condominioId]);
            $stats['total_posts'] = (int)$stmt->fetchColumn();
            
            // Posts por visibilidad
            $sql = "SELECT visible_para, COUNT(*) as count 
                    FROM {$this->table} 
                    WHERE id_condominio = :condominio_id 
                    GROUP BY visible_para";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(['condominio_id' => $condominioId]);
            $stats['posts_por_visibilidad'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Posts por autor
            $sql = "SELECT a.nombres, a.apellido1, COUNT(*) as posts 
                    FROM {$this->table} b
                    LEFT JOIN admin a ON b.creado_por_admin = a.id_admin
                    WHERE b.id_condominio = :condominio_id 
                    GROUP BY b.creado_por_admin 
                    ORDER BY posts DESC";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(['condominio_id' => $condominioId]);
            $stats['posts_por_autor'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $stats;
            
        } catch (Exception $e) {
            $this->logError("Error en getBlogStatistics(): " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener posts recientes
     * @param int $condominioId ID del condominio
     * @param int $limite Número de posts a obtener
     * @return array Posts recientes
     */
    public function getPostsRecientes(int $condominioId, int $limite = 5): array
    {
        try {
            $sql = "SELECT b.*, a.usuario as admin_usuario, a.nombre as admin_nombre
                    FROM {$this->table} b
                    LEFT JOIN admin a ON b.creado_por_admin = a.id_admin
                    WHERE b.id_condominio = :condominio_id
                    ORDER BY b.fecha_creacion DESC
                    LIMIT :limite";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue(':condominio_id', $condominioId, PDO::PARAM_INT);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $this->logError("Error en getPostsRecientes(): " . $e->getMessage());
            return [];
        }
    }
}
?>

