/**
 * Gestión de Casas - Sistema Web Simplificado
 * Permite registrar números de casas en calles específicas
 * Soporte para registro individual y masivo con separadores configurables
 * Basado en la estructura de BD: casas (id_casa, casa, id_condominio, id_calle)
 */

class CasasManager {
    constructor() {
        this.apiUrl = '../php/'; // Ruta a los archivos PHP
        this.casas = new Map();
        this.condominios = new Map();
        this.calles = new Map();
        
        // Configuración de separadores para números de casa
        this.separadores = {
            coma: ',',
            puntoycoma: ';',
            dospuntos: ':',
            espacio: ' ',
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
        this.loadCasasList();
        this.loadCondominiosList();
        this.setupValidation();
        this.setupSeparadorSelector();
    }

    /**
     * Configura los event listeners
     */
    setupEventListeners() {
        // Formulario de registro individual
        const formRegistro = document.getElementById('form-casa');
        if (formRegistro) {
            formRegistro.addEventListener('submit', (e) => {
                e.preventDefault();
                this.registrarCasa();
            });
        }

        // Formulario de registro masivo
        const formMasivo = document.getElementById('form-casas-masivo');
        if (formMasivo) {
            formMasivo.addEventListener('submit', (e) => {
                e.preventDefault();
                this.registrarCasasMasivo();
            });
        }

        // Preview en tiempo real para registro masivo
        const textareaMasivo = document.getElementById('numeros-casas');
        if (textareaMasivo) {
            textareaMasivo.addEventListener('input', (e) => {
                this.previewCasasMasivo(e.target.value);
            });
        }

        // Cambio de condominio - actualizar calles
        const selectCondominioIndividual = document.getElementById('id-condominio-casa');
        if (selectCondominioIndividual) {
            selectCondominioIndividual.addEventListener('change', (e) => {
                this.cargarCallesPorCondominio(e.target.value, 'id-calle-casa');
            });
        }

        const selectCondominioMasivo = document.getElementById('condominio-masivo');
        if (selectCondominioMasivo) {
            selectCondominioMasivo.addEventListener('change', (e) => {
                this.cargarCallesPorCondominio(e.target.value, 'calle-masivo');
            });
        }

        // Cambio de separador
        const selectSeparador = document.getElementById('select-separador');
        if (selectSeparador) {
            selectSeparador.addEventListener('change', (e) => {
                this.cambiarSeparador(e.target.value);
            });
        }

        // Filtros
        const filtroCondominio = document.getElementById('filtro-condominio');
        if (filtroCondominio) {
            filtroCondominio.addEventListener('change', (e) => {
                this.filtrarCasas();
            });
        }

        const filtroCalle = document.getElementById('filtro-calle');
        if (filtroCalle) {
            filtroCalle.addEventListener('change', (e) => {
                this.filtrarCasas();
            });
        }

        const busquedaCasa = document.getElementById('busqueda-casa');
        if (busquedaCasa) {
            busquedaCasa.addEventListener('input', (e) => {
                this.buscarCasas(e.target.value);
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
            { value: 'coma', label: 'Coma (,)', ejemplo: '1, 2, 3, 4, 5' },
            { value: 'puntoycoma', label: 'Punto y coma (;)', ejemplo: '1; 2; 3; 4; 5' },
            { value: 'dospuntos', label: 'Dos puntos (:)', ejemplo: '1: 2: 3: 4: 5' },
            { value: 'espacio', label: 'Espacio ( )', ejemplo: '1 2 3 4 5' },
            { value: 'pipe', label: 'Pipe (|)', ejemplo: '1 | 2 | 3 | 4 | 5' },
            { value: 'guion', label: 'Guión (-)', ejemplo: '1 - 2 - 3 - 4 - 5' },
            { value: 'saltolinea', label: 'Salto de línea', ejemplo: '1\n2\n3\n4\n5' }
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
        const textarea = document.getElementById('numeros-casas');
        if (textarea && textarea.value.trim()) {
            this.previewCasasMasivo(textarea.value);
        }
    }

    /**
     * Actualiza el ejemplo del separador seleccionado
     */
    actualizarEjemploSeparador() {
        const ejemploContainer = document.getElementById('ejemplo-separador');
        if (!ejemploContainer) return;

        const ejemplos = {
            coma: '1, 2, 3, 4, 5, 10, 15, 20',
            puntoycoma: '1; 2; 3; 4; 5; 10; 15; 20',
            dospuntos: '1: 2: 3: 4: 5: 10: 15: 20',
            espacio: '1 2 3 4 5 10 15 20',
            pipe: '1 | 2 | 3 | 4 | 5 | 10 | 15 | 20',
            guion: '1 - 2 - 3 - 4 - 5 - 10 - 15 - 20',
            saltolinea: '1\n2\n3\n4\n5\n10\n15\n20'
        };

        ejemploContainer.innerHTML = `
            <div class="ejemplo-separador">
                <strong>Ejemplo con separador seleccionado:</strong>
                <pre>${ejemplos[this.separadorActivo]}</pre>
            </div>
        `;
    }

    /**
     * Registra una casa individual
     */
    async registrarCasa() {
        try {
            const formData = this.getFormData();
            
            if (!this.validarDatos(formData)) {
                return;
            }

            this.showLoading(true);

            const response = await fetch(this.apiUrl + 'casas_save.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();

            if (result.success) {
                this.showMessage('Casa registrada exitosamente', 'success');
                this.clearForm();
                this.loadCasasList();
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
     * Registra múltiples casas
     */
    async registrarCasasMasivo() {
        try {
            const idCondominio = parseInt(document.getElementById('condominio-masivo')?.value);
            const idCalle = parseInt(document.getElementById('calle-masivo')?.value);
            const textoCasas = document.getElementById('numeros-casas')?.value.trim();

            if (!idCondominio || !idCalle || !textoCasas) {
                this.showMessage('Todos los campos son obligatorios', 'error');
                return;
            }

            const numerosProcesados = this.procesarTextoCasas(textoCasas);
            
            if (numerosProcesados.length === 0) {
                this.showMessage('No se detectaron números de casa válidos', 'error');
                return;
            }

            this.showLoading(true);

            const formData = {
                id_condominio: idCondominio,
                id_calle: idCalle,
                numeros_casas: numerosProcesados
            };

            const response = await fetch(this.apiUrl + 'casas_save_masivo.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();

            if (result.success) {
                this.showMessage(`${result.registradas} casas registradas exitosamente`, 'success');
                this.clearFormMasivo();
                this.loadCasasList();
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
     * Procesa el texto con números de casas
     */
    procesarTextoCasas(texto) {
        let separador = this.separadores[this.separadorActivo];
        
        return texto
            .split(separador)
            .map(numero => numero.trim())
            .filter(numero => numero.length > 0)
            .filter(numero => /^\d+[A-Za-z]?$/.test(numero)) // Solo números y opcionalmente una letra
            .sort((a, b) => {
                // Ordenar numéricamente
                const numA = parseInt(a.replace(/[A-Za-z]/g, ''));
                const numB = parseInt(b.replace(/[A-Za-z]/g, ''));
                return numA - numB;
            });
    }

    /**
     * Preview de casas en tiempo real
     */
    previewCasasMasivo(texto) {
        const previewContainer = document.getElementById('preview-casas');
        if (!previewContainer) return;

        if (!texto.trim()) {
            previewContainer.innerHTML = '<div class="preview-empty">Escriba números de casas para ver el preview...</div>';
            return;
        }

        const numerosProcesados = this.procesarTextoCasas(texto);
        
        if (numerosProcesados.length === 0) {
            previewContainer.innerHTML = '<div class="preview-error">No se detectaron números de casa válidos con el separador seleccionado</div>';
            return;
        }

        const html = `
            <div class="preview-success">
                <h4>Preview: ${numerosProcesados.length} casas detectadas</h4>
                <div class="numeros-grid">
                    ${numerosProcesados.map(numero => `<span class="numero-casa">${numero}</span>`).join('')}
                </div>
            </div>
        `;

        previewContainer.innerHTML = html;
    }

    /**
     * Carga calles por condominio
     */
    async cargarCallesPorCondominio(idCondominio, selectId) {
        const selectCalle = document.getElementById(selectId);
        if (!selectCalle) return;

        if (!idCondominio) {
            selectCalle.innerHTML = '<option value="">Seleccione un condominio primero</option>';
            return;
        }

        try {
            selectCalle.innerHTML = '<option value="">Cargando calles...</option>';

            const response = await fetch(this.apiUrl + `calles_by_condominio.php?id_condominio=${idCondominio}`);
            const calles = await response.json();

            if (calles.length === 0) {
                selectCalle.innerHTML = '<option value="">No hay calles registradas</option>';
                return;
            }

            selectCalle.innerHTML = '<option value="">Seleccione una calle</option>' +
                calles.map(calle => `<option value="${calle.id_calle}">${calle.nombre}</option>`).join('');

        } catch (error) {
            selectCalle.innerHTML = '<option value="">Error al cargar calles</option>';
        }
    }

    /**
     * Elimina una casa
     */
    async eliminarCasa(id) {
        try {
            this.showLoading(true);

            const response = await fetch(this.apiUrl + 'casas_delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id_casa: id })
            });

            const result = await response.json();

            if (result.success) {
                this.showMessage('Casa eliminada exitosamente', 'success');
                this.loadCasasList();
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
            id_condominio: parseInt(document.getElementById('id-condominio-casa')?.value),
            id_calle: parseInt(document.getElementById('id-calle-casa')?.value),
            casa: document.getElementById('numero-casa')?.value.trim()
        };
    }

    /**
     * Valida los datos del formulario
     */
    validarDatos(data) {
        if (!data.id_condominio) {
            this.showMessage('Seleccione un condominio', 'error');
            return false;
        }

        if (!data.id_calle) {
            this.showMessage('Seleccione una calle', 'error');
            return false;
        }

        if (!data.casa) {
            this.showMessage('Ingrese el número de casa', 'error');
            return false;
        }

        if (!/^\d+[A-Za-z]?$/.test(data.casa)) {
            this.showMessage('El número de casa debe ser numérico (ej: 1, 2A, 15B)', 'error');
            return false;
        }

        return true;
    }

    /**
     * Carga la lista de casas
     */
    async loadCasasList() {
        try {
            const response = await fetch(this.apiUrl + 'casas_list.php');
            const casas = await response.json();
            
            this.casas.clear();
            casas.forEach(casa => {
                this.casas.set(casa.id_casa, casa);
            });
            
            this.renderCasasList(casas);
        } catch (error) {
            console.error('Error cargando casas:', error);
            this.showMessage('Error al cargar las casas', 'error');
        }
    }

    /**
     * Carga la lista de condominios
     */
    async loadCondominiosList() {
        try {
            const response = await fetch(this.apiUrl + 'condominios_list.php');
            const condominios = await response.json();
            
            this.condominios.clear();
            condominios.forEach(condominio => {
                this.condominios.set(condominio.id_condominio, condominio);
            });
            
            this.populateCondominiosSelects(condominios);
        } catch (error) {
            console.error('Error cargando condominios:', error);
        }
    }

    /**
     * Llena los selects de condominios
     */
    populateCondominiosSelects(condominios) {
        const selects = [
            'id-condominio-casa',
            'condominio-masivo',
            'filtro-condominio'
        ];

        selects.forEach(selectId => {
            const select = document.getElementById(selectId);
            if (select) {
                const defaultOption = selectId === 'filtro-condominio' ? 
                    '<option value="">Todos los condominios</option>' : 
                    '<option value="">Seleccione un condominio</option>';
                
                select.innerHTML = defaultOption + 
                    condominios.map(cond => `<option value="${cond.id_condominio}">${cond.nombre}</option>`).join('');
            }
        });
    }

    /**
     * Renderiza la lista de casas agrupadas por condominio y calle
     */
    renderCasasList(casas) {
        const container = document.getElementById('casas-list');
        if (!container) return;

        if (casas.length === 0) {
            container.innerHTML = '<div class="empty-state">No hay casas registradas</div>';
            return;
        }

        // Agrupar por condominio y calle
        const grouped = {};
        casas.forEach(casa => {
            const condKey = casa.condominio_nombre || 'Sin condominio';
            const calleKey = casa.calle_nombre || 'Sin calle';
            
            if (!grouped[condKey]) grouped[condKey] = {};
            if (!grouped[condKey][calleKey]) grouped[condKey][calleKey] = [];
            
            grouped[condKey][calleKey].push(casa);
        });

        let html = '';
        Object.keys(grouped).forEach(condominio => {
            html += `<div class="condominio-group">
                <h3 class="condominio-title">🏢 ${condominio}</h3>`;
            
            Object.keys(grouped[condominio]).forEach(calle => {
                const casasCalle = grouped[condominio][calle];
                // Ordenar casas numéricamente
                casasCalle.sort((a, b) => {
                    const numA = parseInt(a.casa.replace(/[A-Za-z]/g, ''));
                    const numB = parseInt(b.casa.replace(/[A-Za-z]/g, ''));
                    return numA - numB;
                });

                html += `<div class="calle-group">
                    <h4 class="calle-title">🛣️ ${calle} (${casasCalle.length} casas)</h4>
                    <div class="casas-grid">
                        ${casasCalle.map(casa => `
                            <div class="casa-card">
                                <div class="casa-numero">${casa.casa}</div>
                                <div class="casa-acciones">
                                    <button onclick="casasManager.confirmarEliminar(${casa.id_casa}, '${casa.casa}')" 
                                            class="btn-eliminar" title="Eliminar casa">
                                        🗑️
                                    </button>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>`;
            });
            
            html += '</div>';
        });

        container.innerHTML = html;
    }

    /**
     * Filtra casas por condominio y calle
     */
    filtrarCasas() {
        // Implementar filtrado si es necesario
        // Por ahora, recargar la lista completa
        this.loadCasasList();
    }

    /**
     * Busca casas por número
     */
    buscarCasas(termino) {
        if (!termino.trim()) {
            this.loadCasasList();
            return;
        }

        const casasFiltradas = Array.from(this.casas.values())
            .filter(casa => casa.casa.toLowerCase().includes(termino.toLowerCase()));
        
        this.renderCasasList(casasFiltradas);
    }

    /**
     * Confirma eliminación de casa
     */
    confirmarEliminar(id, numero) {
        const modal = document.getElementById('modal-confirmar');
        const mensaje = document.getElementById('mensaje-confirmar');
        const btnConfirmar = document.getElementById('btn-confirmar-eliminar');
        
        if (modal && mensaje && btnConfirmar) {
            mensaje.textContent = `¿Está seguro de eliminar la casa número "${numero}"?`;
            btnConfirmar.onclick = () => this.eliminarCasa(id);
            modal.style.display = 'block';
        }
    }

    /**
     * Carga datos iniciales (modo demo)
     */
    loadData() {
        // Datos de ejemplo para desarrollo
        console.log('CasasManager inicializado');
    }

    /**
     * Configuración de validación en tiempo real
     */
    setupValidation() {
        const numeroCasa = document.getElementById('numero-casa');
        if (numeroCasa) {
            numeroCasa.addEventListener('input', (e) => {
                const valor = e.target.value;
                const valido = /^\d+[A-Za-z]?$/.test(valor);
                
                if (valor && !valido) {
                    e.target.classList.add('error');
                    this.showMessage('Formato inválido. Use números y opcionalmente una letra (ej: 1, 2A, 15B)', 'warning');
                } else {
                    e.target.classList.remove('error');
                }
            });
        }
    }

    /**
     * Limpia el formulario individual
     */
    clearForm() {
        const form = document.getElementById('form-casa');
        if (form) {
            form.reset();
            document.getElementById('id-calle-casa').innerHTML = '<option value="">Seleccione un condominio primero</option>';
        }
    }

    /**
     * Limpia el formulario masivo
     */
    clearFormMasivo() {
        const form = document.getElementById('form-casas-masivo');
        if (form) {
            form.reset();
            document.getElementById('calle-masivo').innerHTML = '<option value="">Seleccione un condominio primero</option>';
            document.getElementById('preview-casas').innerHTML = '<div class="preview-empty">Escriba números de casas para ver el preview...</div>';
        }
    }

    /**
     * Muestra mensaje al usuario
     */
    showMessage(message, type = 'info') {
        const messageContainer = document.getElementById('message-container');
        if (messageContainer) {
            messageContainer.innerHTML = `
                <div class="message ${type}">
                    ${message}
                    <button onclick="this.parentElement.remove()">×</button>
                </div>
            `;
            
            setTimeout(() => {
                const messageElement = messageContainer.querySelector('.message');
                if (messageElement) {
                    messageElement.remove();
                }
            }, 5000);
        }
    }

    /**
     * Muestra/oculta indicador de carga
     */
    showLoading(show) {
        const loadingElements = document.querySelectorAll('.loading');
        loadingElements.forEach(el => {
            el.style.display = show ? 'block' : 'none';
        });
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
     * Obtiene todas las casas o filtradas por criterios
     * @param {Object} filtros - Objeto con filtros opcionales
     * @returns {Array} Array de casas
     */
    async getAllCasas(filtros = {}) {
        try {
            let url = this.apiUrl + 'casas_list.php';
            const params = new URLSearchParams();

            // Aplicar filtros si se proporcionan
            if (filtros.id_condominio) {
                params.append('id_condominio', filtros.id_condominio);
            }
            if (filtros.id_calle) {
                params.append('id_calle', filtros.id_calle);
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
            
            const casas = await response.json();
            
            // Actualizar cache local
            this.casas.clear();
            casas.forEach(casa => {
                this.casas.set(casa.id_casa, casa);
            });
            
            return casas;
        } catch (error) {
            console.error('Error obteniendo casas:', error);
            this.showMessage('Error al obtener casas: ' + error.message, 'error');
            return [];
        }
    }

    /**
     * Obtiene una casa por su ID
     * @param {number} id - ID de la casa
     * @returns {Object|null} Objeto casa o null si no existe
     */
    async getCasaById(id) {
        try {
            const response = await fetch(this.apiUrl + `casa_by_id.php?id=${id}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const casa = await response.json();
            
            if (casa) {
                // Actualizar cache local
                this.casas.set(casa.id_casa, casa);
            }
            
            return casa;
        } catch (error) {
            console.error('Error obteniendo casa por ID:', error);
            this.showMessage('Error al obtener casa: ' + error.message, 'error');
            return null;
        }
    }

    /**
     * Obtiene casas por condominio específico
     * @param {number} idCondominio - ID del condominio
     * @returns {Array} Array de casas del condominio
     */
    async getCasasByCondominio(idCondominio) {
        return await this.getAllCasas({ id_condominio: idCondominio });
    }

    /**
     * Obtiene casas por calle específica
     * @param {number} idCalle - ID de la calle
     * @returns {Array} Array de casas de la calle
     */
    async getCasasByCalle(idCalle) {
        return await this.getAllCasas({ id_calle: idCalle });
    }

    /**
     * Busca casas por término de búsqueda (número de casa)
     * @param {string} termino - Término de búsqueda
     * @returns {Array} Array de casas que coinciden
     */
    async searchCasas(termino) {
        return await this.getAllCasas({ busqueda: termino });
    }

    /**
     * Obtiene estadísticas de casas
     * @returns {Object} Objeto con estadísticas
     */
    async getEstadisticasCasas() {
        try {
            const casas = await this.getAllCasas();
            
            const estadisticas = {
                total: casas.length,
                porCondominio: {},
                porCalle: {},
                numerosUnicos: new Set(),
                conLetras: 0,
                soloNumeros: 0
            };

            casas.forEach(casa => {
                // Estadísticas por condominio
                const condominio = casa.condominio_nombre || 'Sin condominio';
                estadisticas.porCondominio[condominio] = (estadisticas.porCondominio[condominio] || 0) + 1;

                // Estadísticas por calle
                const calle = casa.calle_nombre || 'Sin calle';
                estadisticas.porCalle[calle] = (estadisticas.porCalle[calle] || 0) + 1;

                // Análisis de números
                estadisticas.numerosUnicos.add(casa.casa);
                
                if (/[A-Za-z]/.test(casa.casa)) {
                    estadisticas.conLetras++;
                } else {
                    estadisticas.soloNumeros++;
                }
            });

            // Convertir Set a número
            estadisticas.numerosUnicos = estadisticas.numerosUnicos.size;

            return estadisticas;
        } catch (error) {
            console.error('Error obteniendo estadísticas de casas:', error);
            return null;
        }
    }

    /**
     * Exporta casas filtradas a JSON
     * @param {Object} filtros - Filtros a aplicar
     * @returns {string} JSON de casas
     */
    async exportarCasas(filtros = {}) {
        const casas = await this.getAllCasas(filtros);
        return JSON.stringify(casas, null, 2);
    }

    /**
     * Obtiene el rango de números de casas en una calle
     * @param {number} idCalle - ID de la calle
     * @returns {Object} Objeto con min, max y números faltantes
     */
    async getRangoCasasPorCalle(idCalle) {
        try {
            const casas = await this.getCasasByCalle(idCalle);
            
            if (casas.length === 0) {
                return { min: null, max: null, faltantes: [] };
            }

            // Extraer solo números (sin letras)
            const numeros = casas
                .map(casa => parseInt(casa.casa.replace(/[A-Za-z]/g, '')))
                .filter(num => !isNaN(num))
                .sort((a, b) => a - b);

            if (numeros.length === 0) {
                return { min: null, max: null, faltantes: [] };
            }

            const min = Math.min(...numeros);
            const max = Math.max(...numeros);
            const faltantes = [];

            // Encontrar números faltantes en el rango
            for (let i = min; i <= max; i++) {
                if (!numeros.includes(i)) {
                    faltantes.push(i);
                }
            }

            return { min, max, faltantes, total: casas.length };
        } catch (error) {
            console.error('Error obteniendo rango de casas:', error);
            return { min: null, max: null, faltantes: [] };
        }
    }

    /**
     * Verifica si un número de casa ya existe en una calle
     * @param {string} numeroCasa - Número de casa a verificar
     * @param {number} idCalle - ID de la calle
     * @returns {boolean} True si existe, false si no
     */
    async verificarCasaExiste(numeroCasa, idCalle) {
        try {
            const response = await fetch(this.apiUrl + `verificar_casa.php?numero=${numeroCasa}&id_calle=${idCalle}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const resultado = await response.json();
            return resultado.existe || false;
        } catch (error) {
            console.error('Error verificando casa:', error);
            return false;
        }
    }

    // ...existing code...
}

/**
 * Gestión de Casas - Sistema Web Simplificado
 * Permite registrar números de casas en calles específicas
 * Soporte para registro individual y masivo con separadores configurables
 * Basado en la estructura de BD: casas (id_casa, casa, id_condominio, id_calle)
 */

class CasasManager {
    constructor() {
        this.apiUrl = '../php/'; // Ruta a los archivos PHP
        this.casas = new Map();
        this.condominios = new Map();
        this.calles = new Map();
        
        // Configuración de separadores para números de casa
        this.separadores = {
            coma: ',',
            puntoycoma: ';',
            dospuntos: ':',
            espacio: ' ',
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
        this.loadCasasList();
        this.loadCondominiosList();
        this.setupValidation();
        this.setupSeparadorSelector();
    }

    /**
     * Configura los event listeners
     */
    setupEventListeners() {
        // Formulario de registro individual
        const formRegistro = document.getElementById('form-casa');
        if (formRegistro) {
            formRegistro.addEventListener('submit', (e) => {
                e.preventDefault();
                this.registrarCasa();
            });
        }

        // Formulario de registro masivo
        const formMasivo = document.getElementById('form-casas-masivo');
        if (formMasivo) {
            formMasivo.addEventListener('submit', (e) => {
                e.preventDefault();
                this.registrarCasasMasivo();
            });
        }

        // Preview en tiempo real para registro masivo
        const textareaMasivo = document.getElementById('numeros-casas');
        if (textareaMasivo) {
            textareaMasivo.addEventListener('input', (e) => {
                this.previewCasasMasivo(e.target.value);
            });
        }

        // Cambio de condominio - actualizar calles
        const selectCondominioIndividual = document.getElementById('id-condominio-casa');
        if (selectCondominioIndividual) {
            selectCondominioIndividual.addEventListener('change', (e) => {
                this.cargarCallesPorCondominio(e.target.value, 'id-calle-casa');
            });
        }

        const selectCondominioMasivo = document.getElementById('condominio-masivo');
        if (selectCondominioMasivo) {
            selectCondominioMasivo.addEventListener('change', (e) => {
                this.cargarCallesPorCondominio(e.target.value, 'calle-masivo');
            });
        }

        // Cambio de separador
        const selectSeparador = document.getElementById('select-separador');
        if (selectSeparador) {
            selectSeparador.addEventListener('change', (e) => {
                this.cambiarSeparador(e.target.value);
            });
        }

        // Filtros
        const filtroCondominio = document.getElementById('filtro-condominio');
        if (filtroCondominio) {
            filtroCondominio.addEventListener('change', (e) => {
                this.filtrarCasas();
            });
        }

        const filtroCalle = document.getElementById('filtro-calle');
        if (filtroCalle) {
            filtroCalle.addEventListener('change', (e) => {
                this.filtrarCasas();
            });
        }

        const busquedaCasa = document.getElementById('busqueda-casa');
        if (busquedaCasa) {
            busquedaCasa.addEventListener('input', (e) => {
                this.buscarCasas(e.target.value);
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
            { value: 'coma', label: 'Coma (,)', ejemplo: '1, 2, 3, 4, 5' },
            { value: 'puntoycoma', label: 'Punto y coma (;)', ejemplo: '1; 2; 3; 4; 5' },
            { value: 'dospuntos', label: 'Dos puntos (:)', ejemplo: '1: 2: 3: 4: 5' },
            { value: 'espacio', label: 'Espacio ( )', ejemplo: '1 2 3 4 5' },
            { value: 'pipe', label: 'Pipe (|)', ejemplo: '1 | 2 | 3 | 4 | 5' },
            { value: 'guion', label: 'Guión (-)', ejemplo: '1 - 2 - 3 - 4 - 5' },
            { value: 'saltolinea', label: 'Salto de línea', ejemplo: '1\n2\n3\n4\n5' }
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
        const textarea = document.getElementById('numeros-casas');
        if (textarea && textarea.value.trim()) {
            this.previewCasasMasivo(textarea.value);
        }
    }

    /**
     * Actualiza el ejemplo del separador seleccionado
     */
    actualizarEjemploSeparador() {
        const ejemploContainer = document.getElementById('ejemplo-separador');
        if (!ejemploContainer) return;

        const ejemplos = {
            coma: '1, 2, 3, 4, 5, 10, 15, 20',
            puntoycoma: '1; 2; 3; 4; 5; 10; 15; 20',
            dospuntos: '1: 2: 3: 4: 5: 10: 15: 20',
            espacio: '1 2 3 4 5 10 15 20',
            pipe: '1 | 2 | 3 | 4 | 5 | 10 | 15 | 20',
            guion: '1 - 2 - 3 - 4 - 5 - 10 - 15 - 20',
            saltolinea: '1\n2\n3\n4\n5\n10\n15\n20'
        };

        ejemploContainer.innerHTML = `
            <div class="ejemplo-separador">
                <strong>Ejemplo con separador seleccionado:</strong>
                <pre>${ejemplos[this.separadorActivo]}</pre>
            </div>
        `;
    }

    /**
     * Registra una casa individual
     */
    async registrarCasa() {
        try {
            const formData = this.getFormData();
            
            if (!this.validarDatos(formData)) {
                return;
            }

            this.showLoading(true);

            const response = await fetch(this.apiUrl + 'casas_save.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();

            if (result.success) {
                this.showMessage('Casa registrada exitosamente', 'success');
                this.clearForm();
                this.loadCasasList();
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
     * Registra múltiples casas
     */
    async registrarCasasMasivo() {
        try {
            const idCondominio = parseInt(document.getElementById('condominio-masivo')?.value);
            const idCalle = parseInt(document.getElementById('calle-masivo')?.value);
            const textoCasas = document.getElementById('numeros-casas')?.value.trim();

            if (!idCondominio || !idCalle || !textoCasas) {
                this.showMessage('Todos los campos son obligatorios', 'error');
                return;
            }

            const numerosProcesados = this.procesarTextoCasas(textoCasas);
            
            if (numerosProcesados.length === 0) {
                this.showMessage('No se detectaron números de casa válidos', 'error');
                return;
            }

            this.showLoading(true);

            const formData = {
                id_condominio: idCondominio,
                id_calle: idCalle,
                numeros_casas: numerosProcesados
            };

            const response = await fetch(this.apiUrl + 'casas_save_masivo.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();

            if (result.success) {
                this.showMessage(`${result.registradas} casas registradas exitosamente`, 'success');
                this.clearFormMasivo();
                this.loadCasasList();
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
     * Procesa el texto con números de casas
     */
    procesarTextoCasas(texto) {
        let separador = this.separadores[this.separadorActivo];
        
        return texto
            .split(separador)
            .map(numero => numero.trim())
            .filter(numero => numero.length > 0)
            .filter(numero => /^\d+[A-Za-z]?$/.test(numero)) // Solo números y opcionalmente una letra
            .sort((a, b) => {
                // Ordenar numéricamente
                const numA = parseInt(a.replace(/[A-Za-z]/g, ''));
                const numB = parseInt(b.replace(/[A-Za-z]/g, ''));
                return numA - numB;
            });
    }

    /**
     * Preview de casas en tiempo real
     */
    previewCasasMasivo(texto) {
        const previewContainer = document.getElementById('preview-casas');
        if (!previewContainer) return;

        if (!texto.trim()) {
            previewContainer.innerHTML = '<div class="preview-empty">Escriba números de casas para ver el preview...</div>';
            return;
        }

        const numerosProcesados = this.procesarTextoCasas(texto);
        
        if (numerosProcesados.length === 0) {
            previewContainer.innerHTML = '<div class="preview-error">No se detectaron números de casa válidos con el separador seleccionado</div>';
            return;
        }

        const html = `
            <div class="preview-success">
                <h4>Preview: ${numerosProcesados.length} casas detectadas</h4>
                <div class="numeros-grid">
                    ${numerosProcesados.map(numero => `<span class="numero-casa">${numero}</span>`).join('')}
                </div>
            </div>
        `;

        previewContainer.innerHTML = html;
    }

    /**
     * Carga calles por condominio
     */
    async cargarCallesPorCondominio(idCondominio, selectId) {
        const selectCalle = document.getElementById(selectId);
        if (!selectCalle) return;

        if (!idCondominio) {
            selectCalle.innerHTML = '<option value="">Seleccione un condominio primero</option>';
            return;
        }

        try {
            selectCalle.innerHTML = '<option value="">Cargando calles...</option>';

            const response = await fetch(this.apiUrl + `calles_by_condominio.php?id_condominio=${idCondominio}`);
            const calles = await response.json();

            if (calles.length === 0) {
                selectCalle.innerHTML = '<option value="">No hay calles registradas</option>';
                return;
            }

            selectCalle.innerHTML = '<option value="">Seleccione una calle</option>' +
                calles.map(calle => `<option value="${calle.id_calle}">${calle.nombre}</option>`).join('');

        } catch (error) {
            selectCalle.innerHTML = '<option value="">Error al cargar calles</option>';
        }
    }

    /**
     * Elimina una casa
     */
    async eliminarCasa(id) {
        try {
            this.showLoading(true);

            const response = await fetch(this.apiUrl + 'casas_delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id_casa: id })
            });

            const result = await response.json();

            if (result.success) {
                this.showMessage('Casa eliminada exitosamente', 'success');
                this.loadCasasList();
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
            id_condominio: parseInt(document.getElementById('id-condominio-casa')?.value),
            id_calle: parseInt(document.getElementById('id-calle-casa')?.value),
            casa: document.getElementById('numero-casa')?.value.trim()
        };
    }

    /**
     * Valida los datos del formulario
     */
    validarDatos(data) {
        if (!data.id_condominio) {
            this.showMessage('Seleccione un condominio', 'error');
            return false;
        }

        if (!data.id_calle) {
            this.showMessage('Seleccione una calle', 'error');
            return false;
        }

        if (!data.casa) {
            this.showMessage('Ingrese el número de casa', 'error');
            return false;
        }

        if (!/^\d+[A-Za-z]?$/.test(data.casa)) {
            this.showMessage('El número de casa debe ser numérico (ej: 1, 2A, 15B)', 'error');
            return false;
        }

        return true;
    }

    /**
     * Carga la lista de casas
     */
    async loadCasasList() {
        try {
            const response = await fetch(this.apiUrl + 'casas_list.php');
            const casas = await response.json();
            
            this.casas.clear();
            casas.forEach(casa => {
                this.casas.set(casa.id_casa, casa);
            });
            
            this.renderCasasList(casas);
        } catch (error) {
            console.error('Error cargando casas:', error);
            this.showMessage('Error al cargar las casas', 'error');
        }
    }

    /**
     * Carga la lista de condominios
     */
    async loadCondominiosList() {
        try {
            const response = await fetch(this.apiUrl + 'condominios_list.php');
            const condominios = await response.json();
            
            this.condominios.clear();
            condominios.forEach(condominio => {
                this.condominios.set(condominio.id_condominio, condominio);
            });
            
            this.populateCondominiosSelects(condominios);
        } catch (error) {
            console.error('Error cargando condominios:', error);
        }
    }

    /**
     * Llena los selects de condominios
     */
    populateCondominiosSelects(condominios) {
        const selects = [
            'id-condominio-casa',
            'condominio-masivo',
            'filtro-condominio'
        ];

        selects.forEach(selectId => {
            const select = document.getElementById(selectId);
            if (select) {
                const defaultOption = selectId === 'filtro-condominio' ? 
                    '<option value="">Todos los condominios</option>' : 
                    '<option value="">Seleccione un condominio</option>';
                
                select.innerHTML = defaultOption + 
                    condominios.map(cond => `<option value="${cond.id_condominio}">${cond.nombre}</option>`).join('');
            }
        });
    }

    /**
     * Renderiza la lista de casas agrupadas por condominio y calle
     */
    renderCasasList(casas) {
        const container = document.getElementById('casas-list');
        if (!container) return;

        if (casas.length === 0) {
            container.innerHTML = '<div class="empty-state">No hay casas registradas</div>';
            return;
        }

        // Agrupar por condominio y calle
        const grouped = {};
        casas.forEach(casa => {
            const condKey = casa.condominio_nombre || 'Sin condominio';
            const calleKey = casa.calle_nombre || 'Sin calle';
            
            if (!grouped[condKey]) grouped[condKey] = {};
            if (!grouped[condKey][calleKey]) grouped[condKey][calleKey] = [];
            
            grouped[condKey][calleKey].push(casa);
        });

        let html = '';
        Object.keys(grouped).forEach(condominio => {
            html += `<div class="condominio-group">
                <h3 class="condominio-title">🏢 ${condominio}</h3>`;
            
            Object.keys(grouped[condominio]).forEach(calle => {
                const casasCalle = grouped[condominio][calle];
                // Ordenar casas numéricamente
                casasCalle.sort((a, b) => {
                    const numA = parseInt(a.casa.replace(/[A-Za-z]/g, ''));
                    const numB = parseInt(b.casa.replace(/[A-Za-z]/g, ''));
                    return numA - numB;
                });

                html += `<div class="calle-group">
                    <h4 class="calle-title">🛣️ ${calle} (${casasCalle.length} casas)</h4>
                    <div class="casas-grid">
                        ${casasCalle.map(casa => `
                            <div class="casa-card">
                                <div class="casa-numero">${casa.casa}</div>
                                <div class="casa-acciones">
                                    <button onclick="casasManager.confirmarEliminar(${casa.id_casa}, '${casa.casa}')" 
                                            class="btn-eliminar" title="Eliminar casa">
                                        🗑️
                                    </button>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>`;
            });
            
            html += '</div>';
        });

        container.innerHTML = html;
    }

    /**
     * Filtra casas por condominio y calle
     */
    filtrarCasas() {
        // Implementar filtrado si es necesario
        // Por ahora, recargar la lista completa
        this.loadCasasList();
    }

    /**
     * Busca casas por número
     */
    buscarCasas(termino) {
        if (!termino.trim()) {
            this.loadCasasList();
            return;
        }

        const casasFiltradas = Array.from(this.casas.values())
            .filter(casa => casa.casa.toLowerCase().includes(termino.toLowerCase()));
        
        this.renderCasasList(casasFiltradas);
    }

    /**
     * Confirma eliminación de casa
     */
    confirmarEliminar(id, numero) {
        const modal = document.getElementById('modal-confirmar');
        const mensaje = document.getElementById('mensaje-confirmar');
        const btnConfirmar = document.getElementById('btn-confirmar-eliminar');
        
        if (modal && mensaje && btnConfirmar) {
            mensaje.textContent = `¿Está seguro de eliminar la casa número "${numero}"?`;
            btnConfirmar.onclick = () => this.eliminarCasa(id);
            modal.style.display = 'block';
        }
    }

    /**
     * Carga datos iniciales (modo demo)
     */
    loadData() {
        // Datos de ejemplo para desarrollo
        console.log('CasasManager inicializado');
    }

    /**
     * Configuración de validación en tiempo real
     */
    setupValidation() {
        const numeroCasa = document.getElementById('numero-casa');
        if (numeroCasa) {
            numeroCasa.addEventListener('input', (e) => {
                const valor = e.target.value;
                const valido = /^\d+[A-Za-z]?$/.test(valor);
                
                if (valor && !valido) {
                    e.target.classList.add('error');
                    this.showMessage('Formato inválido. Use números y opcionalmente una letra (ej: 1, 2A, 15B)', 'warning');
                } else {
                    e.target.classList.remove('error');
                }
            });
        }
    }

    /**
     * Limpia el formulario individual
     */
    clearForm() {
        const form = document.getElementById('form-casa');
        if (form) {
            form.reset();
            document.getElementById('id-calle-casa').innerHTML = '<option value="">Seleccione un condominio primero</option>';
        }
    }

    /**
     * Limpia el formulario masivo
     */
    clearFormMasivo() {
        const form = document.getElementById('form-casas-masivo');
        if (form) {
            form.reset();
            document.getElementById('calle-masivo').innerHTML = '<option value="">Seleccione un condominio primero</option>';
            document.getElementById('preview-casas').innerHTML = '<div class="preview-empty">Escriba números de casas para ver el preview...</div>';
        }
    }

    /**
     * Muestra mensaje al usuario
     */
    showMessage(message, type = 'info') {
        const messageContainer = document.getElementById('message-container');
        if (messageContainer) {
            messageContainer.innerHTML = `
                <div class="message ${type}">
                    ${message}
                    <button onclick="this.parentElement.remove()">×</button>
                </div>
            `;
            
            setTimeout(() => {
                const messageElement = messageContainer.querySelector('.message');
                if (messageElement) {
                    messageElement.remove();
                }
            }, 5000);
        }
    }

    /**
     * Muestra/oculta indicador de carga
     */
    showLoading(show) {
        const loadingElements = document.querySelectorAll('.loading');
        loadingElements.forEach(el => {
            el.style.display = show ? 'block' : 'none';
        });
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
     * Obtiene todas las casas o filtradas por criterios
     * @param {Object} filtros - Objeto con filtros opcionales
     * @returns {Array} Array de casas
     */
    async getAllCasas(filtros = {}) {
        try {
            let url = this.apiUrl + 'casas_list.php';
            const params = new URLSearchParams();

            // Aplicar filtros si se proporcionan
            if (filtros.id_condominio) {
                params.append('id_condominio', filtros.id_condominio);
            }
            if (filtros.id_calle) {
                params.append('id_calle', filtros.id_calle);
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
            
            const casas = await response.json();
            
            // Actualizar cache local
            this.casas.clear();
            casas.forEach(casa => {
                this.casas.set(casa.id_casa, casa);
            });
            
            return casas;
        } catch (error) {
            console.error('Error obteniendo casas:', error);
            this.showMessage('Error al obtener casas: ' + error.message, 'error');
            return [];
        }
    }

    /**
     * Obtiene una casa por su ID
     * @param {number} id - ID de la casa
     * @returns {Object|null} Objeto casa o null si no existe
     */
    async getCasaById(id) {
        try {
            const response = await fetch(this.apiUrl + `casa_by_id.php?id=${id}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const casa = await response.json();
            
            if (casa) {
                // Actualizar cache local
                this.casas.set(casa.id_casa, casa);
            }
            
            return casa;
        } catch (error) {
            console.error('Error obteniendo casa por ID:', error);
            this.showMessage('Error al obtener casa: ' + error.message, 'error');
            return null;
        }
    }

    /**
     * Obtiene casas por condominio específico
     * @param {number} idCondominio - ID del condominio
     * @returns {Array} Array de casas del condominio
     */
    async getCasasByCondominio(idCondominio) {
        return await this.getAllCasas({ id_condominio: idCondominio });
    }

    /**
     * Obtiene casas por calle específica
     * @param {number} idCalle - ID de la calle
     * @returns {Array} Array de casas de la calle
     */
    async getCasasByCalle(idCalle) {
        return await this.getAllCasas({ id_calle: idCalle });
    }

    /**
     * Busca casas por término de búsqueda (número de casa)
     * @param {string} termino - Término de búsqueda
     * @returns {Array} Array de casas que coinciden
     */
    async searchCasas(termino) {
        return await this.getAllCasas({ busqueda: termino });
    }

    /**
     * Obtiene estadísticas de casas
     * @returns {Object} Objeto con estadísticas
     */
    async getEstadisticasCasas() {
        try {
            const casas = await this.getAllCasas();
            
            const estadisticas = {
                total: casas.length,
                porCondominio: {},
                porCalle: {},
                numerosUnicos: new Set(),
                conLetras: 0,
                soloNumeros: 0
            };

            casas.forEach(casa => {
                // Estadísticas por condominio
                const condominio = casa.condominio_nombre || 'Sin condominio';
                estadisticas.porCondominio[condominio] = (estadisticas.porCondominio[condominio] || 0) + 1;

                // Estadísticas por calle
                const calle = casa.calle_nombre || 'Sin calle';
                estadisticas.porCalle[calle] = (estadisticas.porCalle[calle] || 0) + 1;

                // Análisis de números
                estadisticas.numerosUnicos.add(casa.casa);
                
                if (/[A-Za-z]/.test(casa.casa)) {
                    estadisticas.conLetras++;
                } else {
                    estadisticas.soloNumeros++;
                }
            });

            // Convertir Set a número
            estadisticas.numerosUnicos = estadisticas.numerosUnicos.size;

            return estadisticas;
        } catch (error) {
            console.error('Error obteniendo estadísticas de casas:', error);
            return null;
        }
    }

    /**
     * Exporta casas filtradas a JSON
     * @param {Object} filtros - Filtros a aplicar
     * @returns {string} JSON de casas
     */
    async exportarCasas(filtros = {}) {
        const casas = await this.getAllCasas(filtros);
        return JSON.stringify(casas, null, 2);
    }

    /**
     * Obtiene el rango de números de casas en una calle
     * @param {number} idCalle - ID de la calle
     * @returns {Object} Objeto con min, max y números faltantes
     */
    async getRangoCasasPorCalle(idCalle) {
        try {
            const casas = await this.getCasasByCalle(idCalle);
            
            if (casas.length === 0) {
                return { min: null, max: null, faltantes: [] };
            }

            // Extraer solo números (sin letras)
            const numeros = casas
                .map(casa => parseInt(casa.casa.replace(/[A-Za-z]/g, '')))
                .filter(num => !isNaN(num))
                .sort((a, b) => a - b);

            if (numeros.length === 0) {
                return { min: null, max: null, faltantes: [] };
            }

            const min = Math.min(...numeros);
            const max = Math.max(...numeros);
            const faltantes = [];

            // Encontrar números faltantes en el rango
            for (let i = min; i <= max; i++) {
                if (!numeros.includes(i)) {
                    faltantes.push(i);
                }
            }

            return { min, max, faltantes, total: casas.length };
        } catch (error) {
            console.error('Error obteniendo rango de casas:', error);
            return { min: null, max: null, faltantes: [] };
        }
    }

    /**
     * Verifica si un número de casa ya existe en una calle
     * @param {string} numeroCasa - Número de casa a verificar
     * @param {number} idCalle - ID de la calle
     * @returns {boolean} True si existe, false si no
     */
    async verificarCasaExiste(numeroCasa, idCalle) {
        try {
            const response = await fetch(this.apiUrl + `verificar_casa.php?numero=${numeroCasa}&id_calle=${idCalle}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const resultado = await response.json();
            return resultado.existe || false;
        } catch (error) {
            console.error('Error verificando casa:', error);
            return false;
        }
    }
}

// Solo exportar la clase, no crear instancia automática
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CasasManager;
}
