/**
 * Clase EngomadasAdmin - Gestión completa de engomados para administradores
 * Permite administración global de todos los engomados del sistema
 */
class EngomadasAdmin {
    constructor() {
        this.apiUrl = '../api/engomados.php';
        this.engomados = [];
        this.filteredEngomados = [];
        this.currentFilters = {
            condominio: '',
            calle: '',
            casa: '',
            persona: '',
            placa: '',
            modelo: '',
            color: '',
            ano: ''
        };
    }

    /**
     * Registrar un nuevo engomado
     */
    async registrarEngomado(datos) {
        try {
            // Validar datos
            const validacion = this.validateEngomadasData(datos);
            if (!validacion.valido) {
                return {
                    success: false,
                    message: 'Datos inválidos: ' + validacion.errores.join(', ')
                };
            }

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
                await this.loadEngomados();
                return {
                    success: true,
                    message: 'Engomado registrado correctamente',
                    data: result.data
                };
            } else {
                return {
                    success: false,
                    message: result.message || 'Error al registrar el engomado'
                };
            }
        } catch (error) {
            console.error('Error al registrar engomado:', error);
            return {
                success: false,
                message: 'Error de conexión al registrar el engomado'
            };
        }
    }

    /**
     * Actualizar un engomado existente
     */
    async actualizarEngomado(idEngomado, datos) {
        try {
            const response = await fetch(this.apiUrl, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'actualizar',
                    id_engomado: idEngomado,
                    ...datos
                })
            });

            const result = await response.json();
            
            if (result.success) {
                await this.loadEngomados();
                return {
                    success: true,
                    message: 'Engomado actualizado correctamente',
                    data: result.data
                };
            } else {
                return {
                    success: false,
                    message: result.message || 'Error al actualizar el engomado'
                };
            }
        } catch (error) {
            console.error('Error al actualizar engomado:', error);
            return {
                success: false,
                message: 'Error de conexión al actualizar el engomado'
            };
        }
    }

    /**
     * Eliminar un engomado (baja)
     */
    async eliminarEngomado(idEngomado) {
        try {
            const response = await fetch(this.apiUrl, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'eliminar',
                    id_engomado: idEngomado
                })
            });

            const result = await response.json();
            
            if (result.success) {
                await this.loadEngomados();
                return {
                    success: true,
                    message: 'Engomado eliminado correctamente'
                };
            } else {
                return {
                    success: false,
                    message: result.message || 'Error al eliminar el engomado'
                };
            }
        } catch (error) {
            console.error('Error al eliminar engomado:', error);
            return {
                success: false,
                message: 'Error de conexión al eliminar el engomado'
            };
        }
    }

    /**
     * Obtener todos los engomados
     */
    async loadEngomados() {
        try {
            const response = await fetch(`${this.apiUrl}?action=listar`);
            const result = await response.json();
            
            if (result.success) {
                this.engomados = result.data || [];
                this.filteredEngomados = [...this.engomados];
                return this.engomados;
            } else {
                console.error('Error al cargar engomados:', result.message);
                return [];
            }
        } catch (error) {
            console.error('Error al cargar engomados:', error);
            return [];
        }
    }

    /**
     * Obtener engomado por ID
     */
    async getEngomadobyId(idEngomado) {
        try {
            const response = await fetch(`${this.apiUrl}?action=obtener&id_engomado=${idEngomado}`);
            const result = await response.json();
            
            if (result.success) {
                return result.data;
            } else {
                return null;
            }
        } catch (error) {
            console.error('Error al obtener engomado:', error);
            return null;
        }
    }

    /**
     * Obtener engomados por persona
     */
    async getEngomadobyPersona(idPersona) {
        try {
            const response = await fetch(`${this.apiUrl}?action=por_persona&id_persona=${idPersona}`);
            const result = await response.json();
            
            if (result.success) {
                return result.data || [];
            } else {
                return [];
            }
        } catch (error) {
            console.error('Error al obtener engomados por persona:', error);
            return [];
        }
    }

    /**
     * Buscar engomados por placa
     */
    async searchByPlaca(placa) {
        try {
            const response = await fetch(`${this.apiUrl}?action=buscar_placa&placa=${encodeURIComponent(placa)}`);
            const result = await response.json();
            
            if (result.success) {
                return result.data || [];
            } else {
                return [];
            }
        } catch (error) {
            console.error('Error al buscar por placa:', error);
            return [];
        }
    }

    /**
     * Buscar engomados por diferentes criterios
     */
    async searchEngomados(criterio, valor) {
        try {
            const response = await fetch(`${this.apiUrl}?action=buscar&criterio=${criterio}&valor=${encodeURIComponent(valor)}`);
            const result = await response.json();
            
            if (result.success) {
                return result.data || [];
            } else {
                return [];
            }
        } catch (error) {
            console.error('Error al buscar engomados:', error);
            return [];
        }
    }

    /**
     * Filtrar engomados localmente
     */
    filterEngomados(filters = {}) {
        this.currentFilters = { ...this.currentFilters, ...filters };
        
        this.filteredEngomados = this.engomados.filter(engomado => {
            return Object.keys(this.currentFilters).every(key => {
                if (!this.currentFilters[key]) return true;
                
                const engomadasValue = engomado[key]?.toString().toLowerCase();
                const filterValue = this.currentFilters[key].toString().toLowerCase();
                
                return engomadasValue?.includes(filterValue);
            });
        });

        return this.filteredEngomados;
    }

    /**
     * Limpiar filtros
     */
    clearFilters() {
        this.currentFilters = {
            condominio: '',
            calle: '',
            casa: '',
            persona: '',
            placa: '',
            modelo: '',
            color: '',
            ano: ''
        };
        this.filteredEngomados = [...this.engomados];
        return this.filteredEngomados;
    }

    /**
     * Obtener estadísticas de engomados
     */
    getEstadisticas() {
        const stats = {
            total: this.engomados.length,
            porCondominio: {},
            porCalle: {},
            porCasa: {},
            porModelo: {},
            porColor: {},
            porAno: {},
            distribucion: {
                condominios: new Set(),
                calles: new Set(),
                casas: new Set(),
                personas: new Set(),
                modelos: new Set(),
                colores: new Set(),
                anos: new Set()
            }
        };

        this.engomados.forEach(engomado => {
            // Por ubicación
            const condominio = engomado.nombre_condominio || 'Sin condominio';
            stats.porCondominio[condominio] = (stats.porCondominio[condominio] || 0) + 1;

            const calle = engomado.nombre_calle || 'Sin calle';
            stats.porCalle[calle] = (stats.porCalle[calle] || 0) + 1;

            const casa = engomado.casa || 'Sin casa';
            stats.porCasa[casa] = (stats.porCasa[casa] || 0) + 1;

            // Por características del vehículo
            const modelo = engomado.modelo || 'Sin modelo';
            stats.porModelo[modelo] = (stats.porModelo[modelo] || 0) + 1;

            const color = engomado.color || 'Sin color';
            stats.porColor[color] = (stats.porColor[color] || 0) + 1;

            const ano = engomado.ano || 'Sin año';
            stats.porAno[ano] = (stats.porAno[ano] || 0) + 1;

            // Distribución única
            stats.distribucion.condominios.add(engomado.id_condominio);
            stats.distribucion.calles.add(engomado.id_calle);
            stats.distribucion.casas.add(engomado.id_casa);
            stats.distribucion.personas.add(engomado.id_persona);
            stats.distribucion.modelos.add(engomado.modelo);
            stats.distribucion.colores.add(engomado.color);
            stats.distribucion.anos.add(engomado.ano);
        });

        // Convertir Sets a números
        Object.keys(stats.distribucion).forEach(key => {
            stats.distribucion[key] = stats.distribucion[key].size;
        });

        return stats;
    }

    /**
     * Obtener engomados por condominio
     */
    async getEngomadobyCondominio(idCondominio) {
        try {
            const response = await fetch(`${this.apiUrl}?action=por_condominio&id_condominio=${idCondominio}`);
            const result = await response.json();
            
            if (result.success) {
                return result.data || [];
            } else {
                return [];
            }
        } catch (error) {
            console.error('Error al obtener engomados por condominio:', error);
            return [];
        }
    }

    /**
     * Obtener engomados por calle
     */
    async getEngomadobyCalle(idCalle) {
        try {
            const response = await fetch(`${this.apiUrl}?action=por_calle&id_calle=${idCalle}`);
            const result = await response.json();
            
            if (result.success) {
                return result.data || [];
            } else {
                return [];
            }
        } catch (error) {
            console.error('Error al obtener engomados por calle:', error);
            return [];
        }
    }

    /**
     * Obtener engomados por casa
     */
    async getEngomadobyCasa(idCasa) {
        try {
            const response = await fetch(`${this.apiUrl}?action=por_casa&id_casa=${idCasa}`);
            const result = await response.json();
            
            if (result.success) {
                return result.data || [];
            } else {
                return [];
            }
        } catch (error) {
            console.error('Error al obtener engomados por casa:', error);
            return [];
        }
    }

    /**
     * Comprimir imagen a base64
     */
    async compressImage(file, maxWidth = 800, maxHeight = 600, quality = 0.8) {
        return new Promise((resolve) => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const img = new Image();

            img.onload = () => {
                // Calcular nuevas dimensiones manteniendo la proporción
                let { width, height } = img;
                
                if (width > maxWidth || height > maxHeight) {
                    const ratio = Math.min(maxWidth / width, maxHeight / height);
                    width *= ratio;
                    height *= ratio;
                }

                canvas.width = width;
                canvas.height = height;

                // Dibujar imagen redimensionada
                ctx.drawImage(img, 0, 0, width, height);

                // Convertir a base64
                const base64 = canvas.toDataURL('image/jpeg', quality);
                resolve(base64);
            };

            img.src = URL.createObjectURL(file);
        });
    }

    /**
     * Descomprimir imagen desde base64
     */
    decompressImage(base64Data) {
        return new Promise((resolve) => {
            const img = new Image();
            img.onload = () => {
                resolve(img);
            };
            img.src = base64Data;
        });
    }

    /**
     * Exportar datos de engomados
     */
    exportToCSV() {
        const headers = [
            'ID Engomado', 'Placa', 'Modelo', 'Color', 'Año', 
            'Propietario', 'Condominio', 'Calle', 'Casa', 'CURP', 'Correo'
        ];
        
        const rows = this.filteredEngomados.map(engomado => [
            engomado.id_engomado,
            engomado.placa,
            engomado.modelo,
            engomado.color,
            engomado.ano,
            `${engomado.nombres} ${engomado.apellido1} ${engomado.apellido2}`.trim(),
            engomado.nombre_condominio || '',
            engomado.nombre_calle || '',
            engomado.casa || '',
            engomado.curp || '',
            engomado.correo_electronico || ''
        ]);

        const csvContent = [headers, ...rows]
            .map(row => row.map(field => `"${field}"`).join(','))
            .join('\n');

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `engomados_admin_${new Date().toISOString().split('T')[0]}.csv`);
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
                total_engomados: stats.total,
                condominios_con_engomados: stats.distribucion.condominios,
                calles_con_engomados: stats.distribucion.calles,
                casas_con_engomados: stats.distribucion.casas,
                personas_con_engomados: stats.distribucion.personas,
                modelos_diferentes: stats.distribucion.modelos,
                colores_diferentes: stats.distribucion.colores,
                anos_diferentes: stats.distribucion.anos,
                fecha_reporte: new Date().toLocaleString()
            },
            distribucion_por_condominio: stats.porCondominio,
            distribucion_por_calle: stats.porCalle,
            distribucion_por_casa: stats.porCasa,
            distribucion_por_modelo: stats.porModelo,
            distribucion_por_color: stats.porColor,
            distribucion_por_ano: stats.porAno
        };

        const blob = new Blob([JSON.stringify(exportData, null, 2)], { type: 'application/json' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `estadisticas_engomados_${new Date().toISOString().split('T')[0]}.json`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    /**
     * Verificar si una placa ya existe
     */
    async verificarPlaca(placa, idEngomadobxcluir = null) {
        try {
            const response = await fetch(`${this.apiUrl}?action=verificar_placa&placa=${encodeURIComponent(placa)}&excluir=${idEngomadobxcluir || ''}`);
            const result = await response.json();
            
            return result.success && result.data;
        } catch (error) {
            console.error('Error al verificar placa:', error);
            return false;
        }
    }

    /**
     * Obtener todos los datos filtrados actuales
     */
    getCurrentData() {
        return {
            engomados: this.filteredEngomados,
            total: this.filteredEngomados.length,
            filters: this.currentFilters,
            estadisticas: this.getEstadisticas()
        };
    }

    /**
     * Validar datos antes de registrar
     */
    validateEngomadasData(datos) {
        const errores = [];

        if (!datos.id_persona) errores.push('ID de persona es requerido');
        if (!datos.id_condominio) errores.push('ID de condominio es requerido');
        if (!datos.id_casa) errores.push('ID de casa es requerido');
        if (!datos.id_calle) errores.push('ID de calle es requerido');
        if (!datos.placa) errores.push('Placa es requerida');
        if (!datos.modelo) errores.push('Modelo es requerido');
        if (!datos.color) errores.push('Color es requerido');
        if (!datos.ano) errores.push('Año es requerido');

        // Validar formato de placa (básico)
        if (datos.placa && !/^[A-Z0-9\-]+$/i.test(datos.placa)) {
            errores.push('Formato de placa inválido');
        }

        // Validar año
        const currentYear = new Date().getFullYear();
        if (datos.ano && (datos.ano < 1900 || datos.ano > currentYear + 1)) {
            errores.push('Año inválido');
        }

        return {
            valido: errores.length === 0,
            errores
        };
    }

    /**
     * Limpiar y resetear datos
     */
    reset() {
        this.engomados = [];
        this.filteredEngomados = [];
        this.currentFilters = {
            condominio: '',
            calle: '',
            casa: '',
            persona: '',
            placa: '',
            modelo: '',
            color: '',
            ano: ''
        };
    }

    /**
     * Obtener modelos más comunes
     */
    getModelosComunes() {
        const modelos = {};
        this.engomados.forEach(engomado => {
            const modelo = engomado.modelo?.toLowerCase();
            if (modelo) {
                modelos[modelo] = (modelos[modelo] || 0) + 1;
            }
        });

        return Object.entries(modelos)
            .sort(([,a], [,b]) => b - a)
            .slice(0, 10)
            .map(([modelo]) => modelo);
    }

    /**
     * Obtener colores más comunes
     */
    getColoresComunes() {
        const colores = {};
        this.engomados.forEach(engomado => {
            const color = engomado.color?.toLowerCase();
            if (color) {
                colores[color] = (colores[color] || 0) + 1;
            }
        });

        return Object.entries(colores)
            .sort(([,a], [,b]) => b - a)
            .slice(0, 10)
            .map(([color]) => color);
    }
}

// Solo exportar la clase, no crear instancia automática
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EngomadasAdmin;
}
