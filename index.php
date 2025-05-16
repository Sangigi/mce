<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$currentPage = 'home';
$pageTitle = 'Inicio';
$pageDescription = 'Centro de Estudios Avanzados - Institución dedicada a la investigación y formación de alto nivel';

// Obtener convocatorias activas
$convocatorias = getAnnouncements();

// Obtener cursos próximos
$cursos = getCourses();

// Obtener eventos mas recientes

$eventos = getEvents();

include 'includes/header.php';
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <meta name="description" content="<?php echo isset($pageDescription) ? $pageDescription : 'Centro de Estudios Avanzados'; ?>">
    <link rel="stylesheet" href="/cea-website/assets/css/variables.css">
    <link rel="stylesheet" href="/cea-website/assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php if (isset($extraCSS)) echo $extraCSS; ?>
</head>

<!-- Hero Section -->
<section class="hero">
    <div class="container hero-container">
        <div class="hero-content">
            <h1 class="hero-title">Área de Carnes y Embutidos</h1>
            <p class="hero-subtitle">Calidad e innovación alimentaria</p>
            <p class="hero-text">La FESC Campo 4, a través del Área de Carnes y Embutidos, se dedica a la formación académica, investigación y desarrollo de productos cárnicos de alta calidad, comprometidos con la seguridad alimentaria y la mejora continua.</p>
            <div class="hero-buttons">
                <a href="<?php echo SITE_URL; ?>/pages/courses/index.php" class="btn btn-primary">Ver Cursos</a>
                <a href="<?php echo SITE_URL; ?>/pages/about/mision-vision.php" class="btn btn-outline">Conocer más</a>
            </div>
        </div>
        <div class="hero-image">
            <img src="<?php echo SITE_URL; ?>/assets/img/Módulo CyE.png" alt="Área de Carnes y Embutidos">
            <div class="hero-badge">Calidad Alimentaria <i class="fa-solid fa-tags"></i></div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features">
    <div class="container">
        <h2 class="section-title text-center">¿Por qué elegirnos?</h2>
        <p class="section-subtitle text-center">Comprometidos con la excelencia en la industria cárnica</p>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-drumstick-bite"></i>
                </div>
                <h3 class="feature-title">Calidad e Higiene</h3>
                <p class="feature-text">Contamos con laboratorios y procesos que garantizan productos cárnicos seguros y de calidad.</p>
                <a href="<?php echo SITE_URL; ?>/pages/about/equipo.php" class="feature-link">Conoce nuestras instalaciones <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-flask"></i>
                </div>
                <h3 class="feature-title">Investigación Aplicada</h3>
                <p class="feature-text">Realizamos proyectos de innovación tecnológica en carnes y embutidos.</p>
                <a href="#" class="feature-link">Ver proyectos <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="feature-title">Capacitación Continua</h3>
                <p class="feature-text">Ofrecemos cursos y talleres especializados para estudiantes y profesionales del sector alimentario.</p>
                <a href="#" class="feature-link">Únete a nuestros cursos <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-seedling"></i>
                </div>
                <h3 class="feature-title">Sostenibilidad</h3>
                <p class="feature-text">Promovemos prácticas sostenibles y responsables en la producción cárnica.</p>
                <a href="#" class="feature-link">Conoce nuestras prácticas <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>

<!-- Courses Section -->
<section class="section" style="background-color: var(--gray-100);">
    <div class="container">
        <h2 class="section-title text-center">Cursos Destacados</h2>
        <p class="section-subtitle text-center">Explora nuestra oferta académica</p>
        
        <div class="projects-grid">
            <?php if (count($cursos) > 0): ?>
                <?php foreach (array_slice($cursos, 0, 3) as $curso): ?>
                    <div class="project-card">
                        <div class="project-image">
                            <?php if (!empty($curso['imagen'])): ?>
                                <img src="<?php echo SITE_URL; ?>/uploads/cursos/<?php echo $curso['imagen']; ?>" alt="<?php echo $curso['titulo']; ?>">
                            <?php else: ?>
                                <img src="<?php echo SITE_URL; ?>/assets/img/course-default.jpg" alt="<?php echo $curso['titulo']; ?>">
                            <?php endif; ?>
                        </div>
                        <div class="project-content">
                            <span class="project-category">Curso</span>
                            <h3 class="project-title"><?php echo $curso['titulo']; ?></h3>
                            <div class="project-info">
                                <p><strong>Instructor:</strong> <?php echo $curso['instructor']; ?></p>
                                <p><strong>Fecha:</strong> <?php echo formatDate($curso['fecha_inicio']); ?></p>
                            </div>
                            <p class="project-description"><?php echo substr($curso['descripcion'], 0, 100) . '...'; ?></p>
                            <div class="project-footer">
                                <span class="project-status status-active">Abierto</span>
                                <a href="<?php echo SITE_URL; ?>/pages/courses/detalle.php?id=<?php echo $curso['id']; ?>" class="project-link">Ver detalles <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info" style="grid-column: 1 / -1;">
                    No hay cursos disponibles en este momento.
                </div>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-3">
            <a href="<?php echo SITE_URL; ?>/pages/courses/index.php" class="btn btn-primary">Ver todos los cursos</a>
        </div>
    </div>
</section>

<!-- Announcements Section -->
<section class="section">
    <div class="container">
        <h2 class="section-title text-center">Convocatorias Actuales</h2>
        <p class="section-subtitle text-center">Mantente informado sobre nuestras convocatorias</p>
        
        <div class="announcements-container">
            <div class="announcements-list">
                <?php if (count($convocatorias) > 0): ?>
                    <?php foreach (array_slice($convocatorias, 0, 2) as $convocatoria): ?>
                        <div class="announcement-card">
                            <div class="announcement-date">
                                <div class="date-day"><?php echo date('d', strtotime($convocatoria['fecha_inicio'])); ?></div>
                                <div class="date-month"><?php echo date('M', strtotime($convocatoria['fecha_inicio'])); ?></div>
                            </div>
                            <div class="announcement-content">
                                <span class="announcement-type">Convocatoria</span>
                                <h3 class="announcement-title"><?php echo $convocatoria['titulo']; ?></h3>
                                <p class="announcement-text"><?php echo substr($convocatoria['descripcion'], 0, 150) . '...'; ?></p>
                                <a href="<?php echo SITE_URL; ?>/pages/announcements/detalle.php?id=<?php echo $convocatoria['id']; ?>" class="announcement-link">Leer más <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        No hay convocatorias activas en este momento.
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Events Section -->
            <section class="section">
                <div class="container">
                    <h2 class="section-title text-center">Próximos Eventos</h2>
                    <div class="events-calendar">
                        <div class="calendar-header">
                            <h3>Próximos Eventos</h3>
                        </div>
                        <div class="calendar-body">
                            <?php 
                            // Obtener los eventos desde la base de datos
                            $eventos = getEvents();

                            if (count($eventos) > 0):
                                foreach ($eventos as $evento): ?>
                                    <div class="calendar-event">
                                        <div class="event-date">
                                            <i class="fas fa-calendar"></i> <?php echo date('d \d\e M, Y', strtotime($evento['fecha_inicio'])); ?>
                                        </div>
                                        <h4 class="event-title"><?php echo $evento['titulo']; ?></h4>
                                        <div class="event-location">
                                            <i class="fas fa-map-marker-alt"></i> <?php echo $evento['ubicacion'] ?: 'Ubicación no especificada'; ?>
                                        </div>
                                    </div>
                                <?php endforeach; 
                            else: ?>
                                <div class="alert alert-info">
                                    No hay eventos programados por el momento.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="section">
    <div class="container">
        <h2 class="section-title text-center">Contáctanos</h2>
        <p class="section-subtitle text-center">Estamos para ayudarte</p>

        <div class="contact-container">
            <div>
                <div class="contact-info">
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-text">
                            <strong>Dirección</strong>
                            FESC Campo 4, Carretera Cuautitlán - Teoloyucan Km. 2.5, San Sebastián Xhala, Cuautitlán Izcalli, Estado de México
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="contact-text">
                            <strong>Teléfono</strong>
                            +52 (55) 5623-2000 Ext. 45678
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-text">
                            <strong>Email</strong>
                            carnes-embutidos@fesc.unam.mx
                        </div>
                    </div>

                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="contact-text">
                            <strong>Horario de Atención</strong>
                            Lunes a Viernes: 9:00 AM - 4:00 PM
                        </div>
                    </div>
                </div>

                <div class="contact-map">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3765.2129192570397!2d-99.18956492394826!3d19.690973632193377!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x85d1f5d67d469fd5%3A0x3fd44c85e38cd76e!2sFacultad%20de%20Estudios%20Superiores%20Cuautitl%C3%A1n%20Campo%204!5e0!3m2!1ses-419!2smx!4v1682458123456!5m2!1ses-419!2smx" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>

            <div class="contact-form">
                <form action="<?php echo SITE_URL; ?>/pages/send-contact.php" method="post">
                    <div class="form-group">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" id="nombre" name="nombre" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="asunto" class="form-label">Asunto</label>
                        <input type="text" id="asunto" name="asunto" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="mensaje" class="form-label">Mensaje</label>
                        <textarea id="mensaje" name="mensaje" class="form-textarea" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">Enviar Mensaje</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>