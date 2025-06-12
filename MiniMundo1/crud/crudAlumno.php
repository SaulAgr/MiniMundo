<?php
session_start();
require_once '../includes/conexion.php';
$conexion = conectarBD();

$user_id = $_SESSION['usuario_id'] ?? 0;
$user_rol = $_SESSION['usuario_rol'] ?? 0;
$accion = $_POST['accion'] ?? '';

if (!$user_id || !$user_rol) { http_response_code(403); exit('No autorizado'); }

// === FUNCIONES SEGÚN ACCIÓN ===

// 1. Ver clases inscritas (solo alumno)
if ($accion === 'mis_clases' && $user_rol == 1) {
    $q = mysqli_query($conexion, "
        SELECT c.id, c.nombre, c.periodo, u.nombre as profesor
        FROM inscripciones i
        JOIN clases c ON i.clase_id = c.id
        JOIN usuarios u ON c.profesor_id = u.id
        WHERE i.alumno_id=$user_id AND i.aprobada=1
    ");
    $out = [];
    while($row = mysqli_fetch_assoc($q)) $out[] = $row;
    echo json_encode($out);
    cerrarBD($conexion); exit;
}

// 2. Ver materiales de una clase (solo si inscrito y aprobado)
if ($accion === 'materiales' && $user_rol == 1) {
    $clase_id = intval($_POST['clase_id']);
    $qch = mysqli_query($conexion, "SELECT 1 FROM inscripciones WHERE alumno_id=$user_id AND clase_id=$clase_id AND aprobada=1");
    if (!$qch || mysqli_num_rows($qch) == 0) {
        echo json_encode([]); cerrarBD($conexion); exit;
    }
    $q = mysqli_query($conexion, "SELECT titulo, archivo FROM recursos WHERE clase_id=$clase_id");
    $out = [];
    while($row = mysqli_fetch_assoc($q)) $out = $row;
    echo json_encode($out);
    cerrarBD($conexion); exit;
}

// 3. Baja de clase (solo alumno inscrito)
if ($accion === 'baja_clase' && $user_rol == 1) {
    $clase_id = intval($_POST['clase_id']);
    $qch = mysqli_query($conexion, "SELECT 1 FROM inscripciones WHERE alumno_id=$user_id AND clase_id=$clase_id");
    if (!$qch || mysqli_num_rows($qch) == 0) {
        echo json_encode(['ok'=>false, 'msg'=>'No estás inscrito en esta clase']);
        cerrarBD($conexion); exit;
    }
    $ok = mysqli_query($conexion, "DELETE FROM inscripciones WHERE alumno_id=$user_id AND clase_id=$clase_id");
    echo json_encode(['ok'=>$ok]);
    cerrarBD($conexion); exit;
}

// 4. Clases disponibles para inscribirse (solo alumno)
if ($accion === 'clases_disponibles' && $user_rol == 1) {
    $q = mysqli_query($conexion, "
        SELECT c.id, c.nombre, c.periodo, u.nombre as profesor
        FROM clases c
        JOIN usuarios u ON c.profesor_id = u.id
        WHERE c.id NOT IN (SELECT clase_id FROM inscripciones WHERE alumno_id=$user_id)
    ");
    $out = [];
    while($row = mysqli_fetch_assoc($q)) $out[] = $row;
    echo json_encode($out);
    cerrarBD($conexion); exit;
}

// 5. Solicitar inscripción a una clase (solo alumno)
if ($accion === 'inscribirse' && $user_rol == 1) {
    $clase_id = intval($_POST['clase_id']);
    $q = mysqli_query($conexion, "SELECT id FROM inscripciones WHERE alumno_id=$user_id AND clase_id=$clase_id");
    if (mysqli_num_rows($q) > 0) {
        echo json_encode(['ok'=>false, 'msg'=>'Ya solicitaste inscripción o ya estás inscrito.']);
        cerrarBD($conexion); exit;
    }
    $ok = mysqli_query($conexion, "INSERT INTO inscripciones (alumno_id, clase_id, aprobada) VALUES ($user_id, $clase_id, 0)");
    echo json_encode(['ok'=>$ok]);
    cerrarBD($conexion); exit;
}

// 6. Estado de inscripciones (solo alumno)
if ($accion === 'estado_inscripciones' && $user_rol == 1) {
    $q = mysqli_query($conexion, "
        SELECT c.nombre as clase, c.periodo, u.nombre as profesor, 
        CASE 
            WHEN i.aprobada = 1 THEN 'Inscrito'
            WHEN i.aprobada = 0 THEN 'Pendiente'
            ELSE 'Desconocido'
        END as estado
        FROM inscripciones i
        JOIN clases c ON i.clase_id = c.id
        JOIN usuarios u ON c.profesor_id = u.id
        WHERE i.alumno_id=$user_id
    ");
    $out = [];
    while($row = mysqli_fetch_assoc($q)) $out[] = $row;
    echo json_encode($out);
    cerrarBD($conexion); exit;
}

// 7. Ver tareas de clases inscritas (solo alumno)
if ($accion === 'tareas' && $user_rol == 1) {
    $q = mysqli_query($conexion, "
        SELECT t.id, t.titulo, t.descripcion, t.fecha_entrega, t.archivo, c.nombre as clase
        FROM inscripciones i
        JOIN tareas t ON t.clase_id = i.clase_id
        JOIN clases c ON c.id = i.clase_id
        WHERE i.alumno_id = $user_id AND i.aprobada = 1
        ORDER BY t.fecha_entrega DESC
    ");
    $out = [];
    while($row = mysqli_fetch_assoc($q)) $out[] = $row;
    echo json_encode($out);
    cerrarBD($conexion); exit;
}

// 8. Ver calificaciones (solo alumno)
if ($accion === 'calificaciones' && $user_rol == 1) {
    $q = mysqli_query($conexion, "
        SELECT c.nombre as clase, calif.calificacion, calif.observacion
        FROM inscripciones i
        JOIN clases c ON c.id = i.clase_id
        LEFT JOIN calificaciones calif ON calif.alumno_id = i.alumno_id AND calif.clase_id = i.clase_id
        WHERE i.alumno_id = $user_id AND i.aprobada = 1
    ");
    $out = [];
    while($row = mysqli_fetch_assoc($q)) $out[] = $row;
    echo json_encode($out);
    cerrarBD($conexion); exit;
}

// 9. Ver avisos de clases inscritas (solo alumno)
if ($accion === 'avisos' && $user_rol == 1) {
    $q = mysqli_query($conexion,"
        SELECT a.titulo, a.mensaje, a.fecha, c.nombre as clase
        FROM inscripciones i
        JOIN avisos a ON a.clase_id = i.clase_id
        JOIN clases c ON c.id = i.clase_id
        WHERE i.alumno_id = $user_id AND i.aprobada = 1
        ORDER BY a.fecha DESC
    ");
    $out = [];
    while($row = mysqli_fetch_assoc($q)) $out[] = $row;
    echo json_encode($out);
    cerrarBD($conexion); exit;
}

// 10. Ver reportes (solo alumno)
if ($accion === 'reportes' && $user_rol == 1) {
    $q = mysqli_query($conexion,"
        SELECT r.titulo, r.descripcion, r.fecha, c.nombre as clase
        FROM reportes r
        JOIN clases c ON c.id = r.clase_id
        WHERE r.alumno_id = $user_id
        ORDER BY r.fecha DESC
    ");
    $out = [];
    while($row = mysqli_fetch_assoc($q)) $out[] = $row;
    echo json_encode($out);
    cerrarBD($conexion); exit;
}

// 11. Ver y editar perfil del alumno (solo alumno)
if ($accion === 'perfil' && $user_rol == 1) {
    $q = mysqli_query($conexion, "SELECT nombre, email FROM usuarios WHERE id=$user_id");
    $perfil = mysqli_fetch_assoc($q) ?: [];
    echo json_encode($perfil);
    cerrarBD($conexion); exit;
}
if ($accion === 'editar_perfil' && $user_rol == 1) {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre'] ?? '');
    $email = mysqli_real_escape_string($conexion, $_POST['email'] ?? '');
    // Validar email único
    $q = mysqli_query($conexion, "SELECT id FROM usuarios WHERE email='$email' AND id<>$user_id");
    if (mysqli_num_rows($q) > 0) {
        echo json_encode(['ok'=>false, 'msg'=>'El correo ya está en uso']);
        cerrarBD($conexion); exit;
    }
    $ok = mysqli_query($conexion, "UPDATE usuarios SET nombre='$nombre', email='$email' WHERE id=$user_id");
    if ($ok) {
        $_SESSION['usuario_nombre'] = $nombre;
    }
    echo json_encode(['ok'=>$ok]);
    cerrarBD($conexion); exit;
}

// ADMIN / DIRECTOR / COORDINADOR: Baja alumno (desactiva, solo si rol es alumno)
if ($accion === 'eliminar' && in_array($user_rol, [6,5,4])) {
    $id = intval($_POST['id']);
    $q = mysqli_query($conexion, "SELECT rol_id FROM usuarios WHERE id=$id");
    $row = mysqli_fetch_assoc($q);
    if (!$row || $row['rol_id'] != 1) {
        echo json_encode(['ok'=>false, 'msg'=>'Solo se pueden dar de baja alumnos']);
        cerrarBD($conexion); exit;
    }
    $ok = mysqli_query($conexion, "UPDATE usuarios SET activo=0 WHERE id=$id");
    echo json_encode(['ok'=>$ok]);
    cerrarBD($conexion); exit;
}

// ADMIN / DIRECTOR / COORDINADOR: Reactivar alumno (opcional)
if ($accion === 'reactivar' && in_array($user_rol, [6,5,4])) {
    $id = intval($_POST['id']);
    $q = mysqli_query($conexion, "SELECT rol_id FROM usuarios WHERE id=$id");
    $row = mysqli_fetch_assoc($q);
    if (!$row || $row['rol_id'] != 1) {
        echo json_encode(['ok'=>false, 'msg'=>'Solo se pueden reactivar alumnos']);
        cerrarBD($conexion); exit;
    }
    $ok = mysqli_query($conexion, "UPDATE usuarios SET activo=1 WHERE id=$id");
    echo json_encode(['ok'=>$ok]);
    cerrarBD($conexion); exit;
}

echo json_encode(['ok'=>false, 'msg'=>'Acción no permitida']);
cerrarBD($conexion);
?>