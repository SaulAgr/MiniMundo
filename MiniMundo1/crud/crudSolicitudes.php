<?php
session_start();
require_once '../includes/conexion.php';
$conexion = conectarBD();
if ($_SESSION['usuario_rol'] != 5 && $_SESSION['usuario_rol'] != 6) { http_response_code(403); exit('No autorizado'); }

$accion = $_POST['accion'] ?? '';
if ($accion === 'listar') {
    $res = mysqli_query($conexion, "SELECT s.*, u.nombre as profesor, c.nombre as coord
        FROM solicitudes_accion s
        JOIN usuarios u ON s.usuario_id=u.id
        JOIN usuarios c ON s.solicitante_id=c.id
        WHERE s.estado='pendiente'");
    $out = [];
    while($row = mysqli_fetch_assoc($res)) $out[] = $row;
    echo json_encode($out);
}
if ($accion === 'aprobar') {
    $id = intval($_POST['id']);
    $ok = mysqli_query($conexion, "UPDATE solicitudes_accion SET estado='aprobada' WHERE id=$id");
    echo json_encode(['ok'=>$ok]);
}
if ($accion === 'rechazar') {
    $id = intval($_POST['id']);
    $ok = mysqli_query($conexion, "UPDATE solicitudes_accion SET estado='rechazada' WHERE id=$id");
    echo json_encode(['ok'=>$ok]);
}
cerrarBD($conexion);