<?php
session_start();
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] != 5) {
    header('Location: ../public.php?error=Acceso%20no%20autorizado');
    exit;
}
$nombre = $_SESSION['usuario_nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Panel Admin | Mini Mundo Lingua</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet" />
</head>
<body>
    <div class="sidebar d-flex flex-column p-4">
        <img src="../img/avatar-admin.png" class="brand-avatar mb-2" alt="Admin avatar" />
        <h3 class="mb-4 fw-bold text-center">Admin <i class="bi bi-shield-lock"></i></h3>
        <nav class="nav flex-column">
            <a class="nav-link active" href="#" onclick="mostrarSeccion('inicio', event)" title="Inicio"><i class="bi bi-house-door"></i> Inicio</a>
            <a class="nav-link" href="#" onclick="mostrarSeccion('usuarios', event)" title="Gestión de usuarios"><i class="bi bi-people"></i> Usuarios</a>
            <a class="nav-link" href="#" onclick="mostrarSeccion('clases', event)" title="Gestión de clases"><i class="bi bi-easel2"></i> Clases</a>
            <a class="nav-link" href="#" onclick="mostrarSeccion('coordinadores', event)" title="Gestión de coordinadores"><i class="bi bi-person-badge"></i> Coordinadores</a>
            <a class="nav-link" href="#" onclick="mostrarSeccion('directores', event)" title="Gestión de directores"><i class="bi bi-award"></i> Directores</a>
            <a class="nav-link" href="#" onclick="mostrarSeccion('reportes', event)" title="Ver reportes y estadísticas"><i class="bi bi-bar-chart"></i> Reportes</a>
            <a class="nav-link" href="#" onclick="mostrarSeccion('aprobaciones', event)" title="Aprobar inscripciones"><i class="bi bi-person-check"></i> Inscripciones</a>
            <a class="nav-link" href="#" onclick="mostrarSeccion('configuracion', event)" title="Configuraciones avanzadas"><i class="bi bi-gear"></i> Configuración</a>
            <a class="nav-link mt-4" href="../includes/logout.php" title="Cerrar sesión"><i class="bi bi-box-arrow-left"></i> Cerrar sesión</a>
        </nav>
    </div>
    <div class="main-content">
        <div class="topbar">
            <div>
                <span class="fw-semibold fs-5">Bienvenido, <?= htmlspecialchars($nombre) ?></span>
            </div>
            <div class="d-flex align-items-center">
                <span class="badge bg-primary fs-6 me-2"><i class="bi bi-person-badge-fill"></i> Admin</span>
                <img src="../img/avatar-admin.png" class="avatar-admin" alt="Admin" />
            </div>
        </div>

        <!-- INICIO -->
        <div id="inicio" class="pantalla">
            <h2 class="mb-3">Panel principal del Administrador</h2>
            <div class="row quick-cards mb-5">
                <div class="col-md-4 mb-4">
                    <div class="card text-center border-0" onclick="mostrarSeccion('usuarios', event)">
                        <div class="card-body">
                            <i class="bi bi-people-fill text-primary"></i>
                            <h5 class="card-title mt-2">Usuarios</h5>
                            <p class="card-text">Gestiona altas, bajas y edición de usuarios de todos los roles.</p>
                            <button class="btn btn-outline-primary btn-sm">Ir a usuarios</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card text-center border-0" onclick="mostrarSeccion('clases', event)">
                        <div class="card-body">
                            <i class="bi bi-easel2 text-success"></i>
                            <h5 class="card-title mt-2">Clases</h5>
                            <p class="card-text">Crea, edita y elimina clases y asigna profesores.</p>
                            <button class="btn btn-outline-success btn-sm">Ir a clases</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card text-center border-0" onclick="mostrarSeccion('reportes', event)">
                        <div class="card-body">
                            <i class="bi bi-bar-chart text-warning"></i>
                            <h5 class="card-title mt-2">Reportes</h5>
                            <p class="card-text">Consulta estadísticas y reportes del sistema.</p>
                            <button class="btn btn-outline-warning btn-sm">Ir a reportes</button>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Utiliza el menú lateral para navegar entre las secciones del sistema.
            </div>
        </div>

        <!-- USUARIOS -->
        <div id="usuarios" class="pantalla d-none">
            <h3 class="mb-3"><i class="bi bi-people"></i> Gestión de usuarios</h3>
            <div class="table-responsive" id="tabla-usuarios"></div>
        </div>
        <!-- CLASES -->
        <div id="clases" class="pantalla d-none">
            <h3 class="mb-3"><i class="bi bi-easel2"></i> Gestión de clases</h3>
            <div class="table-responsive" id="tabla-clases"></div>
        </div>
        <!-- COORDINADORES -->
        <div id="coordinadores" class="pantalla d-none">
            <h3 class="mb-3"><i class="bi bi-person-badge"></i> Administrar Coordinadores</h3>
            <div class="table-responsive" id="tabla-coordinadores"></div>
        </div>
        <!-- DIRECTORES -->
        <div id="directores" class="pantalla d-none">
            <h3 class="mb-3"><i class="bi bi-award"></i> Administrar Directores</h3>
            <div class="table-responsive" id="tabla-directores"></div>
        </div>
        <!-- REPORTES -->
        <div id="reportes" class="pantalla d-none">
            <h3 class="mb-3"><i class="bi bi-bar-chart"></i> Reportes y estadísticas</h3>
            <div class="table-responsive" id="tabla-reportes"></div>
        </div>
        <!-- INSCRIPCIONES -->
        <div id="aprobaciones" class="pantalla d-none">
            <h3 class="mb-3"><i class="bi bi-person-check"></i> Aprobaciones de inscripciones</h3>
            <div class="table-responsive" id="tabla-aprobaciones"></div>
        </div>
        <!-- CONFIGURACION -->
        <div id="configuracion" class="pantalla d-none">
            <h3 class="mb-3"><i class="bi bi-gear"></i> Configuración global</h3>
            <div class="table-responsive">
                <p>Próximamente opciones para personalización avanzada del sistema.</p>
            </div>
        </div>
        <div class="footer-admin">
            &copy; <?=date('Y')?> Mini Mundo Lingua • Panel de Administración
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/Js/admin.js"></script>
</body>
</html>