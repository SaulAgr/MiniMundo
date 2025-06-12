<?php
// ==================== FUNCIONES BASE ====================
function conectarBD() {
    static $conn;
    if (!$conn) {
        require_once __DIR__.'/../config/database.php';
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            header('Content-Type: application/json');
            http_response_code(500);
            die(json_encode(['ok' => false, 'error' => 'Error de conexión a BD']));
        }
        $conn->set_charset('utf8');
    }
    return $conn;
}

function verificarSesion($rolesPermitidos = []) {
    session_start();
    if (empty($_SESSION['usuario_id'])) {
        http_response_code(401);
        die(json_encode(['ok' => false, 'msg' => 'No autenticado']));
    }
    if (!empty($rolesPermitidos) && !in_array($_SESSION['usuario_rol'], $rolesPermitidos)) {
        http_response_code(403);
        die(json_encode(['ok' => false, 'msg' => 'No autorizado']));
    }
    return $_SESSION['usuario_id'];
}

function responderJson($data, $exito = true) {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['ok' => $exito], is_array($data) ? $data : ['data' => $data]));
    exit;
}

function ejecutarConsulta($sql, $params = [], $tipos = '') {
    $db = conectarBD();
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        responderJson(['error' => $db->error], false);
    }
    
    if (!empty($params)) {
        $stmt->bind_param($tipos, ...$params);
    }
    
    if (!$stmt->execute()) {
        responderJson(['error' => $stmt->error], false);
    }
    
    return $stmt;
}

// ==================== FUNCIONES DE AYUDA ====================
function sanitizarInput($conexion, $input) {
    return $conexion->real_escape_string(trim($input));
}
?>