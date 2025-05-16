<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Obtener ID del curso
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    redirect(SITE_URL . '/pages/courses/index.php');
}

// Obtener datos del curso
global $db;
$curso = $db->getRow("SELECT * FROM cursos WHERE id = :id", ['id' => $id]);

if (!$curso) {
    redirect(SITE_URL . '/pages/courses/index.php');
}

$currentPage = 'curso-detalle';
$pageTitle = $curso['titulo'];
$pageDescription = substr(strip_tags($curso['descripcion']), 0, 160);

include '../../includes/header.php';
?>

<!-- Page Header -->
<!-- <section class="page-header">
    <div class="container">
        <h1><?php echo $curso['titulo']; ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Inicio</a></li>
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/courses/index.php">Cursos</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo $curso['titulo']; ?></li>
            </ol>
        </nav>
    </div>
</section> -->

<!-- Page Header -->
<section class="hero" style="padding: var(--space-lg) 0;">
  <div class="container">
    <h1 class="hero-title"><?php echo $curso['titulo']; ?></h1>
    <p class="hero-subtitle"><?php echo $curso['descripcion']; ?></p>
  </div>
</section>

<!-- Course Detail Section -->
<section class="section">
    <div class="container">
        <div class="course-detail">
            <div class="course-header">
                <?php if (!empty($curso['imagen'])): ?>
                    <div class="course-image">
                        <img src="<?php echo SITE_URL; ?>/uploads/cursos/<?php echo $curso['imagen']; ?>" alt="<?php echo $curso['titulo']; ?>">
                    </div>
                <?php endif; ?>
                
                <div class="course-info">
                    <div class="course-status <?php echo ($curso['estado'] === 'abierto') ? 'status-open' : (($curso['estado'] === 'completo') ? 'status-full' : 'status-closed'); ?>">
                        <i class="fas <?php echo ($curso['estado'] === 'abierto') ? 'fa-check-circle' : (($curso['estado'] === 'completo') ? 'fa-users' : 'fa-times-circle'); ?>"></i>
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
                    
                    <div class="course-meta">
                        <div class="meta-item">
                            <i class="fas fa-user"></i>
                            <span>Instructor:</span> <?php echo $curso['instructor']; ?>
                        </div>
                        
                        <div class="meta-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Fecha:</span> <?php echo formatDate($curso['fecha_inicio']); ?> - <?php echo formatDate($curso['fecha_fin']); ?>
                        </div>
                        
                        <div class="meta-item">
                            <i class="fas fa-clock"></i>
                            <span>Horario:</span> <?php echo $curso['horario']; ?>
                        </div>
                        
                        <div class="meta-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Lugar:</span> <?php echo $curso['lugar']; ?>
                        </div>
                        
                        <div class="meta-item">
                            <i class="fas fa-users"></i>
                            <span>Cupo:</span> <?php echo $curso['cupo']; ?> personas
                        </div>
                        
                        <div class="meta-item">
                            <i class="fas fa-tag"></i>
                            <span>Precio:</span> 
                            <?php 
                                if ($curso['precio'] > 0) {
                                    echo '$' . number_format($curso['precio'], 2) . ' MXN';
                                } else {
                                    echo 'Gratuito';
                                }
                            ?>
                        </div>
                    </div>
                    
                    <?php if ($curso['estado'] === 'abierto'): ?>
                        <div class="course-actions">
                            <a href="<?php echo SITE_URL; ?>/pages/courses/inscripcion.php?id=<?php echo $curso['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-user-plus"></i> Inscribirme
                            </a>
                            
                            <a href="<?php echo SITE_URL; ?>/pages/contact.php" class="btn btn-outline">
                                <i class="fas fa-question-circle"></i> Solicitar Información
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="course-content">
                <h2>Descripción del Curso</h2>
                <div class="content-text">
                    <?php echo $curso['descripcion']; ?>
                </div>
            </div>
            
            <div class="course-share">
                <span>Compartir:</span>
                <div class="share-buttons">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(SITE_URL . '/pages/courses/detalle.php?id=' . $id); ?>" class="share-button facebook" target="_blank">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(SITE_URL . '/pages/courses/detalle.php?id=' . $id); ?>&text=<?php echo urlencode($curso['titulo']); ?>" class="share-button twitter" target="_blank">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://wa.me/?text=<?php echo urlencode($curso['titulo'] . ' - ' . SITE_URL . '/pages/courses/detalle.php?id=' . $id); ?>" class="share-button whatsapp" target="_blank">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <a href="mailto:?subject=<?php echo urlencode($curso['titulo']); ?>&body=<?php echo urlencode('Te comparto este curso: ' . SITE_URL . '/pages/courses/detalle.php?id=' . $id); ?>" class="share-button email">
                        <i class="fas fa-envelope"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="course-navigation">
            <a href="<?php echo SITE_URL; ?>/pages/courses/index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Cursos
            </a>
        </div>
    </div>
</section>

<!-- Related Courses Section -->
<section class="section bg-light">
    <div class="container">
        <h2 class="section-title text-center">Otros Cursos que te pueden interesar</h2>
        
        <div class="courses-grid">
            <?php
            // Obtener cursos relacionados (excluyendo el actual)
            $cursos_relacionados = $db->getRows("SELECT * FROM cursos WHERE id != :id AND estado = 'abierto' ORDER BY fecha_inicio ASC LIMIT 3", ['id' => $id]);
            
            if (count($cursos_relacionados) > 0):
                foreach ($cursos_relacionados as $curso_rel):
            ?>
                <div class="course-card">
                    <?php if (!empty($curso_rel['imagen'])): ?>
                        <div class="course-image">
                            <img src="<?php echo SITE_URL; ?>/uploads/courses/<?php echo $curso_rel['imagen']; ?>" alt="<?php echo $curso_rel['titulo']; ?>">
                        </div>
                    <?php else: ?>
                        <div class="course-image">
                            <img src="<?php echo SITE_URL; ?>/assets/img/course-default.jpg" alt="<?php echo $curso_rel['titulo']; ?>">
                        </div>
                    <?php endif; ?>
                    
                    <div class="course-content">
                        <h3 class="course-title"><?php echo $curso_rel['titulo']; ?></h3>
                        
                        <div class="course-meta">
                            <div class="meta-item">
                                <i class="fas fa-user"></i> <?php echo $curso_rel['instructor']; ?>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-calendar-alt"></i> <?php echo formatDate($curso_rel['fecha_inicio']); ?>
                            </div>
                        </div>
                        
                        <div class="course-excerpt">
                            <?php echo substr(strip_tags($curso_rel['descripcion']), 0, 100) . '...'; ?>
                        </div>
                        
                        <div class="course-footer">
                            <a href="<?php echo SITE_URL; ?>/pages/courses/detalle.php?id=<?php echo $curso_rel['id']; ?>" class="btn btn-primary btn-sm">
                                Ver Detalles
                            </a>
                        </div>
                    </div>
                </div>
            <?php
                endforeach;
            else:
            ?>
                <div class="alert alert-info" style="grid-column: 1 / -1;">
                    No hay cursos relacionados disponibles en este momento.
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
    .btn-outline {
        background-color: var(--azul-unam);
    }

    .btn-outline:hover {
        filter: brightness(0.7);
        color: var(--gold-unam);
    }
</style>

<?php include '../../includes/footer.php'; ?>