<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

$currentPage = 'cursos';
$pageTitle = 'Cursos';
$pageDescription = 'Explora nuestra oferta de cursos en el Centro de Estudios Avanzados.';

// Obtener todos los cursos
global $db;
$cursos = $db->getRows("SELECT * FROM cursos ORDER BY fecha_inicio DESC");

include '../../includes/header.php';
?>

<!-- Page Header -->
<!-- <section class="page-header">
    <div class="container">
        <h1>Cursos</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Inicio</a></li>
                <li class="breadcrumb-item active" aria-current="page">Cursos</li>
            </ol>
        </nav>
    </div>
</section> -->

<!-- Page Header -->
<section class="hero" style="padding: var(--space-lg) 0;">
  <div class="container">
    <h1 class="hero-title">Cursos</h1>
    <p class="hero-subtitle">Principios rectores del Centro de Estudios Avanzados</p>
  </div>
</section>

<!-- Courses Section -->
<section class="section">
    <div class="container">
        <div class="courses-tabs">
            <a href="<?php echo SITE_URL; ?>/pages/courses/index.php" class="tab-item active">Todos los Cursos</a>
            <a href="<?php echo SITE_URL; ?>/pages/courses/proximos.php" class="tab-item">Próximos Cursos</a>
            <a href="<?php echo SITE_URL; ?>/pages/courses/calendario.php" class="tab-item">Calendario</a>
        </div>
        
        <div class="courses-filters">
            <div class="filter-group">
                <label for="filter-status">Estado:</label>
                <select id="filter-status" class="filter-select">
                    <option value="all">Todos</option>
                    <option value="abierto">Inscripciones Abiertas</option>
                    <option value="completo">Cupo Completo</option>
                    <option value="cerrado">Inscripciones Cerradas</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="filter-date">Ordenar por:</label>
                <select id="filter-date" class="filter-select">
                    <option value="newest">Más Recientes</option>
                    <option value="oldest">Más Antiguos</option>
                    <option value="start-date">Fecha de Inicio</option>
                </select>
            </div>
        </div>
        
        <div class="courses-grid" id="courses-container">
            <?php if (count($cursos) > 0): ?>
                <?php foreach ($cursos as $curso): ?>
                    <div class="course-card" data-status="<?php echo $curso['estado']; ?>">
                        <?php if (!empty($curso['imagen'])): ?>
                            <div class="course-image">
                                <img src="<?php echo SITE_URL; ?>/uploads/cursos/<?php echo $curso['imagen']; ?>" alt="<?php echo $curso['titulo']; ?>">
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
                            </div>
                        <?php else: ?>
                            <div class="course-image">
                                <img src="<?php echo SITE_URL; ?>/assets/img/course-default.jpg" alt="<?php echo $curso['titulo']; ?>">
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
                            </div>
                        <?php endif; ?>
                        
                        <div class="course-content">
                            <h3 class="course-title"><?php echo $curso['titulo']; ?></h3>
                            
                            <div class="course-meta">
                                <div class="meta-item">
                                    <i class="fas fa-user"></i> <?php echo $curso['instructor']; ?>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-calendar-alt"></i> <?php echo formatDate($curso['fecha_inicio']); ?>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-clock"></i> <?php echo $curso['horario']; ?>
                                </div>
                            </div>
                            
                            <div class="course-excerpt">
                                <?php echo substr(strip_tags($curso['descripcion']), 0, 100) . '...'; ?>
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
                                
                                <a href="<?php echo SITE_URL; ?>/pages/courses/detalle.php?id=<?php echo $curso['id']; ?>" class="btn btn-primary btn-sm">
                                    Ver Detalles
                                </a>
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
    </div>
</section>

<!-- Course Filter Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterStatus = document.getElementById('filter-status');
    const filterDate = document.getElementById('filter-date');
    const coursesContainer = document.getElementById('courses-container');
    const courseCards = document.querySelectorAll('.course-card');
    
    // Filter by status
    filterStatus.addEventListener('change', function() {
        const status = this.value;
        
        courseCards.forEach(card => {
            if (status === 'all' || card.dataset.status === status) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    });
    
    // Sort by date
    filterDate.addEventListener('change', function() {
        const sortBy = this.value;
        const cardsArray = Array.from(courseCards);
        
        cardsArray.sort((a, b) => {
            const dateA = new Date(a.querySelector('.meta-item:nth-child(2)').textContent.replace('Fecha: ', ''));
            const dateB = new Date(b.querySelector('.meta-item:nth-child(2)').textContent.replace('Fecha: ', ''));
            
            if (sortBy === 'newest') {
                return dateB - dateA;
            } else if (sortBy === 'oldest') {
                return dateA - dateB;
            } else if (sortBy === 'start-date') {
                return dateA - dateB;
            }
        });
        
        // Remove all cards
        courseCards.forEach(card => {
            card.remove();
        });
        
        // Append sorted cards
        cardsArray.forEach(card => {
            coursesContainer.appendChild(card);
        });
    });
});
</script>

<?php include '../../includes/footer.php'; ?>