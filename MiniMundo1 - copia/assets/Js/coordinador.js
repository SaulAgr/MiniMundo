import { 
  fetchCrud, 
  renderTable, 
  mostrarSeccion as baseMostrarSeccion, 
  renderPerfil, 
  flash, 
  attachForm, 
  crearModal, 
  cerrarModal, 
  cargarPerfil 
} from './utils.js';

document.addEventListener('DOMContentLoaded', function() {
    // --- Mostrar secciones ---
    window.mostrarSeccion = function(seccion, ev) {
    document.querySelectorAll('.pantalla').forEach(div => div.classList.add('d-none'));
    const pantalla = document.getElementById(seccion);
    if (pantalla) {
        pantalla.classList.remove('d-none');
    } else {
        console.warn("Sección no encontrada:", seccion);
        return;
    }
    if (ev) ev.preventDefault();
    switch(seccion) {
        case 'clases': 
            if (typeof cargarClases === "function") cargarClases();
            if (typeof renderFormClase === "function") renderFormClase();
            break;
        case 'profesores': 
            if (typeof cargarProfesores === "function") cargarProfesores();
            if (typeof renderFormProfesor === "function") renderFormProfesor();
            break;
        case 'alumnos': 
            if (typeof cargarAlumnos === "function") cargarAlumnos();
            if (typeof renderFormAlumno === "function") renderFormAlumno();
            break;
        case 'inscripciones': 
            if (typeof cargarInscripciones === "function") cargarInscripciones();
            if (typeof renderFormInscripcion === "function") renderFormInscripcion();
            break;
        case 'perfil': 
            if (typeof cargarPerfil === "function") cargarPerfil();
            break;
    }
};

    // --- Altas ---
    function renderAltaForm({containerId, formId, fields, accion, url, cbSuccess, submitBtn}) {
        const htmlFields = fields.map(f =>
            `<div class="col"><input type="${f.type}" name="${f.name}" class="form-control" placeholder="${f.placeholder}" required></div>`
        ).join('');
        document.getElementById(containerId).innerHTML = `
            <form id="${formId}" class="mb-3">
                <div class="row g-2">
                    ${htmlFields}
                    <div class="col-auto"><button type="submit" class="btn btn-success">${submitBtn}</button></div>
                </div>
            </form>
        `;
        document.getElementById(formId).onsubmit = function(ev) {
            ev.preventDefault();
            let form = new FormData(this);
            form.append('accion', accion);
            fetchCrud(url, form).then(res => {
                if(res.ok) {
                    cbSuccess && cbSuccess();
                    this.reset();
                } else alert(res.msg || 'No se pudo agregar');
            });
        };
    }

    function renderFormProfesor() {
        renderAltaForm({
            containerId: 'form-profesor',
            formId: 'formAddProfesor',
            fields: [
                {type:'text', name:'nombre', placeholder:'Nombre'},
                {type:'email', name:'email', placeholder:'Email'},
                {type:'password', name:'password', placeholder:'Contraseña'}
            ],
            accion: 'crear_profesor',
            url: '../crud/crudCoordinador.php',
            cbSuccess: cargarProfesores,
            submitBtn: 'Agregar profesor'
        });
    }
    function renderFormAlumno() {
        renderAltaForm({
            containerId: 'form-alumno',
            formId: 'formAddAlumno',
            fields: [
                {type:'text', name:'nombre', placeholder:'Nombre'},
                {type:'email', name:'email', placeholder:'Email'},
                {type:'password', name:'password', placeholder:'Contraseña'}
            ],
            accion: 'crear_alumno',
            url: '../crud/crudCoordinador.php',
            cbSuccess: cargarAlumnos,
            submitBtn: 'Agregar alumno'
        });
    }
    function renderFormClase() {
        fetchCrud('../crud/crudCoordinador.php', {accion: 'profesores_activos'})
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
                fetchCrud('../crud/crudCoordinador.php', form)
                .then(res => {
                    if(res.ok) {
                        cargarClases();
                        this.reset();
                    } else alert('No se pudo agregar');
                });
            };
        });
    }
    function renderFormInscripcion() {
        Promise.all([
            fetchCrud('../crud/crudCoordinador.php', {accion: 'alumnos_disponibles_para_inscripcion'}),
            fetchCrud('../crud/crudCoordinador.php', {accion: 'listar_clases'})
        ]).then(([alumnos, clases]) => {
            const cont = document.getElementById('form-inscripcion');
            if (!cont) {
                console.error('No existe el contenedor form-inscripcion en el DOM');
                return;
            }
            if (!Array.isArray(alumnos)) {
                console.error('La respuesta de alumnos no es un array:', alumnos);
                cont.innerHTML = `<div class="alert alert-danger">Error al cargar alumnos. Intenta de nuevo.</div>`;
                return;
            }
            if (alumnos.length === 0) {
                cont.innerHTML = `
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        Todos los alumnos ya están inscritos en todas las clases disponibles.
                    </div>
                `;
                return;
            }
            let optionsAlumnos = alumnos.map(a => `<option value="${a.id}">${a.nombre}</option>`).join('');
            let optionsClases  = clases.map(c => `<option value="${c.id}">${c.nombre}</option>`).join('');
            cont.innerHTML = `
                <form id="formInscribirAlumno" class="mb-3">
                    <div class="row g-2">
                        <div class="col">
                            <select name="alumno_id" class="form-control" required>
                                <option value="">Seleccione alumno</option>
                                ${optionsAlumnos}
                            </select>
                        </div>
                        <div class="col">
                            <select name="clase_id" class="form-control" required>
                                <option value="">Seleccione clase</option>
                                ${optionsClases}
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-success">Inscribir</button>
                        </div>
                    </div>
                </form>
            `;
            document.getElementById('formInscribirAlumno').onsubmit = function(ev) {
                ev.preventDefault();
                let form = new FormData(this);
                form.append('accion', 'inscribir_alumno');
                fetchCrud('../crud/crudCoordinador.php', form)
                .then(res=>{
                    if(res.ok) { cargarInscripciones(); this.reset(); }
                    else alert(res.msg || 'No se pudo inscribir');
                });
            };
        }).catch(e => {
            // Si ocurre un error en la promesa, muestra el error
            const cont = document.getElementById('form-inscripcion');
            if (cont) {
                cont.innerHTML = `<div class="alert alert-danger">Error de conexión o del servidor.</div>`;
            }
            console.error('Error en renderFormInscripcion:', e);
        });
    }

    // --- Tablas ---
    function cargarClases() {
        fetchCrud('../crud/crudCoordinador.php', {accion: 'listar_clases'})
        .then(datos => {
            renderTable(
                datos,
                [
                    {key:'nombre',label:'Nombre'},
                    {key:'profesor',label:'Profesor'},
                    {key:'periodo',label:'Periodo'},
                    {key:'cupo_maximo',label:'Cupo máximo'},
                    {
                        key:'cerrada',
                        label:'Estado',
                        render: fila =>
                            fila.cerrada == 1
                            ? '<span class="badge bg-danger">Cerrada</span>'
                            : '<span class="badge bg-success">Abierta</span>'
                    }
                ],
                'tabla-clases',
                {
                    acciones: [
                        {
                            label: 'Editar',
                            class: 'btn-warning',
                            onClick: (el, id) => editarClaseModal(id)
                        },
                        {
                            label: 'Eliminar',
                            class: 'btn-danger',
                            onClick: (el, id) => eliminarClase(id)
                        }
                    ]
                }
            );
        });
    }
    window.eliminarClase = function(id) {
        if(confirm('¿Eliminar esta clase? Se enviará solicitud al director.')) {
            fetchCrud('../crud/crudCoordinador.php', {accion: 'eliminar_clase', id})
            .then(res => {
                alert(res.msg);
                if(res.ok) cargarClases();
            });
        }
    };
    window.editarClaseModal = function(id) {
        Promise.all([
            fetchCrud('../crud/crudCoordinador.php', {accion: 'listar_clases'}),
            fetchCrud('../crud/crudCoordinador.php', {accion: 'profesores_activos'})
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
                fetchCrud('../crud/crudCoordinador.php', form)
                .then(res=>{
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
    };

    function cargarProfesores() {
        fetchCrud('../crud/crudCoordinador.php', {accion: 'listar_profesores'})
        .then(datos => {
            renderTable(
                datos,
                [
                    {key:'nombre',label:'Nombre'},
                    {key:'email',label:'Email'},
                    {
                        key:'activo',
                        label:'Estado',
                        render: fila =>
                            fila.activo == 1
                            ? '<span class="badge bg-success">Activo</span>'
                            : '<span class="badge bg-danger">Inactivo</span>'
                    }
                ],
                'tabla-profesores'
            );
        });
    }
    function cargarAlumnos() {
        fetchCrud('../crud/crudCoordinador.php', {accion: 'listar_alumnos'})
        .then(datos => {
            renderTable(
                datos,
                [
                    {key:'nombre',label:'Nombre'},
                    {key:'email',label:'Email'},
                    {key:'clase',label:'Clase'},
                    {
                        key:'activo',
                        label:'Estado',
                        render: fila =>
                            fila.activo == 1
                            ? '<span class="badge bg-success">Activo</span>'
                            : '<span class="badge bg-danger">Inactivo</span>'
                    }
                ],
                'tabla-alumnos'
                
            );
        });
    }
    window.abrirInscripcion = function(alumno_id, nombre) {
        fetchCrud('../crud/crudCoordinador.php', {accion: 'listar_clases'})
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
                fetchCrud('../crud/crudCoordinador.php', form)
                .then(res => {
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
    function cargarInscripciones() {
        fetchCrud('../crud/crudCoordinador.php', {accion: 'listar_inscripciones'})
        .then(datos => {
            renderTable(
                datos,
                [
                    {key:'alumno',label:'Alumno'},
                    {key:'clase',label:'Clase'},
                    {
                        key:'aprobada',
                        label:'Estado',
                        render: fila =>
                            fila.aprobada == 1
                            ? '<span class="badge bg-success">Aprobada</span>'
                            : '<span class="badge bg-warning text-dark">Pendiente</span>'
                    },
                    {key:'fecha_inscripcion',label:'Fecha'}
                ],
                'tabla-inscripciones',
                {
                    acciones: [
                        {
                            label: 'Aprobar',
                            class: 'btn-success btn-aprobar-inscripcion',
                            onClick: (el, id) => aprobarInscripcion(id, el)
                        },
                        {
                            label: 'Rechazar',
                            class: 'btn-danger btn-rechazar-inscripcion',
                            onClick: (el, id) => rechazarInscripcion(id, el)
                        }
                    ]
                }
            );

            // Post-procesamiento: deshabilitar/ocultar botones según estado
            const rows = document.querySelectorAll('#tabla-inscripciones tbody tr');
            datos.forEach((fila, idx) => {
                const row = rows[idx];
                if (!row) return;
                const aprobarBtn = row.querySelector('.btn-aprobar-inscripcion');
                const rechazarBtn = row.querySelector('.btn-rechazar-inscripcion');
                if (fila.aprobada == 1) {
                    // Si aprobada, oculta ambos botones
                    if (aprobarBtn) aprobarBtn.style.display = 'none';
                    if (rechazarBtn) rechazarBtn.style.display = 'none';
                } else {
                    // Si pendiente, ambos botones visibles
                    if (aprobarBtn) aprobarBtn.style.display = '';
                    if (rechazarBtn) rechazarBtn.style.display = '';
                }
            });
        });
    }

    // Confirmación al aprobar
    window.aprobarInscripcion = function(id, el) {
        if (confirm('¿Seguro que deseas aceptar al alumno en la clase?')) {
            fetchCrud('../crud/crudCoordinador.php',{accion:'aprobar_inscripcion', id})
            .then(res=>{
                if(res.ok) cargarInscripciones();
                else alert('No se pudo aprobar');
            });
        }
    }

    window.rechazarInscripcion = function(id, el) {
        fetchCrud('../crud/crudCoordinador.php',{accion:'rechazar_inscripcion', id})
        .then(res=>{
            if(res.ok) cargarInscripciones();
            else alert('No se pudo rechazar');
        });
    }

    // --- Perfil ---
    function cargarPerfil() {
        fetchCrud('../crud/crudCoordinador.php', {accion: 'perfil'})
        .then(datos => {
            renderPerfil(datos, 'tabla-perfil', '../crud/crudCoordinador.php');
        });
    }

    // --- Inicio por default ---
    mostrarSeccion('inicio');
});