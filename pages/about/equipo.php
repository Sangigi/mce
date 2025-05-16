<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

$currentPage = 'equipo';
$pageTitle = 'Nuestro Equipo';
$pageDescription = 'Conoce al equipo de profesionales en el área de Carnes y Embutidos del Centro de Estudios Avanzados, comprometidos con la formación y calidad de la industria alimentaria.';

include '../../includes/header.php';

// Función para buscar imágenes
function encontrarImagenMiembro($nombreBase) {
    $extensiones = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $rutaBase = __DIR__ . '/../../assets/img/team/' . $nombreBase;
    foreach ($extensiones as $ext) {
        if (file_exists("$rutaBase.$ext")) {
            return SITE_URL . "/assets/img/team/$nombreBase.$ext";
        }
    }
    return null;
}

$imgJefe = encontrarImagenMiembro('jefe');
$imgSupervisor = encontrarImagenMiembro('supervisor');
$imgTecnico1 = encontrarImagenMiembro('tecnico1');
$imgTecnico2 = encontrarImagenMiembro('tecnico2');
$imgInstructor = encontrarImagenMiembro('instructor');
$imgAsistente = encontrarImagenMiembro('asistente');
$imgInvestigador1 = encontrarImagenMiembro('investigador1');
$imgInvestigador2 = encontrarImagenMiembro('investigador2');
$imgInvestigador3 = encontrarImagenMiembro('investigador3');
$imgAdmin1 = encontrarImagenMiembro('admin1');
$imgAdmin2 = encontrarImagenMiembro('admin2');
$imgAdmin3 = encontrarImagenMiembro('admin3');
?>

<!-- Page Header -->
<section class="hero" style="padding: var(--space-lg) 0;">
  <div class="container">
    <h1 class="hero-title">Nuestro Equipo de Carnes y Embutidos</h1>
    <p class="hero-subtitle">Profesionales dedicados a la investigación y formación en el área de carnes y embutidos.</p>
  </div>
</section>

<!-- About Tabs Section -->
<section class="section">
    <div class="container">
        <div class="about-tabs">
            <a href="<?php echo SITE_URL; ?>/pages/about/mision-vision.php" class="tab-item">Misión y Visión</a>
            <a href="<?php echo SITE_URL; ?>/pages/about/historia.php" class="tab-item">Historia</a>
            <a href="<?php echo SITE_URL; ?>/pages/about/equipo.php" class="tab-item active">Equipo</a>
            <a href="<?php echo SITE_URL; ?>/pages/about/instalaciones.php" class="tab-item">Instalaciones</a>
        </div>
        
        <div class="about-content">
            <div class="team-intro">
                <h2>Especialistas en Carnes y Embutidos</h2>
                <p>En el Centro de Estudios Avanzados, nuestra área de Carnes y Embutidos está integrada por un equipo multidisciplinario que trabaja en la formación técnica, investigación e innovación dentro de la industria alimentaria. Nos especializamos en ofrecer conocimientos de vanguardia sobre la producción, calidad y seguridad alimentaria, enfocados en la carne y sus derivados.</p>
                <p>El compromiso con la calidad, la seguridad y la ética es fundamental en nuestra labor educativa y de investigación, formando profesionales capaces de enfrentar los desafíos del sector.</p>
            </div>
            
            <div class="team-filters">
                <button class="filter-btn active" data-filter="all">Todos</button>
                <button class="filter-btn" data-filter="directivo">Equipo Directivo</button>
                <button class="filter-btn" data-filter="academico">Académicos</button>
                <button class="filter-btn" data-filter="investigacion">Investigadores</button>
                <button class="filter-btn" data-filter="administrativo">Administrativos</button>
            </div>
            
            <div class="team-grid">
                <!-- Equipo Directivo -->
                <div class="team-member" data-category="directivo">
                    <div class="member-image">
                        <?php if ($imgJefe): ?>
                            <img src="<?= $imgJefe ?>" alt="Ing. Juan Pérez">
                        <?php else: ?>
                            <p>Imagen no disponible</p>
                        <?php endif; ?>
                    </div>
                    <div class="member-info">
                        <h3>Ing. Juan Pérez</h3>
                        <p class="member-position">Jefe de Área</p>
                        <p class="member-description">Ingeniero en Agroindustria con más de 15 años de experiencia en la producción y comercialización de productos cárnicos.</p>
                    </div>
                </div>
                
                <div class="team-member" data-category="directivo">
                    <div class="member-image">
                        <?php if ($imgSupervisor): ?>
                            <img src="<?= $imgSupervisor ?>" alt="Lic. Ana Gómez">
                        <?php else: ?>
                            <p>Imagen no disponible</p>
                        <?php endif; ?>
                    </div>
                    <div class="member-info">
                        <h3>Lic. Ana Gómez</h3>
                        <p class="member-position">Supervisora de Producción</p>
                        <p class="member-description">Licenciada en Tecnología de Alimentos, especializada en procesos de embutidos y control de calidad.</p>
                    </div>
                </div>

                <!-- Académicos -->
                <div class="team-member" data-category="academico">
                    <div class="member-image">
                        <?php if ($imgTecnico1): ?>
                            <img src="<?= $imgTecnico1 ?>" alt="Dr. Roberto Martínez">
                        <?php else: ?>
                            <p>Imagen no disponible</p>
                        <?php endif; ?>
                    </div>
                    <div class="member-info">
                        <h3>Dr. Roberto Martínez</h3>
                        <p class="member-position">Profesor Titular</p>
                        <p class="member-description">Doctor en Ciencias de los Alimentos, experto en la optimización de procesos de curado y embutidos.</p>
                    </div>
                </div>

                <div class="team-member" data-category="academico">
                    <div class="member-image">
                        <?php if ($imgTecnico2): ?>
                            <img src="<?= $imgTecnico2 ?>" alt="Dra. María López">
                        <?php else: ?>
                            <p>Imagen no disponible</p>
                        <?php endif; ?>
                    </div>
                    <div class="member-info">
                        <h3>Dra. María López</h3>
                        <p class="member-position">Profesora Adjunta</p>
                        <p class="member-description">Doctora en Tecnología de los Alimentos con énfasis en seguridad alimentaria en la industria cárnica.</p>
                    </div>
                </div>

                <!-- Investigadores -->
                <div class="team-member" data-category="investigacion">
                    <div class="member-image">
                        <?php if ($imgInvestigador1): ?>
                            <img src="<?= $imgInvestigador1 ?>" alt="Dr. Luis Pérez">
                        <?php else: ?>
                            <p>Imagen no disponible</p>
                        <?php endif; ?>
                    </div>
                    <div class="member-info">
                        <h3>Dr. Luis Pérez</h3>
                        <p class="member-position">Investigador Principal</p>
                        <p class="member-description">Especialista en la microbiología de alimentos, con investigaciones sobre la conservación de embutidos y carnes.</p>
                    </div>
                </div>

                <div class="team-member" data-category="investigacion">
                    <div class="member-image">
                        <?php if ($imgInvestigador2): ?>
                            <img src="<?= $imgInvestigador2 ?>" alt="Dra. Elena Rodríguez">
                        <?php else: ?>
                            <p>Imagen no disponible</p>
                        <?php endif; ?>
                    </div>
                    <div class="member-info">
                        <h3>Dra. Elena Rodríguez</h3>
                        <p class="member-position">Investigadora Asociada</p>
                        <p class="member-description">Doctora en Biotecnología con enfoque en la optimización de procesos biológicos para el tratamiento de productos cárnicos.</p>
                    </div>
                </div>

                <div class="team-member" data-category="investigacion">
                    <div class="member-image">
                        <?php if ($imgInvestigador3): ?>
                            <img src="<?= $imgInvestigador3 ?>" alt="Dr. José López">
                        <?php else: ?>
                            <p>Imagen no disponible</p>
                        <?php endif; ?>
                    </div>
                    <div class="member-info">
                        <h3>Dr. José López</h3>
                        <p class="member-position">Investigador Asociado</p>
                        <p class="member-description">Experto en el área de aditivos alimentarios y su aplicación en la industria de carnes y embutidos.</p>
                    </div>
                </div>

                <!-- Administrativos -->
                <div class="team-member" data-category="administrativo">
                    <div class="member-image">
                        <?php if ($imgAdmin1): ?>
                            <img src="<?= $imgAdmin1 ?>" alt="Lic. Teresa Martínez">
                        <?php else: ?>
                            <p>Imagen no disponible</p>
                        <?php endif; ?>
                    </div>
                    <div class="member-info">
                        <h3>Lic. Teresa Martínez</h3>
                        <p class="member-position">Administrativa</p>
                        <p class="member-description">Licenciada en Administración, encargada de coordinar las actividades administrativas dentro del área de producción.</p>
                    </div>
                </div>

                <div class="team-member" data-category="administrativo">
                    <div class="member-image">
                        <?php if ($imgAdmin2): ?>
                            <img src="<?= $imgAdmin2 ?>" alt="Lic. Sandra Ramírez">
                        <?php else: ?>
                            <p>Imagen no disponible</p>
                        <?php endif; ?>
                    </div>
                    <div class="member-info">
                        <h3>Lic. Sandra Ramírez</h3>
                        <p class="member-position">Asistente Administrativa</p>
                        <p class="member-description">Encargada del soporte administrativo en el área de gestión de proyectos de investigación.</p>
                    </div>
                </div>

                <div class="team-member" data-category="administrativo">
                    <div class="member-image">
                        <?php if ($imgAdmin3): ?>
                            <img src="<?= $imgAdmin3 ?>" alt="Lic. Fernando Álvarez">
                        <?php else: ?>
                            <p>Imagen no disponible</p>
                        <?php endif; ?>
                    </div>
                    <div class="member-info">
                        <h3>Lic. Fernando Álvarez</h3>
                        <p class="member-position">Coordinador Administrativo</p>
                        <p class="member-description">Licenciado en Contaduría, responsable de la gestión de recursos y presupuestos para proyectos de investigación.</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filtrado de equipo
    const filterButtons = document.querySelectorAll('.filter-btn');
    const teamMembers = document.querySelectorAll('.team-member');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remover clase active de todos los botones
            filterButtons.forEach(btn => btn.classList.remove('active'));
            
            // Agregar clase active al botón clickeado
            this.classList.add('active');
            
            // Obtener categoría a filtrar
            const filterValue = this.getAttribute('data-filter');
            
            // Mostrar u ocultar miembros del equipo según la categoría
            teamMembers.forEach(member => {
                if (filterValue === 'all' || member.getAttribute('data-category') === filterValue) {
                    member.style.display = 'flex';
                } else {
                    member.style.display = 'none';
                }
            });
        });
    });
});
</script>

<?php include '../../includes/footer.php'; ?>
