<?php
session_start();
require_once '../includes/conexion.php';
$conexion = conectarBD();

$accion = $_POST['accion'] ?? '';
$usuario_rol = $_SESSION['usuario_rol'] ?? null;

// Solo director (4) o admin (6) pueden usar este panel
if (!in_array($usuario_rol, [4,6])) {
    http_response_code(403); exit("No autorizado");
}
$rol_prohibidos = [4,6]; // No puede crear/editar/borrar director ni admin

// --- NUEVO ENDPOINT PARA OBTENER ROLES (para el select del modal) ---
if ($accion === 'roles') {
    $res = mysqli_query($conexion, "SELECT id, nombre FROM roles ORDER BY id");
    $roles = [];
    while($r = mysqli_fetch_assoc($res)) $roles[] = $r;
    echo json_encode($roles); cerrarBD($conexion); exit;
}

// ---- DIRECTOR: LISTAR SOLICITUDES PENDIENTES ----
if ($accion === 'listar_solicitudes') {
    if ($user_rol != 4) { http_response_code(403); exit('No autorizado'); }
    $pendientes = array_filter(leerSolicitudes(), fn($s) => $s['estado'] === 'pendiente');
    echo json_encode(array_values($pendientes)); cerrarBD($conexion); exit;
}

// ---- DIRECTOR: APROBAR SOLICITUD ----
if ($accion === 'aprobar_solicitud') {
    if ($user_rol != 4) { http_response_code(403); exit('No autorizado'); }
    $id = intval($_POST['id']);
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
            echo json_encode(['ok'=>true]);
            cerrarBD($conexion); exit;
        }
    }
    echo json_encode(['ok'=>false, 'msg'=>'Solicitud no encontrada o ya procesada']);
    cerrarBD($conexion); exit;
}

// ---- DIRECTOR: RECHAZAR SOLICITUD ----
if ($accion === 'rechazar_solicitud') {
    if ($user_rol != 4) { http_response_code(403); exit('No autorizado'); }
    $id = intval($_POST['id']);
    actualizarSolicitud($id, 'rechazada', $user_id, $_POST['respuesta'] ?? 'Rechazado');
    echo json_encode(['ok'=>true]);
    cerrarBD($conexion); exit;
}

// --- LISTAR USUARIOS (excepto admin/director) ---
if ($accion === 'listar') {
    $query = "SELECT u.id, u.nombre, u.email, u.activo, u.rol_id, r.nombre as rol 
              FROM usuarios u JOIN roles r ON u.rol_id = r.id";
    $res = mysqli_query($conexion, $query);
    $usuarios = [];
    while($row = mysqli_fetch_assoc($res)) $usuarios[] = $row;
    echo json_encode($usuarios); cerrarBD($conexion); exit;
}

// --- CREAR USUARIO (excepto admin/director) ---
if ($accion === 'crear' && in_array($usuario_rol, [4,6])) {
    $rol_id = intval($_POST['rol_id']);
    if (in_array($rol_id, $rol_prohibidos)) {
        echo json_encode(['ok'=>false,'msg'=>'No puedes crear usuarios admin o director.']); cerrarBD($conexion); exit;
    }
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $email = mysqli_real_escape_string($conexion, $_POST['email']);
    $password = mysqli_real_escape_string($conexion, $_POST['password']);
    $query = "INSERT INTO usuarios (nombre, email, password, rol_id, activo) VALUES ('$nombre', '$email', '$password', $rol_id, 1)";
    $ok = mysqli_query($conexion, $query);
    echo json_encode(['ok' => $ok]); cerrarBD($conexion); exit;
}

// --- EDITAR USUARIO (excepto admin/director) ---
if ($accion === 'editar' && in_array($usuario_rol, [4,6])) {
    $id = intval($_POST['id']);
    $query = "SELECT rol_id FROM usuarios WHERE id = $id";
    $res = mysqli_query($conexion, $query);
    if ($row = mysqli_fetch_assoc($res)) {
        if (in_array($row['rol_id'], $rol_prohibidos)) {
            echo json_encode(['ok'=>false,'msg'=>'No puedes modificar admin o director.']); cerrarBD($conexion); exit;
        }
    }
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $email = mysqli_real_escape_string($conexion, $_POST['email']);
    $rol_id = intval($_POST['rol_id']);
    if (in_array($rol_id, $rol_prohibidos)) {
        echo json_encode(['ok'=>false,'msg'=>'No puedes cambiar a admin/director.']); cerrarBD($conexion); exit;
    }
    $query = "UPDATE usuarios SET nombre='$nombre', email='$email', rol_id=$rol_id WHERE id=$id";
    $ok = mysqli_query($conexion, $query);
    echo json_encode(['ok' => $ok]); cerrarBD($conexion); exit;
}

// --- DAR DE BAJA USUARIO (excepto admin/director) ---
if ($accion === 'baja' && in_array($usuario_rol, [4,6])) {
    $id = intval($_POST['id']);
    $query = "SELECT rol_id FROM usuarios WHERE id = $id";
    $res = mysqli_query($conexion, $query);
    if ($row = mysqli_fetch_assoc($res)) {
        if (in_array($row['rol_id'], $rol_prohibidos)) {
            echo json_encode(['ok'=>false,'msg'=>'No puedes dar de baja admin o director.']); cerrarBD($conexion); exit;
        }
    }
    $query = "UPDATE usuarios SET activo=0 WHERE id=$id";
    $ok = mysqli_query($conexion, $query);
    echo json_encode(['ok' => $ok]); cerrarBD($conexion); exit;
}

echo json_encode(['ok'=>false, 'msg'=>'Acción no permitida']);
cerrarBD($conexion);
?>