<?php
session_start();
require_once __DIR__ . '/crudUtils.php';
$conexion = getConexion();
$accion = $_POST['accion'] ?? '';
$usuario_rol = $_SESSION['usuario_rol'] ?? null;

requireRole([4,5]); // Solo director (4) y admin (5)

if ($accion === 'listar') {
    $query = "
        SELECT r.id, a.nombre as alumno, r.comentario, r.fecha
        FROM reportes r
        JOIN usuarios a ON r.alumno_id = a.id
        ORDER BY r.fecha DESC
    ";
    $arr = fetchQueryAssoc($conexion, $query);
    jsonExit($arr, $conexion);
}

errorResp('Acción no permitida', $conexion);
?>