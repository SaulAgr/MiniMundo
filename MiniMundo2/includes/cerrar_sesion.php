<?php
session_start();

// Registrar actividad de cierre de sesión si hay una sesión activa
if (isset($_SESSION['usuario_id'])) {
    require_once 'funciones.php';
    $conexion = conectarBD();
    registrarActividad($conexion, $_SESSION['usuario_id'], 'Cierre de sesión');
    cerrarBD($conexion);
}

// Destruir la sesión
session_destroy();

// Redirigir al login
header('Location: ../public.php');
exit;
?>
