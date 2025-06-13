<?php
session_start();
require_once __DIR__ . '/crudUtils.php';
$conexion = getConexion();

$user_id = $_SESSION['usuario_id'] ?? 0;
$user_rol = $_SESSION['usuario_rol'] ?? 0;
$accion = $_POST['accion'] ?? '';

// --- CRUD DE PROFESOR (ADMIN, DIRECTOR, COORDINADOR) ---
if ($accion === 'crear') {
    if (is_admin() || is_director() || is_coordinador()) {
        $nombre = postParam($conexion, 'nombre');
        $email  = postParam($conexion, 'email');
        $password = postParam($conexion, 'password');
        $ok = insertarUsuario($conexion, $nombre, $email, $password, 2);
        jsonExit(['ok' => $ok], $conexion);
    } else {
        http_response_code(403); exit('No autorizado');
    }
}

if ($accion === 'eliminar') {
    $id = intParam('id');
    if (is_admin() || is_director()) {
        $ok = mysqli_query($conexion, "UPDATE usuarios SET activo=0 WHERE id=$id AND rol_id=2");
        jsonExit(['ok' => $ok], $conexion);
    } elseif (is_coordinador()) {
        $aprobada = mysqli_query($conexion, "SELECT estado FROM solicitudes_accion WHERE tipo='eliminar_profesor' AND usuario_id=$id AND solicitante_id=$user_id AND estado='aprobada' LIMIT 1");
        if (mysqli_num_rows($aprobada) > 0) {
            $ok = mysqli_query($conexion, "UPDATE usuarios SET activo=0 WHERE id=$id AND rol_id=2");
            mysqli_query($conexion, "DELETE FROM solicitudes_accion WHERE tipo='eliminar_profesor' AND usuario_id=$id AND solicitante_id=$user_id");
            jsonExit(['ok' => $ok], $conexion);
        } else {
            http_response_code(403); exit('Necesita autorización del director');
        }
    } else {
        http_response_code(403); exit('No autorizado');
    }
}

if ($accion === 'perfil') {
    $res = mysqli_query($conexion, "SELECT nombre, email FROM usuarios WHERE id=$user_id");
    $datos = mysqli_fetch_assoc($res) ?: [];
    echo json_encode(['ok'=>true, 'data'=>$datos]);
    cerrarBD($conexion); exit;
}
// ---------------- FUNCIONALIDAD DE PROFESOR ----------------
if (is_profesor()) {
    // === CLASES DEL PROFESOR ===
    if ($accion === 'mis_clases') {
        $query = "SELECT id, nombre, periodo FROM clases WHERE profesor_id = $user_id";
        $out = fetchQueryAssoc($conexion, $query);
        jsonExit($out, $conexion);
    }

    // === ALUMNOS DE UNA CLASE ===
    if ($accion === 'alumnos_clase') {
        $clase_id = intParam('clase_id');
        $qv = mysqli_query($conexion, "SELECT 1 FROM clases WHERE id=$clase_id AND profesor_id=$user_id");
        if (mysqli_num_rows($qv) == 0) {
            jsonExit([], $conexion);
        }
        $query = "SELECT u.id, u.nombre, u.email
                  FROM inscripciones i
                  JOIN usuarios u ON i.alumno_id = u.id
                  WHERE i.clase_id = $clase_id AND i.aprobada = 1 AND u.rol_id = 1";
        $out = fetchQueryAssoc($conexion, $query);
        jsonExit($out, $conexion);
    }

    // === CALIFICACIONES DE UNA CLASE ===
    if ($accion === 'get_calificaciones') {
        $clase_id = intParam('clase_id');
        $query = "SELECT u.id as alumno_id, u.nombre as alumno, c.calificacion, c.observacion
                  FROM inscripciones i
                  JOIN usuarios u ON i.alumno_id = u.id
                  LEFT JOIN calificaciones c ON c.alumno_id = u.id AND c.clase_id = i.clase_id
                  WHERE i.clase_id = $clase_id AND i.aprobada = 1 AND u.rol_id = 1";
        $out = fetchQueryAssoc($conexion, $query);
        jsonExit($out, $conexion);
    }

    if ($accion === 'set_calificacion') {
        $alumno_id = intParam('alumno_id');
        $clase_id = intParam('clase_id');
        $calificacion = floatval($_POST['calificacion']);
        $observacion = postParam($conexion, 'observacion');
        $existe = mysqli_query($conexion, "SELECT 1 FROM calificaciones WHERE alumno_id=$alumno_id AND clase_id=$clase_id");
        if (mysqli_num_rows($existe) > 0) {
            $q = mysqli_query($conexion,
                "UPDATE calificaciones SET calificacion=$calificacion, observacion='$observacion' WHERE alumno_id=$alumno_id AND clase_id=$clase_id"
            );
        } else {
            $q = mysqli_query($conexion,
                "INSERT INTO calificaciones (alumno_id, clase_id, calificacion, observacion) VALUES ($alumno_id, $clase_id, $calificacion, '$observacion')"
            );
        }
        jsonExit(['ok' => $q, 'msg' => mysqli_error($conexion)], $conexion);
    }

    if ($accion === 'borrar_calificacion') {
        $alumno_id = intParam('alumno_id');
        $clase_id = intParam('clase_id');
        $q = mysqli_query($conexion, "DELETE FROM calificaciones WHERE alumno_id=$alumno_id AND clase_id=$clase_id");
        jsonExit(['ok' => $q], $conexion);
    }

    // === CRUD TAREAS ===
    if ($accion === 'listar_tareas') {
        $clase_id = intParam('clase_id');
        $query = "SELECT id, titulo, descripcion, fecha_entrega, archivo FROM tareas WHERE clase_id=$clase_id ORDER BY fecha_entrega DESC";
        $out = fetchQueryAssoc($conexion, $query);
        jsonExit($out, $conexion);
    }

    if ($accion === 'crear_tarea') {
        $clase_id = intParam('clase_id');
        $titulo = postParam($conexion, 'titulo');
        $descripcion = postParam($conexion, 'descripcion');
        $fecha_entrega = postParam($conexion, 'fecha_entrega');
        $archivo = postParam($conexion, 'archivo');
        $q = mysqli_query($conexion,
            "INSERT INTO tareas (clase_id, titulo, descripcion, fecha_entrega, archivo)
             VALUES ($clase_id, '$titulo', '$descripcion', '$fecha_entrega', '$archivo')"
        );
        jsonExit(['ok' => $q], $conexion);
    }

    if ($accion === 'borrar_tarea') {
        $tarea_id = intParam('tarea_id');
        $q = mysqli_query($conexion, "DELETE FROM tareas WHERE id=$tarea_id");
        jsonExit(['ok' => $q], $conexion);
    }

    // === AVISOS A PADRES ===
    if ($accion === 'mandar_aviso') {
        $clase_id = intParam('clase_id');
        $titulo = postParam($conexion, 'titulo');
        $mensaje = postParam($conexion, 'mensaje');
        $fecha = date('Y-m-d H:i:s');
        $q = mysqli_query($conexion,
            "INSERT INTO avisos (clase_id, profesor_id, titulo, mensaje, fecha)
             VALUES ($clase_id, $user_id, '$titulo', '$mensaje', '$fecha')"
        );
        jsonExit(['ok' => $q], $conexion);
    }

    if ($accion === 'listar_avisos') {
        $clase_id = intParam('clase_id');
        $query = "SELECT id, titulo, mensaje, fecha FROM avisos WHERE clase_id=$clase_id ORDER BY fecha DESC";
        $out = fetchQueryAssoc($conexion, $query);
        jsonExit($out, $conexion);
    }

    if ($accion === 'borrar_aviso') {
        $aviso_id = intParam('aviso_id');
        $q = mysqli_query($conexion, "DELETE FROM avisos WHERE id=$aviso_id AND profesor_id=$user_id");
        jsonExit(['ok' => $q], $conexion);
    }

    // === REPORTES SOBRE ALUMNOS ===
    if ($accion === 'listar_reportes') {
        $clase_id = intParam('clase_id');
        $query = "SELECT r.id, r.alumno_id, u.nombre as alumno, r.titulo, r.descripcion, r.fecha
                  FROM reportes r
                  JOIN usuarios u ON r.alumno_id = u.id
                  WHERE r.clase_id=$clase_id AND r.profesor_id=$user_id
                  ORDER BY r.fecha DESC";
        $out = fetchQueryAssoc($conexion, $query);
        jsonExit($out, $conexion);
    }

    if ($accion === 'crear_reporte') {
        $clase_id = intParam('clase_id');
        $alumno_id = intParam('alumno_id');
        $titulo = postParam($conexion, 'titulo');
        $descripcion = postParam($conexion, 'descripcion');
        $fecha = date('Y-m-d H:i:s');
        $q = mysqli_query($conexion,
            "INSERT INTO reportes (clase_id, alumno_id, profesor_id, titulo, descripcion, fecha)
             VALUES ($clase_id, $alumno_id, $user_id, '$titulo', '$descripcion', '$fecha')"
        );
        jsonExit(['ok' => $q], $conexion);
    }

    if ($accion === 'borrar_reporte') {
        $reporte_id = intParam('reporte_id');
        $q = mysqli_query($conexion, "DELETE FROM reportes WHERE id=$reporte_id AND profesor_id=$user_id");
        jsonExit(['ok' => $q], $conexion);
    }

    // === MENSAJES PROFESOR-PADRE ===
    if ($accion === 'padres_mis_alumnos') {
        $query = "
            SELECT DISTINCT u.id, u.nombre
            FROM clases c
            JOIN inscripciones i ON i.clase_id = c.id
            JOIN hijos h ON h.alumno_id = i.alumno_id
            JOIN usuarios u ON u.id = h.padre_id
            WHERE c.profesor_id = $user_id AND i.aprobada=1 AND u.rol_id=6 AND u.activo=1
        ";
        $out = fetchQueryAssoc($conexion, $query);
        jsonExit($out, $conexion);
    }

    if ($accion === 'listar_mensajes') {
        $padre_id = intParam('padre_id');
        $query = "
            SELECT id, mensaje, fecha, de_quien
            FROM mensajes_padre_profesor
            WHERE profesor_id=$user_id AND padre_id=$padre_id
            ORDER BY fecha ASC
        ";
        $out = fetchQueryAssoc($conexion, $query);
        jsonExit($out, $conexion);
    }

    if ($accion === 'enviar_mensaje_padre') {
        $padre_id = intParam('padre_id');
        $mensaje = postParam($conexion, 'mensaje');
        $fecha = date('Y-m-d H:i:s');
        $ok = mysqli_query($conexion, "INSERT INTO mensajes_padre_profesor (padre_id, profesor_id, mensaje, fecha, de_quien) VALUES ($padre_id, $user_id, '$mensaje', '$fecha', 'profesor')");
        jsonExit(['ok' => $ok], $conexion);
    }
}

errorResp('Acción no permitida', $conexion);
?>