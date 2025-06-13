function mostrarSeccion(id, event) {
    if(event) event.preventDefault();
    document.querySelectorAll('.pantalla').forEach(el => el.classList.add('d-none'));
    document.getElementById(id).classList.remove('d-none');
    document.querySelectorAll('.sidebar .nav-link').forEach(link => link.classList.remove('active'));
    let link = Array.from(document.querySelectorAll('.sidebar .nav-link')).find(l => l.textContent.trim().toLowerCase().includes(id));
    if(link) link.classList.add('active');
    if(id === 'usuarios') cargarUsuarios();
    if(id === 'clases') cargarClases();
    if(id === 'coordinadores') cargarCoordinadores();
    if(id === 'directores') cargarDirectores();
    if(id === 'reportes') cargarReportes();
    if(id === 'aprobaciones') cargarAprobaciones();
}

// Utilidad para modales
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
    // Cerrar modal al click fuera del contenedor
    modalBg.addEventListener("click", function(e) {
        if(e.target === modalBg) cerrarModal();
    });
    return modalBg;
}
function cerrarModal() {
    let m = document.getElementById("modal-js");
    if(m) m.remove();
}

// USUARIOS CRUD con modal y select de roles
function cargarUsuarios() {
    const cont = document.getElementById('tabla-usuarios');
    fetch('../crud/crudUsuarios.php', {
        method: 'POST',
        body: new URLSearchParams({accion: 'listar'})
    })
    .then(r=>r.json())
    .then(data=>{
        let html = `<button class="btn btn-dark btn-sm mb-2" id="btnNuevoUsuario">Nuevo Usuario</button>
        <table class="table table-bordered align-middle"><thead>
        <tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Estado</th><th>Acciones</th></thead><tbody>`;
        data.forEach(u=>{
            let acciones = `
                <button class="btn btn-sm btn-outline-secondary btn-editar-usuario" data-id="${u.id}" title="Editar"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger" onclick="bajaUsuario(${u.id})" title="Dar de baja"><i class="bi bi-person-x"></i></button>
            `;
            html += `<tr>
                <td>${u.nombre}</td>
                <td>${u.email}</td>
                <td>${u.rol}</td>
                <td><span class="badge ${u.activo==1?'bg-success':'bg-danger'}">${u.activo==1?'Activo':'Inactivo'}</span></td>
                <td>${acciones}</td>
            </tr>`;
        });
        html += `</tbody></table>`;
        cont.innerHTML = html;
        // Enlazar botón nuevo usuario
        document.getElementById('btnNuevoUsuario').onclick = function() {
            formUsuario();
        };
        // Enlazar todos los botones de editar usuario (delegación)
        document.querySelectorAll('.btn-editar-usuario').forEach(btn=>{
            btn.onclick = function() {
                formUsuario(this.getAttribute('data-id'));
            }
        });
    });
}

function formUsuario(id) {
    fetch('../crud/crudUsuarios.php', { method: 'POST', body: new URLSearchParams({accion: 'roles'}) })
    .then(r => r.json())
    .then(rolesArr => {
        if (!id) {
            // NUEVO USUARIO
            let html = `<form id="formNuevoUsuario" class="p-4" style="min-width:320px;max-width:400px;">
                <h5 class="mb-3">Registrar nuevo usuario</h5>
                <div class="mb-2">
                    <label>Nombre:</label>
                    <input type="text" class="form-control" name="nombre" required>
                </div>
                <div class="mb-2">
                    <label>Email:</label>
                    <input type="email" class="form-control" name="email" required>
                </div>
                <div class="mb-2">
                    <label>Contraseña:</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <div class="mb-2">
                    <label>Rol:</label>
                    <select name="rol_id" class="form-select" required>
                        <option value="">Seleccione un rol</option>
                        ${rolesArr.map(r=>`<option value="${r.id}">${r.nombre}</option>`).join("")}
                    </select>
                </div>
                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary">Registrar</button>
                    <button type="button" class="btn btn-secondary ms-2" id="cancelarFormUsuario">Cancelar</button>
                </div>
            </form>`;
            let modal = crearModal(html);
            document.getElementById("cancelarFormUsuario").onclick = cerrarModal;
            document.getElementById("formNuevoUsuario").onsubmit = function(e){
                e.preventDefault();
                let form = new FormData(this);
                form.append('accion','crear');
                fetch('../crud/crudUsuarios.php',{method:'POST',body:form})
                .then(r=>r.json()).then(res=>{
                    if(res.ok){
                        cerrarModal();
                        cargarUsuarios();
                        alert('Usuario registrado correctamente.');
                    }else{
                        alert(res.msg||'No se pudo registrar');
                    }
                });
            }
        } else {
            // EDITAR USUARIO
            fetch('../crud/crudUsuarios.php', { method: 'POST', body: new URLSearchParams({accion:'listar'}) })
            .then(r=>r.json()).then(users=>{
                let u = users.find(x=>x.id==id);
                if(!u) return alert("Usuario no encontrado.");
                let html = `<form id="formEditUsuario" class="p-4" style="min-width:320px;max-width:400px;">
                    <h5 class="mb-3">Editar usuario</h5>
                    <input type="hidden" name="id" value="${u.id}">
                    <div class="mb-2">
                        <label>Nombre:</label>
                        <input type="text" class="form-control" name="nombre" required value="${u.nombre}">
                    </div>
                    <div class="mb-2">
                        <label>Email:</label>
                        <input type="email" class="form-control" name="email" required value="${u.email}">
                    </div>
                    <div class="mb-2">
                        <label>Rol:</label>
                        <select name="rol_id" class="form-select" required>
                            <option value="">Seleccione un rol</option>
                            ${rolesArr.map(r=>`<option value="${r.id}" ${u.rol_id==r.id?'selected':''}>${r.nombre}</option>`).join("")}
                        </select>
                    </div>
                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <button type="button" class="btn btn-secondary ms-2" id="cancelarEditUsuario">Cancelar</button>
                    </div>
                </form>`;
                let modal = crearModal(html);
                document.getElementById("cancelarEditUsuario").onclick = cerrarModal;
                document.getElementById("formEditUsuario").onsubmit = function(e){
                    e.preventDefault();
                    let form = new FormData(this);
                    form.append('accion','editar');
                    fetch('../crud/crudUsuarios.php',{method:'POST',body:form})
                    .then(r=>r.json()).then(res=>{
                        if(res.ok){
                            cerrarModal();
                            cargarUsuarios();
                            alert('Usuario actualizado correctamente.');
                        }else{
                            alert(res.msg||'No se pudo actualizar');
                        }
                    });
                }
            });
        }
    });
}

function bajaUsuario(id){
    if(confirm("¿Seguro que deseas dar de baja este usuario?"))
    fetch('../crud/crudUsuarios.php',{
        method:'POST',
        body: new URLSearchParams({accion:'baja',id})
    }).then(()=>cargarUsuarios());
}

// CLASES con modal y select de profesor
function cargarClases() {
    const cont = document.getElementById('tabla-clases');
    fetch('../crud/crudClases.php', {
        method: 'POST',
        body: new URLSearchParams({accion: 'listar'})
    })
    .then(r=>r.json()).then(data=>{
        let html = `<button class="btn btn-dark btn-sm mb-2" id="btnNuevaClase">Nueva Clase</button>
        <table class="table table-striped align-middle"><thead>
        <tr><th>Nombre</th><th>Profesor</th><th>Periodo</th><th>Acciones</th></thead><tbody>`;
        data.forEach(c=>{
            html += `<tr>
                <td>${c.nombre}</td>
                <td>${c.profesor}</td>
                <td>${c.periodo}</td>
                <td>
                    <button class="btn btn-sm btn-outline-secondary btn-editar-clase" data-id="${c.id}" title="Editar"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-outline-danger" onclick="eliminarClase(${c.id})" title="Eliminar"><i class="bi bi-trash"></i></button>
                </td>
            </tr>`;
        });
        html += `</tbody></table>`;
        cont.innerHTML = html;
        // Botón nueva clase
        document.getElementById('btnNuevaClase').onclick = function() {
            formClase();
        };
        // Botones editar clase
        document.querySelectorAll('.btn-editar-clase').forEach(btn=>{
            btn.onclick = function() {
                formClase(this.getAttribute('data-id'));
            }
        });
    });
}
function formClase(id){
    // Obtener profesores para el select
    fetch('../crud/crudUsuarios.php', { method: 'POST', body: new URLSearchParams({accion: 'listar'}) })
    .then(r=>r.json())
    .then(usuarios=>{
        let profesores = usuarios.filter(u=>u.rol_id==2);
        if (!id) {
            // NUEVA CLASE
            let html = `<form id="formNuevaClase" class="p-4" style="min-width:320px;max-width:400px;">
                <h5 class="mb-3">Registrar nueva clase</h5>
                <div class="mb-2">
                    <label>Nombre:</label>
                    <input type="text" class="form-control" name="nombre" required>
                </div>
                <div class="mb-2">
                    <label>Profesor asignado:</label>
                    <select name="profesor_id" class="form-select" required>
                        <option value="">Seleccione un profesor</option>
                        ${profesores.map(p=>`<option value="${p.id}">${p.nombre}</option>`).join("")}
                    </select>
                </div>
                <div class="mb-2">
                    <label>Periodo:</label>
                    <input type="text" class="form-control" name="periodo" required>
                </div>
                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary">Registrar</button>
                    <button type="button" class="btn btn-secondary ms-2" id="cancelarFormClase">Cancelar</button>
                </div>
            </form>`;
            let modal = crearModal(html);
            document.getElementById("cancelarFormClase").onclick = cerrarModal;
            document.getElementById("formNuevaClase").onsubmit = function(e){
                e.preventDefault();
                let form = new FormData(this);
                form.append('accion','crear');
                fetch('../crud/crudClases.php',{method:'POST',body:form})
                .then(r=>r.json()).then(res=>{
                    if(res.ok){
                        cerrarModal();
                        cargarClases();
                        alert('Clase registrada correctamente.');
                    }else{
                        alert(res.msg||'No se pudo registrar');
                    }
                });
            }
        } else {
            // EDITAR CLASE
            fetch('../crud/crudClases.php', {method:'POST', body:new URLSearchParams({accion:'listar'})})
            .then(r=>r.json()).then(clases=>{
                let c = clases.find(x=>x.id==id);
                if(!c) return;
                let html = `<form id="formEditClase" class="p-4" style="min-width:320px;max-width:400px;">
                    <h5 class="mb-3">Editar clase</h5>
                    <input type="hidden" name="id" value="${c.id}">
                    <div class="mb-2">
                        <label>Nombre:</label>
                        <input type="text" class="form-control" name="nombre" required value="${c.nombre}">
                    </div>
                    <div class="mb-2">
                        <label>Profesor asignado:</label>
                        <select name="profesor_id" class="form-select" required>
                            <option value="">Seleccione un profesor</option>
                            ${profesores.map(p=>`<option value="${p.id}" ${c.profesor_id==p.id?'selected':''}>${p.nombre}</option>`).join("")}
                        </select>
                    </div>
                    <div class="mb-2">
                        <label>Periodo:</label>
                        <input type="text" class="form-control" name="periodo" required value="${c.periodo}">
                    </div>
                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <button type="button" class="btn btn-secondary ms-2" id="cancelarEditClase">Cancelar</button>
                    </div>
                </form>`;
                let modal = crearModal(html);
                document.getElementById("cancelarEditClase").onclick = cerrarModal;
                document.getElementById("formEditClase").onsubmit = function(e){
                    e.preventDefault();
                    let form = new FormData(this);
                    form.append('accion','editar');
                    fetch('../crud/crudClases.php',{method:'POST',body:form})
                    .then(r=>r.json()).then(res=>{
                        if(res.ok){
                            cerrarModal();
                            cargarClases();
                            alert('Clase actualizada correctamente.');
                        }else{
                            alert(res.msg||'No se pudo actualizar');
                        }
                    });
                }
            });
        }
    });
}
function eliminarClase(id){
    if(confirm("¿Seguro que deseas eliminar esta clase?"))
    fetch('../crud/crudClases.php',{
        method:'POST',
        body: new URLSearchParams({accion:'eliminar',id})
    }).then(()=>cargarClases());
}

// COORDINADORES
function cargarCoordinadores() {
    const cont = document.getElementById('tabla-coordinadores');
    fetch('../crud/crudUsuarios.php', {
        method: 'POST',
        body: new URLSearchParams({accion: 'listar'})
    })
    .then(r=>r.json()).then(data=>{
        let output = data.filter(u=>u.rol_id==3);
        if (output.length === 0) {
            cont.innerHTML = `<div class="alert alert-info">No hay coordinadores registrados.</div>`;
            return;
        }
        let html = `<table class="table table-bordered align-middle"><thead>
        <tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Estado</th></thead><tbody>`;
        output.forEach(u=>{
            html += `<tr>
                <td>${u.nombre}</td>
                <td>${u.email}</td>
                <td>${u.rol}</td>
                <td><span class="badge ${u.activo==1?'bg-success':'bg-danger'}">${u.activo==1?'Activo':'Inactivo'}</span></td>
            </tr>`;
        });
        html += `</tbody></table>`;
        cont.innerHTML = html;
    });
}

// DIRECTORES
function cargarDirectores() {
    const cont = document.getElementById('tabla-directores');
    fetch('../crud/crudUsuarios.php', {
        method: 'POST',
        body: new URLSearchParams({accion: 'listar'})
    })
    .then(r=>r.json()).then(data=>{
        let output = data.filter(u=>u.rol_id==4);
        if (output.length === 0) {
            cont.innerHTML = `<div class="alert alert-info">No hay directores registrados.</div>`;
            return;
        }
        let html = `<table class="table table-bordered align-middle"><thead>
        <tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Estado</th></thead><tbody>`;
        output.forEach(u=>{
            html += `<tr>
                <td>${u.nombre}</td>
                <td>${u.email}</td>
                <td>${u.rol}</td>
                <td><span class="badge ${u.activo==1?'bg-success':'bg-danger'}">${u.activo==1?'Activo':'Inactivo'}</span></td>
            </tr>`;
        });
        html += `</tbody></table>`;
        cont.innerHTML = html;
    });
}

// REPORTES
function cargarReportes() {
    const cont = document.getElementById('tabla-reportes');
    fetch('../crud/crudReportes.php', {
        method: 'POST',
        body: new URLSearchParams({accion: 'listar'})
    })
    .then(r=>r.json()).then(data=>{
        let html = `<table class="table table-bordered align-middle"><thead>
        <tr><th>ID</th><th>Alumno</th><th>Comentario</th><th>Fecha</th></thead><tbody>`;
        data.forEach(r=>{
            html += `<tr>
                <td>${r.id}</td>
                <td>${r.alumno}</td>
                <td>${r.comentario}</td>
                <td>${r.fecha}</td>
            </tr>`;
        });
        html += `</tbody></table>`;
        cont.innerHTML = html;
    });
}

// APROBACIONES
function cargarAprobaciones() {
    const cont = document.getElementById('tabla-aprobaciones');
    fetch('../crud/crudAprobaciones.php', {
        method: 'POST',
        body: new URLSearchParams({accion: 'listar'})
    })
    .then(r=>r.json()).then(data=>{
        let html = `<table class="table table-bordered align-middle"><thead>
        <tr><th>Alumno</th><th>Clase</th><th>Acciones</th></thead><tbody>`;
        data.forEach(i=>{
            html += `<tr>
                <td>${i.alumno}</td>
                <td>${i.clase}</td>
                <td>
                    <button class="btn btn-sm btn-success" onclick="aprobarInscripcion(${i.id})">Aprobar</button>
                    <button class="btn btn-sm btn-danger" onclick="rechazarInscripcion(${i.id})">Rechazar</button>
                </td>
            </tr>`;
        });
        html += `</tbody></table>`;
        cont.innerHTML = html;
    });
}
function aprobarInscripcion(id){
    fetch('../crud/crudAprobaciones.php',{
        method:'POST',
        body: new URLSearchParams({accion:'aprobar',id})
    }).then(()=>cargarAprobaciones());
}
function rechazarInscripcion(id){
    fetch('../crud/crudAprobaciones.php',{
        method:'POST',
        body: new URLSearchParams({accion:'rechazar',id})
    }).then(()=>cargarAprobaciones());
}

document.addEventListener('DOMContentLoaded', ()=>mostrarSeccion('inicio'));