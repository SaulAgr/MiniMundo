<?php
include('includes/functions.php'); 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | Mini Mundo Lingua</title>
    <!-- Bootstrap CDN & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/Css/public.css" rel="stylesheet">
</head>
<body>
<?php renderNavbar(); ?>

<div class="container py-5">
    <div class="row justify-content-center align-items-center" style="min-height: 75vh;">
        <div class="col-md-6 col-lg-5">
            <div class="card login-card p-4 shadow-lg animate__animated animate__fadeInDown">
                <div class="text-center mb-3">
                    <img src="assets/img/logo.png.png" alt="Mini Mundo Lingua" class="brand-logo rounded-circle mb-2 shadow-lg">
                    <h2 class="fw-bold text-primary">Iniciar Sesión</h2>
                    <p class="text-muted mb-0">Accede con la cuenta proporcionada por tu coordinador o administración.</p>
                </div>
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger text-center py-2 mb-3"><?= htmlspecialchars($_GET['error']) ?></div>
                <?php endif; ?>
                <?php if (isset($_GET['mensaje'])): ?>
                    <div class="alert alert-success text-center py-2 mb-3"><?= htmlspecialchars($_GET['mensaje']) ?></div>
                <?php endif; ?>
                <form id="loginForm" action="includes/login.php" method="POST" autocomplete="off">
                    <div class="mb-3">
                        <label for="correo" class="form-label"><i class="bi bi-envelope-fill"></i> Correo electrónico</label>
                        <input type="email" id="correo" name="correo" class="form-control form-control-lg" required placeholder="ejemplo@correo.com" autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="contrasena" class="form-label"><i class="bi bi-lock-fill"></i> Contraseña</label>
                        <div class="input-group">
                            <input type="password" id="contrasena" name="contrasena" class="form-control form-control-lg" required placeholder="********">
                            <button class="btn btn-outline-secondary toggle-btn" type="button" id="togglePassword" tabindex="-1" aria-label="Mostrar u ocultar contraseña">
                                <i class="bi bi-eye" id="icon-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="d-grid mb-3">
                        <button type="submit" id="btnIngresar" class="btn btn-primary btn-lg fw-semibold shadow-sm"><i class="bi bi-box-arrow-in-right"></i> Ingresar</button>
                    </div>
                    <div class="text-center">
                        <small class="text-muted">
                            ¿Olvidaste tu contraseña? 
                            <a href="contacto.php" class="text-decoration-underline fw-bold ms-1 link-recuperar">Contacta a tu coordinador</a>
                        </small>
                    </div>
                </form>
            </div>
            <div class="text-center mt-4">
                <p class="text-muted small">Mini Mundo Lingua &copy; <?= date('Y') ?>. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS, animaciones y JS de login -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/Js/public.js"></script>
<script>
// Desactiva el botón "Ingresar" mientras el formulario se envía
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const btn = document.getElementById('btnIngresar');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Ingresando...';
});
</script>
<?php renderFooter(); ?>
</body>
</html