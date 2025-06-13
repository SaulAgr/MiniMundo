<?php
session_start();
require_once __DIR__ . '/crudUtils.php';
$conexion = getConexion();
$accion = $_POST['accion'] ?? '';
$usuario_rol = $_SESSION['usuario_rol'] ?? null;

requireRole([4,5]); // Solo director (4) y admin (5)

if ($accion === 'listar') {
    $query = "
        SELECT i.id, a.nombre as alumno, c.nombre as clase
        FROM inscripciones i
        JOIN usuarios a ON i.alumno_id = a.id
        JOIN clases c ON i.clase_id = c.id
        WHERE i.aprobada = 0
        ORDER BY i.fecha_inscripcion DESC
    ";
    $arr = fetchQueryAssoc($conexion, $query);
    jsonExit($arr, $conexion);
}

if ($accion === 'aprobar') {
    $id = intParam('id');
    $ok = mysqli_query($conexion, "UPDATE inscripciones SET aprobada=1, fecha_aprobacion=NOW() WHERE id=$id");
    jsonExit(['ok'=>$ok], $conexion);
}

if ($accion === 'rechazar') {
    $id = intParam('id');
    $ok = mysqli_query($conexion, "DELETE FROM inscripciones WHERE id=$id");
    jsonExit(['ok'=>$ok], $conexion);
}

errorResp('Acción no permitida', $conexion);
?>