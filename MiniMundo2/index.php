<?php
include('includes/funciones.php');
$imagenes = [
    "clase gratuita.jpg",
    "clase muestra.jpg",
    "certificaciones maestros.jpg",
    "gibly.jpg",
    "ingles para ti.jpg",
    "inscripciones abiertas.jpg",
    "listening.jpg",
    "publicidad ninos.jpg",
    "teens.jpg",
    "verano2 2025.jpg",
    "clases pesonalizadas.jpg",
    "clases sabatinas.jpg"
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mini Mundo Lingua</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="assets/Css/Styles.css" rel="stylesheet">
    <meta name="description" content="Mini Mundo Lingua: Escuela de inglés divertida, moderna y enfocada en el aprendizaje dinámico para niños y familias.">
</head>
<body>
<!-- Barra de navegación mejorada -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm animated-navbar">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="#">
            <img src="assets/img/logo.png.png" alt="Logo Mini Mundo Lingua" width="48" class="me-2 rounded-circle border border-3 border-warning animate__animated animate__bounceIn">
            <span class="logo-text">Mini Mundo Lingua</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Menú">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link active" href="#">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="#nosotros">Nosotros</a></li>
                <li class="nav-item"><a class="nav-link" href="#oferta">Oferta Educativa</a></li>
                <li class="nav-item"><a class="nav-link" href="#avisos">Avisos</a></li>
                <li class="nav-item"><a class="nav-link" href="#testimonios">Testimonios</a></li>
                <li class="nav-item"><a class="nav-link" href="#contacto">Contacto</a></li>
                <li class="nav-item">
                    <a href="public.php" class="btn btn-outline-light ms-3 nav-btn"><i class="bi bi-person-circle"></i> Ingresar</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Carrusel de imágenes promocionales -->
<div class="carousel-section">
  <div id="mainCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
    <div class="carousel-inner">
      <div class="carousel-item active">
        <img src="assets/img/publicidad ninos.jpg" class="d-block w-100 carousel-img" alt="Promoción niños">
        <div class="carousel-caption d-none d-md-block bg-primary bg-opacity-75 rounded-3 p-3 animate__animated animate__fadeInDown">
          <h2 class="text-white fw-bold">¡Vive el inglés con nosotros!</h2>
          <p>Inscripciones abiertas para todos los niveles. Aprende jugando y disfrutando.</p>
        </div>
      </div>
      <div class="carousel-item">
        <img src="assets/img/inscripciones abiertas.jpg" class="d-block w-100 carousel-img" alt="Inscripciones abiertas">
        <div class="carousel-caption d-none d-md-block bg-warning bg-opacity-75 rounded-3 p-3 animate__animated animate__fadeInDown">
          <h2 class="text-dark fw-bold">¡Reserva tu lugar para verano 2025!</h2>
          <p>Grupos para pequeños, teens y adultos. Cupo limitado.</p>
        </div>
      </div>
      <div class="carousel-item">
        <img src="assets/img/certificaciones maestros.jpg" class="d-block w-100 carousel-img" alt="Certificaciones">
        <div class="carousel-caption d-none d-md-block bg-success bg-opacity-75 rounded-3 p-3 animate__animated animate__fadeInDown">
          <h2 class="text-white fw-bold">Docentes certificados</h2>
          <p>Calidad, profesionalismo y pasión por la enseñanza para tu familia.</p>
        </div>
      </div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#mainCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#mainCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon"></span>
    </button>
  </div>
</div>

<!-- Hero Section -->
<header class="bg-light py-5 text-center hero-section shadow-sm section-animate delay-1">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7 text-lg-start animate__animated animate__fadeInLeft">
                <h1 class="display-4 fw-bold mb-3 text-primary hero-title">¡Aprender inglés nunca fue tan divertido!</h1>
                <p class="lead mb-4 hero-lead">
                    Mini Mundo Lingua es una escuela de inglés innovadora para niños y familias. 
                    Nuestro compromiso es crear experiencias educativas memorables, con actividades dinámicas, tecnología educativa y un ambiente seguro y alegre.
                </p>
                <ul class="list-unstyled mb-4 text-start">
                    <li><i class="bi bi-star-fill text-warning"></i> Clases presenciales y en línea</li>
                    <li><i class="bi bi-award-fill text-primary"></i> Certificaciones internacionales</li>
                    <li><i class="bi bi-people-fill text-success"></i> Grupos pequeños y personalizados</li>
                </ul>
                <a href="#contacto" class="btn btn-warning btn-lg px-4 me-2 animated-btn">Solicitar información</a>
                <a href="public.php" class="btn btn-outline-primary btn-lg px-4 animated-btn">Ingresar</a>
            </div>
            <div class="col-lg-5 d-none d-lg-block animate__animated animate__fadeInRight">
                <img src="assets/img/gibly.jpg" alt="Niños aprendiendo inglés" class="img-fluid rounded shadow-lg hero-image">
            </div>
        </div>
    </div>
</header>

<!-- Sección Nosotros con carrusel cuadrado -->
<section id="nosotros" class="py-5 bg-white border-bottom section-animate delay-1">
    <div class="container">
        <h2 class="text-center mb-4 fw-bold text-primary section-title animated-section-title">¿Quiénes somos?</h2>
        <div class="row align-items-center">
            <div class="col-md-6 mb-4 mb-md-0 animate__animated animate__zoomIn">
                <!-- Carrusel cuadrado de fotos -->
                <div id="carouselNosotros" class="carousel slide carousel-fade shadow" data-bs-ride="carousel" data-bs-interval="2300">
                    <div class="carousel-inner">
                        <?php foreach ($imagenes as $idx => $img): ?>
                        <div class="carousel-item <?= $idx === 0 ? 'active' : '' ?>">
                            <img src="assets/img/<?= htmlspecialchars($img) ?>" alt="" class="img-fluid rounded img-nosotros-carousel mx-auto d-block">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselNosotros" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselNosotros" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                </div>
            </div>
            <div class="col-md-6 animate__animated animate__fadeInUp">
                <p class="fs-5">En <b>Mini Mundo Lingua</b> ofrecemos un ambiente cálido y familiar, con maestros certificados que motivan a aprender jugando. 
                Promovemos la confianza, el trabajo en equipo y la comunicación en inglés desde los primeros niveles. 
                ¡Forma parte de nuestra familia educativa!</p>
            </div>
        </div>
    </div>
</section>

<!-- Oferta Educativa -->
<section id="oferta" class="py-5 bg-light section-animate delay-2">
    <div class="container">
        <h2 class="text-center mb-4 fw-bold text-primary section-title animated-section-title">Oferta Educativa</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow h-100 animated-card oferta-card-clickable">
                    <img src="assets/img/clases pesonalizadas.jpg" alt="Clases personalizadas" class="card-img-top oferta-img">
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">Clases Personalizadas</h5>
                        <p class="card-text">Aprende a tu ritmo y con atención individualizada. Resultados garantizados.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow h-100 animated-card oferta-card-clickable">
                    <img src="assets/img/clases sabatinas.jpg" alt="Clases sabatinas" class="card-img-top oferta-img">
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">Clases Sabatinas</h5>
                        <p class="card-text">Perfectas para quienes buscan avanzar los fines de semana en un ambiente dinámico.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow h-100 animated-card oferta-card-clickable">
                    <img src="assets/img/ingles para ti.jpg" alt="Inglés para ti" class="card-img-top oferta-img">
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">Inglés para todos</h5>
                        <p class="card-text">Cursos para niños, adolescentes y adultos, integrando juegos, música y tecnología.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Sección Avisos -->
<section id="avisos" class="py-5 bg-warning bg-opacity-25 border-bottom">
    <div class="container">
        <h2 class="text-center mb-4 fw-bold text-primary section-title">Avisos Importantes</h2>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if (empty($avisos)): ?>
                    <div class="alert alert-info">No hay avisos por el momento. ¡Vuelve pronto!</div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($avisos as $aviso): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="fw-bold mb-1"><?=htmlspecialchars($aviso['titulo'])?></h5>
                                <div><?=htmlspecialchars($aviso['contenido'])?></div>
                                <?php if (!empty($aviso['fecha'])): ?><small class="text-muted"><?=htmlspecialchars($aviso['fecha'])?></small><?php endif; ?>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Testimonios -->
<section id="testimonios" class="py-5 bg-white border-bottom section-animate delay-3">
    <div class="container">
        <h2 class="text-center mb-4 fw-bold text-primary section-title animated-section-title">Lo que dicen nuestros alumnos y padres</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow h-100 testimonial-card animate__animated animate__fadeInUp">
                    <img src="assets/img/listening.jpg" alt="Testimonio alumno" class="card-img-top testimonial-img">
                    <div class="card-body">
                        <blockquote class="blockquote mb-0">
                            <p>¡Mi hijo aprendió inglés jugando y ahora le encanta ir a clases!</p>
                            <footer class="blockquote-footer mt-2">Mamá de Diego, 7 años</footer>
                        </blockquote>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow h-100 testimonial-card animate__animated animate__fadeInUp">
                    <img src="assets/img/teens.jpg" alt="Testimonio adolescente" class="card-img-top testimonial-img">
                    <div class="card-body">
                        <blockquote class="blockquote mb-0">
                            <p>El seguimiento y la retroalimentación de los maestros es excelente. Recomiendo Mini Mundo Lingua.</p>
                            <footer class="blockquote-footer mt-2">Papá de Mariana, 9 años</footer>
                        </blockquote>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow h-100 testimonial-card animate__animated animate__fadeInUp">
                    <img src="assets/img/clase gratuita.jpg" alt="Testimonio mamá" class="card-img-top testimonial-img">
                    <div class="card-body">
                        <blockquote class="blockquote mb-0">
                            <p>Las clases online son muy dinámicas, mi hija participa y aprende mucho. ¡Gracias equipo!</p>
                            <footer class="blockquote-footer mt-2">Laura, mamá de Sofía</footer>
                        </blockquote>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contacto (oculto por defecto) -->
<section id="contacto" class="py-5 bg-light section-animate delay-1" style="display:none;">
    <div class="container">
        <h2 class="text-center mb-4 fw-bold text-primary section-title animated-section-title">Contáctanos</h2>
        <p class="text-center mb-4">¿Tienes dudas o quieres más información? Completa el formulario y te contactaremos a la brevedad.</p>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php renderContactForm(); ?>
            </div>
        </div>
        <div class="row justify-content-center mt-4">
            <div class="col-md-6 text-center animate__animated animate__fadeInUp">
                <img src="assets/img/verano2 2025.jpg" alt="Verano 2025" class="img-fluid rounded shadow img-contacto">
            </div>
        </div>
    </div>
</section>
<div class="text-center my-4">
    <button id="btnMostrarContacto" class="btn btn-lg btn-warning"><i class="bi bi-envelope"></i> Mostrar formulario de contacto</button>
</div>

<!-- Pie de página -->
<footer class="bg-primary text-white text-center py-4 animated-footer">
    <div class="container">
        <div class="mb-2">
            <a href="https://facebook.com" class="text-white me-3"><i class="bi bi-facebook fs-3"></i></a>
            <a href="https://instagram.com" class="text-white me-3"><i class="bi bi-instagram fs-3"></i></a>
            <a href="https://linkedin.com" class="text-white me-3"><i class="bi bi-linkedin fs-3"></i></a>
            <a href="mailto:info@minimundolingua.com" class="text-white"><i class="bi bi-envelope-fill fs-3"></i></a>
        </div>
        <small>Mini Mundo Lingua &copy; <?= date('Y') ?>. Todos los derechos reservados.</small>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Animaciones CSS extra (animate.css CDN) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<script>
document.getElementById('btnMostrarContacto').onclick = function() {
    var seccion = document.getElementById('contacto');
    seccion.style.display = (seccion.style.display === 'none') ? 'block' : 'none';
    window.scrollTo({top: seccion.offsetTop, behavior: 'smooth'});
    this.style.display = 'none';
};
</script>
</body>
</html>