<?php
session_start();
require_once '../includes/conexion.php';
$conexion = conectarBD();
$accion = $_POST['accion'] ?? '';
$usuario_rol = $_SESSION['usuario_rol'] ?? null;

// Solo Director o Admin
if (!in_array($usuario_rol, [4,5])) { http_response_code(403); exit('No autorizado'); }

if ($accion === 'listar') {
    $res = mysqli_query($conexion, "
        SELECT c.id, c.nombre, c.descripcion, c.periodo, 
            IFNULL(p.nombre, 'Sin asignar') as profesor, c.profesor_id
        FROM clases c
        LEFT JOIN usuarios p ON c.profesor_id = p.id
        ORDER BY c.id DESC
    ");
    $out = [];
    while($r = mysqli_fetch_assoc($res)) $out[] = $r;
    echo json_encode($out); cerrarBD($conexion); exit;
}

if ($accion === 'crear') {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $periodo = mysqli_real_escape_string($conexion, $_POST['periodo']);
    $profesor_id = intval($_POST['profesor_id']);
    $desc = mysqli_real_escape_string($conexion, $_POST['descripcion'] ?? '');
    $ok = mysqli_query($conexion, "INSERT INTO clases (nombre, periodo, profesor_id, descripcion) VALUES ('$nombre', '$periodo', $profesor_id, '$desc')");
    echo json_encode(['ok'=>$ok]); cerrarBD($conexion); exit;
}

if ($accion === 'editar') {
    $id = intval($_POST['id']);
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $periodo = mysqli_real_escape_string($conexion, $_POST['periodo']);
    $profesor_id = intval($_POST['profesor_id']);
    $desc = mysqli_real_escape_string($conexion, $_POST['descripcion'] ?? '');
    $ok = mysqli_query($conexion, "UPDATE clases SET nombre='$nombre', periodo='$periodo', profesor_id=$profesor_id, descripcion='$desc' WHERE id=$id");
    echo json_encode(['ok'=>$ok]); cerrarBD($conexion); exit;
}

if ($accion === 'eliminar') {
    $id = intval($_POST['id']);
    $ok = mysqli_query($conexion, "DELETE FROM clases WHERE id=$id");
    echo json_encode(['ok'=>$ok]); cerrarBD($conexion); exit;
}

echo json_encode(['ok'=>false, 'msg'=>'Acción no permitida']);
cerrarBD($conexion);
?>