/**
 * Sistema de Administración Global - Cyberhole Condominios
 * 
 * Este archivo crea un objeto administrador global que encapsula
 * toda la funcionalidad de administración del sistema de condominios.
 * 
 * Todas las clases de objeto_admin son importadas y sus funcionalidades
 * son expuestas a través de métodos públicos del objeto admin.
 * 
 * Patrón Singleton - Solo una instancia del administrador
 */

class AdminSystem {
    constructor() {
        // Instancias privadas de todas las clases de administración
        this.#empleadosManager = null;
        this.#condominiosManager = null;
        this.#callesManager = null;
        this.#casasManager = null;
        this.#personasManager = null;
        this.#taskManager = null;
        this.#tagsManager = null;
        this.#engomadosManager = null;
        this.#entranceManager = null;
        this.#registroSeguro = null;

        this.#initialized = false;
    }

    // Propiedades privadas
    #empleadosManager;
    #condominiosManager;
    #callesManager;
    #casasManager;
    #personasManager;
    #taskManager;
    #tagsManager;
    #engomadosManager;
    #entranceManager;
    #registroSeguro;
    #initialized;

    /**
     * Inicializa todas las clases de administración
     */
    async init() {
        if (this.#initialized) {
            console.warn('AdminSystem ya está inicializado');
            return;
        }

        try {
            // Cargar todas las clases de administración
            await this.#loadManagers();
            this.#initialized = true;
            console.log('AdminSystem inicializado correctamente');
        } catch (error) {
            console.error('Error al inicializar AdminSystem:', error);
            throw error;
        }
    }

    /**
     * Carga todas las clases de administración
     */
    async #loadManagers() {
        // Verificar que el cliente API esté disponible
        if (typeof apiClient === 'undefined') {
            throw new Error('Cliente API no disponible. Asegúrate de cargar api-client.js primero.');
        }

        // Inicializar API client
        this.apiClient = apiClient;
        
        // Verificar autenticación
        if (!this.apiClient.isAuthenticated()) {
            throw new Error('Usuario no autenticado. Debes iniciar sesión primero.');
        }

        // Cargar managers con conexión a API
        this.#empleadosManager = new EmpleadosManager(this.apiClient);
        this.#condominiosManager = new CondominioManager(this.apiClient);
        this.#callesManager = new CallesManager(this.apiClient);
        this.#casasManager = new CasasManager(this.apiClient);
        this.#personasManager = new Personas(this.apiClient);
        this.#taskManager = new TaskManager(this.apiClient);
        this.#tagsManager = new TagsAdmin(this.apiClient);
        this.#engomadosManager = new EngomadasAdmin(this.apiClient);
        
        if (typeof EntranceManager !== 'undefined') {
            this.#entranceManager = new EntranceManager();
        }
        
        if (typeof RegistroSeguro !== 'undefined') {
            this.#registroSeguro = new RegistroSeguro();
        }
    }

    /**
     * Verifica que el sistema esté inicializado
     */
    #checkInitialized() {
        if (!this.#initialized) {
            throw new Error('AdminSystem no está inicializado. Llama a admin.init() primero.');
        }
    }

    // ========================================
    // MÉTODOS PÚBLICOS - EMPLEADOS
    // ========================================

    /**
     * Inicializa la interfaz de empleados
     */
    initEmpleados() {
        this.#checkInitialized();
        if (this.#empleadosManager) {
            return this.#empleadosManager.init();
        }
        throw new Error('EmpleadosManager no disponible');
    }

    /**
     * Registra un nuevo empleado
     */
    async registrarEmpleado(datos) {
        this.#checkInitialized();
        if (this.#empleadosManager) {
            return await this.#empleadosManager.registrarEmpleado(datos);
        }
        throw new Error('EmpleadosManager no disponible');
    }

    /**
     * Actualiza un empleado existente
     */
    async actualizarEmpleado(id, datos) {
        this.#checkInitialized();
        if (this.#empleadosManager) {
            return await this.#empleadosManager.actualizarEmpleado(id, datos);
        }
        throw new Error('EmpleadosManager no disponible');
    }

    /**
     * Elimina un empleado
     */
    async eliminarEmpleado(id) {
        this.#checkInitialized();
        if (this.#empleadosManager) {
            return await this.#empleadosManager.eliminarEmpleado(id);
        }
        throw new Error('EmpleadosManager no disponible');
    }

    /**
     * Obtiene la lista de empleados
     */
    async getEmpleados(filtros = {}) {
        this.#checkInitialized();
        if (this.#empleadosManager) {
            return await this.#empleadosManager.getEmpleados(filtros);
        }
        throw new Error('EmpleadosManager no disponible');
    }

    /**
     * Obtiene estadísticas de empleados
     */
    async getEstadisticasEmpleados() {
        this.#checkInitialized();
        if (this.#empleadosManager) {
            return await this.#empleadosManager.getEstadisticas();
        }
        throw new Error('EmpleadosManager no disponible');
    }

    // ========================================
    // MÉTODOS PÚBLICOS - CONDOMINIOS
    // ========================================

    /**
     * Inicializa la interfaz de condominios
     */
    initCondominios() {
        this.#checkInitialized();
        if (this.#condominiosManager) {
            return this.#condominiosManager.init();
        }
        throw new Error('CondominiosManager no disponible');
    }

    /**
     * Registra un nuevo condominio
     */
    async registrarCondominio(datos) {
        this.#checkInitialized();
        if (this.#condominiosManager) {
            return await this.#condominiosManager.registrarCondominio(datos);
        }
        throw new Error('CondominiosManager no disponible');
    }

    /**
     * Obtiene la lista de condominios
     */
    async getCondominios() {
        this.#checkInitialized();
        if (this.#condominiosManager) {
            return await this.#condominiosManager.getCondominios();
        }
        throw new Error('CondominiosManager no disponible');
    }

    // Métodos para gestión de condominios
    static async getAllCondominios() {
        return await this.makeRequest('/api/admin/condominios', 'GET');
    }
    
    static async createCondominio(data) {
        return await this.makeRequest('/api/admin/condominios', 'POST', data);
    }
    
    static async updateCondominio(id, data) {
        return await this.makeRequest(`/api/admin/condominios/${id}`, 'PUT', data);
    }
    
    static async deleteCondominio(id) {
        return await this.makeRequest(`/api/admin/condominios/${id}`, 'DELETE');
    }
    
    // ========================================
    // MÉTODOS PÚBLICOS - CALLES
    // ========================================

    /**
     * Inicializa la interfaz de calles
     */
    initCalles() {
        this.#checkInitialized();
        if (this.#callesManager) {
            return this.#callesManager.init();
        }
        throw new Error('CallesManager no disponible');
    }

    /**
     * Registra una nueva calle
     */
    async registrarCalle(datos) {
        this.#checkInitialized();
        if (this.#callesManager) {
            return await this.#callesManager.registrarCalle(datos);
        }
        throw new Error('CallesManager no disponible');
    }

    /**
     * Obtiene las calles de un condominio
     */
    async getCalles(idCondominio) {
        this.#checkInitialized();
        if (this.#callesManager) {
            return await this.#callesManager.getCalles(idCondominio);
        }
        throw new Error('CallesManager no disponible');
    }

    // ========================================
    // MÉTODOS PÚBLICOS - CASAS
    // ========================================

    /**
     * Inicializa la interfaz de casas
     */
    initCasas() {
        this.#checkInitialized();
        if (this.#casasManager) {
            return this.#casasManager.init();
        }
        throw new Error('CasasManager no disponible');
    }

    /**
     * Registra una nueva casa
     */
    async registrarCasa(datos) {
        this.#checkInitialized();
        if (this.#casasManager) {
            return await this.#casasManager.registrarCasa(datos);
        }
        throw new Error('CasasManager no disponible');
    }

    /**
     * Obtiene las casas de una calle
     */
    async getCasas(idCalle) {
        this.#checkInitialized();
        if (this.#casasManager) {
            return await this.#casasManager.getCasas(idCalle);
        }
        throw new Error('CasasManager no disponible');
    }

    // ========================================
    // MÉTODOS PÚBLICOS - PERSONAS
    // ========================================

    /**
     * Registra una nueva persona
     */
    registrarPersona(nombre, email, telefono, idCondominio, idCalle, idCasa, tipoUsuario = 'residente') {
        this.#checkInitialized();
        if (this.#personasManager) {
            return this.#personasManager.registrarPersona(nombre, email, telefono, idCondominio, idCalle, idCasa, tipoUsuario);
        }
        throw new Error('PersonasManager no disponible');
    }

    /**
     * Actualiza una persona
     */
    actualizarPersona(id, datos) {
        this.#checkInitialized();
        if (this.#personasManager) {
            return this.#personasManager.actualizarPersona(id, datos);
        }
        throw new Error('PersonasManager no disponible');
    }

    /**
     * Elimina una persona
     */
    eliminarPersona(id) {
        this.#checkInitialized();
        if (this.#personasManager) {
            return this.#personasManager.eliminarPersona(id);
        }
        throw new Error('PersonasManager no disponible');
    }

    /**
     * Obtiene la lista de personas con filtros
     */
    getPersonas(filtros = {}) {
        this.#checkInitialized();
        if (this.#personasManager) {
            return this.#personasManager.getPersonas(filtros);
        }
        throw new Error('PersonasManager no disponible');
    }

    // ========================================
    // MÉTODOS PÚBLICOS - TAREAS
    // ========================================

    /**
     * Inicializa la interfaz de tareas
     */
    initTareas() {
        this.#checkInitialized();
        if (this.#taskManager) {
            return this.#taskManager.init();
        }
        throw new Error('TaskManager no disponible');
    }

    /**
     * Crea una nueva tarea
     */
    async crearTarea(datos) {
        this.#checkInitialized();
        if (this.#taskManager) {
            return await this.#taskManager.crearTarea(datos);
        }
        throw new Error('TaskManager no disponible');
    }

    /**
     * Asigna trabajadores a una tarea
     */
    async asignarTrabajadores(idTarea, trabajadores) {
        this.#checkInitialized();
        if (this.#taskManager) {
            return await this.#taskManager.asignarTrabajadores(idTarea, trabajadores);
        }
        throw new Error('TaskManager no disponible');
    }

    /**
     * Obtiene la lista de tareas
     */
    async getTareas(filtros = {}) {
        this.#checkInitialized();
        if (this.#taskManager) {
            return await this.#taskManager.getTareas(filtros);
        }
        throw new Error('TaskManager no disponible');
    }

    // Métodos para gestión de tareas
    static async getAllTareas() {
        return await this.makeRequest('/api/admin/tareas', 'GET');
    }
    
    static async createTarea(data) {
        return await this.makeRequest('/api/admin/tareas', 'POST', data);
    }
    
    static async updateTarea(id, data) {
        return await this.makeRequest(`/api/admin/tareas/${id}`, 'PUT', data);
    }
    
    static async deleteTarea(id) {
        return await this.makeRequest(`/api/admin/tareas/${id}`, 'DELETE');
    }
    
    // ========================================
    // MÉTODOS PÚBLICOS - TAGS
    // ========================================

    /**
     * Inicializa la interfaz de tags
     */
    initTags() {
        this.#checkInitialized();
        if (this.#tagsManager) {
            return this.#tagsManager.init();
        }
        throw new Error('TagsManager no disponible');
    }

    /**
     * Crea un nuevo tag
     */
    async crearTag(datos) {
        this.#checkInitialized();
        if (this.#tagsManager) {
            return await this.#tagsManager.crearTag(datos);
        }
        throw new Error('TagsManager no disponible');
    }

    /**
     * Obtiene la lista de tags
     */
    async getTags(filtros = {}) {
        this.#checkInitialized();
        if (this.#tagsManager) {
            return await this.#tagsManager.getTags(filtros);
        }
        throw new Error('TagsManager no disponible');
    }

    // ========================================
    // MÉTODOS PÚBLICOS - ENGOMADOS
    // ========================================

    /**
     * Inicializa la interfaz de engomados
     */
    initEngomados() {
        this.#checkInitialized();
        if (this.#engomadosManager) {
            return this.#engomadosManager.init();
        }
        throw new Error('EngomadosManager no disponible');
    }

    /**
     * Crea un nuevo engomado
     */
    async crearEngomado(datos) {
        this.#checkInitialized();
        if (this.#engomadosManager) {
            return await this.#engomadosManager.crearEngomado(datos);
        }
        throw new Error('EngomadosManager no disponible');
    }

    /**
     * Obtiene la lista de engomados
     */
    async getEngomados(filtros = {}) {
        this.#checkInitialized();
        if (this.#engomadosManager) {
            return await this.#engomadosManager.getEngomados(filtros);
        }
        throw new Error('EngomadosManager no disponible');
    }

    // ========================================
    // MÉTODOS PÚBLICOS - ACCESOS QR
    // ========================================

    /**
     * Inicializa la interfaz de accesos
     */
    initAccesos() {
        this.#checkInitialized();
        if (this.#entranceManager) {
            return this.#entranceManager.init();
        }
        throw new Error('EntranceManager no disponible');
    }

    /**
     * Crea un nuevo código QR de acceso
     */
    async crearCodigoQR(datos) {
        this.#checkInitialized();
        if (this.#entranceManager) {
            return await this.#entranceManager.crearCodigoQR(datos);
        }
        throw new Error('EntranceManager no disponible');
    }

    /**
     * Valida un código QR de acceso
     */
    async validarAcceso(codigoQR) {
        this.#checkInitialized();
        if (this.#entranceManager) {
            return await this.#entranceManager.validarAcceso(codigoQR);
        }
        throw new Error('EntranceManager no disponible');
    }

    /**
     * Obtiene el historial de accesos
     */
    async getHistorialAccesos(filtros = {}) {
        this.#checkInitialized();
        if (this.#entranceManager) {
            return await this.#entranceManager.getHistorial(filtros);
        }
        throw new Error('EntranceManager no disponible');
    }

    // ========================================
    // MÉTODOS PÚBLICOS - REGISTRO Y AUTENTICACIÓN
    // ========================================

    /**
     * Registra un nuevo usuario con encriptación de email y hash de contraseña
     */
    async registrarUsuario(datosUsuario) {
        this.#checkInitialized();
        if (this.#registroSeguro) {
            return await this.#registroSeguro.registrarUsuario(datosUsuario);
        }
        throw new Error('RegistroSeguro no disponible');
    }

    /**
     * Autentica un usuario con email y contraseña
     */
    async autenticarUsuario(email, password) {
        this.#checkInitialized();
        if (this.#registroSeguro) {
            return await this.#registroSeguro.autenticarUsuario(email, password);
        }
        throw new Error('RegistroSeguro no disponible');
    }

    /**
     * Cambia la contraseña de un usuario
     */
    async cambiarContrasena(email, passwordActual, passwordNueva) {
        this.#checkInitialized();
        if (this.#registroSeguro) {
            return await this.#registroSeguro.cambiarContrasena(email, passwordActual, passwordNueva);
        }
        throw new Error('RegistroSeguro no disponible');
    }

    /**
     * Obtiene usuarios de un condominio (solo para administradores)
     * Los emails se desencriptan automáticamente para administradores
     */
    async getUsuariosPorCondominio(idCondominio, esAdmin = false) {
        this.#checkInitialized();
        if (this.#registroSeguro) {
            return await this.#registroSeguro.getUsuariosPorCondominio(idCondominio, esAdmin);
        }
        throw new Error('RegistroSeguro no disponible');
    }

    /**
     * Obtiene estadísticas de usuarios de un condominio (solo para administradores)
     */
    async getEstadisticasUsuarios(idCondominio, esAdmin = false) {
        this.#checkInitialized();
        if (this.#registroSeguro) {
            return await this.#registroSeguro.getEstadisticasUsuarios(idCondominio, esAdmin);
        }
        throw new Error('RegistroSeguro no disponible');
    }

    /**
     * Busca un usuario por email (desencripta automáticamente)
     */
    async buscarUsuarioPorEmail(email) {
        this.#checkInitialized();
        if (this.#registroSeguro) {
            return await this.#registroSeguro.findUserByEmail(email);
        }
        throw new Error('RegistroSeguro no disponible');
    }

    /**
     * Verifica si existe un usuario con el email dado
     */
    async verificarEmailExiste(email) {
        this.#checkInitialized();
        if (this.#registroSeguro) {
            return await this.#registroSeguro.checkEmailExists(email);
        }
        throw new Error('RegistroSeguro no disponible');
    }

    /**
     * Valida formato de email
     */
    validarEmail(email) {
        this.#checkInitialized();
        if (this.#registroSeguro) {
            return this.#registroSeguro.validateEmail(email);
        }
        throw new Error('RegistroSeguro no disponible');
    }

    /**
     * Valida fortaleza de contraseña
     */
    validarContrasena(password) {
        this.#checkInitialized();
        if (this.#registroSeguro) {
            return this.#registroSeguro.validatePassword(password);
        }
        throw new Error('RegistroSeguro no disponible');
    }

    /**
     * Encripta un email (solo para uso interno del sistema)
     */
    async encriptarEmail(email) {
        this.#checkInitialized();
        if (this.#registroSeguro) {
            return await this.#registroSeguro.encryptEmail(email);
        }
        throw new Error('RegistroSeguro no disponible');
    }

    /**
     * Desencripta un email (solo para administradores)
     */
    async desencriptarEmail(encryptedEmail) {
        this.#checkInitialized();
        if (this.#registroSeguro) {
            return await this.#registroSeguro.decryptEmail(encryptedEmail);
        }
        throw new Error('RegistroSeguro no disponible');
    }

    /**
     * Genera hash de contraseña (solo para uso interno del sistema)
     */
    async hashearContrasena(password) {
        this.#checkInitialized();
        if (this.#registroSeguro) {
            return await this.#registroSeguro.hashPassword(password);
        }
        throw new Error('RegistroSeguro no disponible');
    }

    /**
     * Verifica contraseña contra hash (solo para uso interno del sistema)
     */
    async verificarContrasena(password, hash) {
        this.#checkInitialized();
        if (this.#registroSeguro) {
            return await this.#registroSeguro.verifyPassword(password, hash);
        }
        throw new Error('RegistroSeguro no disponible');
    }

    // ========================================
    // MÉTODOS PÚBLICOS - GESTIÓN DE ADMINISTRADORES
    // ========================================

    /**
     * Autentica un administrador
     */
    async autenticarAdministrador(email, password) {
        this.#checkInitialized();
        if (this.#registroSeguro) {
            return await this.#registroSeguro.autenticarAdministrador(email, password);
        }
        throw new Error('RegistroSeguro no disponible');
    }

    /**
     * Registra un nuevo administrador
     */
    async registrarAdministrador(datosAdmin) {
        this.#checkInitialized();
        if (this.#registroSeguro) {
            // Forzar tipo de usuario a administrador
            datosAdmin.tipo_usuario = 'administrador';
            return await this.#registroSeguro.registrarUsuario(datosAdmin);
        }
        throw new Error('RegistroSeguro no disponible');
    }

    /**
     * Obtiene la lista de administradores de un condominio
     */
    async getAdministradores(idCondominio) {
        this.#checkInitialized();
        try {
            const administradores = JSON.parse(localStorage.getItem('administradores') || '[]');
            let adminsFiltrados = administradores;

            if (idCondominio) {
                adminsFiltrados = administradores.filter(a => a.id_condominio === parseInt(idCondominio));
            }

            // Desencriptar emails para mostrar
            if (this.#registroSeguro) {
                const adminsDesencriptados = await Promise.all(
                    adminsFiltrados.map(async (admin) => {
                        const email = await this.#registroSeguro.decryptEmail(admin.email_encrypted);
                        return {
                            ...admin,
                            email,
                            email_encrypted: undefined,
                            password_hash: undefined
                        };
                    })
                );

                return {
                    success: true,
                    data: adminsDesencriptados,
                    total: adminsDesencriptados.length
                };
            }

            return {
                success: false,
                message: 'Sistema de registro no disponible'
            };

        } catch (error) {
            console.error('Error al obtener administradores:', error);
            return {
                success: false,
                message: 'Error al obtener administradores'
            };
        }
    }

    /**
     * Actualiza permisos de un administrador
     */
    async actualizarPermisosAdmin(idAdmin, nuevosPermisos) {
        this.#checkInitialized();
        try {
            const administradores = JSON.parse(localStorage.getItem('administradores') || '[]');
            const adminIndex = administradores.findIndex(a => a.id === parseInt(idAdmin));

            if (adminIndex === -1) {
                return {
                    success: false,
                    message: 'Administrador no encontrado'
                };
            }

            // Actualizar permisos
            administradores[adminIndex].permisos = {
                ...administradores[adminIndex].permisos,
                ...nuevosPermisos
            };

            localStorage.setItem('administradores', JSON.stringify(administradores));

            return {
                success: true,
                message: 'Permisos actualizados exitosamente',
                data: administradores[adminIndex].permisos
            };

        } catch (error) {
            console.error('Error al actualizar permisos:', error);
            return {
                success: false,
                message: 'Error al actualizar permisos'
            };
        }
    }

    /**
     * Desactiva un administrador
     */
    async desactivarAdministrador(idAdmin) {
        this.#checkInitialized();
        try {
            const administradores = JSON.parse(localStorage.getItem('administradores') || '[]');
            const adminIndex = administradores.findIndex(a => a.id === parseInt(idAdmin));

            if (adminIndex === -1) {
                return {
                    success: false,
                    message: 'Administrador no encontrado'
                };
            }

            administradores[adminIndex].activo = false;
            localStorage.setItem('administradores', JSON.stringify(administradores));

            return {
                success: true,
                message: 'Administrador desactivado exitosamente'
            };

        } catch (error) {
            console.error('Error al desactivar administrador:', error);
            return {
                success: false,
                message: 'Error al desactivar administrador'
            };
        }
    }

    /**
     * Obtiene información del estado del sistema
     */
    getSystemInfo() {
        return {
            initialized: this.#initialized,
            managers: {
                empleados: !!this.#empleadosManager,
                condominios: !!this.#condominiosManager,
                calles: !!this.#callesManager,
                casas: !!this.#casasManager,
                personas: !!this.#personasManager,
                tareas: !!this.#taskManager,
                tags: !!this.#tagsManager,
                engomados: !!this.#engomadosManager,
                accesos: !!this.#entranceManager
            }
        };
    }

    // ========================================
    // MÉTODOS PÚBLICOS - API DIRECTA
    // ========================================

    /**
     * Obtener datos del dashboard
     */
    async getDashboardData() {
        this.#checkInitialized();
        try {
            return await this.apiClient.getAdminDashboard();
        } catch (error) {
            console.error('Error al obtener datos del dashboard:', error);
            throw error;
        }
    }

    /**
     * Métodos CRUD para Condominios
     */
    async getAllCondominios() {
        this.#checkInitialized();
        return await this.apiClient.getCondominios();
    }

    async getCondominio(id) {
        this.#checkInitialized();
        return await this.apiClient.getCondominio(id);
    }

    async createCondominio(data) {
        this.#checkInitialized();
        // Validar datos
        const validationRules = {
            nombre: { required: true, type: 'string', minLength: 3 },
            direccion: { required: true, type: 'string', minLength: 10 },
            telefono: { required: false, type: 'string', minLength: 10 },
            email: { required: false, type: 'email' }
        };
        
        const errors = this.apiClient.validateData(data, validationRules);
        if (errors) {
            throw new Error('Datos inválidos: ' + JSON.stringify(errors));
        }
        
        return await this.apiClient.createCondominio(data);
    }

    async updateCondominio(id, data) {
        this.#checkInitialized();
        return await this.apiClient.updateCondominio(id, data);
    }

    async deleteCondominio(id) {
        this.#checkInitialized();
        return await this.apiClient.deleteCondominio(id);
    }

    /**
     * Métodos CRUD para Trabajadores
     */
    async getAllTrabajadores() {
        this.#checkInitialized();
        return await this.apiClient.getTrabajadores();
    }

    async getTrabajador(id) {
        this.#checkInitialized();
        return await this.apiClient.getTrabajador(id);
    }

    async createTrabajador(data) {
        this.#checkInitialized();
        const validationRules = {
            nombre: { required: true, type: 'string', minLength: 3 },
            email: { required: true, type: 'email' },
            telefono: { required: false, type: 'string', minLength: 10 },
            puesto: { required: true, type: 'string' },
            salario: { required: false, type: 'numeric' }
        };
        
        const errors = this.apiClient.validateData(data, validationRules);
        if (errors) {
            throw new Error('Datos inválidos: ' + JSON.stringify(errors));
        }
        
        return await this.apiClient.createTrabajador(data);
    }

    async updateTrabajador(id, data) {
        this.#checkInitialized();
        return await this.apiClient.updateTrabajador(id, data);
    }

    async deleteTrabajador(id) {
        this.#checkInitialized();
        return await this.apiClient.deleteTrabajador(id);
    }

    /**
     * Métodos CRUD para Tareas
     */
    async getAllTareas() {
        this.#checkInitialized();
        return await this.apiClient.getTareas();
    }

    async getTarea(id) {
        this.#checkInitialized();
        return await this.apiClient.getTarea(id);
    }

    async createTarea(data) {
        this.#checkInitialized();
        const validationRules = {
            titulo: { required: true, type: 'string', minLength: 3 },
            descripcion: { required: true, type: 'string', minLength: 10 },
            prioridad: { required: true, type: 'string' },
            fecha_limite: { required: false, type: 'string' },
            trabajador_id: { required: false, type: 'numeric' }
        };
        
        const errors = this.apiClient.validateData(data, validationRules);
        if (errors) {
            throw new Error('Datos inválidos: ' + JSON.stringify(errors));
        }
        
        return await this.apiClient.createTarea(data);
    }

    async updateTarea(id, data) {
        this.#checkInitialized();
        return await this.apiClient.updateTarea(id, data);
    }

    async deleteTarea(id) {
        this.#checkInitialized();
        return await this.apiClient.deleteTarea(id);
    }

    async updateTareaStatus(id, status) {
        this.#checkInitialized();
        return await this.apiClient.updateTareaStatus(id, status);
    }

    /**
     * Métodos CRUD para Pagos
     */
    async getAllPagos() {
        this.#checkInitialized();
        return await this.apiClient.getPagos();
    }

    async getPago(id) {
        this.#checkInitialized();
        return await this.apiClient.getPago(id);
    }

    async createPago(data) {
        this.#checkInitialized();
        const validationRules = {
            residente_id: { required: true, type: 'numeric' },
            monto: { required: true, type: 'numeric' },
            concepto: { required: true, type: 'string', minLength: 3 },
            fecha_vencimiento: { required: false, type: 'string' }
        };
        
        const errors = this.apiClient.validateData(data, validationRules);
        if (errors) {
            throw new Error('Datos inválidos: ' + JSON.stringify(errors));
        }
        
        return await this.apiClient.createPago(data);
    }

    async updatePago(id, data) {
        this.#checkInitialized();
        return await this.apiClient.updatePago(id, data);
    }

    async deletePago(id) {
        this.#checkInitialized();
        return await this.apiClient.deletePago(id);
    }

    /**
     * Métodos CRUD para Incidencias
     */
    async getAllIncidencias() {
        this.#checkInitialized();
        return await this.apiClient.getIncidencias();
    }

    async getIncidencia(id) {
        this.#checkInitialized();
        return await this.apiClient.getIncidencia(id);
    }

    async createIncidencia(data) {
        this.#checkInitialized();
        const validationRules = {
            titulo: { required: true, type: 'string', minLength: 3 },
            descripcion: { required: true, type: 'string', minLength: 10 },
            prioridad: { required: true, type: 'string' },
            tipo: { required: true, type: 'string' }
        };
        
        const errors = this.apiClient.validateData(data, validationRules);
        if (errors) {
            throw new Error('Datos inválidos: ' + JSON.stringify(errors));
        }
        
        return await this.apiClient.createIncidencia(data);
    }

    async updateIncidencia(id, data) {
        this.#checkInitialized();
        return await this.apiClient.updateIncidencia(id, data);
    }

    async deleteIncidencia(id) {
        this.#checkInitialized();
        return await this.apiClient.deleteIncidencia(id);
    }

    /**
     * Métodos CRUD para Avisos
     */
    async getAllAvisos() {
        this.#checkInitialized();
        return await this.apiClient.getAvisos();
    }

    async getAviso(id) {
        this.#checkInitialized();
        return await this.apiClient.getAviso(id);
    }

    async createAviso(data) {
        this.#checkInitialized();
        const validationRules = {
            titulo: { required: true, type: 'string', minLength: 3 },
            contenido: { required: true, type: 'string', minLength: 10 },
            tipo: { required: true, type: 'string' },
            fecha_publicacion: { required: false, type: 'string' }
        };
        
        const errors = this.apiClient.validateData(data, validationRules);
        if (errors) {
            throw new Error('Datos inválidos: ' + JSON.stringify(errors));
        }
        
        return await this.apiClient.createAviso(data);
    }

    async updateAviso(id, data) {
        this.#checkInitialized();
        return await this.apiClient.updateAviso(id, data);
    }

    async deleteAviso(id) {
        this.#checkInitialized();
        return await this.apiClient.deleteAviso(id);
    }

    /**
     * Métodos CRUD para Calles
     */
    async getAllCalles() {
        this.#checkInitialized();
        return await this.apiClient.getCalles();
    }

    async getCalle(id) {
        this.#checkInitialized();
        return await this.apiClient.getCalle(id);
    }

    async createCalle(data) {
        this.#checkInitialized();
        const validationRules = {
            nombre: { required: true, type: 'string', minLength: 3 },
            condominio_id: { required: true, type: 'numeric' }
        };
        
        const errors = this.apiClient.validateData(data, validationRules);
        if (errors) {
            throw new Error('Datos inválidos: ' + JSON.stringify(errors));
        }
        
        return await this.apiClient.createCalle(data);
    }

    async updateCalle(id, data) {
        this.#checkInitialized();
        return await this.apiClient.updateCalle(id, data);
    }

    async deleteCalle(id) {
        this.#checkInitialized();
        return await this.apiClient.deleteCalle(id);
    }

    /**
     * Métodos CRUD para Casas
     */
    async getAllCasas() {
        this.#checkInitialized();
        return await this.apiClient.getCasas();
    }

    async getCasa(id) {
        this.#checkInitialized();
        return await this.apiClient.getCasa(id);
    }

    async createCasa(data) {
        this.#checkInitialized();
        const validationRules = {
            numero: { required: true, type: 'string', minLength: 1 },
            calle_id: { required: true, type: 'numeric' }
        };
        
        const errors = this.apiClient.validateData(data, validationRules);
        if (errors) {
            throw new Error('Datos inválidos: ' + JSON.stringify(errors));
        }
        
        return await this.apiClient.createCasa(data);
    }

    async updateCasa(id, data) {
        this.#checkInitialized();
        return await this.apiClient.updateCasa(id, data);
    }

    async deleteCasa(id) {
        this.#checkInitialized();
        return await this.apiClient.deleteCasa(id);
    }

    /**
     * Métodos CRUD para Residentes
     */
    async getAllResidentes() {
        this.#checkInitialized();
        return await this.apiClient.getResidentes();
    }

    async getResidente(id) {
        this.#checkInitialized();
        return await this.apiClient.getResidente(id);
    }

    async createResidente(data) {
        this.#checkInitialized();
        const validationRules = {
            nombre: { required: true, type: 'string', minLength: 3 },
            email: { required: true, type: 'email' },
            telefono: { required: false, type: 'string', minLength: 10 },
            casa_id: { required: true, type: 'numeric' }
        };
        
        const errors = this.apiClient.validateData(data, validationRules);
        if (errors) {
            throw new Error('Datos inválidos: ' + JSON.stringify(errors));
        }
        
        return await this.apiClient.createResidente(data);
    }

    async updateResidente(id, data) {
        this.#checkInitialized();
        return await this.apiClient.updateResidente(id, data);
    }

    async deleteResidente(id) {
        this.#checkInitialized();
        return await this.apiClient.deleteResidente(id);
    }

    /**
     * Métodos de utilidad para la interfaz
     */
    async uploadFile(file, type = 'general') {
        this.#checkInitialized();
        return await this.apiClient.uploadFile(file, type);
    }

    async getSystemConfig() {
        this.#checkInitialized();
        return await this.apiClient.getSystemConfig();
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

    /**
     * Reinicia el sistema completo
     */
    async restart() {
        this.#initialized = false;
        this.#empleadosManager = null;
        this.#condominiosManager = null;
        this.#callesManager = null;
        this.#casasManager = null;
        this.#personasManager = null;
        this.#taskManager = null;
        this.#tagsManager = null;
        this.#engomadosManager = null;
        this.#entranceManager = null;
        this.#registroSeguro = null;
        
        await this.init();
    }
}

// Crear la instancia global del administrador
const admin = new AdminSystem();

// Hacer disponible globalmente
if (typeof window !== 'undefined') {
    window.admin = admin;
}

// Para módulos CommonJS/Node.js
if (typeof module !== 'undefined' && module.exports) {
    module.exports = admin;
}

// Inicializar automáticamente cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', async () => {
    try {
        await admin.init();
        console.log('Sistema de administración listo para usar');
        console.log('Usa la variable global "admin" para acceder a todas las funcionalidades');
        
        // Mostrar información del sistema en consola
        console.log('Estado del sistema:', admin.getSystemInfo());
    } catch (error) {
        console.error('Error al inicializar el sistema de administración:', error);
    }
});
