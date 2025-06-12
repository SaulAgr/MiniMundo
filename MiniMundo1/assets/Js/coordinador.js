document.addEventListener('DOMContentLoaded', function() {
    window.mostrarSeccion = function(seccion, ev) {
        document.querySelectorAll('.pantalla').forEach(div => div.classList.add('d-none'));
        document.getElementById(seccion).classList.remove('d-none');
        if (ev) ev.preventDefault();

        switch(seccion) {
            case 'clases': cargarClases(); renderFormClase(); break;
            case 'profesores': cargarProfesores(); renderFormProfesor(); break;
            case 'alumnos': cargarAlumnos(); renderFormAlumno(); break;
            case 'inscripciones': cargarInscripciones(); renderFormInscripcion(); break;
            case 'perfil': cargarPerfil(); break;
        }
    };

    // FORMULARIO ALTA PROFESOR
    function renderFormProfesor() {
        document.getElementById('form-profesor').innerHTML = `
            <form id="formAddProfesor" class="mb-3">
                <div class="row g-2">
                    <div class="col"><input type="text" name="nombre" class="form-control" placeholder="Nombre" required></div>
                    <div class="col"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
                    <div class="col"><input type="password" name="password" class="form-control" placeholder="Contraseña" required></div>
                    <div class="col-auto"><button type="submit" class="btn btn-success">Agregar profesor</button></div>
                </div>
            </form>
        `;
        document.getElementById('formAddProfesor').onsubmit = function(ev) {
            ev.preventDefault();
            let form = new FormData(this);
            form.append('accion', 'crear_profesor');
            fetch('../crud/crudCoordinador.php', { method: 'POST', body: form })
            .then(r => r.json()).then(res => {
                if(res.ok) { cargarProfesores(); this.reset(); }
                else alert('No se pudo agregar');
            });
        };
    }

    // FORMULARIO ALTA ALUMNO
    function renderFormAlumno() {
        document.getElementById('form-alumno').innerHTML = `
            <form id="formAddAlumno" class="mb-3">
                <div class="row g-2">
                    <div class="col"><input type="text" name="nombre" class="form-control" placeholder="Nombre" required></div>
                    <div class="col"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
                    <div class="col"><input type="password" name="password" class="form-control" placeholder="Contraseña" required></div>
                    <div class="col-auto"><button type="submit" class="btn btn-success">Agregar alumno</button></div>
                </div>
            </form>
        `;
        document.getElementById('formAddAlumno').onsubmit = function(ev) {
            ev.preventDefault();
            let form = new FormData(this);
            form.append('accion', 'crear_alumno');
            fetch('../crud/crudCoordinador.php', { method: 'POST', body: form })
            .then(r => r.json()).then(res => {
                if(res.ok) { cargarAlumnos(); this.reset(); }
                else alert('No se pudo agregar');
            });
        };
    }

    // FORMULARIO CLASE (alta y edición)
    function renderFormClase() {
        fetch('../crud/crudCoordinador.php', { method: 'POST', body: new URLSearchParams({accion: 'profesores_activos'}) })
        .then(r => r.json())
        .then(profesores => {
            let options = profesores.map(p => `<option value="${p.id}">${p.nombre}</option>`).join('');
            document.getElementById('form-clase').innerHTML = `
                <form id="formAddClase" class="mb-3">
                    <div class="row g-2">
                        <div class="col"><input type="text" name="nombre" class="form-control" placeholder="Nombre de la clase" required></div>
                        <div class="col">
                            <select name="profesor_id" class="form-control" required>
                                <option value="">Seleccione profesor</option>
                                ${options}
                            </select>
                        </div>
                        <div class="col"><input type="text" name="periodo" class="form-control" placeholder="Periodo" required></div>
                        <div class="col"><input type="number" name="cup_maximo" class="form-control" placeholder="Cupo máximo" min="1" value="30" required></div>
                        <div class="col-auto"><button type="submit" class="btn btn-success">Agregar clase</button></div>
                    </div>
                </form>
            `;
            document.getElementById('formAddClase').onsubmit = function(ev) {
                ev.preventDefault();
                let form = new FormData(this);
                form.append('accion', 'crear_clase');
                fetch('../crud/crudCoordinador.php', { method: 'POST', body: form })
                .then(r => r.json()).then(res => {
                    if(res.ok) {
                        cargarClases();
                        this.reset();
                        setTimeout(() => cargarClases(), 300);
                    } else alert('No se pudo agregar');
                });
            };
        });
    }

    // FORMULARIO INSCRIPCIÓN DE ALUMNO A CLASE
    function renderFormInscripcion() {
        Promise.all([
            fetch('../crud/crudCoordinador.php', { method: 'POST', body: new URLSearchParams({accion: 'alumnos_activos'}) }).then(r=>r.json()),
            fetch('../crud/crudCoordinador.php', { method: 'POST', body: new URLSearchParams({accion: 'listar_clases'}) }).then(r=>r.json())
        ]).then(([alumnos, clases]) => {
            let optionsAlumnos = alumnos.map(a => `<option value="${a.id}">${a.nombre}</option>`).join('');
            let optionsClases  = clases.map(c => `<option value="${c.id}">${c.nombre}</option>`).join('');
            document.getElementById('form-inscripcion').innerHTML = `
                <form id="formInscribirAlumno" class="mb-3">
                    <div class="row g-2">
                        <div class="col"><select name="alumno_id" class="form-control" required>
                            <option value="">Seleccione alumno</option>
                            ${optionsAlumnos}
                        </select></div>
                        <div class="col"><select name="clase_id" class="form-control" required>
                            <option value="">Seleccione clase</option>
                            ${optionsClases}
                        </select></div>
                        <div class="col-auto"><button type="submit" class="btn btn-success">Inscribir</button></div>
                    </div>
                </form>
            `;
            document.getElementById('formInscribirAlumno').onsubmit = function(ev) {
                ev.preventDefault();
                let form = new FormData(this);
                form.append('accion', 'inscribir_alumno');
                fetch('../crud/crudCoordinador.php', { method: 'POST', body: form })
                .then(r=>r.json()).then(res=>{
                    if(res.ok) { cargarInscripciones(); this.reset(); }
                    else alert(res.msg || 'No se pudo inscribir');
                });
            };
        });
    }

    // Carga todas las clases y genera la tabla con los botones de editar/eliminar
function cargarClases() {
    fetch('../crud/crudCoordinador.php', {
        method: 'POST',
        body: new URLSearchParams({accion: 'listar_clases'})
    })
    .then(r => r.json())
    .then(datos => {
        let html = `<table class="table table-sm">
            <thead><tr>
                <th>Clase</th><th>Profesor</th><th>Periodo</th><th>Cupo</th><th>Cerrada</th><th>Acciones</th>
            </tr></thead><tbody>`;
        datos.forEach(clase => {
            html += `<tr>
                <td>${clase.nombre}</td>
                <td>${clase.profesor || '-'}</td>
                <td>${clase.periodo}</td>
                <td>${clase.cupo_maximo || '-'}</td>
                <td>${clase.cerrada == 1 ? 'Sí' : 'No'}</td>
                <td>
                    <button class="btn btn-warning btn-sm" onclick="editarClaseModal(${clase.id}, '${clase.nombre}', '${clase.periodo}', '${clase.profesor_id}', '${clase.cupo_maximo}')">Editar</button>
                    <button class="btn btn-danger btn-sm" onclick="eliminarClase(${clase.id})">Eliminar</button>
                </td>
            </tr>`;
        });
        html += `</tbody></table>`;
        document.getElementById('tabla-clases').innerHTML = html;
    });
}

// Eliminar clase: envía solicitud al director
window.eliminarClase = function(id) {
    if(confirm('¿Eliminar esta clase? Se enviará solicitud al director.')) {
        fetch('../crud/crudCoordinador.php', {
            method: 'POST',
            body: new URLSearchParams({accion: 'eliminar_clase', id})
        })
        .then(r => r.json())
        .then(res => {
            alert(res.msg);
            if(res.ok) cargarClases();
        });
    }
};

// Modal de edición de clase: muestra formulario y llama a editarClaseSolicitud al guardar
window.editarClaseModal = function(id, nombre = '', periodo = '', profesor_id = '', cupo_maximo = '') {
    // Aquí debes tener tu modal HTML preparado.
    // Rellena los campos del formulario con los datos actuales.
    document.getElementById('editarClaseId').value = id;
    document.getElementById('editarNombre').value = nombre;
    document.getElementById('editarPeriodo').value = periodo;
    document.getElementById('editarProfesor').value = profesor_id;
    document.getElementById('editarCupo').value = cupo_maximo;
    // Muestra el modal
    $('#modalEditarClase').modal('show'); // Si usas Bootstrap
};

// Cuando el usuario guarda los cambios en el modal, llama a esta función
window.editarClaseSolicitud = function() {
    const id = document.getElementById('editarClaseId').value;
    const nombre = document.getElementById('editarNombre').value;
    const periodo = document.getElementById('editarPeriodo').value;
    const profesor_id = document.getElementById('editarProfesor').value;
    const cup_maximo = document.getElementById('editarCupo').value;

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
    })
    .then(r => r.json())
    .then(res => {
        alert(res.msg);
        if(res.ok) {
            $('#modalEditarClase').modal('hide'); // Si usas Bootstrap
            cargarClases();
        }
    });
};
    // Modal de edición de clase
    window.editarClaseModal = function(id) {
        // Traemos info de la clase y profesores
        Promise.all([
            fetch('../crud/crudCoordinador.php', { method: 'POST', body: new URLSearchParams({accion: 'listar_clases'}) }).then(r=>r.json()),
            fetch('../crud/crudCoordinador.php', { method: 'POST', body: new URLSearchParams({accion: 'profesores_activos'}) }).then(r=>r.json())
        ]).then(([clases, profesores]) => {
            let c = clases.find(x=>x.id==id);
            let options = profesores.map(p => `<option value="${p.id}" ${c.profesor_id==p.id?'selected':''}>${p.nombre}</option>`).join('');
            let html = `
                <form id="formEditClase" class="p-3">
                    <h5>Editar clase</h5>
                    <input type="hidden" name="id" value="${c.id}">
                    <div class="mb-2">
                        <label>Nombre:</label>
                        <input type="text" class="form-control" name="nombre" value="${c.nombre}" required>
                    </div>
                    <div class="mb-2">
                        <label>Profesor:</label>
                        <select name="profesor_id" class="form-control" required>
                            <option value="">Seleccione profesor</option>
                            ${options}
                        </select>
                    </div>
                    <div class="mb-2">
                        <label>Periodo:</label>
                        <input type="text" class="form-control" name="periodo" value="${c.periodo}" required>
                    </div>
                    <div class="mb-2">
                        <label>Cupo máximo:</label>
                        <input type="number" class="form-control" name="cup_maximo" value="${c.cupo_maximo}" min="1" required>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <button type="button" class="btn btn-secondary ms-2" onclick="cerrarModal()">Cancelar</button>
                    </div>
                </form>
            `;
            crearModal(html);
            document.getElementById('formEditClase').onsubmit = function(ev){
                ev.preventDefault();
                let form = new FormData(this);
                form.append('accion','editar_clase');
                fetch('../crud/crudCoordinador.php',{method:'POST',body:form})
                .then(r=>r.json()).then(res=>{
                    if(res.ok){
                        cerrarModal();
                        cargarClases();
                        alert('Clase actualizada correctamente.');
                    }else{
                        alert('No se pudo actualizar');
                    }
                });
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
    window.cerrarModal = function() {
        let m = document.getElementById("modal-js");
        if(m) m.remove();
    };

    // CRUD PROFESORES (solo ver, alta)
    function cargarProfesores() {
        fetch('../crud/crudCoordinador.php', {
            method: 'POST',
            body: new URLSearchParams({accion: 'listar_profesores'})
        })
        .then(r => r.json())
        .then(datos => {
            let html = `<table class="table table-sm"><thead>
                <tr><th>Nombre</th><th>Email</th><th>Activo</th></tr></thead><tbody>`;
            datos.forEach(usuario => {
                html += `<tr>
                    <td>${usuario.nombre}</td>
                    <td>${usuario.email}</td>
                    <td>${usuario.activo == 1 ? 'Sí' : 'No'}</td>
                </tr>`;
            });
            html += `</tbody></table>`;
            document.getElementById('tabla-profesores').innerHTML = html;
        });
    }

    // CRUD ALUMNOS (solo ver los inscritos en sus clases, alta)
    function cargarAlumnos() {
        fetch('../crud/crudCoordinador.php', {
            method: 'POST',
            body: new URLSearchParams({accion: 'listar_alumnos'})
        })
        .then(r => r.json())
        .then(datos => {
            let html = `<table class="table table-sm"><thead>
                <tr><th>Nombre</th><th>Email</th><th>Clase</th><th>Activo</th></tr></thead><tbody>`;
            datos.forEach(usuario => {
                html += `<tr>
                    <td>${usuario.nombre}</td>
                    <td>${usuario.email}</td>
                    <td>${usuario.clase}</td>
                    <td>${usuario.activo == 1 ? 'Sí' : 'No'}</td>
                    <td>
                        <button class="btn btn-success btn-sm" onclick="abrirInscripcion(${usuario.id}, '${usuario.nombre}')">Dar de alta en clase</button>
                    </td>
                </tr>`;
            });
            html += `</tbody></table>`;
            document.getElementById('tabla-alumnos').innerHTML = html;
        });
    }
    window.abrirInscripcion = function(alumno_id, nombre) {
    // Obtén las clases disponibles
    fetch('../crud/crudCoordinador.php', {
        method: 'POST',
        body: new URLSearchParams({accion: 'listar_clases'})
    })
    .then(r => r.json())
    .then(clases => {
        let options = clases.map(c => `<option value="${c.id}">${c.nombre}</option>`).join('');
        let html = `
            <form id="formInscribirAlumnoRapido" class="p-3">
                <h5>Inscribir a ${nombre} en una clase</h5>
                <input type="hidden" name="alumno_id" value="${alumno_id}">
                <div class="mb-2">
                    <label>Clase:</label>
                    <select name="clase_id" class="form-control" required>
                        <option value="">Seleccione clase</option>
                        ${options}
                    </select>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-success">Inscribir</button>
                    <button type="button" class="btn btn-secondary ms-2" onclick="cerrarModal()">Cancelar</button>
                </div>
            </form>
        `;
        crearModal(html);
        document.getElementById('formInscribirAlumnoRapido').onsubmit = function(ev){
            ev.preventDefault();
            let form = new FormData(this);
            form.append('accion', 'inscribir_alumno');
            fetch('../crud/crudCoordinador.php', { method: 'POST', body: form })
            .then(r => r.json()).then(res => {
                if(res.ok) {
                    cerrarModal();
                    cargarAlumnos();
                    alert('Alumno inscrito correctamente.');
                } else {
                    alert(res.msg || 'No se pudo inscribir');
                }
            });
        };
    });
}

    // INSCRIPCIONES (solo de sus clases)
    function cargarInscripciones() {
        fetch('../crud/crudCoordinador.php', {
            method: 'POST',
            body: new URLSearchParams({accion: 'listar_inscripciones'})
        })
        .then(r => r.json())
        .then(datos => {
            let html = `<table class="table table-sm"><thead>
                <tr><th>Alumno</th><th>Clase</th><th>Aprobada</th><th>Fecha</th><th>Acciones</th></tr></thead><tbody>`;
            datos.forEach(ins => {
                html += `<tr>
                    <td>${ins.alumno}</td>
                    <td>${ins.clase}</td>
                    <td>${ins.aprobada == 1 ? 'Sí' : 'No'}</td>
                    <td>${ins.fecha_inscripcion}</td>
                    <td>
                        ${ins.aprobada == 1 ? '' : `
                        <button class="btn btn-success btn-sm" onclick="aprobarInscripcion(${ins.id})">Aprobar</button>
                        <button class="btn btn-danger btn-sm" onclick="rechazarInscripcion(${ins.id})">Rechazar</button>
                        `}
                    </td>
                </tr>`;
            });
            html += `</tbody></table>`;
            document.getElementById('tabla-inscripciones').innerHTML = html;
        });
    }
    window.aprobarInscripcion = function(id) {
        fetch('../crud/crudCoordinador.php',{
            method:'POST',
            body: new URLSearchParams({accion:'aprobar_inscripcion', id})
        }).then(r=>r.json()).then(res=>{
            if(res.ok) cargarInscripciones();
            else alert('No se pudo aprobar');
        });
    }
    window.rechazarInscripcion = function(id) {
        fetch('../crud/crudCoordinador.php',{
            method:'POST',
            body: new URLSearchParams({accion:'rechazar_inscripcion', id})
        }).then(r=>r.json()).then(res=>{
            if(res.ok) cargarInscripciones();
            else alert('No se pudo rechazar');
        });
    }

    // PERFIL DEL COORDINADOR
    function cargarPerfil() {
        fetch('../crud/crudCoordinador.php', {
            method: 'POST',
            body: new URLSearchParams({accion: 'perfil'})
        })
        .then(r => r.json())
        .then(datos => {
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
            document.getElementById('formPerfil').onsubmit = function(ev) {
                ev.preventDefault();
                let form = new FormData(this);
                form.append('accion','editar_perfil');
                fetch('../crud/crudCoordinador.php', {
                    method: 'POST',
                    body: form
                })
                .then(r => r.json())
                .then(res => {
                    if(res.ok) alert('Perfil actualizado');
                    else alert('No se pudo actualizar');
                });
            };
        });
    }

    // Mostrar inicio por default
    mostrarSeccion('inicio');
});