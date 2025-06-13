/**
 * Script para el panel de coordinador
 */
document.addEventListener("DOMContentLoaded", () => {
  // Variables globales
  const usuario_id = obtenerUsuarioId()
  let clases = []
  let profesoresDisponibles = []
  let alumnosDisponibles = []
  const claseSeleccionada = null
  let accionConfirmacion = null

  // Inicialización
  inicializarTabs()
  cargarClases()
  cargarProfesoresDisponibles()
  cargarAlumnosDisponibles()

  // Event listeners para botones principales
  document.getElementById("btn-nueva-clase").addEventListener("click", () => {
    abrirModalClase()
  })

  document.getElementById("btn-actualizar-clases").addEventListener("click", cargarClases)
  document.getElementById("btn-actualizar-inscripciones").addEventListener("click", cargarInscripcionesPendientes)
  document.getElementById("btn-actualizar-estadisticas").addEventListener("click", cargarEstadisticas)

  // Event listeners para selectores
  document.getElementById("select-clase-alumnos").addEventListener("change", function () {
    const claseId = this.value
    if (claseId) {
      cargarAlumnosClase(claseId)
      document.querySelector(".alumnos-actions").style.display = "block"
    } else {
      document.getElementById("lista-alumnos-clase").innerHTML =
        '<p class="info-message">Selecciona una clase para ver los alumnos</p>'
      document.querySelector(".alumnos-actions").style.display = "none"
    }
  })

  document.getElementById("select-clase-reportes").addEventListener("change", function () {
    const claseId = this.value
    if (claseId) {
      document.getElementById("btn-generar-reporte").style.display = "inline-block"
    } else {
      document.getElementById("btn-generar-reporte").style.display = "none"
      document.getElementById("contenedor-reporte").innerHTML =
        '<p class="info-message">Selecciona una clase y genera el reporte</p>'
    }
  })

  // Event listeners para botones de acción
  document.getElementById("btn-agregar-alumno").addEventListener("click", () => {
    const claseId = document.getElementById("select-clase-alumnos").value
    if (claseId) {
      document.getElementById("clase-id-alumno").value = claseId
      document.getElementById("modal-agregar-alumno").style.display = "block"
    }
  })

  document.getElementById("btn-generar-reporte").addEventListener("click", () => {
    const claseId = document.getElementById("select-clase-reportes").value
    if (claseId) {
      generarReporteCalificaciones(claseId)
    }
  })

  // Event listeners para guardar formularios
  document.getElementById("btn-guardar-clase").addEventListener("click", () => {
    guardarClase()
  })

  document.getElementById("btn-confirmar-agregar-alumno").addEventListener("click", () => {
    agregarAlumnoClase()
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
        if (tabId === "inscripciones") {
          cargarInscripcionesPendientes()
        } else if (tabId === "estadisticas") {
          cargarEstadisticas()
        }
      })
    })
  }

  /**
   * Carga las clases del coordinador
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
        clases = data

        if (data.length === 0) {
          contenedor.innerHTML = '<p class="info-message">No tienes clases asignadas</p>'
          return
        }

        // Actualizar selectores
        actualizarSelectoresClases(data)

        // Mostrar clases en cards
        let html = ""
        data.forEach((clase) => {
          html += `
                <div class="card clase-card">
                    <div class="card-header">
                        <div class="card-title">
                            ${clase.nombre}
                            <span class="clase-periodo">${clase.periodo || ""}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="clase-profesor">Profesor: ${clase.profesor_nombre}</div>
                        <div class="clase-alumnos">Alumnos inscritos: ${clase.total_alumnos || 0}</div>
                    </div>
                    <div class="card-footer">
                        <button class="btn-secondary btn-editar-clase" data-clase-id="${clase.id}">Editar</button>
                        <button class="btn-danger btn-eliminar-clase" data-clase-id="${clase.id}">Eliminar</button>
                    </div>
                </div>
                `
        })

        contenedor.innerHTML = html

        // Agregar event listeners a los botones
        document.querySelectorAll(".btn-editar-clase").forEach((btn) => {
          btn.addEventListener("click", function () {
            const claseId = this.getAttribute("data-clase-id")
            editarClase(claseId)
          })
        })

        document.querySelectorAll(".btn-eliminar-clase").forEach((btn) => {
          btn.addEventListener("click", function () {
            const claseId = this.getAttribute("data-clase-id")
            confirmarEliminarClase(claseId)
          })
        })
      })
      .catch((error) => {
        console.error("Error:", error)
        contenedor.innerHTML = '<p class="info-message">Error al cargar las clases</p>'
      })
  }

  /**
   * Actualiza los selectores de clases
   */
  function actualizarSelectoresClases(clases) {
    const selectores = ["select-clase-alumnos", "select-clase-reportes"]

    selectores.forEach((selectorId) => {
      const selector = document.getElementById(selectorId)
      selector.innerHTML = '<option value="">Selecciona una clase</option>'

      clases.forEach((clase) => {
        const option = document.createElement("option")
        option.value = clase.id
        option.textContent = clase.nombre
        selector.appendChild(option)
      })
    })
  }

  /**
   * Carga los profesores disponibles
   */
  function cargarProfesoresDisponibles() {
    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "accion=profesores_disponibles",
    })
      .then((response) => response.json())
      .then((data) => {
        profesoresDisponibles = data
        actualizarSelectorProfesores(data)
      })
      .catch((error) => {
        console.error("Error:", error)
      })
  }

  /**
   * Actualiza el selector de profesores
   */
  function actualizarSelectorProfesores(profesores) {
    const selector = document.getElementById("profesor-clase")
    selector.innerHTML = '<option value="">Selecciona un profesor</option>'

    profesores.forEach((profesor) => {
      const option = document.createElement("option")
      option.value = profesor.id
      option.textContent = profesor.nombre
      selector.appendChild(option)
    })
  }

  /**
   * Carga los alumnos disponibles
   */
  function cargarAlumnosDisponibles() {
    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "accion=alumnos_disponibles",
    })
      .then((response) => response.json())
      .then((data) => {
        alumnosDisponibles = data
        actualizarSelectorAlumnos(data)
      })
      .catch((error) => {
        console.error("Error:", error)
      })
  }

  /**
   * Actualiza el selector de alumnos
   */
  function actualizarSelectorAlumnos(alumnos) {
    const selector = document.getElementById("select-alumno")
    selector.innerHTML = '<option value="">Selecciona un alumno</option>'

    alumnos.forEach((alumno) => {
      const option = document.createElement("option")
      option.value = alumno.id
      option.textContent = alumno.nombre
      selector.appendChild(option)
    })
  }

  /**
   * Abre el modal para crear/editar clase
   */
  function abrirModalClase(claseId = null) {
    if (claseId) {
      // Modo edición
      const clase = clases.find((c) => c.id == claseId)
      if (clase) {
        document.getElementById("titulo-modal-clase").textContent = "Editar Clase"
        document.getElementById("clase-id").value = clase.id
        document.getElementById("nombre-clase").value = clase.nombre
        document.getElementById("periodo-clase").value = clase.periodo || ""
        document.getElementById("profesor-clase").value = clase.profesor_id || ""
        document.getElementById("cupo-clase").value = clase.cupo_maximo || 30
        document.getElementById("descripcion-clase").value = clase.descripcion || ""
      }
    } else {
      // Modo creación
      document.getElementById("titulo-modal-clase").textContent = "Nueva Clase"
      document.getElementById("form-clase").reset()
      document.getElementById("clase-id").value = ""
    }

    document.getElementById("modal-clase").style.display = "block"
  }

  /**
   * Edita una clase existente
   */
  function editarClase(claseId) {
    abrirModalClase(claseId)
  }

  /**
   * Guarda una clase (crear o editar)
   */
  function guardarClase() {
    const claseId = document.getElementById("clase-id").value
    const nombre = document.getElementById("nombre-clase").value
    const periodo = document.getElementById("periodo-clase").value
    const profesorId = document.getElementById("profesor-clase").value
    const cupo = document.getElementById("cupo-clase").value
    const descripcion = document.getElementById("descripcion-clase").value

    if (!nombre || !periodo || !profesorId) {
      alert("Por favor, completa los campos obligatorios")
      return
    }

    const accion = claseId ? "editar_clase" : "crear_clase"
    let body = `accion=${accion}&nombre=${encodeURIComponent(nombre)}&periodo=${encodeURIComponent(periodo)}&profesor_id=${profesorId}&cupo_maximo=${cupo}&descripcion=${encodeURIComponent(descripcion)}`

    if (claseId) {
      body += `&clase_id=${claseId}`
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
          alert(claseId ? "Clase actualizada correctamente" : "Clase creada correctamente")
          cargarClases()
        } else {
          alert("Error al guardar la clase: " + data.msg)
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("Error al guardar la clase")
        cerrarModales()
      })
  }

  /**
   * Confirma la eliminación de una clase
   */
  function confirmarEliminarClase(claseId) {
    const clase = clases.find((c) => c.id == claseId)
    if (clase) {
      document.getElementById("mensaje-confirmacion").textContent =
        `¿Estás seguro de que deseas eliminar la clase "${clase.nombre}"?`
      accionConfirmacion = () => eliminarClase(claseId)
      document.getElementById("modal-confirmar").style.display = "block"
    }
  }

  /**
   * Elimina una clase
   */
  function eliminarClase(claseId) {
    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=eliminar_clase&clase_id=${claseId}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.ok) {
          alert("Clase eliminada correctamente")
          cargarClases()
        } else {
          alert("Error al eliminar la clase: " + data.msg)
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("Error al eliminar la clase")
      })
  }

  /**
   * Carga las inscripciones pendientes
   */
  function cargarInscripcionesPendientes() {
    const contenedor = document.getElementById("lista-inscripciones")
    contenedor.innerHTML = '<div class="loading">Cargando inscripciones pendientes...</div>'

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "accion=inscripciones_pendientes",
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.length === 0) {
          contenedor.innerHTML = '<p class="info-message">No hay inscripciones pendientes</p>'
          return
        }

        let html = `
            <table>
                <thead>
                    <tr>
                        <th>Alumno</th>
                        <th>Clase</th>
                        <th>Fecha de Solicitud</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
            `

        data.forEach((inscripcion) => {
          const fecha = new Date(inscripcion.fecha_inscripcion).toLocaleDateString()
          html += `
                <tr class="inscripcion-row">
                    <td>${inscripcion.alumno}</td>
                    <td>${inscripcion.clase}</td>
                    <td>${fecha}</td>
                    <td class="inscripcion-actions">
                        <button class="btn-success btn-aprobar-inscripcion" data-inscripcion-id="${inscripcion.id}">Aprobar</button>
                        <button class="btn-danger btn-rechazar-inscripcion" data-inscripcion-id="${inscripcion.id}">Rechazar</button>
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
        document.querySelectorAll(".btn-aprobar-inscripcion").forEach((btn) => {
          btn.addEventListener("click", function () {
            const inscripcionId = this.getAttribute("data-inscripcion-id")
            aprobarInscripcion(inscripcionId)
          })
        })

        document.querySelectorAll(".btn-rechazar-inscripcion").forEach((btn) => {
          btn.addEventListener("click", function () {
            const inscripcionId = this.getAttribute("data-inscripcion-id")
            rechazarInscripcion(inscripcionId)
          })
        })
      })
      .catch((error) => {
        console.error("Error:", error)
        contenedor.innerHTML = '<p class="info-message">Error al cargar las inscripciones</p>'
      })
  }

  /**
   * Aprueba una inscripción
   */
  function aprobarInscripcion(inscripcionId) {
    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=aprobar_inscripcion&inscripcion_id=${inscripcionId}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.ok) {
          alert("Inscripción aprobada correctamente")
          cargarInscripcionesPendientes()
        } else {
          alert("Error al aprobar la inscripción: " + data.msg)
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("Error al aprobar la inscripción")
      })
  }

  /**
   * Rechaza una inscripción
   */
  function rechazarInscripcion(inscripcionId) {
    if (!confirm("¿Estás seguro de que deseas rechazar esta inscripción?")) {
      return
    }

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=rechazar_inscripcion&inscripcion_id=${inscripcionId}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.ok) {
          alert("Inscripción rechazada correctamente")
          cargarInscripcionesPendientes()
        } else {
          alert("Error al rechazar la inscripción: " + data.msg)
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("Error al rechazar la inscripción")
      })
  }

  /**
   * Carga los alumnos de una clase
   */
  function cargarAlumnosClase(claseId) {
    const contenedor = document.getElementById("lista-alumnos-clase")
    contenedor.innerHTML = '<div class="loading">Cargando alumnos...</div>'

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=alumnos_clase&clase_id=${claseId}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.length === 0) {
          contenedor.innerHTML = '<p class="info-message">No hay alumnos en esta clase</p>'
          return
        }

        let html = `
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
            `

        data.forEach((alumno) => {
          html += `
                <tr>
                    <td>${alumno.nombre}</td>
                    <td>${alumno.email}</td>
                    <td>${alumno.estado}</td>
                    <td>
                        <button class="btn-danger btn-remover-alumno" 
                                data-alumno-id="${alumno.id}" 
                                data-clase-id="${claseId}">
                            Remover
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
        document.querySelectorAll(".btn-remover-alumno").forEach((btn) => {
          btn.addEventListener("click", function () {
            const alumnoId = this.getAttribute("data-alumno-id")
            const claseId = this.getAttribute("data-clase-id")
            removerAlumnoClase(alumnoId, claseId)
          })
        })
      })
      .catch((error) => {
        console.error("Error:", error)
        contenedor.innerHTML = '<p class="info-message">Error al cargar los alumnos</p>'
      })
  }

  /**
   * Agrega un alumno a una clase
   */
  function agregarAlumnoClase() {
    const claseId = document.getElementById("clase-id-alumno").value
    const alumnoId = document.getElementById("select-alumno").value

    if (!alumnoId) {
      alert("Por favor, selecciona un alumno")
      return
    }

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=agregar_alumno&clase_id=${claseId}&alumno_id=${alumnoId}`,
    })
      .then((response) => response.json())
      .then((data) => {
        cerrarModales()

        if (data.ok) {
          alert("Alumno agregado correctamente")
          cargarAlumnosClase(claseId)
        } else {
          alert("Error al agregar el alumno: " + data.msg)
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("Error al agregar el alumno")
        cerrarModales()
      })
  }

  /**
   * Remueve un alumno de una clase
   */
  function removerAlumnoClase(alumnoId, claseId) {
    if (!confirm("¿Estás seguro de que deseas remover este alumno de la clase?")) {
      return
    }

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=remover_alumno&clase_id=${claseId}&alumno_id=${alumnoId}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.ok) {
          alert("Alumno removido correctamente")
          cargarAlumnosClase(claseId)
        } else {
          alert("Error al remover el alumno: " + data.msg)
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("Error al remover el alumno")
      })
  }

  /**
   * Carga las estadísticas de las clases
   */
  function cargarEstadisticas() {
    const contenedor = document.getElementById("contenedor-estadisticas")
    contenedor.innerHTML = '<div class="loading">Cargando estadísticas...</div>'

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "accion=estadisticas_clases",
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.length === 0) {
          contenedor.innerHTML = '<p class="info-message">No hay estadísticas disponibles</p>'
          return
        }

        let html = ""
        data.forEach((clase) => {
          const porcentajeOcupacion = ((clase.inscritos / clase.cupo_maximo) * 100).toFixed(1)
          const promedio = clase.promedio ? Number.parseFloat(clase.promedio).toFixed(1) : "N/A"

          html += `
                <div class="stat-card">
                    <div class="stat-title">${clase.nombre}</div>
                    <div class="stat-value">${clase.inscritos}/${clase.cupo_maximo}</div>
                    <div class="stat-footer">
                        <div>Ocupación: ${porcentajeOcupacion}%</div>
                        <div>Promedio: ${promedio}</div>
                    </div>
                </div>
                `
        })

        contenedor.innerHTML = html
      })
      .catch((error) => {
        console.error("Error:", error)
        contenedor.innerHTML = '<p class="info-message">Error al cargar las estadísticas</p>'
      })
  }

  /**
   * Genera el reporte de calificaciones de una clase
   */
  function generarReporteCalificaciones(claseId) {
    const contenedor = document.getElementById("contenedor-reporte")
    contenedor.innerHTML = '<div class="loading">Generando reporte...</div>'

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=reporte_calificaciones&clase_id=${claseId}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.length === 0) {
          contenedor.innerHTML = '<p class="info-message">No hay calificaciones para esta clase</p>'
          return
        }

        let html = `
            <table>
                <thead>
                    <tr>
                        <th>Alumno</th>
                        <th>Calificación</th>
                        <th>Observación</th>
                        <th>Período</th>
                    </tr>
                </thead>
                <tbody>
            `

        data.forEach((calificacion) => {
          const calificacionClass = calificacion.calificacion >= 70 ? "calificacion-aprobada" : "calificacion-reprobada"
          html += `
                <tr>
                    <td>${calificacion.alumno}</td>
                    <td><span class="calificacion-valor ${calificacionClass}">${calificacion.calificacion}</span></td>
                    <td>${calificacion.observacion || "-"}</td>
                    <td>${calificacion.periodo_evaluacion || "-"}</td>
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
    return 3 // Placeholder
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
