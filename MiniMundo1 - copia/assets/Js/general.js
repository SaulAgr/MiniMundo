// ==================== FUNCIONES DE INTERFAZ ====================

function mostrarSeccion(seccionId, evento) {
    if (evento) evento.preventDefault();
    document.querySelectorAll('.pantalla').forEach(seccion => seccion.classList.add('d-none'));
    const seccionActual = document.getElementById(seccionId);
    if (seccionActual) seccionActual.classList.remove('d-none');
    document.querySelectorAll('.nav-link').forEach(enlace => {
        enlace.classList.remove('active');
        if (enlace.getAttribute('href') === `#${seccionId}`) {
            enlace.classList.add('active');
        }
    });
}

function crearModal(html) {
    let modalBg = document.createElement('div');
    modalBg.style.position = "fixed";
    modalBg.style.top = 0;
    modalBg.style.left = 0;
    modalBg.style.width = "100vw";
    modalBg.style.height = "100vh";
    modalBg.style.background = "rgba(0,0,0,0.45)";
    modalBg.style.zIndex = 9999;
    modalBg.id = "modal-js";
    let modalCont = document.createElement('div');
    modalCont.style.position = "absolute";
    modalCont.style.top = "50%";
    modalCont.style.left = "50%";
    modalCont.style.transform = "translate(-50%,-50%)";
    modalCont.className = "bg-white shadow rounded";
    modalCont.innerHTML = html;
    modalBg.appendChild(modalCont);
    document.body.appendChild(modalBg);
    modalBg.addEventListener("click", function(e) {
        if(e.target === modalBg) cerrarModal();
    });
    return modalBg;
}
function cerrarModal() {
    let m = document.getElementById("modal-js");
    if(m) m.remove();
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