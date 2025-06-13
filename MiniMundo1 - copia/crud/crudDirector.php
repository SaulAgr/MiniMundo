<?php
session_start();
require_once __DIR__ . '/crudUtils.php';
$conexion = getConexion();

$accion = $_POST['accion'] ?? '';
$usuario_rol = $_SESSION['usuario_rol'] ?? null;
$user_id = $_SESSION['usuario_id'] ?? null;

// Solo director (4) o admin (6) pueden usar este panel
requireRole([4, 6]);
$rol_prohibidos = [4, 6]; // No puede crear/editar/borrar director ni admin

// --- NUEVO ENDPOINT PARA OBTENER ROLES (para el select del modal) ---
if ($accion === 'roles') {
    $roles = getSelectList($conexion, "roles", "", "id, nombre", "ORDER BY id");
    jsonExit($roles, $conexion);
}

// ---- DIRECTOR: LISTAR SOLICITUDES PENDIENTES ----
if ($accion === 'listar_solicitudes') {
    requireRole([4]);
    $pendientes = array_filter(leerSolicitudes(), fn($s) => $s['estado'] === 'pendiente');
    jsonExit(array_values($pendientes), $conexion);
}

// ---- DIRECTOR: APROBAR SOLICITUD ----
if ($accion === 'aprobar_solicitud') {
    requireRole([4]);
    $id = intParam('id');
    $solicitudes = leerSolicitudes();
    foreach ($solicitudes as $s) {
        if ($s['id'] == $id && $s['estado'] === 'pendiente') {
            if ($s['tipo'] === 'eliminar_clase') {
                mysqli_query($conexion, "DELETE FROM clases WHERE id=" . intval($s['entidad_id']));
            }
            if ($s['tipo'] === 'editar_clase') {
                $d = $s['datos'];
                mysqli_query(
                    $conexion,
                    "UPDATE clases SET nombre='" . mysqli_real_escape_string($conexion, $d['nombre']) . "', periodo='" . mysqli_real_escape_string($conexion, $d['periodo']) . "', profesor_id=" . intval($d['profesor_id']) . ", cupo_maximo=" . intval($d['cup_maximo']) . " WHERE id=" . intval($s['entidad_id'])
                );
            }
            actualizarSolicitud($id, 'aprobada', $user_id, 'Aprobado por director');
            jsonExit(['ok' => true], $conexion);
        }
    }
    errorResp('Solicitud no encontrada o ya procesada', $conexion);
}

// ---- DIRECTOR: RECHAZAR SOLICITUD ----
if ($accion === 'rechazar_solicitud') {
    requireRole([4]);
    $id = intParam('id');
    actualizarSolicitud($id, 'rechazada', $user_id, $_POST['respuesta'] ?? 'Rechazado');
    jsonExit(['ok' => true], $conexion);
}

// --- LISTAR USUARIOS (excepto admin/director) ---
if ($accion === 'listar') {
    $query = "SELECT u.id, u.nombre, u.email, u.activo, u.rol_id, r.nombre as rol 
              FROM usuarios u JOIN roles r ON u.rol_id = r.id";
    $usuarios = fetchQueryAssoc($conexion, $query);
    jsonExit($usuarios, $conexion);
}

// --- CREAR USUARIO (excepto admin/director) ---
if ($accion === 'crear' && in_array($usuario_rol, [4, 6])) {
    $rol_id = intParam('rol_id');
    if (in_array($rol_id, $rol_prohibidos)) {
        errorResp('No puedes crear usuarios admin o director.', $conexion);
    }
    $nombre = postParam($conexion, 'nombre');
    $email = postParam($conexion, 'email');
    $password = postParam($conexion, 'password');
    $ok = insertarUsuario($conexion, $nombre, $email, $password, $rol_id);
    jsonExit(['ok' => $ok], $conexion);
}

// --- EDITAR USUARIO (excepto admin/director) ---
if ($accion === 'editar' && in_array($usuario_rol, [4, 6])) {
    $id = intParam('id');
    $query = "SELECT rol_id FROM usuarios WHERE id = $id";
    $res = mysqli_query($conexion, $query);
    if ($row = mysqli_fetch_assoc($res)) {
        if (in_array($row['rol_id'], $rol_prohibidos)) {
            errorResp('No puedes modificar admin o director.', $conexion);
        }
    }
    $nombre = postParam($conexion, 'nombre');
    $email = postParam($conexion, 'email');
    $rol_id = intParam('rol_id');
    if (in_array($rol_id, $rol_prohibidos)) {
        errorResp('No puedes cambiar a admin/director.', $conexion);
    }
    $query = "UPDATE usuarios SET nombre='$nombre', email='$email', rol_id=$rol_id WHERE id=$id";
    $ok = mysqli_query($conexion, $query);
    jsonExit(['ok' => $ok], $conexion);
}

// --- DAR DE BAJA USUARIO (excepto admin/director) ---
if ($accion === 'baja' && in_array($usuario_rol, [4, 6])) {
    $id = intParam('id');
    $query = "SELECT rol_id FROM usuarios WHERE id = $id";
    $res = mysqli_query($conexion, $query);
    if ($row = mysqli_fetch_assoc($res)) {
        if (in_array($row['rol_id'], $rol_prohibidos)) {
            errorResp('No puedes dar de baja admin o director.', $conexion);
        }
    }
    $query = "UPDATE usuarios SET activo=0 WHERE id=$id";
    $ok = mysqli_query($conexion, $query);
    jsonExit(['ok' => $ok], $conexion);
}

errorResp('Acción no permitida', $conexion);
?>