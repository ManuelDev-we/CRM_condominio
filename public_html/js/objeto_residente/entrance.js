/**
 * Clase EntranceResident - Gestión de accesos QR para residentes
 * 
 * Permite a los residentes:
 * - Generar códigos QR de acceso (solo mayores de edad)
 * - Ver su historial de accesos
 * - Dar de baja códigos QR activos
 * - Consultar el estatus de sus códigos QR
 * 
 * Restricciones:
 * - Solo mayores de edad (18+) pueden generar QR
 * - Solo pueden gestionar sus propios códigos QR
 * - Validación de identidad requerida
 */

class EntranceResident {
    constructor(idPersona, datosPersona = null) {
        this.idPersona = idPersona;
        this.datosPersona = datosPersona;
        this.accesos = this.loadAccesos();
        this.nextId = this.generateNextId();
        this.EXPIRATION_HOURS = 8; // 8 horas de expiración
        this.EDAD_MINIMA = 18; // Edad mínima para generar QR
        
        this.STATUS = {
            ACTIVO: 'activo',
            USADO: 'usado',
            EXPIRADO: 'expirado',
            ANULADO: 'anulado'
        };

        this.TIPOS_QR = {
            PERSONAL: 'personal',
            VISITANTE: 'visitante',
            SERVICIO: 'servicio',
            EMERGENCIA: 'emergencia'
        };

        // Cargar datos de la persona si no se proporcionaron
        if (!this.datosPersona) {
            this.loadPersonData();
        }
    }

    /**
     * Carga datos de accesos desde localStorage
     */
    loadAccesos() {
        const stored = localStorage.getItem('accesos_residentes');
        return stored ? JSON.parse(stored) : [];
    }

    /**
     * Guarda accesos en localStorage
     */
    saveAccesos() {
        localStorage.setItem('accesos_residentes', JSON.stringify(this.accesos));
    }

    /**
     * Carga datos de la persona
     */
    loadPersonData() {
        try {
            // Intentar cargar desde localStorage o API
            const personas = JSON.parse(localStorage.getItem('personas') || '[]');
            this.datosPersona = personas.find(p => p.id === this.idPersona);
            
            if (!this.datosPersona) {
                throw new Error('Persona no encontrada');
            }
        } catch (error) {
            console.error('Error al cargar datos de la persona:', error);
            this.datosPersona = null;
        }
    }

    /**
     * Genera siguiente ID para accesos
     */
    generateNextId() {
        if (this.accesos.length === 0) return 1;
        return Math.max(...this.accesos.map(a => a.id_entrance)) + 1;
    }

    /**
     * Calcula la edad de una persona
     */
    calcularEdad(fechaNacimiento) {
        if (!fechaNacimiento) return 0;
        
        const hoy = new Date();
        const nacimiento = new Date(fechaNacimiento);
        let edad = hoy.getFullYear() - nacimiento.getFullYear();
        const mes = hoy.getMonth() - nacimiento.getMonth();
        
        if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
            edad--;
        }
        
        return edad;
    }

    /**
     * Verifica si la persona es mayor de edad
     */
    esMayorDeEdad() {
        if (!this.datosPersona || !this.datosPersona.fecha_nacimiento) {
            // Si no hay fecha de nacimiento, asumir que es mayor de edad
            console.warn('No se encontró fecha de nacimiento, asumiendo mayoría de edad');
            return true;
        }
        
        const edad = this.calcularEdad(this.datosPersona.fecha_nacimiento);
        return edad >= this.EDAD_MINIMA;
    }

    /**
     * Genera un nuevo código QR de acceso
     */
    async generarCodigoQR(datosAcceso) {
        try {
            // Verificar mayoría de edad
            if (!this.esMayorDeEdad()) {
                return {
                    success: false,
                    message: `Solo personas mayores de ${this.EDAD_MINIMA} años pueden generar códigos QR de acceso`
                };
            }

            // Validar datos requeridos
            const {
                nombre_accesante,
                tipo_acceso = this.TIPOS_QR.PERSONAL,
                motivo = 'Acceso personal',
                fecha_limite = null,
                observaciones = ''
            } = datosAcceso;

            if (!nombre_accesante) {
                return {
                    success: false,
                    message: 'El nombre del accesante es obligatorio'
                };
            }

            // Verificar límite de códigos QR activos
            const codigosActivos = this.getCodigosActivos();
            if (codigosActivos.length >= 5) {
                return {
                    success: false,
                    message: 'Ha alcanzado el límite máximo de 5 códigos QR activos. Debe dar de baja alguno antes de crear uno nuevo.'
                };
            }

            // Crear el acceso
            const fechaCreacion = new Date();
            const fechaExpiracion = fecha_limite ? 
                new Date(fecha_limite) : 
                new Date(fechaCreacion.getTime() + (this.EXPIRATION_HOURS * 60 * 60 * 1000));

            const nuevoAcceso = {
                id_entrance: this.nextId++,
                id_persona: this.idPersona,
                id_condominio: this.datosPersona?.id_condominio || 1,
                id_calle: this.datosPersona?.id_calle || 1,
                id_casa: this.datosPersona?.id_casa || 1,
                nombre_accesante: nombre_accesante.trim(),
                tipo_acceso,
                motivo,
                fecha_creacion: fechaCreacion.toISOString(),
                fecha_expiracion: fechaExpiracion.toISOString(),
                hora_creacion: fechaCreacion.toTimeString().split(' ')[0],
                qr_code: '',
                status: this.STATUS.ACTIVO,
                entrada: null,
                salida: null,
                observaciones,
                created_at: fechaCreacion.toISOString(),
                updated_at: fechaCreacion.toISOString(),
                creado_por: this.datosPersona?.nombre || 'Usuario'
            };

            // Generar código QR
            nuevoAcceso.qr_code = this.generateQRCode(nuevoAcceso);

            // Guardar
            this.accesos.push(nuevoAcceso);
            this.saveAccesos();

            return {
                success: true,
                message: 'Código QR generado exitosamente',
                data: {
                    id_entrance: nuevoAcceso.id_entrance,
                    qr_code: nuevoAcceso.qr_code,
                    nombre_accesante: nuevoAcceso.nombre_accesante,
                    fecha_expiracion: nuevoAcceso.fecha_expiracion,
                    tipo_acceso: nuevoAcceso.tipo_acceso,
                    qr_url: this.generateQRImageURL(nuevoAcceso.qr_code)
                }
            };

        } catch (error) {
            console.error('Error al generar código QR:', error);
            return {
                success: false,
                message: 'Error al generar código QR: ' + error.message
            };
        }
    }

    /**
     * Da de baja un código QR activo
     */
    darDeBajaQR(idEntrance, motivo = 'Cancelado por el usuario') {
        try {
            const acceso = this.accesos.find(a => 
                a.id_entrance === parseInt(idEntrance) && 
                a.id_persona === this.idPersona
            );

            if (!acceso) {
                return {
                    success: false,
                    message: 'Código QR no encontrado o no tienes permisos para modificarlo'
                };
            }

            if (acceso.status !== this.STATUS.ACTIVO) {
                return {
                    success: false,
                    message: `No se puede dar de baja un código QR con estado: ${acceso.status}`
                };
            }

            // Cambiar estado a anulado
            acceso.status = this.STATUS.ANULADO;
            acceso.observaciones += ` | Anulado: ${motivo}`;
            acceso.updated_at = new Date().toISOString();

            this.saveAccesos();

            return {
                success: true,
                message: 'Código QR dado de baja exitosamente',
                data: {
                    id_entrance: acceso.id_entrance,
                    nombre_accesante: acceso.nombre_accesante,
                    status: acceso.status
                }
            };

        } catch (error) {
            console.error('Error al dar de baja QR:', error);
            return {
                success: false,
                message: 'Error al dar de baja el código QR'
            };
        }
    }

    /**
     * Obtiene el historial de accesos del usuario
     */
    getHistorial(filtros = {}) {
        try {
            const {
                fechaDesde,
                fechaHasta,
                status,
                tipoAcceso,
                limite = 50
            } = filtros;

            let historial = this.accesos.filter(a => a.id_persona === this.idPersona);

            // Aplicar filtros
            if (fechaDesde) {
                const desde = new Date(fechaDesde);
                historial = historial.filter(a => new Date(a.fecha_creacion) >= desde);
            }

            if (fechaHasta) {
                const hasta = new Date(fechaHasta);
                historial = historial.filter(a => new Date(a.fecha_creacion) <= hasta);
            }

            if (status) {
                historial = historial.filter(a => a.status === status);
            }

            if (tipoAcceso) {
                historial = historial.filter(a => a.tipo_acceso === tipoAcceso);
            }

            // Ordenar por fecha de creación (más reciente primero)
            historial.sort((a, b) => new Date(b.fecha_creacion) - new Date(a.fecha_creacion));

            // Limitar resultados
            historial = historial.slice(0, limite);

            return {
                success: true,
                data: historial,
                total: historial.length,
                filtros: filtros
            };

        } catch (error) {
            console.error('Error al obtener historial:', error);
            return {
                success: false,
                message: 'Error al obtener historial de accesos'
            };
        }
    }

    /**
     * Obtiene el estatus de un código QR específico
     */
    getStatusQR(idEntrance) {
        try {
            const acceso = this.accesos.find(a => 
                a.id_entrance === parseInt(idEntrance) && 
                a.id_persona === this.idPersona
            );

            if (!acceso) {
                return {
                    success: false,
                    message: 'Código QR no encontrado'
                };
            }

            // Verificar si está expirado
            const ahora = new Date();
            const fechaExpiracion = new Date(acceso.fecha_expiracion);
            
            if (acceso.status === this.STATUS.ACTIVO && fechaExpiracion < ahora) {
                acceso.status = this.STATUS.EXPIRADO;
                acceso.updated_at = new Date().toISOString();
                this.saveAccesos();
            }

            return {
                success: true,
                data: {
                    id_entrance: acceso.id_entrance,
                    nombre_accesante: acceso.nombre_accesante,
                    status: acceso.status,
                    fecha_creacion: acceso.fecha_creacion,
                    fecha_expiracion: acceso.fecha_expiracion,
                    tipo_acceso: acceso.tipo_acceso,
                    entrada: acceso.entrada,
                    salida: acceso.salida,
                    es_valido: acceso.status === this.STATUS.ACTIVO && fechaExpiracion > ahora,
                    tiempo_restante: this.getTiempoRestante(fechaExpiracion),
                    qr_url: acceso.status === this.STATUS.ACTIVO ? 
                        this.generateQRImageURL(acceso.qr_code) : null
                }
            };

        } catch (error) {
            console.error('Error al obtener estatus:', error);
            return {
                success: false,
                message: 'Error al obtener estatus del código QR'
            };
        }
    }

    /**
     * Obtiene códigos QR activos del usuario
     */
    getCodigosActivos() {
        const ahora = new Date();
        return this.accesos.filter(a => 
            a.id_persona === this.idPersona && 
            a.status === this.STATUS.ACTIVO &&
            new Date(a.fecha_expiracion) > ahora
        );
    }

    /**
     * Obtiene estadísticas del usuario
     */
    getEstadisticas() {
        try {
            const misAccesos = this.accesos.filter(a => a.id_persona === this.idPersona);
            const ahora = new Date();
            const hace30Dias = new Date(ahora.getTime() - (30 * 24 * 60 * 60 * 1000));

            const estadisticas = {
                total_generados: misAccesos.length,
                activos: misAccesos.filter(a => 
                    a.status === this.STATUS.ACTIVO && 
                    new Date(a.fecha_expiracion) > ahora
                ).length,
                usados: misAccesos.filter(a => a.status === this.STATUS.USADO).length,
                expirados: misAccesos.filter(a => a.status === this.STATUS.EXPIRADO).length,
                anulados: misAccesos.filter(a => a.status === this.STATUS.ANULADO).length,
                este_mes: misAccesos.filter(a => 
                    new Date(a.fecha_creacion) >= hace30Dias
                ).length,
                por_tipo: {}
            };

            // Estadísticas por tipo
            Object.values(this.TIPOS_QR).forEach(tipo => {
                estadisticas.por_tipo[tipo] = misAccesos.filter(a => a.tipo_acceso === tipo).length;
            });

            return {
                success: true,
                data: estadisticas
            };

        } catch (error) {
            console.error('Error al obtener estadísticas:', error);
            return {
                success: false,
                message: 'Error al obtener estadísticas'
            };
        }
    }

    /**
     * Genera código QR único
     */
    generateQRCode(acceso) {
        const data = {
            id: acceso.id_entrance,
            persona: acceso.id_persona,
            condominio: acceso.id_condominio,
            timestamp: acceso.fecha_creacion,
            tipo: acceso.tipo_acceso
        };
        
        return btoa(JSON.stringify(data)) + '_' + Date.now();
    }

    /**
     * Genera URL para imagen QR
     */
    generateQRImageURL(qrCode) {
        const qrData = encodeURIComponent(qrCode);
        return `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${qrData}`;
    }

    /**
     * Calcula tiempo restante hasta expiración
     */
    getTiempoRestante(fechaExpiracion) {
        const ahora = new Date();
        const expiracion = new Date(fechaExpiracion);
        const diff = expiracion - ahora;

        if (diff <= 0) {
            return 'Expirado';
        }

        const horas = Math.floor(diff / (1000 * 60 * 60));
        const minutos = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

        if (horas > 0) {
            return `${horas}h ${minutos}m`;
        } else {
            return `${minutos}m`;
        }
    }

    /**
     * Renueva un código QR activo (extiende su tiempo de vida)
     */
    renovarQR(idEntrance, horasExtension = 8) {
        try {
            // Verificar mayoría de edad
            if (!this.esMayorDeEdad()) {
                return {
                    success: false,
                    message: `Solo personas mayores de ${this.EDAD_MINIMA} años pueden renovar códigos QR`
                };
            }

            const acceso = this.accesos.find(a => 
                a.id_entrance === parseInt(idEntrance) && 
                a.id_persona === this.idPersona
            );

            if (!acceso) {
                return {
                    success: false,
                    message: 'Código QR no encontrado'
                };
            }

            if (acceso.status !== this.STATUS.ACTIVO) {
                return {
                    success: false,
                    message: 'Solo se pueden renovar códigos QR activos'
                };
            }

            // Extender fecha de expiración
            const nuevaExpiracion = new Date(acceso.fecha_expiracion);
            nuevaExpiracion.setHours(nuevaExpiracion.getHours() + horasExtension);
            
            acceso.fecha_expiracion = nuevaExpiracion.toISOString();
            acceso.updated_at = new Date().toISOString();
            acceso.observaciones += ` | Renovado: +${horasExtension}h`;

            this.saveAccesos();

            return {
                success: true,
                message: `Código QR renovado por ${horasExtension} horas`,
                data: {
                    id_entrance: acceso.id_entrance,
                    nueva_expiracion: acceso.fecha_expiracion,
                    tiempo_restante: this.getTiempoRestante(acceso.fecha_expiracion)
                }
            };

        } catch (error) {
            console.error('Error al renovar QR:', error);
            return {
                success: false,
                message: 'Error al renovar código QR'
            };
        }
    }

    /**
     * Obtiene información del usuario
     */
    getInfoUsuario() {
        return {
            id_persona: this.idPersona,
            datos_persona: this.datosPersona,
            es_mayor_edad: this.esMayorDeEdad(),
            edad_calculada: this.datosPersona?.fecha_nacimiento ? 
                this.calcularEdad(this.datosPersona.fecha_nacimiento) : null,
            codigos_activos: this.getCodigosActivos().length,
            limite_codigos: 5
        };
    }
}

// Solo exportar la clase, no crear instancia automática
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EntranceResident;
}
