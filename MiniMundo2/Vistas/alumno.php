<?php
session_start();
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] != 1) {
    header('Location: ../public.php?error=Acceso%20no%20autorizado');
    exit;
}
$nombre = $_SESSION['usuario_nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Panel Alumno | Mini Mundo Lingua</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/alumno.css" rel="stylesheet" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
</head>
<body>
    <div class="container-fluid g-0">
        <div class="row g-0 min-vh-100">
            <!-- Sidebar -->
            <nav class="col-12 col-md-3 col-lg-2 bg-primary text-white sidebar p-4 d-flex flex-column align-items-center">
                <img src="../img/avatar-alumno.png" class="brand-avatar mb-3 shadow" alt="Alumno avatar" width="90" height="90" />
                <h3 class="mb-4 fw-bold text-center">Alumno <i class="bi bi-person"></i></h3>
                <ul class="nav nav-pills flex-column w-100 mb-auto">
                    <li class="nav-item">
                        <a class="nav-link text-white active" href="#" data-seccion="inicio">
                            <i class="bi bi-house-door"></i> Inicio
                        </a>
                    </li>
                    <li>
                        <a class="nav-link text-white" href="#" data-seccion="clases">
                            <i class="bi bi-easel2"></i> Mis clases
                        </a>
                    </li>
                    <li>
                        <a class="nav-link text-white" href="#" data-seccion="avisos">
                            <i class="bi bi-megaphone"></i> Avisos
                        </a>
                    </li>
                    <li>
                        <a class="nav-link text-white" href="#" data-seccion="tareas">
                            <i class="bi bi-journal-text"></i> Tareas
                        </a>
                    </li>
                    <li>
                        <a class="nav-link text-white" href="#" data-seccion="calificaciones">
                            <i class="bi bi-clipboard-check"></i> Calificaciones
                        </a>
                    </li>
                    <li>
                        <a class="nav-link text-white" href="#" data-seccion="reportes">
                            <i class="bi bi-flag"></i> Reportes
                        </a>
                    </li>
                    <!-- Aquí puedes agregar el menú de mensajes si lo deseas -->
                    <li>
                        <a class="nav-link text-white" href="#" data-seccion="perfil">
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
                    <span class="badge bg-primary fs-6 me-2"><i class="bi bi-person"></i> Alumno</span>
                </div>
                <div id="inicio" class="pantalla">
                    <div class="p-4 bg-white rounded shadow-sm mb-4">
                        <h2 class="mb-3">Panel principal del Alumno</h2>
                        <div class="alert alert-primary mb-0">
                            <i class="bi bi-info-circle"></i> Usa el menú lateral para consultar tus clases, tareas, avisos, reportes y calificaciones.
                        </div>
                    </div>
                </div>
                <div id="clases" class="pantalla d-none">
                    <div class="p-4 bg-white rounded shadow-sm mb-4">
                        <h3 class="mb-3"><i class="bi bi-easel2"></i> Mis clases</h3>
                        <div id="tabla-clases"></div>
                        <div id="contenido-clase"></div>
                    </div>
                </div>
                <div id="avisos" class="pantalla d-none">
                    <div class="p-4 bg-white rounded shadow-sm mb-4">
                        <h3 class="mb-3"><i class="bi bi-megaphone"></i> Avisos</h3>
                        <div id="avisos-alumno"></div>
                    </div>
                </div>
                <div id="tareas" class="pantalla d-none">
                    <div class="p-4 bg-white rounded shadow-sm mb-4">
                        <h3 class="mb-3"><i class="bi bi-journal-text"></i> Tareas</h3>
                        <div id="tareas-alumno"></div>
                    </div>
                </div>
                <div id="calificaciones" class="pantalla d-none">
                    <div class="p-4 bg-white rounded shadow-sm mb-4">
                        <h3 class="mb-3"><i class="bi bi-clipboard-check"></i> Calificaciones</h3>
                        <div id="calificaciones-alumno"></div>
                    </div>
                </div>
                <div id="reportes" class="pantalla d-none">
                    <div class="p-4 bg-white rounded shadow-sm mb-4">
                        <h3 class="mb-3"><i class="bi bi-flag"></i> Reportes</h3>
                        <div id="reportes-alumno"></div>
                    </div>
                </div>
                <!-- Mensajes (si lo implementamos) -->
                <div id="mensajes" class="pantalla d-none">
                    <div class="p-4 bg-white rounded shadow-sm mb-4">
                        <h3 class="mb-3"><i class="bi bi-chat-dots"></i> Mensajes con profesor</h3>
                        <div id="mensajes-alumno"></div>
                    </div>
                </div>
                <div id="perfil" class="pantalla d-none">
                    <div class="p-4 bg-white rounded shadow-sm mb-4">
                        <h3 class="mb-3"><i class="bi bi-person-circle"></i> Mi perfil</h3>
                        <div id="tabla-perfil"></div>
                    </div>
                </div>
                <div class="footer-admin text-center d-md-none mt-4 small text-black-50">
                    &copy; <?=date('Y')?> Mini Mundo Lingua • Panel del Alumno
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="../assets/Js/alumno.js"></script>
</body>
</html>