<?php
require_once "conexion.php";
if (session_status() === PHP_SESSION_NONE) session_start();

function loginUsuario($email, $clave) {
    $conexion = conectarBD();
    $email = mysqli_real_escape_string($conexion, $email);
    $sql = "SELECT * FROM usuarios WHERE email = '$email' AND activo = 1 LIMIT 1";
    $res = mysqli_query($conexion, $sql);

    if (!$res) { echo mysqli_error($conexion); }

    if ($res && mysqli_num_rows($res) == 1) {
        $row = mysqli_fetch_assoc($res);
        var_dump($email, $clave, $row['password']); 
        if ($clave === $row['password']) {
            $_SESSION['usuario_id'] = $row['id'];
            $_SESSION['usuario_nombre'] = $row['nombre'];
            $_SESSION['usuario_email'] = $row['email'];
            $_SESSION['usuario_rol'] = $row['rol_id'];
            cerrarBD($conexion);
            return true;
        }
    }
    cerrarBD($conexion);
    return false;
}

?>