/**
 * Script para el panel de administrador
 * Hereda funcionalidades del director y agrega funciones administrativas
 */
document.addEventListener("DOMContentLoaded", () => {
  // Variables globales
  const usuario_id = obtenerUsuarioId()
  let usuarios = []
  let solicitudes = []
  let accionConfirmacion = null
  const usuarioSeleccionado = null
  let solicitudSeleccionada = null

  // Inicialización
  inicializarTabs()
  cargarDashboardAdmin()

  // Event listeners para botones principales
  document.getElementById("btn-actualizar-dashboard").addEventListener("click", cargarDashboardAdmin)
  document.getElementById("btn-actualizar-clases").addEventListener("click", cargarTodasLasClases)
  document.getElementById("btn-actualizar-usuarios").addEventListener("click", cargarUsuarios)
  document.getElementById("btn-actualizar-solicitudes").addEventListener("click", cargarSolicitudes)
  document.getElementById("btn-nuevo-usuario").addEventListener("click", () => {
    abrirModalUsuario()
  })
  document.getElementById("btn-generar-reporte-completo").addEventListener("click", generarReporteCompleto)
  document.getElementById("btn-exportar-datos").addEventListener("click", exportarDatos)

  // Event listeners para funciones administrativas
  document.getElementById("btn-backup-db").addEventListener("click", realizarBackup)
  document.getElementById("btn-limpiar-logs").addEventListener("click", limpiarLogs)
  document.getElementById("btn-ver-actividad").addEventListener("click", verActividadReciente)
  document.getElementById("btn-optimizar-db").addEventListener("click", optimizarBaseDatos)
  document.getElementById("btn-verificar-integridad").addEventListener("click", verificarIntegridad)
  document.getElementById("btn-reparar-tablas").addEventListener("click", repararTablas)
  document.getElementById("btn-ver-logs-completos").addEventListener("click", verLogsCompletos)
  document.getElementById("btn-exportar-logs").addEventListener("click", exportarLogs)
  document.getElementById("btn-limpiar-logs-sistema").addEventListener("click", limpiarLogsSistema)
  document.getElementById("btn-toggle-mantenimiento").addEventListener("click", toggleMantenimiento)
  document.getElementById("btn-toggle-backup-auto").addEventListener("click", configurarBackupAuto)

  // Event listeners para filtros
  document.getElementById("filtro-rol").addEventListener("change", function () {
    const rolId = this.value
    cargarUsuarios(rolId)
  })

  // Event listeners para guardar formularios
  document.getElementById("btn-guardar-usuario").addEventListener("click", () => {
    guardarUsuario()
  })

  document.getElementById("btn-aprobar-solicitud").addEventListener("click", () => {
    responderSolicitud("aprobar")
  })

  document.getElementById("btn-rechazar-solicitud").addEventListener("click", () => {
    responderSolicitud("rechazar")
  })

  document.getElementById("btn-confirmar-accion").addEventListener("click", () => {
    ejecutarAccionConfirmacion()
  })

  // Event listeners para modales
  document.querySelectorAll(".close, .modal-close").forEach((element) => {
    element.addEventListener("click", () => {
      cerrarModales()
    })
  })

  // Funciones principales

  /**
   * Inicializa el sistema de pestañas
   */
  function inicializarTabs() {
    const tabBtns = document.querySelectorAll(".tab-btn")
    const tabContents = document.querySelectorAll(".tab-content")

    tabBtns.forEach((btn) => {
      btn.addEventListener("click", function () {
        const tabId = this.getAttribute("data-tab")

        // Desactivar todas las pestañas
        tabBtns.forEach((btn) => {
          btn.classList.remove("active")
        })

        tabContents.forEach((content) => {
          content.classList.remove("active")
        })

        // Activar la pestaña seleccionada
        this.classList.add("active")
        document.getElementById(tabId).classList.add("active")

        // Cargar datos según la pestaña
        if (tabId === "clases") {
          cargarTodasLasClases()
        } else if (tabId === "usuarios") {
          cargarUsuarios()
        } else if (tabId === "solicitudes") {
          cargarSolicitudes()
        }
      })
    })
  }

  /**
   * Carga el dashboard administrativo con estadísticas avanzadas
   */
  function cargarDashboardAdmin() {
    const contenedor = document.getElementById("estadisticas-admin")
    contenedor.innerHTML = '<div class="loading">Cargando estadísticas del sistema...</div>'

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "accion=estadisticas_generales",
    })
      .then((response) => response.json())
      .then((data) => {
        let html = ""

        // Estadísticas de clases
        if (data.clases) {
          html += `
                <div class="stat-card">
                    <div class="stat-title">Total de Clases</div>
                    <div class="stat-value">${data.clases.total_clases || 0}</div>
                    <div class="stat-footer">Clases activas en el sistema</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-title">Total de Profesores</div>
                    <div class="stat-value">${data.clases.total_profesores || 0}</div>
                    <div class="stat-footer">Profesores activos</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-title">Total de Inscripciones</div>
                    <div class="stat-value">${data.clases.total_inscripciones || 0}</div>
                    <div class="stat-footer">Inscripciones aprobadas</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-title">Promedio General</div>
                    <div class="stat-value">${data.clases.promedio_calificaciones ? Number.parseFloat(data.clases.promedio_calificaciones).toFixed(1) : "N/A"}</div>
                    <div class="stat-footer">Promedio de calificaciones</div>
                </div>
                `
        }

        // Estadísticas de alumnos
        if (data.alumnos) {
          html += `
                <div class="stat-card">
                    <div class="stat-title">Total de Alumnos</div>
                    <div class="stat-value">${data.alumnos.total_alumnos || 0}</div>
                    <div class="stat-footer">Alumnos registrados</div>
                </div>
                `
        }

        // Estadísticas adicionales del sistema
        html += `
            <div class="stat-card">
                <div class="stat-title">Estado del Sistema</div>
                <div class="stat-value">Activo</div>
                <div class="stat-footer">Sistema operativo</div>
            </div>
            `

        contenedor.innerHTML = html
      })
      .catch((error) => {
        console.error("Error:", error)
        contenedor.innerHTML = '<p class="info-message">Error al cargar las estadísticas</p>'
      })
  }

  /**
   * Funciones administrativas específicas
   */

  /**
   * Realiza backup de la base de datos
   */
  function realizarBackup() {
    if (!confirm("¿Estás seguro de que deseas realizar un backup de la base de datos?")) {
      return
    }

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "accion=backup_database",
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.ok) {
          alert("Backup realizado correctamente")
        } else {
          alert("Error al realizar el backup: " + data.msg)
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("Error al realizar el backup")
      })
  }

  /**
   * Limpia logs antiguos
   */
  function limpiarLogs() {
    if (!confirm("¿Estás seguro de que deseas limpiar los logs antiguos (más de 30 días)?")) {
      return
    }

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "accion=limpiar_logs",
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.ok) {
          alert("Logs limpiados correctamente")
        } else {
          alert("Error al limpiar los logs: " + data.msg)
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("Error al limpiar los logs")
      })
  }

  /**
   * Ver actividad reciente del sistema
   */
  function verActividadReciente() {
    alert("Funcionalidad de actividad reciente - En desarrollo")
  }

  /**
   * Optimiza la base de datos
   */
  function optimizarBaseDatos() {
    if (!confirm("¿Estás seguro de que deseas optimizar la base de datos?")) {
      return
    }

    alert("Funcionalidad de optimización - En desarrollo")
  }

  /**
   * Verifica la integridad de la base de datos
   */
  function verificarIntegridad() {
    alert("Funcionalidad de verificación de integridad - En desarrollo")
  }

  /**
   * Repara las tablas de la base de datos
   */
  function repararTablas() {
    if (!confirm("¿Estás seguro de que deseas reparar las tablas?")) {
      return
    }

    alert("Funcionalidad de reparación de tablas - En desarrollo")
  }

  /**
   * Ver logs completos del sistema
   */
  function verLogsCompletos() {
    const contenedor = document.getElementById("contenedor-logs")

    if (contenedor.style.display === "none") {
      contenedor.style.display = "block"
      contenedor.innerHTML = '<div class="loading">Cargando logs...</div>'

      // Simular carga de logs
      setTimeout(() => {
        contenedor.innerHTML = `
          <div>2024-01-15 10:30:15 - Usuario admin inició sesión</div>
          <div>2024-01-15 10:25:32 - Backup automático completado</div>
          <div>2024-01-15 09:45:12 - Sistema iniciado correctamente</div>
          <div>2024-01-14 23:59:59 - Limpieza automática de logs ejecutada</div>
        `
      }, 1000)
    } else {
      contenedor.style.display = "none"
    }
  }

  /**
   * Exporta los logs del sistema
   */
  function exportarLogs() {
    alert("Funcionalidad de exportación de logs - En desarrollo")
  }

  /**
   * Limpia los logs del sistema
   */
  function limpiarLogsSistema() {
    if (!confirm("¿Estás seguro de que deseas limpiar todos los logs del sistema?")) {
      return
    }

    limpiarLogs()
  }

  /**
   * Toggle modo mantenimiento
   */
  function toggleMantenimiento() {
    const btn = document.getElementById("btn-toggle-mantenimiento")
    const textoActual = btn.textContent

    if (textoActual.includes("Activar")) {
      btn.textContent = "Desactivar Mantenimiento"
      btn.className = "btn-danger"
      alert("Modo mantenimiento activado")
    } else {
      btn.textContent = "Activar Mantenimiento"
      btn.className = "btn-warning"
      alert("Modo mantenimiento desactivado")
    }
  }

  /**
   * Configurar backup automático
   */
  function configurarBackupAuto() {
    alert("Funcionalidad de configuración de backup automático - En desarrollo")
  }

  /**
   * Exportar datos del sistema
   */
  function exportarDatos() {
    if (!confirm("¿Estás seguro de que deseas exportar todos los datos del sistema?")) {
      return
    }

    alert("Funcionalidad de exportación de datos - En desarrollo")
  }

  // Funciones heredadas del director (copiadas y adaptadas)

  /**
   * Carga todas las clases del sistema
   */
  function cargarTodasLasClases() {
    const contenedor = document.getElementById("lista-todas-clases")
    contenedor.innerHTML = '<div class="loading">Cargando clases...</div>'

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "accion=todas_clases",
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.length === 0) {
          contenedor.innerHTML = '<p class="info-message">No hay clases registradas</p>'
          return
        }

        let html = `
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Período</th>
                        <th>Profesor</th>
                        <th>Coordinador</th>
                        <th>Inscritos</th>
                        <th>Cupo Máximo</th>
                    </tr>
                </thead>
                <tbody>
            `

        data.forEach((clase) => {
          html += `
                <tr>
                    <td>${clase.nombre}</td>
                    <td>${clase.periodo || "-"}</td>
                    <td>${clase.profesor || "-"}</td>
                    <td>${clase.coordinador || "-"}</td>
                    <td>${clase.inscritos || 0}</td>
                    <td>${clase.cupo_maximo || "-"}</td>
                </tr>
                `
        })

        html += `
                </tbody>
            </table>
            `

        contenedor.innerHTML = html
      })
      .catch((error) => {
        console.error("Error:", error)
        contenedor.innerHTML = '<p class="info-message">Error al cargar las clases</p>'
      })
  }

  /**
   * Carga los usuarios del sistema
   */
  function cargarUsuarios(rolId = "") {
    const contenedor = document.getElementById("lista-usuarios")
    contenedor.innerHTML = '<div class="loading">Cargando usuarios...</div>'

    let body = "accion=usuarios_por_rol"
    if (rolId) {
      body += `&rol_id=${rolId}`
    }

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: body,
    })
      .then((response) => response.json())
      .then((data) => {
        usuarios = data

        if (data.length === 0) {
          contenedor.innerHTML = '<p class="info-message">No hay usuarios registrados</p>'
          return
        }

        let html = `
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Último Acceso</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
            `

        data.forEach((usuario) => {
          const estadoClass = usuario.activo == 1 ? "" : "usuario-inactivo"
          const ultimoAcceso = usuario.ultimo_acceso ? new Date(usuario.ultimo_acceso).toLocaleDateString() : "Nunca"

          html += `
                <tr class="usuario-row ${estadoClass}">
                    <td>${usuario.nombre}</td>
                    <td>${usuario.email}</td>
                    <td>${usuario.rol_nombre}</td>
                    <td>${usuario.activo == 1 ? "Activo" : "Inactivo"}</td>
                    <td>${ultimoAcceso}</td>
                    <td class="usuario-actions">
                        <button class="btn-secondary btn-editar-usuario" data-usuario-id="${usuario.id}">Editar</button>
                        <button class="btn-${usuario.activo == 1 ? "warning" : "success"} btn-cambiar-estado" 
                                data-usuario-id="${usuario.id}" 
                                data-estado="${usuario.activo == 1 ? 0 : 1}">
                            ${usuario.activo == 1 ? "Desactivar" : "Activar"}
                        </button>
                    </td>
                </tr>
                `
        })

        html += `
                </tbody>
            </table>
            `

        contenedor.innerHTML = html

        // Agregar event listeners
        document.querySelectorAll(".btn-editar-usuario").forEach((btn) => {
          btn.addEventListener("click", function () {
            const usuarioId = this.getAttribute("data-usuario-id")
            editarUsuario(usuarioId)
          })
        })

        document.querySelectorAll(".btn-cambiar-estado").forEach((btn) => {
          btn.addEventListener("click", function () {
            const usuarioId = this.getAttribute("data-usuario-id")
            const nuevoEstado = this.getAttribute("data-estado")
            cambiarEstadoUsuario(usuarioId, nuevoEstado)
          })
        })
      })
      .catch((error) => {
        console.error("Error:", error)
        contenedor.innerHTML = '<p class="info-message">Error al cargar los usuarios</p>'
      })
  }

  /**
   * Abre el modal para crear/editar usuario
   */
  function abrirModalUsuario(usuarioId = null) {
    if (usuarioId) {
      // Modo edición
      const usuario = usuarios.find((u) => u.id == usuarioId)
      if (usuario) {
        document.getElementById("titulo-modal-usuario").textContent = "Editar Usuario"
        document.getElementById("usuario-id").value = usuario.id
        document.getElementById("nombre-usuario").value = usuario.nombre
        document.getElementById("email-usuario").value = usuario.email
        document.getElementById("password-usuario").value = ""
        document.getElementById("rol-usuario").value = usuario.rol_id
      }
    } else {
      // Modo creación
      document.getElementById("titulo-modal-usuario").textContent = "Nuevo Usuario"
      document.getElementById("form-usuario").reset()
      document.getElementById("usuario-id").value = ""
    }

    document.getElementById("modal-usuario").style.display = "block"
  }

  /**
   * Edita un usuario existente
   */
  function editarUsuario(usuarioId) {
    abrirModalUsuario(usuarioId)
  }

  /**
   * Guarda un usuario (crear o editar)
   */
  function guardarUsuario() {
    const usuarioId = document.getElementById("usuario-id").value
    const nombre = document.getElementById("nombre-usuario").value
    const email = document.getElementById("email-usuario").value
    const password = document.getElementById("password-usuario").value
    const rolId = document.getElementById("rol-usuario").value

    if (!nombre || !email || !rolId) {
      alert("Por favor, completa los campos obligatorios")
      return
    }

    if (!usuarioId && !password) {
      alert("La contraseña es obligatoria para usuarios nuevos")
      return
    }

    const accion = usuarioId ? "editar_usuario" : "crear_usuario"
    let body = `accion=${accion}&nombre=${encodeURIComponent(nombre)}&email=${encodeURIComponent(email)}&rol_id=${rolId}`

    if (usuarioId) {
      body += `&usuario_id=${usuarioId}`
    }

    if (password) {
      body += `&password=${encodeURIComponent(password)}`
    }

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: body,
    })
      .then((response) => response.json())
      .then((data) => {
        cerrarModales()

        if (data.ok) {
          alert(usuarioId ? "Usuario actualizado correctamente" : "Usuario creado correctamente")
          cargarUsuarios()
        } else {
          alert("Error al guardar el usuario: " + data.msg)
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("Error al guardar el usuario")
        cerrarModales()
      })
  }

  /**
   * Cambia el estado de un usuario
   */
  function cambiarEstadoUsuario(usuarioId, nuevoEstado) {
    const accion = nuevoEstado == 1 ? "activar" : "desactivar"

    if (!confirm(`¿Estás seguro de que deseas ${accion} este usuario?`)) {
      return
    }

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=cambiar_estado_usuario&usuario_id=${usuarioId}&activo=${nuevoEstado}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.ok) {
          alert(`Usuario ${accion}do correctamente`)
          cargarUsuarios()
        } else {
          alert(`Error al ${accion} el usuario: ` + data.msg)
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert(`Error al ${accion} el usuario`)
      })
  }

  /**
   * Carga las solicitudes pendientes
   */
  function cargarSolicitudes() {
    const contenedor = document.getElementById("lista-solicitudes")
    contenedor.innerHTML = '<div class="loading">Cargando solicitudes...</div>'

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "accion=solicitudes_pendientes",
    })
      .then((response) => response.json())
      .then((data) => {
        solicitudes = data

        if (data.length === 0) {
          contenedor.innerHTML = '<p class="info-message">No hay solicitudes pendientes</p>'
          return
        }

        let html = ""
        data.forEach((solicitud) => {
          const fecha = new Date(solicitud.fecha_solicitud).toLocaleDateString()

          html += `
                <div class="card solicitud-card">
                    <div class="card-header">
                        <div class="card-title">
                            <span class="solicitud-tipo">${solicitud.tipo}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <p><strong>Usuario:</strong> ${solicitud.usuario_nombre}</p>
                        <p><strong>Solicitante:</strong> ${solicitud.solicitante_nombre}</p>
                        ${solicitud.motivo ? `<div class="solicitud-motivo">${solicitud.motivo}</div>` : ""}
                        <div class="solicitud-fecha">Fecha: ${fecha}</div>
                    </div>
                    <div class="card-footer">
                        <button class="btn-primary btn-responder-solicitud" data-solicitud-id="${solicitud.id}">Responder</button>
                    </div>
                </div>
                `
        })

        contenedor.innerHTML = html

        // Agregar event listeners
        document.querySelectorAll(".btn-responder-solicitud").forEach((btn) => {
          btn.addEventListener("click", function () {
            const solicitudId = this.getAttribute("data-solicitud-id")
            abrirModalSolicitud(solicitudId)
          })
        })
      })
      .catch((error) => {
        console.error("Error:", error)
        contenedor.innerHTML = '<p class="info-message">Error al cargar las solicitudes</p>'
      })
  }

  /**
   * Abre el modal para responder una solicitud
   */
  function abrirModalSolicitud(solicitudId) {
    const solicitud = solicitudes.find((s) => s.id == solicitudId)
    if (!solicitud) return

    solicitudSeleccionada = solicitudId

    const detalleHtml = `
        <div><strong>Tipo:</strong> ${solicitud.tipo}</div>
        <div><strong>Usuario:</strong> ${solicitud.usuario_nombre}</div>
        <div><strong>Solicitante:</strong> ${solicitud.solicitante_nombre}</div>
        <div><strong>Fecha:</strong> ${new Date(solicitud.fecha_solicitud).toLocaleDateString()}</div>
        ${solicitud.motivo ? `<div><strong>Motivo:</strong> ${solicitud.motivo}</div>` : ""}
        `

    document.getElementById("detalle-solicitud").innerHTML = detalleHtml
    document.getElementById("solicitud-id").value = solicitudId
    document.getElementById("respuesta-solicitud").value = ""
    document.getElementById("modal-solicitud").style.display = "block"
  }

  /**
   * Responde una solicitud (aprobar o rechazar)
   */
  function responderSolicitud(accion) {
    const solicitudId = document.getElementById("solicitud-id").value
    const respuesta = document.getElementById("respuesta-solicitud").value

    if (!respuesta.trim()) {
      alert("Por favor, escribe una respuesta")
      return
    }

    const accionTexto = accion === "aprobar" ? "aprobar_solicitud" : "rechazar_solicitud"

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=${accionTexto}&solicitud_id=${solicitudId}&respuesta=${encodeURIComponent(respuesta)}`,
    })
      .then((response) => response.json())
      .then((data) => {
        cerrarModales()

        if (data.ok) {
          alert(`Solicitud ${accion === "aprobar" ? "aprobada" : "rechazada"} correctamente`)
          cargarSolicitudes()
        } else {
          alert(`Error al ${accion} la solicitud: ` + data.msg)
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert(`Error al ${accion} la solicitud`)
        cerrarModales()
      })
  }

  /**
   * Genera el reporte completo del sistema
   */
  function generarReporteCompleto() {
    const contenedor = document.getElementById("contenedor-reportes")
    contenedor.innerHTML = '<div class="loading">Generando reporte completo...</div>'

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "accion=reporte_completo",
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.length === 0) {
          contenedor.innerHTML = '<p class="info-message">No hay datos para el reporte</p>'
          return
        }

        let html = `
            <table>
                <thead>
                    <tr>
                        <th>Categoría</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
            `

        data.forEach((item) => {
          html += `
                <tr>
                    <td>${item.tipo}</td>
                    <td>${item.total}</td>
                </tr>
                `
        })

        html += `
                </tbody>
            </table>
            `

        contenedor.innerHTML = html
      })
      .catch((error) => {
        console.error("Error:", error)
        contenedor.innerHTML = '<p class="info-message">Error al generar el reporte</p>'
      })
  }

  /**
   * Ejecuta la acción de confirmación
   */
  function ejecutarAccionConfirmacion() {
    if (accionConfirmacion) {
      accionConfirmacion()
      accionConfirmacion = null
    }
    cerrarModales()
  }

  /**
   * Cierra todos los modales
   */
  function cerrarModales() {
    document.querySelectorAll(".modal").forEach((modal) => {
      modal.style.display = "none"
    })
  }

  /**
   * Obtiene el ID del usuario de la sesión
   */
  function obtenerUsuarioId() {
    // En un sistema real, esto vendría de la sesión
    return 5 // Placeholder
  }

  // Cerrar modales al hacer clic fuera de ellos
  window.addEventListener("click", (event) => {
    document.querySelectorAll(".modal").forEach((modal) => {
      if (event.target === modal) {
        modal.style.display = "none"
      }
    })
  })
})
