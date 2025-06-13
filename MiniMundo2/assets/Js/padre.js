/**
 * Script para el panel de padre
 */
document.addEventListener("DOMContentLoaded", () => {
  // Variables globales
  const usuario_id = obtenerUsuarioId()
  let hijos = []
  let profesores = []
  let profesorSeleccionado = null
  let avisoSeleccionado = null
  let accionConfirmacion = null // Declare the variable here

  // Inicialización
  inicializarTabs()
  cargarHijos()

  // Event listeners para botones principales
  document.getElementById("btn-vincular-hijo").addEventListener("click", () => {
    document.getElementById("modal-vincular-hijo").style.display = "block"
  })

  document.getElementById("btn-actualizar-hijos").addEventListener("click", cargarHijos)
  document.getElementById("btn-actualizar-avisos").addEventListener("click", cargarAvisos)

  // Event listeners para selectores
  document.getElementById("select-hijo-calificaciones").addEventListener("change", function () {
    const hijoId = this.value
    if (hijoId) {
      cargarCalificacionesHijo(hijoId)
    } else {
      document.getElementById("contenedor-calificaciones").innerHTML =
        '<p class="info-message">Selecciona un hijo para ver sus calificaciones</p>'
    }
  })

  document.getElementById("select-hijo-reportes").addEventListener("change", function () {
    const hijoId = this.value
    if (hijoId) {
      cargarReportesHijo(hijoId)
    } else {
      document.getElementById("contenedor-reportes").innerHTML =
        '<p class="info-message">Selecciona un hijo para ver sus reportes</p>'
    }
  })

  document.getElementById("select-profesor-mensajes").addEventListener("change", function () {
    const profesorId = this.value
    if (profesorId) {
      profesorSeleccionado = profesorId
      cargarMensajesProfesor(profesorId)
      document.getElementById("form-mensaje").style.display = "block"
    } else {
      document.getElementById("lista-mensajes").innerHTML =
        '<p class="info-message">Selecciona un profesor para ver los mensajes</p>'
      document.getElementById("form-mensaje").style.display = "none"
      profesorSeleccionado = null
    }
  })

  // Event listeners para botones de acción
  document.getElementById("btn-enviar-mensaje").addEventListener("click", () => {
    enviarMensajeProfesor()
  })

  document.getElementById("btn-confirmar-vinculo").addEventListener("click", () => {
    vincularHijo()
  })

  document.getElementById("btn-enviar-comentario").addEventListener("click", () => {
    enviarComentarioAviso()
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
        if (tabId === "avisos") {
          cargarAvisos()
        } else if (tabId === "mensajes") {
          cargarProfesores()
        }
      })
    })
  }

  /**
   * Carga los hijos del padre
   */
  function cargarHijos() {
    const contenedor = document.getElementById("lista-hijos")
    contenedor.innerHTML = '<div class="loading">Cargando información de hijos...</div>'

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "accion=mis_hijos",
    })
      .then((response) => response.json())
      .then((data) => {
        hijos = data

        if (data.length === 0) {
          contenedor.innerHTML =
            '<p class="info-message">No tienes hijos vinculados. Haz clic en "Vincular Hijo" para agregar uno.</p>'
          return
        }

        // Actualizar selectores
        actualizarSelectoresHijos(data)

        // Mostrar hijos en cards
        let html = ""
        data.forEach((hijo) => {
          html += `
                <div class="card hijo-card">
                    <div class="card-header">
                        <div class="hijo-nombre">${hijo.alumno_nombre}</div>
                        <span class="hijo-parentesco">${hijo.parentesco}</span>
                    </div>
                    <div class="card-body">
                        <div class="hijo-email">${hijo.alumno_email}</div>
                    </div>
                    <div class="card-footer">
                        <button class="btn-secondary ver-calificaciones" data-hijo-id="${hijo.alumno_id}">Ver Calificaciones</button>
                        <button class="btn-danger desvincular-hijo" data-vinculo-id="${hijo.id}">Desvincular</button>
                    </div>
                </div>
                `
        })

        contenedor.innerHTML = html

        // Agregar event listeners a los botones
        document.querySelectorAll(".ver-calificaciones").forEach((btn) => {
          btn.addEventListener("click", function () {
            const hijoId = this.getAttribute("data-hijo-id")
            document.getElementById("select-hijo-calificaciones").value = hijoId
            document.querySelector('.tab-btn[data-tab="calificaciones"]').click()
            cargarCalificacionesHijo(hijoId)
          })
        })

        document.querySelectorAll(".desvincular-hijo").forEach((btn) => {
          btn.addEventListener("click", function () {
            const vinculoId = this.getAttribute("data-vinculo-id")
            desvincularHijo(vinculoId)
          })
        })
      })
      .catch((error) => {
        console.error("Error:", error)
        contenedor.innerHTML = '<p class="info-message">Error al cargar la información de los hijos</p>'
      })
  }

  /**
   * Actualiza los selectores de hijos
   */
  function actualizarSelectoresHijos(hijos) {
    const selectores = ["select-hijo-calificaciones", "select-hijo-reportes"]

    selectores.forEach((selectorId) => {
      const selector = document.getElementById(selectorId)
      selector.innerHTML = '<option value="">Selecciona un hijo</option>'

      hijos.forEach((hijo) => {
        const option = document.createElement("option")
        option.value = hijo.alumno_id
        option.textContent = hijo.alumno_nombre
        selector.appendChild(option)
      })
    })
  }

  /**
   * Vincula un nuevo hijo
   */
  function vincularHijo() {
    const alumnoId = document.getElementById("alumno-id").value
    const parentesco = document.getElementById("parentesco").value

    if (!alumnoId || !parentesco) {
      alert("Por favor, completa todos los campos")
      return
    }

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=vincular_hijo&alumno_id=${alumnoId}&parentesco=${encodeURIComponent(parentesco)}`,
    })
      .then((response) => response.json())
      .then((data) => {
        cerrarModales()

        if (data.ok) {
          alert("Hijo vinculado correctamente")
          cargarHijos()
          document.getElementById("form-vincular-hijo").reset()
        } else {
          alert("Error al vincular el hijo: " + data.msg)
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("Error al vincular el hijo")
        cerrarModales()
      })
  }

  /**
   * Desvincula un hijo
   */
  function desvincularHijo(vinculoId) {
    if (!confirm("¿Estás seguro de que deseas desvincular este hijo?")) {
      return
    }

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=desvincular_hijo&vinculo_id=${vinculoId}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.ok) {
          alert("Hijo desvinculado correctamente")
          cargarHijos()
        } else {
          alert("Error al desvincular el hijo: " + data.msg)
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("Error al desvincular el hijo")
      })
  }

  /**
   * Carga las calificaciones de un hijo
   */
  function cargarCalificacionesHijo(hijoId) {
    const contenedor = document.getElementById("contenedor-calificaciones")
    contenedor.innerHTML = '<div class="loading">Cargando calificaciones...</div>'

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=calificaciones_hijo&alumno_id=${hijoId}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.length === 0) {
          contenedor.innerHTML = '<p class="info-message">No hay calificaciones registradas para este hijo</p>'
          return
        }

        let html = `
            <table>
                <thead>
                    <tr>
                        <th>Clase</th>
                        <th>Profesor</th>
                        <th>Calificación</th>
                        <th>Observación</th>
                    </tr>
                </thead>
                <tbody>
            `

        data.forEach((calificacion) => {
          const calificacionClass = calificacion.calificacion >= 70 ? "calificacion-aprobada" : "calificacion-reprobada"
          html += `
                <tr>
                    <td>${calificacion.clase_nombre}</td>
                    <td>${calificacion.profesor_nombre}</td>
                    <td><span class="calificacion-valor ${calificacionClass}">${calificacion.calificacion}</span></td>
                    <td>${calificacion.observacion || "-"}</td>
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
        contenedor.innerHTML = '<p class="info-message">Error al cargar las calificaciones</p>'
      })
  }

  /**
   * Carga los reportes de un hijo
   */
  function cargarReportesHijo(hijoId) {
    const contenedor = document.getElementById("contenedor-reportes")
    contenedor.innerHTML = '<div class="loading">Cargando reportes...</div>'

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=reportes_hijo&alumno_id=${hijoId}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.length === 0) {
          contenedor.innerHTML = '<p class="info-message">No hay reportes para este hijo</p>'
          return
        }

        let html = ""
        data.forEach((reporte) => {
          const fecha = new Date(reporte.fecha).toLocaleDateString()

          html += `
                <div class="card reporte-card">
                    <div class="card-header">
                        <div class="card-title">${reporte.titulo}</div>
                        <div class="aviso-clase">${reporte.clase_nombre}</div>
                    </div>
                    <div class="card-body">
                        <p>${reporte.descripcion}</p>
                        <div class="reporte-fecha">
                            <span>Profesor: ${reporte.profesor_nombre}</span> - 
                            <span>Fecha: ${fecha}</span>
                        </div>
                    </div>
                </div>
                `
        })

        contenedor.innerHTML = html
      })
      .catch((error) => {
        console.error("Error:", error)
        contenedor.innerHTML = '<p class="info-message">Error al cargar los reportes</p>'
      })
  }

  /**
   * Carga los avisos para los hijos
   */
  function cargarAvisos() {
    const contenedor = document.getElementById("lista-avisos")
    contenedor.innerHTML = '<div class="loading">Cargando avisos...</div>'

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "accion=avisos_hijos",
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.length === 0) {
          contenedor.innerHTML = '<p class="info-message">No hay avisos para mostrar</p>'
          return
        }

        let html = ""
        data.forEach((aviso) => {
          const fecha = new Date(aviso.fecha).toLocaleDateString()
          const claseUrgente = aviso.urgente == 1 ? "aviso-urgente" : "aviso-card"

          html += `
                <div class="card ${claseUrgente}">
                    <div class="card-header">
                        <div class="card-title">${aviso.titulo}</div>
                        <div class="aviso-clase">${aviso.clase}</div>
                    </div>
                    <div class="card-body">
                        <p>${aviso.mensaje}</p>
                        <div class="aviso-fecha">
                            <span class="aviso-profesor">Profesor: ${aviso.profesor}</span> - 
                            <span>Fecha: ${fecha}</span>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn-secondary comentar-aviso" data-aviso-id="${aviso.id}">Comentar</button>
                    </div>
                </div>
                `
        })

        contenedor.innerHTML = html

        // Agregar event listeners
        document.querySelectorAll(".comentar-aviso").forEach((btn) => {
          btn.addEventListener("click", function () {
            const avisoId = this.getAttribute("data-aviso-id")
            abrirModalComentarioAviso(avisoId)
          })
        })
      })
      .catch((error) => {
        console.error("Error:", error)
        contenedor.innerHTML = '<p class="info-message">Error al cargar los avisos</p>'
      })
  }

  /**
   * Abre el modal para comentar un aviso
   */
  function abrirModalComentarioAviso(avisoId) {
    avisoSeleccionado = avisoId

    // Buscar el aviso en los datos cargados
    const avisoCard = document.querySelector(`[data-aviso-id="${avisoId}"]`).closest(".card")
    const titulo = avisoCard.querySelector(".card-title").textContent
    const mensaje = avisoCard.querySelector(".card-body p").textContent

    document.getElementById("detalle-aviso").innerHTML = `
        <div><strong>Título:</strong> ${titulo}</div>
        <div><strong>Mensaje:</strong> ${mensaje}</div>
        `

    document.getElementById("aviso-id").value = avisoId
    document.getElementById("mensaje-comentario").value = ""

    // Cargar comentarios existentes
    cargarComentariosAviso(avisoId)

    document.getElementById("modal-comentar-aviso").style.display = "block"
  }

  /**
   * Carga los comentarios de un aviso
   */
  function cargarComentariosAviso(avisoId) {
    const contenedor = document.getElementById("lista-comentarios")
    contenedor.innerHTML = '<div class="loading">Cargando comentarios...</div>'

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=comentarios_aviso&aviso_id=${avisoId}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.length === 0) {
          contenedor.innerHTML = '<p class="info-message">No hay comentarios anteriores</p>'
          return
        }

        let html = ""
        data.forEach((comentario) => {
          const fecha = new Date(comentario.fecha).toLocaleString()

          html += `
                <div class="comment">
                    <div class="comment-header">
                        <span class="comment-author">${comentario.padre_nombre}</span>
                        <span class="comment-time">${fecha}</span>
                    </div>
                    <div class="comment-content">${comentario.mensaje}</div>
                </div>
                `
        })

        contenedor.innerHTML = html
      })
      .catch((error) => {
        console.error("Error:", error)
        contenedor.innerHTML = '<p class="info-message">Error al cargar los comentarios</p>'
      })
  }

  /**
   * Envía un comentario a un aviso
   */
  function enviarComentarioAviso() {
    const avisoId = document.getElementById("aviso-id").value
    const mensaje = document.getElementById("mensaje-comentario").value.trim()

    if (!mensaje) {
      alert("Por favor, escribe un comentario")
      return
    }

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=comentar_aviso&aviso_id=${avisoId}&mensaje=${encodeURIComponent(mensaje)}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.ok) {
          alert("Comentario enviado correctamente")
          document.getElementById("mensaje-comentario").value = ""
          cargarComentariosAviso(avisoId)
        } else {
          alert("Error al enviar el comentario: " + data.msg)
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("Error al enviar el comentario")
      })
  }

  /**
   * Carga los profesores de los hijos
   */
  function cargarProfesores() {
    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "accion=profesores_hijos",
    })
      .then((response) => response.json())
      .then((data) => {
        profesores = data
        const selector = document.getElementById("select-profesor-mensajes")
        selector.innerHTML = '<option value="">Selecciona un profesor</option>'

        data.forEach((profesor) => {
          const option = document.createElement("option")
          option.value = profesor.id
          option.textContent = profesor.nombre
          selector.appendChild(option)
        })
      })
      .catch((error) => {
        console.error("Error:", error)
      })
  }

  /**
   * Carga los mensajes con un profesor
   */
  function cargarMensajesProfesor(profesorId) {
    const contenedor = document.getElementById("lista-mensajes")
    contenedor.innerHTML = '<div class="loading">Cargando mensajes...</div>'

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=mensajes_profesor&profesor_id=${profesorId}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.length === 0) {
          contenedor.innerHTML = '<p class="info-message">No hay mensajes con este profesor</p>'
          return
        }

        let html = ""
        data.forEach((mensaje) => {
          const fecha = new Date(mensaje.fecha).toLocaleString()
          const claseMessage = mensaje.de_quien === "padre" ? "message-sent" : "message-received"

          html += `
                <div class="message ${claseMessage}">
                    <div class="message-header">
                        <span class="message-sender">${mensaje.de_quien === "padre" ? "Tú" : "Profesor"}</span>
                        <span class="message-time">${fecha}</span>
                    </div>
                    <div class="message-content">${mensaje.mensaje}</div>
                </div>
                `
        })

        contenedor.innerHTML = html
        contenedor.scrollTop = contenedor.scrollHeight
      })
      .catch((error) => {
        console.error("Error:", error)
        contenedor.innerHTML = '<p class="info-message">Error al cargar los mensajes</p>'
      })
  }

  /**
   * Envía un mensaje a un profesor
   */
  function enviarMensajeProfesor() {
    const mensaje = document.getElementById("texto-mensaje").value.trim()

    if (!mensaje) {
      alert("Por favor, escribe un mensaje")
      return
    }

    if (!profesorSeleccionado) {
      alert("Por favor, selecciona un profesor")
      return
    }

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=enviar_mensaje_profesor&profesor_id=${profesorSeleccionado}&mensaje=${encodeURIComponent(mensaje)}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.ok) {
          document.getElementById("texto-mensaje").value = ""
          cargarMensajesProfesor(profesorSeleccionado)
        } else {
          alert("Error al enviar el mensaje: " + data.msg)
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("Error al enviar el mensaje")
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
    return 6 // Placeholder
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
