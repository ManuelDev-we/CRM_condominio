/**
 * FUNCIONES AVANZADAS PARA EL TEST INTERACTIVO CYBERHOLE
 * 
 * @description Funciones adicionales para mejorar la experiencia del test
 * @author Sistema Cyberhole - JavaScript Fanático
 * @version 1.0
 * @date 2025-07-28
 */

// Configuración avanzada
const CyberholeTest = {
    config: {
        apiTimeout: 30000,
        retryAttempts: 3,
        animationDuration: 300,
        notificationDuration: 5000
    },
    
    statistics: {
        startTime: null,
        endTime: null,
        totalSteps: 0,
        completedSteps: 0,
        failedSteps: 0,
        cyclesCompleted: 0
    },
    
    notifications: [],
    activeRequests: new Map()
};

// Inicialización avanzada
document.addEventListener('DOMContentLoaded', function() {
    initializeAdvancedFeatures();
    loadUserPreferences();
    setupEventListeners();
});

function initializeAdvancedFeatures() {
    // Agregar botón de modo oscuro
    addDarkModeToggle();
    
    // Configurar shortcuts de teclado
    setupKeyboardShortcuts();
    
    // Inicializar estadísticas
    CyberholeTest.statistics.startTime = new Date();
    
    // Configurar auto-guardado de progreso
    setupAutoSave();
    
    logMessage('🔧 Funciones avanzadas inicializadas', 'success');
}

function addDarkModeToggle() {
    const toggle = document.createElement('button');
    toggle.className = 'dark-mode-toggle';
    toggle.innerHTML = '🌙';
    toggle.setAttribute('data-tooltip', 'Alternar modo oscuro');
    toggle.onclick = toggleDarkMode;
    document.body.appendChild(toggle);
}

function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    const isDark = document.body.classList.contains('dark-mode');
    localStorage.setItem('darkMode', isDark);
    
    const toggle = document.querySelector('.dark-mode-toggle');
    toggle.innerHTML = isDark ? '☀️' : '🌙';
    
    showNotification(
        isDark ? 'Modo oscuro activado' : 'Modo claro activado',
        'info'
    );
}

function setupKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl + Enter: Iniciar test
        if (e.ctrlKey && e.key === 'Enter') {
            e.preventDefault();
            if (!isRunning) {
                startFullTest();
            }
        }
        
        // Ctrl + R: Reiniciar test
        if (e.ctrlKey && e.key === 'r') {
            e.preventDefault();
            if (!isRunning) {
                resetTest();
            }
        }
        
        // Ctrl + D: Descargar reporte
        if (e.ctrlKey && e.key === 'd') {
            e.preventDefault();
            if (document.getElementById('download-report').disabled === false) {
                downloadReport();
            }
        }
        
        // Escape: Detener test (si está corriendo)
        if (e.key === 'Escape') {
            e.preventDefault();
            if (isRunning) {
                confirmStopTest();
            }
        }
    });
}

function setupAutoSave() {
    // Guardar progreso cada 30 segundos
    setInterval(() => {
        if (isRunning || currentStep > 0) {
            saveProgressToLocalStorage();
        }
    }, 30000);
}

function setupEventListeners() {
    // Click en pasos para mostrar detalles
    document.querySelectorAll('.step-item').forEach(item => {
        item.addEventListener('click', function() {
            showStepDetails(this.getAttribute('data-step'));
        });
    });
    
    // Hover en secciones para mostrar información
    document.querySelectorAll('.test-section').forEach(section => {
        section.addEventListener('mouseenter', function() {
            highlightRelatedSteps(this.id);
        });
        
        section.addEventListener('mouseleave', function() {
            removeHighlight();
        });
    });
}

// Funciones de notificación avanzadas
function showNotification(message, type = 'info', duration = null) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <strong>${getNotificationIcon(type)}</strong>
        ${message}
        <button onclick="closeNotification(this)" style="float: right; background: none; border: none; color: white; font-size: 1.2em; cursor: pointer;">&times;</button>
    `;
    
    document.body.appendChild(notification);
    CyberholeTest.notifications.push(notification);
    
    // Auto-cerrar después del tiempo especificado
    const closeTime = duration || CyberholeTest.config.notificationDuration;
    setTimeout(() => {
        closeNotification(notification.querySelector('button'));
    }, closeTime);
}

function getNotificationIcon(type) {
    const icons = {
        'success': '✅',
        'error': '❌',
        'warning': '⚠️',
        'info': 'ℹ️'
    };
    return icons[type] || 'ℹ️';
}

function closeNotification(button) {
    const notification = button.parentElement;
    notification.style.animation = 'slideOutRight 0.3s ease-out';
    setTimeout(() => {
        if (notification.parentElement) {
            notification.parentElement.removeChild(notification);
        }
        const index = CyberholeTest.notifications.indexOf(notification);
        if (index > -1) {
            CyberholeTest.notifications.splice(index, 1);
        }
    }, 300);
}

// Funciones de estadísticas avanzadas
function updateStatistics(stepResult) {
    if (stepResult.success) {
        CyberholeTest.statistics.completedSteps++;
    } else {
        CyberholeTest.statistics.failedSteps++;
    }
    
    CyberholeTest.statistics.totalSteps++;
    
    // Actualizar display de estadísticas si existe
    updateStatisticsDisplay();
}

function updateStatisticsDisplay() {
    const statsDisplay = document.getElementById('statistics-display');
    if (!statsDisplay) return;
    
    const stats = CyberholeTest.statistics;
    const successRate = stats.totalSteps > 0 ? 
        Math.round((stats.completedSteps / stats.totalSteps) * 100) : 0;
    
    statsDisplay.innerHTML = `
        <div class="stat-item">
            <span class="stat-label">Pasos Completados:</span>
            <span class="stat-value">${stats.completedSteps}</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Pasos Fallidos:</span>
            <span class="stat-value">${stats.failedSteps}</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Tasa de Éxito:</span>
            <span class="stat-value">${successRate}%</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Ciclos Completados:</span>
            <span class="stat-value">${stats.cyclesCompleted}</span>
        </div>
    `;
}

// Funciones de gestión de pasos avanzadas
function showStepDetails(stepName) {
    const stepData = getStepInformation(stepName);
    
    const modal = createModal(
        `📋 Detalles del Paso: ${stepName}`,
        `
        <div class="step-details">
            <h4>Descripción:</h4>
            <p>${stepData.description}</p>
            
            <h4>Objetivos:</h4>
            <ul>
                ${stepData.objectives.map(obj => `<li>${obj}</li>`).join('')}
            </ul>
            
            <h4>Prerequisitos:</h4>
            <ul>
                ${stepData.prerequisites.map(req => `<li>${req}</li>`).join('')}
            </ul>
            
            ${stepData.expectedResults ? `
                <h4>Resultados Esperados:</h4>
                <pre>${JSON.stringify(stepData.expectedResults, null, 2)}</pre>
            ` : ''}
        </div>
        `
    );
    
    document.body.appendChild(modal);
}

function getStepInformation(stepName) {
    const stepInfo = {
        'init': {
            description: 'Inicialización completa del sistema de pruebas',
            objectives: ['Cargar todos los servicios', 'Verificar conexiones', 'Preparar entorno'],
            prerequisites: ['Sistema iniciado', 'Base de datos accesible'],
            expectedResults: { services_loaded: 12, cycle: 1 }
        },
        'register-admin': {
            description: 'Registro de administrador del sistema',
            objectives: ['Crear cuenta de admin', 'Validar credenciales', 'Configurar permisos'],
            prerequisites: ['Sistema inicializado', 'Token CSRF generado'],
            expectedResults: { admin_email: 'string', admin_id: 'number' }
        },
        'login-admin': {
            description: 'Autenticación del administrador',
            objectives: ['Validar credenciales', 'Crear sesión', 'Establecer permisos'],
            prerequisites: ['Admin registrado', 'Credenciales válidas'],
            expectedResults: { admin_session: 'number', user_type: 'admin' }
        },
        // ... Agregar más información para otros pasos
    };
    
    return stepInfo[stepName] || {
        description: 'Información no disponible',
        objectives: ['Ejecutar paso'],
        prerequisites: ['Pasos anteriores completados'],
        expectedResults: null
    };
}

function createModal(title, content) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>${title}</h3>
                <button class="modal-close" onclick="closeModal(this)">&times;</button>
            </div>
            <div class="modal-body">
                ${content}
            </div>
            <div class="modal-footer">
                <button class="btn" onclick="closeModal(this)">Cerrar</button>
            </div>
        </div>
    `;
    
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal(modal.querySelector('.modal-close'));
        }
    });
    
    return modal;
}

function closeModal(button) {
    const modal = button.closest('.modal-overlay');
    modal.style.animation = 'fadeOut 0.3s ease-out';
    setTimeout(() => {
        if (modal.parentElement) {
            modal.parentElement.removeChild(modal);
        }
    }, 300);
}

// Funciones de guardado y carga
function saveProgressToLocalStorage() {
    const progressData = {
        currentCycle,
        currentStep,
        testData,
        statistics: CyberholeTest.statistics,
        timestamp: new Date().toISOString()
    };
    
    localStorage.setItem('cyberholeTestProgress', JSON.stringify(progressData));
}

function loadProgressFromLocalStorage() {
    const saved = localStorage.getItem('cyberholeTestProgress');
    if (!saved) return false;
    
    try {
        const progressData = JSON.parse(saved);
        
        // Verificar que no sea muy antiguo (max 24 horas)
        const savedTime = new Date(progressData.timestamp);
        const now = new Date();
        if (now - savedTime > 24 * 60 * 60 * 1000) {
            localStorage.removeItem('cyberholeTestProgress');
            return false;
        }
        
        // Confirmar con el usuario si quiere cargar el progreso
        if (confirm('Se encontró progreso guardado. ¿Desea continuar desde donde se quedó?')) {
            currentCycle = progressData.currentCycle;
            currentStep = progressData.currentStep;
            testData = progressData.testData;
            CyberholeTest.statistics = progressData.statistics;
            
            updateProgress();
            showNotification('Progreso cargado exitosamente', 'success');
            return true;
        }
    } catch (e) {
        console.error('Error cargando progreso:', e);
        localStorage.removeItem('cyberholeTestProgress');
    }
    
    return false;
}

function loadUserPreferences() {
    // Cargar modo oscuro
    const darkMode = localStorage.getItem('darkMode') === 'true';
    if (darkMode) {
        document.body.classList.add('dark-mode');
        const toggle = document.querySelector('.dark-mode-toggle');
        if (toggle) toggle.innerHTML = '☀️';
    }
    
    // Cargar otras preferencias
    const autoSave = localStorage.getItem('autoSave') !== 'false';
    if (!autoSave) {
        clearInterval(CyberholeTest.autoSaveInterval);
    }
}

// Funciones de control avanzado
function confirmStopTest() {
    if (confirm('¿Está seguro de que desea detener el test en curso?')) {
        stopTest();
    }
}

function stopTest() {
    isRunning = false;
    
    // Cancelar todas las solicitudes activas
    CyberholeTest.activeRequests.forEach((controller, id) => {
        controller.abort();
    });
    CyberholeTest.activeRequests.clear();
    
    // Actualizar UI
    document.getElementById('start-test').disabled = false;
    document.getElementById('step-test').disabled = false;
    document.getElementById('reset-test').disabled = false;
    
    logMessage('🛑 Test detenido por el usuario', 'warning');
    showNotification('Test detenido', 'warning');
}

// Función de llamada API mejorada con retry y timeout
async function callAPIAdvanced(endpoint, data, retries = 3) {
    const controller = new AbortController();
    const requestId = Date.now().toString();
    
    CyberholeTest.activeRequests.set(requestId, controller);
    
    try {
        for (let attempt = 1; attempt <= retries; attempt++) {
            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data),
                    signal: controller.signal,
                    timeout: CyberholeTest.config.apiTimeout
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();
                CyberholeTest.activeRequests.delete(requestId);
                return result;
                
            } catch (error) {
                if (error.name === 'AbortError') {
                    throw new Error('Solicitud cancelada');
                }
                
                if (attempt === retries) {
                    throw error;
                }
                
                // Esperar antes del siguiente intento
                await sleep(1000 * attempt);
                logMessage(`🔄 Reintentando solicitud (intento ${attempt + 1}/${retries})`, 'warning');
            }
        }
    } finally {
        CyberholeTest.activeRequests.delete(requestId);
    }
}

// Funciones de utilidad adicionales
function highlightRelatedSteps(sectionId) {
    const section = document.getElementById(sectionId);
    if (!section) return;
    
    section.classList.add('highlighted');
    
    // Resaltar pasos relacionados
    const steps = section.querySelectorAll('.step-item');
    steps.forEach(step => {
        step.style.backgroundColor = 'rgba(0, 123, 255, 0.1)';
    });
}

function removeHighlight() {
    document.querySelectorAll('.test-section').forEach(section => {
        section.classList.remove('highlighted');
    });
    
    document.querySelectorAll('.step-item').forEach(step => {
        step.style.backgroundColor = '';
    });
}

function exportTestData() {
    const exportData = {
        metadata: {
            exportDate: new Date().toISOString(),
            version: '1.0',
            testType: 'Cyberhole Admin Services Test'
        },
        statistics: CyberholeTest.statistics,
        testData: testData,
        logs: getLogEntries()
    };
    
    const blob = new Blob([JSON.stringify(exportData, null, 2)], { 
        type: 'application/json' 
    });
    
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `cyberhole_test_export_${new Date().toISOString().slice(0,19).replace(/:/g, '-')}.json`;
    a.click();
    URL.revokeObjectURL(url);
    
    showNotification('Datos de test exportados exitosamente', 'success');
}

function getLogEntries() {
    const logContainer = document.getElementById('log-container');
    return Array.from(logContainer.children).map(entry => ({
        type: entry.className.split(' ')[1] || 'info',
        message: entry.textContent,
        timestamp: new Date().toISOString()
    }));
}

// Agregar CSS para modales y efectos
const additionalStyles = `
<style>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2000;
    animation: fadeIn 0.3s ease-out;
}

.modal-content {
    background: white;
    border-radius: 12px;
    max-width: 600px;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    animation: slideInUp 0.3s ease-out;
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: between;
    align-items: center;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    padding: 20px;
    border-top: 1px solid #e9ecef;
    text-align: right;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5em;
    cursor: pointer;
    color: #6c757d;
}

.modal-close:hover {
    color: #dc3545;
}

.step-details h4 {
    color: #495057;
    margin-top: 20px;
    margin-bottom: 10px;
}

.step-details ul {
    padding-left: 20px;
}

.step-details li {
    margin-bottom: 5px;
}

.highlighted {
    box-shadow: 0 0 20px rgba(0, 123, 255, 0.3);
    transform: scale(1.02);
    transition: all 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeOut {
    from { opacity: 1; }
    to { opacity: 0; }
}

@keyframes slideOutRight {
    from {
        opacity: 1;
        transform: translateX(0);
    }
    to {
        opacity: 0;
        transform: translateX(100%);
    }
}

.dark-mode {
    filter: invert(1) hue-rotate(180deg);
}

.dark-mode img,
.dark-mode video,
.dark-mode iframe {
    filter: invert(1) hue-rotate(180deg);
}
</style>
`;

// Agregar estilos al documento
document.head.insertAdjacentHTML('beforeend', additionalStyles);

// Inicializar cuando el documento esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeAdvancedFeatures);
} else {
    initializeAdvancedFeatures();
}
