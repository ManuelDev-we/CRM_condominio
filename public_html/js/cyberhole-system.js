/**
 * Sistema de Autenticación - Cyberhole Condominios
 * Conexión Frontend-Backend
 */

class CyberholeAuth {
    constructor() {
        this.apiPath = this.getApiPath();
        this.init();
    }
    
    /**
     * Obtener ruta de la API según ubicación actual
     */
    getApiPath() {
        const path = window.location.pathname;
        const protocol = window.location.protocol;
        
        // Si se está ejecutando desde file://, usar rutas relativas para XAMPP
        if (protocol === 'file:') {
            if (path.includes('/templates.html/') || path.includes('\\templates.html\\')) {
                return '../../apis/auth.php';
            }
            return '../apis/auth.php';
        }
        
        // Para servidor web (localhost o producción)
        const isLocalhost = window.location.hostname === 'localhost' || 
                           window.location.hostname === '127.0.0.1' ||
                           window.location.hostname.includes('.local');
        
        if (path.includes('/templates.html/') || path.includes('\\templates.html\\')) {
            return isLocalhost ? '../../apis/auth.php' : '/apis/auth.php';
        }
        
        return isLocalhost ? '../apis/auth.php' : '/apis/auth.php';
    }
    
    /**
     * Inicializar event listeners
     */
    init() {
        document.addEventListener('DOMContentLoaded', () => {
            this.loadHeaderFooter();
            this.setupLoginForms();
            this.setupRegisterForms();
            this.setupAdminForms();
            this.loadFormData();
        });
    }
    
    /**
     * Cargar header y footer dinámicamente
     */
    async loadHeaderFooter() {
        // Cargar header para admin
        const adminHeaderPlaceholder = document.querySelector('[data-include="admin-header"]');
        if (adminHeaderPlaceholder) {
            try {
                const response = await fetch('../header_admin.html');
                const html = await response.text();
                adminHeaderPlaceholder.outerHTML = html;
                
                // Cargar nombre del usuario desde sessionStorage
                const userStorage = sessionStorage.getItem('user');
                const user = (userStorage && userStorage !== 'undefined' && userStorage !== 'null') 
                    ? JSON.parse(userStorage) 
                    : {};
                const adminNameElement = document.querySelector('.admin-name strong');
                if (adminNameElement && user.nombres) {
                    adminNameElement.textContent = `${user.nombres} ${user.apellido1 || ''}`;
                }
            } catch (error) {
                console.error('Error cargando header admin:', error);
            }
        }
        
        // Cargar header para residentes
        const residentHeaderPlaceholder = document.querySelector('[data-include="resident-header"]');
        if (residentHeaderPlaceholder) {
            try {
                const response = await fetch('../header_resi.html');
                const html = await response.text();
                residentHeaderPlaceholder.outerHTML = html;
                
                // Cargar nombre del usuario desde sessionStorage
                const userStorage = sessionStorage.getItem('user');
                const user = (userStorage && userStorage !== 'undefined' && userStorage !== 'null') 
                    ? JSON.parse(userStorage) 
                    : {};
                const residentNameElement = document.querySelector('.resident-name strong');
                if (residentNameElement && user.nombres) {
                    residentNameElement.textContent = `${user.nombres} ${user.apellido1 || ''}`;
                }
            } catch (error) {
                console.error('Error cargando header residente:', error);
            }
        }
        
        // Cargar footer para admin
        const adminFooterPlaceholder = document.querySelector('[data-include="admin-footer"]');
        if (adminFooterPlaceholder) {
            try {
                const response = await fetch('../footer_admin.html');
                const html = await response.text();
                adminFooterPlaceholder.outerHTML = html;
            } catch (error) {
                console.error('Error cargando footer admin:', error);
            }
        }
        
        // Cargar footer para residentes
        const residentFooterPlaceholder = document.querySelector('[data-include="resident-footer"]');
        if (residentFooterPlaceholder) {
            try {
                const response = await fetch('../footer_resi.html');
                const html = await response.text();
                residentFooterPlaceholder.outerHTML = html;
            } catch (error) {
                console.error('Error cargando footer residente:', error);
            }
        }
    }
    
    /**
     * Configurar formularios de login
     */
    setupLoginForms() {
        // Login Admin
        const adminLoginForm = document.getElementById('adminLoginForm');
        if (adminLoginForm) {
            adminLoginForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                await this.handleAdminLogin(e.target);
            });
        }
        
        // Login Residente
        const residentLoginForm = document.getElementById('residentLoginForm');
        if (residentLoginForm) {
            residentLoginForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                await this.handleResidentLogin(e.target);
            });
        }
    }
    
    /**
     * Configurar formularios de registro
     */
    setupRegisterForms() {
        // Registro Admin
        const adminRegisterForm = document.getElementById('adminRegisterForm');
        if (adminRegisterForm) {
            adminRegisterForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                await this.handleAdminRegister(e.target);
            });
        }
        
        // Registro Residente
        const residentRegisterForm = document.getElementById('residentRegisterForm');
        if (residentRegisterForm) {
            residentRegisterForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                await this.handleResidentRegister(e.target);
            });
        }
    }
    
    /**
     * Cargar datos de formularios (condominios, calles, casas)
     */
    async loadFormData() {
        // Cargar condominios si existe el select - registro de residentes
        const condominioSelect = document.getElementById('id_condominio');
        if (condominioSelect) {
            await this.loadCondominios();
            
            // Event listener para cargar calles
            condominioSelect.addEventListener('change', async () => {
                if (condominioSelect.value) {
                    await this.loadCalles(condominioSelect.value);
                }
            });
        }
        
        // Cargar condominios para asignación de tareas - blog admin
        const asigCondominioSelect = document.getElementById('asig_condominio');
        if (asigCondominioSelect) {
            await this.loadCondominiosForTask();
            
            // Event listener para cargar calles en asignación
            asigCondominioSelect.addEventListener('change', async () => {
                if (asigCondominioSelect.value) {
                    await this.loadCallesForTask(asigCondominioSelect.value);
                    await this.loadEmpleadosForTask(asigCondominioSelect.value);
                }
            });
        }
        
        // Event listener para cargar casas - registro de residentes
        const calleSelect = document.getElementById('id_calle');
        if (calleSelect) {
            calleSelect.addEventListener('change', async () => {
                const condominioId = document.getElementById('id_condominio').value;
                if (condominioId) {
                    await this.loadCasas(condominioId);
                }
            });
        }
    }
    
    /**
     * Manejar login de administrador
     */
    async handleAdminLogin(form) {
        const formData = new FormData(form);
        const data = {
            email: formData.get('email'),
            password: formData.get('password')
        };
        
        try {
            const response = await fetch(`${this.apiPath}?action=login_admin`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showMessage('Login exitoso. Redirigiendo...', 'success');
                sessionStorage.setItem('user', JSON.stringify(result.data.user));
                sessionStorage.setItem('condominios', JSON.stringify(result.data.condominios));
                
                setTimeout(() => {
                    window.location.href = '../admin_template/blog.html';
                }, 1500);
            } else {
                this.showMessage(result.error || 'Error al iniciar sesión', 'error');
                console.log('Login error details:', result);
            }
        } catch (error) {
            this.showMessage('Error de conexión', 'error');
            console.error('Error:', error);
        }
    }
    
    /**
     * Manejar login de residente
     */
    async handleResidentLogin(form) {
        const formData = new FormData(form);
        const data = {
            email: formData.get('email'),
            password: formData.get('password')
        };
        
        try {
            const response = await fetch(`${this.apiPath}?action=login_resident`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showMessage('Login exitoso. Redirigiendo...', 'success');
                sessionStorage.setItem('user', JSON.stringify(result.data.user));
                sessionStorage.setItem('casa', JSON.stringify(result.data.casa));
                
                setTimeout(() => {
                    window.location.href = '../resi_template/acces.html';
                }, 1500);
            } else {
                this.showMessage(result.error || 'Error al iniciar sesión', 'error');
            }
        } catch (error) {
            this.showMessage('Error de conexión', 'error');
            console.error('Error:', error);
        }
    }
    
    /**
     * Manejar registro de administrador
     */
    async handleAdminRegister(form) {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        
        // Validar que los campos requeridos estén presentes
        if (!data.contrasena || !data.confirm_password) {
            this.showMessage('Todos los campos son requeridos', 'error');
            return;
        }
        
        // Validar que las contraseñas coincidan
        if (data.contrasena !== data.confirm_password) {
            this.showMessage('Las contraseñas no coinciden', 'error');
            return;
        }
        
        // Remover confirm_password antes de enviar
        delete data.confirm_password;
        
        try {
            const response = await fetch(`${this.apiPath}?action=register_admin`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showMessage('Registro exitoso. Puedes iniciar sesión ahora.', 'success');
                setTimeout(() => {
                    window.location.href = 'login.html';
                }, 2000);
            } else {
                this.showMessage(result.error || 'Error en el registro', 'error');
            }
        } catch (error) {
            this.showMessage('Error de conexión', 'error');
            console.error('Error:', error);
        }
    }
    
    /**
     * Manejar registro de residente
     */
    async handleResidentRegister(form) {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        
        // Validar que los campos requeridos estén presentes
        const required = ['nombres', 'apellido1', 'apellido2', 'correo_electronico', 'curp', 'contrasena'];
        for (let field of required) {
            if (!data[field]) {
                this.showMessage(`El campo ${field} es requerido`, 'error');
                return;
            }
        }
        
        // Validar CURP
        if (data.curp && data.curp.length !== 18) {
            this.showMessage('CURP debe tener exactamente 18 caracteres', 'error');
            return;
        }
        
        try {
            const response = await fetch(`${this.apiPath}?action=register_resident`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showMessage('Registro exitoso. Puedes iniciar sesión ahora.', 'success');
                setTimeout(() => {
                    window.location.href = 'login.html';
                }, 2000);
            } else {
                this.showMessage(result.error || 'Error en el registro', 'error');
            }
        } catch (error) {
            this.showMessage('Error de conexión', 'error');
            console.error('Error:', error);
        }
    }
    
    /**
     * Cargar condominios para asignación de tareas
     */
    async loadCondominiosForTask() {
        try {
            const response = await fetch(`${this.apiPath}?action=get_condominios`);
            const result = await response.json();
            
            if (result.success) {
                const select = document.getElementById('asig_condominio');
                if (select) {
                    select.innerHTML = '<option value="">Seleccionar Condominio...</option>';
                    
                    result.data.forEach(condominio => {
                        const option = document.createElement('option');
                        option.value = condominio.id_condominio;
                        option.textContent = condominio.nombre;
                        select.appendChild(option);
                    });
                }
            }
        } catch (error) {
            console.error('Error cargando condominios para tareas:', error);
            this.showMessage('Error al cargar condominios', 'error');
        }
    }
    
    /**
     * Cargar calles para asignación de tareas
     */
    async loadCallesForTask(condominioId) {
        try {
            const response = await fetch(`${this.apiPath}?action=get_calles&condominio_id=${condominioId}`);
            const result = await response.json();
            
            if (result.success) {
                const select = document.getElementById('asig_calle');
                if (select) {
                    select.innerHTML = '<option value="">Seleccionar Calle...</option>';
                    
                    result.data.forEach(calle => {
                        const option = document.createElement('option');
                        option.value = calle.id_calle;
                        option.textContent = calle.nombre;
                        select.appendChild(option);
                    });
                }
            }
        } catch (error) {
            console.error('Error cargando calles para tareas:', error);
        }
    }
    
    /**
     * Cargar empleados para asignación de tareas
     */
    async loadEmpleadosForTask(condominioId) {
        try {
            const response = await fetch(`${this.apiPath}?action=get_empleados&condominio_id=${condominioId}`);
            const result = await response.json();
            
            if (result.success) {
                const select = document.getElementById('asig_trabajador');
                if (select) {
                    select.innerHTML = '<option value="">Seleccionar Empleado...</option>';
                    
                    result.data.forEach(empleado => {
                        const option = document.createElement('option');
                        option.value = empleado.id_empleado;
                        option.textContent = `${empleado.nombres} ${empleado.apellido1} - ${empleado.puesto}`;
                        select.appendChild(option);
                    });
                }
            }
        } catch (error) {
            console.error('Error cargando empleados:', error);
            // Si no hay endpoint de empleados, mostrar mensaje
            const select = document.getElementById('asig_trabajador');
            if (select) {
                select.innerHTML = '<option value="">No hay empleados disponibles</option>';
            }
        }
    }
    
    /**
     * Cargar condominios
     */
    async loadCondominios() {
        try {
            const response = await fetch(`${this.apiPath}?action=get_condominios`);
            const result = await response.json();
            
            if (result.success) {
                const select = document.getElementById('id_condominio');
                if (select) {
                    select.innerHTML = '<option value="">Selecciona un condominio</option>';
                    
                    result.data.forEach(condominio => {
                        const option = document.createElement('option');
                        option.value = condominio.id_condominio;
                        option.textContent = condominio.nombre;
                        select.appendChild(option);
                    });
                }
            }
        } catch (error) {
            console.error('Error cargando condominios:', error);
        }
    }
    
    /**
     * Cargar calles
     */
    async loadCalles(condominioId) {
        try {
            const response = await fetch(`${this.apiPath}?action=get_calles&condominio_id=${condominioId}`);
            const result = await response.json();
            
            if (result.success) {
                const select = document.getElementById('id_calle');
                if (select) {
                    select.innerHTML = '<option value="">Selecciona una calle</option>';
                    
                    result.data.forEach(calle => {
                        const option = document.createElement('option');
                        option.value = calle.id_calle;
                        option.textContent = calle.nombre;
                        select.appendChild(option);
                    });
                }
            }
        } catch (error) {
            console.error('Error cargando calles:', error);
        }
    }
    
    /**
     * Cargar casas
     */
    async loadCasas(condominioId) {
        try {
            const response = await fetch(`${this.apiPath}?action=get_casas&condominio_id=${condominioId}`);
            const result = await response.json();
            
            if (result.success) {
                const select = document.getElementById('id_casa');
                if (select) {
                    select.innerHTML = '<option value="">Selecciona una casa</option>';
                    
                    result.data.forEach(casa => {
                        const option = document.createElement('option');
                        option.value = casa.id_casa;
                        option.textContent = `${casa.casa} - ${casa.nombre_calle}`;
                        select.appendChild(option);
                    });
                }
            }
        } catch (error) {
            console.error('Error cargando casas:', error);
        }
    }
    
    /**
     * Mostrar mensajes
     */
    showMessage(message, type = 'success', containerId = 'notification') {
        const notification = document.getElementById(containerId);
        if (notification) {
            notification.textContent = message;
            notification.className = `notification ${type}`;
            notification.style.display = 'block';
            
            setTimeout(() => {
                notification.style.display = 'none';
            }, 5000);
        } else {
            // Fallback al contenedor por defecto
            const defaultNotification = document.getElementById('notification');
            if (defaultNotification) {
                defaultNotification.textContent = message;
                defaultNotification.className = `notification ${type}`;
                defaultNotification.style.display = 'block';
                
                setTimeout(() => {
                    defaultNotification.style.display = 'none';
                }, 5000);
            } else {
                alert(message);
            }
        }
    }
    
    /**
     * Verificar sesión
     */
    async checkSession() {
        try {
            const response = await fetch(`${this.apiPath}?action=check_session`);
            const result = await response.json();
            return result.success ? result.data : null;
        } catch (error) {
            return null;
        }
    }
    
    /**
     * Cerrar sesión
     */
    async logout() {
        try {
            await fetch(`${this.apiPath}?action=logout`, { method: 'POST' });
            sessionStorage.clear();
            window.location.href = '../../index.html';
        } catch (error) {
            sessionStorage.clear();
            window.location.href = '../../index.html';
        }
    }
    
    /**
     * Configurar formularios de administración
     */
    setupAdminForms() {
        // Formulario de crear condominio
        const condominioForm = document.getElementById('createCondominioForm');
        if (condominioForm) {
            condominioForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                await this.createCondominio(new FormData(condominioForm));
            });
        }
        
        // Formulario de crear calles (múltiples)
        const calleForm = document.getElementById('createCalleForm');
        if (calleForm) {
            calleForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                await this.createCalles(new FormData(calleForm));
            });
        }
        
        // Formulario de crear casas (múltiples)
        const casaForm = document.getElementById('createCasaForm');
        if (casaForm) {
            casaForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                await this.createCasas(new FormData(casaForm));
            });
            
            // Vista previa de casas
            this.setupCasaPreview();
        }
        
        // Manejar cambios en los selects de casa
        const casaCondominioSelect = document.getElementById('casa_condominio');
        if (casaCondominioSelect) {
            casaCondominioSelect.addEventListener('change', async () => {
                if (casaCondominioSelect.value) {
                    await this.loadCallesForCasa(casaCondominioSelect.value);
                }
            });
        }
    }
    
    /**
     * Configurar vista previa de casas
     */
    setupCasaPreview() {
        const numeroInicio = document.getElementById('casa_numero_inicio');
        const cantidad = document.getElementById('casa_cantidad');
        const prefijo = document.getElementById('casa_prefijo');
        
        const updatePreview = () => {
            const inicio = parseInt(numeroInicio?.value) || 1;
            const cant = parseInt(cantidad?.value) || 1;
            const pref = prefijo?.value || '';
            
            if (cant > 0 && cant <= 500) {
                this.showCasaPreview(inicio, cant, pref);
            } else {
                this.hideCasaPreview();
            }
        };
        
        [numeroInicio, cantidad, prefijo].forEach(element => {
            if (element) {
                element.addEventListener('input', updatePreview);
            }
        });
    }
    
    /**
     * Mostrar vista previa de casas
     */
    showCasaPreview(inicio, cantidad, prefijo) {
        const preview = document.getElementById('casaPreview');
        const list = document.getElementById('casaPreviewList');
        
        if (!preview || !list) return;
        
        let html = '<strong>Se crearán ' + cantidad + ' casas:</strong><br>';
        
        // Mostrar máximo 10 ejemplos
        const maxShow = Math.min(cantidad, 10);
        for (let i = 0; i < maxShow; i++) {
            const numero = inicio + i;
            const nombre = prefijo + numero;
            html += '<span style="display: inline-block; margin: 2px 5px; padding: 2px 8px; background: #007bff; color: white; border-radius: 3px; font-size: 12px;">' + nombre + '</span>';
        }
        
        if (cantidad > 10) {
            html += '<br><em>... y ' + (cantidad - 10) + ' más</em>';
        }
        
        list.innerHTML = html;
        preview.style.display = 'block';
    }
    
    /**
     * Ocultar vista previa de casas
     */
    hideCasaPreview() {
        const preview = document.getElementById('casaPreview');
        if (preview) {
            preview.style.display = 'none';
        }
    }
    
    /**
     * Crear condominio
     */
    async createCondominio(formData) {
        try {
            // Debug: mostrar datos que se envían
            console.log('=== DEBUG CREAR CONDOMINIO ===');
            console.log('API Path:', this.apiPath);
            console.log('Datos del formulario:');
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }
            
            formData.append('action', 'create_condominio');
            
            const response = await fetch(`${this.apiPath}`, {
                method: 'POST',
                body: formData
            });
            
            console.log('Response status:', response.status);
            console.log('Response statusText:', response.statusText);
            
            // Intentar leer como texto primero para debug
            const responseText = await response.text();
            console.log('Response text:', responseText);
            
            let result;
            try {
                result = JSON.parse(responseText);
            } catch (e) {
                console.error('Error parseando JSON:', e);
                this.showMessage('Error: Respuesta inválida del servidor', 'error');
                return;
            }
            
            console.log('Response JSON:', result);
            
            if (result.success) {
                this.showMessage('Condominio creado exitosamente', 'success');
                document.getElementById('createCondominioForm').reset();
                // Recargar opciones de condominios en otros forms
                setTimeout(() => {
                    this.loadCondominios();
                }, 500);
            } else {
                this.showMessage(result.error || 'Error al crear condominio', 'error');
            }
        } catch (error) {
            console.error('Error completo:', error);
            this.showMessage('Error de conexión: ' + error.message, 'error');
        }
    }
    
    /**
     * Crear calles múltiples
     */
    async createCalles(formData) {
        try {
            this.showMessage('Procesando calles...', 'info', 'calleNotification');
            
            formData.append('action', 'create_calle');
            
            const response = await fetch(`${this.apiPath}`, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                const data = result.data;
                let mensaje = `✅ ${data.total} calle(s) creada(s) exitosamente`;
                
                if (data.errores && data.errores.length > 0) {
                    mensaje += `\n⚠️ Algunos errores: ${data.errores.join(', ')}`;
                }
                
                this.showMessage(mensaje, 'success', 'calleNotification');
                document.getElementById('createCalleForm').reset();
                
                // Recargar opciones de calles
                const condominioId = document.getElementById('calle_condominio').value;
                if (condominioId) {
                    setTimeout(() => {
                        this.loadCalles(condominioId);
                    }, 500);
                }
            } else {
                this.showMessage(result.error || 'Error al crear calles', 'error', 'calleNotification');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showMessage('Error de conexión: ' + error.message, 'error', 'calleNotification');
        }
    }
    
    /**
     * Crear casas múltiples
     */
    async createCasas(formData) {
        try {
            this.showMessage('Generando casas...', 'info', 'casaNotification');
            this.hideCasaPreview();
            
            formData.append('action', 'create_casa');
            
            const response = await fetch(`${this.apiPath}`, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                const data = result.data;
                let mensaje = `✅ ${data.total} casa(s) creada(s) exitosamente`;
                mensaje += `\n📍 Rango: ${data.rango}`;
                
                if (data.errores && data.errores.length > 0) {
                    mensaje += `\n⚠️ Algunos errores: ${data.errores.join(', ')}`;
                }
                
                this.showMessage(mensaje, 'success', 'casaNotification');
                document.getElementById('createCasaForm').reset();
                
                // Recargar opciones de casas
                const condominioId = document.getElementById('casa_condominio').value;
                if (condominioId) {
                    setTimeout(() => {
                        this.loadCasas(condominioId);
                    }, 500);
                }
            } else {
                this.showMessage(result.error || 'Error al crear casas', 'error', 'casaNotification');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showMessage('Error de conexión: ' + error.message, 'error', 'casaNotification');
        }
    }
    
    /**
     * Cargar calles para el formulario de casa
     */
    async loadCallesForCasa(condominioId) {
        try {
            const response = await fetch(`${this.apiPath}?action=get_calles&condominio_id=${condominioId}`);
            const data = await response.json();
            
            const calleSelect = document.getElementById('casa_calle');
            if (calleSelect) {
                calleSelect.innerHTML = '<option value="">Seleccionar Calle...</option>';
                
                if (data.success && data.data) {
                    data.data.forEach(calle => {
                        const option = document.createElement('option');
                        option.value = calle.id_calle;
                        option.textContent = calle.nombre;
                        calleSelect.appendChild(option);
                    });
                }
            }
        } catch (error) {
            console.error('Error al cargar calles:', error);
        }
    }
}

// Inicializar el sistema
const cyberholeAuth = new CyberholeAuth();

// Funciones globales para usar en templates
window.logout = () => cyberholeAuth.logout();
window.checkSession = () => cyberholeAuth.checkSession();
