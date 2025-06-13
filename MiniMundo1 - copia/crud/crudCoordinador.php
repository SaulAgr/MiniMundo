<?php
session_start();
require_once __DIR__ . '/crudUtils.php';
$conexion = getConexion();

try {
    $db = new PDO('mysql:host=localhost;dbname=sistema_educativo;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

$user_id  = $_SESSION['usuario_id'] ?? 0;
$user_rol = $_SESSION['usuario_rol'] ?? 0;
$accion   = $_POST['accion'] ?? '';

// Sólo coordinador puede usar este panel
requireRole([3]);

function okResp($data = [], $msg = '', $conexion = null) {
    $out = ['ok' => true];
    if ($msg) $out['msg'] = $msg;
    // Solo agrega data si no es null/vacío y es array
    if (is_array($data) && !empty($data)) $out['data'] = $data;
    elseif (!is_array($data) && $data) $out['data'] = $data;
    jsonExit($out, $conexion);
}

// --- ALTA DE PROFESOR ---
if ($accion === 'crear_profesor') {
    $nombre   = postParam($conexion, 'nombre');
    $email    = postParam($conexion, 'email');
    $password = postParam($conexion, 'password');
    $ok = insertarUsuario($conexion, $nombre, $email, $password, 2);
    if ($ok) okResp([], 'Profesor creado correctamente', $conexion);
    else errorResp('No se pudo crear el profesor', $conexion);
}

// --- ALTA DE ALUMNO ---
if ($accion === 'crear_alumno') {
    $nombre   = postParam($conexion, 'nombre');
    $email    = postParam($conexion, 'email');
    $password = postParam($conexion, 'password');
    // Verificar si ya existe el email
    $check = mysqli_query($conexion, "SELECT id FROM usuarios WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
        errorResp('El correo ya está registrado', $conexion);
    }
    $ok = insertarUsuario($conexion, $nombre, $email, $password, 1);
    if ($ok) okResp([], 'Alumno creado correctamente', $conexion);
    else errorResp('No se pudo crear el alumno', $conexion);
}

// --- CREAR CLASE ---
if ($accion === 'crear_clase') {
    $nombre      = postParam($conexion, 'nombre');
    $periodo     = postParam($conexion, 'periodo');
    $profesor_id = intParam('profesor_id');
    $cup_maximo  = intParam('cup_maximo', 30);
    $query = "INSERT INTO clases (nombre, periodo, profesor_id, coordinador_id, cupo_maximo) 
              VALUES ('$nombre', '$periodo', $profesor_id, $user_id, $cup_maximo)";
    $ok = mysqli_query($conexion, $query);
    if ($ok) okResp([], 'Clase creada correctamente', $conexion);
    else errorResp('No se pudo crear la clase', $conexion);
}

// --- EDITAR CLASE (solicitud a director) ---
if ($accion === 'editar_clase') {
    $id = intParam('id');
    if (haySolicitudPendiente('editar_clase', $id)) {
        errorResp('Ya existe una solicitud pendiente de aprobación del director.', $conexion);
    }
    $nuevosDatos = [
        'nombre'      => $_POST['nombre'] ?? '',
        'periodo'     => $_POST['periodo'] ?? '',
        'profesor_id' => intParam('profesor_id'),
        'cup_maximo'  => intParam('cup_maximo', 30)
    ];
    $solicitud = [
        'solicitante_id'  => $user_id,
        'tipo'            => 'editar_clase',
        'entidad_id'      => $id,
        'datos'           => $nuevosDatos,
        'estado'          => 'pendiente',
        'fecha_solicitud' => date('Y-m-d H:i:s'),
        'fecha_respuesta' => null,
        'director_id'     => null,
        'respuesta'       => null,
    ];
    crearSolicitud($solicitud);
    okResp([], 'Solicitud enviada al director. Espera aprobación.', $conexion);
}

// --- ELIMINAR CLASE (solicitud a director) ---
if ($accion === 'eliminar_clase') {
    $id = intParam('id');
    if (haySolicitudPendiente('eliminar_clase', $id)) {
        errorResp('Ya existe una solicitud pendiente de aprobación del director.', $conexion);
    }
    $solicitud = [
        'solicitante_id'  => $user_id,
        'tipo'            => 'eliminar_clase',
        'entidad_id'      => $id,
        'datos'           => [],
        'estado'          => 'pendiente',
        'fecha_solicitud' => date('Y-m-d H:i:s'),
        'fecha_respuesta' => null,
        'director_id'     => null,
        'respuesta'       => null,
    ];
    crearSolicitud($solicitud);
    okResp([], 'Solicitud enviada al director. Espera aprobación.', $conexion);
}

// --- LISTADO PARA SELECT DE PROFESORES Y ALUMNOS ---
if ($accion === 'profesores_activos') {
    $out = getUsuariosPorRol($conexion, 2);
    okResp($out, '', $conexion);
}
if ($accion === 'alumnos_activos') {
    $out = getUsuariosPorRol($conexion, 1);
    okResp($out, '', $conexion);
}

// --- LISTAR CLASES DEL COORDINADOR ---
if ($accion === 'listar_clases') {
    $query = "
        SELECT c.id, c.nombre, c.periodo, c.cupo_maximo, c.cerrada, 
               p.nombre AS profesor, c.profesor_id
        FROM clases c
        LEFT JOIN usuarios p ON c.profesor_id=p.id
        WHERE c.coordinador_id = $user_id
    ";
    $out = fetchQueryAssoc($conexion, $query);
    okResp($out, '', $conexion);
}

// --- LISTAR PROFESORES ---
if ($accion === 'listar_profesores') {
    $query = "SELECT id, nombre, email, activo FROM usuarios WHERE rol_id = 2";
    $out = fetchQueryAssoc($conexion, $query);
    okResp($out, '', $conexion);
}

// --- LISTAR ALUMNOS DE SUS CLASES ---
if ($accion === 'listar_alumnos') {
    $query = "
        SELECT u.id, u.nombre, u.email, u.activo, c.nombre as clase
        FROM usuarios u
        LEFT JOIN inscripciones i ON i.alumno_id = u.id
        LEFT JOIN clases c ON i.clase_id = c.id AND c.coordinador_id = $user_id
        WHERE u.rol_id = 1
    ";
    $out = fetchQueryAssoc($conexion, $query);
    okResp($out, '', $conexion);
}

// --- Nueva acción: alumnos_disponibles_para_inscripcion ---
if ($_POST['accion'] == 'alumnos_disponibles_para_inscripcion') {
    // Cuenta cuántas clases están abiertas (no cerradas)
    $sql_total_clases = "SELECT COUNT(*) FROM clases WHERE cerrada = 0";
    $total_clases = $db->query($sql_total_clases)->fetchColumn();

    // Si no hay clases abiertas, no hay alumnos disponibles
    if ($total_clases == 0) {
        echo json_encode([]);
        exit;
    }

    // Trae alumnos que NO estén inscritos en todas las clases abiertas
    $sql = "
        SELECT u.id, u.nombre
        FROM usuarios u
        WHERE u.rol_id = 1
        AND u.activo = 1
        AND (
            SELECT COUNT(*) FROM inscripciones i
            JOIN clases c ON i.clase_id = c.id
            WHERE i.alumno_id = u.id AND c.cerrada = 0
        ) < :total_clases
        ORDER BY u.nombre
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute(['total_clases' => $total_clases]);
    $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
error_log("Alumnos disponibles: " . json_encode($alumnos));
    echo json_encode($alumnos);
    exit;
}

// --- INSCRIBIR ALUMNO A CLASE ---
if ($accion === 'inscribir_alumno') {
    $alumno_id = intParam('alumno_id');
    $clase_id  = intParam('clase_id');
    // Verifica que la clase sea de la coordinadora
    $check = mysqli_query($conexion, "SELECT id FROM clases WHERE id=$clase_id AND coordinador_id=$user_id");
    if (mysqli_num_rows($check) == 0) { errorResp('Clase no tuya', $conexion); }
    // Evita doble inscripción
    $existe = mysqli_query($conexion,"SELECT id FROM inscripciones WHERE alumno_id=$alumno_id AND clase_id=$clase_id");
    if (mysqli_num_rows($existe)>0) { errorResp('Ya inscrito', $conexion); }
    $ok = mysqli_query($conexion, "INSERT INTO inscripciones (alumno_id, clase_id, aprobada) VALUES ($alumno_id, $clase_id, 0)");
    if ($ok) okResp([], 'Inscripción exitosa', $conexion);
    else errorResp('No se pudo inscribir al alumno', $conexion);
}

// --- LISTAR INSCRIPCIONES DE SUS CLASES ---
if ($accion === 'listar_inscripciones') {
    $query = "
        SELECT i.id, a.nombre as alumno, c.nombre as clase, i.aprobada, i.fecha_inscripcion, i.alumno_id, i.clase_id
        FROM inscripciones i
        JOIN usuarios a ON i.alumno_id = a.id
        JOIN clases c ON i.clase_id = c.id
        WHERE c.coordinador_id = $user_id
    ";
    $out = fetchQueryAssoc($conexion, $query);
    okResp($out, '', $conexion);
}

// --- APROBAR INSCRIPCION ---
if ($accion === 'aprobar_inscripcion') {
    $id = intParam('id');
    $ok = mysqli_query($conexion, "
        UPDATE inscripciones 
        SET aprobada=1, fecha_aprobacion=NOW() 
        WHERE id=$id AND clase_id IN (SELECT id FROM clases WHERE coordinador_id=$user_id)
    ");
    if ($ok) okResp([], 'Inscripción aprobada', $conexion);
    else errorResp('No se pudo aprobar la inscripción', $conexion);
}

// --- RECHAZAR INSCRIPCION ---
if ($accion === 'rechazar_inscripcion') {
    $id = intParam('id');
    $ok = mysqli_query($conexion, "
        DELETE FROM inscripciones 
        WHERE id=$id AND clase_id IN (SELECT id FROM clases WHERE coordinador_id=$user_id)
    ");
    if ($ok) okResp([], 'Inscripción rechazada', $conexion);
    else errorResp('No se pudo rechazar la inscripción', $conexion);
}

// --- PERFIL DEL COORDINADOR ---
if ($accion === 'perfil') {
    $query = "SELECT id, nombre, email FROM usuarios WHERE id = $user_id AND rol_id=3";
    $out = mysqli_fetch_assoc(mysqli_query($conexion, $query));
    okResp($out, '', $conexion);
}
if ($accion === 'editar_perfil') {
    $nombre = postParam($conexion, 'nombre');
    $email  = postParam($conexion, 'email');
    $ok = mysqli_query($conexion, "UPDATE usuarios SET nombre='$nombre', email='$email' WHERE id=$user_id AND rol_id=3");
    if ($ok) okResp([], 'Perfil actualizado', $conexion);
    else errorResp('No se pudo actualizar el perfil', $conexion);
}

errorResp('Acción no permitida', $conexion);
?>