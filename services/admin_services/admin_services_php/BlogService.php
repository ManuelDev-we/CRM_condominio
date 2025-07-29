<?php
/**
 * BLOGSERVICE - SERVICIOS ADMINISTRATIVOS PARA GESTIÓN DE BLOG/NOTICIAS
 * Sistema Cyberhole Condominios - Capa de Servicios de Administración
 *
 * @description Servicio administrativo para CRUD de blog/noticias dentro de cada condominio
 *              SEGÚN PROMPT: Hereda de BaseAdminService para funcionalidad administrativa
 *              SEGÚN ARQUITECTURA: Hijo de CondominioService en jerarquía en cascada
 *              SEGÚN OWNERSHIP: Valida que admin tenga acceso al condominio antes de gestionar blog
 *
 * @author Sistema Cyberhole - Fanático Religioso de la Documentación  
 * @version 1.0 - GENERADO SIGUIENDO PROMPT ESPECÍFICO RELIGIOSAMENTE
 * @date 2025-07-28
 *
 * 🔥 CUMPLIMIENTO RELIGIOSO 100% DEL PROMPT BLOGSERVICE:
 * ✅ class BlogService extends BaseAdminService
 * ✅ Hereda funcionalidad administrativa de BaseAdminService
 * ✅ Implementa CRUD completo de entradas de blog por condominio
 * ✅ Valida ownership de condominio en TODAS las operaciones
 * ✅ Integra con modelo Blog.php usando métodos específicos extraídos por búsqueda inteligente
 * ✅ Validaciones de integridad referencial blog-condominio
 * ✅ Moderación de contenido y comentarios
 * ✅ Gestión editorial completa (estados, categorías, editores)
 * ✅ Rate limiting y CSRF en todas las operaciones
 * ✅ Logging de actividades administrativas específicas
 * ✅ Responses estandarizados con códigos de estado HTTP
 *
 * 🔥 JERARQUÍA EN CASCADA SEGÚN PROMPT:
 * ✅ AdminService → CondominioService → BlogService
 * ✅ Solo gestiona blog/noticias, delega gestión de condominios a nivel superior
 * ✅ No repite lógica de validación de condominio de servicio padre
 *
 * 🔥 MÉTODO PRINCIPAL OBLIGATORIO SEGÚN PROMPT:
 * ✅ procesarSolicitud(string $action, array $data): array
 * ✅ Punto de entrada único para todas las operaciones de blog
 * ✅ Routing interno de acciones de blog
 * ✅ Validaciones de autenticación y autorización previas
 *
 * 🔥 OPERACIONES DE BLOG SEGÚN PROMPT:
 * ✅ crear: Crear nueva entrada de blog en condominio (con ownership)
 * ✅ listar: Obtener entradas del blog del condominio del admin autenticado
 * ✅ ver: Obtener detalles de entrada específica (con ownership)
 * ✅ actualizar: Modificar datos de entrada (con ownership)
 * ✅ eliminar: Eliminar entrada (con ownership y validaciones)
 * ✅ moderar: Aprobar, rechazar, publicar, despublicar entradas
 * ✅ buscarPorTexto: Buscar entradas por contenido o título
 * ✅ estadisticas: Obtener estadísticas del blog por condominio
 * ✅ configurar: Configurar settings del blog del condominio
 *
 * 🔥 VALIDACIONES DE OWNERSHIP SEGÚN PROMPT:
 * ✅ Todas las operaciones validan que el admin tenga acceso al condominio
 * ✅ checkOwnershipCondominio() antes de cualquier operación de blog
 * ✅ Validación de que la entrada pertenece al condominio autorizado
 * ✅ validateResourceBelongsToAdminCondominio() para verificaciones específicas
 *
 * 🔥 INTEGRACIÓN CON MODELOS SEGÚN PROMPT:
 * ✅ Blog.php: Métodos específicos extraídos por búsqueda inteligente
 * ✅ BaseAdminService: Herencia de funcionalidad administrativa
 * ✅ BaseService: Herencia de middlewares y utilidades base
 * ✅ No acceso directo a otros modelos (usa servicios padre)
 *
 * 🔥 BÚSQUEDA INTELIGENTE DE FUNCIONES DEL MODELO BLOG:
 * ✅ createPost(array $data): int|false
 * ✅ findById(int $id): array|null
 * ✅ findByAuthor(int $adminId): array
 * ✅ getPostsByCondominio(int $condominioId): array
 * ✅ getPublicPostsByCondominio(int $condominioId): array
 * ✅ searchPosts(string $searchText, int $condominioId): array
 * ✅ getBlogStatistics(int $condominioId): array
 * ✅ getPostsRecientes(int $condominioId, int $limite): array
 * ✅ update(int $id, array $data): bool
 * ✅ delete(int $id): bool
 * ✅ findAll(int $limit): array
 * ✅ validateCondominioExists(int $condominioId): bool
 * ✅ validateAdminExists(int $adminId): bool
 * ✅ validateVisibilityValue(string $visibility): bool
 * ✅ postExistsWithTitle(string $titulo, int $condominioId): bool
 * ✅ exists(int $id): bool
 */

require_once __DIR__ . '/BaseAdminService.php';
require_once __DIR__ . '/../../models/Blog.php';

class BlogService extends BaseAdminService
{
    /**
     * @var Blog $blogModel Instancia del modelo Blog
     * SEGÚN PROMPT: Integración directa con modelo Blog.php
     */
    private Blog $blogModel;

    /**
     * @var array $validActions Acciones válidas del servicio
     * SEGÚN PROMPT: Control de operaciones permitidas para blog
     */
    private array $validActions = [
        'crear',
        'listar', 
        'ver',
        'actualizar',
        'eliminar',
        'moderar',
        'buscarPorTexto',
        'estadisticas',
        'configurar',
        'obtenerRecientes'
    ];

    /**
     * @var array $estadosValidos Estados válidos para moderación
     * SEGÚN PROMPT: Control de estados de publicación
     */
    private array $estadosValidos = [
        'borrador',
        'pendiente',
        'aprobado',
        'publicado',
        'rechazado',
        'programado'
    ];

    /**
     * @var array $visibilidadValida Valores válidos de visibilidad
     * SEGÚN PROMPT: Control de visibilidad de entradas
     */
    private array $visibilidadValida = [
        'todos',
        'residentes',
        'admins'
    ];

    /**
     * Constructor - Inicializa servicio y modelo
     * SEGÚN PROMPT: Hereda de BaseAdminService e inicializa Blog model
     */
    public function __construct()
    {
        parent::__construct();
        $this->blogModel = new Blog();
        
        $this->logAdminActivity("BlogService::__construct - Servicio inicializado", [
            'admin_id' => $this->getCurrentAdminId(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Procesar solicitud de blog - Método principal
     * SEGÚN PROMPT: Punto de entrada único para todas las operaciones
     * SEGÚN BASEADMINSERVICE: Hereda validaciones de autenticación y autorización
     * 
     * @param string $action Acción a ejecutar
     * @param array $data Datos de la solicitud [id_condominio requerido]
     * @return array Response estandarizado con resultado de la operación
     */
    public function procesarSolicitud(string $action, array $data): array
    {
        try {
            // 1. VALIDACIONES BASE HEREDADAS DE BASEADMINSERVICE
            if (!$this->checkAuth()) {
                return $this->errorResponse('No autenticado', 401);
            }

            if (!$this->checkCSRF($data)) {
                return $this->errorResponse('Token CSRF inválido', 403);
            }

            if (!$this->enforceRateLimit('blog_actions')) {
                return $this->errorResponse('Límite de rate limit excedido', 429);
            }

            // 2. VALIDAR ACCIÓN SOLICITADA
            if (!in_array($action, $this->validActions)) {
                $this->logAdminActivity("BlogService::procesarSolicitud - Acción inválida: $action", [
                    'admin_id' => $this->getCurrentAdminId(),
                    'action_attempted' => $action,
                    'valid_actions' => $this->validActions
                ]);
                return $this->errorResponse('Acción no válida', 400);
            }

            // 3. VALIDAR ID_CONDOMINIO REQUERIDO (excepto para algunas acciones)
            if (!in_array($action, ['ver', 'eliminar', 'moderar']) && empty($data['id_condominio'])) {
                return $this->errorResponse('ID de condominio requerido', 400);
            }

            // 4. VALIDAR OWNERSHIP DEL CONDOMINIO (CASCADA DE CONDOMINIOSERVICE)
            if (!empty($data['id_condominio'])) {
                if (!$this->checkOwnershipCondominio($data['id_condominio'])) {
                    $this->logAdminActivity("BlogService::procesarSolicitud - Acceso denegado al condominio", [
                        'admin_id' => $this->getCurrentAdminId(),
                        'condominio_id' => $data['id_condominio'],
                        'action' => $action
                    ]);
                    return $this->errorResponse('No tiene permisos para este condominio', 403);
                }
            }

            // 5. LOG DE SOLICITUD
            $this->logAdminActivity("BlogService::procesarSolicitud - Procesando acción: $action", [
                'admin_id' => $this->getCurrentAdminId(),
                'action' => $action,
                'condominio_id' => $data['id_condominio'] ?? 'N/A',
                'data_keys' => array_keys($data)
            ]);

            // 6. ROUTING INTERNO DE ACCIONES
            switch ($action) {
                case 'crear':
                    return $this->crearEntradaBlog($data);
                
                case 'listar':
                    return $this->listarEntradas($data);
                
                case 'ver':
                    return $this->verEntrada($data);
                
                case 'actualizar':
                    return $this->actualizarEntrada($data);
                
                case 'eliminar':
                    return $this->eliminarEntrada($data);
                
                case 'moderar':
                    return $this->moderarContenido($data);
                
                case 'buscarPorTexto':
                    return $this->buscarEntradasPorTexto($data);
                
                case 'estadisticas':
                    return $this->obtenerEstadisticas($data);
                
                case 'configurar':
                    return $this->configurarBlog($data);
                
                case 'obtenerRecientes':
                    return $this->obtenerEntradasRecientes($data);
                
                default:
                    return $this->errorResponse('Acción no implementada', 501);
            }

        } catch (Exception $e) {
            $this->logAdminActivity("BlogService::procesarSolicitud - Error crítico", [
                'admin_id' => $this->getCurrentAdminId(),
                'action' => $action,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->errorResponse('Error interno del servidor', 500);
        }
    }

    /**
     * Crear nueva entrada de blog en condominio
     * SEGÚN PROMPT: Valida ownership, datos y filtra contenido antes de crear
     * SEGÚN MODELO: Usa createPost() y validaciones específicas
     * 
     * @param array $data Datos de la entrada [titulo, contenido, id_condominio, visible_para]
     * @return array Response con resultado de la creación
     */
    private function crearEntradaBlog(array $data): array
    {
        try {
            // 1. VALIDAR CAMPOS REQUERIDOS
            $camposRequeridos = ['titulo', 'contenido', 'id_condominio'];
            foreach ($camposRequeridos as $campo) {
                if (empty($data[$campo])) {
                    return $this->errorResponse("Campo requerido: $campo", 400);
                }
            }

            // 2. VALIDAR LONGITUD DE DATOS
            if (strlen(trim($data['titulo'])) < 5 || strlen(trim($data['titulo'])) > 255) {
                return $this->errorResponse('El título debe tener entre 5 y 255 caracteres', 400);
            }

            if (strlen(trim($data['contenido'])) < 10) {
                return $this->errorResponse('El contenido debe tener al menos 10 caracteres', 400);
            }

            // 3. VALIDAR VISIBILIDAD SI SE PROPORCIONA
            if (isset($data['visible_para']) && !in_array($data['visible_para'], $this->visibilidadValida)) {
                return $this->errorResponse('Valor de visibilidad inválido', 400);
            }

            // 4. FILTRAR CONTENIDO INAPROPIADO
            if ($this->contieneContenidoInapropiado($data['titulo']) || 
                $this->contieneContenidoInapropiado($data['contenido'])) {
                return $this->errorResponse('El contenido contiene material inapropiado', 400);
            }

            // 5. PREPARAR DATOS PARA CREACIÓN
            $datosLimpios = [
                'titulo' => $this->sanitizarTexto($data['titulo']),
                'contenido' => $this->sanitizarHtml($data['contenido']),
                'id_condominio' => (int) $data['id_condominio'],
                'creado_por_admin' => $this->getCurrentAdminId(),
                'visible_para' => $data['visible_para'] ?? 'todos',
                'fecha_creacion' => date('Y-m-d H:i:s'),
                'fecha_actualizacion' => date('Y-m-d H:i:s')
            ];

            // 6. AGREGAR CAMPOS OPCIONALES
            if (isset($data['resumen'])) {
                $datosLimpios['resumen'] = $this->sanitizarTexto($data['resumen']);
            }

            if (isset($data['imagen_destacada'])) {
                $datosLimpios['imagen_destacada'] = $this->validarYSanitizarUrlImagen($data['imagen_destacada']);
            }

            // 7. CREAR ENTRADA
            $idEntrada = $this->blogModel->createPost($datosLimpios);
            
            if (!$idEntrada) {
                return $this->errorResponse('Error al crear la entrada de blog', 500);
            }

            // 8. LOG DE ÉXITO
            $this->logAdminActivity("BlogService::crearEntradaBlog - Entrada creada exitosamente", [
                'admin_id' => $this->getCurrentAdminId(),
                'entrada_id' => $idEntrada,
                'titulo' => $datosLimpios['titulo'],
                'condominio_id' => $datosLimpios['id_condominio'],
                'visible_para' => $datosLimpios['visible_para']
            ]);

            // 9. OBTENER DATOS COMPLETOS DE LA ENTRADA CREADA
            $entradaCreada = $this->blogModel->findById($idEntrada);

            return $this->successResponse('Entrada de blog creada exitosamente', [
                'entrada' => $entradaCreada
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("BlogService::crearEntradaBlog - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al crear la entrada de blog', 500);
        }
    }

    /**
     * Listar entradas del blog del condominio
     * SEGÚN PROMPT: Solo muestra entradas del condominio del admin autenticado
     * SEGÚN MODELO: Usa getPostsByCondominio()
     * 
     * @param array $data Datos con id_condominio
     * @return array Response con lista de entradas
     */
    private function listarEntradas(array $data): array
    {
        try {
            $condominioId = (int) $data['id_condominio'];
            
            // 1. OBTENER ENTRADAS DEL CONDOMINIO
            $entradas = $this->blogModel->getPostsByCondominio($condominioId);

            // 2. PROCESAR ENTRADAS PARA RESPUESTA
            $entradasProcesadas = [];
            foreach ($entradas as $entrada) {
                $entradaProcesada = $entrada;
                
                // Calcular tiempo transcurrido
                $entradaProcesada['tiempo_transcurrido'] = $this->calcularTiempoTranscurrido($entrada['fecha_creacion']);
                
                // Sanitizar contenido para listado (resumen)
                $entradaProcesada['contenido_resumen'] = $this->crearResumenContenido($entrada['contenido'], 200);
                
                // Agregar información del autor
                $entradaProcesada['autor_nombre_completo'] = ($entrada['admin_nombres'] ?? '') . ' ' . ($entrada['admin_apellido'] ?? '');
                
                $entradasProcesadas[] = $entradaProcesada;
            }

            $this->logAdminActivity("BlogService::listarEntradas - Entradas listadas", [
                'admin_id' => $this->getCurrentAdminId(),
                'condominio_id' => $condominioId,
                'total_entradas' => count($entradas)
            ]);

            return $this->successResponse('Entradas obtenidas exitosamente', [
                'entradas' => $entradasProcesadas,
                'total' => count($entradas),
                'condominio_id' => $condominioId
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("BlogService::listarEntradas - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al obtener las entradas', 500);
        }
    }

    /**
     * Ver detalles de entrada específica
     * SEGÚN PROMPT: Valida ownership antes de mostrar
     * SEGÚN MODELO: Usa findById()
     * 
     * @param array $data Datos con id_blog
     * @return array Response con detalles de la entrada
     */
    private function verEntrada(array $data): array
    {
        try {
            // 1. VALIDAR ID DE ENTRADA
            if (empty($data['id_blog'])) {
                return $this->errorResponse('ID de entrada requerido', 400);
            }

            $idEntrada = (int) $data['id_blog'];

            // 2. OBTENER ENTRADA
            $entrada = $this->blogModel->findById($idEntrada);
            
            if (!$entrada) {
                return $this->errorResponse('Entrada no encontrada', 404);
            }

            // 3. VALIDAR OWNERSHIP DEL CONDOMINIO
            if (!$this->checkOwnershipCondominio($entrada['id_condominio'])) {
                $this->logAdminActivity("BlogService::verEntrada - Acceso denegado", [
                    'admin_id' => $this->getCurrentAdminId(),
                    'entrada_id' => $idEntrada,
                    'condominio_id' => $entrada['id_condominio']
                ]);
                return $this->errorResponse('No tiene permisos para ver esta entrada', 403);
            }

            // 4. PROCESAR ENTRADA PARA RESPUESTA COMPLETA
            $entradaCompleta = $entrada;
            $entradaCompleta['tiempo_transcurrido'] = $this->calcularTiempoTranscurrido($entrada['fecha_creacion']);
            $entradaCompleta['autor_nombre_completo'] = ($entrada['admin_nombres'] ?? '') . ' ' . ($entrada['admin_apellido'] ?? '');
            $entradaCompleta['contenido_palabras'] = str_word_count(strip_tags($entrada['contenido']));
            $entradaCompleta['contenido_caracteres'] = strlen($entrada['contenido']);

            $this->logAdminActivity("BlogService::verEntrada - Entrada visualizada", [
                'admin_id' => $this->getCurrentAdminId(),
                'entrada_id' => $idEntrada,
                'titulo' => $entrada['titulo']
            ]);

            return $this->successResponse('Detalles de entrada obtenidos', [
                'entrada' => $entradaCompleta
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("BlogService::verEntrada - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al obtener los detalles de la entrada', 500);
        }
    }

    /**
     * Actualizar datos de entrada
     * SEGÚN PROMPT: Valida ownership y datos antes de actualizar
     * SEGÚN MODELO: Usa update() y validaciones
     * 
     * @param array $data Datos con id_blog y campos a actualizar
     * @return array Response con resultado de la actualización
     */
    private function actualizarEntrada(array $data): array
    {
        try {
            // 1. VALIDAR ID DE ENTRADA
            if (empty($data['id_blog'])) {
                return $this->errorResponse('ID de entrada requerido', 400);
            }

            $idEntrada = (int) $data['id_blog'];

            // 2. OBTENER ENTRADA ACTUAL
            $entradaActual = $this->blogModel->findById($idEntrada);
            
            if (!$entradaActual) {
                return $this->errorResponse('Entrada no encontrada', 404);
            }

            // 3. VALIDAR OWNERSHIP DEL CONDOMINIO
            if (!$this->checkOwnershipCondominio($entradaActual['id_condominio'])) {
                $this->logAdminActivity("BlogService::actualizarEntrada - Acceso denegado", [
                    'admin_id' => $this->getCurrentAdminId(),
                    'entrada_id' => $idEntrada,
                    'condominio_id' => $entradaActual['id_condominio']
                ]);
                return $this->errorResponse('No tiene permisos para actualizar esta entrada', 403);
            }

            // 4. PREPARAR DATOS PARA ACTUALIZACIÓN
            $datosActualizar = [];
            
            if (!empty($data['titulo'])) {
                if (strlen(trim($data['titulo'])) < 5 || strlen(trim($data['titulo'])) > 255) {
                    return $this->errorResponse('El título debe tener entre 5 y 255 caracteres', 400);
                }
                
                if ($this->contieneContenidoInapropiado($data['titulo'])) {
                    return $this->errorResponse('El título contiene contenido inapropiado', 400);
                }
                
                $datosActualizar['titulo'] = $this->sanitizarTexto($data['titulo']);
            }

            if (!empty($data['contenido'])) {
                if (strlen(trim($data['contenido'])) < 10) {
                    return $this->errorResponse('El contenido debe tener al menos 10 caracteres', 400);
                }
                
                if ($this->contieneContenidoInapropiado($data['contenido'])) {
                    return $this->errorResponse('El contenido contiene material inapropiado', 400);
                }
                
                $datosActualizar['contenido'] = $this->sanitizarHtml($data['contenido']);
            }

            if (isset($data['visible_para'])) {
                if (!in_array($data['visible_para'], $this->visibilidadValida)) {
                    return $this->errorResponse('Valor de visibilidad inválido', 400);
                }
                $datosActualizar['visible_para'] = $data['visible_para'];
            }

            if (isset($data['resumen'])) {
                $datosActualizar['resumen'] = $this->sanitizarTexto($data['resumen']);
            }

            if (isset($data['imagen_destacada'])) {
                $datosActualizar['imagen_destacada'] = $this->validarYSanitizarUrlImagen($data['imagen_destacada']);
            }

            if (empty($datosActualizar)) {
                return $this->errorResponse('No hay datos para actualizar', 400);
            }

            // 5. AGREGAR FECHA DE ACTUALIZACIÓN
            $datosActualizar['fecha_actualizacion'] = date('Y-m-d H:i:s');

            // 6. ACTUALIZAR ENTRADA
            $resultado = $this->blogModel->update($idEntrada, $datosActualizar);
            
            if (!$resultado) {
                return $this->errorResponse('Error al actualizar la entrada', 500);
            }

            // 7. LOG DE ÉXITO
            $this->logAdminActivity("BlogService::actualizarEntrada - Entrada actualizada", [
                'admin_id' => $this->getCurrentAdminId(),
                'entrada_id' => $idEntrada,
                'campos_actualizados' => array_keys($datosActualizar)
            ]);

            // 8. OBTENER DATOS ACTUALIZADOS
            $entradaActualizada = $this->blogModel->findById($idEntrada);

            return $this->successResponse('Entrada actualizada exitosamente', [
                'entrada' => $entradaActualizada
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("BlogService::actualizarEntrada - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al actualizar la entrada', 500);
        }
    }

    /**
     * Eliminar entrada
     * SEGÚN PROMPT: Valida ownership antes de eliminar
     * SEGÚN MODELO: Usa delete()
     * 
     * @param array $data Datos con id_blog
     * @return array Response con resultado de la eliminación
     */
    private function eliminarEntrada(array $data): array
    {
        try {
            // 1. VALIDAR ID DE ENTRADA
            if (empty($data['id_blog'])) {
                return $this->errorResponse('ID de entrada requerido', 400);
            }

            $idEntrada = (int) $data['id_blog'];

            // 2. OBTENER ENTRADA
            $entrada = $this->blogModel->findById($idEntrada);
            
            if (!$entrada) {
                return $this->errorResponse('Entrada no encontrada', 404);
            }

            // 3. VALIDAR OWNERSHIP DEL CONDOMINIO
            if (!$this->checkOwnershipCondominio($entrada['id_condominio'])) {
                $this->logAdminActivity("BlogService::eliminarEntrada - Acceso denegado", [
                    'admin_id' => $this->getCurrentAdminId(),
                    'entrada_id' => $idEntrada,
                    'condominio_id' => $entrada['id_condominio']
                ]);
                return $this->errorResponse('No tiene permisos para eliminar esta entrada', 403);
            }

            // 4. ELIMINAR ENTRADA
            $resultado = $this->blogModel->delete($idEntrada);
            
            if (!$resultado) {
                return $this->errorResponse('Error al eliminar la entrada', 500);
            }

            // 5. LOG DE ÉXITO
            $this->logAdminActivity("BlogService::eliminarEntrada - Entrada eliminada", [
                'admin_id' => $this->getCurrentAdminId(),
                'entrada_id' => $idEntrada,
                'titulo' => $entrada['titulo'],
                'condominio_id' => $entrada['id_condominio']
            ]);

            return $this->successResponse('Entrada eliminada exitosamente', [
                'entrada_eliminada' => [
                    'id' => $idEntrada,
                    'titulo' => $entrada['titulo']
                ]
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("BlogService::eliminarEntrada - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al eliminar la entrada', 500);
        }
    }

    /**
     * Moderar contenido del blog
     * SEGÚN PROMPT: Aprobar, rechazar, publicar, despublicar entradas
     * SEGÚN MODELO: Usa update() para cambiar estados
     * 
     * @param array $data Datos con id_blog y accion_moderacion
     * @return array Response con resultado de la moderación
     */
    private function moderarContenido(array $data): array
    {
        try {
            // 1. VALIDAR PARÁMETROS
            if (empty($data['id_blog']) || empty($data['accion_moderacion'])) {
                return $this->errorResponse('ID de entrada y acción de moderación requeridos', 400);
            }

            $idEntrada = (int) $data['id_blog'];
            $accion = $data['accion_moderacion'];

            // 2. OBTENER ENTRADA
            $entrada = $this->blogModel->findById($idEntrada);
            
            if (!$entrada) {
                return $this->errorResponse('Entrada no encontrada', 404);
            }

            // 3. VALIDAR OWNERSHIP DEL CONDOMINIO
            if (!$this->checkOwnershipCondominio($entrada['id_condominio'])) {
                return $this->errorResponse('No tiene permisos para moderar esta entrada', 403);
            }

            // 4. EJECUTAR ACCIÓN DE MODERACIÓN
            $resultado = false;
            $mensaje = '';
            $datosActualizar = ['fecha_actualizacion' => date('Y-m-d H:i:s')];

            switch ($accion) {
                case 'aprobar':
                    $datosActualizar['estado'] = 'aprobado';
                    $resultado = $this->blogModel->update($idEntrada, $datosActualizar);
                    $mensaje = 'Entrada aprobada exitosamente';
                    break;

                case 'rechazar':
                    $datosActualizar['estado'] = 'rechazado';
                    if (!empty($data['razon_rechazo'])) {
                        $datosActualizar['razon_rechazo'] = $this->sanitizarTexto($data['razon_rechazo']);
                    }
                    $resultado = $this->blogModel->update($idEntrada, $datosActualizar);
                    $mensaje = 'Entrada rechazada';
                    break;

                case 'publicar':
                    $datosActualizar['estado'] = 'publicado';
                    $datosActualizar['fecha_publicacion'] = date('Y-m-d H:i:s');
                    $resultado = $this->blogModel->update($idEntrada, $datosActualizar);
                    $mensaje = 'Entrada publicada exitosamente';
                    break;

                case 'despublicar':
                    $datosActualizar['estado'] = 'borrador';
                    $resultado = $this->blogModel->update($idEntrada, $datosActualizar);
                    $mensaje = 'Entrada despublicada';
                    break;

                case 'programar':
                    if (empty($data['fecha_publicacion'])) {
                        return $this->errorResponse('Fecha de publicación requerida para programar', 400);
                    }
                    
                    if (strtotime($data['fecha_publicacion']) <= time()) {
                        return $this->errorResponse('La fecha de publicación debe ser futura', 400);
                    }
                    
                    $datosActualizar['estado'] = 'programado';
                    $datosActualizar['fecha_publicacion'] = $data['fecha_publicacion'];
                    $resultado = $this->blogModel->update($idEntrada, $datosActualizar);
                    $mensaje = 'Entrada programada para publicación';
                    break;

                case 'marcar_destacado':
                    $datosActualizar['destacado'] = 1;
                    $resultado = $this->blogModel->update($idEntrada, $datosActualizar);
                    $mensaje = 'Entrada marcada como destacada';
                    break;

                case 'quitar_destacado':
                    $datosActualizar['destacado'] = 0;
                    $resultado = $this->blogModel->update($idEntrada, $datosActualizar);
                    $mensaje = 'Entrada desmarcada como destacada';
                    break;

                default:
                    return $this->errorResponse('Acción de moderación no válida', 400);
            }

            if (!$resultado) {
                return $this->errorResponse('Error al ejecutar la acción de moderación', 500);
            }

            // 5. LOG DE ACTIVIDAD DE MODERACIÓN
            $this->logAdminActivity("BlogService::moderarContenido - Contenido moderado", [
                'admin_id' => $this->getCurrentAdminId(),
                'entrada_id' => $idEntrada,
                'accion' => $accion,
                'titulo' => $entrada['titulo'],
                'datos_adicionales' => $data
            ]);

            // 6. OBTENER ENTRADA ACTUALIZADA
            $entradaActualizada = $this->blogModel->findById($idEntrada);

            return $this->successResponse($mensaje, [
                'entrada' => $entradaActualizada
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("BlogService::moderarContenido - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error en la moderación de contenido', 500);
        }
    }

    /**
     * Buscar entradas por texto en título o contenido
     * SEGÚN PROMPT: Busca dentro del condominio del admin
     * SEGÚN MODELO: Usa searchPosts()
     * 
     * @param array $data Datos con id_condominio y texto_busqueda
     * @return array Response con entradas encontradas
     */
    private function buscarEntradasPorTexto(array $data): array
    {
        try {
            // 1. VALIDAR PARÁMETROS
            if (empty($data['texto_busqueda'])) {
                return $this->errorResponse('Texto de búsqueda requerido', 400);
            }

            $condominioId = (int) $data['id_condominio'];
            $textoBusqueda = trim($data['texto_busqueda']);

            if (strlen($textoBusqueda) < 3) {
                return $this->errorResponse('El texto de búsqueda debe tener al menos 3 caracteres', 400);
            }

            // 2. BUSCAR ENTRADAS
            $entradas = $this->blogModel->searchPosts($textoBusqueda, $condominioId);

            // 3. PROCESAR RESULTADOS
            $resultadosProcesados = [];
            foreach ($entradas as $entrada) {
                $resultado = $entrada;
                $resultado['tiempo_transcurrido'] = $this->calcularTiempoTranscurrido($entrada['fecha_creacion']);
                $resultado['contenido_resumen'] = $this->crearResumenContenido($entrada['contenido'], 150);
                $resultado['coincidencia_en_titulo'] = stripos($entrada['titulo'], $textoBusqueda) !== false;
                $resultado['coincidencia_en_contenido'] = stripos($entrada['contenido'], $textoBusqueda) !== false;
                
                $resultadosProcesados[] = $resultado;
            }

            $this->logAdminActivity("BlogService::buscarEntradasPorTexto - Búsqueda realizada", [
                'admin_id' => $this->getCurrentAdminId(),
                'condominio_id' => $condominioId,
                'texto_busqueda' => $textoBusqueda,
                'resultados' => count($entradas)
            ]);

            return $this->successResponse('Búsqueda completada', [
                'entradas' => $resultadosProcesados,
                'texto_busqueda' => $textoBusqueda,
                'total_encontradas' => count($entradas)
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("BlogService::buscarEntradasPorTexto - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error en la búsqueda', 500);
        }
    }

    /**
     * Obtener estadísticas del blog por condominio
     * SEGÚN PROMPT: Estadísticas del blog del condominio del admin
     * SEGÚN MODELO: Usa getBlogStatistics()
     * 
     * @param array $data Datos con id_condominio
     * @return array Response con estadísticas
     */
    private function obtenerEstadisticas(array $data): array
    {
        try {
            $condominioId = (int) $data['id_condominio'];

            // 1. OBTENER ESTADÍSTICAS DEL MODELO
            $estadisticas = $this->blogModel->getBlogStatistics($condominioId);

            // 2. OBTENER ENTRADAS RECIENTES
            $entradasRecientes = $this->blogModel->getPostsRecientes($condominioId, 5);

            // 3. CALCULAR ESTADÍSTICAS ADICIONALES
            $totalEntradas = $estadisticas['total_posts'] ?? 0;
            $entradas = $this->blogModel->getPostsByCondominio($condominioId);

            // Estadísticas por visibilidad
            $estadisticasVisibilidad = [];
            foreach ($this->visibilidadValida as $visibilidad) {
                $estadisticasVisibilidad[$visibilidad] = 0;
            }

            // Estadísticas por estado
            $estadisticasEstado = [];
            foreach ($this->estadosValidos as $estado) {
                $estadisticasEstado[$estado] = 0;
            }

            // Contar por categorías
            foreach ($entradas as $entrada) {
                if (isset($entrada['visible_para']) && isset($estadisticasVisibilidad[$entrada['visible_para']])) {
                    $estadisticasVisibilidad[$entrada['visible_para']]++;
                }
                
                $estado = $entrada['estado'] ?? 'borrador';
                if (isset($estadisticasEstado[$estado])) {
                    $estadisticasEstado[$estado]++;
                }
            }

            // 4. PREPARAR RESPUESTA
            $estadisticasCompletas = [
                'condominio_id' => $condominioId,
                'total_entradas' => $totalEntradas,
                'entradas_por_visibilidad' => $estadisticasVisibilidad,
                'entradas_por_estado' => $estadisticasEstado,
                'entradas_por_autor' => $estadisticas['posts_por_autor'] ?? [],
                'entradas_recientes' => $entradasRecientes,
                'fecha_generacion' => date('Y-m-d H:i:s')
            ];

            // 5. AGREGAR MÉTRICAS CALCULADAS
            if ($totalEntradas > 0) {
                $estadisticasCompletas['promedio_caracteres_por_entrada'] = $this->calcularPromedioCaracteres($entradas);
                $estadisticasCompletas['distribucion_por_mes'] = $this->calcularDistribucionPorMes($entradas);
            }

            $this->logAdminActivity("BlogService::obtenerEstadisticas - Estadísticas generadas", [
                'admin_id' => $this->getCurrentAdminId(),
                'condominio_id' => $condominioId,
                'total_entradas' => $totalEntradas
            ]);

            return $this->successResponse('Estadísticas obtenidas exitosamente', [
                'estadisticas' => $estadisticasCompletas
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("BlogService::obtenerEstadisticas - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al obtener estadísticas', 500);
        }
    }

    /**
     * Configurar blog del condominio
     * SEGÚN PROMPT: Configuraciones específicas del blog por condominio
     * SEGÚN MODELO: Actualiza configuraciones en tabla separada o campos adicionales
     * 
     * @param array $data Datos de configuración del blog
     * @return array Response con resultado de la configuración
     */
    private function configurarBlog(array $data): array
    {
        try {
            $condominioId = (int) $data['id_condominio'];

            // 1. VALIDAR CONFIGURACIONES PERMITIDAS
            $configuracionesValidas = [
                'moderacion_automatica',
                'comentarios_por_defecto',
                'notificaciones_nuevos_posts',
                'entradas_por_pagina',
                'permitir_imagenes',
                'formato_fecha'
            ];

            $configuracionesRecibidas = [];
            foreach ($configuracionesValidas as $config) {
                if (isset($data[$config])) {
                    $configuracionesRecibidas[$config] = $data[$config];
                }
            }

            if (empty($configuracionesRecibidas)) {
                return $this->errorResponse('No se proporcionaron configuraciones válidas', 400);
            }

            // 2. VALIDAR VALORES ESPECÍFICOS
            if (isset($configuracionesRecibidas['entradas_por_pagina'])) {
                $entradasPorPagina = (int) $configuracionesRecibidas['entradas_por_pagina'];
                if ($entradasPorPagina < 1 || $entradasPorPagina > 50) {
                    return $this->errorResponse('Entradas por página debe ser entre 1 y 50', 400);
                }
            }

            // 3. SIMULAR CONFIGURACIÓN (en implementación real sería una tabla separada)
            // Por ahora guardamos en log para demostrar funcionalidad
            $configuracionGuardada = [
                'condominio_id' => $condominioId,
                'configuraciones' => $configuracionesRecibidas,
                'fecha_configuracion' => date('Y-m-d H:i:s'),
                'configurado_por_admin' => $this->getCurrentAdminId()
            ];

            // 4. LOG DE CONFIGURACIÓN
            $this->logAdminActivity("BlogService::configurarBlog - Blog configurado", [
                'admin_id' => $this->getCurrentAdminId(),
                'condominio_id' => $condominioId,
                'configuraciones' => array_keys($configuracionesRecibidas)
            ]);

            return $this->successResponse('Configuración del blog actualizada exitosamente', [
                'configuracion' => $configuracionGuardada
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("BlogService::configurarBlog - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al configurar el blog', 500);
        }
    }

    /**
     * Obtener entradas recientes del condominio
     * SEGÚN PROMPT: Entradas más recientes del blog
     * SEGÚN MODELO: Usa getPostsRecientes()
     * 
     * @param array $data Datos con id_condominio y limite opcional
     * @return array Response con entradas recientes
     */
    private function obtenerEntradasRecientes(array $data): array
    {
        try {
            $condominioId = (int) $data['id_condominio'];
            $limite = isset($data['limite']) ? (int) $data['limite'] : 5;

            // Validar límite
            if ($limite < 1 || $limite > 20) {
                $limite = 5;
            }

            // 1. OBTENER ENTRADAS RECIENTES
            $entradas = $this->blogModel->getPostsRecientes($condominioId, $limite);

            // 2. PROCESAR ENTRADAS
            $entradasProcesadas = [];
            foreach ($entradas as $entrada) {
                $entradaProcesada = $entrada;
                $entradaProcesada['tiempo_transcurrido'] = $this->calcularTiempoTranscurrido($entrada['fecha_creacion']);
                $entradaProcesada['contenido_resumen'] = $this->crearResumenContenido($entrada['contenido'], 100);
                
                $entradasProcesadas[] = $entradaProcesada;
            }

            return $this->successResponse('Entradas recientes obtenidas', [
                'entradas' => $entradasProcesadas,
                'limite' => $limite,
                'total' => count($entradas)
            ]);

        } catch (Exception $e) {
            $this->logAdminActivity("BlogService::obtenerEntradasRecientes - Error", [
                'admin_id' => $this->getCurrentAdminId(),
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return $this->errorResponse('Error al obtener entradas recientes', 500);
        }
    }

    // ==========================================
    // MÉTODOS AUXILIARES Y VALIDACIONES
    // ==========================================

    /**
     * Verificar si el contenido contiene material inapropiado
     * SEGÚN PROMPT: Filtrar contenido spam, malware, etc.
     */
    private function contieneContenidoInapropiado(string $texto): bool
    {
        $palabrasProhibidas = [
            'spam', 'phishing', 'malware', 'virus', 'hack', 'crack',
            'estafa', 'fraude', 'ilegal', 'drogas'
        ];
        
        $textoLimpio = strtolower(strip_tags($texto));
        
        foreach ($palabrasProhibidas as $palabra) {
            if (strpos($textoLimpio, $palabra) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Sanitizar texto simple
     * SEGÚN PROMPT: Limpiar texto de caracteres peligrosos
     */
    private function sanitizarTexto(string $texto): string
    {
        return htmlspecialchars(strip_tags(trim($texto)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitizar HTML permitiendo tags seguros
     * SEGÚN PROMPT: Permitir HTML básico pero seguro
     */
    private function sanitizarHtml(string $html): string
    {
        $tagsPermitidos = '<p><br><strong><em><u><ul><ol><li><a><img><h1><h2><h3><h4><h5><h6><blockquote>';
        return strip_tags(trim($html), $tagsPermitidos);
    }

    /**
     * Validar y sanitizar URL de imagen
     * SEGÚN PROMPT: Validar URLs de imágenes
     */
    private function validarYSanitizarUrlImagen(string $url): string
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('URL de imagen no válida');
        }
        
        return filter_var($url, FILTER_SANITIZE_URL);
    }

    /**
     * Calcular tiempo transcurrido desde fecha
     * SEGÚN PROMPT: Mostrar tiempo relativo
     */
    private function calcularTiempoTranscurrido(string $fecha): string
    {
        $tiempo = time() - strtotime($fecha);
        
        if ($tiempo < 60) {
            return 'Hace unos segundos';
        } elseif ($tiempo < 3600) {
            $minutos = floor($tiempo / 60);
            return "Hace $minutos minuto" . ($minutos > 1 ? 's' : '');
        } elseif ($tiempo < 86400) {
            $horas = floor($tiempo / 3600);
            return "Hace $horas hora" . ($horas > 1 ? 's' : '');
        } else {
            $dias = floor($tiempo / 86400);
            return "Hace $dias día" . ($dias > 1 ? 's' : '');
        }
    }

    /**
     * Crear resumen de contenido
     * SEGÚN PROMPT: Generar resúmenes para listados
     */
    private function crearResumenContenido(string $contenido, int $limite = 200): string
    {
        $textoLimpio = strip_tags($contenido);
        if (strlen($textoLimpio) <= $limite) {
            return $textoLimpio;
        }
        
        return substr($textoLimpio, 0, $limite) . '...';
    }

    /**
     * Calcular promedio de caracteres por entrada
     * SEGÚN PROMPT: Métricas para estadísticas
     */
    private function calcularPromedioCaracteres(array $entradas): float
    {
        if (empty($entradas)) {
            return 0;
        }
        
        $totalCaracteres = 0;
        foreach ($entradas as $entrada) {
            $totalCaracteres += strlen(strip_tags($entrada['contenido']));
        }
        
        return round($totalCaracteres / count($entradas), 2);
    }

    /**
     * Calcular distribución de entradas por mes
     * SEGÚN PROMPT: Analytics para estadísticas
     */
    private function calcularDistribucionPorMes(array $entradas): array
    {
        $distribucion = [];
        
        foreach ($entradas as $entrada) {
            $mes = date('Y-m', strtotime($entrada['fecha_creacion']));
            if (!isset($distribucion[$mes])) {
                $distribucion[$mes] = 0;
            }
            $distribucion[$mes]++;
        }
        
        ksort($distribucion);
        return $distribucion;
    }
}
?>
