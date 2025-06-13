<?php
session_start();
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] != 3) {
    header('Location: ../public.php?error=Acceso%20no%20autorizado');
    exit;
}
$nombre = $_SESSION['usuario_nombre'] ?? '';
$rol_nombre = $_SESSION['usuario_rol_nombre'] ?? 'coordinador';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Panel Coordinador | Mini Mundo Lingua</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet" />
</head>
<body>
    <div class="sidebar d-flex flex-column p-4">
        <img src="../img/avatar-coordinador.png" class="brand-avatar mb-2" alt="Coordinador avatar" />
        <h3 class="mb-4 fw-bold text-center">Coordinador <i class="bi bi-person-badge"></i></h3>
        <nav class="nav flex-column">
            <a class="nav-link active" href="#" onclick="mostrarSeccion('inicio', event)" title="Inicio"><i class="bi bi-house-door"></i> Inicio</a>
            <a class="nav-link" href="#" onclick="mostrarSeccion('clases', event)" title="Gestión de clases"><i class="bi bi-easel2"></i> Clases</a>
            <a class="nav-link" href="#" onclick="mostrarSeccion('profesores', event)" title="Gestión de profesores"><i class="bi bi-person-video2"></i> Profesores</a>
            <a class="nav-link" href="#" onclick="mostrarSeccion('alumnos', event)" title="Alumnos en clases"><i class="bi bi-people"></i> Alumnos</a>
            <a class="nav-link" href="#" onclick="mostrarSeccion('inscripciones', event)" title="Aprobar inscripciones"><i class="bi bi-person-check"></i> Inscripciones</a>
            <a class="nav-link" href="#" onclick="mostrarSeccion('perfil', event)" title="Editar perfil"><i class="bi bi-person-circle"></i> Mi perfil</a>
            <a class="nav-link mt-4" href="../includes/logout.php" title="Cerrar sesión"><i class="bi bi-box-arrow-left"></i> Cerrar sesión</a>
        </nav>
    </div>
    <div class="main-content">
        <div class="topbar d-flex justify-content-between align-items-center">
            <span class="fw-semibold fs-5">Bienvenido, <?= htmlspecialchars($nombre) ?></span>
            <span class="badge bg-info fs-6 me-2"><i class="bi bi-person-badge"></i> Coordinador</span>
        </div>

        <!-- INICIO -->
        <div id="inicio" class="pantalla">
            <h2 class="mb-3">Panel principal del Coordinador</h2>
            <div class="row quick-cards mb-5">
                <div class="col-md-4 mb-4">
                    <div class="card text-center border-0" onclick="mostrarSeccion('clases', event)">
                        <div class="card-body">
                            <i class="bi bi-easel2 text-success"></i>
                            <h5 class="card-title mt-2">Clases</h5>
                            <p class="card-text">Gestiona las clases asignadas, profesores y alumnos.</p>
                            <button class="btn btn-outline-success btn-sm">Ir a clases</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card text-center border-0" onclick="mostrarSeccion('profesores', event)">
                        <div class="card-body">
                            <i class="bi bi-person-video2 text-primary"></i>
                            <h5 class="card-title mt-2">Profesores</h5>
                            <p class="card-text">Asigna y administra profesores de tus clases.</p>
                            <button class="btn btn-outline-primary btn-sm">Ir a profesores</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card text-center border-0" onclick="mostrarSeccion('inscripciones', event)">
                        <div class="card-body">
                            <i class="bi bi-person-check text-warning"></i>
                            <h5 class="card-title mt-2">Inscripciones</h5>
                            <p class="card-text">Aprueba inscripciones de alumnos en tus clases.</p>
                            <button class="btn btn-outline-warning btn-sm">Ver inscripciones</button>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Utiliza el menú lateral para navegar entre las secciones del sistema.
            </div>
        </div>

        <!-- CLASES -->
        <div id="clases" class="pantalla d-none">
            <h3 class="mb-3"><i class="bi bi-easel2"></i> Mis clases</h3>
            <div id="form-clase"></div>
            <div class="table-responsive" id="tabla-clases"></div>
        </div>
        <!-- PROFESORES -->
        <div id="profesores" class="pantalla d-none">
            <h3 class="mb-3"><i class="bi bi-person-video2"></i> Profesores asignados</h3>
            <div id="form-profesor"></div>
            <div class="table-responsive" id="tabla-profesores"></div>
        </div>
        <!-- ALUMNOS -->
        <div id="alumnos" class="pantalla d-none">
            <h3 class="mb-3"><i class="bi bi-people"></i> Alumnos por clase</h3>
            <div id="form-alumno"></div>
            <div class="table-responsive" id="tabla-alumnos"></div>
        </div>
        <!-- INSCRIPCIONES -->
        <div id="inscripciones" class="pantalla d-none">
            <h3 class="mb-3"><i class="bi bi-person-check"></i> Inscripciones pendientes</h3>
            <div id="form-inscripcion"></div>
            <div class="table-responsive" id="tabla-inscripciones"></div>
        </div>
        <!-- PERFIL -->
        <div id="perfil" class="pantalla d-none">
            <h3 class="mb-3"><i class="bi bi-person-circle"></i> Mi perfil</h3>
            <div class="table-responsive" id="tabla-perfil"></div>
        </div>
        <div class="footer-admin">
            &copy; <?=date('Y')?> Mini Mundo Lingua • Panel de Coordinación
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Para que JS pueda saber el rol actual (útil para cargar perfil)
        window.usuario_rol = "<?= htmlspecialchars($rol_nombre) ?>";
    </script>
    <script type="module" src="../assets/Js/coordinador.js"></script>
</body>
</html>