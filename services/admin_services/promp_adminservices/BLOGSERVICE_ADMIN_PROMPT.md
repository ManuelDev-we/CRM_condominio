# BLOGSERVICE_ADMIN_PROMPT.md
## Prompt Especializado para BlogService.php

### 🎯 PROPÓSITO DEL SERVICIO
Administrar el sistema de blog/noticias dentro de cada condominio. Gestiona creación, edición, moderación y publicación de entradas de blog, noticias y anuncios específicos para cada comunidad, con capacidades de moderación y gestión editorial.

---

## 🏗️ ARQUITECTURA Y HERENCIA

### Clase Base
```php
class BlogService extends BaseAdminService
```

### Dependencias
- **Hereda de:** `BaseAdminService.php`
- **Modelos principales:** `Blog.php`, `Persona.php`
- **Posición en cascada:** Nivel 2 (Segunda Capa - Contenido Editorial)
- **Servicios relacionados:** AdminService, PersonaService
- **Requiere validaciones de:** CondominioService

---

## 📚 MÉTODOS DEL MODELO BLOG DISPONIBLES

### Métodos de Gestión de Entradas
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `createBlogPost()` | array $data | int | Crear entrada de blog |
| `findBlogPostById()` | int $id | array | Buscar entrada por ID |
| `findBlogPostsByCondominio()` | int $condominioId | array | Buscar entradas por condominio |
| `findBlogPostsByAuthor()` | int $authorId | array | Buscar entradas por autor |
| `findBlogPostsByCategory()` | string $categoria | array | Buscar entradas por categoría |
| `findBlogPostsByStatus()` | string $estado | array | Buscar entradas por estado |
| `updateBlogPost()` | int $id, array $data | bool | Actualizar entrada |
| `deleteBlogPost()` | int $id | bool | Eliminar entrada |

### Métodos de Estados de Publicación
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `publishBlogPost()` | int $id | bool | Publicar entrada |
| `unpublishBlogPost()` | int $id | bool | Despublicar entrada |
| `approveBlogPost()` | int $id | bool | Aprobar entrada |
| `rejectBlogPost()` | int $id, string $razon | bool | Rechazar entrada |
| `scheduleBlogPost()` | int $id, datetime $fecha | bool | Programar publicación |
| `setBlogPostPriority()` | int $id, int $prioridad | bool | Establecer prioridad |
| `pinBlogPost()` | int $id | bool | Fijar entrada |
| `unpinBlogPost()` | int $id | bool | Desfijar entrada |

### Métodos de Categorías y Tags
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `createBlogCategory()` | array $data | int | Crear categoría |
| `findBlogCategoriesByCondominio()` | int $condominioId | array | Buscar categorías por condominio |
| `updateBlogCategory()` | int $id, array $data | bool | Actualizar categoría |
| `deleteBlogCategory()` | int $id | bool | Eliminar categoría |
| `addTagsToBlogPost()` | int $postId, array $tags | bool | Agregar tags a entrada |
| `removeTagsFromBlogPost()` | int $postId, array $tags | bool | Remover tags de entrada |
| `findBlogPostsByTags()` | array $tags | array | Buscar entradas por tags |

### Métodos de Comentarios y Interacción
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `getCommentsForBlogPost()` | int $postId | array | Obtener comentarios |
| `approveComment()` | int $commentId | bool | Aprobar comentario |
| `rejectComment()` | int $commentId | bool | Rechazar comentario |
| `deleteComment()` | int $commentId | bool | Eliminar comentario |
| `moderateComments()` | array $commentIds, string $accion | bool | Moderar comentarios |
| `setBlogPostCommentsEnabled()` | int $postId, bool $enabled | bool | Habilitar/deshabilitar comentarios |

### Métodos de Moderación
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `flagContentInappropriate()` | int $postId, string $razon | bool | Marcar contenido inapropiado |
| `unflagContent()` | int $postId | bool | Desmarcar contenido |
| `setBlogPostVisibility()` | int $postId, string $visibilidad | bool | Establecer visibilidad |
| `reportBlogPost()` | int $postId, array $data | int | Reportar entrada |
| `getReportedContent()` | int $condominioId | array | Obtener contenido reportado |

### Métodos de Configuración Editorial
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `setBlogSettings()` | int $condominioId, array $settings | bool | Configurar blog |
| `getBlogSettings()` | int $condominioId | array | Obtener configuración |
| `setEditorialPolicy()` | int $condominioId, array $policy | bool | Establecer política editorial |
| `setModerationRules()` | int $condominioId, array $rules | bool | Establecer reglas de moderación |
| `addEditor()` | int $condominioId, int $personaId | bool | Agregar editor |
| `removeEditor()` | int $condominioId, int $personaId | bool | Remover editor |

### Métodos de Estadísticas y Analytics
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `getBlogAnalytics()` | int $condominioId, array $periodo | array | Obtener analytics |
| `getPostViews()` | int $postId | int | Obtener vistas de entrada |
| `getPopularPosts()` | int $condominioId, int $limit | array | Obtener entradas populares |
| `getEngagementStats()` | int $condominioId | array | Estadísticas de engagement |
| `getContentPerformance()` | int $condominioId, array $periodo | array | Rendimiento de contenido |

### Métodos de Notificaciones
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `notifyNewPost()` | int $postId | bool | Notificar nueva entrada |
| `notifyPostApproved()` | int $postId | bool | Notificar entrada aprobada |
| `notifyPostRejected()` | int $postId, string $razon | bool | Notificar entrada rechazada |
| `sendNewsletterDigest()` | int $condominioId | bool | Enviar resumen de noticias |

### Métodos de Validación
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `validateCondominioExists()` | int $condominioId | bool | Valida existencia de condominio |
| `validatePersonaExists()` | int $personaId | bool | Valida existencia de persona |
| `validateBlogPostExists()` | int $postId | bool | Valida existencia de entrada |
| `validateCategoryExists()` | int $categoryId | bool | Valida existencia de categoría |
| `validateEditorialPermissions()` | int $personaId, int $condominioId | bool | Valida permisos editoriales |

### Métodos Base Heredados
| Método | Entrada | Salida | Descripción |
|--------|---------|--------|-------------|
| `create()` | array $data | int | Crear registro |
| `findById()` | int $id | array | Buscar por ID |
| `update()` | int $id, array $data | bool | Actualizar registro |
| `delete()` | int $id | bool | Eliminar registro |
| `findAll()` | int $limit = 100 | array | Obtener todos los registros |

---

## 🔧 FUNCIONES DE NEGOCIO REQUERIDAS

### 1. **Crear Entrada de Blog**
```php
public function crearEntradaBlog($adminId, $condominioId, $datos)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para crear entradas en este blog');
    }
    
    // Validar campos requeridos
    $this->validateRequiredFields($datos, ['titulo', 'contenido', 'categoria_id']);
    
    // Validar longitud del contenido
    if (strlen($datos['contenido']) < 50) {
        return $this->errorResponse('El contenido debe tener al menos 50 caracteres');
    }
    
    if (strlen($datos['titulo']) > 200) {
        return $this->errorResponse('El título no puede exceder 200 caracteres');
    }
    
    // Validar que la categoría existe
    if (!$this->blogModel->validateCategoryExists($datos['categoria_id'])) {
        return $this->errorResponse('Categoría no encontrada');
    }
    
    // Filtrar contenido inapropiado
    if ($this->containsInappropriateContent($datos['contenido']) || 
        $this->containsInappropriateContent($datos['titulo'])) {
        return $this->errorResponse('El contenido contiene material inapropiado');
    }
    
    // Preparar datos de la entrada
    $entradaData = [
        'condominio_id' => $condominioId,
        'autor_id' => $adminId,
        'titulo' => $this->sanitizeText($datos['titulo']),
        'contenido' => $this->sanitizeHtml($datos['contenido']),
        'categoria_id' => $datos['categoria_id'],
        'estado' => $datos['estado'] ?? 'borrador',
        'fecha_creacion' => date('Y-m-d H:i:s'),
        'visibilidad' => $datos['visibilidad'] ?? 'publico',
        'comentarios_habilitados' => $datos['comentarios_habilitados'] ?? true,
        'prioridad' => $datos['prioridad'] ?? 1,
        'fijado' => false
    ];
    
    // Agregar metadatos si se proporcionan
    if (isset($datos['resumen'])) {
        $entradaData['resumen'] = $this->sanitizeText($datos['resumen']);
    }
    
    if (isset($datos['imagen_destacada'])) {
        $entradaData['imagen_destacada'] = $this->validateAndSanitizeImageUrl($datos['imagen_destacada']);
    }
    
    if (isset($datos['fecha_publicacion']) && $datos['estado'] == 'programado') {
        $entradaData['fecha_publicacion'] = $datos['fecha_publicacion'];
    }
    
    // Crear entrada
    $postId = $this->blogModel->createBlogPost($entradaData);
    
    // Agregar tags si se proporcionan
    if (isset($datos['tags']) && is_array($datos['tags'])) {
        $this->blogModel->addTagsToBlogPost($postId, $datos['tags']);
    }
    
    // Publicar automáticamente si el estado es publicado
    if ($datos['estado'] == 'publicado') {
        $this->blogModel->publishBlogPost($postId);
        $this->blogModel->notifyNewPost($postId);
    }
    
    // Log de actividad
    $this->logAdminActivity('entrada_blog_creada', [
        'admin_id' => $adminId,
        'condominio_id' => $condominioId,
        'post_id' => $postId,
        'titulo' => $datos['titulo'],
        'categoria_id' => $datos['categoria_id'],
        'estado' => $datos['estado'] ?? 'borrador'
    ]);
    
    return $this->successResponse(['id' => $postId], 'Entrada de blog creada exitosamente');
}
```

### 2. **Moderar Contenido**
```php
public function moderarContenido($adminId, $postId, $accion, $datos = [])
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Obtener entrada
    $entrada = $this->blogModel->findBlogPostById($postId);
    if (!$entrada) {
        return $this->errorResponse('Entrada no encontrada');
    }
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($entrada['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para moderar este contenido');
    }
    
    $resultado = false;
    $mensaje = '';
    
    switch ($accion) {
        case 'aprobar':
            if ($entrada['estado'] != 'pendiente') {
                return $this->errorResponse('Solo se pueden aprobar entradas pendientes');
            }
            
            $resultado = $this->blogModel->approveBlogPost($postId);
            $this->blogModel->publishBlogPost($postId);
            $this->blogModel->notifyPostApproved($postId);
            $mensaje = 'Entrada aprobada y publicada';
            break;
            
        case 'rechazar':
            if ($entrada['estado'] != 'pendiente') {
                return $this->errorResponse('Solo se pueden rechazar entradas pendientes');
            }
            
            $razon = $datos['razon'] ?? 'Contenido no cumple con las políticas editoriales';
            $resultado = $this->blogModel->rejectBlogPost($postId, $razon);
            $this->blogModel->notifyPostRejected($postId, $razon);
            $mensaje = 'Entrada rechazada';
            break;
            
        case 'publicar':
            if ($entrada['estado'] != 'borrador' && $entrada['estado'] != 'aprobado') {
                return $this->errorResponse('La entrada no puede ser publicada');
            }
            
            $resultado = $this->blogModel->publishBlogPost($postId);
            $this->blogModel->notifyNewPost($postId);
            $mensaje = 'Entrada publicada exitosamente';
            break;
            
        case 'despublicar':
            if ($entrada['estado'] != 'publicado') {
                return $this->errorResponse('Solo se pueden despublicar entradas publicadas');
            }
            
            $resultado = $this->blogModel->unpublishBlogPost($postId);
            $mensaje = 'Entrada despublicada';
            break;
            
        case 'fijar':
            $resultado = $this->blogModel->pinBlogPost($postId);
            $mensaje = 'Entrada fijada';
            break;
            
        case 'desfijar':
            $resultado = $this->blogModel->unpinBlogPost($postId);
            $mensaje = 'Entrada desfijada';
            break;
            
        case 'marcar_inapropiado':
            $razon = $datos['razon'] ?? 'Contenido reportado como inapropiado';
            $resultado = $this->blogModel->flagContentInappropriate($postId, $razon);
            $this->blogModel->unpublishBlogPost($postId); // Despublicar automáticamente
            $mensaje = 'Contenido marcado como inapropiado y despublicado';
            break;
            
        case 'desmarcar_inapropiado':
            $resultado = $this->blogModel->unflagContent($postId);
            $mensaje = 'Marcado de contenido inapropiado removido';
            break;
            
        case 'programar':
            if (!isset($datos['fecha_publicacion'])) {
                return $this->errorResponse('Se requiere fecha de publicación');
            }
            
            $fechaPublicacion = $datos['fecha_publicacion'];
            if (strtotime($fechaPublicacion) <= time()) {
                return $this->errorResponse('La fecha de publicación debe ser futura');
            }
            
            $resultado = $this->blogModel->scheduleBlogPost($postId, $fechaPublicacion);
            $mensaje = 'Entrada programada para publicación';
            break;
            
        default:
            return $this->errorResponse('Acción de moderación no válida');
    }
    
    // Log de actividad de moderación
    $this->logAdminActivity('contenido_moderado', [
        'admin_id' => $adminId,
        'post_id' => $postId,
        'accion' => $accion,
        'condominio_id' => $entrada['condominio_id'],
        'titulo' => $entrada['titulo'],
        'datos_adicionales' => $datos
    ]);
    
    return $this->successResponse($resultado, $mensaje);
}
```

### 3. **Gestionar Comentarios**
```php
public function gestionarComentarios($adminId, $postId, $accion, $datos = [])
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Obtener entrada
    $entrada = $this->blogModel->findBlogPostById($postId);
    if (!$entrada) {
        return $this->errorResponse('Entrada no encontrada');
    }
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($entrada['condominio_id'], $adminId)) {
        return $this->errorResponse('No tienes permisos para gestionar comentarios en esta entrada');
    }
    
    $resultado = false;
    $mensaje = '';
    
    switch ($accion) {
        case 'obtener_comentarios':
            $comentarios = $this->blogModel->getCommentsForBlogPost($postId);
            
            // Agregar información del autor de cada comentario
            foreach ($comentarios as &$comentario) {
                $comentario['autor_info'] = $this->personaService->obtenerPersonaBasica($comentario['autor_id']);
                $comentario['tiempo_transcurrido'] = $this->calculateTimeAgo($comentario['fecha_creacion']);
            }
            
            return $this->successResponse($comentarios, 'Comentarios obtenidos exitosamente');
            
        case 'aprobar_comentario':
            if (!isset($datos['comment_id'])) {
                return $this->errorResponse('ID de comentario requerido');
            }
            
            $resultado = $this->blogModel->approveComment($datos['comment_id']);
            $mensaje = 'Comentario aprobado';
            break;
            
        case 'rechazar_comentario':
            if (!isset($datos['comment_id'])) {
                return $this->errorResponse('ID de comentario requerido');
            }
            
            $resultado = $this->blogModel->rejectComment($datos['comment_id']);
            $mensaje = 'Comentario rechazado';
            break;
            
        case 'eliminar_comentario':
            if (!isset($datos['comment_id'])) {
                return $this->errorResponse('ID de comentario requerido');
            }
            
            $resultado = $this->blogModel->deleteComment($datos['comment_id']);
            $mensaje = 'Comentario eliminado';
            break;
            
        case 'moderar_multiple':
            if (!isset($datos['comment_ids']) || !isset($datos['accion_moderacion'])) {
                return $this->errorResponse('IDs de comentarios y acción requeridos');
            }
            
            $resultado = $this->blogModel->moderateComments($datos['comment_ids'], $datos['accion_moderacion']);
            $mensaje = 'Comentarios moderados exitosamente';
            break;
            
        case 'habilitar_comentarios':
            $resultado = $this->blogModel->setBlogPostCommentsEnabled($postId, true);
            $mensaje = 'Comentarios habilitados para esta entrada';
            break;
            
        case 'deshabilitar_comentarios':
            $resultado = $this->blogModel->setBlogPostCommentsEnabled($postId, false);
            $mensaje = 'Comentarios deshabilitados para esta entrada';
            break;
            
        default:
            return $this->errorResponse('Acción no válida para gestión de comentarios');
    }
    
    // Log de actividad
    $this->logAdminActivity('comentarios_gestionados', [
        'admin_id' => $adminId,
        'post_id' => $postId,
        'accion' => $accion,
        'datos' => $datos
    ]);
    
    return $this->successResponse($resultado, $mensaje);
}
```

### 4. **Configurar Blog del Condominio**
```php
public function configurarBlog($adminId, $condominioId, $configuracion)
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para configurar el blog de este condominio');
    }
    
    // Configuraciones válidas
    $configuracionesValidas = [
        'titulo_blog', 'descripcion_blog', 'posts_por_pagina', 'moderacion_automatica',
        'comentarios_por_defecto', 'notificaciones_nuevos_posts', 'politica_editorial',
        'reglas_moderacion', 'categorias_permitidas', 'tags_permitidos',
        'formato_fecha', 'zona_horaria', 'idioma_blog'
    ];
    
    // Validar configuraciones
    foreach ($configuracion as $clave => $valor) {
        if (!in_array($clave, $configuracionesValidas)) {
            return $this->errorResponse("Configuración '$clave' no válida");
        }
    }
    
    // Validaciones específicas
    if (isset($configuracion['posts_por_pagina'])) {
        if (!is_numeric($configuracion['posts_por_pagina']) || $configuracion['posts_por_pagina'] < 1 || $configuracion['posts_por_pagina'] > 50) {
            return $this->errorResponse('Posts por página debe ser un número entre 1 y 50');
        }
    }
    
    if (isset($configuracion['titulo_blog'])) {
        if (strlen($configuracion['titulo_blog']) > 100) {
            return $this->errorResponse('El título del blog no puede exceder 100 caracteres');
        }
    }
    
    // Establecer configuración del blog
    $resultado = $this->blogModel->setBlogSettings($condominioId, $configuracion);
    
    // Establecer política editorial si se proporciona
    if (isset($configuracion['politica_editorial'])) {
        $this->blogModel->setEditorialPolicy($condominioId, $configuracion['politica_editorial']);
    }
    
    // Establecer reglas de moderación si se proporcionan
    if (isset($configuracion['reglas_moderacion'])) {
        $this->blogModel->setModerationRules($condominioId, $configuracion['reglas_moderacion']);
    }
    
    // Log de actividad
    $this->logAdminActivity('blog_configurado', [
        'admin_id' => $adminId,
        'condominio_id' => $condominioId,
        'configuraciones' => array_keys($configuracion)
    ]);
    
    return $this->successResponse($resultado, 'Configuración del blog actualizada exitosamente');
}
```

### 5. **Obtener Analytics del Blog**
```php
public function obtenerAnalyticsBlog($adminId, $condominioId, $periodo = [])
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para ver analytics de este blog');
    }
    
    // Aplicar rate limiting
    $this->enforceRateLimit('analytics_blog_' . $adminId);
    
    // Período por defecto: último mes
    if (empty($periodo)) {
        $periodo = [
            'desde' => date('Y-m-d', strtotime('-30 days')),
            'hasta' => date('Y-m-d')
        ];
    }
    
    // Obtener analytics generales
    $analytics = $this->blogModel->getBlogAnalytics($condominioId, $periodo);
    
    // Estadísticas adicionales
    $analytics['entradas_populares'] = $this->blogModel->getPopularPosts($condominioId, 10);
    $analytics['estadisticas_engagement'] = $this->blogModel->getEngagementStats($condominioId);
    $analytics['rendimiento_contenido'] = $this->blogModel->getContentPerformance($condominioId, $periodo);
    
    // Métricas calculadas
    $analytics['metricas_calculadas'] = [
        'promedio_vistas_por_entrada' => $this->calcularPromedioVistasPorEntrada($condominioId, $periodo),
        'tasa_engagement' => $this->calcularTasaEngagement($condominioId, $periodo),
        'crecimiento_audiencia' => $this->calcularCrecimientoAudiencia($condominioId, $periodo),
        'categorias_mas_populares' => $this->obtenerCategoriasMasPopulares($condominioId, $periodo),
        'dias_mas_activos' => $this->obtenerDiasMasActivos($condominioId, $periodo)
    ];
    
    // Comparación con período anterior
    $periodoAnterior = [
        'desde' => date('Y-m-d', strtotime($periodo['desde'] . ' -30 days')),
        'hasta' => date('Y-m-d', strtotime($periodo['hasta'] . ' -30 days'))
    ];
    
    $analyticsAnterior = $this->blogModel->getBlogAnalytics($condominioId, $periodoAnterior);
    $analytics['comparacion_periodo_anterior'] = $this->compararAnalytics($analytics, $analyticsAnterior);
    
    // Recomendaciones automáticas
    $analytics['recomendaciones'] = $this->generarRecomendaciones($analytics);
    
    return $this->successResponse($analytics, 'Analytics del blog obtenidos exitosamente');
}
```

### 6. **Gestionar Categorías y Editores**
```php
public function gestionarEditores($adminId, $condominioId, $accion, $datos = [])
{
    // Validar autenticación
    $this->checkAdminAuth();
    
    // Validar CSRF
    $this->checkCSRF('POST');
    
    // Validar ownership del condominio
    if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
        return $this->errorResponse('No tienes permisos para gestionar editores en este blog');
    }
    
    $resultado = false;
    $mensaje = '';
    
    switch ($accion) {
        case 'agregar_editor':
            $this->validateRequiredFields($datos, ['persona_id']);
            
            // Validar que la persona existe
            if (!$this->blogModel->validatePersonaExists($datos['persona_id'])) {
                return $this->errorResponse('Persona no encontrada');
            }
            
            // Verificar que la persona pertenece al condominio
            if (!$this->personaService->personaPerteneceACondominio($datos['persona_id'], $condominioId)) {
                return $this->errorResponse('La persona debe pertenecer al condominio');
            }
            
            $resultado = $this->blogModel->addEditor($condominioId, $datos['persona_id']);
            $mensaje = 'Editor agregado exitosamente';
            break;
            
        case 'remover_editor':
            $this->validateRequiredFields($datos, ['persona_id']);
            
            $resultado = $this->blogModel->removeEditor($condominioId, $datos['persona_id']);
            $mensaje = 'Editor removido exitosamente';
            break;
            
        case 'crear_categoria':
            $this->validateRequiredFields($datos, ['nombre', 'descripcion']);
            
            $categoriaData = [
                'condominio_id' => $condominioId,
                'nombre' => $this->sanitizeText($datos['nombre']),
                'descripcion' => $this->sanitizeText($datos['descripcion']),
                'color' => $datos['color'] ?? '#007bff',
                'icono' => $datos['icono'] ?? 'default',
                'activa' => true,
                'fecha_creacion' => date('Y-m-d H:i:s')
            ];
            
            $categoriaId = $this->blogModel->createBlogCategory($categoriaData);
            $resultado = ['categoria_id' => $categoriaId];
            $mensaje = 'Categoría creada exitosamente';
            break;
            
        case 'actualizar_categoria':
            $this->validateRequiredFields($datos, ['categoria_id']);
            
            $resultado = $this->blogModel->updateBlogCategory($datos['categoria_id'], $datos);
            $mensaje = 'Categoría actualizada exitosamente';
            break;
            
        case 'eliminar_categoria':
            $this->validateRequiredFields($datos, ['categoria_id']);
            
            // Verificar que no hay entradas usando esta categoría
            $entradas = $this->blogModel->findBlogPostsByCategory($datos['categoria_id']);
            if (count($entradas) > 0) {
                return $this->errorResponse('No se puede eliminar una categoría que tiene entradas asociadas');
            }
            
            $resultado = $this->blogModel->deleteBlogCategory($datos['categoria_id']);
            $mensaje = 'Categoría eliminada exitosamente';
            break;
            
        default:
            return $this->errorResponse('Acción no válida para gestión de editores/categorías');
    }
    
    // Log de actividad
    $this->logAdminActivity('editores_categorias_gestionados', [
        'admin_id' => $adminId,
        'condominio_id' => $condominioId,
        'accion' => $accion,
        'datos' => $datos
    ]);
    
    return $this->successResponse($resultado, $mensaje);
}
```

---

## 🔒 VALIDACIONES DE SEGURIDAD REQUERIDAS

### Middleware Embebido
```php
private function checkAdminAuth()
{
    if (!$this->authAdmin()) {
        throw new UnauthorizedException('Acceso no autorizado');
    }
}
```

### Validaciones Específicas
```php
private function containsInappropriateContent($texto)
{
    $palabrasProhibidas = ['spam', 'phishing', 'malware', 'virus'];
    $textoLower = strtolower($texto);
    
    foreach ($palabrasProhibidas as $palabra) {
        if (strpos($textoLower, $palabra) !== false) {
            return true;
        }
    }
    
    return false;
}

private function sanitizeText($texto)
{
    return htmlspecialchars(strip_tags($texto), ENT_QUOTES, 'UTF-8');
}

private function sanitizeHtml($html)
{
    // Permitir solo tags HTML seguros
    $tagsPermitidos = '<p><br><strong><em><u><ul><ol><li><a><img><h1><h2><h3><h4><h5><h6>';
    return strip_tags($html, $tagsPermitidos);
}

private function validateAndSanitizeImageUrl($url)
{
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        throw new InvalidArgumentException('URL de imagen no válida');
    }
    
    return $url;
}
```

---

## 🔄 INTEGRACIÓN CON OTROS SERVICIOS

### Debe usar servicios en cascada:
```php
// Validaciones de otros servicios
if (!$this->condominioService->validarOwnership($condominioId, $adminId)) {
    return $this->errorResponse("No tienes permisos");
}

// Verificar personas en el condominio
if (!$this->personaService->personaPerteneceACondominio($personaId, $condominioId)) {
    return $this->errorResponse("Persona no pertenece al condominio");
}
```

### Proporciona para otros servicios:
```php
// Para otros servicios que necesiten información del blog
public function obtenerEntradasRecientes($condominioId, $limite = 5);
public function obtenerNoticiasDestacadas($condominioId);
public function verificarPermisoEdicion($personaId, $condominioId);
```

---

## 🚫 RESTRICCIONES IMPORTANTES

### Lo que NO debe hacer:
- ❌ **NO gestionar usuarios directamente** (usar PersonaService)
- ❌ **NO manejar condominios** (usar CondominioService)
- ❌ **NO gestionar accesos físicos** (usar AccesosService)

### Scope específico:
- ✅ **CRUD de entradas de blog/noticias**
- ✅ **Moderación de contenido y comentarios**
- ✅ **Gestión editorial (editores, categorías)**
- ✅ **Analytics y estadísticas del blog**
- ✅ **Configuración del blog por condominio**
- ✅ **Notificaciones de contenido**

---

## 📋 ESTRUCTURA DE RESPUESTAS

### Éxito
```php
return $this->successResponse([
    'blog' => $blogData,
    'mensaje' => 'Blog gestionado exitosamente'
]);
```

### Error de Moderación
```php
return $this->errorResponse(
    'Contenido contiene material inapropiado',
    400
);
```

---

## 🔍 LOGGING REQUERIDO

### Actividades a registrar:
- ✅ Creación/modificación de entradas
- ✅ Acciones de moderación
- ✅ Gestión de comentarios
- ✅ Configuración del blog
- ✅ Gestión de editores y categorías

---

## 📅 INFORMACIÓN DEL PROMPT
- **Fecha de creación:** 28 de Julio, 2025
- **Servicio:** BlogService.php
- **Posición en cascada:** Nivel 2 (Segunda Capa - Contenido Editorial)
- **Estado:** ✅ Listo para implementación

---

## 🎯 INSTRUCCIONES PARA COPILOT

Al generar código para BlogService.php:

1. **SIEMPRE heredar de BaseAdminService**
2. **USAR métodos de Blog.php y Persona.php**
3. **VALIDAR ownership del condominio en TODAS las operaciones**
4. **IMPLEMENTAR moderación de contenido robusta**
5. **GESTIONAR estados de publicación apropiadamente**
6. **SANITIZAR contenido HTML y texto**
7. **PROPORCIONAR analytics detallados**
8. **MANEJAR notificaciones de contenido**
9. **REGISTRAR logs de todas las actividades editoriales**
