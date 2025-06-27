/**
 * Gestión de Condominios - Sistema Web Completo
 * Permite registrar, eliminar y gestionar condominios y toda su información relacionada
 * Basado en la estructura de BD: condominios, calles, casas, empleados, personas, etc.
 */

class CondominioManager {
    constructor() {
        this.apiUrl = '../php/'; // Ruta a los archivos PHP
        this.condominios = new Map();
        this.loadCondominios();
    }

    /**
     * Inicializa la interfaz web
     */
    init() {
        this.setupEventListeners();
        this.loadCondominiosList();
        this.setupValidation();
    }

    /**
     * Configura los event listeners
     */
    setupEventListeners() {
        // Formulario de registro
        const formRegistro = document.getElementById('form-condominio');
        if (formRegistro) {
            formRegistro.addEventListener('submit', (e) => {
                e.preventDefault();
                this.registrarCondominio();
            });
        }

        // Botón de eliminar
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-eliminar-condominio')) {
                const id = e.target.dataset.condominioId;
                this.mostrarConfirmacionEliminar(id);
            }
        });

        // Botón de editar
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-editar-condominio')) {
                const id = e.target.dataset.condominioId;
                this.editarCondominio(id);
            }
        });

        // Búsqueda en tiempo real
        const searchInput = document.getElementById('search-condominios');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.filtrarCondominios(e.target.value);
            });
        }
    }

    /**
     * Registra un nuevo condominio
     */
    async registrarCondominio() {
        try {
            const formData = this.getFormData();
            
            // Validar datos
            if (!this.validarDatos(formData)) {
                return;
            }

            this.showLoading(true);

            const response = await fetch(this.apiUrl + 'condominios_save.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();

            if (result.success) {
                this.showMessage('Condominio registrado exitosamente', 'success');
                this.clearForm();
                this.loadCondominiosList();
            } else {
                this.showMessage('Error: ' + result.message, 'error');
            }

        } catch (error) {
            this.showMessage('Error de conexión: ' + error.message, 'error');
        } finally {
            this.showLoading(false);
        }
    }

    /**
     * Elimina un condominio y toda su información relacionada
     */
    async eliminarCondominio(id) {
        try {
            this.showLoading(true);

            const response = await fetch(this.apiUrl + 'condominios_delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id_condominio: id })
            });

            const result = await response.json();

            if (result.success) {
                this.showMessage('Condominio eliminado exitosamente', 'success');
                this.loadCondominiosList();
                this.closeModal();
            } else {
                this.showMessage('Error: ' + result.message, 'error');
            }

        } catch (error) {
            this.showMessage('Error de conexión: ' + error.message, 'error');
        } finally {
            this.showLoading(false);
        }
    }

    /**
     * Obtiene todos los condominios o filtrados por criterios
     * @param {Object} filtros - Objeto con filtros opcionales
     * @returns {Array} Array de condominios
     */
    async getAllCondominios(filtros = {}) {
        try {
            let url = this.apiUrl + 'condominios_list.php';
            const params = new URLSearchParams();

            // Aplicar filtros si se proporcionan
            if (filtros.busqueda) {
                params.append('busqueda', filtros.busqueda);
            }
            if (filtros.activo !== undefined) {
                params.append('activo', filtros.activo);
            }
            if (filtros.limite) {
                params.append('limite', filtros.limite);
            }
            if (filtros.offset) {
                params.append('offset', filtros.offset);
            }

            if (params.toString()) {
                url += '?' + params.toString();
            }

            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const condominios = await response.json();
            
            // Actualizar cache local
            this.condominios.clear();
            condominios.forEach(condominio => {
                this.condominios.set(condominio.id_condominio, condominio);
            });
            
            return condominios;
        } catch (error) {
            console.error('Error obteniendo condominios:', error);
            this.showMessage('Error al obtener condominios: ' + error.message, 'error');
            return [];
        }
    }

    /**
     * Obtiene un condominio por su ID
     * @param {number} id - ID del condominio
     * @returns {Object|null} Objeto condominio o null si no existe
     */
    async getCondominioById(id) {
        try {
            const response = await fetch(this.apiUrl + `condominio_by_id.php?id=${id}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const condominio = await response.json();
            
            if (condominio) {
                // Actualizar cache local
                this.condominios.set(condominio.id_condominio, condominio);
            }
            
            return condominio;
        } catch (error) {
            console.error('Error obteniendo condominio por ID:', error);
            this.showMessage('Error al obtener condominio: ' + error.message, 'error');
            return null;
        }
    }

    /**
     * Busca condominios por término de búsqueda
     * @param {string} termino - Término de búsqueda
     * @returns {Array} Array de condominios que coinciden
     */
    async searchCondominios(termino) {
        return await this.getAllCondominios({ busqueda: termino });
    }

    /**
     * Obtiene condominios activos únicamente
     * @returns {Array} Array de condominios activos
     */
    async getCondominiosActivos() {
        return await this.getAllCondominios({ activo: true });
    }

    /**
     * Obtiene estadísticas de condominios
     * @returns {Object} Objeto con estadísticas
     */
    async getEstadisticasCondominios() {
        try {
            const condominios = await this.getAllCondominios();
            
            const estadisticas = {
                total: condominios.length,
                activos: condominios.filter(c => c.activo !== false).length,
                inactivos: condominios.filter(c => c.activo === false).length,
                conCalles: 0,
                conCasas: 0,
                conEmpleados: 0
            };

            // Obtener estadísticas adicionales si están disponibles
            for (const condominio of condominios) {
                if (condominio.total_calles > 0) estadisticas.conCalles++;
                if (condominio.total_casas > 0) estadisticas.conCasas++;
                if (condominio.total_empleados > 0) estadisticas.conEmpleados++;
            }

            return estadisticas;
        } catch (error) {
            console.error('Error obteniendo estadísticas de condominios:', error);
            return null;
        }
    }

    /**
     * Exporta condominios filtrados a JSON
     * @param {Object} filtros - Filtros a aplicar
     * @returns {string} JSON de condominios
     */
    async exportarCondominios(filtros = {}) {
        const condominios = await this.getAllCondominios(filtros);
        return JSON.stringify(condominios, null, 2);
    }

    /**
     * Elimina un condominio y toda su información relacionada
     */
    async eliminarCondominio(id) {
        try {
            this.showLoading(true);

            const response = await fetch(this.apiUrl + 'condominios_delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id_condominio: id })
            });

            const result = await response.json();

            if (result.success) {
                this.showMessage('Condominio eliminado exitosamente', 'success');
                this.loadCondominiosList();
                this.closeModal();
            } else {
                this.showMessage('Error: ' + result.message, 'error');
            }

        } catch (error) {
            this.showMessage('Error de conexión: ' + error.message, 'error');
        } finally {
            this.showLoading(false);
        }
    }

    /**
     * Obtiene datos del formulario
     */
    getFormData() {
        return {
            nombre: document.getElementById('nombre-condominio')?.value.trim(),
            direccion: document.getElementById('direccion-condominio')?.value.trim(),
            descripcion: document.getElementById('descripcion-condominio')?.value.trim(),
            telefono: document.getElementById('telefono-condominio')?.value.trim(),
            email: document.getElementById('email-condominio')?.value.trim(),
            administrador: document.getElementById('administrador-condominio')?.value.trim()
        };
    }

    /**
     * Valida los datos del formulario
     */
    validarDatos(data) {
        const errores = [];

        if (!data.nombre) {
            errores.push('El nombre del condominio es obligatorio');
        }

        if (!data.direccion) {
            errores.push('La dirección es obligatoria');
        }

        if (data.email && !this.validarEmail(data.email)) {
            errores.push('El formato del email no es válido');
        }

        if (data.telefono && !this.validarTelefono(data.telefono)) {
            errores.push('El formato del teléfono no es válido');
        }

        if (errores.length > 0) {
            this.showMessage('Errores de validación:\n' + errores.join('\n'), 'error');
            return false;
        }

        return true;
    }

    /**
     * Valida formato de email
     */
    validarEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }

    /**
     * Valida formato de teléfono
     */
    validarTelefono(telefono) {
        const regex = /^[\d\s\-\(\)]+$/;
        return regex.test(telefono);
    }

    /**
     * Carga la lista de condominios
     */
    async loadCondominiosList() {
        try {
            const response = await fetch(this.apiUrl + 'condominios_get.php');
            const result = await response.json();

            if (result.success) {
                this.renderCondominiosList(result.data);
            } else {
                this.showMessage('Error al cargar condominios: ' + result.message, 'error');
            }

        } catch (error) {
            this.showMessage('Error de conexión: ' + error.message, 'error');
        }
    }

    /**
     * Renderiza la lista de condominios
     */
    renderCondominiosList(condominios) {
        const container = document.getElementById('condominios-list');
        if (!container) return;

        if (condominios.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <h3>No hay condominios registrados</h3>
                    <p>Comienza registrando tu primer condominio</p>
                </div>
            `;
            return;
        }

        const html = condominios.map(condominio => `
            <div class="condominio-card" data-id="${condominio.id_condominio}">
                <div class="condominio-header">
                    <h3>${condominio.nombre}</h3>
                    <div class="condominio-actions">
                        <button class="btn btn-edit btn-editar-condominio" 
                                data-condominio-id="${condominio.id_condominio}"
                                title="Editar condominio">
                            ✏️
                        </button>
                        <button class="btn btn-delete btn-eliminar-condominio" 
                                data-condominio-id="${condominio.id_condominio}"
                                title="Eliminar condominio">
                            🗑️
                        </button>
                    </div>
                </div>
                <div class="condominio-info">
                    <p><strong>Dirección:</strong> ${condominio.direccion}</p>
                    ${condominio.descripcion ? `<p><strong>Descripción:</strong> ${condominio.descripcion}</p>` : ''}
                    ${condominio.telefono ? `<p><strong>Teléfono:</strong> ${condominio.telefono}</p>` : ''}
                    ${condominio.email ? `<p><strong>Email:</strong> ${condominio.email}</p>` : ''}
                    ${condominio.administrador ? `<p><strong>Administrador:</strong> ${condominio.administrador}</p>` : ''}
                </div>
                <div class="condominio-stats">
                    <div class="stat">
                        <span class="stat-number">${condominio.total_calles || 0}</span>
                        <span class="stat-label">Calles</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">${condominio.total_casas || 0}</span>
                        <span class="stat-label">Casas</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">${condominio.total_empleados || 0}</span>
                        <span class="stat-label">Empleados</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">${condominio.total_residentes || 0}</span>
                        <span class="stat-label">Residentes</span>
                    </div>
                </div>
            </div>
        `).join('');

        container.innerHTML = html;
    }

    /**
     * Muestra confirmación para eliminar
     */
    mostrarConfirmacionEliminar(id) {
        const condominio = this.getCondominioById(id);
        if (!condominio) return;

        const modal = document.getElementById('delete-modal') || this.createDeleteModal();
        
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h2>⚠️ Confirmar Eliminación</h2>
                    <button class="modal-close" onclick="this.closest('.modal').style.display='none'">&times;</button>
                </div>
                <div class="modal-body">
                    <p><strong>¿Estás seguro de que deseas eliminar el condominio "${condominio.nombre}"?</strong></p>
                    <div class="warning-info">
                        <p>⚠️ Esta acción eliminará PERMANENTEMENTE:</p>
                        <ul>
                            <li>El condominio y toda su información</li>
                            <li>Todas las calles asociadas</li>
                            <li>Todas las casas asociadas</li>
                            <li>Todos los empleados asociados</li>
                            <li>Todos los residentes asociados</li>
                            <li>Todos los registros de entrada</li>
                            <li>Todos los engomados de vehículos</li>
                        </ul>
                        <p><strong>Esta acción NO se puede deshacer.</strong></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="this.closest('.modal').style.display='none'">
                        Cancelar
                    </button>
                    <button class="btn btn-danger" onclick="condominioManager.eliminarCondominio(${id})">
                        Eliminar Permanentemente
                    </button>
                </div>
            </div>
        `;

        modal.style.display = 'block';
    }

    /**
     * Crea el modal de eliminación
     */
    createDeleteModal() {
        const modal = document.createElement('div');
        modal.id = 'delete-modal';
        modal.className = 'modal';
        document.body.appendChild(modal);
        return modal;
    }

    /**
     * Edita un condominio
     */
    async editarCondominio(id) {
        try {
            const response = await fetch(this.apiUrl + 'condominios_get.php?id=' + id);
            const result = await response.json();

            if (result.success && result.data) {
                this.fillEditForm(result.data);
                this.showEditModal();
            } else {
                this.showMessage('Error al cargar datos del condominio', 'error');
            }

        } catch (error) {
            this.showMessage('Error de conexión: ' + error.message, 'error');
        }
    }

    /**
     * Llena el formulario de edición
     */
    fillEditForm(condominio) {
        document.getElementById('edit-id-condominio').value = condominio.id_condominio;
        document.getElementById('edit-nombre-condominio').value = condominio.nombre;
        document.getElementById('edit-direccion-condominio').value = condominio.direccion;
        document.getElementById('edit-descripcion-condominio').value = condominio.descripcion || '';
        document.getElementById('edit-telefono-condominio').value = condominio.telefono || '';
        document.getElementById('edit-email-condominio').value = condominio.email || '';
        document.getElementById('edit-administrador-condominio').value = condominio.administrador || '';
    }

    /**
     * Filtra condominios por búsqueda
     */
    filtrarCondominios(searchTerm) {
        const cards = document.querySelectorAll('.condominio-card');
        const term = searchTerm.toLowerCase();

        cards.forEach(card => {
            const nombre = card.querySelector('h3').textContent.toLowerCase();
            const direccion = card.querySelector('.condominio-info p').textContent.toLowerCase();
            
            if (nombre.includes(term) || direccion.includes(term)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    /**
     * Obtiene condominio por ID
     */
    getCondominioById(id) {
        const card = document.querySelector(`[data-id="${id}"]`);
        if (!card) return null;

        return {
            id_condominio: id,
            nombre: card.querySelector('h3').textContent
        };
    }

    /**
     * Limpia el formulario
     */
    clearForm() {
        const form = document.getElementById('form-condominio');
        if (form) {
            form.reset();
        }
    }

    /**
     * Muestra mensaje al usuario
     */
    showMessage(message, type = 'info') {
        const messageContainer = document.getElementById('message-container') || this.createMessageContainer();
        
        messageContainer.innerHTML = `
            <div class="alert alert-${type}">
                ${message}
                <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
            </div>
        `;

        // Auto-hide después de 5 segundos
        setTimeout(() => {
            messageContainer.innerHTML = '';
        }, 5000);
    }

    /**
     * Crea contenedor de mensajes
     */
    createMessageContainer() {
        const container = document.createElement('div');
        container.id = 'message-container';
        container.className = 'message-container';
        document.body.insertBefore(container, document.body.firstChild);
        return container;
    }

    /**
     * Muestra/oculta loading
     */
    showLoading(show) {
        const loader = document.getElementById('loader') || this.createLoader();
        loader.style.display = show ? 'block' : 'none';
    }

    /**
     * Crea loader
     */
    createLoader() {
        const loader = document.createElement('div');
        loader.id = 'loader';
        loader.className = 'loader';
        loader.innerHTML = '<div class="spinner"></div>';
        document.body.appendChild(loader);
        return loader;
    }

    /**
     * Cierra modal
     */
    closeModal() {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.style.display = 'none';
        });
    }

    /**
     * Configuración de validación en tiempo real
     */
    setupValidation() {
        const inputs = document.querySelectorAll('input[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', () => {
                this.validateInput(input);
            });
        });
    }

    /**
     * Valida un input específico
     */
    validateInput(input) {
        const value = input.value.trim();
        const isValid = value.length > 0;
        
        input.classList.toggle('invalid', !isValid);
        input.classList.toggle('valid', isValid);
    }

    /**
     * Exporta datos de condominios
     */
    async exportarCondominios() {
        try {
            const response = await fetch(this.apiUrl + 'condominios_export.php');
            const result = await response.json();

            if (result.success) {
                this.downloadJSON(result.data, 'condominios_export.json');
            } else {
                this.showMessage('Error al exportar: ' + result.message, 'error');
            }

        } catch (error) {
            this.showMessage('Error de conexión: ' + error.message, 'error');
        }
    }

    /**
     * Descarga archivo JSON
     */
    downloadJSON(data, filename) {
        const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
}

// Solo exportar la clase, no crear instancia automática
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CondominioManager;
}