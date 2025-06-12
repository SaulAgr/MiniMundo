<?php
session_start();
require_once '../includes/conexion.php';
$conexion = conectarBD();

$user_id = $_SESSION['usuario_id'] ?? 0;
$user_rol = $_SESSION['usuario_rol'] ?? 0;
$accion = $_POST['accion'] ?? '';

function is_admin()      { return $_SESSION['usuario_rol'] == 5; }
function is_director()   { return $_SESSION['usuario_rol'] == 4; }
function is_coordinador(){ return $_SESSION['usuario_rol'] == 3; }
function is_profesor()   { return $_SESSION['usuario_rol'] == 2; }

// --- CRUD DE PROFESOR (ADMIN, DIRECTOR, COORDINADOR) ---
if ($accion === 'crear') {
    if (is_admin() || is_director() || is_coordinador()) {
        $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
        $email  = mysqli_real_escape_string($conexion, $_POST['email']);
        $password = mysqli_real_escape_string($conexion, $_POST['password']);
        $ok = mysqli_query($conexion, "INSERT INTO usuarios (nombre, email, password, rol_id, activo) VALUES ('$nombre', '$email', '$password', 2, 1)");
        echo json_encode(['ok'=>$ok]);
        cerrarBD($conexion); exit;
    } else { http_response_code(403); exit('No autorizado'); }
}

if ($accion === 'eliminar') {
    $id = intval($_POST['id']);
    if (is_admin() || is_director()) {
        $ok = mysqli_query($conexion, "UPDATE usuarios SET activo=0 WHERE id=$id AND rol_id=2");
        echo json_encode(['ok'=>$ok]);
    } elseif (is_coordinador()) {
        $aprobada = mysqli_query($conexion, "SELECT estado FROM solicitudes_accion WHERE tipo='eliminar_profesor' AND usuario_id=$id AND solicitante_id=$user_id AND estado='aprobada' LIMIT 1");
        if (mysqli_num_rows($aprobada) > 0) {
            $ok = mysqli_query($conexion, "UPDATE usuarios SET activo=0 WHERE id=$id AND rol_id=2");
            mysqli_query($conexion, "DELETE FROM solicitudes_accion WHERE tipo='eliminar_profesor' AND usuario_id=$id AND solicitante_id=$user_id");
            echo json_encode(['ok'=>$ok]);
        } else {
            http_response_code(403); exit('Necesita autorización del director');
        }
    } else {
        http_response_code(403); exit('No autorizado');
    }
    cerrarBD($conexion); exit;
}

// ---------------- FUNCIONALIDAD DE PROFESOR ----------------
if (is_profesor()) {
    // === CLASES DEL PROFESOR ===
    if ($accion === 'mis_clases') {
        $q = mysqli_query($conexion, "SELECT id, nombre, periodo FROM clases WHERE profesor_id = $user_id");
        $out = [];
        while($row = mysqli_fetch_assoc($q)) $out[] = $row;
        echo json_encode($out); cerrarBD($conexion); exit;
    }

    // === ALUMNOS DE UNA CLASE ===
    if ($accion === 'alumnos_clase') {
        $clase_id = intval($_POST['clase_id']);
        $qv = mysqli_query($conexion, "SELECT 1 FROM clases WHERE id=$clase_id AND profesor_id=$user_id");
        if (mysqli_num_rows($qv)==0) { echo json_encode([]); cerrarBD($conexion); exit; }
        $q = mysqli_query($conexion,
            "SELECT u.id, u.nombre, u.email
             FROM inscripciones i
             JOIN usuarios u ON i.alumno_id = u.id
             WHERE i.clase_id = $clase_id AND i.aprobada = 1 AND u.rol_id = 1"
        );
        $out = [];
        while($row = mysqli_fetch_assoc($q)) $out[] = $row;
        echo json_encode($out); cerrarBD($conexion); exit;
    }

    // === CALIFICACIONES DE UNA CLASE ===
    if ($accion === 'get_calificaciones') {
        $clase_id = intval($_POST['clase_id']);
        $q = mysqli_query($conexion,
            "SELECT u.id as alumno_id, u.nombre as alumno, c.calificacion, c.observacion
             FROM inscripciones i
             JOIN usuarios u ON i.alumno_id = u.id
             LEFT JOIN calificaciones c ON c.alumno_id = u.id AND c.clase_id = i.clase_id
             WHERE i.clase_id = $clase_id AND i.aprobada = 1 AND u.rol_id = 1"
        );
        $out = [];
        while($row = mysqli_fetch_assoc($q)) $out[] = $row;
        echo json_encode($out); cerrarBD($conexion); exit;
    }

    if ($accion === 'set_calificacion') {
        $alumno_id = intval($_POST['alumno_id']);
        $clase_id = intval($_POST['clase_id']);
        $calificacion = floatval($_POST['calificacion']);
        $observacion = mysqli_real_escape_string($conexion, $_POST['observacion'] ?? '');
        $q = mysqli_query($conexion,
            "INSERT INTO calificaciones (alumno_id, clase_id, calificacion, observacion)
             VALUES ($alumno_id, $clase_id, $calificacion, '$observacion')
             ON DUPLICATE KEY UPDATE calificacion=$calificacion, observacion='$observacion'"
        );
        echo json_encode(['ok'=>$q]); cerrarBD($conexion); exit;
    }

    if ($accion === 'borrar_calificacion') {
        $alumno_id = intval($_POST['alumno_id']);
        $clase_id = intval($_POST['clase_id']);
        $q = mysqli_query($conexion, "DELETE FROM calificaciones WHERE alumno_id=$alumno_id AND clase_id=$clase_id");
        echo json_encode(['ok'=>$q]); cerrarBD($conexion); exit;
    }

    // === CRUD TAREAS ===
    if ($accion === 'listar_tareas') {
        $clase_id = intval($_POST['clase_id']);
        $q = mysqli_query($conexion, "SELECT id, titulo, descripcion, fecha_entrega, archivo FROM tareas WHERE clase_id=$clase_id ORDER BY fecha_entrega DESC");
        $out = [];
        while($row = mysqli_fetch_assoc($q)) $out[] = $row;
        echo json_encode($out); cerrarBD($conexion); exit;
    }

    if ($accion === 'crear_tarea') {
        $clase_id = intval($_POST['clase_id']);
        $titulo = mysqli_real_escape_string($conexion, $_POST['titulo']);
        $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
        $fecha_entrega = mysqli_real_escape_string($conexion, $_POST['fecha_entrega']);
        $archivo = mysqli_real_escape_string($conexion, $_POST['archivo'] ?? '');
        $q = mysqli_query($conexion,
            "INSERT INTO tareas (clase_id, titulo, descripcion, fecha_entrega, archivo)
             VALUES ($clase_id, '$titulo', '$descripcion', '$fecha_entrega', '$archivo')"
        );
        echo json_encode(['ok'=>$q]); cerrarBD($conexion); exit;
    }

    if ($accion === 'borrar_tarea') {
        $tarea_id = intval($_POST['tarea_id']);
        $q = mysqli_query($conexion, "DELETE FROM tareas WHERE id=$tarea_id");
        echo json_encode(['ok'=>$q]); cerrarBD($conexion); exit;
    }

    // === AVISOS A PADRES ===
    if ($accion === 'mandar_aviso') {
        $clase_id = intval($_POST['clase_id']);
        $titulo = mysqli_real_escape_string($conexion, $_POST['titulo']);
        $mensaje = mysqli_real_escape_string($conexion, $_POST['mensaje']);
        $fecha = date('Y-m-d H:i:s');
        $q = mysqli_query($conexion,
            "INSERT INTO avisos (clase_id, profesor_id, titulo, mensaje, fecha)
             VALUES ($clase_id, $user_id, '$titulo', '$mensaje', '$fecha')"
        );
        echo json_encode(['ok'=>$q]); cerrarBD($conexion); exit;
    }

    if ($accion === 'listar_avisos') {
        $clase_id = intval($_POST['clase_id']);
        $q = mysqli_query($conexion,
            "SELECT id, titulo, mensaje, fecha FROM avisos WHERE clase_id=$clase_id ORDER BY fecha DESC"
        );
        $out = [];
        while($row = mysqli_fetch_assoc($q)) $out[] = $row;
        echo json_encode($out); cerrarBD($conexion); exit;
    }

    if ($accion === 'borrar_aviso') {
        $aviso_id = intval($_POST['aviso_id']);
        $q = mysqli_query($conexion, "DELETE FROM avisos WHERE id=$aviso_id AND profesor_id=$user_id");
        echo json_encode(['ok'=>$q]); cerrarBD($conexion); exit;
    }

    // === REPORTES SOBRE ALUMNOS ===
    if ($accion === 'listar_reportes') {
        $clase_id = intval($_POST['clase_id']);
        $q = mysqli_query($conexion,
            "SELECT r.id, r.alumno_id, u.nombre as alumno, r.titulo, r.descripcion, r.fecha
             FROM reportes r
             JOIN usuarios u ON r.alumno_id = u.id
             WHERE r.clase_id=$clase_id AND r.profesor_id=$user_id
             ORDER BY r.fecha DESC"
        );
        $out = [];
        while($row = mysqli_fetch_assoc($q)) $out[] = $row;
        echo json_encode($out); cerrarBD($conexion); exit;
    }

    if ($accion === 'crear_reporte') {
        $clase_id = intval($_POST['clase_id']);
        $alumno_id = intval($_POST['alumno_id']);
        $titulo = mysqli_real_escape_string($conexion, $_POST['titulo']);
        $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
        $fecha = date('Y-m-d H:i:s');
        $q = mysqli_query($conexion,
            "INSERT INTO reportes (clase_id, alumno_id, profesor_id, titulo, descripcion, fecha)
             VALUES ($clase_id, $alumno_id, $user_id, '$titulo', '$descripcion', '$fecha')"
        );
        echo json_encode(['ok'=>$q]); cerrarBD($conexion); exit;
    }

    if ($accion === 'borrar_reporte') {
        $reporte_id = intval($_POST['reporte_id']);
        $q = mysqli_query($conexion, "DELETE FROM reportes WHERE id=$reporte_id AND profesor_id=$user_id");
        echo json_encode(['ok'=>$q]); cerrarBD($conexion); exit;
    }

    // === MENSAJES PROFESOR-PADRE ===
    if ($accion === 'padres_mis_alumnos') {
        $q = mysqli_query($conexion, "
            SELECT DISTINCT u.id, u.nombre
            FROM clases c
            JOIN inscripciones i ON i.clase_id = c.id
            JOIN hijos h ON h.alumno_id = i.alumno_id
            JOIN usuarios u ON u.id = h.padre_id
            WHERE c.profesor_id = $user_id AND i.aprobada=1 AND u.rol_id=6 AND u.activo=1
        ");
        $out = [];
        while($row = mysqli_fetch_assoc($q)) $out[] = $row;
        echo json_encode($out); cerrarBD($conexion); exit;
    }

    if ($accion === 'listar_mensajes') {
        $padre_id = intval($_POST['padre_id']);
        $q = mysqli_query($conexion,"
            SELECT id, mensaje, fecha, de_quien
            FROM mensajes_padre_profesor
            WHERE profesor_id=$user_id AND padre_id=$padre_id
            ORDER BY fecha ASC
        ");
        $out = [];
        while($row = mysqli_fetch_assoc($q)) $out[] = $row;
        echo json_encode($out); cerrarBD($conexion); exit;
    }

    if ($accion === 'enviar_mensaje_padre') {
        $padre_id = intval($_POST['padre_id']);
        $mensaje = mysqli_real_escape_string($conexion, $_POST['mensaje']);
        $fecha = date('Y-m-d H:i:s');
        $ok = mysqli_query($conexion, "INSERT INTO mensajes_padre_profesor (padre_id, profesor_id, mensaje, fecha, de_quien) VALUES ($padre_id, $user_id, '$mensaje', '$fecha', 'profesor')");
        echo json_encode(['ok'=>$ok]); cerrarBD($conexion); exit;
    }
}

echo json_encode(['ok'=>false, 'msg'=>'Acción no permitida']);
cerrarBD($conexion);