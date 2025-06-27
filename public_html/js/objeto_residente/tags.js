/**
 * Clase TagsResidente - Gestión de tags para residentes
 * Permite solo administración de tags propios del residente
 */
class TagsResidente {
    constructor(idPersona = null, idCondominio = null, idCasa = null, idCalle = null) {
        this.apiUrl = '../api/tags.php';
        this.idPersona = idPersona;
        this.idCondominio = idCondominio;
        this.idCasa = idCasa;
        this.idCalle = idCalle;
        this.miTag = null;
        this.solicitudPendiente = false;
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
     * Solicitar registro de mi tag
     */
    async solicitarTag() {
        try {
            if (!this.idPersona || !this.idCondominio || !this.idCasa || !this.idCalle) {
                return {
                    success: false,
                    message: 'Datos de residente incompletos'
                };
            }

            // Verificar si ya tiene tag
            const tieneTag = await this.verificarMiTag();
            if (tieneTag) {
                return {
                    success: false,
                    message: 'Ya tienes un tag registrado'
                };
            }

            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'solicitar_tag',
                    id_persona: this.idPersona,
                    id_condominio: this.idCondominio,
                    id_casa: this.idCasa,
                    id_calle: this.idCalle
                })
            });

            const result = await response.json();
            
            if (result.success) {
                this.solicitudPendiente = true;
                return {
                    success: true,
                    message: 'Solicitud de tag enviada. Espera la aprobación del administrador.',
                    data: result.data
                };
            } else {
                return {
                    success: false,
                    message: result.message || 'Error al solicitar el tag'
                };
            }
        } catch (error) {
            console.error('Error al solicitar tag:', error);
            return {
                success: false,
                message: 'Error de conexión al solicitar el tag'
            };
        }
    }

    /**
     * Obtener mi tag actual
     */
    async obtenerMiTag() {
        try {
            if (!this.idPersona) {
                return null;
            }

            const response = await fetch(`${this.apiUrl}?action=obtener_mi_tag&id_persona=${this.idPersona}`);
            const result = await response.json();
            
            if (result.success && result.data) {
                this.miTag = result.data;
                return this.miTag;
            } else {
                this.miTag = null;
                return null;
            }
        } catch (error) {
            console.error('Error al obtener mi tag:', error);
            return null;
        }
    }

    /**
     * Verificar si tengo tag
     */
    async verificarMiTag() {
        try {
            if (!this.idPersona || !this.idCasa) {
                return false;
            }

            const response = await fetch(`${this.apiUrl}?action=verificar&id_persona=${this.idPersona}&id_casa=${this.idCasa}`);
            const result = await response.json();
            
            return result.success && result.data;
        } catch (error) {
            console.error('Error al verificar mi tag:', error);
            return false;
        }
    }

    /**
     * Solicitar baja de mi tag
     */
    async solicitarBajaTag() {
        try {
            if (!this.idPersona || !this.idCasa) {
                return {
                    success: false,
                    message: 'Datos insuficientes para solicitar baja'
                };
            }

            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'solicitar_baja_tag',
                    id_persona: this.idPersona,
                    id_casa: this.idCasa
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
                    message: result.message || 'Error al solicitar la baja del tag'
                };
            }
        } catch (error) {
            console.error('Error al solicitar baja de tag:', error);
            return {
                success: false,
                message: 'Error de conexión al solicitar la baja del tag'
            };
        }
    }

    /**
     * Obtener estado de mis solicitudes
     */
    async obtenerMisSolicitudes() {
        try {
            if (!this.idPersona) {
                return [];
            }

            const response = await fetch(`${this.apiUrl}?action=mis_solicitudes&id_persona=${this.idPersona}`);
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
     * Obtener información de mi tag y estado
     */
    async obtenerMiInfo() {
        try {
            const miTag = await this.obtenerMiTag();
            const misSolicitudes = await this.obtenerMisSolicitudes();
            
            return {
                tieneTag: !!miTag,
                tag: miTag,
                solicitudes: misSolicitudes,
                solicitudPendiente: misSolicitudes.some(s => s.estado === 'pendiente')
            };
        } catch (error) {
            console.error('Error al obtener mi información:', error);
            return {
                tieneTag: false,
                tag: null,
                solicitudes: [],
                solicitudPendiente: false
            };
        }
    }

    /**
     * Obtener tags de otros residentes de mi casa (solo para visualización)
     */
    async obtenerTagsDeMiCasa() {
        try {
            if (!this.idCasa) {
                return [];
            }

            const response = await fetch(`${this.apiUrl}?action=tags_casa&id_casa=${this.idCasa}`);
            const result = await response.json();
            
            if (result.success) {
                // Filtrar datos sensibles, solo mostrar nombres
                return (result.data || []).map(tag => ({
                    id_persona: tag.id_persona,
                    nombre_completo: `${tag.nombres} ${tag.apellido1} ${tag.apellido2}`.trim(),
                    fecha_registro: tag.fecha_registro || 'N/A'
                }));
            } else {
                return [];
            }
        } catch (error) {
            console.error('Error al obtener tags de mi casa:', error);
            return [];
        }
    }

    /**
     * Obtener tags de mi calle (solo para visualización)
     */
    async obtenerTagsDeMiCalle() {
        try {
            if (!this.idCalle) {
                return [];
            }

            const response = await fetch(`${this.apiUrl}?action=tags_calle_residente&id_calle=${this.idCalle}`);
            const result = await response.json();
            
            if (result.success) {
                // Filtrar datos sensibles, solo mostrar información básica
                return (result.data || []).map(tag => ({
                    casa: tag.casa,
                    total_tags: tag.total_tags || 1
                }));
            } else {
                return [];
            }
        } catch (error) {
            console.error('Error al obtener tags de mi calle:', error);
            return [];
        }
    }

    /**
     * Obtener estadísticas básicas de mi área
     */
    async obtenerEstadisticasMiArea() {
        try {
            const tagsCasa = await this.obtenerTagsDeMiCasa();
            const tagsCalle = await this.obtenerTagsDeMiCalle();
            const miInfo = await this.obtenerMiInfo();

            return {
                mi_casa: {
                    total_tags: tagsCasa.length,
                    tengo_tag: miInfo.tieneTag,
                    mi_tag_fecha: miInfo.tag?.fecha_registro || null
                },
                mi_calle: {
                    casas_con_tags: tagsCalle.length,
                    total_tags_calle: tagsCalle.reduce((sum, casa) => sum + casa.total_tags, 0)
                },
                mis_solicitudes: {
                    total: miInfo.solicitudes.length,
                    pendientes: miInfo.solicitudes.filter(s => s.estado === 'pendiente').length,
                    aprobadas: miInfo.solicitudes.filter(s => s.estado === 'aprobada').length,
                    rechazadas: miInfo.solicitudes.filter(s => s.estado === 'rechazada').length
                }
            };
        } catch (error) {
            console.error('Error al obtener estadísticas:', error);
            return {
                mi_casa: { total_tags: 0, tengo_tag: false, mi_tag_fecha: null },
                mi_calle: { casas_con_tags: 0, total_tags_calle: 0 },
                mis_solicitudes: { total: 0, pendientes: 0, aprobadas: 0, rechazadas: 0 }
            };
        }
    }

    /**
     * Actualizar mi información personal (solo algunos campos)
     */
    async actualizarMiInfo(datos) {
        try {
            // Solo permitir actualizar ciertos campos seguros
            const datosPermitidos = {
                correo_electronico: datos.correo_electronico,
                // Agregar otros campos que el residente pueda actualizar
            };

            const response = await fetch(this.apiUrl, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'actualizar_mi_info',
                    id_persona: this.idPersona,
                    ...datosPermitidos
                })
            });

            const result = await response.json();
            
            if (result.success) {
                return {
                    success: true,
                    message: 'Información actualizada correctamente'
                };
            } else {
                return {
                    success: false,
                    message: result.message || 'Error al actualizar la información'
                };
            }
        } catch (error) {
            console.error('Error al actualizar información:', error);
            return {
                success: false,
                message: 'Error de conexión al actualizar la información'
            };
        }
    }

    /**
     * Exportar mi información de tag
     */
    exportarMiInfo() {
        if (!this.miTag) {
            alert('No tienes información de tag para exportar');
            return;
        }

        const datos = {
            informacion_tag: {
                propietario: `${this.miTag.nombres} ${this.miTag.apellido1} ${this.miTag.apellido2}`.trim(),
                condominio: this.miTag.nombre_condominio,
                calle: this.miTag.nombre_calle,
                casa: this.miTag.casa,
                fecha_registro: this.miTag.fecha_registro || 'N/A',
                fecha_exportacion: new Date().toLocaleString()
            }
        };

        const blob = new Blob([JSON.stringify(datos, null, 2)], { type: 'application/json' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `mi_tag_${new Date().toISOString().split('T')[0]}.json`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    /**
     * Validar si puedo solicitar un tag
     */
    async puedoSolicitarTag() {
        try {
            const miInfo = await this.obtenerMiInfo();
            
            return {
                puede: !miInfo.tieneTag && !miInfo.solicitudPendiente,
                razon: miInfo.tieneTag ? 'Ya tienes un tag registrado' : 
                       miInfo.solicitudPendiente ? 'Ya tienes una solicitud pendiente' :
                       'Puedes solicitar un tag'
            };
        } catch (error) {
            return {
                puede: false,
                razon: 'Error al verificar el estado'
            };
        }
    }

    /**
     * Validar si puedo solicitar baja de tag
     */
    async puedoSolicitarBaja() {
        try {
            const miInfo = await this.obtenerMiInfo();
            const tieneSolicitudBajaPendiente = miInfo.solicitudes.some(
                s => s.tipo === 'baja' && s.estado === 'pendiente'
            );
            
            return {
                puede: miInfo.tieneTag && !tieneSolicitudBajaPendiente,
                razon: !miInfo.tieneTag ? 'No tienes un tag registrado' :
                       tieneSolicitudBajaPendiente ? 'Ya tienes una solicitud de baja pendiente' :
                       'Puedes solicitar la baja del tag'
            };
        } catch (error) {
            return {
                puede: false,
                razon: 'Error al verificar el estado'
            };
        }
    }

    /**
     * Limpiar datos locales
     */
    reset() {
        this.miTag = null;
        this.solicitudPendiente = false;
    }
}

// Crear instancia global para uso en la aplicación
window.tagsResidente = new TagsResidente();
