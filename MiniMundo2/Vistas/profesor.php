<?php
session_start();
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] != 2) {
    header('Location: ../public.php?error=Acceso%20no%20autorizado');
    exit;
}
$nombre = $_SESSION['usuario_nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Panel Profesor | Mini Mundo Lingua</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/profesor.css" rel="stylesheet" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
</head>
<body>
    <div class="container-fluid g-0">
        <div class="row g-0 min-vh-100">
            <!-- Sidebar -->
            <nav class="col-12 col-md-3 col-lg-2 bg-success text-white sidebar p-4 d-flex flex-column align-items-center">
                <img src="../img/avatar-profesor.png" class="brand-avatar mb-3 shadow" alt="Profesor avatar" width="90" height="90" />
                <h3 class="mb-4 fw-bold text-center">Profesor <i class="bi bi-person-badge"></i></h3>
                <ul class="nav nav-pills flex-column w-100 mb-auto">
                    <li class="nav-item">
                        <a class="nav-link text-white active" href="#" onclick="mostrarSeccion('inicio', event)">
                            <i class="bi bi-house-door"></i> Inicio
                        </a>
                    </li>
                    <li>
                        <a class="nav-link text-white" href="#" onclick="mostrarSeccion('misclases', event)">
                            <i class="bi bi-easel2"></i> Mis clases
                        </a>
                    </li>
                    <li>
                        <a class="nav-link text-white" href="#" onclick="mostrarSeccion('avisos', event)">
                            <i class="bi bi-megaphone"></i> Avisos
                        </a>
                    </li>
                    <li>
                        <a class="nav-link text-white" href="#" onclick="mostrarSeccion('reportes', event)">
                            <i class="bi bi-flag"></i> Reportes
                        </a>
                    </li>
                    <li>
                        <a class="nav-link text-white" href="#" onclick="mostrarSeccion('mensajes', event)">
                            <i class="bi bi-chat-dots"></i> Mensajes a padres
                        </a>
                    </li>
                    <li>
                        <a class="nav-link text-white" href="#" onclick="mostrarSeccion('perfil', event)">
                            <i class="bi bi-person-circle"></i> Mi perfil
                        </a>
                    </li>
                    <li class="mt-4 border-top pt-3">
                        <a class="nav-link text-white" href="../includes/logout.php">
                            <i class="bi bi-box-arrow-left"></i> Cerrar sesión
                        </a>
                    </li>
                </ul>
                <div class="mt-auto d-none d-md-block small text-white-50 text-center">
                    &copy; <?=date('Y')?> Mini Mundo Lingua
                </div>
            </nav>
            <!-- Main content -->
            <main class="col-12 col-md-9 col-lg-10 p-4 main-content bg-light">
                <div class="topbar d-flex justify-content-between align-items-center mb-4">
                    <span class="fw-semibold fs-5">Bienvenido, <?= htmlspecialchars($nombre) ?></span>
                    <span class="badge bg-success fs-6 me-2"><i class="bi bi-person-badge"></i> Profesor</span>
                </div>
                <!-- INICIO -->
                <div id="inicio" class="pantalla">
                    <div class="p-4 bg-white rounded shadow-sm mb-4">
                        <h2 class="mb-3">Panel principal del Profesor</h2>
                        <div class="alert alert-success mb-0">
                            <i class="bi bi-info-circle"></i> Usa el menú lateral para gestionar tus clases, tareas, avisos, reportes y mensajes.
                        </div>
                    </div>
                </div>
                <!-- MIS CLASES -->
                <div id="misclases" class="pantalla d-none">
                    <div class="p-4 bg-white rounded shadow-sm mb-4">
                        <h3 class="mb-3"><i class="bi bi-easel2"></i> Mis clases</h3>
                        <div id="tabla-misclases"></div>
                        <div id="contenido-clase"></div>
                    </div>
                </div>
                <!-- AVISOS -->
                <div id="avisos" class="pantalla d-none">
                    <div class="p-4 bg-white rounded shadow-sm mb-4">
                        <h3 class="mb-3"><i class="bi bi-megaphone"></i> Avisos a padres</h3>
                        <div id="avisos-profesor"></div>
                    </div>
                </div>
                <!-- REPORTES -->
                <div id="reportes" class="pantalla d-none">
                    <div class="p-4 bg-white rounded shadow-sm mb-4">
                        <h3 class="mb-3"><i class="bi bi-flag"></i> Reportes de alumnos</h3>
                        <div id="reportes-profesor"></div>
                    </div>
                </div>
                <!-- MENSAJES PROFESOR-PADRE -->
                <div id="mensajes" class="pantalla d-none">
                    <div class="p-4 bg-white rounded shadow-sm mb-4">
                        <h3 class="mb-3"><i class="bi bi-chat-dots"></i> Mensajes con padres</h3>
                        <div id="mensajes-profesor"></div>
                    </div>
                </div>
                <!-- PERFIL -->
                <div id="perfil" class="pantalla d-none">
                    <div class="p-4 bg-white rounded shadow-sm mb-4">
                        <h3 class="mb-3"><i class="bi bi-person-circle"></i> Mi perfil</h3>
                        <div id="tabla-perfil"></div>
                    </div>
                </div>
                <div class="footer-admin text-center d-md-none mt-4 small text-black-50">
                    &copy; <?=date('Y')?> Mini Mundo Lingua • Panel del Profesor
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>window.usuario_rol = "<?= $_SESSION['usuario_rol_nombre'] ?>";</script>
    <script type="module" src="../assets/Js/profesor.js"></script>
</body>
</html>