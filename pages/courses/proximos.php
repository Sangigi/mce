<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

$currentPage = 'proximos-cursos';
$pageTitle = 'Próximos Cursos';
$pageDescription = 'Descubre los próximos cursos disponibles en el Centro de Estudios Avanzados.';

// Obtener próximos cursos (ordenados por fecha de inicio)
global $db;
$cursos = $db->getRows("SELECT * FROM cursos WHERE fecha_inicio >= CURDATE() ORDER BY fecha_inicio ASC");

include '../../includes/header.php';
?>

<!-- Page Header -->
<!-- <section class="page-header">
    <div class="container">
        <h1>Próximos Cursos</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Inicio</a></li>
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/courses/index.php">Cursos</a></li>
                <li class="breadcrumb-item active" aria-current="page">Próximos Cursos</li>
            </ol>
        </nav>
    </div>
</section> -->

<!-- Page Header -->
<section class="hero" style="padding: var(--space-lg) 0;">
  <div class="container">
    <h1 class="hero-title">Próximos Cursos</h1>
    <p class="hero-subtitle">Principios rectores del Centro de Estudios Avanzados</p>
  </div>
</section>

<!-- Courses Section -->
<section class="section">
    <div class="container">
        <div class="courses-tabs">
            <a href="<?php echo SITE_URL; ?>/pages/courses/index.php" class="tab-item">Todos los Cursos</a>
            <a href="<?php echo SITE_URL; ?>/pages/courses/proximos.php" class="tab-item active">Próximos Cursos</a>
            <a href="<?php echo SITE_URL; ?>/pages/courses/calendario.php" class="tab-item">Calendario</a>
        </div>
        
        <div class="upcoming-courses-intro">
            <div class="intro-text">
                <h2>Próximos Cursos y Talleres</h2>
                <p>Descubre nuestra oferta de cursos y talleres que comenzarán próximamente. Inscríbete con anticipación para asegurar tu lugar y aprovechar nuestras promociones por inscripción temprana.</p>
            </div>
            <div class="intro-cta">
                <a href="#courses-list" class="btn btn-primary">Ver Cursos</a>
            </div>
        </div>
        
        <div id="courses-list" class="upcoming-courses-container">
            <?php if (count($cursos) > 0): ?>
                <div class="timeline-courses">
                    <?php 
                    $currentMonth = '';
                    foreach ($cursos as $curso): 
                        $courseMonth = date('F Y', strtotime($curso['fecha_inicio']));
                        if ($courseMonth != $currentMonth):
                            $currentMonth = $courseMonth;
                            // Traducir mes al español
                            $monthEn = date('F', strtotime($curso['fecha_inicio']));
                            $monthEs = '';
                            switch($monthEn) {
                                case 'January': $monthEs = 'Enero'; break;
                                case 'February': $monthEs = 'Febrero'; break;
                                case 'March': $monthEs = 'Marzo'; break;
                                case 'April': $monthEs = 'Abril'; break;
                                case 'May': $monthEs = 'Mayo'; break;
                                case 'June': $monthEs = 'Junio'; break;
                                case 'July': $monthEs = 'Julio'; break;
                                case 'August': $monthEs = 'Agosto'; break;
                                case 'September': $monthEs = 'Septiembre'; break;
                                case 'October': $monthEs = 'Octubre'; break;
                                case 'November': $monthEs = 'Noviembre'; break;
                                case 'December': $monthEs = 'Diciembre'; break;
                            }
                            $monthDisplay = $monthEs . ' ' . date('Y', strtotime($curso['fecha_inicio']));
                    ?>
                        <div class="timeline-month">
                            <div class="month-marker">
                                <span><?php echo $monthDisplay; ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="timeline-course">
                        <div class="course-date">
                            <div class="date-day"><?php echo date('d', strtotime($curso['fecha_inicio'])); ?></div>
                            <div class="date-month"><?php echo date('M', strtotime($curso['fecha_inicio'])); ?></div>
                        </div>
                        
                        <div class="course-card">
                            <?php if (!empty($curso['imagen'])): ?>
                                <div class="course-image">
                                    <img src="<?php echo SITE_URL; ?>/uploads/cursos/<?php echo $curso['imagen']; ?>" alt="<?php echo $curso['titulo']; ?>">
                                </div>
                            <?php else: ?>
                                <div class="course-image">
                                    <img src="<?php echo SITE_URL; ?>/assets/img/course-default.jpg" alt="<?php echo $curso['titulo']; ?>">
                                </div>
                            <?php endif; ?>
                            
                            <div class="course-content">
                                <div class="course-status <?php echo ($curso['estado'] === 'abierto') ? 'status-open' : (($curso['estado'] === 'completo') ? 'status-full' : 'status-closed'); ?>">
                                    <?php 
                                        if ($curso['estado'] === 'abierto') {
                                            echo 'Inscripciones Abiertas';
                                        } elseif ($curso['estado'] === 'completo') {
                                            echo 'Cupo Completo';
                                        } else {
                                            echo 'Inscripciones Cerradas';
                                        }
                                    ?>
                                </div>
                                
                                <h3 class="course-title"><?php echo $curso['titulo']; ?></h3>
                                
                                <div class="course-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-user"></i> <?php echo $curso['instructor']; ?>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-calendar-alt"></i> <?php echo formatDate($curso['fecha_inicio']); ?> - <?php echo formatDate($curso['fecha_fin']); ?>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-clock"></i> <?php echo $curso['horario']; ?>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-map-marker-alt"></i> <?php echo $curso['lugar']; ?>
                                    </div>
                                </div>
                                
                                <div class="course-excerpt">
                                    <?php echo substr(strip_tags($curso['descripcion']), 0, 150) . '...'; ?>
                                </div>
                                
                                <div class="course-footer">
                                    <div class="course-price">
                                        <?php 
                                            if ($curso['precio'] > 0) {
                                                echo '$' . number_format($curso['precio'], 2) . ' MXN';
                                            } else {
                                                echo 'Gratuito';
                                            }
                                        ?>
                                    </div>
                                    
                                    <div class="course-actions">
                                        <a href="<?php echo SITE_URL; ?>/pages/courses/detalle.php?id=<?php echo $curso['id']; ?>" class="btn btn-outline btn-sm">
                                            <i class="fas fa-info-circle"></i> Detalles
                                        </a>
                                        
                                        <?php if ($curso['estado'] === 'abierto'): ?>
                                            <a href="<?php echo SITE_URL; ?>/pages/courses/inscription.php?id=<?php echo $curso['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-user-plus"></i> Inscribirse
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-courses">
                    <div class="no-courses-icon">
                        <i class="fas fa-calendar-times"></i>
                    </div>
                    <h3>No hay próximos cursos programados</h3>
                    <p>Actualmente no hay cursos programados para fechas futuras. Por favor, revisa más adelante o contacta con nosotros para más información.</p>
                    <a href="<?php echo SITE_URL; ?>/pages/contact.php" class="btn btn-primary">Contactar</a>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="courses-cta">
            <div class="cta-card">
                <div class="cta-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="cta-content">
                    <h3>¿Quieres recibir notificaciones sobre nuevos cursos?</h3>
                    <p>Suscríbete a nuestro boletín para recibir información sobre próximos cursos y eventos.</p>
                    <form class="cta-form" action="<?php echo SITE_URL; ?>/subscribe.php" method="post">
                        <div class="form-group">
                            <input type="email" name="email" class="form-input" placeholder="Tu correo electrónico" required>
                            <button type="submit" class="btn btn-primary">Suscribirse</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    /* Estilos para la sección de próximos cursos */
    .upcoming-courses-intro {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 2rem;
        background: linear-gradient(135deg, var(--cea-blue-lighter) 0%, var(--cea-green-lighter) 100%);
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }
    
    .intro-text {
        flex: 1;
    }
    
    .intro-text h2 {
        color: var(--cea-blue);
        margin-bottom: 1rem;
        font-size: 1.8rem;
    }
    
    .intro-text p {
        color: var(--gray-700);
        max-width: 600px;
    }
    
    .intro-cta {
        margin-left: 2rem;
    }
    
    .timeline-courses {
        position: relative;
        padding-left: 2rem;
        margin-bottom: 3rem;
    }
    
    .timeline-courses::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        width: 4px;
        background: linear-gradient(to bottom, var(--cea-blue) 0%, var(--cea-green) 100%);
        border-radius: 4px;
    }
    
    .timeline-month {
        position: relative;
        margin-bottom: 2rem;
        padding-left: 1.5rem;
    }

    .btn-outline {
        background-color: var(--azul-unam);
    }

    .btn-outline:hover {
        filter: brightness(0.7);
        color: var(--gold-unam);
    }
    
    .month-marker {
        position: absolute;
        left: -3.5rem;
        top: 0;
        width: 3rem;
        height: 3rem;
        background: var(--cea-blue);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        box-shadow: 0 4px 10px rgba(0, 75, 141, 0.3);
        z-index: 1;
    }
    
    .month-marker span {
        font-size: 0.8rem;
        text-align: center;
        line-height: 1.2;
    }
    
    .timeline-course {
        display: flex;
        margin-bottom: 2rem;
        position: relative;
    }
    
    .timeline-course::before {
        content: '';
        position: absolute;
        left: -2.45rem;
        top: 2.5rem;
        width: 1rem;
        height: 1rem;
        background: white;
        border: 3px solid var(--cea-green);
        border-radius: 50%;
        z-index: 1;
    }
    
    .course-date {
        min-width: 5rem;
        text-align: center;
        margin-right: 1.5rem;
        background: var(--cea-blue-lighter);
        border-radius: 8px;
        padding: 0.5rem;
        height: fit-content;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }
    
    .date-day {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--cea-blue);
        line-height: 1;
    }
    
    .date-month {
        font-size: 0.9rem;
        color: var(--gray-600);
        text-transform: uppercase;
    }
    
    .course-card {
        flex: 1;
        display: flex;
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .course-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    .course-image {
        width: 30%;
        min-width: 200px;
        position: relative;
    }
    
    .course-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .course-content {
        flex: 1;
        padding: 1.5rem;
        position: relative;
    }
    
    .course-status {
        position: absolute;
        top: 1rem;
        right: 1rem;
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .status-open {
        background-color: var(--cea-green-lighter);
        color: var(--cea-green);
    }
    
    .status-full {
        background-color: var(--yellow-light);
        color: var(--yellow);
    }
    
    .status-closed {
        background-color: var(--gray-200);
        color: var(--gray-600);
    }
    
    .course-title {
        font-size: 1.4rem;
        margin-bottom: 1rem;
        color: var(--cea-blue);
    }
    
    .course-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    
    .meta-item {
        display: flex;
        align-items: center;
        font-size: 0.9rem;
        color: var(--gray-600);
    }
    
    .meta-item i {
        margin-right: 0.5rem;
        color: var(--cea-blue);
    }
    
    .course-excerpt {
        margin-bottom: 1.5rem;
        color: var(--gray-700);
        line-height: 1.6;
    }
    
    .course-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: auto;
    }
    
    .course-price {
        font-weight: 700;
        font-size: 1.2rem;
        color: var(--cea-green);
    }
    
    .course-actions {
        display: flex;
        gap: 0.5rem;
    }
    
    .no-courses {
        text-align: center;
        padding: 3rem;
        background: var(--gray-100);
        border-radius: 12px;
    }
    
    .no-courses-icon {
        font-size: 3rem;
        color: var(--gray-400);
        margin-bottom: 1rem;
    }
    
    .no-courses h3 {
        color: var(--gray-700);
        margin-bottom: 1rem;
    }
    
    .no-courses p {
        color: var(--gray-600);
        max-width: 500px;
        margin: 0 auto 1.5rem;
    }
    
    .courses-cta {
        margin-top: 3rem;
    }
    
    .cta-card {
        display: flex;
        align-items: center;
        background: linear-gradient(135deg, var(--cea-blue) 0%, var(--cea-blue-dark) 100%);
        padding: 2rem;
        border-radius: 12px;
        color: white;
        box-shadow: 0 10px 20px rgba(0, 75, 141, 0.2);
    }
    
    .cta-icon {
        font-size: 2.5rem;
        margin-right: 2rem;
    }
    
    .cta-content {
        flex: 1;
    }
    
    .cta-content h3 {
        margin-bottom: 0.5rem;
        font-size: 1.4rem;
        color: #fff;
    }
    
    .cta-content p {
        margin-bottom: 1rem;
        opacity: 0.9;
    }
    
    .cta-form .form-group {
        display: flex;
        max-width: 500px;
    }
    
    .cta-form .form-input {
        flex: 1;
        padding: 0.75rem 1rem;
        border: none;
        border-radius: 4px 0 0 4px;
    }
    
    .cta-form .btn {
        border-radius: 0 4px 4px 0;
    }
    
    /* Responsive */
    @media (max-width: 992px) {
        .upcoming-courses-intro {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .intro-cta {
            margin-left: 0;
            margin-top: 1rem;
        }
        
        .course-card {
            flex-direction: column;
        }
        
        .course-image {
            width: 100%;
            height: 200px;
        }
        
        .cta-card {
            flex-direction: column;
            text-align: center;
        }
        
        .cta-icon {
            margin-right: 0;
            margin-bottom: 1rem;
        }
        
        .cta-form .form-group {
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .cta-form .form-input,
        .cta-form .btn {
            border-radius: 4px;
        }
    }
    
    @media (max-width: 768px) {
        .timeline-course {
            flex-direction: column;
        }
        
        .course-date {
            margin-right: 0;
            margin-bottom: 1rem;
            width: 5rem;
        }
        
        .course-meta {
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .course-footer {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .course-actions {
            width: 100%;
            justify-content: space-between;
        }
    }
</style>

<?php include '../../includes/footer.php'; ?>