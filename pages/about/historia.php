<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

$currentPage = 'historia';
$pageTitle = 'Área de Carnes y Embutidos';
$pageDescription = 'Conoce la historia y desarrollo del Área de Carnes y Embutidos del CEA en FESC4.';

$extensiones = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$imagen = null;

$basePath = __DIR__ . '/../../assets/img/history-main';

foreach ($extensiones as $ext) {
    if (file_exists("$basePath.$ext")) {
        $webPath = SITE_URL . "/assets/img/history-main.$ext";
        $imagen = true;
        break;
    }
}

include '../../includes/header.php';
?>

<section class="hero" style="padding: var(--space-lg) 0;">
  <div class="container">
    <h1 class="hero-title">Área de Carnes y Embutidos</h1>
    <p class="hero-subtitle">Historia y desarrollo FESC4</p>
  </div>
</section>

<section class="section">
    <div class="container">
        <div class="about-tabs">
            <a href="<?php echo SITE_URL; ?>/pages/about/mision-vision.php" class="tab-item">Misión y Visión</a>
            <a href="<?php echo SITE_URL; ?>/pages/about/historia.php" class="tab-item active">Historia</a>
            <a href="<?php echo SITE_URL; ?>/pages/about/equipo.php" class="tab-item">Equipo</a>
            <a href="<?php echo SITE_URL; ?>/pages/about/instalaciones.php" class="tab-item">Instalaciones</a>
        </div>

        <div class="about-content">
            <div class="history-intro">
                <div class="history-image">
                    <?php if ($imagen): ?>
                        <img src="<?= $webPath ?>" alt="Área de Carnes y Embutidos del CEA FESC4">
                    <?php else: ?>
                        <p>Imagen no disponible</p>
                    <?php endif; ?>
                </div>
                <div class="history-text">
                    <h2>Historia del Área</h2>
                    <p>El Área de Carnes y Embutidos del Centro de Estudios Avanzados (CEA) en FESC4 fue establecida con el objetivo de impulsar la formación técnica y científica en el procesamiento de productos cárnicos, brindando a los estudiantes una experiencia integral desde la teoría hasta la práctica profesional.</p>
                    <p>Desde sus inicios, ha contribuido a mejorar la calidad e inocuidad alimentaria mediante investigaciones aplicadas, desarrollo de nuevos productos y capacitación constante a estudiantes y personal académico.</p>
                    <p>Actualmente, el área cuenta con equipos especializados y se encuentra en constante actualización tecnológica para responder a las demandas del sector alimentario nacional.</p>
                </div>
            </div>

            <div class="timeline-section">
    <h2 class="section-title text-center">Línea del Tiempo</h2>
    <div class="timeline">
        <div class="timeline-item">
            <div class="timeline-date">2012</div>
            <div class="timeline-content">
                <h3>Creación del Área</h3>
                <p>Se establece formalmente el área dentro del CEA FESC4 con la visión de formar profesionales especializados en carnes y embutidos.</p>
            </div>
        </div>
        <div class="timeline-item">
            <div class="timeline-date">2013</div>
            <div class="timeline-content">
                <h3>Inicio de cursos y talleres</h3>
                <p>Se imparten los primeros talleres de procesamiento básico de carne, orientados a estudiantes de ingeniería alimentaria.</p>
            </div>
        </div>
        <div class="timeline-item">
            <div class="timeline-date">2015</div>
            <div class="timeline-content">
                <h3>Primer laboratorio</h3>
                <p>Inicia operaciones el primer laboratorio de procesamiento cárnico, con equipos básicos y protocolos de calidad.</p>
            </div>
        </div>
        <div class="timeline-item">
            <div class="timeline-date">2016</div>
            <div class="timeline-content">
                <h3>Primer producto certificado</h3>
                <p>Uno de los embutidos desarrollados por estudiantes obtiene certificación universitaria en calidad e inocuidad.</p>
            </div>
        </div>
        <div class="timeline-item">
            <div class="timeline-date">2018</div>
            <div class="timeline-content">
                <h3>Vinculación con la industria</h3>
                <p>Se firman convenios con empresas del ramo cárnico para realizar prácticas profesionales y proyectos colaborativos.</p>
            </div>
        </div>
        <div class="timeline-item">
            <div class="timeline-date">2019</div>
            <div class="timeline-content">
                <h3>Participación en ferias</h3>
                <p>El área participa en ferias académicas y tecnológicas, presentando productos cárnicos innovadores desarrollados por estudiantes.</p>
            </div>
        </div>
        <div class="timeline-item">
            <div class="timeline-date">2021</div>
            <div class="timeline-content">
                <h3>Modernización</h3>
                <p>Se renueva el equipamiento del área con tecnología de punta, mejorando la capacidad de producción y análisis de calidad.</p>
            </div>
        </div>
        <div class="timeline-item">
            <div class="timeline-date">2023</div>
            <div class="timeline-content">
                <h3>Prácticas sostenibles</h3>
                <p>Se implementan procesos de reducción de residuos y aprovechamiento de subproductos en el área de producción.</p>
            </div>
        </div>
        <div class="timeline-item">
            <div class="timeline-date">Hoy</div>
            <div class="timeline-content">
                <h3>Proyección actual</h3>
                <p>El área se posiciona como referente en formación técnica cárnica, con un enfoque en sostenibilidad, innovación y seguridad alimentaria.</p>
            </div>
        </div>
    </div>
</div>


            <div class="achievements-section">
                <h2 class="section-title text-center">Logros Destacados</h2>
                <div class="achievements-container">
                    <div class="achievement-item">
                        <div class="achievement-icon">
                            <i class="fas fa-industry"></i>
                        </div>
                        <div class="achievement-content">
                            <h3>Producción de calidad</h3>
                            <p>Producción mensual de embutidos con estándares industriales para prácticas académicas.</p>
                        </div>
                    </div>
                    <div class="achievement-item">
                        <div class="achievement-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div class="achievement-content">
                            <h3>Capacitación continua</h3>
                            <p>Más de 300 estudiantes capacitados anualmente en técnicas modernas de procesamiento cárnico.</p>
                        </div>
                    </div>
                    <div class="achievement-item">
                        <div class="achievement-icon">
                            <i class="fas fa-vial"></i>
                        </div>
                        <div class="achievement-content">
                            <h3>Investigación aplicada</h3>
                            <p>Desarrollo de proyectos de investigación en conservación, inocuidad y nuevos productos cárnicos.</p>
                        </div>
                    </div>
                    <div class="achievement-item">
                        <div class="achievement-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <div class="achievement-content">
                            <h3>Alianzas estratégicas</h3>
                            <p>Colaboración activa con universidades, empresas y organismos de control sanitario.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<?php include '../../includes/footer.php'; ?>
