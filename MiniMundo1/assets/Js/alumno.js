document.addEventListener('DOMContentLoaded', function() {
    window.mostrarSeccion = function(seccion, ev) {
        document.querySelectorAll('.pantalla').forEach(div => div.classList.add('d-none'));
        document.getElementById(seccion).classList.remove('d-none');
        if (ev) ev.preventDefault();

        switch(seccion) {
            case 'clases': cargarClases(); break;
            case 'avisos': cargarAvisos(); break;
            case 'tareas': cargarTareas(); break;
            case 'calificaciones': cargarCalificaciones(); break;
            case 'reportes': cargarReportes(); break;
            case 'perfil': cargarPerfil(); break;
            // case 'mensajes': cargarMensajes(); break; // se agrega cuando implementemos chat
        }
    };

    function cargarClases() {
        fetch('../crud/crudAlumno.php', {
            method:'POST',
            body: new URLSearchParams({accion:'mis_clases'})
        })
        .then(r=>r.json())
        .then(clases=>{
            let html = `<table class="table table-sm"><thead>
                <tr><th>Clase</th><th>Periodo</th><th>Profesor</th></tr></thead><tbody>`;
            if(clases.length===0) html += `<tr><td colspan="3">No estás inscrito en ninguna clase.</td></tr>`;
            clases.forEach(c=>{
                html += `<tr>
                    <td>${c.nombre}</td>
                    <td>${c.periodo}</td>
                    <td>${c.profesor}</td>
                </tr>`;
            });
            html += `</tbody></table>`;
            document.getElementById('tabla-clases').innerHTML = html;
            document.getElementById('contenido-clase').innerHTML = "";
        });
    }

    function cargarAvisos() {
        fetch('../crud/crudAlumno.php', {
            method:'POST',
            body: new URLSearchParams({accion:'avisos'})
        })
        .then(r=>r.json())
        .then(datos=>{
            let html = `<ul class="list-group">`;
            if(datos.length === 0) html += `<li class="list-group-item">No tienes avisos recientes.</li>`;
            datos.forEach(a=>{
                html += `<li class="list-group-item">
                    <strong>${a.titulo}</strong>: ${a.mensaje} <br>
                    <span class="text-muted small">${a.fecha}</span>
                </li>`;
            });
            html += `</ul>`;
            document.getElementById('avisos-alumno').innerHTML = html;
        });
    }

    function cargarTareas() {
        fetch('../crud/crudAlumno.php', {
            method:'POST',
            body: new URLSearchParams({accion:'tareas'})
        })
        .then(r=>r.json())
        .then(datos=>{
            let html = `<ul class="list-group">`;
            if(datos.length === 0) html += `<li class="list-group-item">No tienes tareas asignadas.</li>`;
            datos.forEach(t=>{
                html += `<li class="list-group-item">
                    <strong>${t.titulo}</strong>: ${t.descripcion} 
                    <br>Entrega: ${t.fecha_entrega}
                    ${t.archivo ? `<br><a href="../recursos/${t.archivo}" target="_blank"><i class="bi bi-file-earmark-arrow-down"></i> Descargar archivo</a>` : ''}
                </li>`;
            });
            html += `</ul>`;
            document.getElementById('tareas-alumno').innerHTML = html;
        });
    }

    function cargarCalificaciones() {
        fetch('../crud/crudAlumno.php', {
            method:'POST',
            body: new URLSearchParams({accion:'calificaciones'})
        })
        .then(r=>r.json())
        .then(datos=>{
            let html = `<table class="table table-bordered table-sm">
                <thead><tr><th>Clase</th><th>Calificación</th><th>Observación</th></tr></thead><tbody>`;
            if(datos.length===0) html += `<tr><td colspan="3">No tienes calificaciones registradas.</td></tr>`;
            datos.forEach(c=>{
                html += `<tr>
                    <td>${c.clase}</td>
                    <td>${c.calificacion!==null?c.calificacion:'-'}</td>
                    <td>${c.observacion||''}</td>
                </tr>`;
            });
            html += `</tbody></table>`;
            document.getElementById('calificaciones-alumno').innerHTML = html;
        });
    }

    function cargarReportes() {
        fetch('../crud/crudAlumno.php', {
            method:'POST',
            body: new URLSearchParams({accion:'reportes'})
        })
        .then(r=>r.json())
        .then(datos=>{
            let html = `<ul class="list-group">`;
            if(datos.length === 0) html += `<li class="list-group-item">No tienes reportes recientes.</li>`;
            datos.forEach(r=>{
                html += `<li class="list-group-item">
                    <strong>${r.titulo}</strong>: ${r.descripcion} <br>
                    <span class="text-muted small">${r.fecha}</span>
                </li>`;
            });
            html += `</ul>`;
            document.getElementById('reportes-alumno').innerHTML = html;
        });
    }

    function cargarPerfil() {
        fetch('../crud/crudUsuarios.php', {
            method:'POST',
            body: new URLSearchParams({accion:'perfil'})
        })
        .then(r=>r.json())
        .then(datos=>{
            let html = `<form id="formPerfil">
                <div class="mb-2">
                    <label class="form-label">Nombre</label>
                    <input type="text" class="form-control" name="nombre" value="${datos.nombre}">
                </div>
                <div class="mb-2">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="${datos.email}">
                </div>
                <button class="btn btn-primary" type="submit">Guardar</button>
            </form>`;
            document.getElementById('tabla-perfil').innerHTML = html;
            let f = document.getElementById('formPerfil');
            if(f) f.addEventListener('submit', function(ev){
                ev.preventDefault();
                let form = new FormData(this);
                form.append('accion','editar_perfil');
                fetch('../crud/crudUsuarios.php', {
                    method:'POST', body:form
                }).then(r=>r.json()).then(res=>{
                    if(res.ok) alert('Perfil actualizado');
                    else alert('No se pudo actualizar');
                });
            });
        });
    }

  
});