<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

$currentPage = 'convocatorias-pasadas';
$pageTitle = 'Convocatorias Pasadas';
$pageDescription = 'Archivo histórico de convocatorias del Centro de Estudios Avanzados. Consulta las convocatorias anteriores.';

// Obtener convocatorias pasadas
$convocatorias = getAnnouncementsByType('inactiva');


include '../../includes/header.php';
?>

<!-- Page Header -->
<!-- <section class="page-header">
    <div class="container">
        <h1>Convocatorias Pasadas</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Inicio</a></li>
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/announcements/index.php">Convocatorias</a></li>
                <li class="breadcrumb-item active" aria-current="page">Convocatorias Pasadas</li>
            </ol>
        </nav>
    </div>
</section> -->

<!-- Page Header -->
<section class="hero" style="padding: var(--space-lg) 0;">
  <div class="container">
    <h1 class="hero-title">Convocatorias Pasadas</h1>
    <p class="hero-subtitle">Principios rectores del Centro de Estudios Avanzados</p>
  </div>
</section>

<!-- Announcements Section -->
<section class="section">
    <div class="container">
        <div class="announcements-tabs">
            <a href="<?php echo SITE_URL; ?>/pages/announcements/index.php" class="tab-item">Convocatorias Actuales</a>
            <a href="<?php echo SITE_URL; ?>/pages/announcements/pasadas.php" class="tab-item active">Convocatorias Pasadas</a>
        </div>
        
        <div class="announcements-intro">
            <h2>Archivo de Convocatorias</h2>
            <p>En esta sección encontrarás un archivo histórico de las convocatorias que han concluido. Aunque estas convocatorias ya no están vigentes, pueden servir como referencia para futuras oportunidades.</p>
        </div>
        
        <div class="announcements-filters">
            <div class="filter-group">
                <label for="filter-type">Tipo:</label>
                <select id="filter-type" class="filter-select">
                    <option value="all">Todos</option>
                    <option value="beca">Becas</option>
                    <option value="programa">Programas Académicos</option>
                    <option value="investigacion">Proyectos de Investigación</option>
                    <option value="evento">Eventos</option>
                    <option value="otro">Otros</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="filter-year">Año:</label>
                <select id="filter-year" class="filter-select">
                    <option value="all">Todos</option>
                    <option value="2023">2023</option>
                    <option value="2022">2022</option>
                    <option value="2021">2021</option>
                    <option value="2020">2020</option>
                </select>
            </div>
        </div>
        
        <div class="announcements-list" id="announcements-container">
            <?php if (count($convocatorias) > 0): ?>
                <?php foreach ($convocatorias as $convocatoria): ?>
                    <div class="announcement-item" data-type="<?php echo $convocatoria['tipo']; ?>" data-year="<?php echo date('Y', strtotime($convocatoria['fecha_fin'])); ?>">
                        <div class="announcement-date">
                            <div class="date-day"><?php echo date('d', strtotime($convocatoria['fecha_fin'])); ?></div>
                            <div class="date-month"><?php echo date('M', strtotime($convocatoria['fecha_fin'])); ?></div>
                            <div class="date-year"><?php echo date('Y', strtotime($convocatoria['fecha_fin'])); ?></div>
                        </div>
                        <div class="announcement-content">
                            <div class="announcement-meta">
                                <span class="announcement-type"><?php echo ucfirst($convocatoria['tipo']); ?></span>
                                <span class="announcement-status">Finalizada</span>
                            </div>
                            <h3 class="announcement-title"><?php echo $convocatoria['titulo']; ?></h3>
                            <p class="announcement-excerpt"><?php echo substr(strip_tags($convocatoria['descripcion']), 0, 150) . '...'; ?></p>
                            <div class="announcement-footer">
                                <a href="<?php echo SITE_URL; ?>/pages/announcements/detalle.php?id=<?php echo $convocatoria['id']; ?>" class="announcement-link">Ver detalles <i class="fas fa-arrow-right"></i></a>
                                <?php if (!empty($convocatoria['documento'])): ?>
                                    <a href="<?php echo SITE_URL; ?>/uploads/documentos/<?php echo $convocatoria['documento']; ?>" class="announcement-link" target="_blank"><i class="fas fa-file-pdf"></i> Descargar PDF</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    No hay convocatorias pasadas disponibles en este momento.
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
    .section-title {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 25px;
    text-align: center;
    color: #444;
}

.past-events {
    display: flex;
    flex-direction: column;
    gap: 16px;
    padding: 10px 0;
}

.past-event {
    background-color: #eaeaea;
    border-left: 6px solid #002147;
    padding: 15px 20px;
    border-radius: 8px;
    transition: background-color 0.3s ease;
}

.past-event:hover {
    background-color: #dcdcdc;
}

.past-event .event-title {
    font-size: 1.2rem;
    font-weight: bold;
    color: #002147;
    margin-bottom: 5px;
}

.past-event .event-date,
.past-event .event-location {
    font-size: 0.9rem;
    color: #555;
    display: flex;
    align-items: center;
    gap: 6px;
}

.past-event i {
    color: #0055a4;
}

</style>

<!-- Announcements Filter Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterType = document.getElementById('filter-type');
    const filterYear = document.getElementById('filter-year');
    const announcementItems = document.querySelectorAll('.announcement-item');
    
    // Filter function
    function filterAnnouncements() {
        const type = filterType.value;
        const year = filterYear.value;
        
        announcementItems.forEach(item => {
            const typeMatch = type === 'all' || item.dataset.type === type;
            const yearMatch = year === 'all' || item.dataset.year === year;
            
            if (typeMatch && yearMatch) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }
    
    // Add event listeners
    filterType.addEventListener('change', filterAnnouncements);
    filterYear.addEventListener('change', filterAnnouncements);
});
</script>

<?php include '../../includes/footer.php'; ?>