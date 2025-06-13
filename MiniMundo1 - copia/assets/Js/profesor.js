// panelProfesor.js - Profesor dashboard main JS
// Depende de utils.js

import { mostrarSeccion, fetchCrud, renderTable, cargarPerfil, flash } from './utils.js';

document.addEventListener('DOMContentLoaded', function() {
    // Indica el rol actual para utils.js
    window.usuario_rol = "profesor";

    // Secciones y callbacks
    const seccionesProfesor = {
        misclases: cargarMisClases,
        avisos: cargarAvisos,
        reportes: cargarReportes,
        perfil: () => cargarPerfil('tabla-perfil'),
        mensajes: cargarMensajes,
    };

    // Muestra sección y ejecuta callback (usa utils)
    window.mostrarSeccion = (seccion, ev) => mostrarSeccion(seccion, seccionesProfesor, ev);

    // CLASES
    function cargarMisClases() {
        fetchCrud('../crud/crudProfesor.php', {accion: 'mis_clases'})
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
        fetchCrud('../crud/crudProfesor.php', {accion: 'alumnos_clase', clase_id})
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
        fetchCrud('../crud/crudProfesor.php', {accion: 'listar_tareas', clase_id})
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
                    else flash('No se pudo agregar', 'danger');
                });
            });
        });
    };

    window.borrarTarea = function(tarea_id, clase_id, nombre) {
        if(confirm('¿Borrar esta tarea?')) {
            fetchCrud('../crud/crudProfesor.php', {accion:'borrar_tarea', tarea_id})
            .then(res=>{
                if(res.ok) verTareas(clase_id, nombre);
                else flash('No se pudo borrar', 'danger');
            });
        }
    };
// CALIFICACIONES
window.verCalificaciones = function(clase_id, nombre) {
    fetchCrud('../crud/crudProfesor.php', {accion:'get_calificaciones', clase_id})
    .then(alumnos=>{
        let html = `<h5>Calificaciones de "${nombre}":</h5>
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>Alumno</th>
                    <th>Calificación</th>
                    <th>Observación</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>`;
        if(alumnos.length===0) {
            html += `<tr><td colspan="4">No hay alumnos con inscripción aprobada en esta clase.</td></tr>`;
        }
        alumnos.forEach(a=>{
            html += `<tr>
                <td>${a.alumno}</td>
                <td>
                    <input type="number" step="0.1" min="0" max="10" name="calificacion" value="${a.calificacion||''}" class="form-control form-control-sm" style="width:90px;display:inline-block">
                </td>
                <td>
                    <input type="text" name="observacion" value="${a.observacion||''}" class="form-control form-control-sm">
                </td>
                <td>
                    <form class="d-inline formCalif" data-alumno="${a.alumno_id}" data-clase="${clase_id}" onsubmit="return false;">
                        <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-save"></i></button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="borrarCalif(${a.alumno_id},${clase_id},'${nombre.replace(/'/g, "\\'")}')"><i class="bi bi-x"></i></button>
                        <input type="hidden" name="calificacion_hidden" value="${a.calificacion||''}">
                        <input type="hidden" name="observacion_hidden" value="${a.observacion||''}">
                    </form>
                </td>
            </tr>`;
        });
        html += `</tbody></table>`;
        document.getElementById('contenido-clase').innerHTML = html;

        // Asocia el evento submit DESPUÉS de renderizar el HTML
        document.querySelectorAll('.formCalif').forEach(form=>{
            form.addEventListener('submit',function(ev){
                ev.preventDefault();
                // Para asegurar que tomamos el input de la misma fila:
                let tr = this.closest('tr');
                let calif = tr.querySelector('input[name="calificacion"]').value;
                let obs = tr.querySelector('input[name="observacion"]').value;
                let alumno_id = this.dataset.alumno, clase_id = this.dataset.clase;
                let fd = new FormData();
                fd.append('accion','set_calificacion');
                fd.append('alumno_id', alumno_id);
                fd.append('clase_id', clase_id);
                fd.append('calificacion', calif);
                fd.append('observacion', obs);
                fetch('../crud/crudProfesor.php', {method:'POST',body:fd})
                .then(r => r.text())
                .then(txt => {
                    // Mostrar la respuesta cruda para depuración
                    console.log('Respuesta cruda:', txt);
                    let res;
                    try { res = JSON.parse(txt); }
                    catch(e) { flash('La respuesta no es JSON válido: ' + txt, 'danger'); return; }
                    if(res.ok) {
                        flash('Calificación guardada', 'success');
                        verCalificaciones(clase_id, nombre);
                    }
                    else flash('No se pudo guardar: ' + (res.msg || 'Error desconocido'), 'danger');
                })
                .catch(err => {
                    flash('Error en la petición: ' + err, 'danger');
                    console.log('Error fetch:', err);
                });
            });
        });
    });
};

// Mejorado: recibe también el nombre para refrescar correctamente el panel
window.borrarCalif = function(alumno_id, clase_id, nombre) {
    if(confirm('¿Borrar calificación?')) {
        fetchCrud('../crud/crudProfesor.php', {accion:'borrar_calificacion', alumno_id, clase_id})
        .then(res=>{
            if(res.ok) flash('Calificación eliminada', 'success');
            else flash('No se pudo borrar', 'danger');
            // Refresca la tabla directamente
            verCalificaciones(clase_id, nombre);
        });
    }
};
    // AVISOS
    function cargarAvisos() {
        fetchCrud('../crud/crudProfesor.php', {accion:'mis_clases'})
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

            let claseSelect = document.querySelector('select[name="clase_id"]');
            if (claseSelect && claseSelect.options.length > 1) {
                // Selecciona la primera clase si hay alguna
                claseSelect.selectedIndex = 1;
            }

            // Form submit
            let f = document.getElementById('formAviso');
            if (f) f.addEventListener('submit', function(ev){
                ev.preventDefault();
                let fd = new FormData(this);
                fd.append('accion','mandar_aviso');
                fetchCrud('../crud/crudProfesor.php', Object.fromEntries(fd.entries()))
                .then(res=>{
                    if (res.ok) listarAvisos();
                    else flash('No se pudo enviar', 'danger');
                });
            });

            // Llama a listarAvisos() para la clase seleccionada (si hay)
            listarAvisos();

            if (claseSelect) {
                claseSelect.addEventListener('change', listarAvisos);
            }
        });
    }
    function listarAvisos() {
        let clase_id = document.querySelector('select[name="clase_id"]')?.value || '';
        if (!clase_id) {
            document.getElementById('avisos-lista').innerHTML = '<div class="alert alert-info">Selecciona una clase para ver los avisos.</div>';
            return;
        }
        fetchCrud('../crud/crudProfesor.php', {accion:'listar_avisos', clase_id})
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
            fetchCrud('../crud/crudProfesor.php', {accion:'borrar_aviso', aviso_id})
            .then(res=>{
                if(res.ok) listarAvisos();
                else flash('No se pudo borrar', 'danger');
            });
        }
    };

    // REPORTES
    function cargarReportes() {
        fetchCrud('../crud/crudProfesor.php', {accion:'mis_clases'})
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

            // Cargar alumnos utilitaria
            function cargarAlumnosDeClase(clase_id) {
                if (!clase_id) {
                    document.getElementById('alumnosReporte').innerHTML = `<option value="">Seleccione alumno</option>`;
                    return;
                }
                fetchCrud('../crud/crudProfesor.php', {accion:'alumnos_clase', clase_id})
                .then(alumnos=>{
                    let opts = `<option value="">Seleccione alumno</option>`;
                    alumnos.forEach(a=>{ opts+=`<option value="${a.id}">${a.nombre}</option>`; });
                    document.getElementById('alumnosReporte').innerHTML = opts;
                });
            }

            // Selección y carga automática de los alumnos al cargar la sección
            let claseSel = document.querySelector('select[name="clase_id"]');
            if(claseSel) {
                if(claseSel.options.length > 1) {
                    claseSel.selectedIndex = 1; // Selecciona la primera clase real
                    cargarAlumnosDeClase(claseSel.value);
                } else {
                    cargarAlumnosDeClase('');
                }
                claseSel.addEventListener('change', function() {
                    cargarAlumnosDeClase(this.value);
                    listarReportes();
                });
            }

            // Form submit (debe ir después de poner el HTML en el DOM)
            let f = document.getElementById('formReporte');
            if(f) f.addEventListener('submit', function(ev){
                ev.preventDefault();
                let fd = new FormData(this);
                fd.append('accion','crear_reporte');
                console.log("Submit disparado", Object.fromEntries(fd.entries()));
                fetchCrud('../crud/crudProfesor.php', Object.fromEntries(fd.entries()))
                .then(res=>{
                    if(res.ok) listarReportes();
                    else flash('No se pudo crear', 'danger');
                });
            });

            listarReportes();
        });
    }
    function listarReportes() {
        let clase_id = document.querySelector('select[name="clase_id"]')?.value || '';
        if(!clase_id) return;
        fetchCrud('../crud/crudProfesor.php', {accion:'listar_reportes', clase_id})
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
            fetchCrud('../crud/crudProfesor.php', {accion:'borrar_reporte', reporte_id})
            .then(res=>{
                if(res.ok) listarReportes();
                else flash('No se pudo borrar', 'danger');
            });
        }
    };

    // MENSAJES PROFESOR-PADRE
    function cargarMensajes() {
        fetchCrud('../crud/crudProfesor.php', {accion:'padres_mis_alumnos'})
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
        fetchCrud('../crud/crudProfesor.php', {accion:'listar_mensajes', padre_id})
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
                fetchCrud('../crud/crudProfesor.php', Object.fromEntries(fd.entries()))
                .then(res=>{
                    if(res.ok) cargarChat(padre_id);
                    else flash('No se pudo enviar', 'danger');
                });
            });
        });
    }

    // PERFIL - Usa la función centralizada de utils.js (ver secciónes arriba)
    // window.usuario_rol = "profesor" ya está arriba, así que cargarPerfil() usará el CRUD correcto

    // Inicializa la primera sección visible si aplica
    // mostrarSeccion('misclases');

});