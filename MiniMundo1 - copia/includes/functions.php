<?php
function renderNavbar() {
    echo '
    <nav class="navbar navbar-expand-lg custom-navbar shadow">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">Mini Mundo Lingua</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="menu">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Sobre Nosotros</a></li>
                    <li class="nav-item"><a class="nav-link" href="contacto.php">Contacto</a></li>
                    <li class="nav-item"><a class="nav-link btn btn-outline-light ms-2" href="public.php">Ingresar</a></li>
                </ul>
            </div>
        </div>
    </nav>';
}

function renderFooter() {
    echo '
    <footer class="custom-footer text-center py-3 mt-5">
        <p class="mb-0">&copy; ' . date('Y') . ' Mini Mundo Lingua. Todos los derechos reservados.</p>
    </footer>';
}
function renderContactForm() {
    echo '
    <div class="container mt-5">
        <h2 class="text-center mb-4">Contáctanos</h2>
        <form action="includes/contacto.php" method="POST">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre:</label>
                <input type="text" id="nombre" name="nombre" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="correo" class="form-label">Correo:</label>
                <input type="email" id="correo" name="correo" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="mensaje" class="form-label">Mensaje:</label>
                <textarea id="mensaje" name="mensaje" class="form-control" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Enviar</button>
        </form>
    </div>';
}