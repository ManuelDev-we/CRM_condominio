/**
 * Utilidades para manejo seguro de sessionStorage
 * Previene errores como "undefined" is not valid JSON
 */

class StorageUtils {
    /**
     * Obtener datos del sessionStorage de forma segura
     * @param {string} key - Clave del sessionStorage
     * @param {any} defaultValue - Valor por defecto si no existe o es inválido
     * @returns {any} - Datos parseados o valor por defecto
     */
    static getItem(key, defaultValue = null) {
        try {
            const item = sessionStorage.getItem(key);
            
            // Verificar si el item existe y no es undefined/null como string
            if (!item || item === 'undefined' || item === 'null') {
                return defaultValue;
            }
            
            return JSON.parse(item);
        } catch (error) {
            console.warn(`Error al parsear sessionStorage['${key}']:`, error);
            return defaultValue;
        }
    }
    
    /**
     * Guardar datos en sessionStorage de forma segura
     * @param {string} key - Clave del sessionStorage
     * @param {any} value - Valor a guardar
     * @returns {boolean} - true si se guardó correctamente
     */
    static setItem(key, value) {
        try {
            if (value === undefined || value === null) {
                sessionStorage.removeItem(key);
                return true;
            }
            
            sessionStorage.setItem(key, JSON.stringify(value));
            return true;
        } catch (error) {
            console.error(`Error al guardar sessionStorage['${key}']:`, error);
            return false;
        }
    }
    
    /**
     * Eliminar item del sessionStorage
     * @param {string} key - Clave a eliminar
     */
    static removeItem(key) {
        try {
            sessionStorage.removeItem(key);
        } catch (error) {
            console.error(`Error al eliminar sessionStorage['${key}']:`, error);
        }
    }
    
    /**
     * Limpiar todo el sessionStorage
     */
    static clear() {
        try {
            sessionStorage.clear();
        } catch (error) {
            console.error('Error al limpiar sessionStorage:', error);
        }
    }
    
    /**
     * Verificar si una clave existe y tiene datos válidos
     * @param {string} key - Clave a verificar
     * @returns {boolean} - true si existe y es válida
     */
    static hasValidData(key) {
        const item = sessionStorage.getItem(key);
        return item && item !== 'undefined' && item !== 'null' && item !== '{}' && item !== '[]';
    }
    
    /**
     * Obtener información del usuario actual de forma segura
     * @returns {object} - Datos del usuario o objeto vacío
     */
    static getCurrentUser() {
        return this.getItem('user', {});
    }
    
    /**
     * Obtener condominios del usuario actual
     * @returns {array} - Array de condominios o array vacío
     */
    static getCurrentCondominios() {
        return this.getItem('condominios', []);
    }
    
    /**
     * Obtener casa del usuario actual (para residentes)
     * @returns {object} - Datos de la casa o null
     */
    static getCurrentCasa() {
        return this.getItem('casa', null);
    }
    
    /**
     * Verificar si hay una sesión válida
     * @returns {boolean} - true si hay sesión válida
     */
    static hasValidSession() {
        const user = this.getCurrentUser();
        return user && (user.id_admin || user.id_persona) && user.nombres;
    }
    
    /**
     * Obtener tipo de usuario actual
     * @returns {string} - 'admin', 'resident' o 'guest'
     */
    static getUserType() {
        const user = this.getCurrentUser();
        if (user.id_admin) return 'admin';
        if (user.id_persona) return 'resident';
        return 'guest';
    }
    
    /**
     * Debug: Mostrar estado actual del sessionStorage
     */
    static debugInfo() {
        console.group('🔍 SessionStorage Debug Info');
        console.log('User:', this.getCurrentUser());
        console.log('Condominios:', this.getCurrentCondominios());
        console.log('Casa:', this.getCurrentCasa());
        console.log('Valid Session:', this.hasValidSession());
        console.log('User Type:', this.getUserType());
        console.log('Raw sessionStorage:');
        for (let i = 0; i < sessionStorage.length; i++) {
            const key = sessionStorage.key(i);
            console.log(`  ${key}:`, sessionStorage.getItem(key));
        }
        console.groupEnd();
    }
}

// Hacer disponible globalmente
window.StorageUtils = StorageUtils;
