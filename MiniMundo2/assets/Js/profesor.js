/**
 * Script para el panel de profesor
 */
document.addEventListener("DOMContentLoaded", () => {
  // Variables globales
  const usuario_id = obtenerUsuarioId()
  let clases = []
  let alumnosClaseActual = []
  let padreSeleccionado = null

  // Inicialización
  inicializarTabs()
  cargarClases()

  // Event listeners para botones de actualización
  document.getElementById("btn-actualizar-clases").addEventListener("click", cargarClases)

  // Event listeners para selectores
  document.getElementById("select-clase-calificaciones").addEventListener("change", function () {
    const claseId = this.value
    if (claseId) {
      cargarCalificacionesClase(claseId)
    } else {
      document.getElementById("contenedor-calificaciones").innerHTML =
        '<p class="info-message">Selecciona una clase para ver las calificaciones</p>'
    }
  })

  document.getElementById("select-clase-tareas").addEventListener("change", function () {
    const claseId = this.value
    if (claseId) {
      cargarTareasClase(claseId)
      document.getElementById("btn-nueva-tarea").style.display = "block"
    } else {
      document.getElementById("lista-tareas").innerHTML =
        '<p class="info-message">Selecciona una clase para ver las tareas</p>'
      document.getElementById("btn-nueva-tarea").style.display = "none"
    }
  })

  document.getElementById("select-clase-avisos").addEventListener("change", function () {
    const claseId = this.value
    if (claseId) {
      cargarAvisosClase(claseId)
      document.getElementById("btn-nuevo-aviso").style.display = "block"
    } else {
      document.getElementById("lista-avisos").innerHTML =
        '<p class="info-message">Selecciona una clase para ver los avisos</p>'
      document.getElementById("btn-nuevo-aviso").style.display = "none"
    }
  })

  document.getElementById("select-clase-reportes").addEventListener("change", function () {
    const claseId = this.value
    if (claseId) {
      cargarReportesClase(claseId)
      document.getElementById("btn-nuevo-reporte").style.display = "block"
    } else {
      document.getElementById("lista-reportes").innerHTML =
        '<p class="info-message">Selecciona una clase para ver los reportes</p>'
      document.getElementById("btn-nuevo-reporte").style.display = "none"
    }
  })

  document.getElementById("select-padre-mensajes").addEventListener("change", function () {
    const padreId = this.value
    if (padreId) {
      padreSeleccionado = padreId
      cargarMensajesPadre(padreId)
      document.getElementById("form-mensaje").style.display = "block"
    } else {
      document.getElementById("lista-mensajes").innerHTML =
        '<p class="info-message">Selecciona un padre para ver los mensajes</p>'
      document.getElementById("form-mensaje").style.display = "none"
      padreSeleccionado = null
    }
  })

  // Event listeners para botones de acción
  document.getElementById("btn-nueva-tarea").addEventListener("click", () => {
    const claseId = document.getElementById("select-clase-tareas").value
    if (claseId) {
      document.getElementById("clase-id-tarea").value = claseId
      document.getElementById("modal-tarea").style.display = "block"
    }
  })

  document.getElementById("btn-nuevo-aviso").addEventListener("click", () => {
    const claseId = document.getElementById("select-clase-avisos").value
    if (claseId) {
      document.getElementById("clase-id-aviso").value = claseId
      document.getElementById("modal-aviso").style.display = "block"
    }
  })

  document.getElementById("btn-nuevo-reporte").addEventListener("click", () => {
    const claseId = document.getElementById("select-clase-reportes").value
    if (claseId) {
      document.getElementById("clase-id-reporte").value = claseId
      cargarAlumnosParaReporte(claseId)
      document.getElementById("modal-reporte").style.display = "block"
    }
  })

  document.getElementById("btn-enviar-mensaje").addEventListener("click", () => {
    enviarMensajePadre()
  })

  // Event listeners para guardar formularios
  document.getElementById("btn-guardar-calificacion").addEventListener("click", () => {
    guardarCalificacion()
  })

  document.getElementById("btn-guardar-tarea").addEventListener("click", () => {
    guardarTarea()
  })

  document.getElementById("btn-guardar-aviso").addEventListener("click", () => {
    guardarAviso()
  })

  document.getElementById("btn-guardar-reporte").addEventListener("click", () => {
    guardarReporte()
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

        // Cargar datos específicos según la pestaña
        if (tabId === "mensajes") {
          cargarPadresAlumnos()
        }
      })
    })
  }

  /**
   * Carga las clases del profesor
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
                        <div class="clase-alumnos">Alumnos inscritos: ${clase.total_alumnos || 0}</div>
                    </div>
                    <div class="card-footer">
                        <button class="btn-secondary ver-alumnos" data-clase-id="${clase.id}">Ver Alumnos</button>
                    </div>
                </div>
                `
        })

        contenedor.innerHTML = html

        // Agregar event listeners a los botones
        document.querySelectorAll(".ver-alumnos").forEach((btn) => {
          btn.addEventListener("click", function () {
            const claseId = this.getAttribute("data-clase-id")
            cargarAlumnosClase(claseId)
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
    const selectores = [
      "select-clase-calificaciones",
      "select-clase-tareas",
      "select-clase-avisos",
      "select-clase-reportes",
    ]

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
   * Carga los alumnos de una clase
   */
  function cargarAlumnosClase(claseId) {
    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=alumnos_clase&clase_id=${claseId}`,
    })
      .then((response) => response.json())
      .then((data) => {
        alumnosClaseActual = data
        // Aquí podrías mostrar los alumnos en un modal o cambiar a otra pestaña
        console.log("Alumnos de la clase:", data)
      })
      .catch((error) => {
        console.error("Error:", error)
      })
  }

  /**
   * Carga las calificaciones de una clase
   */
  function cargarCalificacionesClase(claseId) {
    const contenedor = document.getElementById("contenedor-calificaciones")
    contenedor.innerHTML = '<div class="loading">Cargando calificaciones...</div>'

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=get_calificaciones&clase_id=${claseId}`,
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
                        <th>Alumno</th>
                        <th>Calificación</th>
                        <th>Observación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
            `

        data.forEach((alumno) => {
          html += `
                <tr class="alumno-row">
                    <td>${alumno.alumno}</td>
                    <td>
                        <input type="number" class="calificacion-input" 
                               value="${alumno.calificacion || ""}" 
                               min="0" max="100" step="0.1"
                               data-alumno-id="${alumno.alumno_id}">
                    </td>
                    <td>${alumno.observacion || "-"}</td>
                    <td>
                        <button class="btn-primary btn-asignar-calificacion" 
                                data-alumno-id="${alumno.alumno_id}" 
                                data-alumno-nombre="${alumno.alumno}">
                            Asignar
                        </button>
                        ${
                          alumno.calificacion
                            ? `
                        <button class="btn-danger btn-borrar-calificacion" 
                                data-alumno-id="${alumno.alumno_id}">
                            Borrar
                        </button>
                        `
                            : ""
                        }
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
        document.querySelectorAll(".btn-asignar-calificacion").forEach((btn) => {
          btn.addEventListener("click", function () {
            const alumnoId = this.getAttribute("data-alumno-id")
            const alumnoNombre = this.getAttribute("data-alumno-nombre")
            abrirModalCalificacion(alumnoId, alumnoNombre, claseId)
          })
        })

        document.querySelectorAll(".btn-borrar-calificacion").forEach((btn) => {
          btn.addEventListener("click", function () {
            const alumnoId = this.getAttribute("data-alumno-id")
            borrarCalificacion(alumnoId, claseId)
          })
        })
      })
      .catch((error) => {
        console.error("Error:", error)
        contenedor.innerHTML = '<p class="info-message">Error al cargar las calificaciones</p>'
      })
  }

  /**
   * Abre el modal para asignar calificación
   */
  function abrirModalCalificacion(alumnoId, alumnoNombre, claseId) {
    document.getElementById("alumno-id").value = alumnoId
    document.getElementById("clase-id-cal").value = claseId
    document.getElementById("nombre-alumno").textContent = alumnoNombre
    document.getElementById("calificacion").value = ""
    document.getElementById("observacion").value = ""
    document.getElementById("modal-calificacion").style.display = "block"
  }

  /**
   * Guarda una calificación
   */
  function guardarCalificacion() {
    const alumnoId = document.getElementById("alumno-id").value
    const claseId = document.getElementById("clase-id-cal").value
    const calificacion = document.getElementById("calificacion").value
    const observacion = document.getElementById("observacion").value

    if (!calificacion) {
      alert("Por favor, ingresa una calificación")
      return
    }

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=set_calificacion&alumno_id=${alumnoId}&clase_id=${claseId}&calificacion=${calificacion}&observacion=${encodeURIComponent(observacion)}`,
    })
      .then((response) => response.json())
      .then((data) => {
        cerrarModales()

        if (data.ok) {
          alert("Calificación guardada correctamente")
          cargarCalificacionesClase(claseId)
        } else {
          alert("Error al guardar la calificación: " + data.msg)
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("Error al guardar la calificación")
        cerrarModales()
      })
  }

  /**
   * Borra una calificación
   */
  function borrarCalificacion(alumnoId, claseId) {
    if (!confirm("¿Estás seguro de que deseas borrar esta calificación?")) {
      return
    }

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=borrar_calificacion&alumno_id=${alumnoId}&clase_id=${claseId}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.ok) {
          alert("Calificación borrada correctamente")
          cargarCalificacionesClase(claseId)
        } else {
          alert("Error al borrar la calificación: " + data.msg)
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("Error al borrar la calificación")
      })
  }

  /**
   * Carga las tareas de una clase
   */
  function cargarTareasClase(claseId) {
    const contenedor = document.getElementById("lista-tareas")
    contenedor.innerHTML = '<div class="loading">Cargando tareas...</div>'

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=listar_tareas&clase_id=${claseId}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.length === 0) {
          contenedor.innerHTML = '<p class="info-message">No hay tareas creadas para esta clase</p>'
          return
        }

        let html = ""
        data.forEach((tarea) => {
          const fechaEntrega = new Date(tarea.fecha_entrega).toLocaleDateString()
          html += `
                <div class="card tarea-card">
                    <div class="card-header">
                        <div class="card-title">${tarea.titulo}</div>
                    </div>
                    <div class="card-body">
                        <p>${tarea.descripcion}</p>
                        <div class="tarea-fecha">Fecha de entrega: ${fechaEntrega}</div>
                    </div>
                    <div class="card-footer">
                        <button class="btn-danger btn-borrar-tarea" data-tarea-id="${tarea.id}">Eliminar</button>
                    </div>
                </div>
                `
        })

        contenedor.innerHTML = html

        // Agregar event listeners
        document.querySelectorAll(".btn-borrar-tarea").forEach((btn) => {
          btn.addEventListener("click", function () {
            const tareaId = this.getAttribute("data-tarea-id")
            borrarTarea(tareaId, claseId)
          })
        })
      })
      .catch((error) => {
        console.error("Error:", error)
        contenedor.innerHTML = '<p class="info-message">Error al cargar las tareas</p>'
      })
  }

  /**
   * Guarda una nueva tarea
   */
  function guardarTarea() {
    const claseId = document.getElementById("clase-id-tarea").value
    const titulo = document.getElementById("titulo-tarea").value
    const descripcion = document.getElementById("descripcion-tarea").value
    const fechaEntrega = document.getElementById("fecha-entrega").value

    if (!titulo || !descripcion || !fechaEntrega) {
      alert("Por favor, completa todos los campos")
      return
    }

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=crear_tarea&clase_id=${claseId}&titulo=${encodeURIComponent(titulo)}&descripcion=${encodeURIComponent(descripcion)}&fecha_entrega=${fechaEntrega}`,
    })
      .then((response) => response.json())
      .then((data) => {
        cerrarModales()

        if (data.ok) {
          alert("Tarea creada correctamente")
          cargarTareasClase(claseId)
          document.getElementById("form-tarea").reset()
        } else {
          alert("Error al crear la tarea: " + data.msg)
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("Error al crear la tarea")
        cerrarModales()
      })
  }

  /**
   * Borra una tarea
   */
  function borrarTarea(tareaId, claseId) {
    if (!confirm("¿Estás seguro de que deseas eliminar esta tarea?")) {
      return
    }

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=borrar_tarea&tarea_id=${tareaId}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.ok) {
          alert("Tarea eliminada correctamente")
          cargarTareasClase(claseId)
        } else {
          alert("Error al eliminar la tarea: " + data.msg)
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("Error al eliminar la tarea")
      })
  }

  /**
   * Carga los avisos de una clase
   */
  function cargarAvisosClase(claseId) {
    const contenedor = document.getElementById("lista-avisos")
    contenedor.innerHTML = '<div class="loading">Cargando avisos...</div>'

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=listar_avisos&clase_id=${claseId}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.length === 0) {
          contenedor.innerHTML = '<p class="info-message">No hay avisos creados para esta clase</p>'
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
                    </div>
                    <div class="card-body">
                        <p>${aviso.mensaje}</p>
                        <div class="aviso-fecha">Fecha: ${fecha}</div>
                    </div>
                    <div class="card-footer">
                        <button class="btn-danger btn-borrar-aviso" data-aviso-id="${aviso.id}">Eliminar</button>
                    </div>
                </div>
                `
        })

        contenedor.innerHTML = html

        // Agregar event listeners
        document.querySelectorAll(".btn-borrar-aviso").forEach((btn) => {
          btn.addEventListener("click", function () {
            const avisoId = this.getAttribute("data-aviso-id")
            borrarAviso(avisoId, claseId)
          })
        })
      })
      .catch((error) => {
        console.error("Error:", error)
        contenedor.innerHTML = '<p class="info-message">Error al cargar los avisos</p>'
      })
  }

  /**
   * Guarda un nuevo aviso
   */
  function guardarAviso() {
    const claseId = document.getElementById("clase-id-aviso").value
    const titulo = document.getElementById("titulo-aviso").value
    const mensaje = document.getElementById("mensaje-aviso").value

    if (!titulo || !mensaje) {
      alert("Por favor, completa todos los campos")
      return
    }

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=mandar_aviso&clase_id=${claseId}&titulo=${encodeURIComponent(titulo)}&mensaje=${encodeURIComponent(mensaje)}`,
    })
      .then((response) => response.json())
      .then((data) => {
        cerrarModales()

        if (data.ok) {
          alert("Aviso creado correctamente")
          cargarAvisosClase(claseId)
          document.getElementById("form-aviso").reset()
        } else {
          alert("Error al crear el aviso: " + data.msg)
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("Error al crear el aviso")
        cerrarModales()
      })
  }

  /**
   * Borra un aviso
   */
  function borrarAviso(avisoId, claseId) {
    if (!confirm("¿Estás seguro de que deseas eliminar este aviso?")) {
      return
    }

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=borrar_aviso&aviso_id=${avisoId}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.ok) {
          alert("Aviso eliminado correctamente")
          cargarAvisosClase(claseId)
        } else {
          alert("Error al eliminar el aviso: " + data.msg)
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("Error al eliminar el aviso")
      })
  }

  /**
   * Carga los reportes de una clase
   */
  function cargarReportesClase(claseId) {
    const contenedor = document.getElementById("lista-reportes")
    contenedor.innerHTML = '<div class="loading">Cargando reportes...</div>'

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=listar_reportes&clase_id=${claseId}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.length === 0) {
          contenedor.innerHTML = '<p class="info-message">No hay reportes creados para esta clase</p>'
          return
        }

        let html = ""
        data.forEach((reporte) => {
          const fecha = new Date(reporte.fecha).toLocaleDateString()

          html += `
                <div class="card reporte-card">
                    <div class="card-header">
                        <div class="card-title">${reporte.titulo}</div>
                        <div>Alumno: ${reporte.alumno}</div>
                    </div>
                    <div class="card-body">
                        <p>${reporte.descripcion}</p>
                        <div class="reporte-fecha">Fecha: ${fecha}</div>
                    </div>
                    <div class="card-footer">
                        <button class="btn-danger btn-borrar-reporte" data-reporte-id="${reporte.id}">Eliminar</button>
                    </div>
                </div>
                `
        })

        contenedor.innerHTML = html

        // Agregar event listeners
        document.querySelectorAll(".btn-borrar-reporte").forEach((btn) => {
          btn.addEventListener("click", function () {
            const reporteId = this.getAttribute("data-reporte-id")
            borrarReporte(reporteId, claseId)
          })
        })
      })
      .catch((error) => {
        console.error("Error:", error)
        contenedor.innerHTML = '<p class="info-message">Error al cargar los reportes</p>'
      })
  }

  /**
   * Carga los alumnos para el selector de reportes
   */
  function cargarAlumnosParaReporte(claseId) {
    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=alumnos_clase&clase_id=${claseId}`,
    })
      .then((response) => response.json())
      .then((data) => {
        const selector = document.getElementById("alumno-reporte")
        selector.innerHTML = '<option value="">Selecciona un alumno</option>'

        data.forEach((alumno) => {
          const option = document.createElement("option")
          option.value = alumno.id
          option.textContent = alumno.nombre
          selector.appendChild(option)
        })
      })
      .catch((error) => {
        console.error("Error:", error)
      })
  }

  /**
   * Guarda un nuevo reporte
   */
  function guardarReporte() {
    const claseId = document.getElementById("clase-id-reporte").value
    const alumnoId = document.getElementById("alumno-reporte").value
    const titulo = document.getElementById("titulo-reporte").value
    const descripcion = document.getElementById("descripcion-reporte").value

    if (!alumnoId || !titulo || !descripcion) {
      alert("Por favor, completa todos los campos")
      return
    }

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=crear_reporte&clase_id=${claseId}&alumno_id=${alumnoId}&titulo=${encodeURIComponent(titulo)}&descripcion=${encodeURIComponent(descripcion)}`,
    })
      .then((response) => response.json())
      .then((data) => {
        cerrarModales()

        if (data.ok) {
          alert("Reporte creado correctamente")
          cargarReportesClase(claseId)
          document.getElementById("form-reporte").reset()
        } else {
          alert("Error al crear el reporte: " + data.msg)
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("Error al crear el reporte")
        cerrarModales()
      })
  }

  /**
   * Borra un reporte
   */
  function borrarReporte(reporteId, claseId) {
    if (!confirm("¿Estás seguro de que deseas eliminar este reporte?")) {
      return
    }

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=borrar_reporte&reporte_id=${reporteId}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.ok) {
          alert("Reporte eliminado correctamente")
          cargarReportesClase(claseId)
        } else {
          alert("Error al eliminar el reporte: " + data.msg)
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("Error al eliminar el reporte")
      })
  }

  /**
   * Carga los padres de los alumnos del profesor
   */
  function cargarPadresAlumnos() {
    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "accion=padres_mis_alumnos",
    })
      .then((response) => response.json())
      .then((data) => {
        const selector = document.getElementById("select-padre-mensajes")
        selector.innerHTML = '<option value="">Selecciona un padre</option>'

        data.forEach((padre) => {
          const option = document.createElement("option")
          option.value = padre.id
          option.textContent = padre.nombre
          selector.appendChild(option)
        })
      })
      .catch((error) => {
        console.error("Error:", error)
      })
  }

  /**
   * Carga los mensajes con un padre
   */
  function cargarMensajesPadre(padreId) {
    const contenedor = document.getElementById("lista-mensajes")
    contenedor.innerHTML = '<div class="loading">Cargando mensajes...</div>'

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=mensajes_padre&padre_id=${padreId}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.length === 0) {
          contenedor.innerHTML = '<p class="info-message">No hay mensajes con este padre</p>'
          return
        }

        let html = ""
        data.forEach((mensaje) => {
          const fecha = new Date(mensaje.fecha).toLocaleString()
          const claseMessage = mensaje.de_quien === "profesor" ? "message-sent" : "message-received"

          html += `
                <div class="message ${claseMessage}">
                    <div class="message-header">
                        <span class="message-sender">${mensaje.de_quien === "profesor" ? "Tú" : "Padre"}</span>
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
   * Envía un mensaje a un padre
   */
  function enviarMensajePadre() {
    const mensaje = document.getElementById("texto-mensaje").value.trim()

    if (!mensaje) {
      alert("Por favor, escribe un mensaje")
      return
    }

    if (!padreSeleccionado) {
      alert("Por favor, selecciona un padre")
      return
    }

    fetch("../includes/implementacion.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `accion=enviar_mensaje_padre&padre_id=${padreSeleccionado}&mensaje=${encodeURIComponent(mensaje)}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.ok) {
          document.getElementById("texto-mensaje").value = ""
          cargarMensajesPadre(padreSeleccionado)
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
    return 2 // Placeholder
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
