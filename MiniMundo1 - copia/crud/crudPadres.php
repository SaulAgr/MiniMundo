<?php
session_start();
require_once __DIR__ . '/crudUtils.php';
$conexion = getConexion();

$user_id = $_SESSION['usuario_id'] ?? 0;
$user_rol = $_SESSION['usuario_rol'] ?? 0;
$accion = $_POST['accion'] ?? '';

// El rol del padre suele ser 6 (ajústalo si tu base usa otro número)
requireRole([6]);

// 1. Obtener hijos del padre (asume tabla hijos: id, padre_id, alumno_id)
function hijos_del_padre($conexion, $padre_id) {
    $q = mysqli_query($conexion, "SELECT u.id, u.nombre FROM hijos h JOIN usuarios u ON h.alumno_id=u.id WHERE h.padre_id=$padre_id AND u.activo=1");
    $hijos = [];
    while($row = mysqli_fetch_assoc($q)) $hijos[] = $row;
    return $hijos;
}

// 2. Avisos de profesores para hijos
if ($accion === 'avisos_hijos') {
    $hijos = hijos_del_padre($conexion, $user_id);
    $avisos = [];
    foreach($hijos as $hijo) {
        $query = "
            SELECT a.id, '{$hijo['nombre']}' as hijo, c.nombre as clase, p.nombre as profesor, a.titulo, a.mensaje, a.fecha
            FROM inscripciones i
            JOIN clases c ON i.clase_id=c.id
            JOIN avisos a ON a.clase_id=c.id
            JOIN usuarios p ON c.profesor_id=p.id
            WHERE i.alumno_id={$hijo['id']} AND i.aprobada=1
            ORDER BY a.fecha DESC
        ";
        $avisos = array_merge($avisos, fetchQueryAssoc($conexion, $query));
    }
    jsonExit($avisos, $conexion);
}

// 3. Comentar aviso
if ($accion === 'comentar_aviso') {
    $aviso_id = intParam('aviso_id');
    $mensaje = postParam($conexion, 'mensaje');
    $fecha = date('Y-m-d H:i:s');
    $ok = mysqli_query($conexion, "INSERT INTO avisos_comentarios (aviso_id, padre_id, mensaje, fecha) VALUES ($aviso_id, $user_id, '$mensaje', '$fecha')");
    jsonExit(['ok'=>$ok], $conexion);
}

// 4. Listar comentarios sobre un aviso
if ($accion === 'listar_comentarios_aviso') {
    $aviso_id = intParam('aviso_id');
    $query = "SELECT id, mensaje, fecha FROM avisos_comentarios WHERE aviso_id=$aviso_id AND padre_id=$user_id ORDER BY fecha DESC";
    $out = fetchQueryAssoc($conexion, $query);
    jsonExit($out, $conexion);
}

// 5. Borrar comentario
if ($accion === 'borrar_comentario') {
    $id = intParam('id');
    $ok = mysqli_query($conexion, "DELETE FROM avisos_comentarios WHERE id=$id AND padre_id=$user_id");
    jsonExit(['ok'=>$ok], $conexion);
}

// 6. Progreso de hijos
if ($accion === 'progreso_hijos') {
    $hijos = hijos_del_padre($conexion, $user_id);
    $out = [];
    foreach($hijos as $hijo) {
        $query = "
            SELECT '{$hijo['nombre']}' as hijo, c.nombre as clase, p.nombre as profesor, cal.calificacion, cal.observacion
            FROM inscripciones i
            JOIN clases c ON i.clase_id=c.id
            JOIN usuarios p ON c.profesor_id=p.id
            LEFT JOIN calificaciones cal ON cal.alumno_id=i.alumno_id AND cal.clase_id=i.clase_id
            WHERE i.alumno_id={$hijo['id']} AND i.aprobada=1
        ";
        $out = array_merge($out, fetchQueryAssoc($conexion, $query));
    }
    jsonExit($out, $conexion);
}

// 7. Profesores de hijos para mensajes
if ($accion === 'profesores_hijos') {
    $hijos = hijos_del_padre($conexion, $user_id);
    $profes = [];
    foreach($hijos as $hijo) {
        $query = "
            SELECT DISTINCT p.id, p.nombre, c.nombre as clase
            FROM inscripciones i 
            JOIN clases c ON i.clase_id=c.id
            JOIN usuarios p ON c.profesor_id=p.id
            WHERE i.alumno_id={$hijo['id']} AND i.aprobada=1
        ";
        $profes = array_merge($profes, fetchQueryAssoc($conexion, $query));
    }
    jsonExit($profes, $conexion);
}

// 8. Listar mensajes con profesor
if ($accion === 'listar_mensajes') {
    $profesor_id = intParam('profesor_id');
    $query = "
        SELECT id, mensaje, fecha, IF(de_quien='padre','Padre','Profesor') as de_quien
        FROM mensajes_padre_profesor
        WHERE padre_id=$user_id AND profesor_id=$profesor_id
        ORDER BY fecha ASC
    ";
    $out = fetchQueryAssoc($conexion, $query);
    jsonExit($out, $conexion);
}

// 9. Enviar mensaje
if ($accion === 'enviar_mensaje') {
    $profesor_id = intParam('profesor_id');
    $mensaje = postParam($conexion, 'mensaje');
    $fecha = date('Y-m-d H:i:s');
    $ok = mysqli_query($conexion, "INSERT INTO mensajes_padre_profesor (padre_id, profesor_id, mensaje, fecha, de_quien) VALUES ($user_id, $profesor_id, '$mensaje', '$fecha', 'padre')");
    jsonExit(['ok'=>$ok], $conexion);
}

// 10. Borrar mensaje
if ($accion === 'borrar_mensaje') {
    $id = intParam('id');
    $ok = mysqli_query($conexion, "DELETE FROM mensajes_padre_profesor WHERE id=$id AND padre_id=$user_id");
    jsonExit(['ok'=>$ok], $conexion);
}
if ($accion === 'perfil') {
    $res = mysqli_query($conexion, "SELECT nombre, email FROM usuarios WHERE id=$user_id");
    $datos = mysqli_fetch_assoc($res) ?: [];
    echo json_encode(['ok'=>true, 'data'=>$datos]);
    cerrarBD($conexion); exit;
}
errorResp('Acción no permitida', $conexion);
?>