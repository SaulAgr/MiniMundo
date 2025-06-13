<?php
session_start();

if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] != 4) {
    header('Location: ../public.php?error=Acceso%20no%20autorizado');
    exit;
}

$nombre = $_SESSION['usuario_nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Panel Director | Mini Mundo Lingua</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet" />
</head>
<body>
    <div class="sidebar d-flex flex-column p-4">
        <img src="../img/avatar-director.png" class="brand-avatar mb-2" alt="Director avatar" />
        <h3 class="mb-4 fw-bold text-center">Director <i class="bi bi-award"></i></h3>
        <nav class="nav flex-column">
            <a class="nav-link active" href="#" onclick="mostrarSeccion('inicio', event)" title="Inicio"><i class="bi bi-house-door"></i> Inicio</a>
            <a class="nav-link" href="#" onclick="mostrarSeccion('usuarios', event)" title="Gestión de usuarios"><i class="bi bi-people"></i> Usuarios</a>
            <a class="nav-link" href="#" onclick="mostrarSeccion('clases', event)" title="Gestión de clases"><i class="bi bi-easel2"></i> Clases</a>
            <a class="nav-link" href="#" onclick="mostrarSeccion('coordinadores', event)" title="Gestión de coordinadores"><i class="bi bi-person-badge"></i> Coordinadores</a>
            <a class="nav-link" href="#" onclick="mostrarSeccion('profesores', event)" title="Gestión de profesores"><i class="bi bi-person-video2"></i> Profesores</a>
            <a class="nav-link" href="#" onclick="mostrarSeccion('reportes', event)" title="Ver reportes y estadísticas"><i class="bi bi-bar-chart"></i> Reportes</a>
            <a class="nav-link" href="#" onclick="mostrarSeccion('aprobaciones', event)" title="Aprobar inscripciones"><i class="bi bi-person-check"></i> Inscripciones</a>
            <a class="nav-link" href="#" onclick="mostrarSeccion('configuracion', event)" title="Configuraciones"><i class="bi bi-gear"></i> Configuración</a>
            <a class="nav-link mt-4" href="../includes/logout.php" title="Cerrar sesión"><i class="bi bi-box-arrow-left"></i> Cerrar sesión</a>
        </nav>
    </div>
    <div class="main-content">
        <div class="topbar">
            <div>
                <span class="fw-semibold fs-5">Bienvenido, <?= htmlspecialchars($nombre) ?></span>
            </div>
            <div class="d-flex align-items-center">
                <span class="badge bg-warning fs-6 me-2"><i class="bi bi-award"></i> Director</span>
                <img src="../img/avatar-director.png" class="avatar-admin" alt="Director" />
            </div>
        </div>

        <!-- INICIO -->
        <div id="inicio" class="pantalla">
            <h2 class="mb-3">Panel principal del Director</h2>
            <div class="row quick-cards mb-5">
                <div class="col-md-4 mb-4">
                    <div class="card text-center border-0" onclick="mostrarSeccion('usuarios', event)">
                        <div class="card-body">
                            <i class="bi bi-people-fill text-primary"></i>
                            <h5 class="card-title mt-2">Usuarios</h5>
                            <p class="card-text">Gestiona usuarios, coordinadores, profesores, alumnos y padres.</p>
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
                <div class="col-md-4 mb-4">
                    <!-- Sección para mostrar solicitudes en la página del director -->
                    <h2>Solicitudes Pendientes</h2>
                    <table border="1">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tipo</th>
                                <th>ID Entidad</th>
                                <th>Solicitante</th>
                                <th>Fecha</th>
                                <th>Opciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaSolicitudes">
                            <!-- Aquí se cargan las solicitudes con JS -->
                        </tbody>
                    </table>
                    <script src="../assets/Js/solicitudes.js"></script>
                    <script>
                        document.addEventListener('DOMContentLoaded', cargarSolicitudes);
                    </script>
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
            <h3 class="mb-3"><i class="bi bi-person-badge"></i> Coordinadores</h3>
            <div class="table-responsive" id="tabla-coordinadores"></div>
        </div>
        <!-- PROFESORES -->
        <div id="profesores" class="pantalla d-none">
            <h3 class="mb-3"><i class="bi bi-person-video2"></i> Profesores</h3>
            <div class="table-responsive" id="tabla-profesores"></div>
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
            <h3 class="mb-3"><i class="bi bi-gear"></i> Configuración</h3>
            <div class="table-responsive">
                <p>Próximamente opciones para personalización avanzada del sistema.</p>
            </div>
        </div>
        <div class="footer-admin">
            &copy; <?=date('Y')?> Mini Mundo Lingua • Panel de Dirección
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/Js/solicitudes.js"></script>
    <script src="../assets/Js/director.js"></script>
</body>
</html>