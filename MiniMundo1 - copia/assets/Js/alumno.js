import {
  mostrarSeccion,
  renderTable,
  fetchCrud,
  renderPerfil,
  flash
} from './utils.js';

// ========== CONFIGURACIÓN DE SECCIONES ==========
const secciones = {
  clases: cargarClases,
  avisos: cargarAvisos,
  tareas: cargarTareas,
  calificaciones: cargarCalificaciones,
  reportes: cargarReportes,
  perfil: cargarPerfil,
  // mensajes: cargarMensajes, // Para chat futuro
  inicio: () => {}
};

// ========== COLUMNAS PARA TABLAS ==========
const columnasClases = [
  { key: "nombre", label: "Clase" },
  { key: "periodo", label: "Periodo" },
  { key: "profesor", label: "Profesor" }
];

const columnasCalificaciones = [
  { key: "clase", label: "Clase" },
  { key: "calificacion", label: "Calificación", render: c => c.calificacion !== null ? c.calificacion : '-' },
  { key: "observacion", label: "Observación" }
];

// ========== FUNCIONES DE SECCIÓN ==========

function cargarClases() {
  fetchCrud('../crud/crudAlumno.php', { accion: 'mis_clases' })
    .then(clases => {
      renderTable(clases, columnasClases, 'tabla-clases', {
        emptyMsg: 'No estás inscrito en ninguna clase.'
      });
      const contenido = document.getElementById('contenido-clase');
      if (contenido) contenido.innerHTML = "";
    });
}

function cargarAvisos() {
  fetchCrud('../crud/crudAlumno.php', { accion: 'avisos' })
    .then(datos => {
      let html = `<ul class="list-group">`;
      if (datos.length === 0) html += `<li class="list-group-item">No tienes avisos recientes.</li>`;
      datos.forEach(a => {
        html += `<li class="list-group-item">
          <strong>${a.titulo}</strong>: ${a.mensaje} <br>
          <span class="text-muted small">${a.fecha}</span>
        </li>`;
      });
      html += `</ul>`;
      const avisos = document.getElementById('avisos-alumno');
      if (avisos) avisos.innerHTML = html;
    });
}

function cargarTareas() {
  fetchCrud('../crud/crudAlumno.php', { accion: 'tareas' })
    .then(datos => {
      let html = `<ul class="list-group">`;
      if (datos.length === 0) html += `<li class="list-group-item">No tienes tareas asignadas.</li>`;
      datos.forEach(t => {
        html += `<li class="list-group-item">
          <strong>${t.titulo}</strong>: ${t.descripcion} 
          <br>Entrega: ${t.fecha_entrega}
          ${t.archivo ? `<br><a href="../recursos/${t.archivo}" target="_blank"><i class="bi bi-file-earmark-arrow-down"></i> Descargar archivo</a>` : ''}
        </li>`;
      });
      html += `</ul>`;
      const tareas = document.getElementById('tareas-alumno');
      if (tareas) tareas.innerHTML = html;
    });
}

function cargarCalificaciones() {
  fetchCrud('../crud/crudAlumno.php', { accion: 'calificaciones' })
    .then(datos => {
      renderTable(datos, columnasCalificaciones, 'calificaciones-alumno', {
        emptyMsg: 'No tienes calificaciones registradas.'
      });
    });
}

function cargarReportes() {
  fetchCrud('../crud/crudAlumno.php', { accion: 'reportes' })
    .then(datos => {
      let html = `<ul class="list-group">`;
      if (datos.length === 0) html += `<li class="list-group-item">No tienes reportes recientes.</li>`;
      datos.forEach(r => {
        html += `<li class="list-group-item">
          <strong>${r.titulo}</strong>: ${r.descripcion} <br>
          <span class="text-muted small">${r.fecha}</span>
        </li>`;
      });
      html += `</ul>`;
      const reportes = document.getElementById('reportes-alumno');
      if (reportes) reportes.innerHTML = html;
    });
}

function cargarPerfil() {
  fetchCrud('../crud/crudUsuarios.php', { accion: 'perfil' })
    .then(datos => {
      // Si la respuesta es {ok:true, data:{...}}, usa datos.data
      // Si la respuesta es solo {nombre:"...", email:"..."}, usa datos directamente
      const perfil = datos.data ? datos.data : datos;
      renderPerfil(perfil, 'tabla-perfil', '../crud/crudUsuarios.php');
    });
}

// ========== INICIALIZACIÓN Y ENLACE SIDEBAR ==========
document.addEventListener('DOMContentLoaded', () => {
  mostrarSeccion('inicio', secciones);

  // ENLAZADO CORRECTO PARA TODA LA SIDEBAR
  document.querySelectorAll('.sidebar [data-seccion]').forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      mostrarSeccion(link.getAttribute('data-seccion'), secciones, e);
      // Manejo de clase 'active' visual:
      document.querySelectorAll('.sidebar .nav-link').forEach(l => l.classList.remove('active'));
      link.classList.add('active');
    });
  });
});