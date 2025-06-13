// Eliminar clase (coordinador)
function eliminarClase(id) {
    if (!confirm("¿Seguro que quieres eliminar la clase? Se enviará solicitud al director.")) return;
    fetch('../crud/crudCoordinador.php', {
        method: 'POST',
        body: new URLSearchParams({
            accion: 'eliminar_clase',
            id: id
        })
    }).then(r => r.json()).then(res => {
        alert(res.msg);
        if (res.ok) cargarClases(); // Refresca la tabla si quieres
    });
}

// Editar clase (coordinador)
function editarClase(id) {
    // Recoge los nuevos datos del formulario de edición
    const nombre = document.getElementById('nombreEditar').value;
    const periodo = document.getElementById('periodoEditar').value;
    const profesor_id = document.getElementById('profesorEditar').value;
    const cup_maximo = document.getElementById('cupoEditar').value;

    fetch('../crud/crudCoordinador.php', {
        method: 'POST',
        body: new URLSearchParams({
            accion: 'editar_clase',
            id: id,
            nombre: nombre,
            periodo: periodo,
            profesor_id: profesor_id,
            cup_maximo: cup_maximo
        })
    }).then(r => r.json()).then(res => {
        alert(res.msg);
        if (res.ok) cargarClases();
    });
}

// Director: Listar solicitudes pendientes
function cargarSolicitudes() {
    fetch('../crud/crudCoordinador.php', {
        method: 'POST',
        body: new URLSearchParams({accion: 'listar_solicitudes'})
    })
    .then(r => r.json())
    .then(solicitudes => {
        let html = '';
        solicitudes.forEach(sol => {
            html += `<tr>
                <td>${sol.id}</td>
                <td>${sol.tipo}</td>
                <td>${sol.entidad_id}</td>
                <td>${sol.solicitante_id}</td>
                <td>${sol.fecha_solicitud}</td>
                <td>
                    <button onclick="aprobarSolicitud(${sol.id})">Aprobar</button>
                    <button onclick="rechazarSolicitud(${sol.id})">Rechazar</button>
                </td>
            </tr>`;
        });
        document.getElementById('tablaSolicitudes').innerHTML = html;
    });
}

// Director: Aprobar solicitud
function aprobarSolicitud(id) {
    if (!confirm("¿Aprobar solicitud?")) return;
    fetch('../crud/crudCoordinador.php', {
        method: 'POST',
        body: new URLSearchParams({accion: 'aprobar_solicitud', id: id})
    }).then(r => r.json()).then(res => {
        alert(res.ok ? "Solicitud aprobada" : res.msg);
        cargarSolicitudes();
    });
}

// Director: Rechazar solicitud
function rechazarSolicitud(id) {
    let resp = prompt("Motivo de rechazo:");
    fetch('../crud/crudCoordinador.php', {
        method: 'POST',
        body: new URLSearchParams({accion: 'rechazar_solicitud', id: id, respuesta: resp})
    }).then(r => r.json()).then(res => {
        alert("Solicitud rechazada");
        cargarSolicitudes();
    });
}