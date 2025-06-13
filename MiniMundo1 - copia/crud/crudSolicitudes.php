<?php
session_start();
require_once __DIR__ . '/crudUtils.php';
$conexion = getConexion();

requireRole([5,6]); // Solo admin (6) y director (5)

$accion = $_POST['accion'] ?? '';

if ($accion === 'listar') {
    $query = "SELECT s.*, u.nombre as profesor, c.nombre as coord
        FROM solicitudes_accion s
        JOIN usuarios u ON s.usuario_id=u.id
        JOIN usuarios c ON s.solicitante_id=c.id
        WHERE s.estado='pendiente'";
    $out = fetchQueryAssoc($conexion, $query);
    jsonExit($out, $conexion);
}

if ($accion === 'aprobar') {
    $id = intParam('id');
    $ok = mysqli_query($conexion, "UPDATE solicitudes_accion SET estado='aprobada' WHERE id=$id");
    jsonExit(['ok'=>$ok], $conexion);
}

if ($accion === 'rechazar') {
    $id = intParam('id');
    $ok = mysqli_query($conexion, "UPDATE solicitudes_accion SET estado='rechazada' WHERE id=$id");
    jsonExit(['ok'=>$ok], $conexion);
}

cerrarBD($conexion);
?>