/**
 * Conexión Frontend-Backend - Sistema de Condominios
 * Manejo de autenticación y API calls
 */

class CyberholeAPI {
    constructor() {
        this.baseURL = this.getApiPath();
    }
    
    /**
     * Obtener la ruta correcta de la API
     */
    getApiPath() {
        const currentPath = window.location.pathname;
        
        // Si estamos en templates.html, necesitamos subir 2 niveles
        if (currentPath.includes('/templates.html/')) {
            return '../../apis/';
        }
        
        // Si estamos en el root, subir 1 nivel
        return '../apis/';
    }
    
    /**
     * Realizar petición a la API
     */
    async request(endpoint, data = null, method = 'GET') {
        const url = `${this.baseURL}${endpoint}`;
        
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };
        
        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }
        
        try {
            const response = await fetch(url, options);
            const result = await response.json();
            
            if (!response.ok) {
                throw new Error(result.error || 'Error en la petición');
            }
            
            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }
    
    /**
     * Login de administrador
     */
    async loginAdmin(email, password) {
        return await this.request('auth.php?action=login_admin', {
            email,
            password
        }, 'POST');
    }
    
    /**
     * Login de residente
     */
    async loginResident(email, password) {
        return await this.request('auth.php?action=login_resident', {
            email,
            password
        }, 'POST');
    }
    
    /**
     * Registro de administrador
     */
    async registerAdmin(data) {
        return await this.request('auth.php?action=register_admin', data, 'POST');
    }
    
    /**
     * Registro de residente
     */
    async registerResident(data) {
        return await this.request('auth.php?action=register_resident', data, 'POST');
    }
    
    /**
     * Cerrar sesión
     */
    async logout() {
        return await this.request('auth.php?action=logout', null, 'POST');
    }
    
    /**
     * Verificar sesión
     */
    async checkSession() {
        return await this.request('auth.php?action=check_session');
    }
    
    /**
     * Obtener condominios
     */
    async getCondominios() {
        return await this.request('auth.php?action=get_condominios');
    }
    
    /**
     * Obtener calles
     */
    async getCalles(condominioId) {
        return await this.request(`auth.php?action=get_calles&condominio_id=${condominioId}`);
    }
    
    /**
     * Obtener casas
     */
    async getCasas(condominioId) {
        return await this.request(`auth.php?action=get_casas&condominio_id=${condominioId}`);
    }
}

// Utilidades para mostrar mensajes
class NotificationHelper {
    static show(message, type = 'success') {
        const notification = document.getElementById('notification');
        if (notification) {
            notification.textContent = message;
            notification.className = `notification ${type}`;
            notification.style.display = 'block';
            
            setTimeout(() => {
                notification.style.display = 'none';
            }, 5000);
        } else {
            // Fallback a alert si no hay elemento notification
            alert(message);
        }
    }
    
    static showError(message) {
        this.show(message, 'error');
    }
    
    static showSuccess(message) {
        this.show(message, 'success');
    }
}

// Instancia global de la API
const api = new CyberholeAPI();

// Event listeners cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    
    // Login de administrador
    const adminLoginForm = document.getElementById('adminLoginForm');
    if (adminLoginForm) {
        adminLoginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            try {
                const result = await api.loginAdmin(email, password);
                
                if (result.success) {
                    NotificationHelper.showSuccess('Login exitoso. Redirigiendo...');
                    
                    // Guardar datos de usuario en sessionStorage
                    sessionStorage.setItem('user', JSON.stringify(result.data.user));
                    sessionStorage.setItem('condominios', JSON.stringify(result.data.condominios));
                    
                    setTimeout(() => {
                        window.location.href = '../admin_template/blog.html';
                    }, 1500);
                } else {
                    NotificationHelper.showError(result.error || 'Error al iniciar sesión');
                }
            } catch (error) {
                NotificationHelper.showError('Error de conexión: ' + error.message);
            }
        });
    }
    
    // Login de residente
    const residentLoginForm = document.getElementById('residentLoginForm');
    if (residentLoginForm) {
        residentLoginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            try {
                const result = await api.loginResident(email, password);
                
                if (result.success) {
                    NotificationHelper.showSuccess('Login exitoso. Redirigiendo...');
                    
                    // Guardar datos de usuario en sessionStorage
                    sessionStorage.setItem('user', JSON.stringify(result.data.user));
                    sessionStorage.setItem('casa', JSON.stringify(result.data.casa));
                    
                    setTimeout(() => {
                        window.location.href = '../resi_template/acces.html';
                    }, 1500);
                } else {
                    NotificationHelper.showError(result.error || 'Error al iniciar sesión');
                }
            } catch (error) {
                NotificationHelper.showError('Error de conexión: ' + error.message);
            }
        });
    }
    
    // Registro de administrador
    const adminRegisterForm = document.getElementById('adminRegisterForm');
    if (adminRegisterForm) {
        adminRegisterForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(adminRegisterForm);
            const data = Object.fromEntries(formData);
            
            try {
                const result = await api.registerAdmin(data);
                
                if (result.success) {
                    NotificationHelper.showSuccess('Registro exitoso. Puedes iniciar sesión ahora.');
                    setTimeout(() => {
                        window.location.href = 'login.html';
                    }, 2000);
                } else {
                    NotificationHelper.showError(result.error || 'Error en el registro');
                }
            } catch (error) {
                NotificationHelper.showError('Error de conexión: ' + error.message);
            }
        });
    }
    
    // Registro de residente
    const residentRegisterForm = document.getElementById('residentRegisterForm');
    if (residentRegisterForm) {
        // Cargar condominios
        loadCondominios();
        
        residentRegisterForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(residentRegisterForm);
            const data = Object.fromEntries(formData);
            
            try {
                const result = await api.registerResident(data);
                
                if (result.success) {
                    NotificationHelper.showSuccess('Registro exitoso. Puedes iniciar sesión ahora.');
                    setTimeout(() => {
                        window.location.href = 'login.html';
                    }, 2000);
                } else {
                    NotificationHelper.showError(result.error || 'Error en el registro');
                }
            } catch (error) {
                NotificationHelper.showError('Error de conexión: ' + error.message);
            }
        });
    }
    
    // Manejar selección de condominio para cargar calles
    const condominioSelect = document.getElementById('id_condominio');
    if (condominioSelect) {
        condominioSelect.addEventListener('change', function() {
            const condominioId = this.value;
            if (condominioId) {
                loadCalles(condominioId);
            }
        });
    }
    
    // Manejar selección de calle para cargar casas
    const calleSelect = document.getElementById('id_calle');
    if (calleSelect) {
        calleSelect.addEventListener('change', function() {
            const condominioId = document.getElementById('id_condominio').value;
            if (condominioId) {
                loadCasas(condominioId);
            }
        });
    }
});

// Función para cargar condominios
async function loadCondominios() {
    try {
        const result = await api.getCondominios();
        
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

// Función para cargar calles
async function loadCalles(condominioId) {
    try {
        const result = await api.getCalles(condominioId);
        
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

// Función para cargar casas
async function loadCasas(condominioId) {
    try {
        const result = await api.getCasas(condominioId);
        
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

// Función para verificar sesión (útil para páginas protegidas)
async function checkUserSession() {
    try {
        const result = await api.checkSession();
        return result.success ? result.data : null;
    } catch (error) {
        return null;
    }
}

// Función para cerrar sesión
async function logout() {
    try {
        await api.logout();
        sessionStorage.clear();
        window.location.href = '../../index.html';
    } catch (error) {
        console.error('Error al cerrar sesión:', error);
        // Forzar logout local
        sessionStorage.clear();
        window.location.href = '../../index.html';
    }
}
