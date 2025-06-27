/**
 * Sistema de Residentes - Cyberhole Condominios
 * 
 * Este archivo crea un objeto residente global que encapsula
 * toda la funcionalidad disponible para residentes del sistema de condominios.
 * 
 * Funcionalidades para residentes:
 * - Login y autenticación
 * - Gestión de accesos QR (solo mayores de edad)
 * - Gestión de tags personales
 * - Gestión de engomados personales
 * - Perfil personal
 * 
 * Patrón Singleton - Solo una instancia del residente
 */

class ResidentSystem {
    constructor() {
        // Propiedades privadas
        this.#usuarioActual = null;
        this.#entranceManager = null;
        this.#tagsManager = null;
        this.#engomadosManager = null;
        this.#registroSeguro = null;
        this.#isAuthenticated = false;
        this.#initialized = false;
    }

    // Propiedades privadas
    #usuarioActual;
    #entranceManager;
    #tagsManager;
    #engomadosManager;
    #registroSeguro;
    #isAuthenticated;
    #initialized;

    /**
     * Inicializa el sistema de residentes
     */
    async init() {
        if (this.#initialized) {
            console.warn('ResidentSystem ya está inicializado');
            return;
        }

        try {
            // Verificar que el cliente API esté disponible
            if (typeof apiClient === 'undefined') {
                throw new Error('Cliente API no disponible. Asegúrate de cargar api-client.js primero.');
            }

            // Inicializar API client
            this.apiClient = apiClient;
            
            // Cargar sistema de registro
            if (typeof RegistroSeguro !== 'undefined') {
                this.#registroSeguro = new RegistroSeguro();
            }

            // Verificar si hay una sesión activa
            await this.#checkActiveSession();

            this.#initialized = true;
            console.log('ResidentSystem inicializado correctamente');
        } catch (error) {
            console.error('Error al inicializar ResidentSystem:', error);
            throw error;
        }
    }

    /**
     * Verifica si hay una sesión activa guardada
     */
    async #checkActiveSession() {
        try {
            const sessionData = localStorage.getItem('resident_session');
            if (sessionData) {
                const session = JSON.parse(sessionData);
                const ahora = new Date();
                const expiracion = new Date(session.expiracion);

                if (expiracion > ahora) {
                    // Sesión válida, restaurar usuario
                    this.#usuarioActual = session.usuario;
                    this.#isAuthenticated = true;
                    await this.#loadResidentManagers();
                    console.log('Sesión restaurada para:', this.#usuarioActual.nombre);
                } else {
                    // Sesión expirada, limpiar
                    localStorage.removeItem('resident_session');
                }
            }
        } catch (error) {
            console.error('Error al verificar sesión activa:', error);
            localStorage.removeItem('resident_session');
        }
    }

    /**
     * Carga los managers específicos del residente
     */
    async #loadResidentManagers() {
        if (!this.#usuarioActual) return;

        try {
            // Cargar manager de accesos QR
            if (typeof EntranceResident !== 'undefined') {
                this.#entranceManager = new EntranceResident(
                    this.#usuarioActual.id, 
                    this.#usuarioActual
                );
            }

            // Cargar manager de tags (si existe)
            if (typeof TagsResident !== 'undefined') {
                this.#tagsManager = new TagsResident(this.#usuarioActual.id);
            }

            // Cargar manager de engomados (si existe)
            if (typeof EngomadosResident !== 'undefined') {
                this.#engomadosManager = new EngomadosResident(this.#usuarioActual.id);
            }

        } catch (error) {
            console.error('Error al cargar managers de residente:', error);
        }
    }

    /**
     * Verifica que el usuario esté autenticado
     */
    #checkAuthenticated() {
        if (!this.#isAuthenticated || !this.#usuarioActual) {
            throw new Error('Usuario no autenticado. Debe iniciar sesión primero.');
        }
    }

    /**
     * Verifica que el sistema esté inicializado
     */
    #checkInitialized() {
        if (!this.#initialized) {
            throw new Error('ResidentSystem no está inicializado. Llama a residente.init() primero.');
        }
    }

    // ========================================
    // MÉTODOS PÚBLICOS - AUTENTICACIÓN
    // ========================================

    /**
     * Inicia sesión de residente
     */
    async login(email, password, recordarme = false) {
        this.#checkInitialized();
        
        try {
            // Intentar autenticar como residente primero (tabla personas)
            const resultadoResidente = await this.#autenticarResidente(email, password);
            
            if (resultadoResidente.success) {
                // Guardar usuario actual
                this.#usuarioActual = resultadoResidente.data;
                this.#isAuthenticated = true;

                // Cargar managers específicos del residente
                await this.#loadResidentManagers();

                // Guardar sesión si se solicita
                if (recordarme) {
                    this.#saveSession();
                }

                return {
                    success: true,
                    message: 'Inicio de sesión exitoso como residente',
                    data: {
                        nombre: this.#usuarioActual.nombre,
                        email: this.#usuarioActual.email,
                        tipo_usuario: this.#usuarioActual.tipo_usuario,
                        ultimo_acceso: this.#usuarioActual.ultimo_acceso
                    }
                };
            }

            // Si no es residente, verificar si es administrador y rechazar
            const esAdministrador = await this.#verificarEsAdministrador(email, password);
            if (esAdministrador) {
                return {
                    success: false,
                    message: 'Los administradores deben usar el panel de administración'
                };
            }

            // Si no es ni residente ni administrador
            return {
                success: false,
                message: 'Credenciales inválidas o usuario no encontrado'
            };

        } catch (error) {
            console.error('Error en login:', error);
            return {
                success: false,
                message: 'Error al iniciar sesión: ' + error.message
            };
        }
    }

    /**
     * Autentica un residente contra la tabla personas
     */
    async #autenticarResidente(email, password) {
        try {
            // Cargar personas desde localStorage
            const personas = JSON.parse(localStorage.getItem('personas') || '[]');
            
            // Buscar persona por email (desencriptando)
            if (!this.#registroSeguro) {
                throw new Error('Sistema de registro no disponible');
            }

            for (const persona of personas) {
                // Si la persona tiene email encriptado, desencriptarlo
                let emailPersona = persona.email;
                if (persona.email_encrypted) {
                    try {
                        emailPersona = await this.#registroSeguro.decryptEmail(persona.email_encrypted);
                    } catch (error) {
                        console.warn('Error al desencriptar email:', error);
                        continue;
                    }
                }

                if (emailPersona && emailPersona.toLowerCase() === email.toLowerCase()) {
                    // Verificar contraseña
                    let passwordValida = false;
                    
                    if (persona.password_hash) {
                        // Usar hash de contraseña
                        passwordValida = await this.#registroSeguro.verifyPassword(password, persona.password_hash);
                    } else if (persona.password) {
                        // Compatibilidad con contraseñas en texto plano (migrar a hash)
                        passwordValida = (persona.password === password);
                        
                        // Migrar a hash automáticamente
                        if (passwordValida) {
                            persona.password_hash = await this.#registroSeguro.hashPassword(password);
                            delete persona.password; // Eliminar contraseña en texto plano
                            localStorage.setItem('personas', JSON.stringify(personas));
                        }
                    }

                    if (passwordValida) {
                        // Actualizar último acceso
                        persona.ultimo_acceso = new Date().toISOString();
                        localStorage.setItem('personas', JSON.stringify(personas));

                        return {
                            success: true,
                            data: {
                                id: persona.id,
                                nombre: persona.nombre,
                                email: emailPersona,
                                telefono: persona.telefono,
                                tipo_usuario: persona.tipo_usuario || 'residente',
                                id_condominio: persona.id_condominio,
                                id_calle: persona.id_calle,
                                id_casa: persona.id_casa,
                                fecha_nacimiento: persona.fecha_nacimiento,
                                activo: persona.activo !== false,
                                ultimo_acceso: persona.ultimo_acceso
                            }
                        };
                    }
                }
            }

            return {
                success: false,
                message: 'Credenciales inválidas'
            };

        } catch (error) {
            console.error('Error al autenticar residente:', error);
            return {
                success: false,
                message: 'Error en la autenticación'
            };
        }
    }

    /**
     * Verifica si el email/password corresponde a un administrador
     */
    async #verificarEsAdministrador(email, password) {
        try {
            // Cargar administradores desde localStorage
            const administradores = JSON.parse(localStorage.getItem('administradores') || '[]');
            
            if (!this.#registroSeguro) {
                return false;
            }

            for (const admin of administradores) {
                // Desencriptar email del administrador
                let emailAdmin = admin.email;
                if (admin.email_encrypted) {
                    try {
                        emailAdmin = await this.#registroSeguro.decryptEmail(admin.email_encrypted);
                    } catch (error) {
                        console.warn('Error al desencriptar email de admin:', error);
                        continue;
                    }
                }

                if (emailAdmin && emailAdmin.toLowerCase() === email.toLowerCase()) {
                    // Verificar contraseña
                    if (admin.password_hash) {
                        const passwordValida = await this.#registroSeguro.verifyPassword(password, admin.password_hash);
                        return passwordValida;
                    } else if (admin.password) {
                        return admin.password === password;
                    }
                }
            }

            return false;

        } catch (error) {
            console.error('Error al verificar administrador:', error);
            return false;
        }
    }

    /**
     * Cierra sesión del residente
     */
    logout() {
        try {
            // Limpiar datos
            this.#usuarioActual = null;
            this.#isAuthenticated = false;
            this.#entranceManager = null;
            this.#tagsManager = null;
            this.#engomadosManager = null;

            // Limpiar sesión guardada
            localStorage.removeItem('resident_session');

            return {
                success: true,
                message: 'Sesión cerrada exitosamente'
            };

        } catch (error) {
            console.error('Error en logout:', error);
            return {
                success: false,
                message: 'Error al cerrar sesión'
            };
        }
    }

    /**
     * Guarda la sesión en localStorage
     */
    #saveSession() {
        try {
            const expiracion = new Date();
            expiracion.setHours(expiracion.getHours() + 24); // 24 horas

            const sessionData = {
                usuario: this.#usuarioActual,
                expiracion: expiracion.toISOString(),
                timestamp: new Date().toISOString()
            };

            localStorage.setItem('resident_session', JSON.stringify(sessionData));
        } catch (error) {
            console.error('Error al guardar sesión:', error);
        }
    }

    /**
     * Obtiene información del usuario actual
     */
    getUsuarioActual() {
        this.#checkAuthenticated();
        return {
            ...this.#usuarioActual,
            es_mayor_edad: this.#entranceManager ? this.#entranceManager.esMayorDeEdad() : false
        };
    }

    /**
     * Verifica si el usuario está autenticado
     */
    isAuthenticated() {
        return this.#isAuthenticated && this.#usuarioActual !== null;
    }

    // ========================================
    // MÉTODOS PÚBLICOS - GESTIÓN DE ACCESOS QR
    // ========================================

    /**
     * Genera un código QR de acceso (solo mayores de edad)
     */
    async generarCodigoQR(datosAcceso) {
        this.#checkAuthenticated();
        if (!this.#entranceManager) {
            throw new Error('Manager de accesos no disponible');
        }
        return await this.#entranceManager.generarCodigoQR(datosAcceso);
    }

    /**
     * Da de baja un código QR
     */
    darDeBajaQR(idEntrance, motivo = 'Cancelado por el usuario') {
        this.#checkAuthenticated();
        if (!this.#entranceManager) {
            throw new Error('Manager de accesos no disponible');
        }
        return this.#entranceManager.darDeBajaQR(idEntrance, motivo);
    }

    /**
     * Obtiene el historial de accesos
     */
    getHistorialAccesos(filtros = {}) {
        this.#checkAuthenticated();
        if (!this.#entranceManager) {
            throw new Error('Manager de accesos no disponible');
        }
        return this.#entranceManager.getHistorial(filtros);
    }

    /**
     * Obtiene el estatus de un código QR
     */
    getStatusQR(idEntrance) {
        this.#checkAuthenticated();
        if (!this.#entranceManager) {
            throw new Error('Manager de accesos no disponible');
        }
        return this.#entranceManager.getStatusQR(idEntrance);
    }

    /**
     * Obtiene códigos QR activos
     */
    getCodigosActivos() {
        this.#checkAuthenticated();
        if (!this.#entranceManager) {
            throw new Error('Manager de accesos no disponible');
        }
        return this.#entranceManager.getCodigosActivos();
    }

    /**
     * Renueva un código QR activo
     */
    renovarQR(idEntrance, horasExtension = 8) {
        this.#checkAuthenticated();
        if (!this.#entranceManager) {
            throw new Error('Manager de accesos no disponible');
        }
        return this.#entranceManager.renovarQR(idEntrance, horasExtension);
    }

    /**
     * Obtiene estadísticas de accesos del usuario
     */
    getEstadisticasAccesos() {
        this.#checkAuthenticated();
        if (!this.#entranceManager) {
            throw new Error('Manager de accesos no disponible');
        }
        return this.#entranceManager.getEstadisticas();
    }

    // ========================================
    // MÉTODOS PÚBLICOS - GESTIÓN DE TAGS
    // ========================================

    /**
     * Crea un nuevo tag personal
     */
    async crearTag(datosTag) {
        this.#checkAuthenticated();
        if (!this.#tagsManager) {
            throw new Error('Manager de tags no disponible');
        }
        return await this.#tagsManager.crearTag(datosTag);
    }

    /**
     * Obtiene los tags del residente
     */
    async getTags(filtros = {}) {
        this.#checkAuthenticated();
        if (!this.#tagsManager) {
            throw new Error('Manager de tags no disponible');
        }
        return await this.#tagsManager.getTags(filtros);
    }

    /**
     * Actualiza un tag existente
     */
    async actualizarTag(idTag, datos) {
        this.#checkAuthenticated();
        if (!this.#tagsManager) {
            throw new Error('Manager de tags no disponible');
        }
        return await this.#tagsManager.actualizarTag(idTag, datos);
    }

    /**
     * Elimina un tag
     */
    async eliminarTag(idTag) {
        this.#checkAuthenticated();
        if (!this.#tagsManager) {
            throw new Error('Manager de tags no disponible');
        }
        return await this.#tagsManager.eliminarTag(idTag);
    }

    // ========================================
    // MÉTODOS PÚBLICOS - GESTIÓN DE ENGOMADOS
    // ========================================

    /**
     * Registra un nuevo engomado
     */
    async registrarEngomado(datosEngomado) {
        this.#checkAuthenticated();
        if (!this.#engomadosManager) {
            throw new Error('Manager de engomados no disponible');
        }
        return await this.#engomadosManager.registrarEngomado(datosEngomado);
    }

    /**
     * Obtiene los engomados del residente
     */
    async getEngomados(filtros = {}) {
        this.#checkAuthenticated();
        if (!this.#engomadosManager) {
            throw new Error('Manager de engomados no disponible');
        }
        return await this.#engomadosManager.getEngomados(filtros);
    }

    /**
     * Actualiza un engomado existente
     */
    async actualizarEngomado(idEngomado, datos) {
        this.#checkAuthenticated();
        if (!this.#engomadosManager) {
            throw new Error('Manager de engomados no disponible');
        }
        return await this.#engomadosManager.actualizarEngomado(idEngomado, datos);
    }

    /**
     * Elimina un engomado
     */
    async eliminarEngomado(idEngomado) {
        this.#checkAuthenticated();
        if (!this.#engomadosManager) {
            throw new Error('Manager de engomados no disponible');
        }
        return await this.#engomadosManager.eliminarEngomado(idEngomado);
    }

    /**
     * Solicita aprobación para un engomado
     */
    async solicitarAprobacionEngomado(idEngomado) {
        this.#checkAuthenticated();
        if (!this.#engomadosManager) {
            throw new Error('Manager de engomados no disponible');
        }
        return await this.#engomadosManager.solicitarAprobacion(idEngomado);
    }

    // ========================================
    // MÉTODOS PÚBLICOS - PERFIL PERSONAL
    // ========================================

    /**
     * Actualiza información del perfil
     */
    async actualizarPerfil(datosNuevos) {
        this.#checkAuthenticated();
        
        try {
            // Validar que solo se actualicen campos permitidos
            const camposPermitidos = ['nombre', 'telefono'];
            const datosLimpios = {};
            
            camposPermitidos.forEach(campo => {
                if (datosNuevos[campo] !== undefined) {
                    datosLimpios[campo] = datosNuevos[campo];
                }
            });

            if (Object.keys(datosLimpios).length === 0) {
                return {
                    success: false,
                    message: 'No hay datos válidos para actualizar'
                };
            }

            // Actualizar datos locales
            Object.assign(this.#usuarioActual, datosLimpios);

            // Guardar en localStorage (simulando actualización en base de datos)
            const usuarios = JSON.parse(localStorage.getItem('usuarios_registrados') || '[]');
            const indiceUsuario = usuarios.findIndex(u => u.id === this.#usuarioActual.id);
            
            if (indiceUsuario !== -1) {
                Object.assign(usuarios[indiceUsuario], datosLimpios);
                localStorage.setItem('usuarios_registrados', JSON.stringify(usuarios));
            }

            // Actualizar sesión
            this.#saveSession();

            return {
                success: true,
                message: 'Perfil actualizado exitosamente',
                data: this.#usuarioActual
            };

        } catch (error) {
            console.error('Error al actualizar perfil:', error);
            return {
                success: false,
                message: 'Error al actualizar perfil: ' + error.message
            };
        }
    }

    /**
     * Cambia la contraseña del usuario
     */
    async cambiarContrasena(passwordActual, passwordNueva) {
        this.#checkAuthenticated();
        if (!this.#registroSeguro) {
            throw new Error('Sistema de registro no disponible');
        }
        
        return await this.#registroSeguro.cambiarContrasena(
            this.#usuarioActual.email, 
            passwordActual, 
            passwordNueva
        );
    }

    // ========================================
    // MÉTODOS UTILITARIOS
    // ========================================

    /**
     * Obtiene información del estado del sistema
     */
    getSystemInfo() {
        return {
            initialized: this.#initialized,
            authenticated: this.#isAuthenticated,
            usuario: this.#usuarioActual ? {
                nombre: this.#usuarioActual.nombre,
                email: this.#usuarioActual.email,
                tipo_usuario: this.#usuarioActual.tipo_usuario
            } : null,
            managers: {
                accesos: !!this.#entranceManager,
                tags: !!this.#tagsManager,
                engomados: !!this.#engomadosManager,
                registro: !!this.#registroSeguro
            }
        };
    }

    /**
     * Reinicia el sistema completo
     */
    async restart() {
        // Cerrar sesión si está autenticado
        if (this.#isAuthenticated) {
            this.logout();
        }

        // Reinicializar
        this.#initialized = false;
        await this.init();
    }

    /**
     * Obtiene tipos de QR disponibles
     */
    getTiposQR() {
        this.#checkAuthenticated();
        if (!this.#entranceManager) {
            throw new Error('Manager de accesos no disponible');
        }
        return this.#entranceManager.TIPOS_QR;
    }

    /**
     * Verifica si el usuario puede generar QR (es mayor de edad)
     */
    puedeGenerarQR() {
        this.#checkAuthenticated();
        if (!this.#entranceManager) {
            return false;
        }
        return this.#entranceManager.esMayorDeEdad();
    }

    // ========================================
    // MÉTODOS PÚBLICOS - API DIRECTA
    // ========================================

    /**
     * Obtener datos del dashboard de residente
     */
    async getDashboardData() {
        this.#checkAuthenticated();
        try {
            return await this.apiClient.getResidentDashboard();
        } catch (error) {
            console.error('Error al obtener datos del dashboard:', error);
            throw error;
        }
    }

    /**
     * Obtener perfil del residente actual
     */
    async getMyProfile() {
        this.#checkAuthenticated();
        return await this.apiClient.getMyProfile();
    }

    /**
     * Actualizar perfil del residente actual
     */
    async updateMyProfile(data) {
        this.#checkAuthenticated();
        const validationRules = {
            nombre: { required: false, type: 'string', minLength: 3 },
            telefono: { required: false, type: 'string', minLength: 10 },
            email: { required: false, type: 'email' }
        };
        
        const errors = this.apiClient.validateData(data, validationRules);
        if (errors) {
            throw new Error('Datos inválidos: ' + JSON.stringify(errors));
        }
        
        return await this.apiClient.updateMyProfile(data);
    }

    /**
     * Obtener accesos del residente actual
     */
    async getMyAccesses() {
        this.#checkAuthenticated();
        return await this.apiClient.getMyAccesses();
    }

    /**
     * Crear nuevo acceso QR
     */
    async createAccess(data) {
        this.#checkAuthenticated();
        const validationRules = {
            tipo: { required: true, type: 'string' },
            nombre_visitante: { required: false, type: 'string', minLength: 3 },
            motivo: { required: false, type: 'string', minLength: 5 },
            fecha_expiracion: { required: false, type: 'string' }
        };
        
        const errors = this.apiClient.validateData(data, validationRules);
        if (errors) {
            throw new Error('Datos inválidos: ' + JSON.stringify(errors));
        }
        
        return await this.apiClient.createAccess(data);
    }

    /**
     * Obtener pagos del residente actual
     */
    async getMyPayments() {
        this.#checkAuthenticated();
        return await this.apiClient.getMyPayments();
    }

    /**
     * Obtener incidencias del residente actual
     */
    async getMyIncidencias() {
        this.#checkAuthenticated();
        return await this.apiClient.getMyIncidencias();
    }

    /**
     * Crear nueva incidencia
     */
    async createMyIncidencia(data) {
        this.#checkAuthenticated();
        const validationRules = {
            titulo: { required: true, type: 'string', minLength: 3 },
            descripcion: { required: true, type: 'string', minLength: 10 },
            tipo: { required: true, type: 'string' },
            prioridad: { required: false, type: 'string' }
        };
        
        const errors = this.apiClient.validateData(data, validationRules);
        if (errors) {
            throw new Error('Datos inválidos: ' + JSON.stringify(errors));
        }
        
        return await this.apiClient.createMyIncidencia(data);
    }

    /**
     * Obtener avisos públicos
     */
    async getAnnouncements() {
        this.#checkAuthenticated();
        return await this.apiClient.getAvisos();
    }

    /**
     * Subir archivo
     */
    async uploadFile(file, type = 'resident') {
        this.#checkAuthenticated();
        return await this.apiClient.uploadFile(file, type);
    }

    /**
     * Manejo de errores con notificaciones
     */
    handleError(error, context = 'Operación') {
        console.error(`Error en ${context}:`, error);
        
        let message = 'Error desconocido';
        if (error.message) {
            message = error.message;
        } else if (error.data && error.data.message) {
            message = error.data.message;
        }
        
        // Mostrar notificación al usuario
        this.showNotification(message, 'error');
        
        return {
            success: false,
            error: message,
            details: error
        };
    }

    /**
     * Mostrar notificación al usuario
     */
    showNotification(message, type = 'info') {
        // Crear notificación visual
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        
        // Estilos básicos
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            z-index: 9999;
            max-width: 300px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        `;
        
        // Colores según tipo
        switch (type) {
            case 'error':
                notification.style.backgroundColor = '#dc3545';
                break;
            case 'success':
                notification.style.backgroundColor = '#28a745';
                break;
            case 'warning':
                notification.style.backgroundColor = '#ffc107';
                notification.style.color = '#212529';
                break;
            default:
                notification.style.backgroundColor = '#17a2b8';
        }
        
        document.body.appendChild(notification);
        
        // Remover después de 5 segundos
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 5000);
    }
}

// Crear la instancia global del residente
const residente = new ResidentSystem();

// Hacer disponible globalmente
if (typeof window !== 'undefined') {
    window.residente = residente;
}

// Para módulos CommonJS/Node.js
if (typeof module !== 'undefined' && module.exports) {
    module.exports = residente;
}

// Inicializar automáticamente cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', async () => {
    try {
        await residente.init();
        console.log('Sistema de residentes listo para usar');
        console.log('Usa la variable global "residente" para acceder a todas las funcionalidades');
        
        // Mostrar información del sistema en consola
        console.log('Estado del sistema:', residente.getSystemInfo());
        
        // Si hay una sesión activa, mostrar información
        if (residente.isAuthenticated()) {
            console.log('Sesión activa detectada para:', residente.getUsuarioActual().nombre);
        }
    } catch (error) {
        console.error('Error al inicializar el sistema de residentes:', error);
    }
});