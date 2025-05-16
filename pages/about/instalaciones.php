<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

$currentPage = 'instalaciones';
$pageTitle = 'Instalaciones';
$pageDescription = 'Conoce las modernas instalaciones del Centro de Estudios Avanzados, diseñadas para ofrecer un ambiente óptimo para el aprendizaje y la investigación.';

// Función para buscar imágenes de instalaciones
function encontrarImagenInstalacion($nombreBase) {
    $extensiones = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $rutaBase = __DIR__ . '/../../assets/img/facilities/' . $nombreBase;
    foreach ($extensiones as $ext) {
        if (file_exists("$rutaBase.$ext")) {
            return SITE_URL . "/assets/img/facilities/$nombreBase.$ext";
        }
    }
    return null;
}

// Definir las imágenes de las instalaciones
$imgCampus = encontrarImagenInstalacion('campus');
$imgBiblioteca = encontrarImagenInstalacion('biblioteca');
$imgAuditorio = encontrarImagenInstalacion('auditorio');
$imgLaboratorio = encontrarImagenInstalacion('laboratorio');
$imgAulas = encontrarImagenInstalacion('aulas');
$imgAreasComunes = encontrarImagenInstalacion('areas-comunes');

include '../../includes/header.php';
?>

<!-- Page Header -->
<!-- <section class="page-header">
    <div class="container">
        <h1>Instalaciones</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Inicio</a></li>
                <li class="breadcrumb-item">Acerca de</li>
                <li class="breadcrumb-item active" aria-current="page">Instalaciones</li>
            </ol>
        </nav>
    </div>
</section> -->

<!-- Page Header -->
<section class="hero" style="padding: var(--space-lg) 0;">
  <div class="container">
    <h1 class="hero-title">Área de Carnes y Embutidos</h1>
    <p class="hero-subtitle">Conoce las instalaciones especializadas para el desarrollo y formación en la industria de carnes y embutidos</p>
  </div>
</section>

<!-- About Tabs Section -->
<section class="section">
    <div class="container">
        <div class="about-tabs">
            <a href="<?php echo SITE_URL; ?>/pages/about/mision-vision.php" class="tab-item">Misión y Visión</a>
            <a href="<?php echo SITE_URL; ?>/pages/about/historia.php" class="tab-item">Historia</a>
            <a href="<?php echo SITE_URL; ?>/pages/about/equipo.php" class="tab-item">Equipo</a>
            <a href="<?php echo SITE_URL; ?>/pages/about/instalaciones.php" class="tab-item active">Instalaciones</a>
        </div>
        
        <div class="about-content">
            <div class="facilities-intro">
              <h2>Centro de Tecnología Cárnica</h2>
              <p>La <strong>Área de Carnes y Embutidos</strong> del Centro de Estudios Avanzados en FESC Campo 4 está equipada con infraestructura especializada para el procesamiento, conservación y análisis de productos cárnicos. Este espacio está destinado a la formación práctica de estudiantes y al desarrollo de investigación aplicada en tecnologías alimentarias.</p>
              <p>Cuenta con cámaras frías, áreas de despiece, laboratorios de análisis y equipos de envasado al vacío, lo que permite simular condiciones industriales en un ambiente académico controlado.</p>
            </div>
            
            <div class="facilities-gallery">
                <!-- Galería Principal -->
                <div class="gallery-main">
                    <?php if ($imgCampus): ?>
                        <img src="<?= $imgCampus ?>" alt="Campus Principal" id="galleryMainImage">
                    <?php else: ?>
                        <img alt="Campus Principal" id="galleryMainImage">
                    <?php endif; ?>
                    <div class="gallery-caption" id="galleryCaption">Campus Principal</div>
                </div>
                    
                <!-- Miniaturas -->
                <div class="gallery-thumbs">
                    <div class="thumb active" data-image="<?= $imgCampus ?>" data-caption="Campus Principal">
                        <?php if ($imgCampus): ?>
                            <img src="<?= $imgCampus ?>" alt="Campus Principal">
                        <?php else: ?>
                            <img alt="Campus Principal">
                        <?php endif; ?>
                    </div>
                        
                    <div class="thumb" data-image="<?= $imgBiblioteca ?>" data-caption="Biblioteca Central">
                        <?php if ($imgBiblioteca): ?>
                            <img src="<?= $imgBiblioteca ?>" alt="Biblioteca Central">
                        <?php else: ?>
                            <p>Imagen no disponible</p>
                        <?php endif; ?>
                    </div>
                        
                    <div class="thumb" data-image="<?= $imgAuditorio ?>" data-caption="Auditorio Principal">
                        <?php if ($imgAuditorio): ?>
                            <img src="<?= $imgAuditorio ?>" alt="Auditorio Principal">
                        <?php else: ?>
                            <p>Imagen no disponible</p>
                        <?php endif; ?>
                    </div>
                        
                    <div class="thumb" data-image="<?= $imgLaboratorio ?>" data-caption="Laboratorios de Investigación">
                        <?php if ($imgLaboratorio): ?>
                            <img src="<?= $imgLaboratorio ?>" alt="Laboratorios de Investigación">
                        <?php else: ?>
                            <p>Imagen no disponible</p>
                        <?php endif; ?>
                    </div>
                        
                    <div class="thumb" data-image="<?= $imgAulas ?>" data-caption="Aulas de Posgrado">
                        <?php if ($imgAulas): ?>
                            <img src="<?= $imgAulas ?>" alt="Aulas de Posgrado">
                        <?php else: ?>
                            <p>Imagen no disponible</p>
                        <?php endif; ?>
                    </div>
                        
                    <div class="thumb" data-image="<?= $imgAreasComunes ?>" data-caption="Áreas Comunes">
                        <?php if ($imgAreasComunes): ?>
                            <img src="<?= $imgAreasComunes ?>" alt="Áreas Comunes">
                        <?php else: ?>
                            <p>Imagen no disponible</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="facilities-grid">
        <div class="facility-card">
          <div class="facility-icon"><i class="fas fa-drumstick-bite"></i></div>
          <h3>Procesamiento</h3>
          <p>Espacios equipados para el despiece, transformación y embutido de productos cárnicos, con enfoque en seguridad alimentaria.</p>
        </div>
        <div class="facility-card">
          <div class="facility-icon"><i class="fas fa-snowflake"></i></div>
          <h3>Conservación</h3>
          <p>Cámaras de refrigeración y congelación que permiten el manejo adecuado de la cadena de frío.</p>
        </div>
        <div class="facility-card">
          <div class="facility-icon"><i class="fas fa-vial"></i></div>
          <h3>Laboratorios</h3>
          <p>Laboratorios para análisis físico-químico y microbiológico de alimentos, asegurando el control de calidad de los productos.</p>
        </div>
        <div class="facility-card">
          <div class="facility-icon"><i class="fas fa-box-open"></i></div>
          <h3>Empaque</h3>
          <p>Zona para empaque al vacío y etiquetado, orientada al aprendizaje de buenas prácticas en presentación comercial.</p>
        </div>
      </div>
            
            <div class="virtual-tour">
        <h2 class="section-title text-center">Recorrido Virtual</h2>
        <div class="tour-container">
          <iframe width="100%" height="500" src="https://www.youtube.com/embed/REEMPLAZAR_CON_VIDEO" title="Recorrido Virtual Área de Carnes" frameborder="0" allowfullscreen></iframe>
        </div>
        <p class="text-center">Descubre cómo se prepara el futuro de la tecnología alimentaria en la FESC Campo 4.</p>
      </div>
            
            <div class="location-section">
        <h2 class="section-title text-center">Ubicación</h2>
        <div class="location-container">
          <div class="location-map">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3765.2129192570397!2d-99.18956492394826!3d19.690973632193377!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x85d1f5d67d469fd5%3A0x3fd44c85e38cd76e!2sFacultad%20de%20Estudios%20Superiores%20Cuautitl%C3%A1n%20Campo%204!5e0!3m2!1ses-419!2smx!4v1682458123456!5m2!1ses-419!2smx" width="100%" height="400" style="border:0;" allowfullscreen loading="lazy"></iframe>
          </div>
          <div class="location-info">
            <h3>Cómo Llegar</h3>
            <div class="location-item">
              <div class="location-icon"><i class="fas fa-map-marker-alt"></i></div>
              <div class="location-text">
                <h4>Dirección</h4>
                <p>FESC Campo 4, Av. 1ro. de Mayo S/N, Sta. María Las Torres, Cuautitlán Izcalli, Edo. de México</p>
              </div>
            </div>
            <div class="location-item">
              <div class="location-icon"><i class="fas fa-bus"></i></div>
              <div class="location-text">
                <h4>Transporte Público</h4>
                <p>RTP, combis locales y transporte universitario desde Campo 1 y 2</p>
              </div>
            </div>
          </div>
        </div>
      </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Galería de imágenes
    const thumbs = document.querySelectorAll('.gallery-thumbs .thumb');
    const mainImage = document.getElementById('galleryMainImage');
    const caption = document.getElementById('galleryCaption');
    
    thumbs.forEach(thumb => {
        thumb.addEventListener('click', function() {
            // Remover clase active de todos los thumbs
            thumbs.forEach(t => t.classList.remove('active'));
            
            // Agregar clase active al thumb clickeado
            this.classList.add('active');
            
            // Actualizar imagen principal y caption
            const imageUrl = this.getAttribute('data-image');
            const imageCaption = this.getAttribute('data-caption');
            
            mainImage.src = imageUrl;
            caption.textContent = imageCaption;
        });
    });
});
</script>

<?php include '../../includes/footer.php'; ?>