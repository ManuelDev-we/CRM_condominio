/**
 * Gestión de Empleados - Sistema Web Completo
 * Permite registrar, editar, eliminar y gestionar empleados de condominios
 * Manejo de puestos dinámicos con filtrado y normalización lowercase
 * Basado en la estructura de BD: empleados_condominio
 */

class EmpleadosManager {
    constructor() {
        this.apiUrl = '../php/'; // Ruta a los archivos PHP
        this.empleados = new Map();
        this.condominios = new Map();
        this.puestos = new Set(); // Set dinámico de puestos
        this.empleadoEditando = null; // ID del empleado en edición
        
        this.loadData();
    }

    /**
     * Inicializa la interfaz web
     */
    init() {
        this.setupEventListeners();
        this.loadEmpleadosList();
        this.loadCondominiosList();
        this.setupValidation();
        this.loadPuestosList();
    }

    /**
     * Configura los event listeners
     */
    setupEventListeners() {
        // Formulario de registro/edición
        const formEmpleado = document.getElementById('form-empleado');
        if (formEmpleado) {
            formEmpleado.addEventListener('submit', (e) => {
                e.preventDefault();
                if (this.empleadoEditando) {
                    this.actualizarEmpleado();
                } else {
                    this.registrarEmpleado();
                }
            });
        }

        // Filtros
        const filtroCondominio = document.getElementById('filtro-condominio');
        if (filtroCondominio) {
            filtroCondominio.addEventListener('change', () => {
                this.filtrarEmpleados();
            });
        }

        const filtroPuesto = document.getElementById('filtro-puesto');
        if (filtroPuesto) {
            filtroPuesto.addEventListener('change', () => {
                this.filtrarEmpleados();
            });
        }

        const busquedaEmpleado = document.getElementById('busqueda-empleado');
        if (busquedaEmpleado) {
            busquedaEmpleado.addEventListener('input', (e) => {
                this.buscarEmpleados(e.target.value);
            });
        }

        // Filtros avanzados de fecha
        const filtroFechaDesde = document.getElementById('filtro-fecha-desde');
        if (filtroFechaDesde) {
            filtroFechaDesde.addEventListener('change', () => {
                this.filtrarEmpleados();
            });
        }

        const filtroFechaHasta = document.getElementById('filtro-fecha-hasta');
        if (filtroFechaHasta) {
            filtroFechaHasta.addEventListener('change', () => {
                this.filtrarEmpleados();
            });
        }

        // Botón limpiar filtros
        const btnLimpiarFiltros = document.getElementById('btn-limpiar-filtros');
        if (btnLimpiarFiltros) {
            btnLimpiarFiltros.addEventListener('click', () => {
                this.limpiarFiltros();
            });
        }

        // Botón mostrar estadísticas
        const btnEstadisticas = document.getElementById('btn-estadisticas');
        if (btnEstadisticas) {
            btnEstadisticas.addEventListener('click', () => {
                this.mostrarEstadisticas();
            });
        }

        // Campo de puesto con normalización
        const inputPuesto = document.getElementById('puesto-empleado');
        if (inputPuesto) {
            inputPuesto.addEventListener('input', (e) => {
                // Normalizar a lowercase mientras el usuario escribe
                const valor = e.target.value.toLowerCase().trim();
                e.target.value = valor;
                this.mostrarSugerenciasPuesto(valor);
            });

            inputPuesto.addEventListener('blur', (e) => {
                setTimeout(() => {
                    this.ocultarSugerenciasPuesto();
                }, 200);
            });
        }

        // Botón cancelar edición
        const btnCancelar = document.getElementById('btn-cancelar-edicion');
        if (btnCancelar) {
            btnCancelar.addEventListener('click', () => {
                this.cancelarEdicion();
            });
        }
    }

    /**
     * Registra un nuevo empleado
     */
    async registrarEmpleado() {
        try {
            const formData = this.getFormData();
            
            if (!this.validarDatos(formData)) {
                return;
            }

            this.showLoading(true);

            const response = await fetch(this.apiUrl + 'empleados_save.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();

            if (result.success) {
                this.showMessage('Empleado registrado exitosamente', 'success');
                this.clearForm();
                this.loadEmpleadosList();
                this.loadPuestosList(); // Actualizar lista de puestos
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
     * Actualiza un empleado existente
     */
    async actualizarEmpleado() {
        try {
            const formData = this.getFormData();
            formData.id_empleado = this.empleadoEditando;
            
            if (!this.validarDatos(formData)) {
                return;
            }

            this.showLoading(true);

            const response = await fetch(this.apiUrl + 'empleados_update.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();

            if (result.success) {
                this.showMessage('Empleado actualizado exitosamente', 'success');
                this.cancelarEdicion();
                this.loadEmpleadosList();
                this.loadPuestosList(); // Actualizar lista de puestos
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
     * Elimina un empleado
     */
    async eliminarEmpleado(id) {
        try {
            this.showLoading(true);

            const response = await fetch(this.apiUrl + 'empleados_delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id_empleado: id })
            });

            const result = await response.json();

            if (result.success) {
                this.showMessage('Empleado eliminado exitosamente', 'success');
                this.loadEmpleadosList();
                this.loadPuestosList(); // Actualizar lista de puestos
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
     * Inicia la edición de un empleado
     */
    editarEmpleado(id) {
        const empleado = this.empleados.get(id);
        if (!empleado) return;

        this.empleadoEditando = id;

        // Llenar el formulario con los datos del empleado
        document.getElementById('id-condominio-empleado').value = empleado.id_condominio;
        document.getElementById('nombres-empleado').value = empleado.nombres;
        document.getElementById('apellido1-empleado').value = empleado.apellido1;
        document.getElementById('apellido2-empleado').value = empleado.apellido2;
        document.getElementById('puesto-empleado').value = empleado.puesto;
        document.getElementById('fecha-contrato').value = empleado.fecha_contrato;

        // Cambiar la interfaz a modo edición
        this.activarModoEdicion();

        // Cambiar a la pestaña de formulario
        const tabFormulario = document.querySelector('[onclick*="tab-formulario"]');
        if (tabFormulario) {
            tabFormulario.click();
        }
    }

    /**
     * Activa el modo edición en la interfaz
     */
    activarModoEdicion() {
        const titulo = document.querySelector('#tab-formulario h2');
        const btnSubmit = document.querySelector('#form-empleado button[type="submit"]');
        const btnCancelar = document.getElementById('btn-cancelar-edicion');

        if (titulo) titulo.textContent = 'Editar Empleado';
        if (btnSubmit) btnSubmit.textContent = 'Actualizar Empleado';
        if (btnCancelar) btnCancelar.style.display = 'inline-block';
    }

    /**
     * Cancela la edición y vuelve al modo registro
     */
    cancelarEdicion() {
        this.empleadoEditando = null;
        this.clearForm();

        const titulo = document.querySelector('#tab-formulario h2');
        const btnSubmit = document.querySelector('#form-empleado button[type="submit"]');
        const btnCancelar = document.getElementById('btn-cancelar-edicion');

        if (titulo) titulo.textContent = 'Registro de Empleado';
        if (btnSubmit) btnSubmit.textContent = 'Registrar Empleado';
        if (btnCancelar) btnCancelar.style.display = 'none';
    }

    /**
     * Muestra sugerencias de puestos mientras el usuario escribe
     */
    mostrarSugerenciasPuesto(valor) {
        const sugerenciasContainer = document.getElementById('sugerencias-puesto');
        if (!sugerenciasContainer || !valor) {
            this.ocultarSugerenciasPuesto();
            return;
        }

        const puestosArray = Array.from(this.puestos);
        const sugerencias = puestosArray.filter(puesto => 
            puesto.toLowerCase().includes(valor.toLowerCase()) && puesto !== valor
        );

        if (sugerencias.length === 0) {
            this.ocultarSugerenciasPuesto();
            return;
        }

        const html = sugerencias.map(puesto => 
            `<div class="sugerencia-item" onclick="empleadosManager.seleccionarPuesto('${puesto}')">
                ${puesto}
            </div>`
        ).join('');

        sugerenciasContainer.innerHTML = html;
        sugerenciasContainer.style.display = 'block';
    }

    /**
     * Oculta las sugerencias de puestos
     */
    ocultarSugerenciasPuesto() {
        const sugerenciasContainer = document.getElementById('sugerencias-puesto');
        if (sugerenciasContainer) {
            sugerenciasContainer.style.display = 'none';
        }
    }

    /**
     * Selecciona un puesto de las sugerencias
     */
    seleccionarPuesto(puesto) {
        const inputPuesto = document.getElementById('puesto-empleado');
        if (inputPuesto) {
            inputPuesto.value = puesto;
        }
        this.ocultarSugerenciasPuesto();
    }

    /**
     * Obtiene datos del formulario
     */
    getFormData() {
        return {
            id_condominio: parseInt(document.getElementById('id-condominio-empleado')?.value),
            nombres: document.getElementById('nombres-empleado')?.value.trim(),
            apellido1: document.getElementById('apellido1-empleado')?.value.trim(),
            apellido2: document.getElementById('apellido2-empleado')?.value.trim(),
            puesto: document.getElementById('puesto-empleado')?.value.toLowerCase().trim(),
            fecha_contrato: document.getElementById('fecha-contrato')?.value || null
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

        if (!data.nombres) {
            this.showMessage('Ingrese el nombre del empleado', 'error');
            return false;
        }

        if (!data.apellido1) {
            this.showMessage('Ingrese el primer apellido', 'error');
            return false;
        }

        if (!data.apellido2) {
            this.showMessage('Ingrese el segundo apellido', 'error');
            return false;
        }

        if (!data.puesto) {
            this.showMessage('Ingrese el puesto del empleado', 'error');
            return false;
        }

        // Validar nombres y apellidos (solo letras y espacios)
        const regexNombres = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
        
        if (!regexNombres.test(data.nombres)) {
            this.showMessage('El nombre solo debe contener letras', 'error');
            return false;
        }

        if (!regexNombres.test(data.apellido1)) {
            this.showMessage('El primer apellido solo debe contener letras', 'error');
            return false;
        }

        if (!regexNombres.test(data.apellido2)) {
            this.showMessage('El segundo apellido solo debe contener letras', 'error');
            return false;
        }

        return true;
    }

    /**
     * Carga la lista de empleados
     */
    async loadEmpleadosList() {
        try {
            const response = await fetch(this.apiUrl + 'empleados_list.php');
            const empleados = await response.json();
            
            this.empleados.clear();
            empleados.forEach(empleado => {
                this.empleados.set(empleado.id_empleado, empleado);
            });
            
            this.renderEmpleadosList(empleados);
        } catch (error) {
            console.error('Error cargando empleados:', error);
            this.showMessage('Error al cargar los empleados', 'error');
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
     * Carga la lista de puestos únicos existentes
     */
    async loadPuestosList() {
        try {
            const response = await fetch(this.apiUrl + 'empleados_puestos.php');
            const puestos = await response.json();
            
            this.puestos.clear();
            puestos.forEach(puesto => {
                this.puestos.add(puesto.puesto);
            });
            
            this.populatePuestosFilter();
        } catch (error) {
            console.error('Error cargando puestos:', error);
        }
    }

    /**
     * Llena los selects de condominios
     */
    populateCondominiosSelects(condominios) {
        const selects = [
            'id-condominio-empleado',
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
     * Llena el filtro de puestos
     */
    populatePuestosFilter() {
        const select = document.getElementById('filtro-puesto');
        if (select) {
            const puestosArray = Array.from(this.puestos).sort();
            select.innerHTML = '<option value="">Todos los puestos</option>' + 
                puestosArray.map(puesto => `<option value="${puesto}">${puesto}</option>`).join('');
        }
    }

    /**
     * Renderiza la lista de empleados
     */
    renderEmpleadosList(empleados) {
        const container = document.getElementById('empleados-list');
        if (!container) return;

        if (empleados.length === 0) {
            container.innerHTML = '<div class="empty-state">No hay empleados registrados</div>';
            return;
        }

        // Agrupar por condominio
        const grouped = {};
        empleados.forEach(empleado => {
            const condKey = empleado.condominio_nombre || 'Sin condominio';
            if (!grouped[condKey]) grouped[condKey] = [];
            grouped[condKey].push(empleado);
        });

        let html = '';
        Object.keys(grouped).forEach(condominio => {
            const empleadosCondominio = grouped[condominio];
            
            html += `<div class="condominio-group">
                <h3 class="condominio-title">🏢 ${condominio} (${empleadosCondominio.length} empleados)</h3>
                <div class="empleados-grid">`;
            
            empleadosCondominio.forEach(empleado => {
                const fechaContrato = empleado.fecha_contrato ? 
                    new Date(empleado.fecha_contrato).toLocaleDateString('es-ES') : 
                    'No especificada';

                html += `
                    <div class="empleado-card">
                        <div class="empleado-header">
                            <div class="empleado-avatar">
                                ${empleado.nombres.charAt(0)}${empleado.apellido1.charAt(0)}
                            </div>
                            <div class="empleado-info">
                                <h4 class="empleado-nombre">${empleado.nombres} ${empleado.apellido1} ${empleado.apellido2}</h4>
                                <div class="empleado-puesto">${empleado.puesto}</div>
                            </div>
                        </div>
                        <div class="empleado-detalles">
                            <div class="detalle-item">
                                <span class="detalle-label">Fecha de contrato:</span>
                                <span class="detalle-valor">${fechaContrato}</span>
                            </div>
                        </div>
                        <div class="empleado-acciones">
                            <button onclick="empleadosManager.editarEmpleado(${empleado.id_empleado})" 
                                    class="btn-editar" title="Editar empleado">
                                ✏️
                            </button>
                            <button onclick="empleadosManager.confirmarEliminar(${empleado.id_empleado}, '${empleado.nombres} ${empleado.apellido1}')" 
                                    class="btn-eliminar" title="Eliminar empleado">
                                🗑️
                            </button>
                        </div>
                    </div>
                `;
            });
            
            html += '</div></div>';
        });

        container.innerHTML = html;
    }

    /**
     * Filtra empleados por condominio y puesto (versión mejorada)
     */
    async filtrarEmpleados() {
        await this.aplicarFiltrosAvanzados();
    }

    /**
     * Busca empleados por nombre (versión mejorada)
     */
    async buscarEmpleados(termino) {
        if (!termino.trim()) {
            await this.loadEmpleadosList();
            this.mostrarContadorResultados(this.empleados.size);
            return;
        }

        const empleadosFiltrados = await this.searchEmpleados(termino);
        this.renderEmpleadosList(empleadosFiltrados);
        this.mostrarContadorResultados(empleadosFiltrados.length);
    }

    /**
     * Confirma eliminación de empleado
     */
    confirmarEliminar(id, nombre) {
        const modal = document.getElementById('modal-confirmar');
        const mensaje = document.getElementById('mensaje-confirmar');
        const btnConfirmar = document.getElementById('btn-confirmar-eliminar');
        
        if (modal && mensaje && btnConfirmar) {
            mensaje.textContent = `¿Está seguro de eliminar al empleado "${nombre}"?`;
            btnConfirmar.onclick = () => this.eliminarEmpleado(id);
            modal.style.display = 'block';
        }
    }

    /**
     * Configuración de validación en tiempo real
     */
    setupValidation() {
        // Validación de nombres en tiempo real
        const inputs = [
            'nombres-empleado',
            'apellido1-empleado', 
            'apellido2-empleado'
        ];

        inputs.forEach(inputId => {
            const input = document.getElementById(inputId);
            if (input) {
                input.addEventListener('input', (e) => {
                    const valor = e.target.value;
                    const valido = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]*$/.test(valor);
                    
                    if (!valido) {
                        e.target.classList.add('error');
                    } else {
                        e.target.classList.remove('error');
                    }
                });
            }
        });
    }

    /**
     * Carga datos iniciales (modo demo)
     */
    loadData() {
        console.log('EmpleadosManager inicializado');
    }

    /**
     * Limpia el formulario
     */
    clearForm() {
        const form = document.getElementById('form-empleado');
        if (form) {
            form.reset();
        }
        this.ocultarSugerenciasPuesto();
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
     * Obtiene todos los empleados o filtrados por criterios específicos
     * @param {Object} filtros - Objeto con filtros opcionales
     * @returns {Array} Array de empleados
     */
    async getAllEmpleados(filtros = {}) {
        try {
            let url = this.apiUrl + 'empleados_list.php';
            const params = new URLSearchParams();

            // Aplicar filtros si se proporcionan
            if (filtros.id_condominio) {
                params.append('id_condominio', filtros.id_condominio);
            }
            if (filtros.puesto) {
                params.append('puesto', filtros.puesto.toLowerCase());
            }
            if (filtros.busqueda) {
                params.append('busqueda', filtros.busqueda);
            }
            if (filtros.fecha_desde) {
                params.append('fecha_desde', filtros.fecha_desde);
            }
            if (filtros.fecha_hasta) {
                params.append('fecha_hasta', filtros.fecha_hasta);
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
            
            const empleados = await response.json();
            
            // Actualizar cache local
            this.empleados.clear();
            empleados.forEach(empleado => {
                this.empleados.set(empleado.id_empleado, empleado);
            });
            
            return empleados;
        } catch (error) {
            console.error('Error obteniendo empleados:', error);
            this.showMessage('Error al obtener empleados: ' + error.message, 'error');
            return [];
        }
    }

    /**
     * Obtiene un empleado por su ID
     * @param {number} id - ID del empleado
     * @returns {Object|null} Objeto empleado o null si no existe
     */
    async getEmpleadoById(id) {
        try {
            const response = await fetch(this.apiUrl + `empleado_by_id.php?id=${id}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const empleado = await response.json();
            
            if (empleado) {
                // Actualizar cache local
                this.empleados.set(empleado.id_empleado, empleado);
            }
            
            return empleado;
        } catch (error) {
            console.error('Error obteniendo empleado por ID:', error);
            this.showMessage('Error al obtener empleado: ' + error.message, 'error');
            return null;
        }
    }

    /**
     * Obtiene empleados por condominio específico
     * @param {number} idCondominio - ID del condominio
     * @returns {Array} Array de empleados del condominio
     */
    async getEmpleadosByCondominio(idCondominio) {
        return await this.getAllEmpleados({ id_condominio: idCondominio });
    }

    /**
     * Obtiene empleados por puesto específico
     * @param {string} puesto - Puesto del empleado
     * @returns {Array} Array de empleados con ese puesto
     */
    async getEmpleadosByPuesto(puesto) {
        return await this.getAllEmpleados({ puesto: puesto });
    }

    /**
     * Busca empleados por término de búsqueda
     * @param {string} termino - Término de búsqueda
     * @returns {Array} Array de empleados que coinciden
     */
    async searchEmpleados(termino) {
        return await this.getAllEmpleados({ busqueda: termino });
    }

    /**
     * Obtiene empleados contratados en un rango de fechas
     * @param {string} fechaDesde - Fecha desde (YYYY-MM-DD)
     * @param {string} fechaHasta - Fecha hasta (YYYY-MM-DD)
     * @returns {Array} Array de empleados contratados en el rango
     */
    async getEmpleadosByFechaContrato(fechaDesde, fechaHasta) {
        return await this.getAllEmpleados({ 
            fecha_desde: fechaDesde, 
            fecha_hasta: fechaHasta 
        });
    }

    /**
     * Obtiene estadísticas de empleados
     * @returns {Object} Objeto con estadísticas
     */
    async getEstadisticasEmpleados() {
        try {
            const empleados = await this.getAllEmpleados();
            
            const estadisticas = {
                total: empleados.length,
                porCondominio: {},
                porPuesto: {},
                contratadosEsteAno: 0,
                contratadosEsteMes: 0
            };

            const fechaActual = new Date();
            const anoActual = fechaActual.getFullYear();
            const mesActual = fechaActual.getMonth();

            empleados.forEach(empleado => {
                // Estadísticas por condominio
                const condominio = empleado.condominio_nombre || 'Sin condominio';
                estadisticas.porCondominio[condominio] = (estadisticas.porCondominio[condominio] || 0) + 1;

                // Estadísticas por puesto
                const puesto = empleado.puesto || 'Sin puesto';
                estadisticas.porPuesto[puesto] = (estadisticas.porPuesto[puesto] || 0) + 1;

                // Estadísticas por fecha de contrato
                if (empleado.fecha_contrato) {
                    const fechaContrato = new Date(empleado.fecha_contrato);
                    if (fechaContrato.getFullYear() === anoActual) {
                        estadisticas.contratadosEsteAno++;
                        if (fechaContrato.getMonth() === mesActual) {
                            estadisticas.contratadosEsteMes++;
                        }
                    }
                }
            });

            return estadisticas;
        } catch (error) {
            console.error('Error obteniendo estadísticas:', error);
            return null;
        }
    }

    /**
     * Aplica filtros avanzados con múltiples criterios
     */
    async aplicarFiltrosAvanzados() {
        const filtros = {
            id_condominio: document.getElementById('filtro-condominio')?.value || null,
            puesto: document.getElementById('filtro-puesto')?.value || null,
            busqueda: document.getElementById('busqueda-empleado')?.value.trim() || null,
            fecha_desde: document.getElementById('filtro-fecha-desde')?.value || null,
            fecha_hasta: document.getElementById('filtro-fecha-hasta')?.value || null
        };

        // Limpiar filtros vacíos
        Object.keys(filtros).forEach(key => {
            if (!filtros[key]) {
                delete filtros[key];
            }
        });

        const empleadosFiltrados = await this.getAllEmpleados(filtros);
        this.renderEmpleadosList(empleadosFiltrados);
        
        // Mostrar contador de resultados
        this.mostrarContadorResultados(empleadosFiltrados.length);
    }

    /**
     * Muestra contador de resultados filtrados
     */
    mostrarContadorResultados(cantidad) {
        const contador = document.getElementById('contador-resultados');
        if (contador) {
            if (cantidad === 0) {
                contador.innerHTML = '<div class="contador-vacio">No se encontraron empleados con los filtros aplicados</div>';
            } else {
                contador.innerHTML = `<div class="contador-resultados">Mostrando ${cantidad} empleado${cantidad !== 1 ? 's' : ''}</div>`;
            }
        }
    }

    /**
     * Limpia todos los filtros y muestra todos los empleados
     */
    async limpiarFiltros() {
        // Limpiar campos de filtro
        const filtros = [
            'filtro-condominio',
            'filtro-puesto', 
            'busqueda-empleado',
            'filtro-fecha-desde',
            'filtro-fecha-hasta'
        ];

        filtros.forEach(filtroId => {
            const elemento = document.getElementById(filtroId);
            if (elemento) {
                elemento.value = '';
            }
        });

        // Cargar todos los empleados
        await this.loadEmpleadosList();
        
        // Limpiar contador
        const contador = document.getElementById('contador-resultados');
        if (contador) {
            contador.innerHTML = '';
        }
    }

    /**
     * Obtiene resumen de datos del sistema (usando datos locales de empleados)
     * @returns {Object} Resumen con contadores de empleados
     */
    async getResumenSistema() {
        try {
            const empleados = await this.getAllEmpleados();

            return {
                empleados: {
                    total: empleados.length,
                    activos: empleados.filter(e => e.activo !== false).length
                },
                puestos: {
                    total: this.puestos.size,
                    lista: Array.from(this.puestos).sort()
                }
            };
        } catch (error) {
            console.error('Error obteniendo resumen:', error);
            return null;
        }
    }

    /**
     * Exporta empleados filtrados a JSON
     * @param {Object} filtros - Filtros a aplicar
     * @returns {string} JSON de empleados
     */
    async exportarEmpleados(filtros = {}) {
        const empleados = await this.getAllEmpleados(filtros);
        return JSON.stringify(empleados, null, 2);
    }

    /**
     * Muestra estadísticas del sistema
     */
    async mostrarEstadisticas() {
        try {
            const [estadisticasEmpleados, resumenSistema] = await Promise.all([
                this.getEstadisticasEmpleados(),
                this.getResumenSistema()
            ]);

            const modal = document.getElementById('modal-estadisticas');
            const contenido = document.getElementById('contenido-estadisticas');
            
            if (!modal || !contenido) return;

            let html = '<h3>📊 Estadísticas del Sistema</h3>';
            
            // Resumen general
            html += `
                <div class="estadisticas-section">
                    <h4>📈 Resumen General</h4>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-number">${resumenSistema.empleados.total}</div>
                            <div class="stat-label">Total Empleados</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">${resumenSistema.condominios.total}</div>
                            <div class="stat-label">Total Condominios</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">${resumenSistema.calles.total}</div>
                            <div class="stat-label">Total Calles</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">${resumenSistema.puestos.total}</div>
                            <div class="stat-label">Tipos de Puestos</div>
                        </div>
                    </div>
                </div>
            `;

            // Empleados por condominio
            html += `
                <div class="estadisticas-section">
                    <h4>🏢 Empleados por Condominio</h4>
                    <div class="stats-list">
                        ${Object.entries(estadisticasEmpleados.porCondominio)
                            .sort((a, b) => b[1] - a[1])
                            .map(([condominio, cantidad]) => `
                                <div class="stat-row">
                                    <span class="stat-name">${condominio}</span>
                                    <span class="stat-value">${cantidad}</span>
                                </div>
                            `).join('')}
                    </div>
                </div>
            `;

            // Empleados por puesto
            html += `
                <div class="estadisticas-section">
                    <h4>👔 Empleados por Puesto</h4>
                    <div class="stats-list">
                        ${Object.entries(estadisticasEmpleados.porPuesto)
                            .sort((a, b) => b[1] - a[1])
                            .map(([puesto, cantidad]) => `
                                <div class="stat-row">
                                    <span class="stat-name">${puesto}</span>
                                    <span class="stat-value">${cantidad}</span>
                                </div>
                            `).join('')}
                    </div>
                </div>
            `;

            // Estadísticas de contratación
            html += `
                <div class="estadisticas-section">
                    <h4>📅 Contrataciones</h4>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-number">${estadisticasEmpleados.contratadosEsteAno}</div>
                            <div class="stat-label">Este Año</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">${estadisticasEmpleados.contratadosEsteMes}</div>
                            <div class="stat-label">Este Mes</div>
                        </div>
                    </div>
                </div>
            `;

            contenido.innerHTML = html;
            modal.style.display = 'block';

        } catch (error) {
            this.showMessage('Error al cargar estadísticas', 'error');
        }
    }

    // ...existing code...
}

// Solo exportar la clase, no crear instancia automática
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EmpleadosManager;
}
