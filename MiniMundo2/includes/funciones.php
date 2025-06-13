<?php
/**
 * Archivo centralizado de funciones para el Sistema Educativo
 * Reemplaza todas las operaciones CRUD dispersas con funciones estandarizadas
 */

// Conexión a la base de datos
function conectarBD() {
    $servidor = "localhost";
    $usuario = "root";
    $password = "";
    $basedatos = "sistema_educativo";
    
    $conexion = mysqli_connect($servidor, $usuario, $password, $basedatos);
    
    if (!$conexion) {
        die("Error de conexión: " . mysqli_connect_error());
    }
    
    mysqli_set_charset($conexion, "utf8mb4");
    return $conexion;
}

function cerrarBD($conexion) {
    mysqli_close($conexion);
}

// Función para sanitizar entradas
function limpiarEntrada($conexion, $dato) {
    return mysqli_real_escape_string($conexion, trim($dato));
}

// Función para responder en formato JSON
function responderJSON($datos) {
    header('Content-Type: application/json');
    echo json_encode($datos);
    exit;
}

// Función para verificar permisos
function verificarPermiso($rolesPermitidos) {
    $rolActual = $_SESSION['usuario_rol'] ?? 0;
    return in_array($rolActual, $rolesPermitidos);
}

// Función para registrar actividad
function registrarActividad($conexion, $usuario_id, $accion, $detalles = '') {
    $accion = limpiarEntrada($conexion, $accion);
    $detalles = limpiarEntrada($conexion, $detalles);
    $fecha = date('Y-m-d H:i:s');
    
    $query = "INSERT INTO registro_actividad (usuario_id, accion, detalles, fecha) 
              VALUES ($usuario_id, '$accion', '$detalles', '$fecha')";
    
    return mysqli_query($conexion, $query);
}

// Función para actualizar último acceso
function actualizarUltimoAcceso($conexion, $usuario_id) {
    $fecha = date('Y-m-d H:i:s');
    $query = "UPDATE usuarios SET ultimo_acceso = '$fecha' WHERE id = $usuario_id";
    return mysqli_query($conexion, $query);
}

/**
 * FUNCIONES PARA USUARIOS
 */

// Obtener usuario por ID
function obtenerUsuario($conexion, $id) {
    $id = intval($id);
    $query = "SELECT u.*, r.nombre as rol_nombre 
              FROM usuarios u 
              JOIN roles r ON u.rol_id = r.id 
              WHERE u.id = $id";
    $resultado = mysqli_query($conexion, $query);
    return mysqli_fetch_assoc($resultado);
}

// Obtener usuarios por rol
function obtenerUsuariosPorRol($conexion, $rol_id) {
    $rol_id = intval($rol_id);
    $query = "SELECT u.*, r.nombre as rol_nombre 
              FROM usuarios u 
              JOIN roles r ON u.rol_id = r.id 
              WHERE u.rol_id = $rol_id AND u.activo = 1";
    $resultado = mysqli_query($conexion, $query);
    
    $usuarios = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $usuarios[] = $row;
    }
    
    return $usuarios;
}

// Crear nuevo usuario
function crearUsuario($conexion, $nombre, $email, $password, $rol_id) {
    $nombre = limpiarEntrada($conexion, $nombre);
    $email = limpiarEntrada($conexion, $email);
    $password = limpiarEntrada($conexion, $password);
    $rol_id = intval($rol_id);
    
    // Verificar si el email ya existe
    $verificar = mysqli_query($conexion, "SELECT id FROM usuarios WHERE email = '$email'");
    if (mysqli_num_rows($verificar) > 0) {
        return ['ok' => false, 'msg' => 'El email ya está registrado'];
    }
    
    $query = "INSERT INTO usuarios (nombre, email, password, rol_id, activo) 
              VALUES ('$nombre', '$email', '$password', $rol_id, 1)";
    
    if (mysqli_query($conexion, $query)) {
        return ['ok' => true, 'id' => mysqli_insert_id($conexion)];
    } else {
        return ['ok' => false, 'msg' => mysqli_error($conexion)];
    }
}

// Actualizar usuario
function actualizarUsuario($conexion, $id, $datos) {
    $id = intval($id);
    $campos = [];
    
    foreach ($datos as $campo => $valor) {
        if ($campo != 'id') {
            $valor = limpiarEntrada($conexion, $valor);
            $campos[] = "$campo = '$valor'";
        }
    }
    
    $camposStr = implode(', ', $campos);
    $query = "UPDATE usuarios SET $camposStr WHERE id = $id";
    
    if (mysqli_query($conexion, $query)) {
        return ['ok' => true];
    } else {
        return ['ok' => false, 'msg' => mysqli_error($conexion)];
    }
}

// Cambiar estado de usuario (activar/desactivar)
function cambiarEstadoUsuario($conexion, $id, $activo) {
    $id = intval($id);
    $activo = $activo ? 1 : 0;
    
    $query = "UPDATE usuarios SET activo = $activo WHERE id = $id";
    
    if (mysqli_query($conexion, $query)) {
        return ['ok' => true];
    } else {
        return ['ok' => false, 'msg' => mysqli_error($conexion)];
    }
}

// Verificar credenciales de usuario
function verificarCredenciales($conexion, $email, $password) {
    $email = limpiarEntrada($conexion, $email);
    $password = limpiarEntrada($conexion, $password);
    
    $query = "SELECT u.*, r.nombre as rol_nombre 
              FROM usuarios u 
              JOIN roles r ON u.rol_id = r.id 
              WHERE u.email = '$email' AND u.password = '$password' AND u.activo = 1";
    
    $resultado = mysqli_query($conexion, $query);
    
    if (mysqli_num_rows($resultado) > 0) {
        return mysqli_fetch_assoc($resultado);
    } else {
        return false;
    }
}

/**
 * FUNCIONES PARA CLASES
 */

// Obtener clase por ID
function obtenerClase($conexion, $id) {
    $id = intval($id);
    $query = "SELECT c.*, p.nombre as profesor_nombre, coord.nombre as coordinador_nombre 
              FROM clases c 
              LEFT JOIN usuarios p ON c.profesor_id = p.id 
              LEFT JOIN usuarios coord ON c.coordinador_id = coord.id 
              WHERE c.id = $id";
    
    $resultado = mysqli_query($conexion, $query);
    return mysqli_fetch_assoc($resultado);
}

// Obtener clases por profesor
function obtenerClasesPorProfesor($conexion, $profesor_id) {
    $profesor_id = intval($profesor_id);
    $query = "SELECT c.*, COUNT(i.id) as total_alumnos 
              FROM clases c 
              LEFT JOIN inscripciones i ON c.id = i.clase_id AND i.aprobada = 1 
              WHERE c.profesor_id = $profesor_id 
              GROUP BY c.id";
    
    $resultado = mysqli_query($conexion, $query);
    
    $clases = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $clases[] = $row;
    }
    
    return $clases;
}

// Obtener clases por coordinador
function obtenerClasesPorCoordinador($conexion, $coordinador_id) {
    $coordinador_id = intval($coordinador_id);
    $query = "SELECT c.*, p.nombre as profesor_nombre, COUNT(i.id) as total_alumnos 
              FROM clases c 
              LEFT JOIN usuarios p ON c.profesor_id = p.id 
              LEFT JOIN inscripciones i ON c.id = i.clase_id AND i.aprobada = 1 
              WHERE c.coordinador_id = $coordinador_id 
              GROUP BY c.id";
    
    $resultado = mysqli_query($conexion, $query);
    
    $clases = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $clases[] = $row;
    }
    
    return $clases;
}

// Crear nueva clase
function crearClase($conexion, $nombre, $periodo, $profesor_id, $coordinador_id, $cupo_maximo = 30, $descripcion = '') {
    $nombre = limpiarEntrada($conexion, $nombre);
    $periodo = limpiarEntrada($conexion, $periodo);
    $profesor_id = intval($profesor_id);
    $coordinador_id = intval($coordinador_id);
    $cupo_maximo = intval($cupo_maximo);
    $descripcion = limpiarEntrada($conexion, $descripcion);
    
    $query = "INSERT INTO clases (nombre, periodo, profesor_id, coordinador_id, cupo_maximo, descripcion) 
              VALUES ('$nombre', '$periodo', $profesor_id, $coordinador_id, $cupo_maximo, '$descripcion')";
    
    if (mysqli_query($conexion, $query)) {
        return ['ok' => true, 'id' => mysqli_insert_id($conexion)];
    } else {
        return ['ok' => false, 'msg' => mysqli_error($conexion)];
    }
}

// Actualizar clase
function actualizarClase($conexion, $id, $datos) {
    $id = intval($id);
    $campos = [];
    
    foreach ($datos as $campo => $valor) {
        if ($campo != 'id') {
            $valor = limpiarEntrada($conexion, $valor);
            $campos[] = "$campo = '$valor'";
        }
    }
    
    $camposStr = implode(', ', $campos);
    $query = "UPDATE clases SET $camposStr WHERE id = $id";
    
    if (mysqli_query($conexion, $query)) {
        return ['ok' => true];
    } else {
        return ['ok' => false, 'msg' => mysqli_error($conexion)];
    }
}

// Eliminar clase
function eliminarClase($conexion, $id) {
    $id = intval($id);
    
    // Verificar si hay inscripciones
    $verificar = mysqli_query($conexion, "SELECT COUNT(*) as total FROM inscripciones WHERE clase_id = $id");
    $row = mysqli_fetch_assoc($verificar);
    
    if ($row['total'] > 0) {
        return ['ok' => false, 'msg' => 'No se puede eliminar la clase porque tiene alumnos inscritos'];
    }
    
    $query = "DELETE FROM clases WHERE id = $id";
    
    if (mysqli_query($conexion, $query)) {
        return ['ok' => true];
    } else {
        return ['ok' => false, 'msg' => mysqli_error($conexion)];
    }
}

/**
 * FUNCIONES PARA INSCRIPCIONES
 */

// Obtener inscripciones por clase
function obtenerInscripcionesPorClase($conexion, $clase_id) {
    $clase_id = intval($clase_id);
    $query = "SELECT i.*, u.nombre as alumno_nombre, u.email as alumno_email 
              FROM inscripciones i 
              JOIN usuarios u ON i.alumno_id = u.id 
              WHERE i.clase_id = $clase_id 
              ORDER BY i.aprobada ASC, u.nombre ASC";
    
    $resultado = mysqli_query($conexion, $query);
    
    $inscripciones = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $inscripciones[] = $row;
    }
    
    return $inscripciones;
}

// Obtener inscripciones por alumno
function obtenerInscripcionesPorAlumno($conexion, $alumno_id) {
    $alumno_id = intval($alumno_id);
    $query = "SELECT i.*, c.nombre as clase_nombre, c.periodo, p.nombre as profesor_nombre 
              FROM inscripciones i 
              JOIN clases c ON i.clase_id = c.id 
              LEFT JOIN usuarios p ON c.profesor_id = p.id 
              WHERE i.alumno_id = $alumno_id 
              ORDER BY i.aprobada ASC, c.nombre ASC";
    
    $resultado = mysqli_query($conexion, $query);
    
    $inscripciones = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $inscripciones[] = $row;
    }
    
    return $inscripciones;
}

// Inscribir alumno a clase
function inscribirAlumno($conexion, $alumno_id, $clase_id, $aprobada = 0) {
    $alumno_id = intval($alumno_id);
    $clase_id = intval($clase_id);
    $aprobada = intval($aprobada);
    
    // Verificar si ya está inscrito
    $verificar = mysqli_query($conexion, "SELECT id FROM inscripciones WHERE alumno_id = $alumno_id AND clase_id = $clase_id");
    if (mysqli_num_rows($verificar) > 0) {
        return ['ok' => false, 'msg' => 'El alumno ya está inscrito en esta clase'];
    }
    
    // Verificar cupo disponible
    if ($aprobada == 1) {
        $verificarCupo = mysqli_query($conexion, 
            "SELECT c.cupo_maximo, COUNT(i.id) as inscritos 
             FROM clases c 
             LEFT JOIN inscripciones i ON c.id = i.clase_id AND i.aprobada = 1 
             WHERE c.id = $clase_id 
             GROUP BY c.id"
        );
        
        $cupo = mysqli_fetch_assoc($verificarCupo);
        if ($cupo && $cupo['inscritos'] >= $cupo['cupo_maximo']) {
            return ['ok' => false, 'msg' => 'La clase ha alcanzado su cupo máximo'];
        }
    }
    
    $fecha = date('Y-m-d H:i:s');
    $query = "INSERT INTO inscripciones (alumno_id, clase_id, aprobada, fecha_inscripcion) 
              VALUES ($alumno_id, $clase_id, $aprobada, '$fecha')";
    
    if (mysqli_query($conexion, $query)) {
        return ['ok' => true, 'id' => mysqli_insert_id($conexion)];
    } else {
        return ['ok' => false, 'msg' => mysqli_error($conexion)];
    }
}

// Aprobar inscripción
function aprobarInscripcion($conexion, $id) {
    $id = intval($id);
    
    // Verificar cupo disponible
    $verificarInscripcion = mysqli_query($conexion, "SELECT clase_id FROM inscripciones WHERE id = $id");
    $inscripcion = mysqli_fetch_assoc($verificarInscripcion);
    
    if ($inscripcion) {
        $clase_id = $inscripcion['clase_id'];
        
        $verificarCupo = mysqli_query($conexion, 
            "SELECT c.cupo_maximo, COUNT(i.id) as inscritos 
             FROM clases c 
             LEFT JOIN inscripciones i ON c.id = i.clase_id AND i.aprobada = 1 
             WHERE c.id = $clase_id 
             GROUP BY c.id"
        );
        
        $cupo = mysqli_fetch_assoc($verificarCupo);
        if ($cupo && $cupo['inscritos'] >= $cupo['cupo_maximo']) {
            return ['ok' => false, 'msg' => 'La clase ha alcanzado su cupo máximo'];
        }
    }
    
    $fecha = date('Y-m-d H:i:s');
    $query = "UPDATE inscripciones SET aprobada = 1, fecha_aprobacion = '$fecha' WHERE id = $id";
    
    if (mysqli_query($conexion, $query)) {
        return ['ok' => true];
    } else {
        return ['ok' => false, 'msg' => mysqli_error($conexion)];
    }
}

// Rechazar inscripción
function rechazarInscripcion($conexion, $id) {
    $id = intval($id);
    $query = "DELETE FROM inscripciones WHERE id = $id";
    
    if (mysqli_query($conexion, $query)) {
        return ['ok' => true];
    } else {
        return ['ok' => false, 'msg' => mysqli_error($conexion)];
    }
}

/**
 * FUNCIONES PARA CALIFICACIONES
 */

// Obtener calificaciones por alumno
function obtenerCalificacionesPorAlumno($conexion, $alumno_id) {
    $alumno_id = intval($alumno_id);
    $query = "SELECT c.*, cl.nombre as clase_nombre, p.nombre as profesor_nombre 
              FROM calificaciones c 
              JOIN clases cl ON c.clase_id = cl.id 
              LEFT JOIN usuarios p ON cl.profesor_id = p.id 
              WHERE c.alumno_id = $alumno_id";
    
    $resultado = mysqli_query($conexion, $query);
    
    $calificaciones = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $calificaciones[] = $row;
    }
    
    return $calificaciones;
}

// Obtener calificaciones por clase
function obtenerCalificacionesPorClase($conexion, $clase_id) {
    $clase_id = intval($clase_id);
    $query = "SELECT c.*, u.nombre as alumno_nombre 
              FROM calificaciones c 
              JOIN usuarios u ON c.alumno_id = u.id 
              WHERE c.clase_id = $clase_id 
              ORDER BY u.nombre ASC";
    
    $resultado = mysqli_query($conexion, $query);
    
    $calificaciones = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $calificaciones[] = $row;
    }
    
    return $calificaciones;
}

// Asignar calificación
function asignarCalificacion($conexion, $alumno_id, $clase_id, $calificacion, $observacion = '', $periodo = '') {
    $alumno_id = intval($alumno_id);
    $clase_id = intval($clase_id);
    $calificacion = floatval($calificacion);
    $observacion = limpiarEntrada($conexion, $observacion);
    $periodo = limpiarEntrada($conexion, $periodo);
    
    // Verificar si ya existe
    $verificar = mysqli_query($conexion, "SELECT 1 FROM calificaciones WHERE alumno_id = $alumno_id AND clase_id = $clase_id");
    
    if (mysqli_num_rows($verificar) > 0) {
        $query = "UPDATE calificaciones 
                  SET calificacion = $calificacion, observacion = '$observacion', periodo_evaluacion = '$periodo' 
                  WHERE alumno_id = $alumno_id AND clase_id = $clase_id";
    } else {
        $query = "INSERT INTO calificaciones (alumno_id, clase_id, calificacion, observacion, periodo_evaluacion) 
                  VALUES ($alumno_id, $clase_id, $calificacion, '$observacion', '$periodo')";
    }
    
    if (mysqli_query($conexion, $query)) {
        return ['ok' => true];
    } else {
        return ['ok' => false, 'msg' => mysqli_error($conexion)];
    }
}

// Eliminar calificación
function eliminarCalificacion($conexion, $alumno_id, $clase_id) {
    $alumno_id = intval($alumno_id);
    $clase_id = intval($clase_id);
    
    $query = "DELETE FROM calificaciones WHERE alumno_id = $alumno_id AND clase_id = $clase_id";
    
    if (mysqli_query($conexion, $query)) {
        return ['ok' => true];
    } else {
        return ['ok' => false, 'msg' => mysqli_error($conexion)];
    }
}

/**
 * FUNCIONES PARA TAREAS
 */

// Obtener tareas por clase
function obtenerTareasPorClase($conexion, $clase_id) {
    $clase_id = intval($clase_id);
    $query = "SELECT * FROM tareas WHERE clase_id = $clase_id ORDER BY fecha_entrega ASC";
    
    $resultado = mysqli_query($conexion, $query);
    
    $tareas = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $tareas[] = $row;
    }
    
    return $tareas;
}

// Crear tarea
function crearTarea($conexion, $clase_id, $titulo, $descripcion, $fecha_entrega, $archivo = '') {
    $clase_id = intval($clase_id);
    $titulo = limpiarEntrada($conexion, $titulo);
    $descripcion = limpiarEntrada($conexion, $descripcion);
    $fecha_entrega = limpiarEntrada($conexion, $fecha_entrega);
    $archivo = limpiarEntrada($conexion, $archivo);
    
    $query = "INSERT INTO tareas (clase_id, titulo, descripcion, fecha_entrega, archivo) 
              VALUES ($clase_id, '$titulo', '$descripcion', '$fecha_entrega', '$archivo')";
    
    if (mysqli_query($conexion, $query)) {
        return ['ok' => true, 'id' => mysqli_insert_id($conexion)];
    } else {
        return ['ok' => false, 'msg' => mysqli_error($conexion)];
    }
}

// Actualizar tarea
function actualizarTarea($conexion, $id, $datos) {
    $id = intval($id);
    $campos = [];
    
    foreach ($datos as $campo => $valor) {
        if ($campo != 'id') {
            $valor = limpiarEntrada($conexion, $valor);
            $campos[] = "$campo = '$valor'";
        }
    }
    
    $camposStr = implode(', ', $campos);
    $query = "UPDATE tareas SET $camposStr WHERE id = $id";
    
    if (mysqli_query($conexion, $query)) {
        return ['ok' => true];
    } else {
        return ['ok' => false, 'msg' => mysqli_error($conexion)];
    }
}

// Eliminar tarea
function eliminarTarea($conexion, $id) {
    $id = intval($id);
    $query = "DELETE FROM tareas WHERE id = $id";
    
    if (mysqli_query($conexion, $query)) {
        return ['ok' => true];
    } else {
        return ['ok' => false, 'msg' => mysqli_error($conexion)];
    }
}

/**
 * FUNCIONES PARA AVISOS
 */

// Obtener avisos por clase
function obtenerAvisosPorClase($conexion, $clase_id) {
    $clase_id = intval($clase_id);
    $query = "SELECT a.*, p.nombre as profesor_nombre 
              FROM avisos a 
              JOIN usuarios p ON a.profesor_id = p.id 
              WHERE a.clase_id = $clase_id 
              ORDER BY a.fecha DESC";
    
    $resultado = mysqli_query($conexion, $query);
    
    $avisos = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $avisos[] = $row;
    }
    
    return $avisos;
}

// Obtener avisos para alumno
function obtenerAvisosParaAlumno($conexion, $alumno_id) {
    $alumno_id = intval($alumno_id);
    $query = "SELECT a.*, c.nombre as clase_nombre, p.nombre as profesor_nombre 
              FROM avisos a 
              JOIN clases c ON a.clase_id = c.id 
              JOIN usuarios p ON a.profesor_id = p.id 
              JOIN inscripciones i ON i.clase_id = c.id 
              WHERE i.alumno_id = $alumno_id AND i.aprobada = 1 
              ORDER BY a.fecha DESC";
    
    $resultado = mysqli_query($conexion, $query);
    
    $avisos = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $avisos[] = $row;
    }
    
    return $avisos;
}

// Crear aviso
function crearAviso($conexion, $clase_id, $profesor_id, $titulo, $mensaje, $urgente = 0) {
    $clase_id = intval($clase_id);
    $profesor_id = intval($profesor_id);
    $titulo = limpiarEntrada($conexion, $titulo);
    $mensaje = limpiarEntrada($conexion, $mensaje);
    $urgente = intval($urgente);
    
    $query = "INSERT INTO avisos (clase_id, profesor_id, titulo, mensaje, urgente) 
              VALUES ($clase_id, $profesor_id, '$titulo', '$mensaje', $urgente)";
    
    if (mysqli_query($conexion, $query)) {
        return ['ok' => true, 'id' => mysqli_insert_id($conexion)];
    } else {
        return ['ok' => false, 'msg' => mysqli_error($conexion)];
    }
}

// Eliminar aviso
function eliminarAviso($conexion, $id, $profesor_id) {
    $id = intval($id);
    $profesor_id = intval($profesor_id);
    
    $query = "DELETE FROM avisos WHERE id = $id AND profesor_id = $profesor_id";
    
    if (mysqli_query($conexion, $query)) {
        return ['ok' => true];
    } else {
        return ['ok' => false, 'msg' => mysqli_error($conexion)];
    }
}

/**
 * FUNCIONES PARA COMENTARIOS DE AVISOS
 */

// Obtener comentarios de un aviso
function obtenerComentariosAviso($conexion, $aviso_id) {
    $aviso_id = intval($aviso_id);
    $query = "SELECT c.*, p.nombre as padre_nombre 
              FROM avisos_comentarios c 
              JOIN usuarios p ON c.padre_id = p.id 
              WHERE c.aviso_id = $aviso_id 
              ORDER BY c.fecha DESC";
    
    $resultado = mysqli_query($conexion, $query);
    
    $comentarios = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $comentarios[] = $row;
    }
    
    return $comentarios;
}

// Crear comentario
function crearComentarioAviso($conexion, $aviso_id, $padre_id, $mensaje) {
    $aviso_id = intval($aviso_id);
    $padre_id = intval($padre_id);
    $mensaje = limpiarEntrada($conexion, $mensaje);
    
    $query = "INSERT INTO avisos_comentarios (aviso_id, padre_id, mensaje) 
              VALUES ($aviso_id, $padre_id, '$mensaje')";
    
    if (mysqli_query($conexion, $query)) {
        return ['ok' => true, 'id' => mysqli_insert_id($conexion)];
    } else {
        return ['ok' => false, 'msg' => mysqli_error($conexion)];
    }
}

// Eliminar comentario
function eliminarComentarioAviso($conexion, $id, $padre_id) {
    $id = intval($id);
    $padre_id = intval($padre_id);
    
    $query = "DELETE FROM avisos_comentarios WHERE id = $id AND padre_id = $padre_id";
    
    if (mysqli_query($conexion, $query)) {
        return ['ok' => true];
    } else {
        return ['ok' => false, 'msg' => mysqli_error($conexion)];
    }
}

/**
 * FUNCIONES PARA REPORTES
 */

// Obtener reportes por alumno
function obtenerReportesPorAlumno($conexion, $alumno_id) {
    $alumno_id = intval($alumno_id);
    $query = "SELECT r.*, c.nombre as clase_nombre, p.nombre as profesor_nombre 
              FROM reportes r 
              JOIN clases c ON r.clase_id = c.id 
              JOIN usuarios p ON r.profesor_id = p.id 
              WHERE r.alumno_id = $alumno_id 
              ORDER BY r.fecha DESC";
    
    $resultado = mysqli_query($conexion, $query);
    
    $reportes = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $reportes[] = $row;
    }
    
    return $reportes;
}

// Obtener reportes por clase
function obtenerReportesPorClase($conexion, $clase_id) {
    $clase_id = intval($clase_id);
    $query = "SELECT r.*, a.nombre as alumno_nombre, p.nombre as profesor_nombre 
              FROM reportes r 
              JOIN usuarios a ON r.alumno_id = a.id 
              JOIN usuarios p ON r.profesor_id = p.id 
              WHERE r.clase_id = $clase_id 
              ORDER BY r.fecha DESC";
    
    $resultado = mysqli_query($conexion, $query);
    
    $reportes = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $reportes[] = $row;
    }
    
    return $reportes;
}

// Crear reporte
function crearReporte($conexion, $clase_id, $alumno_id, $profesor_id, $titulo, $descripcion, $tipo = 'academico') {
    $clase_id = intval($clase_id);
    $alumno_id = intval($alumno_id);
    $profesor_id = intval($profesor_id);
    $titulo = limpiarEntrada($conexion, $titulo);
    $descripcion = limpiarEntrada($conexion, $descripcion);
    $tipo = limpiarEntrada($conexion, $tipo);
    
    $query = "INSERT INTO reportes (clase_id, alumno_id, profesor_id, titulo, descripcion, tipo_reporte) 
              VALUES ($clase_id, $alumno_id, $profesor_id, '$titulo', '$descripcion', '$tipo')";
    
    if (mysqli_query($conexion, $query)) {
        return ['ok' => true, 'id' => mysqli_insert_id($conexion)];
    } else {
        return ['ok' => false, 'msg' => mysqli_error($conexion)];
    }
}

// Eliminar reporte
function eliminarReporte($conexion, $id, $profesor_id) {
    $id = intval($id);
    $profesor_id = intval($profesor_id);
    
    $query = "DELETE FROM reportes WHERE id = $id AND profesor_id = $profesor_id";
    
    if (mysqli_query($conexion, $query)) {
        return ['ok' => true];
    } else {
        return ['ok' => false, 'msg' => mysqli_error($conexion)];
    }
}

/**
 * FUNCIONES PARA MENSAJES PADRE-PROFESOR
 */

// Obtener mensajes entre padre y profesor
function obtenerMensajesPadreProfesor($conexion, $padre_id, $profesor_id) {
    $padre_id = intval($padre_id);
    $profesor_id = intval($profesor_id);
    
    $query = "SELECT * FROM mensajes_padre_profesor 
              WHERE (padre_id = $padre_id AND profesor_id = $profesor_id) 
              ORDER BY fecha ASC";
    
    $resultado = mysqli_query($conexion, $query);
    
    $mensajes = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $mensajes[] = $row;
    }
    
    return $mensajes;
}

// Enviar mensaje
function enviarMensaje($conexion, $padre_id, $profesor_id, $mensaje, $de_quien) {
    $padre_id = intval($padre_id);
    $profesor_id = intval($profesor_id);
    $mensaje = limpiarEntrada($conexion, $mensaje);
    $de_quien = limpiarEntrada($conexion, $de_quien);
    
    $query = "INSERT INTO mensajes_padre_profesor (padre_id, profesor_id, mensaje, de_quien) 
              VALUES ($padre_id, $profesor_id, '$mensaje', '$de_quien')";
    
    if (mysqli_query($conexion, $query)) {
        return ['ok' => true, 'id' => mysqli_insert_id($conexion)];
    } else {
        return ['ok' => false, 'msg' => mysqli_error($conexion)];
    }
}

// Eliminar mensaje
function eliminarMensaje($conexion, $id, $usuario_id, $es_padre) {
    $id = intval($id);
    $usuario_id = intval($usuario_id);
    
    if ($es_padre) {
        $query = "DELETE FROM mensajes_padre_profesor WHERE id = $id AND padre_id = $usuario_id";
    } else {
        $query = "DELETE FROM mensajes_padre_profesor WHERE id = $id AND profesor_id = $usuario_id";
    }
    
    if (mysqli_query($conexion, $query)) {
        return ['ok' => true];
    } else {
        return ['ok' => false, 'msg' => mysqli_error($conexion)];
    }
}

/**
 * FUNCIONES PARA HIJOS (RELACIÓN PADRE-ALUMNO)
 */

// Obtener hijos de un padre
function obtenerHijosPadre($conexion, $padre_id) {
    $padre_id = intval($padre_id);
    $query = "SELECT h.*, a.nombre as alumno_nombre, a.email as alumno_email 
              FROM hijos h 
              JOIN usuarios a ON h.alumno_id = a.id 
              WHERE h.padre_id = $padre_id";
    
    $resultado = mysqli_query($conexion, $query);
    
    $hijos = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $hijos[] = $row;
    }
    
    return $hijos;
}

// Vincular hijo a padre
function vincularHijo($conexion, $padre_id, $alumno_id, $parentesco = 'padre') {
    $padre_id = intval($padre_id);
    $alumno_id = intval($alumno_id);
    $parentesco = limpiarEntrada($conexion, $parentesco);
    
    // Verificar si ya existe
    $verificar = mysqli_query($conexion, "SELECT id FROM hijos WHERE padre_id = $padre_id AND alumno_id = $alumno_id");
    if (mysqli_num_rows($verificar) > 0) {
        return ['ok' => false, 'msg' => 'Este alumno ya está vinculado a este padre'];
    }
    
    $query = "INSERT INTO hijos (padre_id, alumno_id, parentesco) 
              VALUES ($padre_id, $alumno_id, '$parentesco')";
    
    if (mysqli_query($conexion, $query)) {
        return ['ok' => true, 'id' => mysqli_insert_id($conexion)];
    } else {
        return ['ok' => false, 'msg' => mysqli_error($conexion)];
    }
}

// Desvincular hijo de padre
function desvincularHijo($conexion, $id, $padre_id) {
    $id = intval($id);
    $padre_id = intval($padre_id);
    
    $query = "DELETE FROM hijos WHERE id = $id AND padre_id = $padre_id";
    
    if (mysqli_query($conexion, $query)) {
        return ['ok' => true];
    } else {
        return ['ok' => false, 'msg' => mysqli_error($conexion)];
    }
}

/**
 * FUNCIONES PARA SOLICITUDES DE ACCIÓN
 */

// Obtener solicitudes pendientes
function obtenerSolicitudesPendientes($conexion) {
    $query = "SELECT s.*, u.nombre as usuario_nombre, sol.nombre as solicitante_nombre 
              FROM solicitudes_accion s 
              JOIN usuarios u ON s.usuario_id = u.id 
              JOIN usuarios sol ON s.solicitante_id = sol.id 
              WHERE s.estado = 'pendiente' 
              ORDER BY s.fecha_solicitud DESC";
    
    $resultado = mysqli_query($conexion, $query);
    
    $solicitudes = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $solicitudes[] = $row;
    }
    
    return $solicitudes;
}

// Crear solicitud
function crearSolicitud($conexion, $tipo, $usuario_id, $solicitante_id, $motivo = '') {
    $tipo = limpiarEntrada($conexion, $tipo);
    $usuario_id = intval($usuario_id);
    $solicitante_id = intval($solicitante_id);
    $motivo = limpiarEntrada($conexion, $motivo);
    
    $query = "INSERT INTO solicitudes_accion (tipo, usuario_id, solicitante_id, motivo) 
              VALUES ('$tipo', $usuario_id, $solicitante_id, '$motivo')";
    
    if (mysqli_query($conexion, $query)) {
        return ['ok' => true, 'id' => mysqli_insert_id($conexion)];
    } else {
        return ['ok' => false, 'msg' => mysqli_error($conexion)];
    }
}

// Aprobar solicitud
function aprobarSolicitud($conexion, $id, $respuesta = '') {
    $id = intval($id);
    $respuesta = limpiarEntrada($conexion, $respuesta);
    $fecha = date('Y-m-d H:i:s');
    
    $query = "UPDATE solicitudes_accion 
              SET estado = 'aprobada', respuesta = '$respuesta', fecha_respuesta = '$fecha' 
              WHERE id = $id";
    
    if (mysqli_query($conexion, $query)) {
        return ['ok' => true];
    } else {
        return ['ok' => false, 'msg' => mysqli_error($conexion)];
    }
}

// Rechazar solicitud
function rechazarSolicitud($conexion, $id, $respuesta = '') {
    $id = intval($id);
    $respuesta = limpiarEntrada($conexion, $respuesta);
    $fecha = date('Y-m-d H:i:s');
    
    $query = "UPDATE solicitudes_accion 
              SET estado = 'rechazada', respuesta = '$respuesta', fecha_respuesta = '$fecha' 
              WHERE id = $id";
    
    if (mysqli_query($conexion, $query)) {
        return ['ok' => true];
    } else {
        return ['ok' => false, 'msg' => mysqli_error($conexion)];
    }
}

/**
 * FUNCIONES PARA ESTADÍSTICAS Y REPORTES
 */

// Obtener estadísticas de clases
function obtenerEstadisticasClases($conexion) {
    $query = "SELECT 
                COUNT(DISTINCT c.id) as total_clases,
                COUNT(DISTINCT c.profesor_id) as total_profesores,
                SUM(CASE WHEN i.aprobada = 1 THEN 1 ELSE 0 END) as total_inscripciones,
                AVG(cal.calificacion) as promedio_calificaciones
              FROM clases c
              LEFT JOIN inscripciones i ON c.id = i.clase_id
              LEFT JOIN calificaciones cal ON i.clase_id = cal.clase_id AND i.alumno_id = cal.alumno_id";
    
    $resultado = mysqli_query($conexion, $query);
    return mysqli_fetch_assoc($resultado);
}

// Obtener estadísticas de alumnos
function obtenerEstadisticasAlumnos($conexion) {
    $query = "SELECT 
                COUNT(DISTINCT u.id) as total_alumnos,
                COUNT(DISTINCT i.clase_id) as total_clases_inscritas,
                AVG(cal.calificacion) as promedio_general
              FROM usuarios u
              JOIN inscripciones i ON u.id = i.alumno_id AND i.aprobada = 1
              LEFT JOIN calificaciones cal ON i.alumno_id = cal.alumno_id AND i.clase_id = cal.clase_id
              WHERE u.rol_id = 1";
    
    $resultado = mysqli_query($conexion, $query);
    return mysqli_fetch_assoc($resultado);
}

// Generar reporte de calificaciones por clase
function generarReporteCalificacionesClase($conexion, $clase_id) {
    $clase_id = intval($clase_id);
    $query = "SELECT 
                c.nombre as clase,
                u.nombre as alumno,
                cal.calificacion,
                cal.observacion,
                cal.periodo_evaluacion
              FROM calificaciones cal
              JOIN usuarios u ON cal.alumno_id = u.id
              JOIN clases c ON cal.clase_id = c.id
              WHERE cal.clase_id = $clase_id
              ORDER BY u.nombre ASC";
    
    $resultado = mysqli_query($conexion, $query);
    
    $reporte = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $reporte[] = $row;
    }
    
    return $reporte;
}

// Generar reporte de asistencia (si se implementa en el futuro)
function generarReporteAsistencia($conexion, $clase_id, $fecha_inicio, $fecha_fin) {
    // Esta función es un placeholder para una futura implementación
    // de un sistema de asistencia
    return ['ok' => false, 'msg' => 'Funcionalidad no implementada'];
}

?>
