/**
 * Clase para gestionar accesos (entrance) con generación de QR
 * Incluye control de entrada/salida, expiración de 8 horas y uso único
 */
class EntranceManager {
    constructor() {
        this.accesos = [];
        this.nextId = 1;
        this.EXPIRATION_HOURS = 8; // 8 horas de expiración
        this.STATUS = {
            ACTIVO: 'activo',
            USADO: 'usado',
            EXPIRADO: 'expirado',
            ANULADO: 'anulado'
        };
        this.initializeData();
    }

    /**
     * Inicializa datos de prueba
     */
    initializeData() {
        // Datos de prueba (en producción vendrían de la BD)
        this.accesos = [
            {
                id_entrance: 1,
                id_persona: 1,
                id_condominio: 1,
                id_calle: 1,
                id_casa: 1,
                nombre_accesante: "Juan Pérez",
                fecha_creacion: new Date('2025-06-26T10:00:00'),
                hora_creacion: '10:00:00',
                qr_code: '',
                status: this.STATUS.ACTIVO,
                entrada: null,
                salida: null,
                created_at: new Date('2025-06-26T10:00:00'),
                updated_at: new Date('2025-06-26T10:00:00')
            }
        ];
        this.nextId = 2;
        this.generateQRForExisting();
    }

    /**
     * Genera QR para accesos existentes
     */
    generateQRForExisting() {
        this.accesos.forEach(acceso => {
            if (!acceso.qr_code) {
                acceso.qr_code = this.generateQRData(acceso);
            }
        });
    }

    /**
     * Genera los datos del QR
     */
    generateQRData(acceso) {
        const qrData = {
            fecha_creacion: acceso.fecha_creacion.toISOString().split('T')[0],
            id_entrance: acceso.id_entrance,
            id_persona: acceso.id_persona,
            id_condominio: acceso.id_condominio,
            id_calle: acceso.id_calle,
            id_casa: acceso.id_casa,
            nombre_accesante: acceso.nombre_accesante,
            hora_creacion: acceso.hora_creacion
        };
        return JSON.stringify(qrData);
    }

    /**
     * Crea un nuevo acceso con QR
     */
    crearAcceso(data) {
        try {
            // Validar datos requeridos
            const camposRequeridos = ['id_persona', 'id_condominio', 'id_calle', 'id_casa', 'nombre_accesante'];
            for (let campo of camposRequeridos) {
                if (!data[campo]) {
                    throw new Error(`El campo ${campo} es requerido`);
                }
            }

            const now = new Date();
            const nuevoAcceso = {
                id_entrance: this.nextId++,
                id_persona: parseInt(data.id_persona),
                id_condominio: parseInt(data.id_condominio),
                id_calle: parseInt(data.id_calle),
                id_casa: parseInt(data.id_casa),
                nombre_accesante: data.nombre_accesante.trim(),
                fecha_creacion: now,
                hora_creacion: now.toTimeString().split(' ')[0],
                qr_code: '',
                status: this.STATUS.ACTIVO,
                entrada: null,
                salida: null,
                created_at: now,
                updated_at: now
            };

            // Generar QR
            nuevoAcceso.qr_code = this.generateQRData(nuevoAcceso);

            this.accesos.push(nuevoAcceso);

            return {
                success: true,
                data: nuevoAcceso,
                message: 'Acceso creado exitosamente con código QR'
            };
        } catch (error) {
            return {
                success: false,
                message: error.message
            };
        }
    }

    /**
     * Registra entrada usando QR
     */
    registrarEntrada(qrData) {
        try {
            let acceso;
            
            // Si qrData es string, parsearlo
            if (typeof qrData === 'string') {
                qrData = JSON.parse(qrData);
            }

            // Buscar acceso por ID
            acceso = this.accesos.find(a => a.id_entrance === qrData.id_entrance);
            
            if (!acceso) {
                return {
                    success: false,
                    message: 'Acceso no encontrado'
                };
            }

            // Verificar si está expirado
            if (this.isExpired(acceso)) {
                acceso.status = this.STATUS.EXPIRADO;
                return {
                    success: false,
                    message: 'El acceso ha expirado (más de 8 horas desde su creación)'
                };
            }

            // Verificar si ya fue usado para entrada
            if (acceso.entrada && !acceso.salida) {
                return {
                    success: false,
                    message: 'Este QR ya fue utilizado para entrada. Debe ser usado para salida.'
                };
            }

            // Verificar si ya fue completamente usado (entrada y salida)
            if (acceso.status === this.STATUS.USADO) {
                return {
                    success: false,
                    message: 'Este QR ya fue utilizado completamente y no puede reutilizarse'
                };
            }

            // Registrar entrada
            const now = new Date();
            acceso.entrada = now;
            acceso.status = this.STATUS.ACTIVO;
            acceso.updated_at = now;

            return {
                success: true,
                data: acceso,
                message: `Entrada registrada para ${acceso.nombre_accesante}`
            };
        } catch (error) {
            return {
                success: false,
                message: 'Error al procesar el QR: ' + error.message
            };
        }
    }

    /**
     * Registra salida usando QR
     */
    registrarSalida(qrData) {
        try {
            let acceso;
            
            // Si qrData es string, parsearlo
            if (typeof qrData === 'string') {
                qrData = JSON.parse(qrData);
            }

            // Buscar acceso por ID
            acceso = this.accesos.find(a => a.id_entrance === qrData.id_entrance);
            
            if (!acceso) {
                return {
                    success: false,
                    message: 'Acceso no encontrado'
                };
            }

            // Verificar si no tiene entrada registrada
            if (!acceso.entrada) {
                return {
                    success: false,
                    message: 'No se puede registrar salida sin entrada previa'
                };
            }

            // Verificar si ya tiene salida registrada
            if (acceso.salida) {
                return {
                    success: false,
                    message: 'Ya se registró la salida para este acceso'
                };
            }

            // Registrar salida
            const now = new Date();
            acceso.salida = now;
            acceso.status = this.STATUS.USADO;
            acceso.updated_at = now;

            return {
                success: true,
                data: acceso,
                message: `Salida registrada para ${acceso.nombre_accesante}`
            };
        } catch (error) {
            return {
                success: false,
                message: 'Error al procesar el QR: ' + error.message
            };
        }
    }

    /**
     * Verifica si un acceso está expirado (más de 8 horas)
     */
    isExpired(acceso) {
        const now = new Date();
        const creationTime = new Date(acceso.fecha_creacion);
        const diffHours = (now - creationTime) / (1000 * 60 * 60);
        return diffHours > this.EXPIRATION_HOURS;
    }

    /**
     * Anula un acceso manualmente
     */
    anularAcceso(id_entrance) {
        try {
            const acceso = this.accesos.find(a => a.id_entrance === id_entrance);
            
            if (!acceso) {
                return {
                    success: false,
                    message: 'Acceso no encontrado'
                };
            }

            acceso.status = this.STATUS.ANULADO;
            acceso.updated_at = new Date();

            return {
                success: true,
                data: acceso,
                message: 'Acceso anulado exitosamente'
            };
        } catch (error) {
            return {
                success: false,
                message: error.message
            };
        }
    }

    /**
     * Actualiza estados de accesos expirados
     */
    actualizarEstadosExpirados() {
        let actualizados = 0;
        
        this.accesos.forEach(acceso => {
            if (acceso.status === this.STATUS.ACTIVO && this.isExpired(acceso)) {
                acceso.status = this.STATUS.EXPIRADO;
                acceso.updated_at = new Date();
                actualizados++;
            }
        });

        return {
            success: true,
            message: `${actualizados} accesos marcados como expirados`
        };
    }

    /**
     * Obtiene todos los accesos
     */
    getAll() {
        this.actualizarEstadosExpirados();
        return this.accesos.map(acceso => ({
            ...acceso,
            tiempo_transcurrido: this.getTiempoTranscurrido(acceso),
            tiempo_restante: this.getTiempoRestante(acceso),
            puede_entrar: this.puedeEntrar(acceso),
            puede_salir: this.puedeSalir(acceso)
        }));
    }

    /**
     * Obtiene un acceso por ID
     */
    getById(id) {
        this.actualizarEstadosExpirados();
        const acceso = this.accesos.find(a => a.id_entrance === parseInt(id));
        if (!acceso) return null;

        return {
            ...acceso,
            tiempo_transcurrido: this.getTiempoTranscurrido(acceso),
            tiempo_restante: this.getTiempoRestante(acceso),
            puede_entrar: this.puedeEntrar(acceso),
            puede_salir: this.puedeSalir(acceso)
        };
    }

    /**
     * Busca accesos por criterios
     */
    buscar(criterio) {
        this.actualizarEstadosExpirados();
        const termino = criterio.toLowerCase();
        
        return this.accesos.filter(acceso => 
            acceso.nombre_accesante.toLowerCase().includes(termino) ||
            acceso.id_entrance.toString().includes(termino) ||
            acceso.status.toLowerCase().includes(termino)
        );
    }

    /**
     * Obtiene estadísticas de accesos
     */
    getEstadisticas() {
        this.actualizarEstadosExpirados();
        
        const total = this.accesos.length;
        const activos = this.accesos.filter(a => a.status === this.STATUS.ACTIVO).length;
        const usados = this.accesos.filter(a => a.status === this.STATUS.USADO).length;
        const expirados = this.accesos.filter(a => a.status === this.STATUS.EXPIRADO).length;
        const anulados = this.accesos.filter(a => a.status === this.STATUS.ANULADO).length;
        const con_entrada = this.accesos.filter(a => a.entrada).length;
        const con_salida = this.accesos.filter(a => a.salida).length;
        const en_interior = this.accesos.filter(a => a.entrada && !a.salida).length;

        return {
            total,
            por_estado: {
                activos,
                usados,
                expirados,
                anulados
            },
            movimientos: {
                con_entrada,
                con_salida,
                en_interior
            },
            porcentajes: {
                activos: total > 0 ? ((activos / total) * 100).toFixed(1) : 0,
                usados: total > 0 ? ((usados / total) * 100).toFixed(1) : 0,
                expirados: total > 0 ? ((expirados / total) * 100).toFixed(1) : 0,
                anulados: total > 0 ? ((anulados / total) * 100).toFixed(1) : 0
            }
        };
    }

    /**
     * Exporta accesos a CSV
     */
    exportarCSV() {
        this.actualizarEstadosExpirados();
        
        const headers = [
            'ID', 'Persona ID', 'Condominio ID', 'Calle ID', 'Casa ID',
            'Nombre Accesante', 'Fecha Creación', 'Hora Creación',
            'Estado', 'Entrada', 'Salida', 'Tiempo Transcurrido'
        ];

        const rows = this.accesos.map(acceso => [
            acceso.id_entrance,
            acceso.id_persona,
            acceso.id_condominio,
            acceso.id_calle,
            acceso.id_casa,
            acceso.nombre_accesante,
            acceso.fecha_creacion.toISOString().split('T')[0],
            acceso.hora_creacion,
            acceso.status,
            acceso.entrada ? acceso.entrada.toLocaleString() : '',
            acceso.salida ? acceso.salida.toLocaleString() : '',
            this.getTiempoTranscurrido(acceso)
        ]);

        let csv = headers.join(',') + '\n';
        csv += rows.map(row => row.map(cell => `"${cell}"`).join(',')).join('\n');

        return csv;
    }

    /**
     * Filtra accesos por múltiples criterios
     */
    filtrar(filtros) {
        this.actualizarEstadosExpirados();
        
        let resultado = [...this.accesos];

        if (filtros.status) {
            resultado = resultado.filter(a => a.status === filtros.status);
        }

        if (filtros.condominio) {
            resultado = resultado.filter(a => a.id_condominio === parseInt(filtros.condominio));
        }

        if (filtros.fecha_desde) {
            const desde = new Date(filtros.fecha_desde);
            resultado = resultado.filter(a => a.fecha_creacion >= desde);
        }

        if (filtros.fecha_hasta) {
            const hasta = new Date(filtros.fecha_hasta);
            hasta.setHours(23, 59, 59, 999);
            resultado = resultado.filter(a => a.fecha_creacion <= hasta);
        }

        if (filtros.nombre) {
            const nombre = filtros.nombre.toLowerCase();
            resultado = resultado.filter(a => 
                a.nombre_accesante.toLowerCase().includes(nombre)
            );
        }

        return resultado;
    }

    // Métodos auxiliares
    getTiempoTranscurrido(acceso) {
        const now = new Date();
        const creacion = new Date(acceso.fecha_creacion);
        const diffMs = now - creacion;
        const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
        const diffMinutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
        return `${diffHours}h ${diffMinutes}m`;
    }

    getTiempoRestante(acceso) {
        if (this.isExpired(acceso)) return '0h 0m';
        
        const now = new Date();
        const creacion = new Date(acceso.fecha_creacion);
        const expiracion = new Date(creacion.getTime() + (this.EXPIRATION_HOURS * 60 * 60 * 1000));
        const diffMs = expiracion - now;
        
        if (diffMs <= 0) return '0h 0m';
        
        const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
        const diffMinutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
        return `${diffHours}h ${diffMinutes}m`;
    }

    puedeEntrar(acceso) {
        return acceso.status === this.STATUS.ACTIVO && 
               !this.isExpired(acceso) && 
               !acceso.entrada;
    }

    puedeSalir(acceso) {
        return acceso.entrada && 
               !acceso.salida && 
               acceso.status !== this.STATUS.ANULADO;
    }

    /**
     * Genera URL del QR visual (usando API externa)
     */
    generarQRVisual(acceso) {
        const qrData = encodeURIComponent(acceso.qr_code);
        return `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${qrData}`;
    }

    /**
     * Procesa QR escaneado (simulación)
     */
    procesarQRScaneado(qrCode, accion = 'entrada') {
        if (accion === 'entrada') {
            return this.registrarEntrada(qrCode);
        } else if (accion === 'salida') {
            return this.registrarSalida(qrCode);
        } else {
            return {
                success: false,
                message: 'Acción no válida. Use "entrada" o "salida"'
            };
        }
    }
}

// Solo exportar la clase, no crear instancia automática
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EntranceManager;
}