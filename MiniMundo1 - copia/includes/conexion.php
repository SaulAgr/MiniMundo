<?php
function conectarBD() {
    $conexion = mysqli_connect("localhost", "root", "", "sistema_educativo");
    if (!$conexion) {
        die("Error al conectar: " . mysqli_connect_error());
    }
    return $conexion;
}
function cerrarBD($conexion) {
    mysqli_close($conexion);
}
?>