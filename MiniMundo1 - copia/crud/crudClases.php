<?php
session_start();
require_once __DIR__ . '/crudUtils.php';
$conexion = getConexion();
$accion = $_POST['accion'] ?? '';
$usuario_rol = $_SESSION['usuario_rol'] ?? null;

// Solo Director o Admin
requireRole([4,5]);

if ($accion === 'listar') {
    $query = "
        SELECT c.id, c.nombre, c.descripcion, c.periodo, 
            IFNULL(p.nombre, 'Sin asignar') as profesor, c.profesor_id
        FROM clases c
        LEFT JOIN usuarios p ON c.profesor_id = p.id
        ORDER BY c.id DESC
    ";
    $out = fetchQueryAssoc($conexion, $query);
    jsonExit($out, $conexion);
}

if ($accion === 'crear') {
    $nombre = postParam($conexion, 'nombre');
    $periodo = postParam($conexion, 'periodo');
    $profesor_id = intParam('profesor_id');
    $desc = postParam($conexion, 'descripcion');
    $ok = mysqli_query($conexion, "INSERT INTO clases (nombre, periodo, profesor_id, descripcion) VALUES ('$nombre', '$periodo', $profesor_id, '$desc')");
    jsonExit(['ok'=>$ok], $conexion);
}

if ($accion === 'editar') {
    $id = intParam('id');
    $nombre = postParam($conexion, 'nombre');
    $periodo = postParam($conexion, 'periodo');
    $profesor_id = intParam('profesor_id');
    $desc = postParam($conexion, 'descripcion');
    $ok = mysqli_query($conexion, "UPDATE clases SET nombre='$nombre', periodo='$periodo', profesor_id=$profesor_id, descripcion='$desc' WHERE id=$id");
    jsonExit(['ok'=>$ok], $conexion);
}

if ($accion === 'eliminar') {
    $id = intParam('id');
    $ok = mysqli_query($conexion, "DELETE FROM clases WHERE id=$id");
    jsonExit(['ok'=>$ok], $conexion);
}

errorResp('Acción no permitida', $conexion);
?>