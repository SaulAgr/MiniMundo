<?php
session_start();
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] != 6) { // Rol padre = 6
    header('Location: ../public.php?error=Acceso%20no%20autorizado');
    exit;
}
$nombre = $_SESSION['usuario_nombre'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Panel Padre | Mini Mundo Lingua</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/padre.css" rel="stylesheet" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
</head>
<body>
    <div class="container-fluid g-0">
        <div class="row g-0 min-vh-100">
            <!-- Sidebar -->
            <nav class="col-12 col-md-3 col-lg-2 bg-warning text-dark sidebar p-4 d-flex flex-column align-items-center">
                <img src="../img/avatar-padre.png" class="brand-avatar mb-3 shadow" alt="Padre avatar" width="90" height="90" />
                <h3 class="mb-4 fw-bold text-center">Padre <i class="bi bi-person-hearts"></i></h3>
                <ul class="nav nav-pills flex-column w-100 mb-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#" onclick="mostrarSeccion('inicio', event)">
                            <i class="bi bi-house-door"></i> Inicio
                        </a>
                    </li>
                    <li>
                        <a class="nav-link" href="#" onclick="mostrarSeccion('avisos', event)">
                            <i class="bi bi-megaphone"></i> Avisos
                        </a>
                    </li>
                    <li>
                        <a class="nav-link" href="#" onclick="mostrarSeccion('progreso', event)">
                            <i class="bi bi-bar-chart-line"></i> Progreso de hijos
                        </a>
                    </li>
                    <li>
                        <a class="nav-link" href="#" onclick="mostrarSeccion('mensajes', event)">
                            <i class="bi bi-chat-dots"></i> Chat con profesor
                        </a>
                    </li>
                    <li>
                        <a class="nav-link" href="#" onclick="mostrarSeccion('perfil', event)">
                            <i class="bi bi-person-circle"></i> Mi perfil
                        </a>
                    </li>
                    <li class="mt-4 border-top pt-3">
                        <a class="nav-link" href="../includes/logout.php">
                            <i class="bi bi-box-arrow-left"></i> Cerrar sesión
                        </a>
                    </li>
                </ul>
                <div class="mt-auto d-none d-md-block small text-dark text-center">
                    &copy; <?=date('Y')?> Mini Mundo Lingua
                </div>
            </nav>
            <!-- Main content -->
            <main class="col-12 col-md-9 col-lg-10 p-4 main-content bg-light">
                <div class="topbar d-flex justify-content-between align-items-center mb-4">
                    <span class="fw-semibold fs-5">Bienvenido, <?= htmlspecialchars($nombre) ?></span>
                    <span class="badge bg-warning text-dark fs-6 me-2"><i class="bi bi-person-hearts"></i> Padre</span>
                </div>
                <!-- INICIO -->
                <div id="inicio" class="pantalla">
                    <div class="p-4 bg-white rounded shadow-sm mb-4">
                        <h2 class="mb-3">Panel principal del Padre</h2>
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-info-circle"></i> Utiliza el menú lateral para ver avisos, contactar profesores y consultar el progreso de tus hijos.
                        </div>
                    </div>
                </div>
                <!-- AVISOS -->
                <div id="avisos" class="pantalla d-none">
                    <div class="p-4 bg-white rounded shadow-sm mb-4">
                        <h3 class="mb-3"><i class="bi bi-megaphone"></i> Avisos de profesores</h3>
                        <div id="tabla-avisos"></div>
                        <form id="formAvisoPadre" class="mt-4">
                            <h6>Enviar comentario sobre un aviso:</h6>
                            <div class="row g-2 align-items-end">
                                <div class="col">
                                    <select name="aviso_id" class="form-control" required id="input-aviso-id">
                                        <option value="">Selecciona un aviso</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <input type="text" class="form-control" name="mensaje" placeholder="Comentario o duda" required>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-warning" type="submit">Enviar</button>
                                </div>
                            </div>
                        </form>
                        <div id="comentarios-avisos" class="mt-3"></div>
                    </div>
                </div>
                <!-- PROGRESO DE HIJOS -->
                <div id="progreso" class="pantalla d-none">
                    <div class="p-4 bg-white rounded shadow-sm mb-4">
                        <h3 class="mb-3"><i class="bi bi-bar-chart-line"></i> Progreso de hijos</h3>
                        <div id="tabla-progreso"></div>
                    </div>
                </div>
                <!-- MENSAJES/CHAT CON PROFESOR -->
                <div id="mensajes" class="pantalla d-none">
                    <div class="p-4 bg-white rounded shadow-sm mb-4">
                        <h3 class="mb-3"><i class="bi bi-chat-dots"></i> Chat con profesor</h3>
                        <div>
                            <select id="input-profesor-id" class="form-select mb-3">
                                <option value="">Selecciona profesor</option>
                            </select>
                        </div>
                        <div id="tabla-mensajes"></div>
                        <form id="formMensajePadre" class="mt-4">
                            <div class="row g-2 align-items-end">
                                <div class="col">
                                    <input type="text" class="form-control" name="mensaje" placeholder="Escribe tu mensaje" required>
                                    <input type="hidden" name="profesor_id" id="profesor_id_hidden">
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-warning" type="submit">Enviar</button>
                                </div>
                            </div>
                        </form>
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
                    &copy; <?=date('Y')?> Mini Mundo Lingua • Panel del Padre
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/Js/padre.js"></script>
</body>
</html>