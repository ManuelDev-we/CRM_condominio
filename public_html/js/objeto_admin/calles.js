/**
 * Gestión de Calles - Sistema Web Completo
 * Permite registrar, eliminar y gestionar calles con múltiples formatos de entrada
 * Soporte para separadores configurables y carga masiva
 * Basado en la estructura de BD: calles, casas y relaciones
 */

class CallesManager {
    constructor() {
        this.apiUrl = '../php/'; // Ruta a los archivos PHP
        this.calles = new Map();
        this.condominios = new Map();
        
        // Configuración de separadores
        this.separadores = {
            coma: ',',
            puntoycoma: ';',
            dospuntos: ':',
            punto: '.',
            comillas: '"',
            comillassimples: "'",
            pipe: '|',
            guion: '-',
            saltolinea: '\n'
        };
        
        this.separadorActivo = 'coma'; // Separador por defecto
        this.loadData();
    }

    /**
     * Inicializa la interfaz web
     */
    init() {
        this.setupEventListeners();
        this.loadCallesList();
        this.loadCondominiosList();
        this.setupValidation();
        this.setupSeparadorSelector();
    }

    /**
     * Configura los event listeners
     */
    setupEventListeners() {
        // Formulario de registro individual
        const formRegistro = document.getElementById('form-calle');
        if (formRegistro) {
            formRegistro.addEventListener('submit', (e) => {
                e.preventDefault();
                this.registrarCalle();
            });
        }

        // Formulario de registro masivo
        const formMasivo = document.getElementById('form-calles-masivo');
        if (formMasivo) {
            formMasivo.addEventListener('submit', (e) => {
                e.preventDefault();
                this.registrarCallesMasivo();
            });
        }

        // Botón de eliminar
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-eliminar-calle')) {
                const id = e.target.dataset.calleId;
                this.mostrarConfirmacionEliminar(id);
            }
        });

        // Botón de editar
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-editar-calle')) {
                const id = e.target.dataset.calleId;
                this.editarCalle(id);
            }
        });

        // Cambio de condominio
        const selectCondominio = document.getElementById('select-condominio');
        if (selectCondominio) {
            selectCondominio.addEventListener('change', (e) => {
                this.filtrarCallesPorCondominio(e.target.value);
            });
        }

        // Búsqueda en tiempo real
        const searchInput = document.getElementById('search-calles');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.filtrarCalles(e.target.value);
            });
        }

        // Cambio de separador
        const selectSeparador = document.getElementById('select-separador');
        if (selectSeparador) {
            selectSeparador.addEventListener('change', (e) => {
                this.cambiarSeparador(e.target.value);
            });
        }

        // Preview de calles en tiempo real
        const textareaCalles = document.getElementById('calles-masivo');
        if (textareaCalles) {
            textareaCalles.addEventListener('input', (e) => {
                this.previewCallesMasivo(e.target.value);
            });
        }
    }

    /**
     * Configura el selector de separadores
     */
    setupSeparadorSelector() {
        const selectSeparador = document.getElementById('select-separador');
        if (!selectSeparador) return;

        const opciones = [
            { value: 'coma', label: 'Coma (,)', ejemplo: 'Calle A, Calle B, Calle C' },
            { value: 'puntoycoma', label: 'Punto y coma (;)', ejemplo: 'Calle A; Calle B; Calle C' },
            { value: 'dospuntos', label: 'Dos puntos (:)', ejemplo: 'Calle A: Calle B: Calle C' },
            { value: 'punto', label: 'Punto (.)', ejemplo: 'Calle A. Calle B. Calle C' },
            { value: 'pipe', label: 'Pipe (|)', ejemplo: 'Calle A | Calle B | Calle C' },
            { value: 'guion', label: 'Guión (-)', ejemplo: 'Calle A - Calle B - Calle C' },
            { value: 'saltolinea', label: 'Salto de línea', ejemplo: 'Calle A\nCalle B\nCalle C' }
        ];

        selectSeparador.innerHTML = opciones.map(op => 
            `<option value="${op.value}" ${op.value === this.separadorActivo ? 'selected' : ''}>
                ${op.label}
            </option>`
        ).join('');

        this.actualizarEjemploSeparador();
    }

    /**
     * Cambia el separador activo
     */
    cambiarSeparador(nuevoSeparador) {
        this.separadorActivo = nuevoSeparador;
        this.actualizarEjemploSeparador();
        
        // Reanalizar el contenido del textarea si hay texto
        const textarea = document.getElementById('calles-masivo');
        if (textarea && textarea.value.trim()) {
            this.previewCallesMasivo(textarea.value);
        }
    }

    /**
     * Actualiza el ejemplo del separador seleccionado
     */
    actualizarEjemploSeparador() {
        const ejemploContainer = document.getElementById('ejemplo-separador');
        if (!ejemploContainer) return;

        const ejemplos = {
            coma: 'Calle Principal, Avenida Norte, Privada Sur, Boulevard Este',
            puntoycoma: 'Calle Principal; Avenida Norte; Privada Sur; Boulevard Este',
            dospuntos: 'Calle Principal: Avenida Norte: Privada Sur: Boulevard Este',
            punto: 'Calle Principal. Avenida Norte. Privada Sur. Boulevard Este',
            pipe: 'Calle Principal | Avenida Norte | Privada Sur | Boulevard Este',
            guion: 'Calle Principal - Avenida Norte - Privada Sur - Boulevard Este',
            saltolinea: 'Calle Principal\nAvenida Norte\nPrivada Sur\nBoulevard Este'
        };

        ejemploContainer.innerHTML = `
            <div class="ejemplo-separador">
                <strong>Ejemplo con separador seleccionado:</strong>
                <pre>${ejemplos[this.separadorActivo]}</pre>
            </div>
        `;
    }

    /**
     * Registra una calle individual
     */
    async registrarCalle() {
        try {
            const formData = this.getFormData();
            
            if (!this.validarDatos(formData)) {
                return;
            }

            this.showLoading(true);

            const response = await fetch(this.apiUrl + 'calles_save.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();

            if (result.success) {
                this.showMessage('Calle registrada exitosamente', 'success');
                this.clearForm();
                this.loadCallesList();
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
     * Registra múltiples calles usando el separador configurado
     */
    async registrarCallesMasivo() {
        try {
            const idCondominio = document.getElementById('condominio-masivo').value;
            const textoCalles = document.getElementById('calles-masivo').value.trim();

            if (!idCondominio) {
                this.showMessage('Debe seleccionar un condominio', 'error');
                return;
            }

            if (!textoCalles) {
                this.showMessage('Debe ingresar nombres de calles', 'error');
                return;
            }

            const callesProcesadas = this.procesarTextoCalles(textoCalles);
            
            if (callesProcesadas.length === 0) {
                this.showMessage('No se encontraron calles válidas para procesar', 'error');
                return;
            }

            this.showLoading(true);

            const datosParaEnvio = {
                id_condominio: parseInt(idCondominio),
                calles: callesProcesadas
            };

            const response = await fetch(this.apiUrl + 'calles_save_masivo.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(datosParaEnvio)
            });

            const result = await response.json();

            if (result.success) {
                this.showMessage(`${result.registradas} calles registradas exitosamente`, 'success');
                this.clearFormMasivo();
                this.loadCallesList();
                this.mostrarResultadoMasivo(result);
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
     * Procesa el texto de calles usando el separador configurado
     */
    procesarTextoCalles(texto) {
        let separador = this.separadores[this.separadorActivo];
        
        // Casos especiales para comillas
        if (this.separadorActivo === 'comillas' || this.separadorActivo === 'comillassimples') {
            // Extraer texto entre comillas
            const regex = this.separadorActivo === 'comillas' ? /"([^"]+)"/g : /'([^']+)'/g;
            const matches = [];
            let match;
            while ((match = regex.exec(texto)) !== null) {
                matches.push(match[1].trim());
            }
            return matches.filter(calle => calle.length > 0);
        }

        // Procesamiento normal con separadores
        return texto
            .split(separador)
            .map(calle => calle.trim())
            .filter(calle => calle.length > 0)
            .map(calle => {
                // Remover comillas si las tiene
                return calle.replace(/^["']|["']$/g, '');
            });
    }

    /**
     * Preview de calles en tiempo real
     */
    previewCallesMasivo(texto) {
        const previewContainer = document.getElementById('preview-calles');
        if (!previewContainer) return;

        if (!texto.trim()) {
            previewContainer.innerHTML = '<div class="preview-empty">Escriba nombres de calles para ver el preview...</div>';
            return;
        }

        const callesProcesadas = this.procesarTextoCalles(texto);
        
        if (callesProcesadas.length === 0) {
            previewContainer.innerHTML = '<div class="preview-error">No se detectaron calles válidas con el separador seleccionado</div>';
            return;
        }

        const html = `
            <div class="preview-success">
                <h4>Preview: ${callesProcesadas.length} calles detectadas</h4>
                <ol class="preview-list">
                    ${callesProcesadas.map(calle => `<li>${calle}</li>`).join('')}
                </ol>
            </div>
        `;

        previewContainer.innerHTML = html;
    }

    /**
     * Elimina una calle y todas sus casas asociadas
     */
    async eliminarCalle(id) {
        try {
            this.showLoading(true);

            const response = await fetch(this.apiUrl + 'calles_delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id_calle: id })
            });

            const result = await response.json();

            if (result.success) {
                this.showMessage('Calle eliminada exitosamente', 'success');
                this.loadCallesList();
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
     * Obtiene datos del formulario individual
     */
    getFormData() {
        return {
            id_condominio: parseInt(document.getElementById('id-condominio-calle')?.value),
            nombre: document.getElementById('nombre-calle')?.value.trim(),
            descripcion: document.getElementById('descripcion-calle')?.value.trim()
        };
    }

    /**
     * Valida los datos del formulario
     */
    validarDatos(data) {
        const errores = [];

        if (!data.id_condominio) {
            errores.push('Debe seleccionar un condominio');
        }

        if (!data.nombre) {
            errores.push('El nombre de la calle es obligatorio');
        }

        if (data.nombre && data.nombre.length < 2) {
            errores.push('El nombre de la calle debe tener al menos 2 caracteres');
        }

        if (errores.length > 0) {
            this.showMessage('Errores de validación:\n' + errores.join('\n'), 'error');
            return false;
        }

        return true;
    }

    /**
     * Carga la lista de condominios
     */
    async loadCondominiosList() {
        try {
            const response = await fetch(this.apiUrl + 'condominios_get.php');
            const result = await response.json();

            if (result.success) {
                this.renderCondominiosSelect(result.data);
            }

        } catch (error) {
            console.error('Error al cargar condominios:', error);
        }
    }

    /**
     * Renderiza el select de condominios
     */
    renderCondominiosSelect(condominios) {
        const selects = ['id-condominio-calle', 'condominio-masivo', 'select-condominio'];
        
        selects.forEach(selectId => {
            const select = document.getElementById(selectId);
            if (!select) return;

            const defaultOption = selectId === 'select-condominio' ? 
                '<option value="">Todos los condominios</option>' : 
                '<option value="">Seleccione un condominio</option>';

            select.innerHTML = defaultOption + condominios.map(cond => 
                `<option value="${cond.id_condominio}">${cond.nombre}</option>`
            ).join('');
        });
    }

    /**
     * Carga la lista de calles
     */
    async loadCallesList() {
        try {
            const response = await fetch(this.apiUrl + 'calles_get.php');
            const result = await response.json();

            if (result.success) {
                this.renderCallesList(result.data);
            } else {
                this.showMessage('Error al cargar calles: ' + result.message, 'error');
            }

        } catch (error) {
            this.showMessage('Error de conexión: ' + error.message, 'error');
        }
    }

    /**
     * Renderiza la lista de calles
     */
    renderCallesList(calles) {
        const container = document.getElementById('calles-list');
        if (!container) return;

        if (calles.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <h3>No hay calles registradas</h3>
                    <p>Comienza registrando calles en tus condominios</p>
                </div>
            `;
            return;
        }

        // Agrupar calles por condominio
        const callesPorCondominio = this.agruparCallesPorCondominio(calles);

        const html = Object.entries(callesPorCondominio).map(([condominioNombre, callesCondominio]) => `
            <div class="condominio-group">
                <h3 class="condominio-title">🏢 ${condominioNombre} (${callesCondominio.length} calles)</h3>
                <div class="calles-grid">
                    ${callesCondominio.map(calle => `
                        <div class="calle-card" data-id="${calle.id_calle}">
                            <div class="calle-header">
                                <h4>${calle.nombre}</h4>
                                <div class="calle-actions">
                                    <button class="btn btn-edit btn-editar-calle" 
                                            data-calle-id="${calle.id_calle}"
                                            title="Editar calle">
                                        ✏️
                                    </button>
                                    <button class="btn btn-delete btn-eliminar-calle" 
                                            data-calle-id="${calle.id_calle}"
                                            title="Eliminar calle">
                                        🗑️
                                    </button>
                                </div>
                            </div>
                            ${calle.descripcion ? `<p class="calle-descripcion">${calle.descripcion}</p>` : ''}
                            <div class="calle-stats">
                                <div class="stat">
                                    <span class="stat-number">${calle.total_casas || 0}</span>
                                    <span class="stat-label">Casas</span>
                                </div>
                                <div class="stat">
                                    <span class="stat-number">${calle.total_residentes || 0}</span>
                                    <span class="stat-label">Residentes</span>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `).join('');

        container.innerHTML = html;
    }

    /**
     * Agrupa calles por condominio
     */
    agruparCallesPorCondominio(calles) {
        return calles.reduce((grupos, calle) => {
            const condominioNombre = calle.condominio_nombre || 'Sin condominio';
            if (!grupos[condominioNombre]) {
                grupos[condominioNombre] = [];
            }
            grupos[condominioNombre].push(calle);
            return grupos;
        }, {});
    }

    /**
     * Muestra confirmación para eliminar calle
     */
    mostrarConfirmacionEliminar(id) {
        const calle = this.getCalleById(id);
        if (!calle) return;

        const modal = document.getElementById('delete-modal') || this.createDeleteModal();
        
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h2>⚠️ Confirmar Eliminación de Calle</h2>
                    <button class="modal-close" onclick="this.closest('.modal').style.display='none'">&times;</button>
                </div>
                <div class="modal-body">
                    <p><strong>¿Estás seguro de que deseas eliminar la calle "${calle.nombre}"?</strong></p>
                    <div class="warning-info">
                        <p>⚠️ Esta acción eliminará PERMANENTEMENTE:</p>
                        <ul>
                            <li>La calle y toda su información</li>
                            <li>Todas las casas de esta calle</li>
                            <li>Todos los residentes de estas casas</li>
                            <li>Todos los registros de entrada relacionados</li>
                            <li>Todos los engomados de vehículos asociados</li>
                        </ul>
                        <p><strong>Esta acción NO se puede deshacer.</strong></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="this.closest('.modal').style.display='none'">
                        Cancelar
                    </button>
                    <button class="btn btn-danger" onclick="callesManager.eliminarCalle(${id})">
                        Eliminar Permanentemente
                    </button>
                </div>
            </div>
        `;

        modal.style.display = 'block';
    }

    /**
     * Muestra resultado del registro masivo
     */
    mostrarResultadoMasivo(result) {
        const resultContainer = document.getElementById('resultado-masivo');
        if (!resultContainer) return;

        let html = `
            <div class="resultado-masivo">
                <h4>✅ Registro Masivo Completado</h4>
                <p><strong>Calles registradas:</strong> ${result.registradas}</p>
                ${result.errores && result.errores.length > 0 ? `
                    <p><strong>Errores:</strong> ${result.errores.length}</p>
                    <div class="errores-detalle">
                        ${result.errores.map(error => `<p class="error-item">❌ ${error}</p>`).join('')}
                    </div>
                ` : ''}
            </div>
        `;

        resultContainer.innerHTML = html;
        resultContainer.style.display = 'block';

        // Auto-hide después de 10 segundos
        setTimeout(() => {
            resultContainer.style.display = 'none';
        }, 10000);
    }

    /**
     * Filtrar calles por condominio
     */
    filtrarCallesPorCondominio(condominioId) {
        const groups = document.querySelectorAll('.condominio-group');
        
        if (!condominioId) {
            // Mostrar todos
            groups.forEach(group => group.style.display = 'block');
            return;
        }

        groups.forEach(group => {
            const title = group.querySelector('.condominio-title').textContent;
            // Aquí deberías implementar lógica más robusta para filtrar por ID
            group.style.display = 'block'; // Por ahora mostrar todos
        });
    }

    /**
     * Obtiene calle por ID
     */
    getCalleById(id) {
        const card = document.querySelector(`[data-id="${id}"]`);
        if (!card) return null;

        return {
            id_calle: id,
            nombre: card.querySelector('h4').textContent
        };
    }

    /**
     * Limpia formularios
     */
    clearForm() {
        const form = document.getElementById('form-calle');
        if (form) form.reset();
    }

    clearFormMasivo() {
        const form = document.getElementById('form-calles-masivo');
        if (form) form.reset();
        
        const preview = document.getElementById('preview-calles');
        if (preview) preview.innerHTML = '<div class="preview-empty">Escriba nombres de calles para ver el preview...</div>';
        
        const resultado = document.getElementById('resultado-masivo');
        if (resultado) resultado.style.display = 'none';
    }

    /**
     * Utilidades
     */
    createDeleteModal() {
        const modal = document.createElement('div');
        modal.id = 'delete-modal';
        modal.className = 'modal';
        document.body.appendChild(modal);
        return modal;
    }

    closeModal() {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => modal.style.display = 'none');
    }

    showMessage(message, type = 'info') {
        const messageContainer = document.getElementById('message-container') || this.createMessageContainer();
        
        messageContainer.innerHTML = `
            <div class="alert alert-${type}">
                ${message}
                <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
            </div>
        `;

        setTimeout(() => {
            messageContainer.innerHTML = '';
        }, 5000);
    }

    createMessageContainer() {
        const container = document.createElement('div');
        container.id = 'message-container';
        container.className = 'message-container';
        document.body.insertBefore(container, document.body.firstChild);
        return container;
    }

    showLoading(show) {
        const loader = document.getElementById('loader') || this.createLoader();
        loader.style.display = show ? 'block' : 'none';
    }

    createLoader() {
        const loader = document.createElement('div');
        loader.id = 'loader';
        loader.className = 'loader';
        loader.innerHTML = '<div class="spinner"></div>';
        document.body.appendChild(loader);
        return loader;
    }

    setupValidation() {
        const inputs = document.querySelectorAll('input[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', () => {
                this.validateInput(input);
            });
        });
    }

    validateInput(input) {
        const value = input.value.trim();
        const isValid = value.length > 0;
        
        input.classList.toggle('invalid', !isValid);
        input.classList.toggle('valid', isValid);
    }

    filtrarCalles(searchTerm) {
        const cards = document.querySelectorAll('.calle-card');
        const term = searchTerm.toLowerCase();

        cards.forEach(card => {
            const nombre = card.querySelector('h4').textContent.toLowerCase();
            const descripcion = card.querySelector('.calle-descripcion')?.textContent.toLowerCase() || '';
            
            if (nombre.includes(term) || descripcion.includes(term)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    loadData() {
        // Cargar datos iniciales si es necesario
    }

    /**
     * Obtiene todas las calles o filtradas por criterios
     * @param {Object} filtros - Objeto con filtros opcionales
     * @returns {Array} Array de calles
     */
    async getAllCalles(filtros = {}) {
        try {
            let url = this.apiUrl + 'calles_list.php';
            const params = new URLSearchParams();

            // Aplicar filtros si se proporcionan
            if (filtros.id_condominio) {
                params.append('id_condominio', filtros.id_condominio);
            }
            if (filtros.busqueda) {
                params.append('busqueda', filtros.busqueda);
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
            
            const calles = await response.json();
            
            // Actualizar cache local
            this.calles.clear();
            calles.forEach(calle => {
                this.calles.set(calle.id_calle, calle);
            });
            
            return calles;
        } catch (error) {
            console.error('Error obteniendo calles:', error);
            this.showMessage('Error al obtener calles: ' + error.message, 'error');
            return [];
        }
    }

    /**
     * Obtiene una calle por su ID
     * @param {number} id - ID de la calle
     * @returns {Object|null} Objeto calle o null si no existe
     */
    async getCalleById(id) {
        try {
            const response = await fetch(this.apiUrl + `calle_by_id.php?id=${id}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const calle = await response.json();
            
            if (calle) {
                // Actualizar cache local
                this.calles.set(calle.id_calle, calle);
            }
            
            return calle;
        } catch (error) {
            console.error('Error obteniendo calle por ID:', error);
            this.showMessage('Error al obtener calle: ' + error.message, 'error');
            return null;
        }
    }

    /**
     * Obtiene calles por condominio específico
     * @param {number} idCondominio - ID del condominio
     * @returns {Array} Array de calles del condominio
     */
    async getCallesByCondominio(idCondominio) {
        return await this.getAllCalles({ id_condominio: idCondominio });
    }

    /**
     * Busca calles por término de búsqueda
     * @param {string} termino - Término de búsqueda
     * @returns {Array} Array de calles que coinciden
     */
    async searchCalles(termino) {
        return await this.getAllCalles({ busqueda: termino });
    }

    /**
     * Obtiene estadísticas de calles
     * @returns {Object} Objeto con estadísticas
     */
    async getEstadisticasCalles() {
        try {
            const calles = await this.getAllCalles();
            
            const estadisticas = {
                total: calles.length,
                porCondominio: {},
                conCasas: 0,
                sinCasas: 0
            };

            calles.forEach(calle => {
                // Estadísticas por condominio
                const condominio = calle.condominio_nombre || 'Sin condominio';
                estadisticas.porCondominio[condominio] = (estadisticas.porCondominio[condominio] || 0) + 1;

                // Estadísticas de casas
                if (calle.total_casas > 0) {
                    estadisticas.conCasas++;
                } else {
                    estadisticas.sinCasas++;
                }
            });

            return estadisticas;
        } catch (error) {
            console.error('Error obteniendo estadísticas de calles:', error);
            return null;
        }
    }

    /**
     * Exporta calles filtradas a JSON
     * @param {Object} filtros - Filtros a aplicar
     * @returns {string} JSON de calles
     */
    async exportarCalles(filtros = {}) {
        const calles = await this.getAllCalles(filtros);
        return JSON.stringify(calles, null, 2);
    }

    /**
     * Obtiene calles con sus casas asociadas
     * @param {Object} filtros - Filtros opcionales
     * @returns {Array} Array de calles con información de casas
     */
    async getCallesConCasas(filtros = {}) {
        try {
            let url = this.apiUrl + 'calles_con_casas.php';
            const params = new URLSearchParams();

            if (filtros.id_condominio) {
                params.append('id_condominio', filtros.id_condominio);
            }
            if (filtros.busqueda) {
                params.append('busqueda', filtros.busqueda);
            }

            if (params.toString()) {
                url += '?' + params.toString();
            }

            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('Error obteniendo calles con casas:', error);
            this.showMessage('Error al obtener calles con casas: ' + error.message, 'error');
            return [];
        }
    }
}

// Solo exportar la clase, no crear instancia automática
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CallesManager;
}