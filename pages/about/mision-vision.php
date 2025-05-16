<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

$currentPage = 'mision-vision';
$pageTitle = 'Misión y Visión';
$pageDescription = 'Conoce la misión y visión del Centro de Estudios Avanzados de la UNAM FESC Campo 4, así como sus valores y objetivos institucionales.';

include '../../includes/header.php';
?>

<!-- Page Header -->
<section class="hero" style="padding: var(--space-lg) 0;">
  <div class="container">
    <h1 class="hero-title">Misión y Visión</h1>
    <p class="hero-subtitle">Principios rectores del Área de Carnes y Embutidos</p>
  </div>
</section>

<!-- Mission and Vision Section -->
<section class="section">
  <div class="container">
    <div class="about-tabs">
        <a href="<?php echo SITE_URL; ?>/pages/about/mision-vision.php" class="tab-item active">Misión y Visión</a>
        <a href="<?php echo SITE_URL; ?>/pages/about/historia.php" class="tab-item">Historia</a>
        <a href="<?php echo SITE_URL; ?>/pages/about/equipo.php" class="tab-item">Equipo</a>
        <a href="<?php echo SITE_URL; ?>/pages/about/instalaciones.php" class="tab-item">Instalaciones</a>
    </div>
    <div class="about-container" style="grid-template-columns: 1fr;">
      <div class="about-content">
        <div style="background-color: var(--azul-unam-lighter); padding: var(--space-lg); border-radius: var(--radius-lg); margin-bottom: var(--space-lg); position: relative; overflow: hidden;">
          <div style="position: absolute; top: 0; right: 0; width: 150px; height: 150px; background-color: var(--azul-unam); clip-path: polygon(100% 0, 0 0, 100% 100%); opacity: 0.1;"></div>
          <div style="position: absolute; bottom: 0; left: 0; width: 150px; height: 150px; background-color: var(--gold-unam); clip-path: polygon(0 100%, 0 0, 100% 100%); opacity: 0.1;"></div>
          
          <h2 class="section-title" style="color: var(--azul-unam); display: flex; align-items: center; gap: var(--space-sm);">
            <i class="fas fa-bullseye" style="color: var(--gold-unam);"></i> Misión
          </h2>
          <p style="font-size: 1.1rem; line-height: 1.8; margin-bottom: var(--space-md);">
            El Área de Carnes y Embutidos del CEA tiene como misión fomentar la investigación aplicada, la innovación tecnológica y la formación académica especializada en la transformación, conservación y calidad de productos cárnicos. Se busca contribuir al desarrollo de procesos sustentables que atiendan las necesidades del sector agroalimentario nacional.
          </p>
          <p style="font-size: 1.1rem; line-height: 1.8;">
            Promovemos la transferencia de conocimiento y tecnología mediante proyectos interdisciplinarios y colaborativos que impacten positivamente en la industria alimentaria y en la formación de profesionales con enfoque científico y ético.
          </p>
        </div>
        
        <div style="background-color: var(--gold-unam-lighter); padding: var(--space-lg); border-radius: var(--radius-lg); margin-bottom: var(--space-lg); position: relative; overflow: hidden;">
          <div style="position: absolute; top: 0; right: 0; width: 150px; height: 150px; background-color: var(--gold-unam); clip-path: polygon(100% 0, 0 0, 100% 100%); opacity: 0.1;"></div>
          <div style="position: absolute; bottom: 0; left: 0; width: 150px; height: 150px; background-color: var(--azul-unam); clip-path: polygon(0 100%, 0 0, 100% 100%); opacity: 0.1;"></div>
          
          <h2 class="section-title" style="color: var(--azul-unam); display: flex; align-items: center; gap: var(--space-sm);">
            <i class="fas fa-eye" style="color: var(--gold-unam);"></i> Visión
          </h2>
          <p style="font-size: 1.1rem; line-height: 1.8; margin-bottom: var(--space-md);">
            Ser un referente nacional en investigación, desarrollo y capacitación en el ámbito de carnes y embutidos, reconocido por la excelencia de sus procesos, la innovación en sus productos y su vinculación efectiva con la industria, el gobierno y la academia.
          </p>
          <p style="font-size: 1.1rem; line-height: 1.8;">
            Nos proyectamos como un espacio que impulse soluciones sostenibles, seguras y de alta calidad para la cadena de valor cárnica, incorporando buenas prácticas de manufactura, trazabilidad y tecnologías emergentes.
          </p>
        </div>
        
        <div style="background-color: var(--white); padding: var(--space-lg); border-radius: var(--radius-lg); box-shadow: var(--shadow-md);">
          <h2 class="section-title" style="color: var(--azul-unam); display: flex; align-items: center; gap: var(--space-sm);">
            <i class="fas fa-handshake" style="color: var(--gold-unam);"></i> Valores
          </h2>
          
          <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: var(--space-md); margin-top: var(--space-md);">
            <div style="background-color: var(--azul-unam-lighter); padding: var(--space-md); border-radius: var(--radius-md); border-left: 4px solid var(--azul-unam);">
              <h3 style="color: var(--azul-unam); font-size: 1.2rem; margin-bottom: var(--space-xs);">Calidad</h3>
              <p>Enfoque constante en mejorar la seguridad, higiene y propiedades sensoriales de los productos cárnicos desarrollados.</p>
            </div>
            
            <div style="background-color: var(--gold-unam-lighter); padding: var(--space-md); border-radius: var(--radius-md); border-left: 4px solid var(--gold-unam);">
              <h3 style="color: var(--azul-unam); font-size: 1.2rem; margin-bottom: var(--space-xs);">Ética Profesional</h3>
              <p>Conducta responsable y honesta en todas las etapas de investigación, docencia y desarrollo tecnológico.</p>
            </div>
            
            <div style="background-color: var(--teal-light); padding: var(--space-md); border-radius: var(--radius-md); border-left: 4px solid var(--teal);">
              <h3 style="color: var(--azul-unam); font-size: 1.2rem; margin-bottom: var(--space-xs);">Sustentabilidad</h3>
              <p>Compromiso con prácticas que reduzcan el impacto ambiental del procesamiento cárnico.</p>
            </div>
            
            <div style="background-color: var(--purple-light); padding: var(--space-md); border-radius: var(--radius-md); border-left: 4px solid var(--purple);">
              <h3 style="color: var(--azul-unam); font-size: 1.2rem; margin-bottom: var(--space-xs);">Capacitación Continua</h3>
              <p>Promoción del aprendizaje y actualización constante del personal técnico y académico.</p>
            </div>
            
            <div style="background-color: var(--green-light); padding: var(--space-md); border-radius: var(--radius-md); border-left: 4px solid var(--green);">
              <h3 style="color: var(--azul-unam); font-size: 1.2rem; margin-bottom: var(--space-xs);">Vinculación</h3>
              <p>Interacción proactiva con el sector productivo para atender necesidades reales del mercado.</p>
            </div>
            
            <div style="background-color: var(--red-light); padding: var(--space-md); border-radius: var(--radius-md); border-left: 4px solid var(--red);">
              <h3 style="color: var(--azul-unam); font-size: 1.2rem; margin-bottom: var(--space-xs);">Innovación</h3>
              <p>Aplicación de tecnologías emergentes para mejorar productos cárnicos con valor agregado.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
  // Toggle mobile menu
  document.getElementById('mobile-menu-toggle').addEventListener('click', function() {
    document.getElementById('main-nav').classList.toggle('active');
  });
  
  // Toggle dropdown on mobile
  if (window.innerWidth <= 768) {
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(dropdown => {
      const toggle = dropdown.querySelector('.dropdown-toggle');
      toggle.addEventListener('click', function(e) {
        e.preventDefault();
        dropdown.classList.toggle('active');
      });
    });
  }
</script>

<?php include '../../includes/footer.php'; ?>
