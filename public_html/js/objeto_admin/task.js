/**
 * Sistema de Gestión de Tareas para Administradores
 * Incluye funcionalidades de:
 * - Gestión completa de tareas (CRUD)
 * - Compresión y descompresión de fotos
 * - Asignación de trabajadores a tareas
 * - Exportación y estadísticas
 */

class TaskManager {
    constructor() {
        this.tasks = this.loadTasks();
        this.compressionRatio = 0.7; // Calidad de compresión por defecto
    }

    // Cargar tareas desde localStorage
    loadTasks() {
        const data = localStorage.getItem('condominioTasks');
        return data ? JSON.parse(data) : [];
    }

    // Guardar tareas en localStorage
    saveTasks() {
        localStorage.setItem('condominioTasks', JSON.stringify(this.tasks));
    }

    // Generar ID único para tareas
    generateTaskId() {
        return 'TASK-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
    }

    // ===== GESTIÓN DE TAREAS =====

    // Crear nueva tarea
    createTask(taskData) {
        try {
            // Validar datos requeridos
            const requiredFields = ['titulo', 'descripcion', 'prioridad', 'fechaLimite'];
            for (let field of requiredFields) {
                if (!taskData[field]) {
                    throw new Error(`El campo ${field} es requerido`);
                }
            }

            const newTask = {
                id: this.generateTaskId(),
                titulo: taskData.titulo.trim(),
                descripcion: taskData.descripcion.trim(),
                prioridad: taskData.prioridad, // alta, media, baja
                estado: taskData.estado || 'pendiente', // pendiente, en_progreso, completada, cancelada
                fechaCreacion: new Date().toISOString(),
                fechaLimite: taskData.fechaLimite,
                fechaCompletada: null,
                trabajadoresAsignados: taskData.trabajadoresAsignados || [],
                fotos: taskData.fotos || [],
                fotosComprimidas: {},
                ubicacion: {
                    condominio: taskData.condominio || '',
                    calle: taskData.calle || '',
                    casa: taskData.casa || ''
                },
                createdBy: taskData.createdBy || 'admin',
                notas: taskData.notas || '',
                categoria: taskData.categoria || 'general'
            };

            this.tasks.push(newTask);
            this.saveTasks();

            return {
                success: true,
                message: 'Tarea creada exitosamente',
                taskId: newTask.id,
                task: newTask
            };

        } catch (error) {
            return {
                success: false,
                message: error.message
            };
        }
    }

    // Obtener todas las tareas
    getAllTasks() {
        return this.tasks;
    }

    // Obtener tarea por ID
    getTaskById(taskId) {
        return this.tasks.find(task => task.id === taskId);
    }

    // Actualizar tarea
    updateTask(taskId, updateData) {
        try {
            const taskIndex = this.tasks.findIndex(task => task.id === taskId);
            if (taskIndex === -1) {
                throw new Error('Tarea no encontrada');
            }

            // Actualizar campos permitidos
            const allowedFields = ['titulo', 'descripcion', 'prioridad', 'estado', 'fechaLimite', 'ubicacion', 'notas', 'categoria'];
            allowedFields.forEach(field => {
                if (updateData[field] !== undefined) {
                    this.tasks[taskIndex][field] = updateData[field];
                }
            });

            // Si se marca como completada, agregar fecha
            if (updateData.estado === 'completada' && !this.tasks[taskIndex].fechaCompletada) {
                this.tasks[taskIndex].fechaCompletada = new Date().toISOString();
            }

            this.saveTasks();

            return {
                success: true,
                message: 'Tarea actualizada exitosamente',
                task: this.tasks[taskIndex]
            };

        } catch (error) {
            return {
                success: false,
                message: error.message
            };
        }
    }

    // Eliminar tarea
    deleteTask(taskId) {
        try {
            const taskIndex = this.tasks.findIndex(task => task.id === taskId);
            if (taskIndex === -1) {
                throw new Error('Tarea no encontrada');
            }

            const deletedTask = this.tasks[taskIndex];
            this.tasks.splice(taskIndex, 1);
            this.saveTasks();

            return {
                success: true,
                message: 'Tarea eliminada exitosamente',
                task: deletedTask
            };

        } catch (error) {
            return {
                success: false,
                message: error.message
            };
        }
    }

    // ===== GESTIÓN DE TRABAJADORES EN TAREAS =====

    // Asignar trabajador a tarea
    assignWorkerToTask(taskId, workerId, workerName = null) {
        try {
            const task = this.getTaskById(taskId);
            if (!task) {
                throw new Error('Tarea no encontrada');
            }

            // Verificar si el trabajador ya está asignado
            if (task.trabajadoresAsignados.some(w => w.id === workerId)) {
                throw new Error('El trabajador ya está asignado a esta tarea');
            }

            const workerAssignment = {
                id: workerId,
                name: workerName || `Trabajador ${workerId}`,
                fechaAsignacion: new Date().toISOString(),
                estado: 'asignado' // asignado, trabajando, completado
            };

            task.trabajadoresAsignados.push(workerAssignment);
            this.saveTasks();

            return {
                success: true,
                message: 'Trabajador asignado exitosamente',
                assignment: workerAssignment
            };

        } catch (error) {
            return {
                success: false,
                message: error.message
            };
        }
    }

    // Remover trabajador de tarea
    removeWorkerFromTask(taskId, workerId) {
        try {
            const task = this.getTaskById(taskId);
            if (!task) {
                throw new Error('Tarea no encontrada');
            }

            const workerIndex = task.trabajadoresAsignados.findIndex(w => w.id === workerId);
            if (workerIndex === -1) {
                throw new Error('Trabajador no encontrado en esta tarea');
            }

            const removedWorker = task.trabajadoresAsignados[workerIndex];
            task.trabajadoresAsignados.splice(workerIndex, 1);
            this.saveTasks();

            return {
                success: true,
                message: 'Trabajador removido de la tarea',
                worker: removedWorker
            };

        } catch (error) {
            return {
                success: false,
                message: error.message
            };
        }
    }

    // Actualizar estado de trabajador en tarea
    updateWorkerStatus(taskId, workerId, newStatus) {
        try {
            const task = this.getTaskById(taskId);
            if (!task) {
                throw new Error('Tarea no encontrada');
            }

            const worker = task.trabajadoresAsignados.find(w => w.id === workerId);
            if (!worker) {
                throw new Error('Trabajador no encontrado en esta tarea');
            }

            worker.estado = newStatus;
            worker.fechaActualizacion = new Date().toISOString();
            this.saveTasks();

            return {
                success: true,
                message: 'Estado del trabajador actualizado',
                worker: worker
            };

        } catch (error) {
            return {
                success: false,
                message: error.message
            };
        }
    }

    // ===== GESTIÓN DE FOTOS =====

    // Agregar foto a tarea
    addPhotoToTask(taskId, photoFile, description = '') {
        try {
            const task = this.getTaskById(taskId);
            if (!task) {
                throw new Error('Tarea no encontrada');
            }

            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                
                reader.onload = (e) => {
                    const photoData = {
                        id: 'PHOTO-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9),
                        originalName: photoFile.name,
                        size: photoFile.size,
                        type: photoFile.type,
                        data: e.target.result,
                        description: description,
                        fechaSubida: new Date().toISOString(),
                        comprimida: false
                    };

                    task.fotos.push(photoData);
                    this.saveTasks();

                    resolve({
                        success: true,
                        message: 'Foto agregada exitosamente',
                        photo: photoData
                    });
                };

                reader.onerror = () => {
                    reject({
                        success: false,
                        message: 'Error al leer el archivo de foto'
                    });
                };

                reader.readAsDataURL(photoFile);
            });

        } catch (error) {
            return Promise.resolve({
                success: false,
                message: error.message
            });
        }
    }

    // Comprimir foto específica
    compressPhoto(taskId, photoId, quality = null) {
        try {
            const task = this.getTaskById(taskId);
            if (!task) {
                throw new Error('Tarea no encontrada');
            }

            const photo = task.fotos.find(p => p.id === photoId);
            if (!photo) {
                throw new Error('Foto no encontrada');
            }

            if (photo.comprimida) {
                throw new Error('La foto ya está comprimida');
            }

            return new Promise((resolve, reject) => {
                const img = new Image();
                
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    
                    // Calcular nuevas dimensiones manteniendo proporción
                    const maxWidth = 1200;
                    const maxHeight = 1200;
                    let { width, height } = img;
                    
                    if (width > height) {
                        if (width > maxWidth) {
                            height = (height * maxWidth) / width;
                            width = maxWidth;
                        }
                    } else {
                        if (height > maxHeight) {
                            width = (width * maxHeight) / height;
                            height = maxHeight;
                        }
                    }
                    
                    canvas.width = width;
                    canvas.height = height;
                    
                    // Dibujar imagen redimensionada
                    ctx.drawImage(img, 0, 0, width, height);
                    
                    // Comprimir con calidad especificada
                    const compressionQuality = quality || this.compressionRatio;
                    const compressedData = canvas.toDataURL(photo.type, compressionQuality);
                    
                    // Guardar datos originales para descompresión
                    if (!task.fotosComprimidas[photoId]) {
                        task.fotosComprimidas[photoId] = {
                            originalData: photo.data,
                            originalSize: photo.size,
                            fechaCompresion: new Date().toISOString()
                        };
                    }
                    
                    // Actualizar foto con datos comprimidos
                    photo.data = compressedData;
                    photo.comprimida = true;
                    photo.tamanoComprimido = Math.round(compressedData.length * 0.75); // Estimación del tamaño
                    photo.fechaCompresion = new Date().toISOString();
                    
                    this.saveTasks();
                    
                    resolve({
                        success: true,
                        message: 'Foto comprimida exitosamente',
                        photo: photo,
                        compressionRatio: Math.round((1 - photo.tamanoComprimido / photo.size) * 100)
                    });
                };
                
                img.onerror = () => {
                    reject({
                        success: false,
                        message: 'Error al procesar la imagen'
                    });
                };
                
                img.src = photo.data;
            });

        } catch (error) {
            return Promise.resolve({
                success: false,
                message: error.message
            });
        }
    }

    // Descomprimir foto específica
    decompressPhoto(taskId, photoId) {
        try {
            const task = this.getTaskById(taskId);
            if (!task) {
                throw new Error('Tarea no encontrada');
            }

            const photo = task.fotos.find(p => p.id === photoId);
            if (!photo) {
                throw new Error('Foto no encontrada');
            }

            if (!photo.comprimida) {
                throw new Error('La foto no está comprimida');
            }

            const originalData = task.fotosComprimidas[photoId];
            if (!originalData) {
                throw new Error('No se encontraron datos originales para descomprimir');
            }

            // Restaurar datos originales
            photo.data = originalData.originalData;
            photo.comprimida = false;
            delete photo.tamanoComprimido;
            delete photo.fechaCompresion;
            
            this.saveTasks();

            return {
                success: true,
                message: 'Foto descomprimida exitosamente',
                photo: photo
            };

        } catch (error) {
            return {
                success: false,
                message: error.message
            };
        }
    }

    // Comprimir todas las fotos de una tarea
    compressAllPhotos(taskId, quality = null) {
        const task = this.getTaskById(taskId);
        if (!task) {
            return {
                success: false,
                message: 'Tarea no encontrada'
            };
        }

        const photosToCompress = task.fotos.filter(photo => !photo.comprimida);
        if (photosToCompress.length === 0) {
            return {
                success: false,
                message: 'No hay fotos sin comprimir en esta tarea'
            };
        }

        // Comprimir fotos secuencialmente
        const compressPromises = photosToCompress.map(photo => 
            this.compressPhoto(taskId, photo.id, quality)
        );

        return Promise.all(compressPromises)
            .then(results => {
                const successful = results.filter(r => r.success).length;
                return {
                    success: true,
                    message: `${successful} de ${photosToCompress.length} fotos comprimidas exitosamente`,
                    results: results
                };
            })
            .catch(error => ({
                success: false,
                message: 'Error al comprimir fotos: ' + error.message
            }));
    }

    // Descomprimir todas las fotos de una tarea
    decompressAllPhotos(taskId) {
        const task = this.getTaskById(taskId);
        if (!task) {
            return {
                success: false,
                message: 'Tarea no encontrada'
            };
        }

        const compressedPhotos = task.fotos.filter(photo => photo.comprimida);
        if (compressedPhotos.length === 0) {
            return {
                success: false,
                message: 'No hay fotos comprimidas en esta tarea'
            };
        }

        const results = compressedPhotos.map(photo => 
            this.decompressPhoto(taskId, photo.id)
        );

        const successful = results.filter(r => r.success).length;
        return {
            success: true,
            message: `${successful} de ${compressedPhotos.length} fotos descomprimidas exitosamente`,
            results: results
        };
    }

    // Eliminar foto de tarea
    removePhotoFromTask(taskId, photoId) {
        try {
            const task = this.getTaskById(taskId);
            if (!task) {
                throw new Error('Tarea no encontrada');
            }

            const photoIndex = task.fotos.findIndex(p => p.id === photoId);
            if (photoIndex === -1) {
                throw new Error('Foto no encontrada');
            }

            const removedPhoto = task.fotos[photoIndex];
            task.fotos.splice(photoIndex, 1);
            
            // Eliminar datos de compresión si existen
            delete task.fotosComprimidas[photoId];
            
            this.saveTasks();

            return {
                success: true,
                message: 'Foto eliminada exitosamente',
                photo: removedPhoto
            };

        } catch (error) {
            return {
                success: false,
                message: error.message
            };
        }
    }

    // ===== FILTROS Y BÚSQUEDAS =====

    // Filtrar tareas por múltiples criterios
    filterTasks(filters = {}) {
        let filteredTasks = [...this.tasks];

        // Filtro por estado
        if (filters.estado) {
            filteredTasks = filteredTasks.filter(task => task.estado === filters.estado);
        }

        // Filtro por prioridad
        if (filters.prioridad) {
            filteredTasks = filteredTasks.filter(task => task.prioridad === filters.prioridad);
        }

        // Filtro por trabajador asignado
        if (filters.trabajadorId) {
            filteredTasks = filteredTasks.filter(task => 
                task.trabajadoresAsignados.some(w => w.id === filters.trabajadorId)
            );
        }

        // Filtro por ubicación (condominio)
        if (filters.condominio) {
            filteredTasks = filteredTasks.filter(task => 
                task.ubicacion.condominio === filters.condominio
            );
        }

        // Filtro por fecha límite (próximas a vencer)
        if (filters.proximasVencer) {
            const now = new Date();
            const daysLimit = filters.proximasVencer; // días
            filteredTasks = filteredTasks.filter(task => {
                const fechaLimite = new Date(task.fechaLimite);
                const diffTime = fechaLimite - now;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                return diffDays <= daysLimit && diffDays >= 0;
            });
        }

        // Filtro por texto (título o descripción)
        if (filters.texto) {
            const searchText = filters.texto.toLowerCase();
            filteredTasks = filteredTasks.filter(task => 
                task.titulo.toLowerCase().includes(searchText) ||
                task.descripcion.toLowerCase().includes(searchText)
            );
        }

        return filteredTasks;
    }

    // Buscar tareas
    searchTasks(searchTerm) {
        return this.filterTasks({ texto: searchTerm });
    }

    // Obtener tareas por trabajador
    getTasksByWorker(workerId) {
        return this.tasks.filter(task => 
            task.trabajadoresAsignados.some(w => w.id === workerId)
        );
    }

    // ===== ESTADÍSTICAS =====

    // Obtener estadísticas generales
    getTaskStatistics() {
        const total = this.tasks.length;
        const byStatus = {
            pendiente: 0,
            en_progreso: 0,
            completada: 0,
            cancelada: 0
        };
        
        const byPriority = {
            alta: 0,
            media: 0,
            baja: 0
        };

        let totalPhotos = 0;
        let compressedPhotos = 0;
        let totalWorkerAssignments = 0;

        this.tasks.forEach(task => {
            byStatus[task.estado]++;
            byPriority[task.prioridad]++;
            totalPhotos += task.fotos.length;
            compressedPhotos += task.fotos.filter(p => p.comprimida).length;
            totalWorkerAssignments += task.trabajadoresAsignados.length;
        });

        return {
            total,
            byStatus,
            byPriority,
            photos: {
                total: totalPhotos,
                compressed: compressedPhotos,
                uncompressed: totalPhotos - compressedPhotos,
                compressionRate: totalPhotos > 0 ? Math.round((compressedPhotos / totalPhotos) * 100) : 0
            },
            workers: {
                totalAssignments: totalWorkerAssignments,
                averagePerTask: total > 0 ? Math.round((totalWorkerAssignments / total) * 100) / 100 : 0
            }
        };
    }

    // ===== EXPORTACIÓN =====

    // Exportar tareas a JSON
    exportTasksToJSON() {
        const dataToExport = {
            tasks: this.tasks,
            exportDate: new Date().toISOString(),
            totalTasks: this.tasks.length,
            statistics: this.getTaskStatistics()
        };

        const blob = new Blob([JSON.stringify(dataToExport, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `tareas_condominio_${new Date().toISOString().split('T')[0]}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);

        return {
            success: true,
            message: 'Tareas exportadas exitosamente'
        };
    }

    // Exportar tareas a CSV
    exportTasksToCSV() {
        if (this.tasks.length === 0) {
            return {
                success: false,
                message: 'No hay tareas para exportar'
            };
        }

        const headers = [
            'ID', 'Título', 'Descripción', 'Prioridad', 'Estado',
            'Fecha Creación', 'Fecha Límite', 'Fecha Completada',
            'Trabajadores Asignados', 'Fotos', 'Condominio', 'Calle', 'Casa', 'Categoría'
        ];

        const csvContent = [headers.join(',')];

        this.tasks.forEach(task => {
            const row = [
                task.id,
                `"${task.titulo}"`,
                `"${task.descripcion}"`,
                task.prioridad,
                task.estado,
                task.fechaCreacion.split('T')[0],
                task.fechaLimite,
                task.fechaCompletada ? task.fechaCompletada.split('T')[0] : '',
                task.trabajadoresAsignados.length,
                task.fotos.length,
                task.ubicacion.condominio || '',
                task.ubicacion.calle || '',
                task.ubicacion.casa || '',
                task.categoria
            ];
            csvContent.push(row.join(','));
        });

        const blob = new Blob([csvContent.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `tareas_condominio_${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);

        return {
            success: true,
            message: 'Tareas exportadas a CSV exitosamente'
        };
    }

    // ===== UTILIDADES =====

    // Limpiar tareas completadas antiguas
    cleanupCompletedTasks(daysOld = 30) {
        const cutoffDate = new Date();
        cutoffDate.setDate(cutoffDate.getDate() - daysOld);

        const initialCount = this.tasks.length;
        this.tasks = this.tasks.filter(task => {
            if (task.estado === 'completada' && task.fechaCompletada) {
                const completedDate = new Date(task.fechaCompletada);
                return completedDate > cutoffDate;
            }
            return true;
        });

        this.saveTasks();
        const removedCount = initialCount - this.tasks.length;

        return {
            success: true,
            message: `${removedCount} tareas completadas antiguas fueron eliminadas`,
            removedCount
        };
    }

    // Obtener información de almacenamiento
    getStorageInfo() {
        const tasksSize = JSON.stringify(this.tasks).length;
        const totalPhotos = this.tasks.reduce((sum, task) => sum + task.fotos.length, 0);
        const compressedPhotos = this.tasks.reduce((sum, task) => 
            sum + task.fotos.filter(p => p.comprimida).length, 0
        );

        return {
            totalTasks: this.tasks.length,
            storageSize: `${Math.round(tasksSize / 1024)} KB`,
            photos: {
                total: totalPhotos,
                compressed: compressedPhotos,
                uncompressed: totalPhotos - compressedPhotos
            }
        };
    }
}

// Inicializar gestor de tareas
const taskManager = new TaskManager();

// Funciones de utilidad para la interfaz
const TaskUtils = {
    // Formatear fecha para mostrar
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    },

    // Formatear tamaño de archivo
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },

    // Obtener color por prioridad
    getPriorityColor(priority) {
        const colors = {
            alta: '#dc3545',
            media: '#ffc107',
            baja: '#28a745'
        };
        return colors[priority] || '#6c757d';
    },

    // Obtener color por estado
    getStatusColor(status) {
        const colors = {
            pendiente: '#6c757d',
            en_progreso: '#007bff',
            completada: '#28a745',
            cancelada: '#dc3545'
        };
        return colors[status] || '#6c757d';
    },

    // Validar que una fecha límite sea válida
    isValidDeadline(dateString) {
        const deadline = new Date(dateString);
        const now = new Date();
        return deadline > now;
    },

    // Calcular días restantes hasta fecha límite
    getDaysUntilDeadline(dateString) {
        const deadline = new Date(dateString);
        const now = new Date();
        const diffTime = deadline - now;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        return diffDays;
    }
};

// Solo exportar las clases, no crear instancias automáticas
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { TaskManager, TaskUtils };
}