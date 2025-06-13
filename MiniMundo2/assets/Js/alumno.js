/**
 * Script para el panel de alumno
 */
document.addEventListener("DOMContentLoaded", () => {
  // Variables globales
  const usuario_id = obtenerUsuarioId()
  let claseSeleccionadaInscripcion = null

  // Inicialización
  inicializarTabs()
  cargarClases()
  cargarPerfil()

  // Event listeners para botones de actualización
  document.getElementById("btn-actualizar-clases").addEventListener("click", cargarClases)
  document.getElementById("btn-actualizar-tareas").addEventListener("click", cargarTareas)
  document.getElementById("btn-actualizar-calificaciones").addEventListener("click", cargarCalificaciones)
  document.getElementById("btn-actualizar-avisos").addEventListener("click", cargarAvisos)
  document.getElementById("btn-actualizar-inscripciones").addEventListener("click", () => {
    cargarClasesDisponibles()
    cargarEstadoInscripciones()
  })

  // Event listener para el formulario de perfil
  document.getElementById("form-perfil").addEventListener("submit", (e) => {
    e.preventDefault()
    actualizarPerfil()
  })

  // Event listeners para modales
  document.querySelectorAll(".close, .modal-close").forEach((element) => {
    element.addEventListener("click", () => {
      cerrarModales()
    })
  })

  document.getElementById("btn-confirmar-inscripcion").addEventListener("click", () => {
    inscribirseClase()
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
        if (tabId === "tareas" && document.getElementById("lista-tareas").innerHTML.includes("Cargando")) {
          cargarTareas()
        } else if (
          tabId === "calificaciones" &&
          document.getElementById("lista-calificaciones").innerHTML.includes("Cargando")
        ) {
          cargarCalificaciones()
        } else if (tabId === "avisos" && document.getElementById("lista-avisos").innerHTML.includes("Cargando")) {
          cargarAvisos()
        } else if (tabId === "inscripciones") {
          cargarClasesDisponibles()
          cargarEstadoInscripciones()
        }
      })
    })
  }

  /**
   * Carga las clases del alumno
   */
  function cargarClases() {
    const contenedor = document.getElementById("lista-clases")
    contenedor.innerHTML = '<div class="loading">Cargando clases...</div>'

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "accion=mis_clases",
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.length === 0) {
          contenedor.innerHTML = '<p class="info-message">No estás inscrito en ninguna clase</p>'
          return
        }

        let html = ""
        data.forEach((clase) => {
          html += `
                <div class="card clase-card">
                    <div class="card-header">
                        <div class="card-title">
                            ${clase.nombre}
                            <span class="clase-periodo">${clase.periodo}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="clase-profesor">Profesor: ${clase.profesor}</div>
                    </div>
                    <div class="card-footer">
                        <button class="btn-secondary ver-tareas" data-clase-id="${clase.id}">Ver Tareas</button>
                    </div>
                </div>
                `
        })

        contenedor.innerHTML = html

        // Agregar event listeners a los botones
        document.querySelectorAll(".ver-tareas").forEach((btn) => {
          btn.addEventListener("click", function () {
            const claseId = this.getAttribute("data-clase-id")
            // Cambiar a la pestaña de tareas
            document.querySelector('.tab-btn[data-tab="tareas"]').click()
            // Aquí se podría filtrar las tareas por clase
          })
        })
      })
      .catch((error) => {
        console.error("Error:", error)
        contenedor.innerHTML = '<p class="info-message">Error al cargar las clases</p>'
      })
  }

  /**
   * Carga las tareas del alumno
   */
  function cargarTareas() {
    const contenedor = document.getElementById("lista-tareas")
    contenedor.innerHTML = '<div class="loading">Cargando tareas...</div>'

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "accion=tareas",
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.length === 0) {
          contenedor.innerHTML = '<p class="info-message">No tienes tareas asignadas</p>'
          return
        }

        let html = ""
        data.forEach((tarea) => {
          const fechaEntrega = new Date(tarea.fecha_entrega).toLocaleDateString()
          html += `
                <div class="card tarea-card">
                    <div class="card-header">
                        <div class="card-title">${tarea.titulo}</div>
                        <div class="clase-nombre">${tarea.clase}</div>
                    </div>
                    <div class="card-body">
                        <p>${tarea.descripcion}</p>
                        <div class="tarea-fecha">Fecha de entrega: ${fechaEntrega}</div>
                    </div>
                </div>
                `
        })

        contenedor.innerHTML = html
      })
      .catch((error) => {
        console.error("Error:", error)
        contenedor.innerHTML = '<p class="info-message">Error al cargar las tareas</p>'
      })
  }

  /**
   * Carga las calificaciones del alumno
   */
  function cargarCalificaciones() {
    const contenedor = document.getElementById("lista-calificaciones")
    contenedor.innerHTML = '<div class="loading">Cargando calificaciones...</div>'

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "accion=calificaciones",
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.length === 0) {
          contenedor.innerHTML = '<p class="info-message">No tienes calificaciones registradas</p>'
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
   * Carga los avisos para el alumno
   */
  function cargarAvisos() {
    const contenedor = document.getElementById("lista-avisos")
    contenedor.innerHTML = '<div class="loading">Cargando avisos...</div>'

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "accion=avisos",
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
                        <div class="aviso-clase">${aviso.clase_nombre}</div>
                    </div>
                    <div class="card-body">
                        <p>${aviso.mensaje}</p>
                        <div class="aviso-fecha">
                            <span>Profesor: ${aviso.profesor_nombre}</span> - 
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
        contenedor.innerHTML = '<p class="info-message">Error al cargar los avisos</p>'
      })
  }

  /**
   * Carga las clases disponibles para inscripción
   */
  function cargarClasesDisponibles() {
    const contenedor = document.getElementById("clases-disponibles")
    contenedor.innerHTML = '<div class="loading">Cargando clases disponibles...</div>'

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "accion=clases_disponibles",
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.length === 0) {
          contenedor.innerHTML = '<p class="info-message">No hay clases disponibles para inscripción</p>'
          return
        }

        let html = ""
        data.forEach((clase) => {
          html += `
                <div class="card clase-card">
                    <div class="card-header">
                        <div class="card-title">
                            ${clase.nombre}
                            <span class="clase-periodo">${clase.periodo}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="clase-profesor">Profesor: ${clase.profesor}</div>
                    </div>
                    <div class="card-footer">
                        <button class="btn-primary btn-inscribirse" data-clase-id="${clase.id}" data-clase-nombre="${clase.nombre}">Inscribirse</button>
                    </div>
                </div>
                `
        })

        contenedor.innerHTML = html

        // Agregar event listeners a los botones de inscripción
        document.querySelectorAll(".btn-inscribirse").forEach((btn) => {
          btn.addEventListener("click", function () {
            claseSeleccionadaInscripcion = this.getAttribute("data-clase-id")
            const nombreClase = this.getAttribute("data-clase-nombre")
            document.getElementById("nombre-clase-inscripcion").textContent = nombreClase
            document.getElementById("modal-inscripcion").style.display = "block"
          })
        })
      })
      .catch((error) => {
        console.error("Error:", error)
        contenedor.innerHTML = '<p class="info-message">Error al cargar las clases disponibles</p>'
      })
  }

  /**
   * Carga el estado de las inscripciones del alumno
   */
  function cargarEstadoInscripciones() {
    const contenedor = document.getElementById("estado-inscripciones")
    contenedor.innerHTML = '<div class="loading">Cargando estado de inscripciones...</div>'

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "accion=estado_inscripciones",
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.length === 0) {
          contenedor.innerHTML = '<p class="info-message">No tienes inscripciones registradas</p>'
          return
        }

        let html = `
            <table>
                <thead>
                    <tr>
                        <th>Clase</th>
                        <th>Periodo</th>
                        <th>Profesor</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
            `

        data.forEach((inscripcion) => {
          const rowClass = inscripcion.estado === "Pendiente" ? "inscripcion-pendiente" : "inscripcion-aprobada"
          html += `
                <tr class="${rowClass}">
                    <td>${inscripcion.clase}</td>
                    <td>${inscripcion.periodo}</td>
                    <td>${inscripcion.profesor}</td>
                    <td>${inscripcion.estado}</td>
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
        contenedor.innerHTML = '<p class="info-message">Error al cargar el estado de inscripciones</p>'
      })
  }

  /**
   * Carga los datos del perfil del alumno
   */
  function cargarPerfil() {
    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "accion=perfil",
    })
      .then((response) => response.json())
      .then((data) => {
        document.getElementById("nombre").value = data.nombre
        document.getElementById("email").value = data.email
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("Error al cargar los datos del perfil")
      })
  }

  /**
   * Actualiza los datos del perfil del alumno
   */
  function actualizarPerfil() {
    const nombre = document.getElementById("nombre").value
    const email = document.getElementById("email").value

    if (!nombre || !email) {
      alert("Por favor, completa todos los campos")
      return
    }

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=editar_perfil&nombre=${encodeURIComponent(nombre)}&email=${encodeURIComponent(email)}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.ok) {
          alert("Perfil actualizado correctamente")
        } else {
          alert("Error al actualizar el perfil: " + data.msg)
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("Error al actualizar el perfil")
      })
  }

  /**
   * Inscribe al alumno en una clase
   */
  function inscribirseClase() {
    if (!claseSeleccionadaInscripcion) return

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=inscribirse&clase_id=${claseSeleccionadaInscripcion}`,
    })
      .then((response) => response.json())
      .then((data) => {
        cerrarModales()

        if (data.ok) {
          alert("Solicitud de inscripción enviada correctamente. Espera la aprobación del coordinador.")
          cargarClasesDisponibles()
          cargarEstadoInscripciones()
        } else {
          alert("Error al inscribirse: " + data.msg)
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("Error al procesar la inscripción")
        cerrarModales()
      })
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
    return 1 // Placeholder
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
