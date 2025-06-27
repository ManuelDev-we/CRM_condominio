/**
 * Clase TagsAdmin - Gestión completa de tags para administradores
 * Permite administración global de todos los tags del sistema
 */
class TagsAdmin {
    constructor() {
        this.apiUrl = '../api/tags.php';
        this.tags = [];
        this.filteredTags = [];
        this.currentFilters = {
            condominio: '',
            calle: '',
            casa: '',
            persona: ''
        };
    }

    /**
     * Registrar un nuevo tag
     */
    async registrarTag(datos) {
        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'crear',
                    ...datos
                })
            });

            const result = await response.json();
            
            if (result.success) {
                await this.loadTags();
                return {
                    success: true,
                    message: 'Tag registrado correctamente',
                    data: result.data
                };
            } else {
                return {
                    success: false,
                    message: result.message || 'Error al registrar el tag'
                };
            }
        } catch (error) {
            console.error('Error al registrar tag:', error);
            return {
                success: false,
                message: 'Error de conexión al registrar el tag'
            };
        }
    }

    /**
     * Eliminar un tag (baja)
     */
    async eliminarTag(idPersona, idCasa) {
        try {
            const response = await fetch(this.apiUrl, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'eliminar',
                    id_persona: idPersona,
                    id_casa: idCasa
                })
            });

            const result = await response.json();
            
            if (result.success) {
                await this.loadTags();
                return {
                    success: true,
                    message: 'Tag eliminado correctamente'
                };
            } else {
                return {
                    success: false,
                    message: result.message || 'Error al eliminar el tag'
                };
            }
        } catch (error) {
            console.error('Error al eliminar tag:', error);
            return {
                success: false,
                message: 'Error de conexión al eliminar el tag'
            };
        }
    }

    /**
     * Obtener todos los tags
     */
    async loadTags() {
        try {
            const response = await fetch(`${this.apiUrl}?action=listar`);
            const result = await response.json();
            
            if (result.success) {
                this.tags = result.data || [];
                this.filteredTags = [...this.tags];
                return this.tags;
            } else {
                console.error('Error al cargar tags:', result.message);
                return [];
            }
        } catch (error) {
            console.error('Error al cargar tags:', error);
            return [];
        }
    }

    /**
     * Obtener tag por ID de persona
     */
    async getTagByPersona(idPersona) {
        try {
            const response = await fetch(`${this.apiUrl}?action=obtener&id_persona=${idPersona}`);
            const result = await response.json();
            
            if (result.success) {
                return result.data;
            } else {
                return null;
            }
        } catch (error) {
            console.error('Error al obtener tag:', error);
            return null;
        }
    }

    /**
     * Buscar tags por diferentes criterios
     */
    async searchTags(criterio, valor) {
        try {
            const response = await fetch(`${this.apiUrl}?action=buscar&criterio=${criterio}&valor=${encodeURIComponent(valor)}`);
            const result = await response.json();
            
            if (result.success) {
                return result.data || [];
            } else {
                return [];
            }
        } catch (error) {
            console.error('Error al buscar tags:', error);
            return [];
        }
    }

    /**
     * Filtrar tags localmente
     */
    filterTags(filters = {}) {
        this.currentFilters = { ...this.currentFilters, ...filters };
        
        this.filteredTags = this.tags.filter(tag => {
            return Object.keys(this.currentFilters).every(key => {
                if (!this.currentFilters[key]) return true;
                
                const tagValue = tag[key]?.toString().toLowerCase();
                const filterValue = this.currentFilters[key].toString().toLowerCase();
                
                return tagValue?.includes(filterValue);
            });
        });

        return this.filteredTags;
    }

    /**
     * Limpiar filtros
     */
    clearFilters() {
        this.currentFilters = {
            condominio: '',
            calle: '',
            casa: '',
            persona: ''
        };
        this.filteredTags = [...this.tags];
        return this.filteredTags;
    }

    /**
     * Obtener estadísticas de tags
     */
    getEstadisticas() {
        const stats = {
            total: this.tags.length,
            porCondominio: {},
            porCalle: {},
            porCasa: {},
            distribucion: {
                condominios: new Set(),
                calles: new Set(),
                casas: new Set(),
                personas: new Set()
            }
        };

        this.tags.forEach(tag => {
            // Por condominio
            const condominio = tag.nombre_condominio || 'Sin condominio';
            stats.porCondominio[condominio] = (stats.porCondominio[condominio] || 0) + 1;

            // Por calle
            const calle = tag.nombre_calle || 'Sin calle';
            stats.porCalle[calle] = (stats.porCalle[calle] || 0) + 1;

            // Por casa
            const casa = tag.casa || 'Sin casa';
            stats.porCasa[casa] = (stats.porCasa[casa] || 0) + 1;

            // Distribución única
            stats.distribucion.condominios.add(tag.id_condominio);
            stats.distribucion.calles.add(tag.id_calle);
            stats.distribucion.casas.add(tag.id_casa);
            stats.distribucion.personas.add(tag.id_persona);
        });

        // Convertir Sets a números
        stats.distribucion.condominios = stats.distribucion.condominios.size;
        stats.distribucion.calles = stats.distribucion.calles.size;
        stats.distribucion.casas = stats.distribucion.casas.size;
        stats.distribucion.personas = stats.distribucion.personas.size;

        return stats;
    }

    /**
     * Obtener tags por condominio
     */
    async getTagsByCondominio(idCondominio) {
        try {
            const response = await fetch(`${this.apiUrl}?action=por_condominio&id_condominio=${idCondominio}`);
            const result = await response.json();
            
            if (result.success) {
                return result.data || [];
            } else {
                return [];
            }
        } catch (error) {
            console.error('Error al obtener tags por condominio:', error);
            return [];
        }
    }

    /**
     * Obtener tags por calle
     */
    async getTagsByCalle(idCalle) {
        try {
            const response = await fetch(`${this.apiUrl}?action=por_calle&id_calle=${idCalle}`);
            const result = await response.json();
            
            if (result.success) {
                return result.data || [];
            } else {
                return [];
            }
        } catch (error) {
            console.error('Error al obtener tags por calle:', error);
            return [];
        }
    }

    /**
     * Obtener tags por casa
     */
    async getTagsByCasa(idCasa) {
        try {
            const response = await fetch(`${this.apiUrl}?action=por_casa&id_casa=${idCasa}`);
            const result = await response.json();
            
            if (result.success) {
                return result.data || [];
            } else {
                return [];
            }
        } catch (error) {
            console.error('Error al obtener tags por casa:', error);
            return [];
        }
    }

    /**
     * Exportar datos de tags
     */
    exportToCSV() {
        const headers = ['ID Persona', 'Nombre Completo', 'Condominio', 'Calle', 'Casa', 'CURP', 'Correo'];
        const rows = this.filteredTags.map(tag => [
            tag.id_persona,
            `${tag.nombres} ${tag.apellido1} ${tag.apellido2}`.trim(),
            tag.nombre_condominio || '',
            tag.nombre_calle || '',
            tag.casa || '',
            tag.curp || '',
            tag.correo_electronico || ''
        ]);

        const csvContent = [headers, ...rows]
            .map(row => row.map(field => `"${field}"`).join(','))
            .join('\n');

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `tags_admin_${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    /**
     * Exportar estadísticas
     */
    exportEstadisticas() {
        const stats = this.getEstadisticas();
        const exportData = {
            resumen: {
                total_tags: stats.total,
                condominios_con_tags: stats.distribucion.condominios,
                calles_con_tags: stats.distribucion.calles,
                casas_con_tags: stats.distribucion.casas,
                personas_con_tags: stats.distribucion.personas,
                fecha_reporte: new Date().toLocaleString()
            },
            distribucion_por_condominio: stats.porCondominio,
            distribucion_por_calle: stats.porCalle,
            distribucion_por_casa: stats.porCasa
        };

        const blob = new Blob([JSON.stringify(exportData, null, 2)], { type: 'application/json' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `estadisticas_tags_${new Date().toISOString().split('T')[0]}.json`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    /**
     * Verificar si una persona tiene tag
     */
    async verificarTag(idPersona, idCasa) {
        try {
            const response = await fetch(`${this.apiUrl}?action=verificar&id_persona=${idPersona}&id_casa=${idCasa}`);
            const result = await response.json();
            
            return result.success && result.data;
        } catch (error) {
            console.error('Error al verificar tag:', error);
            return false;
        }
    }

    /**
     * Obtener todos los datos filtrados actuales
     */
    getCurrentData() {
        return {
            tags: this.filteredTags,
            total: this.filteredTags.length,
            filters: this.currentFilters,
            estadisticas: this.getEstadisticas()
        };
    }

    /**
     * Validar datos antes de registrar
     */
    validateTagData(datos) {
        const errores = [];

        if (!datos.id_persona) errores.push('ID de persona es requerido');
        if (!datos.id_condominio) errores.push('ID de condominio es requerido');
        if (!datos.id_casa) errores.push('ID de casa es requerido');
        if (!datos.id_calle) errores.push('ID de calle es requerido');

        return {
            valido: errores.length === 0,
            errores
        };
    }

    /**
     * Limpiar y resetear datos
     */
    reset() {
        this.tags = [];
        this.filteredTags = [];
        this.currentFilters = {
            condominio: '',
            calle: '',
            casa: '',
            persona: ''
        };
    }
}

// Solo exportar la clase, no crear instancia automática
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TagsAdmin;
}
