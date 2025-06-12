<?php
session_start();
require_once '../includes/conexion.php';
$conexion = conectarBD();
$accion = $_POST['accion'] ?? '';
$usuario_rol = $_SESSION['usuario_rol'] ?? null;
if (!in_array($usuario_rol, [4,5])) { http_response_code(403); exit('No autorizado'); }

if ($accion === 'listar') {
    $res = mysqli_query($conexion, "
        SELECT r.id, a.nombre as alumno, r.comentario, r.fecha
        FROM reportes r
        JOIN usuarios a ON r.alumno_id = a.id
        ORDER BY r.fecha DESC
    ");
    $arr = [];
    while($row = mysqli_fetch_assoc($res)) $arr[] = $row;
    echo json_encode($arr); cerrarBD($conexion); exit;
}
echo json_encode(['ok'=>false, 'msg'=>'Acción no permitida']);
cerrarBD($conexion);
?>