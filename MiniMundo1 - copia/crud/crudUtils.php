<?php
// Utilidades para centralizar lógica común en los CRUD del sistema

// --- Sesión y roles ---

function requireSession() {
    if (!isset($_SESSION['usuario_id'])) {
        http_response_code(403);
        exit('No autorizado: sesión no iniciada');
    }
}

function requireRole($roles) {
    if (!in_array($_SESSION['usuario_rol'], (array)$roles)) {
        http_response_code(403);
        exit('No autorizado: rol incorrecto');
    }
}

function is_admin()      { return $_SESSION['usuario_rol'] == 5; }
function is_director()   { return $_SESSION['usuario_rol'] == 4; }
function is_coordinador(){ return $_SESSION['usuario_rol'] == 3; }
function is_profesor()   { return $_SESSION['usuario_rol'] == 2; }
function is_alumno()     { return $_SESSION['usuario_rol'] == 1; }
function is_padre()      { return $_SESSION['usuario_rol'] == 6; }

// --- Conexión a BD ---

function getConexion() {
    require_once __DIR__ . '/../includes/conexion.php';
    return conectarBD();
}

// --- Sanitización y obtención de parámetros ---

function postParam($conexion, $param, $default = '') {
    return mysqli_real_escape_string($conexion, $_POST[$param] ?? $default);
}

function intParam($param, $default = 0) {
    return isset($_POST[$param]) ? intval($_POST[$param]) : $default;
}

// --- Respuestas JSON y cierre ---

function jsonExit($data, $conexion = null) {
    echo json_encode($data);
    if ($conexion) cerrarBD($conexion);
    exit;
}

function errorResp($msg, $conexion = null) {
    jsonExit(['ok'=>false, 'msg'=>$msg], $conexion);
}

// --- Consultas comunes ---

function fetchQueryAssoc($conexion, $query) {
    $q = mysqli_query($conexion, $query);
    $out = [];
    while($row = mysqli_fetch_assoc($q)) $out[] = $row;
    return $out;
}

function getUsuariosPorRol($conexion, $rol_id) {
    $stmt = mysqli_prepare($conexion, "SELECT id, nombre, email, activo FROM usuarios WHERE rol_id = ? AND activo = 1");
    mysqli_stmt_bind_param($stmt, "i", $rol_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $usuarios = [];
    while($row = mysqli_fetch_assoc($result)) $usuarios[] = $row;
    return $usuarios;
}

function getSelectList($conexion, $tabla, $where = "") {
    $query = "SELECT id, nombre FROM $tabla";
    if ($where) $query .= " WHERE $where";
    return fetchQueryAssoc($conexion, $query);
}

// --- Permisos sobre recursos ---

function usuarioPuedeModificarClase($conexion, $user_id, $clase_id) {
    $q = mysqli_prepare($conexion, "SELECT id FROM clases WHERE id = ? AND (profesor_id = ? OR coordinador_id = ?)");
    mysqli_stmt_bind_param($q, "iii", $clase_id, $user_id, $user_id);
    mysqli_stmt_execute($q);
    $result = mysqli_stmt_get_result($q);
    return mysqli_num_rows($result) > 0;
}

// --- Acciones sobre usuarios ---

function insertarUsuario($conexion, $nombre, $email, $password, $rol_id) {
    $stmt = mysqli_prepare($conexion, "INSERT INTO usuarios (nombre, email, password, rol_id, activo) VALUES (?, ?, ?, ?, 1)");
    mysqli_stmt_bind_param($stmt, "sssi", $nombre, $email, $password, $rol_id);
    $ok = mysqli_stmt_execute($stmt);
    return $ok;
}

// --- Utilidades para solicitudes (opcional, ya recomendadas antes) ---

function solicitudesPath() {
    return __DIR__ . '/../assets/Js/solicitudes.json';
}
function leerSolicitudes() {
    $archivo = solicitudesPath();
    return file_exists($archivo) ? json_decode(file_get_contents($archivo), true) : [];
}
function guardarSolicitudes($solicitudes) {
    $archivo = solicitudesPath();
    file_put_contents($archivo, json_encode($solicitudes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
function crearSolicitud($solicitud) {
    $solicitudes = leerSolicitudes();
    $solicitud['id'] = count($solicitudes) ? max(array_column($solicitudes, 'id')) + 1 : 1;
    $solicitudes[] = $solicitud;
    guardarSolicitudes($solicitudes);
}
function haySolicitudPendiente($tipo, $entidad_id) {
    $solicitudes = leerSolicitudes();
    foreach ($solicitudes as $s) {
        if ($s['tipo'] === $tipo && $s['entidad_id'] == $entidad_id && $s['estado'] === 'pendiente') {
            return true;
        }
    }
    return false;
}
function actualizarSolicitud($id, $nuevoEstado, $director_id, $respuesta = null) {
    $solicitudes = leerSolicitudes();
    foreach ($solicitudes as &$sol) {
        if ($sol['id'] == $id) {
            $sol['estado'] = $nuevoEstado;
            $sol['fecha_respuesta'] = date('Y-m-d H:i:s');
            $sol['director_id'] = $director_id;
            $sol['respuesta'] = $respuesta;
        }
    }
    guardarSolicitudes($solicitudes);
}
?>