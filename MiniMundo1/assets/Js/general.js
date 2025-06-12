// ==================== FUNCIONES DE INTERFAZ ====================
function mostrarSeccion(seccionId, evento) {
    if (evento) evento.preventDefault();
    
    // Ocultar todas las secciones
    document.querySelectorAll('.pantalla').forEach(seccion => {
        seccion.classList.add('d-none');
    });
    
    // Mostrar sección actual
    const seccionActual = document.getElementById(seccionId);
    if (seccionActual) {
        seccionActual.classList.remove('d-none');
    }
    
    // Actualizar navegación activa
    document.querySelectorAll('.nav-link').forEach(enlace => {
        enlace.classList.remove('active');
        if (enlace.getAttribute('href') === `#${seccionId}`) {
            enlace.classList.add('active');
        }
    });
}

// ==================== MANEJO DE API ====================
async function hacerPeticion(url, datos = {}, metodo = 'POST') {
    try {
        const formData = new FormData();
        for (const clave in datos) {
            formData.append(clave, datos[clave]);
        }

        const respuesta = await fetch(url, {
            method: metodo,
            body: metodo === 'GET' ? null : formData
        });

        if (!respuesta.ok) {
            throw new Error(`Error ${respuesta.status}: ${respuesta.statusText}`);
        }

        return await respuesta.json();
    } catch (error) {
        console.error('Error en petición:', error);
        mostrarAlerta('error', error.message);
        throw error;
    }
}

// ==================== MANEJO DE DATOS ====================
function crearTabla(datos, columnas, acciones = null) {
    if (!datos || datos.length === 0) {
        return '<div class="alert alert-info">No hay datos disponibles</div>';
    }

    const encabezados = columnas.map(col => `<th>${col.titulo}</th>`).join('');
    const filas = datos.map(item => {
        const celdas = columnas.map(col => {
            const valor = col.formato ? col.formato(item[col.campo]) : (item[col.campo] || '-');
            return `<td>${valor}</td>`;
        }).join('');
        
        const botones = acciones ? `<td>${acciones(item)}</td>` : '';
        return `<tr>${celdas}${botones}</tr>`;
    }).join('');

    return `
        <table class="table table-striped table-hover">
            <thead><tr>${encabezados}${acciones ? '<th>Acciones</th>' : ''}</tr></thead>
            <tbody>${filas}</tbody>
        </table>
    `;
}

function mostrarAlerta(tipo, mensaje) {
    const contenedor = document.createElement('div');
    contenedor.className = `alert alert-${tipo} alert-dismissible fade show`;
    contenedor.role = 'alert';
    contenedor.innerHTML = `
        ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const contenedorAlertas = document.getElementById('alert-container') || document.body;
    contenedorAlertas.prepend(contenedor);
    
    setTimeout(() => contenedor.remove(), 5000);
}