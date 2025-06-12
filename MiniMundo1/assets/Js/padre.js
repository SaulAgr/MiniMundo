document.addEventListener('DOMContentLoaded', function() {
    window.mostrarSeccion = function(seccion, ev) {
        document.querySelectorAll('.pantalla').forEach(div => div.classList.add('d-none'));
        document.getElementById(seccion).classList.remove('d-none');
        if (ev) ev.preventDefault();

        switch(seccion) {
            case 'avisos': cargarAvisos(); break;
            case 'progreso': cargarProgreso(); break;
            case 'mensajes': cargarMensajes(); break;
            case 'perfil': cargarPerfil(); break;
        }
    };

    // ========== AVISOS DE PROFESORES ==========
    function cargarAvisos() {
        fetch('../crud/crudPadres.php', {
            method: 'POST',
            body: new URLSearchParams({accion: 'avisos_hijos'})
        })
        .then(r => r.json())
        .then(avisos => {
            let html = `<table class="table table-sm"><thead>
                <tr><th>Hijo</th><th>Clase</th><th>Profesor</th><th>Título</th><th>Mensaje</th><th>Fecha</th></tr></thead><tbody>`;
            let opts = `<option value="">Selecciona un aviso</option>`;
            avisos.forEach(a => {
                html += `<tr>
                    <td>${a.hijo}</td>
                    <td>${a.clase}</td>
                    <td>${a.profesor}</td>
                    <td>${a.titulo}</td>
                    <td>${a.mensaje}</td>
                    <td>${a.fecha}</td>
                </tr>`;
                opts += `<option value="${a.id}">${a.titulo} (${a.clase} - ${a.fecha})</option>`;
            });
            html += `</tbody></table>`;
            document.getElementById('tabla-avisos').innerHTML = html;
            document.getElementById('input-aviso-id').innerHTML = opts;

            // Si hay avisos, selecciona el primero para mostrar comentarios
            if (avisos.length > 0) {
                document.getElementById('input-aviso-id').value = avisos[0].id;
                cargarComentariosAvisos();
            } else {
                document.getElementById('comentarios-avisos').innerHTML = `<div class="alert alert-info">No hay avisos disponibles.</div>`;
            }
        });
    }

    // ========== ENVÍO DE COMENTARIOS SOBRE AVISO ==========
    let fAviso = document.getElementById('formAvisoPadre');
    if(fAviso) fAviso.addEventListener('submit', function(ev){
        ev.preventDefault();
        let form = new FormData(this);
        form.append('accion','comentar_aviso');
        fetch('../crud/crudPadres.php',{method:'POST',body:form})
        .then(r=>r.json())
        .then(res=>{
            if(res.ok){
                alert('Comentario enviado');
                this.reset();
                cargarAvisos(); // Refresca lista de avisos y comentarios
            } else alert('No se pudo enviar');
        });
    });

    function cargarComentariosAvisos() {
        let aviso_id = document.getElementById('input-aviso-id').value;
        if (!aviso_id) {
            document.getElementById('comentarios-avisos').innerHTML = "";
            return;
        }
        fetch('../crud/crudPadres.php',{
            method:'POST',
            body:new URLSearchParams({accion:'listar_comentarios_aviso', aviso_id})
        })
        .then(r=>r.json())
        .then(coms=>{
            let html = `<h6>Comentarios sobre el aviso:</h6><ul class="list-group">`;
            if(coms.length === 0) html += `<li class="list-group-item">Sin comentarios.</li>`;
            coms.forEach(c=>{
                html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>${c.mensaje} <br>
                    <span class="text-muted small">${c.fecha}</span></span>
                    <button class="btn btn-danger btn-sm" onclick="borrarComentario(${c.id})"><i class="bi bi-trash"></i></button>
                </li>`;
            });
            html += `</ul>`;
            document.getElementById('comentarios-avisos').innerHTML = html;
        });
    }
    window.borrarComentario = function(id){
        if(confirm("¿Borrar este comentario?")){
            fetch('../crud/crudPadres.php',{
                method:'POST',
                body: new URLSearchParams({accion:'borrar_comentario',id})
            }).then(r=>r.json())
            .then(res=>{if(res.ok) cargarComentariosAvisos(); else alert("No se pudo borrar");});
        }
    };
    // Vuelve a enlazar el evento después de cargarAvisos()
    document.addEventListener('change', function(ev){
        if(ev.target && ev.target.id === 'input-aviso-id') cargarComentariosAvisos();
    });

    // ========== PROGRESO DE HIJOS ==========
    function cargarProgreso() {
        fetch('../crud/crudPadres.php',{
            method:'POST',
            body: new URLSearchParams({accion:'progreso_hijos'})
        })
        .then(r=>r.json())
        .then(regs=>{
            let html = `<table class="table table-sm"><thead>
                <tr><th>Hijo</th><th>Clase</th><th>Profesor</th><th>Calificación</th><th>Observación</th></tr></thead><tbody>`;
            regs.forEach(r=>{
                html += `<tr>
                    <td>${r.hijo}</td>
                    <td>${r.clase}</td>
                    <td>${r.profesor}</td>
                    <td>${r.calificacion??'-'}</td>
                    <td>${r.observacion??'-'}</td>
                </tr>`;
            });
            html += `</tbody></table>`;
            document.getElementById('tabla-progreso').innerHTML = html;
        });
    }

    // ========== CHAT CON PROFESOR (PADRE <-> PROFESOR) ==========
    function cargarMensajes() {
        fetch('../crud/crudPadres.php',{
            method:'POST',
            body: new URLSearchParams({accion:'profesores_hijos'})
        })
        .then(r=>r.json())
        .then(profesores=>{
            let opts = `<option value="">Selecciona profesor</option>`;
            profesores.forEach(p=>{
                opts+= `<option value="${p.id}">${p.nombre} (${p.clase})</option>`;
            });
            document.getElementById('input-profesor-id').innerHTML = opts;

            // Si hay profesores, selecciona el primero y carga sus mensajes
            if(profesores.length > 0) {
                document.getElementById('input-profesor-id').value = profesores[0].id;
                document.getElementById('profesor_id_hidden').value = profesores[0].id;
                listarMensajes();
            } else {
                document.getElementById('tabla-mensajes').innerHTML = `<div class="alert alert-info">No tienes profesores asignados a tus hijos.</div>`;
                document.getElementById('profesor_id_hidden').value = '';
            }
        });
    }

    function listarMensajes() {
        let prof_id = document.getElementById('input-profesor-id').value;
        document.getElementById('profesor_id_hidden').value = prof_id;
        if(!prof_id){ document.getElementById('tabla-mensajes').innerHTML = ""; return;}
        fetch('../crud/crudPadres.php',{
            method:'POST',
            body: new URLSearchParams({accion:'listar_mensajes', profesor_id: prof_id})
        })
        .then(r=>r.json())
        .then(msgs=>{
            let html = `<ul class="list-group" style="max-height:300px;overflow:auto;">`;
            if(msgs.length === 0) html+= `<li class="list-group-item">No hay mensajes aún.</li>`;
            msgs.forEach(m=>{
                html += `<li class="list-group-item">
                    <span class="${m.de_quien==='Padre'?'text-primary':'text-success'} fw-semibold">${m.de_quien}:</span> 
                    ${m.mensaje}
                    <span class="text-muted small float-end">${m.fecha}</span>
                    <button class="btn btn-danger btn-sm float-end me-2" onclick="borrarMensaje(${m.id})"><i class="bi bi-trash"></i></button>
                </li>`;
            });
            html += `</ul>`;
            document.getElementById('tabla-mensajes').innerHTML = html;
        });
    }
    window.borrarMensaje = function(id){
        if(confirm("¿Borrar este mensaje?")){
            fetch('../crud/crudPadres.php',{
                method:'POST',
                body: new URLSearchParams({accion:'borrar_mensaje',id})
            }).then(r=>r.json())
            .then(res=>{if(res.ok) listarMensajes(); else alert("No se pudo borrar");});
        }
    };
    // Cambia de chat al cambiar el select
    document.addEventListener('change', function(ev){
        if(ev.target && ev.target.id === 'input-profesor-id') listarMensajes();
    });

    // ENVÍO DE MENSAJE CHAT
    let fMsg = document.getElementById('formMensajePadre');
    if(fMsg) fMsg.addEventListener('submit', function(ev){
        ev.preventDefault();
        let form = new FormData(this);
        form.append('profesor_id', document.getElementById('input-profesor-id').value);
        form.append('accion','enviar_mensaje');
        fetch('../crud/crudPadres.php',{method:'POST',body:form})
        .then(r=>r.json())
        .then(res=>{
            if(res.ok){
                alert('Mensaje enviado');
                this.reset();
                listarMensajes();
            } else alert('No se pudo enviar');
        });
    });

    // ========== PERFIL ==========
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
                <button class="btn btn-warning" type="submit">Guardar</button>
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