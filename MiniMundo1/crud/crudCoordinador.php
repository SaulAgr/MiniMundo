<?php
session_start();
require_once '../includes/conexion.php';
$conexion = conectarBD();

$user_id = $_SESSION['usuario_id'] ?? 0;
$user_rol = $_SESSION['usuario_rol'] ?? 0;
$accion = $_POST['accion'] ?? '';

if (!$user_id || $user_rol != 3) { http_response_code(403); exit('No autorizado'); }

function solicitudesPath() {
    // Carpeta donde se guardarán las solicitudes (ajustada a tu petición)
    return __DIR__ . '/../assets/Js/solicitudes.json';
}

// Leer todas las solicitudes
function leerSolicitudes() {
    $archivo = solicitudesPath();
    return file_exists($archivo) ? json_decode(file_get_contents($archivo), true) : [];
}

// Guardar todas las solicitudes (sobreescribe el archivo)
function guardarSolicitudes($solicitudes) {
    $archivo = solicitudesPath();
    file_put_contents($archivo, json_encode($solicitudes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Agregar una solicitud nueva
function crearSolicitud($solicitud) {
    $solicitudes = leerSolicitudes();
    $solicitud['id'] = count($solicitudes) ? max(array_column($solicitudes, 'id')) + 1 : 1;
    $solicitudes[] = $solicitud;
    guardarSolicitudes($solicitudes);
}

// Buscar si ya hay una solicitud pendiente para ese tipo y entidad
function haySolicitudPendiente($tipo, $entidad_id) {
    $solicitudes = leerSolicitudes();
    foreach ($solicitudes as $s) {
        if ($s['tipo'] === $tipo && $s['entidad_id'] == $entidad_id && $s['estado'] === 'pendiente') {
            return true;
        }
    }
    return false;
}

// Actualizar estado de una solicitud
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
// --- ALTA DE PROFESOR ---
if ($accion === 'crear_profesor') {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre'] ?? '');
    $email = mysqli_real_escape_string($conexion, $_POST['email'] ?? '');
    $password = mysqli_real_escape_string($conexion, $_POST['password'] ?? '');
    $ok = mysqli_query($conexion, "INSERT INTO usuarios (nombre, email, password, rol_id, activo) VALUES ('$nombre', '$email', '$password', 2, 1)");
    echo json_encode(['ok'=>$ok]); cerrarBD($conexion); exit;
}
// --- ALTA DE ALUMNO ---
if ($accion === 'crear_alumno') {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre'] ?? '');
    $email = mysqli_real_escape_string($conexion, $_POST['email'] ?? '');
    $password = mysqli_real_escape_string($conexion, $_POST['password'] ?? '');

    // Verificar si ya existe el email
    $check = mysqli_query($conexion, "SELECT id FROM usuarios WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
        echo json_encode(['ok'=>false, 'msg'=>'El correo ya está registrado']);
        cerrarBD($conexion); exit;
    }

    $ok = mysqli_query($conexion, "INSERT INTO usuarios (nombre, email, password, rol_id, activo) VALUES ('$nombre', '$email', '$password', 1, 1)");
    echo json_encode(['ok'=>$ok]); cerrarBD($conexion); exit;
}

// ---- SOLICITUD UNIVERSAL: ELIMINAR CLASE ----
if ($accion === 'eliminar_clase') {
    $id = intval($_POST['id']);
    if (haySolicitudPendiente('eliminar_clase', $id)) {
        echo json_encode(['ok'=>false, 'msg'=>'Ya existe una solicitud pendiente de aprobación del director.']);
        cerrarBD($conexion); exit;
    }
    $solicitud = [
        'solicitante_id' => $user_id,
        'tipo' => 'eliminar_clase',
        'entidad_id' => $id,
        'datos' => [],
        'estado' => 'pendiente',
        'fecha_solicitud' => date('Y-m-d H:i:s'),
        'fecha_respuesta' => null,
        'director_id' => null,
        'respuesta' => null,
    ];
    crearSolicitud($solicitud);
    echo json_encode(['ok'=>true, 'msg'=>'Solicitud enviada al director. Espera aprobación.']);
    cerrarBD($conexion); exit;
}

// ---- SOLICITUD UNIVERSAL: EDITAR CLASE ----
if ($accion === 'editar_clase') {
    $id = intval($_POST['id']);
    if (haySolicitudPendiente('editar_clase', $id)) {
        echo json_encode(['ok'=>false, 'msg'=>'Ya existe una solicitud pendiente de aprobación del director.']);
        cerrarBD($conexion); exit;
    }
    $nuevosDatos = [
        'nombre' => $_POST['nombre'] ?? '',
        'periodo' => $_POST['periodo'] ?? '',
        'profesor_id' => intval($_POST['profesor_id'] ?? 0),
        'cup_maximo' => intval($_POST['cup_maximo'] ?? 30)
    ];
    $solicitud = [
        'solicitante_id' => $user_id,
        'tipo' => 'editar_clase',
        'entidad_id' => $id,
        'datos' => $nuevosDatos,
        'estado' => 'pendiente',
        'fecha_solicitud' => date('Y-m-d H:i:s'),
        'fecha_respuesta' => null,
        'director_id' => null,
        'respuesta' => null,
    ];
    crearSolicitud($solicitud);
    echo json_encode(['ok'=>true, 'msg'=>'Solicitud enviada al director. Espera aprobación.']);
    cerrarBD($conexion); exit;
}
// --- LISTADO PARA SELECT DE PROFESORES Y ALUMNOS ---
if ($accion === 'profesores_activos') {
    $q = mysqli_query($conexion, "SELECT id, nombre FROM usuarios WHERE rol_id=2 AND activo=1");
    $out = [];
    while($row = mysqli_fetch_assoc($q)) $out[] = $row;
    echo json_encode($out); cerrarBD($conexion); exit;
}
if ($accion === 'alumnos_activos') {
    $q = mysqli_query($conexion, "SELECT id, nombre FROM usuarios WHERE rol_id=1 AND activo=1");
    $out = [];
    while($row = mysqli_fetch_assoc($q)) $out[] = $row;
    echo json_encode($out); cerrarBD($conexion); exit;
}
// --- CREAR CLASE ---
if ($accion === 'crear_clase') {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre'] ?? '');
    $periodo = mysqli_real_escape_string($conexion, $_POST['periodo'] ?? '');
    $profesor_id = intval($_POST['profesor_id'] ?? 0);
    $cup_maximo = intval($_POST['cup_maximo'] ?? 30);
    $query = "INSERT INTO clases (nombre, periodo, profesor_id, coordinador_id, cupo_maximo) 
              VALUES ('$nombre', '$periodo', $profesor_id, $user_id, $cup_maximo)";
    $ok = mysqli_query($conexion, $query);
    echo json_encode(['ok' => $ok]); cerrarBD($conexion); exit;
}
// --- EDITAR CLASE (asignar profesor) ---
if ($accion === 'editar_clase') {
    $id = intval($_POST['id']);
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre'] ?? '');
    $periodo = mysqli_real_escape_string($conexion, $_POST['periodo'] ?? '');
    $profesor_id = intval($_POST['profesor_id'] ?? 0);
    $cup_maximo = intval($_POST['cup_maximo'] ?? 30);
    $ok = mysqli_query($conexion, "UPDATE clases
        SET nombre='$nombre', periodo='$periodo', profesor_id=$profesor_id, cupo_maximo=$cup_maximo
        WHERE id=$id AND coordinador_id=$user_id");
    echo json_encode(['ok'=>$ok]); cerrarBD($conexion); exit;
}
// --- ELIMINAR CLASE ---
if ($accion === 'eliminar_clase') {
    $id = intval($_POST['id']);
    $ok = mysqli_query($conexion, "DELETE FROM clases WHERE id=$id AND coordinador_id=$user_id");
    echo json_encode(['ok'=>$ok]); cerrarBD($conexion); exit;
}
// --- LISTAR CLASES DEL COORDINADOR ---
if ($accion === 'listar_clases') {
    $q = mysqli_query($conexion, "
        SELECT c.id, c.nombre, c.periodo, c.cupo_maximo, c.cerrada, 
               p.nombre AS profesor, c.profesor_id
        FROM clases c
        LEFT JOIN usuarios p ON c.profesor_id=p.id
        WHERE c.coordinador_id = $user_id
    ");
    $out = [];
    while($row = mysqli_fetch_assoc($q)) $out[] = $row;
    echo json_encode($out); cerrarBD($conexion); exit;
}
// --- LISTAR PROFESORES ---
if ($accion === 'listar_profesores') {
    $q = mysqli_query($conexion, "SELECT id, nombre, email, activo FROM usuarios WHERE rol_id = 2");
    $out = [];
    while($row = mysqli_fetch_assoc($q)) $out[] = $row;
    echo json_encode($out); cerrarBD($conexion); exit;
}
// --- LISTAR ALUMNOS DE SUS CLASES ---
if ($accion === 'listar_alumnos') {
    $q = mysqli_query($conexion, "
        SELECT u.id, u.nombre, u.email, u.activo, c.nombre as clase
        FROM usuarios u
        LEFT JOIN inscripciones i ON i.alumno_id = u.id
        LEFT JOIN clases c ON i.clase_id = c.id AND c.coordinador_id = $user_id
        WHERE u.rol_id = 1
    ");
    $out = [];
    while($row = mysqli_fetch_assoc($q)) $out[] = $row;
    echo json_encode($out); cerrarBD($conexion); exit;
}
// --- INSCRIBIR ALUMNO A CLASE ---
if ($accion === 'inscribir_alumno') {
    $alumno_id = intval($_POST['alumno_id'] ?? 0);
    $clase_id = intval($_POST['clase_id'] ?? 0);
    // Verifica que la clase sea de la coordinadora
    $check = mysqli_query($conexion, "SELECT id FROM clases WHERE id=$clase_id AND coordinador_id=$user_id");
    if(mysqli_num_rows($check) == 0) { echo json_encode(['ok'=>false, 'msg'=>'Clase no tuya']); cerrarBD($conexion); exit; }
    // Evita doble inscripción
    $existe = mysqli_query($conexion,"SELECT id FROM inscripciones WHERE alumno_id=$alumno_id AND clase_id=$clase_id");
    if(mysqli_num_rows($existe)>0){ echo json_encode(['ok'=>false, 'msg'=>'Ya inscrito']); cerrarBD($conexion); exit; }
    $ok = mysqli_query($conexion, "INSERT INTO inscripciones (alumno_id, clase_id, aprobada) VALUES ($alumno_id, $clase_id, 1)");
    echo json_encode(['ok'=>$ok]); cerrarBD($conexion); exit;
}
// --- LISTAR INSCRIPCIONES DE SUS CLASES ---
if ($accion === 'listar_inscripciones') {
    $q = mysqli_query($conexion,"
        SELECT i.id, a.nombre as alumno, c.nombre as clase, i.aprobada, i.fecha_inscripcion, i.alumno_id, i.clase_id
        FROM inscripciones i
        JOIN usuarios a ON i.alumno_id = a.id
        JOIN clases c ON i.clase_id = c.id
        WHERE c.coordinador_id = $user_id
    ");
    $out = [];
    while($row = mysqli_fetch_assoc($q)) $out[] = $row;
    echo json_encode($out); cerrarBD($conexion); exit;
}
// --- APROBAR INSCRIPCION ---
if ($accion === 'aprobar_inscripcion') {
    $id = intval($_POST['id']);
    $ok = mysqli_query($conexion, "
        UPDATE inscripciones 
        SET aprobada=1, fecha_aprobacion=NOW() 
        WHERE id=$id AND clase_id IN (SELECT id FROM clases WHERE coordinador_id=$user_id)
    ");
    echo json_encode(['ok'=>$ok]); cerrarBD($conexion); exit;
}
// --- RECHAZAR INSCRIPCION ---
if ($accion === 'rechazar_inscripcion') {
    $id = intval($_POST['id']);
    $ok = mysqli_query($conexion, "
        DELETE FROM inscripciones 
        WHERE id=$id AND clase_id IN (SELECT id FROM clases WHERE coordinador_id=$user_id)
    ");
    echo json_encode(['ok'=>$ok]); cerrarBD($conexion); exit;
}
// --- PERFIL DEL COORDINADOR ---
if ($accion === 'perfil') {
    $q = mysqli_query($conexion, "SELECT id, nombre, email FROM usuarios WHERE id = $user_id AND rol_id=3");
    $out = mysqli_fetch_assoc($q);
    echo json_encode($out); cerrarBD($conexion); exit;
}
if ($accion === 'editar_perfil') {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre'] ?? '');
    $email = mysqli_real_escape_string($conexion, $_POST['email'] ?? '');
    $ok = mysqli_query($conexion, "UPDATE usuarios SET nombre='$nombre', email='$email' WHERE id=$user_id AND rol_id=3");
    echo json_encode(['ok'=>$ok]); cerrarBD($conexion); exit;
}

echo json_encode(['ok'=>false, 'msg'=>'Acción no permitida']);
cerrarBD($conexion);
?>