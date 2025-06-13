<?php
/**
 * Archivo de implementación para reemplazar los archivos CRUD existentes
 * Este archivo muestra cómo usar las nuevas funciones en los diferentes roles
 */

session_start();
require_once 'funciones.php';

// Verificar si hay una sesión activa
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_rol'])) {
    header('Location: ../public.php');
    exit;
}

$conexion = conectarBD();
$usuario_id = $_SESSION['usuario_id'];
$usuario_rol = $_SESSION['usuario_rol'];
$accion = $_POST['accion'] ?? '';

// Actualizar último acceso
actualizarUltimoAcceso($conexion, $usuario_id);

// Procesar la acción según el rol del usuario
switch ($usuario_rol) {
    case 1: // Alumno
        procesarAccionAlumno($conexion, $usuario_id, $accion);
        break;
    case 2: // Profesor
        procesarAccionProfesor($conexion, $usuario_id, $accion);
        break;
    case 3: // Coordinador
        procesarAccionCoordinador($conexion, $usuario_id, $accion);
        break;
    case 4: // Director
        procesarAccionDirector($conexion, $usuario_id, $accion);
        break;
    case 5: // Admin
        procesarAccionAdmin($conexion, $usuario_id, $accion);
        break;
    case 6: // Padre
        procesarAccionPadre($conexion, $usuario_id, $accion);
        break;
    default:
        responderJSON(['ok' => false, 'msg' => 'Rol no válido']);
}

cerrarBD($conexion);

/**
 * Funciones para procesar acciones según el rol
 */

function procesarAccionAlumno($conexion, $usuario_id, $accion) {
    switch ($accion) {
        case 'mis_clases':
            $inscripciones = obtenerInscripcionesPorAlumno($conexion, $usuario_id);
            $clases = [];
            
            foreach ($inscripciones as $inscripcion) {
                if ($inscripcion['aprobada'] == 1) {
                    $clases[] = [
                        'id' => $inscripcion['clase_id'],
                        'nombre' => $inscripcion['clase_nombre'],
                        'periodo' => $inscripcion['periodo'] ?? '',
                        'profesor' => $inscripcion['profesor_nombre']
                    ];
                }
            }
            
            responderJSON($clases);
            break;
            
        case 'avisos':
            $avisos = obtenerAvisosParaAlumno($conexion, $usuario_id);
            responderJSON($avisos);
            break;
            
        case 'tareas':
            $inscripciones = obtenerInscripcionesPorAlumno($conexion, $usuario_id);
            $tareas = [];
            
            foreach ($inscripciones as $inscripcion) {
                if ($inscripcion['aprobada'] == 1) {
                    $tareasClase = obtenerTareasPorClase($conexion, $inscripcion['clase_id']);
                    foreach ($tareasClase as $tarea) {
                        $tarea['clase'] = $inscripcion['clase_nombre'];
                        $tareas[] = $tarea;
                    }
                }
            }
            
            responderJSON($tareas);
            break;
            
        case 'calificaciones':
            $calificaciones = obtenerCalificacionesPorAlumno($conexion, $usuario_id);
            responderJSON($calificaciones);
            break;
            
        case 'reportes':
            $reportes = obtenerReportesPorAlumno($conexion, $usuario_id);
            responderJSON($reportes);
            break;
            
        case 'perfil':
            $usuario = obtenerUsuario($conexion, $usuario_id);
            responderJSON([
                'nombre' => $usuario['nombre'],
                'email' => $usuario['email']
            ]);
            break;
            
        case 'editar_perfil':
            $nombre = $_POST['nombre'] ?? '';
            $email = $_POST['email'] ?? '';
            
            $resultado = actualizarUsuario($conexion, $usuario_id, [
                'nombre' => $nombre,
                'email' => $email
            ]);
            
            responderJSON($resultado);
            break;
            
        case 'clases_disponibles':
            $query = "SELECT c.id, c.nombre, c.periodo, p.nombre as profesor
                      FROM clases c
                      JOIN usuarios p ON c.profesor_id = p.id
                      WHERE c.id NOT IN (
                          SELECT clase_id FROM inscripciones WHERE alumno_id = $usuario_id
                      )";
            
            $resultado = mysqli_query($conexion, $query);
            $clases = [];
            
            while ($row = mysqli_fetch_assoc($resultado)) {
                $clases[] = $row;
            }
            
            responderJSON($clases);
            break;
            
        case 'inscribirse':
            $clase_id = intval($_POST['clase_id'] ?? 0);
            $resultado = inscribirAlumno($conexion, $usuario_id, $clase_id, 0);
            responderJSON($resultado);
            break;
            
        case 'estado_inscripciones':
            $inscripciones = obtenerInscripcionesPorAlumno($conexion, $usuario_id);
            $estados = [];
            
            foreach ($inscripciones as $inscripcion) {
                $estados[] = [
                    'clase' => $inscripcion['clase_nombre'],
                    'periodo' => $inscripcion['periodo'] ?? '',
                    'profesor' => $inscripcion['profesor_nombre'],
                    'estado' => $inscripcion['aprobada'] == 1 ? 'Inscrito' : 'Pendiente'
                ];
            }
            
            responderJSON($estados);
            break;
            
        default:
            responderJSON(['ok' => false, 'msg' => 'Acción no permitida para Alumno']);
    }
}

function procesarAccionProfesor($conexion, $usuario_id, $accion) {
    switch ($accion) {
        case 'mis_clases':
            $clases = obtenerClasesPorProfesor($conexion, $usuario_id);
            responderJSON($clases);
            break;
            
        case 'alumnos_clase':
            $clase_id = intval($_POST['clase_id'] ?? 0);
            
            // Verificar que la clase pertenece al profesor
            $verificar = mysqli_query($conexion, "SELECT 1 FROM clases WHERE id = $clase_id AND profesor_id = $usuario_id");
            if (mysqli_num_rows($verificar) == 0) {
                responderJSON([]);
                break;
            }
            
            $inscripciones = obtenerInscripcionesPorClase($conexion, $clase_id);
            $alumnos = [];
            
            foreach ($inscripciones as $inscripcion) {
                if ($inscripcion['aprobada'] == 1) {
                    $alumnos[] = [
                        'id' => $inscripcion['alumno_id'],
                        'nombre' => $inscripcion['alumno_nombre'],
                        'email' => $inscripcion['alumno_email']
                    ];
                }
            }
            
            responderJSON($alumnos);
            break;
            
        case 'get_calificaciones':
            $clase_id = intval($_POST['clase_id'] ?? 0);
            
            // Verificar que la clase pertenece al profesor
            $verificar = mysqli_query($conexion, "SELECT 1 FROM clases WHERE id = $clase_id AND profesor_id = $usuario_id");
            if (mysqli_num_rows($verificar) == 0) {
                responderJSON([]);
                break;
            }
            
            $query = "SELECT u.id as alumno_id, u.nombre as alumno, c.calificacion, c.observacion
                      FROM inscripciones i
                      JOIN usuarios u ON i.alumno_id = u.id
                      LEFT JOIN calificaciones c ON c.alumno_id = u.id AND c.clase_id = i.clase_id
                      WHERE i.clase_id = $clase_id AND i.aprobada = 1 AND u.rol_id = 1";
            
            $resultado = mysqli_query($conexion, $query);
            $calificaciones = [];
            
            while ($row = mysqli_fetch_assoc($resultado)) {
                $calificaciones[] = $row;
            }
            
            responderJSON($calificaciones);
            break;
            
        case 'set_calificacion':
            $alumno_id = intval($_POST['alumno_id'] ?? 0);
            $clase_id = intval($_POST['clase_id'] ?? 0);
            $calificacion = floatval($_POST['calificacion'] ?? 0);
            $observacion = $_POST['observacion'] ?? '';
            
            // Verificar que la clase pertenece al profesor
            $verificar = mysqli_query($conexion, "SELECT 1 FROM clases WHERE id = $clase_id AND profesor_id = $usuario_id");
            if (mysqli_num_rows($verificar) == 0) {
                responderJSON(['ok' => false, 'msg' => 'No tienes permiso para esta clase']);
                break;
            }
            
            $resultado = asignarCalificacion($conexion, $alumno_id, $clase_id, $calificacion, $observacion);
            responderJSON($resultado);
            break;
            
        case 'borrar_calificacion':
            $alumno_id = intval($_POST['alumno_id'] ?? 0);
            $clase_id = intval($_POST['clase_id'] ?? 0);
            
            // Verificar que la clase pertenece al profesor
            $verificar = mysqli_query($conexion, "SELECT 1 FROM clases WHERE id = $clase_id AND profesor_id = $usuario_id");
            if (mysqli_num_rows($verificar) == 0) {
                responderJSON(['ok' => false, 'msg' => 'No tienes permiso para esta clase']);
                break;
            }
            
            $resultado = eliminarCalificacion($conexion, $alumno_id, $clase_id);
            responderJSON($resultado);
            break;
            
        case 'listar_tareas':
            $clase_id = intval($_POST['clase_id'] ?? 0);
            
            // Verificar que la clase pertenece al profesor
            $verificar = mysqli_query($conexion, "SELECT 1 FROM clases WHERE id = $clase_id AND profesor_id = $usuario_id");
            if (mysqli_num_rows($verificar) == 0) {
                responderJSON([]);
                break;
            }
            
            $tareas = obtenerTareasPorClase($conexion, $clase_id);
            responderJSON($tareas);
            break;
            
        case 'crear_tarea':
            $clase_id = intval($_POST['clase_id'] ?? 0);
            $titulo = $_POST['titulo'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            $fecha_entrega = $_POST['fecha_entrega'] ?? '';
            $archivo = $_POST['archivo'] ?? '';
            
            // Verificar que la clase pertenece al profesor
            $verificar = mysqli_query($conexion, "SELECT 1 FROM clases WHERE id = $clase_id AND profesor_id = $usuario_id");
            if (mysqli_num_rows($verificar) == 0) {
                responderJSON(['ok' => false, 'msg' => 'No tienes permiso para esta clase']);
                break;
            }
            
            $resultado = crearTarea($conexion, $clase_id, $titulo, $descripcion, $fecha_entrega, $archivo);
            responderJSON($resultado);
            break;
            
        case 'borrar_tarea':
            $tarea_id = intval($_POST['tarea_id'] ?? 0);
            
            // Verificar que la tarea pertenece a una clase del profesor
            $verificar = mysqli_query($conexion, "
                SELECT 1 FROM tareas t
                JOIN clases c ON t.clase_id = c.id
                WHERE t.id = $tarea_id AND c.profesor_id = $usuario_id
            ");
            
            if (mysqli_num_rows($verificar) == 0) {
                responderJSON(['ok' => false, 'msg' => 'No tienes permiso para esta tarea']);
                break;
            }
            
            $resultado = eliminarTarea($conexion, $tarea_id);
            responderJSON($resultado);
            break;
            
        case 'mandar_aviso':
            $clase_id = intval($_POST['clase_id'] ?? 0);
            $titulo = $_POST['titulo'] ?? '';
            $mensaje = $_POST['mensaje'] ?? '';
            
            // Verificar que la clase pertenece al profesor
            $verificar = mysqli_query($conexion, "SELECT 1 FROM clases WHERE id = $clase_id AND profesor_id = $usuario_id");
            if (mysqli_num_rows($verificar) == 0) {
                responderJSON(['ok' => false, 'msg' => 'No tienes permiso para esta clase']);
                break;
            }
            
            $resultado = crearAviso($conexion, $clase_id, $usuario_id, $titulo, $mensaje);
            responderJSON($resultado);
            break;
            
        case 'listar_avisos':
            $clase_id = intval($_POST['clase_id'] ?? 0);
            
            // Verificar que la clase pertenece al profesor
            $verificar = mysqli_query($conexion, "SELECT 1 FROM clases WHERE id = $clase_id AND profesor_id = $usuario_id");
            if (mysqli_num_rows($verificar) == 0) {
                responderJSON([]);
                break;
            }
            
            $avisos = obtenerAvisosPorClase($conexion, $clase_id);
            responderJSON($avisos);
            break;
            
        case 'borrar_aviso':
            $aviso_id = intval($_POST['aviso_id'] ?? 0);
            $resultado = eliminarAviso($conexion, $aviso_id, $usuario_id);
            responderJSON($resultado);
            break;
            
        case 'listar_reportes':
            $clase_id = intval($_POST['clase_id'] ?? 0);
            
            // Verificar que la clase pertenece al profesor
            $verificar = mysqli_query($conexion, "SELECT 1 FROM clases WHERE id = $clase_id AND profesor_id = $usuario_id");
            if (mysqli_num_rows($verificar) == 0) {
                responderJSON([]);
                break;
            }
            
            $query = "SELECT r.id, r.alumno_id, u.nombre as alumno, r.titulo, r.descripcion, r.fecha
                      FROM reportes r
                      JOIN usuarios u ON r.alumno_id = u.id
                      WHERE r.clase_id = $clase_id AND r.profesor_id = $usuario_id
                      ORDER BY r.fecha DESC";
            
            $resultado = mysqli_query($conexion, $query);
            $reportes = [];
            
            while ($row = mysqli_fetch_assoc($resultado)) {
                $reportes[] = $row;
            }
            
            responderJSON($reportes);
            break;
            
        case 'crear_reporte':
            $clase_id = intval($_POST['clase_id'] ?? 0);
            $alumno_id = intval($_POST['alumno_id'] ?? 0);
            $titulo = $_POST['titulo'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            
            // Verificar que la clase pertenece al profesor
            $verificar = mysqli_query($conexion, "SELECT 1 FROM clases WHERE id = $clase_id AND profesor_id = $usuario_id");
            if (mysqli_num_rows($verificar) == 0) {
                responderJSON(['ok' => false, 'msg' => 'No tienes permiso para esta clase']);
                break;
            }
            
            $resultado = crearReporte($conexion, $clase_id, $alumno_id, $usuario_id, $titulo, $descripcion);
            responderJSON($resultado);
            break;
            
        case 'borrar_reporte':
            $reporte_id = intval($_POST['reporte_id'] ?? 0);
            $resultado = eliminarReporte($conexion, $reporte_id, $usuario_id);
            responderJSON($resultado);
            break;
            
        case 'padres_mis_alumnos':
            $query = "SELECT DISTINCT u.id, u.nombre
                      FROM clases c
                      JOIN inscripciones i ON i.clase_id = c.id
                      JOIN hijos h ON h.alumno_id = i.alumno_id
                      JOIN usuarios u ON u.id = h.padre_id
                      WHERE c.profesor_id = $usuario_id AND i.aprobada = 1 AND u.rol_id = 6 AND u.activo = 1";
            
            $resultado = mysqli_query($conexion, $query);
            $padres = [];
            
            while ($row = mysqli_fetch_assoc($resultado)) {
                $padres[] = $row;
            }
            
            responderJSON($padres);
            break;
            
        case 'mensajes_padre':
            $padre_id = intval($_POST['padre_id'] ?? 0);
            $mensajes = obtenerMensajesPadreProfesor($conexion, $padre_id, $usuario_id);
            responderJSON($mensajes);
            break;
            
        case 'enviar_mensaje_padre':
            $padre_id = intval($_POST['padre_id'] ?? 0);
            $mensaje = $_POST['mensaje'] ?? '';
            $resultado = enviarMensaje($conexion, $padre_id, $usuario_id, $mensaje, 'profesor');
            responderJSON($resultado);
            break;
            
        default:
            responderJSON(['ok' => false, 'msg' => 'Acción no permitida para Profesor']);
    }
}

function procesarAccionCoordinador($conexion, $usuario_id, $accion) {
    switch ($accion) {
        case 'mis_clases':
            $clases = obtenerClasesPorCoordinador($conexion, $usuario_id);
            responderJSON($clases);
            break;
            
        case 'crear_clase':
            $nombre = $_POST['nombre'] ?? '';
            $periodo = $_POST['periodo'] ?? '';
            $profesor_id = intval($_POST['profesor_id'] ?? 0);
            $cupo_maximo = intval($_POST['cupo_maximo'] ?? 30);
            $descripcion = $_POST['descripcion'] ?? '';
            
            $resultado = crearClase($conexion, $nombre, $periodo, $profesor_id, $usuario_id, $cupo_maximo, $descripcion);
            responderJSON($resultado);
            break;
            
        case 'editar_clase':
            $clase_id = intval($_POST['clase_id'] ?? 0);
            $datos = [];
            
            if (isset($_POST['nombre'])) $datos['nombre'] = $_POST['nombre'];
            if (isset($_POST['periodo'])) $datos['periodo'] = $_POST['periodo'];
            if (isset($_POST['profesor_id'])) $datos['profesor_id'] = intval($_POST['profesor_id']);
            if (isset($_POST['cupo_maximo'])) $datos['cupo_maximo'] = intval($_POST['cupo_maximo']);
            if (isset($_POST['descripcion'])) $datos['descripcion'] = $_POST['descripcion'];
            
            // Verificar que la clase pertenece al coordinador
            $verificar = mysqli_query($conexion, "SELECT 1 FROM clases WHERE id = $clase_id AND coordinador_id = $usuario_id");
            if (mysqli_num_rows($verificar) == 0) {
                responderJSON(['ok' => false, 'msg' => 'No tienes permiso para esta clase']);
                break;
            }
            
            $resultado = actualizarClase($conexion, $clase_id, $datos);
            responderJSON($resultado);
            break;
            
        case 'eliminar_clase':
            $clase_id = intval($_POST['clase_id'] ?? 0);
            
            // Verificar que la clase pertenece al coordinador
            $verificar = mysqli_query($conexion, "SELECT 1 FROM clases WHERE id = $clase_id AND coordinador_id = $usuario_id");
            if (mysqli_num_rows($verificar) == 0) {
                responderJSON(['ok' => false, 'msg' => 'No tienes permiso para esta clase']);
                break;
            }
            
            $resultado = eliminarClase($conexion, $clase_id);
            responderJSON($resultado);
            break;
            
        case 'profesores_disponibles':
            $profesores = obtenerUsuariosPorRol($conexion, 2);
            responderJSON($profesores);
            break;
            
        case 'inscripciones_pendientes':
            $query = "SELECT i.id, i.alumno_id, u.nombre as alumno, c.nombre as clase, i.fecha_inscripcion
                      FROM inscripciones i
                      JOIN usuarios u ON i.alumno_id = u.id
                      JOIN clases c ON i.clase_id = c.id
                      WHERE c.coordinador_id = $usuario_id AND i.aprobada = 0
                      ORDER BY i.fecha_inscripcion ASC";
            
            $resultado = mysqli_query($conexion, $query);
            $inscripciones = [];
            
            while ($row = mysqli_fetch_assoc($resultado)) {
                $inscripciones[] = $row;
            }
            
            responderJSON($inscripciones);
            break;
            
        case 'aprobar_inscripcion':
            $inscripcion_id = intval($_POST['inscripcion_id'] ?? 0);
            
            // Verificar que la inscripción pertenece a una clase del coordinador
            $verificar = mysqli_query($conexion, "
                SELECT 1 FROM inscripciones i
                JOIN clases c ON i.clase_id = c.id
                WHERE i.id = $inscripcion_id AND c.coordinador_id = $usuario_id
            ");
            
            if (mysqli_num_rows($verificar) == 0) {
                responderJSON(['ok' => false, 'msg' => 'No tienes permiso para esta inscripción']);
                break;
            }
            
            $resultado = aprobarInscripcion($conexion, $inscripcion_id);
            responderJSON($resultado);
            break;
            
        case 'rechazar_inscripcion':
            $inscripcion_id = intval($_POST['inscripcion_id'] ?? 0);
            
            // Verificar que la inscripción pertenece a una clase del coordinador
            $verificar = mysqli_query($conexion, "
                SELECT 1 FROM inscripciones i
                JOIN clases c ON i.clase_id = c.id
                WHERE i.id = $inscripcion_id AND c.coordinador_id = $usuario_id
            ");
            
            if (mysqli_num_rows($verificar) == 0) {
                responderJSON(['ok' => false, 'msg' => 'No tienes permiso para esta inscripción']);
                break;
            }
            
            $resultado = rechazarInscripcion($conexion, $inscripcion_id);
            responderJSON($resultado);
            break;
            
        case 'alumnos_clase':
            $clase_id = intval($_POST['clase_id'] ?? 0);
            
            // Verificar que la clase pertenece al coordinador
            $verificar = mysqli_query($conexion, "SELECT 1 FROM clases WHERE id = $clase_id AND coordinador_id = $usuario_id");
            if (mysqli_num_rows($verificar) == 0) {
                responderJSON([]);
                break;
            }
            
            $inscripciones = obtenerInscripcionesPorClase($conexion, $clase_id);
            $alumnos = [];
            
            foreach ($inscripciones as $inscripcion) {
                $alumnos[] = [
                    'id' => $inscripcion['alumno_id'],
                    'nombre' => $inscripcion['alumno_nombre'],
                    'email' => $inscripcion['alumno_email'],
                    'estado' => $inscripcion['aprobada'] == 1 ? 'Inscrito' : 'Pendiente'
                ];
            }
            
            responderJSON($alumnos);
            break;
            
        case 'agregar_alumno':
            $clase_id = intval($_POST['clase_id'] ?? 0);
            $alumno_id = intval($_POST['alumno_id'] ?? 0);
            
            // Verificar que la clase pertenece al coordinador
            $verificar = mysqli_query($conexion, "SELECT 1 FROM clases WHERE id = $clase_id AND coordinador_id = $usuario_id");
            if (mysqli_num_rows($verificar) == 0) {
                responderJSON(['ok' => false, 'msg' => 'No tienes permiso para esta clase']);
                break;
            }
            
            $resultado = inscribirAlumno($conexion, $alumno_id, $clase_id, 1);
            responderJSON($resultado);
            break;
            
        case 'remover_alumno':
            $clase_id = intval($_POST['clase_id'] ?? 0);
            $alumno_id = intval($_POST['alumno_id'] ?? 0);
            
            // Verificar que la clase pertenece al coordinador
            $verificar = mysqli_query($conexion, "SELECT 1 FROM clases WHERE id = $clase_id AND coordinador_id = $usuario_id");
            if (mysqli_num_rows($verificar) == 0) {
                responderJSON(['ok' => false, 'msg' => 'No tienes permiso para esta clase']);
                break;
            }
            
            $query = "DELETE FROM inscripciones WHERE alumno_id = $alumno_id AND clase_id = $clase_id";
            
            if (mysqli_query($conexion, $query)) {
                responderJSON(['ok' => true]);
            } else {
                responderJSON(['ok' => false, 'msg' => mysqli_error($conexion)]);
            }
            break;
            
        case 'alumnos_disponibles':
            $alumnos = obtenerUsuariosPorRol($conexion, 1);
            responderJSON($alumnos);
            break;
            
        case 'estadisticas_clases':
            $query = "SELECT 
                        c.id,
                        c.nombre,
                        c.cupo_maximo,
                        COUNT(i.id) as inscritos,
                        AVG(cal.calificacion) as promedio
                      FROM clases c
                      LEFT JOIN inscripciones i ON c.id = i.clase_id AND i.aprobada = 1
                      LEFT JOIN calificaciones cal ON i.clase_id = cal.clase_id AND i.alumno_id = cal.alumno_id
                      WHERE c.coordinador_id = $usuario_id
                      GROUP BY c.id";
            
            $resultado = mysqli_query($conexion, $query);
            $estadisticas = [];
            
            while ($row = mysqli_fetch_assoc($resultado)) {
                $estadisticas[] = $row;
            }
            
            responderJSON($estadisticas);
            break;
            
        case 'reporte_calificaciones':
            $clase_id = intval($_POST['clase_id'] ?? 0);
            
            // Verificar que la clase pertenece al coordinador
            $verificar = mysqli_query($conexion, "SELECT 1 FROM clases WHERE id = $clase_id AND coordinador_id = $usuario_id");
            if (mysqli_num_rows($verificar) == 0) {
                responderJSON([]);
                break;
            }
            
            $reporte = generarReporteCalificacionesClase($conexion, $clase_id);
            responderJSON($reporte);
            break;
            
        default:
            responderJSON(['ok' => false, 'msg' => 'Acción no permitida para Coordinador']);
    }
}

function procesarAccionDirector($conexion, $usuario_id, $accion) {
    switch ($accion) {
        case 'todas_clases':
            $query = "SELECT c.*, p.nombre as profesor, coord.nombre as coordinador, COUNT(i.id) as inscritos
                      FROM clases c
                      LEFT JOIN usuarios p ON c.profesor_id = p.id
                      LEFT JOIN usuarios coord ON c.coordinador_id = coord.id
                      LEFT JOIN inscripciones i ON c.id = i.clase_id AND i.aprobada = 1
                      GROUP BY c.id";
            
            $resultado = mysqli_query($conexion, $query);
            $clases = [];
            
            while ($row = mysqli_fetch_assoc($resultado)) {
                $clases[] = $row;
            }
            
            responderJSON($clases);
            break;
            
        case 'estadisticas_generales':
            $estadisticas = obtenerEstadisticasClases($conexion);
            $estadisticasAlumnos = obtenerEstadisticasAlumnos($conexion);
            
            responderJSON([
                'clases' => $estadisticas,
                'alumnos' => $estadisticasAlumnos
            ]);
            break;
            
        case 'usuarios_por_rol':
            $rol_id = intval($_POST['rol_id'] ?? 0);
            $usuarios = obtenerUsuariosPorRol($conexion, $rol_id);
            responderJSON($usuarios);
            break;
            
        case 'crear_usuario':
            $nombre = $_POST['nombre'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $rol_id = intval($_POST['rol_id'] ?? 0);
            
            $resultado = crearUsuario($conexion, $nombre, $email, $password, $rol_id);
            responderJSON($resultado);
            break;
            
        case 'editar_usuario':
            $usuario_id_editar = intval($_POST['usuario_id'] ?? 0);
            $datos = [];
            
            if (isset($_POST['nombre'])) $datos['nombre'] = $_POST['nombre'];
            if (isset($_POST['email'])) $datos['email'] = $_POST['email'];
            if (isset($_POST['password']) && !empty($_POST['password'])) $datos['password'] = $_POST['password'];
            if (isset($_POST['rol_id'])) $datos['rol_id'] = intval($_POST['rol_id']);
            
            $resultado = actualizarUsuario($conexion, $usuario_id_editar, $datos);
            responderJSON($resultado);
            break;
            
        case 'cambiar_estado_usuario':
            $usuario_id_cambiar = intval($_POST['usuario_id'] ?? 0);
            $activo = intval($_POST['activo'] ?? 0);
            
            $resultado = cambiarEstadoUsuario($conexion, $usuario_id_cambiar, $activo);
            responderJSON($resultado);
            break;
            
        case 'solicitudes_pendientes':
            $solicitudes = obtenerSolicitudesPendientes($conexion);
            responderJSON($solicitudes);
            break;
            
        case 'aprobar_solicitud':
            $solicitud_id = intval($_POST['solicitud_id'] ?? 0);
            $respuesta = $_POST['respuesta'] ?? '';
            
            $resultado = aprobarSolicitud($conexion, $solicitud_id, $respuesta);
            responderJSON($resultado);
            break;
            
        case 'rechazar_solicitud':
            $solicitud_id = intval($_POST['solicitud_id'] ?? 0);
            $respuesta = $_POST['respuesta'] ?? '';
            
            $resultado = rechazarSolicitud($conexion, $solicitud_id, $respuesta);
            responderJSON($resultado);
            break;
            
        case 'reporte_completo':
            $query = "SELECT 
                        'Clases' as tipo,
                        COUNT(*) as total
                      FROM clases
                      UNION ALL
                      SELECT 
                        'Alumnos' as tipo,
                        COUNT(*) as total
                      FROM usuarios WHERE rol_id = 1 AND activo = 1
                      UNION ALL
                      SELECT 
                        'Profesores' as tipo,
                        COUNT(*) as total
                      FROM usuarios WHERE rol_id = 2 AND activo = 1
                      UNION ALL
                      SELECT 
                        'Coordinadores' as tipo,
                        COUNT(*) as total
                      FROM usuarios WHERE rol_id = 3 AND activo = 1
                      UNION ALL
                      SELECT 
                        'Padres' as tipo,
                        COUNT(*) as total
                      FROM usuarios WHERE rol_id = 6 AND activo = 1";
            
            $resultado = mysqli_query($conexion, $query);
            $reporte = [];
            
            while ($row = mysqli_fetch_assoc($resultado)) {
                $reporte[] = $row;
            }
            
            responderJSON($reporte);
            break;
            
        default:
            responderJSON(['ok' => false, 'msg' => 'Acción no permitida para Director']);
    }
}

function procesarAccionAdmin($conexion, $usuario_id, $accion) {
    // El admin tiene acceso a todas las funciones del director más funciones adicionales
    if (in_array($accion, ['todas_clases', 'estadisticas_generales', 'usuarios_por_rol', 'crear_usuario', 'editar_usuario', 'cambiar_estado_usuario', 'solicitudes_pendientes', 'aprobar_solicitud', 'rechazar_solicitud', 'reporte_completo'])) {
        procesarAccionDirector($conexion, $usuario_id, $accion);
        return;
    }
    
    switch ($accion) {
        case 'backup_database':
            // Esta función requeriría implementación específica del servidor
            responderJSON(['ok' => false, 'msg' => 'Funcionalidad de backup no implementada']);
            break;
            
        case 'limpiar_logs':
            $query = "DELETE FROM registro_actividad WHERE fecha < DATE_SUB(NOW(), INTERVAL 30 DAY)";
            
            if (mysqli_query($conexion, $query)) {
                responderJSON(['ok' => true, 'msg' => 'Logs limpiados correctamente']);
            } else {
                responderJSON(['ok' => false, 'msg' => mysqli_error($conexion)]);
            }
            break;
            
        case 'configuracion_sistema':
            // Placeholder para configuraciones del sistema
            responderJSON(['ok' => false, 'msg' => 'Funcionalidad de configuración no implementada']);
            break;
            
        default:
            responderJSON(['ok' => false, 'msg' => 'Acción no permitida para Admin']);
    }
}

function procesarAccionPadre($conexion, $usuario_id, $accion) {
    switch ($accion) {
        case 'mis_hijos':
            $hijos = obtenerHijosPadre($conexion, $usuario_id);
            responderJSON($hijos);
            break;
            
        case 'vincular_hijo':
            $alumno_id = intval($_POST['alumno_id'] ?? 0);
            $parentesco = $_POST['parentesco'] ?? 'padre';
            
            $resultado = vincularHijo($conexion, $usuario_id, $alumno_id, $parentesco);
            responderJSON($resultado);
            break;
            
        case 'desvincular_hijo':
            $vinculo_id = intval($_POST['vinculo_id'] ?? 0);
            $resultado = desvincularHijo($conexion, $vinculo_id, $usuario_id);
            responderJSON($resultado);
            break;
            
        case 'calificaciones_hijo':
            $alumno_id = intval($_POST['alumno_id'] ?? 0);
            
            // Verificar que el alumno es hijo del padre
            $verificar = mysqli_query($conexion, "SELECT 1 FROM hijos WHERE padre_id = $usuario_id AND alumno_id = $alumno_id");
            if (mysqli_num_rows($verificar) == 0) {
                responderJSON([]);
                break;
            }
            
            $calificaciones = obtenerCalificacionesPorAlumno($conexion, $alumno_id);
            responderJSON($calificaciones);
            break;
            
        case 'reportes_hijo':
            $alumno_id = intval($_POST['alumno_id'] ?? 0);
            
            // Verificar que el alumno es hijo del padre
            $verificar = mysqli_query($conexion, "SELECT 1 FROM hijos WHERE padre_id = $usuario_id AND alumno_id = $alumno_id");
            if (mysqli_num_rows($verificar) == 0) {
                responderJSON([]);
                break;
            }
            
            $reportes = obtenerReportesPorAlumno($conexion, $alumno_id);
            responderJSON($reportes);
            break;
            
        case 'profesores_hijos':
            $query = "SELECT DISTINCT p.id, p.nombre
                      FROM hijos h
                      JOIN inscripciones i ON h.alumno_id = i.alumno_id
                      JOIN clases c ON i.clase_id = c.id
                      JOIN usuarios p ON c.profesor_id = p.id
                      WHERE h.padre_id = $usuario_id AND i.aprobada = 1 AND p.activo = 1";
            
            $resultado = mysqli_query($conexion, $query);
            $profesores = [];
            
            while ($row = mysqli_fetch_assoc($resultado)) {
                $profesores[] = $row;
            }
            
            responderJSON($profesores);
            break;
            
        case 'mensajes_profesor':
            $profesor_id = intval($_POST['profesor_id'] ?? 0);
            $mensajes = obtenerMensajesPadreProfesor($conexion, $usuario_id, $profesor_id);
            responderJSON($mensajes);
            break;
            
        case 'enviar_mensaje_profesor':
            $profesor_id = intval($_POST['profesor_id'] ?? 0);
            $mensaje = $_POST['mensaje'] ?? '';
            $resultado = enviarMensaje($conexion, $usuario_id, $profesor_id, $mensaje, 'padre');
            responderJSON($resultado);
            break;
            
        case 'avisos_hijos':
            $query = "SELECT DISTINCT a.*, c.nombre as clase, p.nombre as profesor
                      FROM hijos h
                      JOIN inscripciones i ON h.alumno_id = i.alumno_id
                      JOIN avisos a ON i.clase_id = a.clase_id
                      JOIN clases c ON a.clase_id = c.id
                      JOIN usuarios p ON a.profesor_id = p.id
                      WHERE h.padre_id = $usuario_id AND i.aprobada = 1
                      ORDER BY a.fecha DESC";
            
            $resultado = mysqli_query($conexion, $query);
            $avisos = [];
            
            while ($row = mysqli_fetch_assoc($resultado)) {
                $avisos[] = $row;
            }
            
            responderJSON($avisos);
            break;
            
        case 'comentar_aviso':
            $aviso_id = intval($_POST['aviso_id'] ?? 0);
            $mensaje = $_POST['mensaje'] ?? '';
            
            // Verificar que el padre tiene acceso al aviso (a través de sus hijos)
            $verificar = mysqli_query($conexion, "
                SELECT 1 FROM avisos a
                JOIN inscripciones i ON a.clase_id = i.clase_id
                JOIN hijos h ON i.alumno_id = h.alumno_id
                WHERE a.id = $aviso_id AND h.padre_id = $usuario_id AND i.aprobada = 1
            ");
            
            if (mysqli_num_rows($verificar) == 0) {
                responderJSON(['ok' => false, 'msg' => 'No tienes acceso a este aviso']);
                break;
            }
            
            $resultado = crearComentarioAviso($conexion, $aviso_id, $usuario_id, $mensaje);
            responderJSON($resultado);
            break;
            
        case 'comentarios_aviso':
            $aviso_id = intval($_POST['aviso_id'] ?? 0);
            
            // Verificar que el padre tiene acceso al aviso
            $verificar = mysqli_query($conexion, "
                SELECT 1 FROM avisos a
                JOIN inscripciones i ON a.clase_id = i.clase_id
                JOIN hijos h ON i.alumno_id = h.alumno_id
                WHERE a.id = $aviso_id AND h.padre_id = $usuario_id AND i.aprobada = 1
            ");
            
            if (mysqli_num_rows($verificar) == 0) {
                responderJSON([]);
                break;
            }
            
            $comentarios = obtenerComentariosAviso($conexion, $aviso_id);
            responderJSON($comentarios);
            break;
            
        default:
            responderJSON(['ok' => false, 'msg' => 'Acción no permitida para Padre']);
    }
}

?>
