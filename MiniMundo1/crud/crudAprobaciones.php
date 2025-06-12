<?php
session_start();
require_once '../includes/conexion.php';
$conexion = conectarBD();
$accion = $_POST['accion'] ?? '';
$usuario_rol = $_SESSION['usuario_rol'] ?? null;
if (!in_array($usuario_rol, [4,5])) { http_response_code(403); exit('No autorizado'); }

if ($accion === 'listar') {
    $res = mysqli_query($conexion, "
        SELECT i.id, a.nombre as alumno, c.nombre as clase
        FROM inscripciones i
        JOIN usuarios a ON i.alumno_id = a.id
        JOIN clases c ON i.clase_id = c.id
        WHERE i.aprobada = 0
        ORDER BY i.fecha_inscripcion DESC
    ");
    $arr = [];
    while($row = mysqli_fetch_assoc($res)) $arr[] = $row;
    echo json_encode($arr); cerrarBD($conexion); exit;
}

if ($accion === 'aprobar') {
    $id = intval($_POST['id']);
    $ok = mysqli_query($conexion, "UPDATE inscripciones SET aprobada=1, fecha_aprobacion=NOW() WHERE id=$id");
    echo json_encode(['ok'=>$ok]); cerrarBD($conexion); exit;
}

if ($accion === 'rechazar') {
    $id = intval($_POST['id']);
    $ok = mysqli_query($conexion, "DELETE FROM inscripciones WHERE id=$id");
    echo json_encode(['ok'=>$ok]); cerrarBD($conexion); exit;
}

echo json_encode(['ok'=>false, 'msg'=>'Acción no permitida']);
cerrarBD($conexion);
?>