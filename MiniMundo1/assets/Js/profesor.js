document.addEventListener('DOMContentLoaded', function() {
    window.mostrarSeccion = function(seccion, ev) {
        document.querySelectorAll('.pantalla').forEach(div => div.classList.add('d-none'));
        document.getElementById(seccion).classList.remove('d-none');
        if (ev) ev.preventDefault();

        switch(seccion) {
            case 'misclases': cargarMisClases(); break;
            case 'avisos': cargarAvisos(); break;
            case 'reportes': cargarReportes(); break;
            case 'perfil': cargarPerfil(); break;
            case 'mensajes': cargarMensajes(); break;
        }
    };

    // CLASES
    function cargarMisClases() {
        fetch('../crud/crudProfesor.php', {
            method: 'POST',
            body: new URLSearchParams({accion: 'mis_clases'})
        })
        .then(r => r.json())
        .then(datos => {
            let html = `<table class="table table-sm"><thead>
                <tr><th>Clase</th><th>Periodo</th><th>Alumnos</th><th>Tareas</th><th>Calificaciones</th></tr></thead><tbody>`;
            if(datos.length === 0) {
                html += `<tr><td colspan="5">No tienes clases asignadas</td></tr>`;
            }
            datos.forEach(clase => {
                html += `<tr>
                    <td>${clase.nombre}</td>
                    <td>${clase.periodo}</td>
                    <td><button class="btn btn-info btn-sm" onclick="verAlumnos(${clase.id},'${clase.nombre}')">Ver</button></td>
                    <td><button class="btn btn-success btn-sm" onclick="verTareas(${clase.id},'${clase.nombre}')">Ver</button></td>
                    <td><button class="btn btn-warning btn-sm" onclick="verCalificaciones(${clase.id},'${clase.nombre}')">Ver</button></td>
                </tr>`;
            });
            html += `</tbody></table>
                <div id="contenido-extra"></div>
            `;
            document.getElementById('tabla-misclases').innerHTML = html;
            document.getElementById('contenido-clase').innerHTML = "";
        });
    }
    window.verAlumnos = function(clase_id, nombre) {
        fetch('../crud/crudProfesor.php', {
            method: 'POST',
            body: new URLSearchParams({accion: 'alumnos_clase', clase_id})
        })
        .then(r => r.json())
        .then(datos => {
            let html = `<h5>Alumnos de "${nombre}":</h5><ul class="list-group mb-3">`;
            if(datos.length===0) html += `<li class="list-group-item">No hay alumnos inscritos.</li>`;
            datos.forEach(a => {
                html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>${a.nombre}</span>
                    <span class="badge bg-primary">${a.email}</span>
                </li>`;
            });
            html += `</ul>`;
            document.getElementById('contenido-clase').innerHTML = html;
        });
    };

    // TAREAS
    window.verTareas = function(clase_id, nombre) {
        fetch('../crud/crudProfesor.php', {
            method: 'POST',
            body: new URLSearchParams({accion: 'listar_tareas', clase_id})
        })
        .then(r => r.json())
        .then(tareas => {
            let html = `<h5>Tareas de "${nombre}":</h5>
            <form id="formTarea" class="mb-3">
                <div class="row g-2">
                    <div class="col"><input type="text" name="titulo" class="form-control" placeholder="Título" required></div>
                    <div class="col"><input type="text" name="descripcion" class="form-control" placeholder="Descripción" required></div>
                    <div class="col"><input type="date" name="fecha_entrega" class="form-control" required></div>
                    <input type="hidden" name="clase_id" value="${clase_id}">
                    <div class="col-auto"><button type="submit" class="btn btn-success">Agregar tarea</button></div>
                </div>
            </form>`;
            html += `<ul class="list-group">`;
            if(tareas.length===0) html += `<li class="list-group-item">No hay tareas registradas.</li>`;
            tareas.forEach(t => {
                html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>
                        <strong>${t.titulo}</strong> - ${t.descripcion} 
                        (Entrega: ${t.fecha_entrega}) 
                        ${t.archivo ? `<a href="../recursos/${t.archivo}" target="_blank" class="ms-2"><i class="bi bi-file-earmark-arrow-down"></i></a>` : ''}
                    </span>
                    <button class="btn btn-danger btn-sm" onclick="borrarTarea(${t.id},${clase_id},'${nombre}')"><i class="bi bi-trash"></i></button>
                </li>`;
            });
            html += `</ul>`;
            document.getElementById('contenido-clase').innerHTML = html;

            // Form submit
            let f = document.getElementById('formTarea');
            if(f) f.addEventListener('submit', function(ev){
                ev.preventDefault();
                let form = new FormData(this);
                form.append('accion','crear_tarea');
                fetch('../crud/crudProfesor.php', {method:'POST', body:form})
                .then(r=>r.json()).then(res=>{
                    if(res.ok) verTareas(clase_id, nombre);
                    else alert('No se pudo agregar');
                });
            });
        });
    };
    window.borrarTarea = function(tarea_id, clase_id, nombre) {
        if(confirm('¿Borrar esta tarea?')) {
            fetch('../crud/crudProfesor.php', {
                method:'POST',
                body: new URLSearchParams({accion:'borrar_tarea', tarea_id})
            }).then(r=>r.json()).then(res=>{
                if(res.ok) verTareas(clase_id, nombre);
                else alert('No se pudo borrar');
            });
        }
    };

    // CALIFICACIONES
    window.verCalificaciones = function(clase_id, nombre) {
        fetch('../crud/crudProfesor.php', {
            method:'POST',
            body: new URLSearchParams({accion:'get_calificaciones', clase_id})
        })
        .then(r=>r.json())
        .then(alumnos=>{
            let html = `<h5>Calificaciones de "${nombre}":</h5>
            <table class="table table-bordered table-sm">
                <thead><tr><th>Alumno</th><th>Calificación</th><th>Observación</th><th>Acción</th></tr></thead><tbody>`;
            if(alumnos.length===0) html += `<tr><td colspan="4">No hay alumnos con inscripción aprobada en esta clase.</td></tr>`;
            alumnos.forEach(a=>{
                html += `<tr>
                    <td>${a.alumno}</td>
                    <td>
                        <form class="d-inline formCalif" data-alumno="${a.alumno_id}" data-clase="${clase_id}">
                            <input type="number" step="0.1" min="0" max="10" name="calificacion" value="${a.calificacion||''}" class="form-control form-control-sm" style="width:90px;display:inline;">
                    </td>
                    <td>
                            <input type="text" name="observacion" value="${a.observacion||''}" class="form-control form-control-sm">
                    </td>
                    <td>
                            <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-save"></i></button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="borrarCalif(${a.alumno_id},${clase_id})"><i class="bi bi-x"></i></button>
                        </form>
                    </td>
                </tr>`;
            });
            html += `</tbody></table>`;
            document.getElementById('contenido-clase').innerHTML = html;
            document.querySelectorAll('.formCalif').forEach(form=>{
                form.addEventListener('submit',function(ev){
                    ev.preventDefault();
                    let alumno_id = this.dataset.alumno, clase_id = this.dataset.clase;
                    let calif = this.calificacion.value, obs = this.observacion.value;
                    let fd = new FormData();
                    fd.append('accion','set_calificacion');
                    fd.append('alumno_id', alumno_id);
                    fd.append('clase_id', clase_id);
                    fd.append('calificacion', calif);
                    fd.append('observacion', obs);
                    fetch('../crud/crudProfesor.php', {method:'POST',body:fd})
                    .then(r=>r.json()).then(res=>{
                        if(res.ok) verCalificaciones(clase_id, nombre);
                        else alert('No se pudo guardar');
                    });
                });
            });
        });
    };
    window.borrarCalif = function(alumno_id, clase_id) {
        if(confirm('¿Borrar calificación?')) {
            fetch('../crud/crudProfesor.php', {
                method:'POST',
                body: new URLSearchParams({accion:'borrar_calificacion', alumno_id, clase_id})
            }).then(r=>r.json()).then(res=>{
                if(res.ok) document.querySelector('button[type="submit"]').click(); // refrescar
                else alert('No se pudo borrar');
            });
        }
    };

    // AVISOS
    function cargarAvisos() {
        fetch('../crud/crudProfesor.php', {
            method:'POST',
            body: new URLSearchParams({accion:'mis_clases'})
        })
        .then(r=>r.json())
        .then(clases=>{
            let html = `<h5>Enviar aviso:</h5>
                <form id="formAviso" class="mb-3">
                <div class="row g-2">
                    <div class="col">
                        <select name="clase_id" class="form-control" required>
                            <option value="">Seleccione clase</option>
                            ${clases.map(c=>`<option value="${c.id}">${c.nombre}</option>`).join('')}
                        </select>
                    </div>
                    <div class="col"><input type="text" name="titulo" class="form-control" placeholder="Título" required></div>
                    <div class="col"><input type="text" name="mensaje" class="form-control" placeholder="Mensaje" required></div>
                    <div class="col-auto"><button type="submit" class="btn btn-success">Enviar</button></div>
                </div></form>
                <div id="avisos-lista"></div>
            `;
            document.getElementById('avisos-profesor').innerHTML = html;

            // Form submit
            let f = document.getElementById('formAviso');
            if(f) f.addEventListener('submit', function(ev){
                ev.preventDefault();
                let fd = new FormData(this);
                fd.append('accion','mandar_aviso');
                fetch('../crud/crudProfesor.php', {method:'POST', body:fd})
                .then(r=>r.json()).then(res=>{
                    if(res.ok) listarAvisos();
                    else alert('No se pudo enviar');
                });
            });
            listarAvisos();
        });
    }
    function listarAvisos() {
        let clase_id = document.querySelector('select[name="clase_id"]')?.value || '';
        if(!clase_id) return;
        fetch('../crud/crudProfesor.php', {
            method:'POST',
            body: new URLSearchParams({accion:'listar_avisos', clase_id})
        })
        .then(r=>r.json())
        .then(datos=>{
            let html = `<ul class="list-group">`;
            if(datos.length === 0) html += `<li class="list-group-item">No hay avisos para esta clase.</li>`;
            datos.forEach(a=>{
                html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>
                        <strong>${a.titulo}</strong>: ${a.mensaje} <br>
                        <span class="text-muted small">${a.fecha}</span>
                    </span>
                    <button class="btn btn-danger btn-sm" onclick="borrarAviso(${a.id})"><i class="bi bi-trash"></i></button>
                </li>`;
            });
            html += `</ul>`;
            document.getElementById('avisos-lista').innerHTML = html;
        });
    }
    window.borrarAviso = function(aviso_id) {
        if(confirm('¿Borrar este aviso?')) {
            fetch('../crud/crudProfesor.php', {
                method:'POST',
                body: new URLSearchParams({accion:'borrar_aviso', aviso_id})
            }).then(r=>r.json()).then(res=>{
                if(res.ok) listarAvisos();
                else alert('No se pudo borrar');
            });
        }
    };

    // REPORTES
    function cargarReportes() {
        fetch('../crud/crudProfesor.php', {
            method:'POST',
            body: new URLSearchParams({accion:'mis_clases'})
        })
        .then(r=>r.json())
        .then(clases=>{
            let html = `<h5>Crear reporte:</h5>
                <form id="formReporte" class="mb-3">
                <div class="row g-2">
                    <div class="col">
                        <select name="clase_id" class="form-control" required>
                            <option value="">Seleccione clase</option>
                            ${clases.map(c=>`<option value="${c.id}">${c.nombre}</option>`).join('')}
                        </select>
                    </div>
                    <div class="col">
                        <input type="text" name="titulo" class="form-control" placeholder="Título" required>
                    </div>
                    <div class="col">
                        <input type="text" name="descripcion" class="form-control" placeholder="Descripción" required>
                    </div>
                    <div class="col">
                        <select name="alumno_id" class="form-control" required id="alumnosReporte">
                            <option value="">Seleccione alumno</option>
                        </select>
                    </div>
                    <div class="col-auto"><button type="submit" class="btn btn-success">Crear</button></div>
                </div></form>
                <div id="reportes-lista"></div>
            `;
            document.getElementById('reportes-profesor').innerHTML = html;

            // Cargar alumnos al cambiar clase
            let claseSel = document.querySelector('select[name="clase_id"]');
            if(claseSel) {
                claseSel.addEventListener('change', function() {
                    fetch('../crud/crudProfesor.php', {
                        method:'POST',
                        body: new URLSearchParams({accion:'alumnos_clase', clase_id: this.value})
                    })
                    .then(r=>r.json())
                    .then(alumnos=>{
                        let opts = `<option value="">Seleccione alumno</option>`;
                        alumnos.forEach(a=>{ opts+=`<option value="${a.id}">${a.nombre}</option>`; });
                        document.getElementById('alumnosReporte').innerHTML = opts;
                        listarReportes();
                    });
                });
            }

            // Form submit
            let f = document.getElementById('formReporte');
            if(f) f.addEventListener('submit', function(ev){
                ev.preventDefault();
                let fd = new FormData(this);
                fd.append('accion','crear_reporte');
                fetch('../crud/crudProfesor.php', {method:'POST', body:fd})
                .then(r=>r.json()).then(res=>{
                    if(res.ok) listarReportes();
                    else alert('No se pudo crear');
                });
            });

            listarReportes();
        });
    }
    function listarReportes() {
        let clase_id = document.querySelector('select[name="clase_id"]')?.value || '';
        if(!clase_id) return;
        fetch('../crud/crudProfesor.php', {
            method:'POST',
            body: new URLSearchParams({accion:'listar_reportes', clase_id})
        })
        .then(r=>r.json())
        .then(datos=>{
            let html = `<ul class="list-group">`;
            if(datos.length === 0) html += `<li class="list-group-item">No hay reportes para esta clase.</li>`;
            datos.forEach(a=>{
                html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>
                        <strong>${a.titulo}</strong>: ${a.descripcion} <br>
                        Alumno: <span class="fw-semibold">${a.alumno}</span> <br>
                        <span class="text-muted small">${a.fecha}</span>
                    </span>
                    <button class="btn btn-danger btn-sm" onclick="borrarReporte(${a.id})"><i class="bi bi-trash"></i></button>
                </li>`;
            });
            html += `</ul>`;
            document.getElementById('reportes-lista').innerHTML = html;
        });
    }
    window.borrarReporte = function(reporte_id) {
        if(confirm('¿Borrar este reporte?')) {
            fetch('../crud/crudProfesor.php', {
                method:'POST',
                body: new URLSearchParams({accion:'borrar_reporte', reporte_id})
            }).then(r=>r.json()).then(res=>{
                if(res.ok) listarReportes();
                else alert('No se pudo borrar');
            });
        }
    };

    // MENSAJES PROFESOR-PADRE
    function cargarMensajes() {
        fetch('../crud/crudProfesor.php', {
            method:'POST',
            body: new URLSearchParams({accion:'padres_mis_alumnos'})
        })
        .then(r=>r.json())
        .then(padres=>{
            let html = `<h5>Selecciona un padre para conversar:</h5>
                <select id="padreSelect" class="form-select mb-3">
                    <option value="">Seleccione padre</option>
                    ${padres.map(p=>`<option value="${p.id}">${p.nombre}</option>`).join('')}
                </select>
                <div id="mensajes-chat"></div>`;
            document.getElementById('mensajes-profesor').innerHTML = html;

            document.getElementById('padreSelect').addEventListener('change', function(){
                let padre_id = this.value;
                if(!padre_id) { document.getElementById('mensajes-chat').innerHTML = ''; return; }
                cargarChat(padre_id);
            });
        });
    }

    function cargarChat(padre_id) {
        fetch('../crud/crudProfesor.php', {
            method:'POST',
            body: new URLSearchParams({accion:'listar_mensajes', padre_id})
        })
        .then(r=>r.json())
        .then(msgs=>{
            let html = `<div class="mb-2" style="max-height:300px;overflow:auto;"><ul class="list-group">`;
            if(msgs.length===0) html+=`<li class="list-group-item">No hay mensajes aún.</li>`;
            msgs.forEach(m=>{
                html += `<li class="list-group-item${m.de_quien=='profesor'?' list-group-item-success':''}">
                    <strong>${m.de_quien=='profesor'?'Tú':'Padre'}:</strong> ${m.mensaje} <br>
                    <small class="text-muted">${m.fecha}</small>
                </li>`;
            });
            html += `</ul></div>
                <form id="formEnviarMensaje" class="input-group">
                    <input type="hidden" name="padre_id" value="${padre_id}">
                    <input type="text" name="mensaje" class="form-control" placeholder="Escribe un mensaje..." required>
                    <button class="btn btn-primary" type="submit">Enviar</button>
                </form>`;
            document.getElementById('mensajes-chat').innerHTML = html;
            document.getElementById('formEnviarMensaje').addEventListener('submit', function(ev){
                ev.preventDefault();
                let fd = new FormData(this);
                fd.append('accion','enviar_mensaje_padre');
                fetch('../crud/crudProfesor.php', {method:'POST', body:fd})
                .then(r=>r.json()).then(res=>{
                    if(res.ok) cargarChat(padre_id);
                    else alert('No se pudo enviar');
                });
            });
        });
    }

    // PERFIL
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



