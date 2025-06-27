class Personas {
    constructor() {
        this.personas = this.loadPersonas();
        this.currentId = this.generateNextId();
    }

    // Cargar personas desde localStorage
    loadPersonas() {
        const stored = localStorage.getItem('personas');
        return stored ? JSON.parse(stored) : [];
    }

    // Guardar personas en localStorage
    savePersonas() {
        localStorage.setItem('personas', JSON.stringify(this.personas));
    }

    // Generar siguiente ID
    generateNextId() {
        if (this.personas.length === 0) return 1;
        return Math.max(...this.personas.map(p => p.id)) + 1;
    }

    // Registrar nueva persona
    registrarPersona(nombre, email, telefono, id_condominio, id_calle, id_casa, tipo_usuario = 'residente') {
        try {
            // Validaciones
            if (!nombre || !email || !telefono || !id_condominio || !id_calle || !id_casa) {
                throw new Error('Todos los campos son obligatorios');
            }

            if (!this.validarEmail(email)) {
                throw new Error('Email inválido');
            }

            if (!this.validarTelefono(telefono)) {
                throw new Error('Teléfono inválido');
            }

            // Verificar si ya existe una persona con el mismo email
            if (this.personas.some(p => p.email.toLowerCase() === email.toLowerCase())) {
                throw new Error('Ya existe una persona registrada con este email');
            }

            // Obtener información de la casa, calle y condominio
            const casaInfo = this.obtenerInfoCasa(id_casa, id_calle, id_condominio);
            
            const nuevaPersona = {
                id: this.currentId++,
                nombre: nombre.trim(),
                email: email.toLowerCase().trim(),
                telefono: telefono.trim(),
                id_condominio: parseInt(id_condominio),
                id_calle: parseInt(id_calle),
                id_casa: parseInt(id_casa),
                tipo_usuario: tipo_usuario,
                fecha_registro: new Date().toISOString(),
                estado: 'activo',
                // Información adicional para mostrar
                direccion_completa: casaInfo.direccion_completa,
                nombre_condominio: casaInfo.nombre_condominio,
                nombre_calle: casaInfo.nombre_calle,
                numero_casa: casaInfo.numero_casa
            };

            this.personas.push(nuevaPersona);
            this.savePersonas();

            return {
                success: true,
                message: 'Persona registrada exitosamente',
                data: nuevaPersona
            };

        } catch (error) {
            return {
                success: false,
                message: error.message
            };
        }
    }

    // Obtener información completa de casa, calle y condominio
    obtenerInfoCasa(id_casa, id_calle, id_condominio) {
        try {
            // Obtener datos de condominios
            const condominios = JSON.parse(localStorage.getItem('condominios') || '[]');
            const condominio = condominios.find(c => c.id === parseInt(id_condominio));

            // Obtener datos de calles
            const calles = JSON.parse(localStorage.getItem('calles') || '[]');
            const calle = calles.find(c => c.id === parseInt(id_calle));

            // Obtener datos de casas
            const casas = JSON.parse(localStorage.getItem('casas') || '[]');
            const casa = casas.find(c => c.id === parseInt(id_casa));

            return {
                direccion_completa: `${casa?.numero || 'N/A'}, ${calle?.nombre || 'N/A'}, ${condominio?.nombre || 'N/A'}`,
                nombre_condominio: condominio?.nombre || 'N/A',
                nombre_calle: calle?.nombre || 'N/A',
                numero_casa: casa?.numero || 'N/A'
            };
        } catch (error) {
            return {
                direccion_completa: 'Información no disponible',
                nombre_condominio: 'N/A',
                nombre_calle: 'N/A',
                numero_casa: 'N/A'
            };
        }
    }

    // Validar email
    validarEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Validar teléfono
    validarTelefono(telefono) {
        const telefonoRegex = /^[\d\s\-\+\(\)]{8,15}$/;
        return telefonoRegex.test(telefono);
    }

    // Obtener todas las personas
    getAll() {
        return this.personas.map(persona => ({
            ...persona,
            direccion_completa: this.obtenerInfoCasa(persona.id_casa, persona.id_calle, persona.id_condominio).direccion_completa
        }));
    }

    // Obtener personas por condominio
    getByCondominio(id_condominio) {
        return this.personas
            .filter(persona => persona.id_condominio === parseInt(id_condominio))
            .map(persona => ({
                ...persona,
                direccion_completa: this.obtenerInfoCasa(persona.id_casa, persona.id_calle, persona.id_condominio).direccion_completa
            }));
    }

    // Obtener persona por ID
    getById(id) {
        const persona = this.personas.find(p => p.id === parseInt(id));
        if (persona) {
            return {
                ...persona,
                direccion_completa: this.obtenerInfoCasa(persona.id_casa, persona.id_calle, persona.id_condominio).direccion_completa
            };
        }
        return null;
    }

    // Buscar personas
    search(termino, filtros = {}) {
        let resultados = this.personas;

        // Filtrar por término de búsqueda
        if (termino) {
            const terminoLower = termino.toLowerCase();
            resultados = resultados.filter(persona =>
                persona.nombre.toLowerCase().includes(terminoLower) ||
                persona.email.toLowerCase().includes(terminoLower) ||
                persona.telefono.includes(terminoLower)
            );
        }

        // Aplicar filtros adicionales
        if (filtros.id_condominio) {
            resultados = resultados.filter(p => p.id_condominio === parseInt(filtros.id_condominio));
        }

        if (filtros.id_calle) {
            resultados = resultados.filter(p => p.id_calle === parseInt(filtros.id_calle));
        }

        if (filtros.id_casa) {
            resultados = resultados.filter(p => p.id_casa === parseInt(filtros.id_casa));
        }

        if (filtros.tipo_usuario) {
            resultados = resultados.filter(p => p.tipo_usuario === filtros.tipo_usuario);
        }

        if (filtros.estado) {
            resultados = resultados.filter(p => p.estado === filtros.estado);
        }

        // Agregar información de dirección completa
        return resultados.map(persona => ({
            ...persona,
            direccion_completa: this.obtenerInfoCasa(persona.id_casa, persona.id_calle, persona.id_condominio).direccion_completa
        }));
    }

    // Eliminar persona
    eliminarPersona(id) {
        try {
            const index = this.personas.findIndex(p => p.id === parseInt(id));
            
            if (index === -1) {
                throw new Error('Persona no encontrada');
            }

            const personaEliminada = this.personas[index];
            this.personas.splice(index, 1);
            this.savePersonas();

            return {
                success: true,
                message: `Persona ${personaEliminada.nombre} eliminada exitosamente`,
                data: personaEliminada
            };

        } catch (error) {
            return {
                success: false,
                message: error.message
            };
        }
    }

    // Actualizar persona
    actualizarPersona(id, datosActualizados) {
        try {
            const index = this.personas.findIndex(p => p.id === parseInt(id));
            
            if (index === -1) {
                throw new Error('Persona no encontrada');
            }

            // Validar email si se está actualizando
            if (datosActualizados.email && !this.validarEmail(datosActualizados.email)) {
                throw new Error('Email inválido');
            }

            // Validar teléfono si se está actualizando
            if (datosActualizados.telefono && !this.validarTelefono(datosActualizados.telefono)) {
                throw new Error('Teléfono inválido');
            }

            // Verificar email duplicado
            if (datosActualizados.email) {
                const emailExistente = this.personas.find(p => 
                    p.id !== parseInt(id) && 
                    p.email.toLowerCase() === datosActualizados.email.toLowerCase()
                );
                if (emailExistente) {
                    throw new Error('Ya existe una persona registrada con este email');
                }
            }

            // Actualizar datos
            Object.keys(datosActualizados).forEach(key => {
                if (datosActualizados[key] !== undefined && datosActualizados[key] !== null) {
                    this.personas[index][key] = datosActualizados[key];
                }
            });

            // Actualizar información de dirección si cambió la ubicación
            if (datosActualizados.id_casa || datosActualizados.id_calle || datosActualizados.id_condominio) {
                const casaInfo = this.obtenerInfoCasa(
                    this.personas[index].id_casa,
                    this.personas[index].id_calle,
                    this.personas[index].id_condominio
                );
                this.personas[index].direccion_completa = casaInfo.direccion_completa;
                this.personas[index].nombre_condominio = casaInfo.nombre_condominio;
                this.personas[index].nombre_calle = casaInfo.nombre_calle;
                this.personas[index].numero_casa = casaInfo.numero_casa;
            }

            this.savePersonas();

            return {
                success: true,
                message: 'Persona actualizada exitosamente',
                data: this.personas[index]
            };

        } catch (error) {
            return {
                success: false,
                message: error.message
            };
        }
    }

    // Cambiar estado de persona
    cambiarEstado(id, nuevoEstado) {
        try {
            const index = this.personas.findIndex(p => p.id === parseInt(id));
            
            if (index === -1) {
                throw new Error('Persona no encontrada');
            }

            const estadosValidos = ['activo', 'inactivo', 'suspendido'];
            if (!estadosValidos.includes(nuevoEstado)) {
                throw new Error('Estado inválido');
            }

            this.personas[index].estado = nuevoEstado;
            this.savePersonas();

            return {
                success: true,
                message: `Estado de la persona cambiado a ${nuevoEstado}`,
                data: this.personas[index]
            };

        } catch (error) {
            return {
                success: false,
                message: error.message
            };
        }
    }

    // Exportar datos
    export(formato = 'json') {
        try {
            const datos = this.getAll();
            
            if (formato === 'csv') {
                return this.exportToCSV(datos);
            } else {
                return {
                    success: true,
                    data: datos,
                    formato: 'json'
                };
            }
        } catch (error) {
            return {
                success: false,
                message: error.message
            };
        }
    }

    // Exportar a CSV
    exportToCSV(datos) {
        try {
            const headers = ['ID', 'Nombre', 'Email', 'Teléfono', 'Condominio', 'Calle', 'Casa', 'Tipo Usuario', 'Estado', 'Fecha Registro'];
            const csvContent = [headers.join(',')];

            datos.forEach(persona => {
                const row = [
                    persona.id,
                    `"${persona.nombre}"`,
                    persona.email,
                    persona.telefono,
                    `"${persona.nombre_condominio}"`,
                    `"${persona.nombre_calle}"`,
                    persona.numero_casa,
                    persona.tipo_usuario,
                    persona.estado,
                    new Date(persona.fecha_registro).toLocaleDateString()
                ];
                csvContent.push(row.join(','));
            });

            return {
                success: true,
                data: csvContent.join('\n'),
                formato: 'csv'
            };
        } catch (error) {
            return {
                success: false,
                message: error.message
            };
        }
    }

    // Obtener estadísticas
    getEstadisticas() {
        try {
            const total = this.personas.length;
            const activos = this.personas.filter(p => p.estado === 'activo').length;
            const inactivos = this.personas.filter(p => p.estado === 'inactivo').length;
            const suspendidos = this.personas.filter(p => p.estado === 'suspendido').length;

            // Estadísticas por tipo de usuario
            const residentes = this.personas.filter(p => p.tipo_usuario === 'residente').length;
            const visitantes = this.personas.filter(p => p.tipo_usuario === 'visitante').length;
            const inquilinos = this.personas.filter(p => p.tipo_usuario === 'inquilino').length;

            // Estadísticas por condominio
            const porCondominio = {};
            this.personas.forEach(persona => {
                const condominio = persona.nombre_condominio || 'Sin definir';
                porCondominio[condominio] = (porCondominio[condominio] || 0) + 1;
            });

            return {
                total,
                porEstado: {
                    activos,
                    inactivos,
                    suspendidos
                },
                porTipoUsuario: {
                    residentes,
                    visitantes,
                    inquilinos
                },
                porCondominio
            };
        } catch (error) {
            console.error('Error al obtener estadísticas:', error);
            return {
                total: 0,
                porEstado: { activos: 0, inactivos: 0, suspendidos: 0 },
                porTipoUsuario: { residentes: 0, visitantes: 0, inquilinos: 0 },
                porCondominio: {}
            };
        }
    }
}

// Solo exportar la clase, no crear instancia automática
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Personas;
}