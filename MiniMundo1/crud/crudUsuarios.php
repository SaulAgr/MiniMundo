<?php
session_start();
require_once '../includes/conexion.php';
$conexion = conectarBD();

$user_id = $_SESSION['usuario_id'] ?? 0;
$user_rol = $_SESSION['usuario_rol'] ?? 0;
$accion = $_POST['accion'] ?? '';

function is_admin()      { return $_SESSION['usuario_rol'] == 6; }
function is_director()   { return $_SESSION['usuario_rol'] == 5; }
function is_coordinador(){ return $_SESSION['usuario_rol'] == 4; }

// === LISTAR USUARIOS ===
if ($accion === 'listar') {
    if (is_admin()) {
        $res = mysqli_query($conexion, "SELECT u.id, u.nombre, u.email, u.activo, u.rol_id, r.nombre as rol FROM usuarios u JOIN roles r ON u.rol_id = r.id");
    } elseif (is_director()) {
        // No mostrar admin ni director
        $res = mysqli_query($conexion, "SELECT u.id, u.nombre, u.email, u.activo, u.rol_id, r.nombre as rol FROM usuarios u JOIN roles r ON u.rol_id = r.id WHERE u.rol_id NOT IN (5,6)");
    } elseif (is_coordinador()) {
        // Solo profesores y alumnos
        $res = mysqli_query($conexion, "SELECT u.id, u.nombre, u.email, u.activo, u.rol_id, r.nombre as rol FROM usuarios u JOIN roles r ON u.rol_id = r.id WHERE u.rol_id IN (2,3)");
    } else { http_response_code(403); exit('No autorizado'); }
    $arr = [];
    while($row = mysqli_fetch_assoc($res)) $arr[] = $row;
    echo json_encode($arr);
}

// === PERFIL DEL USUARIO ACTUAL ===
if ($accion === 'perfil') {
    $res = mysqli_query($conexion, "SELECT nombre, email FROM usuarios WHERE id=$user_id");
    echo json_encode(mysqli_fetch_assoc($res));
}
if ($accion === 'editar_perfil') {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $email = mysqli_real_escape_string($conexion, $_POST['email']);
    $ok = mysqli_query($conexion, "UPDATE usuarios SET nombre='$nombre', email='$email' WHERE id=$user_id");
    echo json_encode(['ok'=>$ok]);
}

// === CREAR, EDITAR, ELIMINAR: SOLO ADMIN/DIRECTOR pueden crear admin/director ===
if ($accion === 'crear') {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $email = mysqli_real_escape_string($conexion, $_POST['email']);
    $password = mysqli_real_escape_string($conexion, $_POST['password']);
    $rol_id = intval($_POST['rol_id']);
    // director no puede crear admin/director, coordinador solo profs/alumnos
    if (is_admin()) {
        $ok = mysqli_query($conexion, "INSERT INTO usuarios (nombre, email, password, rol_id, activo) VALUES ('$nombre', '$email', '$password', $rol_id, 1)");
    } elseif (is_director() && !in_array($rol_id, [5,6])) {
        $ok = mysqli_query($conexion, "INSERT INTO usuarios (nombre, email, password, rol_id, activo) VALUES ('$nombre', '$email', '$password', $rol_id, 1)");
    } elseif (is_coordinador() && in_array($rol_id, [2,3])) {
        $ok = mysqli_query($conexion, "INSERT INTO usuarios (nombre, email, password, rol_id, activo) VALUES ('$nombre', '$email', '$password', $rol_id, 1)");
    } else { http_response_code(403); exit('No autorizado'); }
    echo json_encode(['ok'=>$ok]);
}
if ($accion === 'editar') {
    $id = intval($_POST['id']);
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $email = mysqli_real_escape_string($conexion, $_POST['email']);
    $rol_id = intval($_POST['rol_id']);
    if (is_admin()) {
        $ok = mysqli_query($conexion, "UPDATE usuarios SET nombre='$nombre', email='$email', rol_id=$rol_id WHERE id=$id");
    } elseif (is_director()) {
        // No puede editar admin/director
        $q = mysqli_query($conexion, "SELECT rol_id FROM usuarios WHERE id=$id");
        $row = mysqli_fetch_assoc($q);
        if (in_array($row['rol_id'], [5,6]) || in_array($rol_id, [5,6])) { http_response_code(403); exit('No permitido'); }
        $ok = mysqli_query($conexion, "UPDATE usuarios SET nombre='$nombre', email='$email', rol_id=$rol_id WHERE id=$id");
    } elseif (is_coordinador()) {
        $q = mysqli_query($conexion, "SELECT rol_id FROM usuarios WHERE id=$id");
        $row = mysqli_fetch_assoc($q);
        if (!in_array($row['rol_id'], [2,3]) || !in_array($rol_id, [2,3])) { http_response_code(403); exit('No permitido'); }
        $ok = mysqli_query($conexion, "UPDATE usuarios SET nombre='$nombre', email='$email', rol_id=$rol_id WHERE id=$id");
    } else { http_response_code(403); exit('No autorizado'); }
    echo json_encode(['ok'=>$ok]);
}
if ($accion === 'baja') {
    $id = intval($_POST['id']);
    if (is_admin()) {
        $ok = mysqli_query($conexion, "UPDATE usuarios SET activo=0 WHERE id=$id");
    } elseif (is_director()) {
        $q = mysqli_query($conexion, "SELECT rol_id FROM usuarios WHERE id=$id");
        $row = mysqli_fetch_assoc($q);
        if (in_array($row['rol_id'], [5,6])) { http_response_code(403); exit('No permitido'); }
        $ok = mysqli_query($conexion, "UPDATE usuarios SET activo=0 WHERE id=$id");
    } elseif (is_coordinador()) {
        $q = mysqli_query($conexion, "SELECT rol_id FROM usuarios WHERE id=$id");
        $row = mysqli_fetch_assoc($q);
        if (!in_array($row['rol_id'], [2,3])) { http_response_code(403); exit('No permitido'); }
        $ok = mysqli_query($conexion, "UPDATE usuarios SET activo=0 WHERE id=$id");
    } else { http_response_code(403); exit('No autorizado'); }
    echo json_encode(['ok'=>$ok]);
}

cerrarBD($conexion);