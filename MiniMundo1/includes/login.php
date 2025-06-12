<?php
session_start();
require_once "conexion.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $correo = $_POST['correo'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';

    $conexion = conectarBD();
    $correo = mysqli_real_escape_string($conexion, $correo);

    // Busca el usuario activo
    $sql = "SELECT * FROM usuarios WHERE email = '$correo' AND activo = 1 LIMIT 1";
    $res = mysqli_query($conexion, $sql);

    if ($res && mysqli_num_rows($res) === 1) {
        $usuario = mysqli_fetch_assoc($res);
        if ($contrasena === $usuario['password']) {
            // Login correcto - asigno sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_rol'] = $usuario['rol_id'];

            // Obtener nombre del rol
            $rol_id = $usuario['rol_id'];
            $sqlRol = "SELECT nombre FROM roles WHERE id = $rol_id LIMIT 1";
            $resRol = mysqli_query($conexion, $sqlRol);
            $nombreRol = '';
            if ($resRol && mysqli_num_rows($resRol) === 1) {
                $rowRol = mysqli_fetch_assoc($resRol);
                // normaliza a minusculas sin espacios 
                $nombreRol = strtolower(trim($rowRol['nombre']));
                $_SESSION['usuario_rol_nombre'] = $nombreRol;
            }

            cerrarBD($conexion);

            switch ($nombreRol) {
                case 'alumno':
                    header("Location: ../Vistas/alumno.php");
                    break;
                case 'padre':
                    header("Location: ../Vistas/padre.php");
                    break;
                case 'profesor':
                    header("Location: ../Vistas/profesor.php");
                    break;
                case 'coordinador':
                    header("Location: ../Vistas/coordinador.php");
                    break;
                case 'director':
                    header("Location: ../Vistas/director.php");
                    break;
                case 'admin':
                    header("Location: ../Vistas/admin.php");
                    break;
                default:
                    header("Location: ../public.php?error=Rol%20no%20válido");
            }
            exit;
        }
    }
    cerrarBD($conexion);
    header("Location: ../public.php?error=Correo%20o%20contrase%C3%B1a%20incorrectos");
    exit;
} else {
    header("Location: ../public.php");
    exit;
}
?>