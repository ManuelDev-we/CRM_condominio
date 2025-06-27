/**
 * Clase EngomadasResidente - Gestión de engomados para residentes
 * Permite solo administración de engomados propios del residente
 */
class EngomadasResidente {
    constructor(idPersona = null, idCondominio = null, idCasa = null, idCalle = null) {
        this.apiUrl = '../api/engomados.php';
        this.idPersona = idPersona;
        this.idCondominio = idCondominio;
        this.idCasa = idCasa;
        this.idCalle = idCalle;
        this.misEngomados = [];
        this.filteredEngomados = [];
        this.currentFilters = {
            placa: '',
            modelo: '',
            color: '',
            ano: ''
        };
    }

    /**
     * Configurar datos del residente
     */
    setResidenteData(idPersona, idCondominio, idCasa, idCalle) {
        this.idPersona = idPersona;
        this.idCondominio = idCondominio;
        this.idCasa = idCasa;
        this.idCalle = idCalle;
    }

    /**
     * Solicitar registro de un nuevo engomado
     */
    async solicitarEngomado(datos) {
        try {
            if (!this.idPersona || !this.idCondominio || !this.idCasa || !this.idCalle) {
                return {
                    success: false,
                    message: 'Datos de residente incompletos'
                };
            }

            // Validar datos del vehículo
            const validacion = this.validateEngomadasData(datos);
            if (!validacion.valido) {
                return {
                    success: false,
                    message: 'Datos inválidos: ' + validacion.errores.join(', ')
                };
            }

            // Verificar si la placa ya existe
            const placaExiste = await this.verificarPlaca(datos.placa);
            if (placaExiste) {
                return {
                    success: false,
                    message: 'Esta placa ya está registrada'
                };
            }

            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'solicitar_engomado',
                    id_persona: this.idPersona,
                    id_condominio: this.idCondominio,
                    id_casa: this.idCasa,
                    id_calle: this.idCalle,
                    ...datos
                })
            });

            const result = await response.json();
            
            if (result.success) {
                await this.loadMisEngomados();
                return {
                    success: true,
                    message: 'Solicitud de engomado enviada. Espera la aprobación del administrador.',
                    data: result.data
                };
            } else {
                return {
                    success: false,
                    message: result.message || 'Error al solicitar el engomado'
                };
            }
        } catch (error) {
            console.error('Error al solicitar engomado:', error);
            return {
                success: false,
                message: 'Error de conexión al solicitar el engomado'
            };
        }
    }

    /**
     * Actualizar uno de mis engomados
     */
    async actualizarMiEngomado(idEngomado, datos) {
        try {
            // Verificar que el engomado me pertenece
            const esNúio = await this.verificarPropiedadEngomado(idEngomado);
            if (!esNúio) {
                return {
                    success: false,
                    message: 'No tienes permisos para actualizar este engomado'
                };
            }

            // Validar datos
            const validacion = this.validateEngomadasData(datos);
            if (!validacion.valido) {
                return {
                    success: false,
                    message: 'Datos inválidos: ' + validacion.errores.join(', ')
                };
            }

            // Verificar placa si se está cambiando
            if (datos.placa) {
                const placaExiste = await this.verificarPlaca(datos.placa, idEngomado);
                if (placaExiste) {
                    return {
                        success: false,
                        message: 'Esta placa ya está registrada por otro vehículo'
                    };
                }
            }

            const response = await fetch(this.apiUrl, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'actualizar_mi_engomado',
                    id_engomado: idEngomado,
                    id_persona: this.idPersona,
                    ...datos
                })
            });

            const result = await response.json();
            
            if (result.success) {
                await this.loadMisEngomados();
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
     * Solicitar baja de uno de mis engomados
     */
    async solicitarBajaEngomado(idEngomado) {
        try {
            // Verificar que el engomado me pertenece
            const esNúio = await this.verificarPropiedadEngomado(idEngomado);
            if (!esNúio) {
                return {
                    success: false,
                    message: 'No tienes permisos para dar de baja este engomado'
                };
            }

            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'solicitar_baja_engomado',
                    id_engomado: idEngomado,
                    id_persona: this.idPersona
                })
            });

            const result = await response.json();
            
            if (result.success) {
                return {
                    success: true,
                    message: 'Solicitud de baja enviada. El administrador procesará tu solicitud.',
                    data: result.data
                };
            } else {
                return {
                    success: false,
                    message: result.message || 'Error al solicitar la baja del engomado'
                };
            }
        } catch (error) {
            console.error('Error al solicitar baja de engomado:', error);
            return {
                success: false,
                message: 'Error de conexión al solicitar la baja del engomado'
            };
        }
    }

    /**
     * Obtener todos mis engomados
     */
    async loadMisEngomados() {
        try {
            if (!this.idPersona) {
                return [];
            }

            const response = await fetch(`${this.apiUrl}?action=mis_engomados&id_persona=${this.idPersona}`);
            const result = await response.json();
            
            if (result.success) {
                this.misEngomados = result.data || [];
                this.filteredEngomados = [...this.misEngomados];
                return this.misEngomados;
            } else {
                console.error('Error al cargar mis engomados:', result.message);
                return [];
            }
        } catch (error) {
            console.error('Error al cargar mis engomados:', error);
            return [];
        }
    }

    /**
     * Obtener un engomado específico mío por ID
     */
    async obtenerMiEngomado(idEngomado) {
        try {
            const response = await fetch(`${this.apiUrl}?action=obtener_mi_engomado&id_engomado=${idEngomado}&id_persona=${this.idPersona}`);
            const result = await response.json();
            
            if (result.success) {
                return result.data;
            } else {
                return null;
            }
        } catch (error) {
            console.error('Error al obtener mi engomado:', error);
            return null;
        }
    }

    /**
     * Buscar en mis engomados
     */
    searchMisEngomados(criterio, valor) {
        if (!valor) {
            this.filteredEngomados = [...this.misEngomados];
            return this.filteredEngomados;
        }

        const valorNormalizado = valor.toLowerCase();
        this.filteredEngomados = this.misEngomados.filter(engomado => {
            const campoValor = engomado[criterio]?.toString().toLowerCase();
            return campoValor?.includes(valorNormalizado);
        });

        return this.filteredEngomados;
    }

    /**
     * Filtrar mis engomados localmente
     */
    filterMisEngomados(filters = {}) {
        this.currentFilters = { ...this.currentFilters, ...filters };
        
        this.filteredEngomados = this.misEngomados.filter(engomado => {
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
            placa: '',
            modelo: '',
            color: '',
            ano: ''
        };
        this.filteredEngomados = [...this.misEngomados];
        return this.filteredEngomados;
    }

    /**
     * Obtener mis estadísticas
     */
    getMisEstadisticas() {
        const stats = {
            total: this.misEngomados.length,
            porModelo: {},
            porColor: {},
            porAno: {},
            vehiculoMasAntiguo: null,
            vehiculoMasNuevo: null,
            modelosMasComunes: [],
            coloresMasComunes: []
        };

        if (this.misEngomados.length === 0) {
            return stats;
        }

        // Procesar cada engomado
        this.misEngomados.forEach(engomado => {
            // Por modelo
            const modelo = engomado.modelo || 'Sin modelo';
            stats.porModelo[modelo] = (stats.porModelo[modelo] || 0) + 1;

            // Por color
            const color = engomado.color || 'Sin color';
            stats.porColor[color] = (stats.porColor[color] || 0) + 1;

            // Por año
            const ano = engomado.ano || 'Sin año';
            stats.porAno[ano] = (stats.porAno[ano] || 0) + 1;
        });

        // Encontrar vehículo más antiguo y más nuevo
        const anos = this.misEngomados
            .map(e => parseInt(e.ano))
            .filter(ano => !isNaN(ano))
            .sort((a, b) => a - b);

        if (anos.length > 0) {
            stats.vehiculoMasAntiguo = anos[0];
            stats.vehiculoMasNuevo = anos[anos.length - 1];
        }

        // Modelos más comunes
        stats.modelosMasComunes = Object.entries(stats.porModelo)
            .sort(([,a], [,b]) => b - a)
            .slice(0, 3)
            .map(([modelo, cantidad]) => ({ modelo, cantidad }));

        // Colores más comunes
        stats.coloresMasComunes = Object.entries(stats.porColor)
            .sort(([,a], [,b]) => b - a)
            .slice(0, 3)
            .map(([color, cantidad]) => ({ color, cantidad }));

        return stats;
    }

    /**
     * Obtener estado de mis solicitudes
     */
    async obtenerMisSolicitudes() {
        try {
            if (!this.idPersona) {
                return [];
            }

            const response = await fetch(`${this.apiUrl}?action=mis_solicitudes_engomados&id_persona=${this.idPersona}`);
            const result = await response.json();
            
            if (result.success) {
                return result.data || [];
            } else {
                return [];
            }
        } catch (error) {
            console.error('Error al obtener solicitudes:', error);
            return [];
        }
    }

    /**
     * Comprimir imagen a base64
     */
    async compressImage(file, maxWidth = 600, maxHeight = 400, quality = 0.7) {
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
     * Verificar si un engomado me pertenece
     */
    async verificarPropiedadEngomado(idEngomado) {
        try {
            const response = await fetch(`${this.apiUrl}?action=verificar_propiedad&id_engomado=${idEngomado}&id_persona=${this.idPersona}`);
            const result = await response.json();
            
            return result.success && result.data;
        } catch (error) {
            console.error('Error al verificar propiedad:', error);
            return false;
        }
    }

    /**
     * Exportar mis engomados
     */
    exportarMisEngomados() {
        if (this.misEngomados.length === 0) {
            alert('No tienes engomados para exportar');
            return;
        }

        const headers = ['Placa', 'Modelo', 'Color', 'Año', 'Fecha Registro'];
        const rows = this.filteredEngomados.map(engomado => [
            engomado.placa,
            engomado.modelo,
            engomado.color,
            engomado.ano,
            engomado.fecha_registro || 'N/A'
        ]);

        const csvContent = [headers, ...rows]
            .map(row => row.map(field => `"${field}"`).join(','))
            .join('\n');

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `mis_engomados_${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    /**
     * Exportar mis estadísticas
     */
    exportarMisEstadisticas() {
        const stats = this.getMisEstadisticas();
        const misSolicitudes = this.obtenerMisSolicitudes();
        
        const exportData = {
            resumen: {
                total_engomados: stats.total,
                vehiculo_mas_antiguo: stats.vehiculoMasAntiguo,
                vehiculo_mas_nuevo: stats.vehiculoMasNuevo,
                fecha_reporte: new Date().toLocaleString(),
                propietario: this.idPersona
            },
            distribucion: {
                por_modelo: stats.porModelo,
                por_color: stats.porColor,
                por_ano: stats.porAno
            },
            top_3: {
                modelos_mas_comunes: stats.modelosMasComunes,
                colores_mas_comunes: stats.coloresMasComunes
            }
        };

        const blob = new Blob([JSON.stringify(exportData, null, 2)], { type: 'application/json' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `mis_estadisticas_engomados_${new Date().toISOString().split('T')[0]}.json`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    /**
     * Validar datos antes de registrar/actualizar
     */
    validateEngomadasData(datos) {
        const errores = [];

        if (!datos.placa) errores.push('Placa es requerida');
        if (!datos.modelo) errores.push('Modelo es requerido');
        if (!datos.color) errores.push('Color es requerido');
        if (!datos.ano) errores.push('Año es requerido');

        // Validar formato de placa (básico)
        if (datos.placa && !/^[A-Z0-9\-]+$/i.test(datos.placa)) {
            errores.push('Formato de placa inválido (solo letras, números y guiones)');
        }

        // Validar año
        const currentYear = new Date().getFullYear();
        if (datos.ano && (datos.ano < 1900 || datos.ano > currentYear + 1)) {
            errores.push('Año inválido (debe estar entre 1900 y ' + (currentYear + 1) + ')');
        }

        return {
            valido: errores.length === 0,
            errores
        };
    }

    /**
     * Obtener mis datos actuales
     */
    getCurrentData() {
        return {
            engomados: this.filteredEngomados,
            total: this.filteredEngomados.length,
            filters: this.currentFilters,
            estadisticas: this.getMisEstadisticas()
        };
    }

    /**
     * Limpiar y resetear datos
     */
    reset() {
        this.misEngomados = [];
        this.filteredEngomados = [];
        this.currentFilters = {
            placa: '',
            modelo: '',
            color: '',
            ano: ''
        };
    }

    /**
     * Obtener sugerencias de modelos basadas en mis vehículos anteriores
     */
    getModelosSugeridos() {
        const modelos = new Set();
        this.misEngomados.forEach(engomado => {
            if (engomado.modelo) {
                modelos.add(engomado.modelo.toLowerCase());
            }
        });
        return Array.from(modelos);
    }

    /**
     * Obtener sugerencias de colores basadas en mis vehículos anteriores
     */
    getColoresSugeridos() {
        const colores = new Set();
        this.misEngomados.forEach(engomado => {
            if (engomado.color) {
                colores.add(engomado.color.toLowerCase());
            }
        });
        return Array.from(colores);
    }

    /**
     * Validar límite de engomados por residente
     */
    async validarLimiteEngomados() {
        const limite = 5; // Límite configurable
        return {
            puede: this.misEngomados.length < limite,
            limite: limite,
            actual: this.misEngomados.length,
            restantes: limite - this.misEngomados.length
        };
    }
}

// Crear instancia global para uso en la aplicación
window.engomadasResidente = new EngomadasResidente();
