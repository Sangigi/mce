<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$pageTitle = 'Página no encontrada';
$pageDescription = 'La página que estás buscando no existe o ha sido movida.';

include '../includes/header.php';
?>

<!-- Error Section -->
<section class="section error-section">
    <div class="container">
        <div class="error-container">
            <div class="error-image">
                <img src="<?php echo SITE_URL; ?>/assets/img/404.svg" alt="Página no encontrada">
            </div>
            <div class="error-content">
                <h1>404</h1>
                <h2>Página no encontrada</h2>
                <p>Lo sentimos, la página que estás buscando no existe o ha sido movida.</p>
                <div class="error-actions">
                    <a href="<?php echo SITE_URL; ?>" class="btn btn-primary">
                        <i class="fas fa-home"></i> Volver al inicio
                    </a>
                    <a href="<?php echo SITE_URL; ?>/pages/contact.php" class="btn btn-outline">
                        <i class="fas fa-envelope"></i> Contactar soporte
                    </a>
                </div>
            </div>
        </div>
        
        <div class="error-suggestions">
            <h3>Quizás te interese:</h3>
            <div class="suggestions-grid">
                <a href="<?php echo SITE_URL; ?>/pages/courses/index.php" class="suggestion-item">
                    <div class="suggestion-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="suggestion-text">
                        <h4>Cursos</h4>
                        <p>Explora nuestra oferta académica</p>
                    </div>
                </a>
                
                <a href="<?php echo SITE_URL; ?>/pages/announcements/index.php" class="suggestion-item">
                    <div class="suggestion-icon">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <div class="suggestion-text">
                        <h4>Convocatorias</h4>
                        <p>Consulta nuestras convocatorias actuales</p>
                    </div>
                </a>
                
                <a href="<?php echo SITE_URL; ?>/pages/about/mision-vision.php" class="suggestion-item">
                    <div class="suggestion-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="suggestion-text">
                        <h4>Acerca de</h4>
                        <p>Conoce más sobre el CEA</p>
                    </div>
                </a>
                
                <a href="<?php echo SITE_URL; ?>/pages/contact.php" class="suggestion-item">
                    <div class="suggestion-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="suggestion-text">
                        <h4>Contacto</h4>
                        <p>Ponte en contacto con nosotros</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>