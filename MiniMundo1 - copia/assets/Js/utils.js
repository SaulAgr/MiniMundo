// utils.js - Funciones utilitarias para paneles de usuario

// Muestra solo la sección seleccionada y ejecuta el callback de la sección
export function mostrarSeccion(seccion, secciones = {}, ev = null) {
  document.querySelectorAll('.pantalla').forEach(div => div.classList.add('d-none'));
  const div = document.getElementById(seccion);
  if (div) div.classList.remove('d-none');
  if (ev && typeof ev.preventDefault === "function") ev.preventDefault();
  if (secciones[seccion]) secciones[seccion]();
}

// Renderizado genérico de tablas
export function renderTable(data, columns, containerId, opts={}) {
  const el = document.getElementById(containerId);
  if (!el) return;
  let html = `<table class="table table-sm table-bordered mb-0"><thead><tr>`;
  columns.forEach(col => html += `<th>${col.label}</th>`);
  if (opts.acciones) html += `<th></th>`;
  html += `</tr></thead><tbody>`;
  if (!data || data.length === 0) {
    html += `<tr><td colspan="${columns.length + (opts.acciones?1:0)}">${opts.emptyMsg||'Sin registros.'}</td></tr>`;
  } else {
    data.forEach(fila => {
      html += `<tr>`;
      columns.forEach(col => {
        html += `<td>${col.render ? col.render(fila) : (fila[col.key] ?? '')}</td>`;
      });
      if (opts.acciones) {
        html += `<td>`;
        opts.acciones.forEach(acc => {
          html += `<button class="btn btn-sm ${acc.class||''}" type="button" title="${acc.label.replace(/<[^>]+>/g,'')}"
            onclick="(${acc.onClick})(this, '${fila.id}')">${acc.label}</button> `;
        });
        html += `</td>`;
      }
      html += `</tr>`;
    });
  }
  html += `</tbody></table>`;
  el.innerHTML = html;
}

// AJAX wrapper para peticiones tipo CRUD con fetch
export function fetchCrud(url, params) {
  return fetch(url, {
    method: 'POST',
    body: new URLSearchParams(params)
  })
  .then(r => r.json())
  .then(res => {
    // Si la respuesta es {ok:true, data:...} o solo array
    if (res && typeof res === 'object' && 'data' in res) return res.data;
    return res;
  });
}

// Renderizado de formulario de perfil y manejo de submit
export function renderPerfil(datos, containerId, url) {
  const el = document.getElementById(containerId);
  if (!el) return;
  let html = `<form id="formPerfil">
    <div class="mb-2">
        <label class="form-label">Nombre</label>
        <input type="text" class="form-control" name="nombre" value="${datos.nombre||''}">
    </div>
    <div class="mb-2">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" name="email" value="${datos.email||''}">
    </div>
    <button class="btn btn-primary" type="submit">Guardar</button>
  </form>`;
  el.innerHTML = html;
  let f = document.getElementById('formPerfil');
  if(f) f.addEventListener('submit', function(ev){
    ev.preventDefault();
    let form = new FormData(this);
    form.append('accion','editar_perfil');
    fetch(url, {
      method:'POST', body:form
    }).then(r=>r.json()).then(res=>{
      if(res.ok) flash('Perfil actualizado','success');
      else flash(res.msg || 'No se pudo actualizar', 'danger');
    });
  });
}

// Mini sistema de mensajes flash
export function flash(msg, tipo='info', contId='flash-msg') {
  let cont = document.getElementById(contId);
  if (!cont) {
    cont = document.createElement('div');
    cont.id = contId;
    cont.style.position = 'fixed';
    cont.style.top = '20px';
    cont.style.right = '20px';
    cont.style.zIndex = 9999;
    document.body.appendChild(cont);
  }
  cont.innerHTML = `<div class="alert alert-${tipo} alert-dismissible fade show" role="alert">
    ${msg}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>`;
  setTimeout(() => {
    if (cont.firstChild) cont.firstChild.classList.remove('show');
  }, 3500);
}

// Utilidad para enlazar envío de formularios AJAX
export function attachForm(formId, cb, opts={}) {
  let f = document.getElementById(formId);
  if (!f) return;
  f.addEventListener('submit', function(ev){
    ev.preventDefault();
    let form = new FormData(this);
    if (opts.extraParams) {
      Object.entries(opts.extraParams).forEach(([k,v])=>form.append(k,v));
    }
    fetch(opts.url || f.action, {
      method: 'POST',
      body: form
    }).then(r=>r.json()).then(cb);
  });
}

// Modal básico para formularios
export function crearModal(html) {
  let modalCont = document.createElement('div');
  modalCont.className = 'modal fade';
  modalCont.id = 'modalUtils';
  modalCont.innerHTML = `<div class="modal-dialog"><div class="modal-content">
    <div class="modal-body">${html}</div>
  </div></div>`;
  document.body.appendChild(modalCont);
  let modal = new bootstrap.Modal(modalCont);
  modal.show();
  modalCont.addEventListener('hidden.bs.modal', () => modalCont.remove());
}
export function cerrarModal() {
  let m = document.getElementById('modalUtils');
  if (m) {
    let modal = bootstrap.Modal.getInstance(m);
    if (modal) modal.hide();
  }
}

export function getCrudPerfilByRol(rol) {
  switch (rol) {
    case "profesor":
      return "../crud/crudDocente.php";
    case "padre":
      return "../crud/crudPadre.php";
    case "alumno":
    case "coordinador":
      return "../crud/crudUsuarios.php";
    default:
      return "../crud/crudUsuarios.php";
  }
}

// Carga y renderiza el perfil del usuario (universal para cualquier panel)
export function cargarPerfil(containerId = 'tabla-perfil') {
  // window.usuario_rol debe estar definido antes de llamar esto
  const rol = window.usuario_rol || "alumno";
  const url = getCrudPerfilByRol(rol);
  fetchCrud(url, { accion: "perfil" }).then(datos => {
    renderPerfil(datos, containerId, url);
  });
}