<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

$currentPage = 'carnes-embutidos';
$pageTitle = 'Carnes y Embutidos';
$pageDescription = 'Explora las convocatorias del área de Carnes y Embutidos del Centro de Estudios Avanzados de la FESC4. Programas académicos, becas, y más.';

// Obtener convocatorias activas del área de Carnes y Embutidos
$convocatorias = getAnnouncementsByType('activa', 'carnes-embutidos');

include '../../includes/header.php';
?>

<!-- Page Header -->
<section class="hero" style="padding: var(--space-lg) 0;">
  <div class="container">
    <h1 class="hero-title">Carnes y Embutidos</h1>
    <p class="hero-subtitle">Convocatorias y programas para el área de Carnes y Embutidos del CEA FESC4</p>
  </div>
</section>

<!-- Announcements Section -->
<section class="section">
    <div class="container">
        <div class="announcements-tabs">
            <a href="<?php echo SITE_URL; ?>/pages/announcements/index.php" class="tab-item active">Convocatorias Actuales</a>
            <a href="<?php echo SITE_URL; ?>/pages/announcements/pasadas.php" class="tab-item">Convocatorias Pasadas</a>
        </div>
        
        <div class="announcements-intro">
            <h2>Convocatorias Actuales</h2>
            <p>En el área de Carnes y Embutidos, el CEA FESC4 ofrece diversas convocatorias para formación y proyectos especializados. Explora nuestras oportunidades académicas y programas vigentes.</p>
            <p>Te invitamos a estar al tanto de las nuevas convocatorias y aprovechar las oportunidades que se presenten.</p>
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
                <label for="filter-date">Ordenar por:</label>
                <select id="filter-date" class="filter-select">
                    <option value="closest">Fecha límite más cercana</option>
                    <option value="farthest">Fecha límite más lejana</option>
                    <option value="newest">Más recientes</option>
                </select>
            </div>
        </div>
        
        <div class="announcements-grid" id="announcements-container">
            <?php if (count($convocatorias) > 0): ?>
                <?php foreach ($convocatorias as $convocatoria): ?>
                    <div class="announcement-card" data-type="<?php echo $convocatoria['tipo']; ?>">
                        <?php if (!empty($convocatoria['imagen'])): ?>
                            <div class="announcement-image">
                                <img src="<?php echo SITE_URL; ?>/uploads/convocatorias/<?php echo $convocatoria['imagen']; ?>" alt="<?php echo $convocatoria['titulo']; ?>">
                            </div>
                        <?php endif; ?>
                        
                        <div class="announcement-content">
                            <div class="announcement-meta">
                                <span class="announcement-type"><?php echo ucfirst($convocatoria['tipo']); ?></span>
                                <span class="announcement-date">Publicada: <?php echo formatDate($convocatoria['fecha_publicacion']); ?></span>
                            </div>
                            
                            <h3 class="announcement-title"><?php echo $convocatoria['titulo']; ?></h3>
                            
                            <div class="announcement-excerpt">
                                <?php echo substr(strip_tags($convocatoria['descripcion']), 0, 150) . '...'; ?>
                            </div>
                            
                            <div class="announcement-dates">
                                <div class="date-item">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>Inicio:</span> <?php echo formatDate($convocatoria['fecha_inicio']); ?>
                                </div>
                                <div class="date-item">
                                    <i class="fas fa-calendar-times"></i>
                                    <span>Cierre:</span> <?php echo formatDate($convocatoria['fecha_fin']); ?>
                                </div>
                            </div>
                            
                            <div class="announcement-footer">
                                <a href="<?php echo SITE_URL; ?>/pages/announcements/detalle.php?id=<?php echo $convocatoria['id']; ?>" class="btn btn-primary btn-sm">
                                    Ver Detalles
                                </a>
                                <?php if (!empty($convocatoria['documento'])): ?>
                                    <a href="<?php echo SITE_URL; ?>/uploads/documentos/<?php echo $convocatoria['documento']; ?>" class="btn btn-outline btn-sm" target="_blank">
                                        <i class="fas fa-file-pdf"></i> Descargar PDF
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info" style="grid-column: 1 / -1;">
                    No hay convocatorias activas en este momento. Por favor, revisa más tarde o consulta las convocatorias pasadas.
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Subscription Section -->
<section class="section bg-light">
    <div class="container">
        <div class="subscription-container">
            <div class="subscription-content">
                <h2>Mantente Informado</h2>
                <p>Suscríbete a nuestro boletín para recibir notificaciones sobre nuevas convocatorias, eventos y noticias del área de Carnes y Embutidos del CEA FESC4.</p>
                <form class="subscription-form" action="<?php echo SITE_URL; ?>/subscribe.php" method="post">
                    <div class="form-group">
                        <input type="email" name="email" class="form-input" placeholder="Tu correo electrónico" required>
                        <button type="submit" class="btn btn-primary">
                            Suscribirme
                        </button>
                    </div>
                </form>
            </div>
            <div class="subscription-image">
                <img src="<?php echo SITE_URL; ?>/assets/img/newsletter.jpg" alt="Suscríbete a nuestro boletín">
            </div>
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
    .section-title {
    font-size: 2.2rem;
    font-weight: bold;
    text-align: center;
    margin-bottom: 30px;
    color: #002147;
}

.events-calendar {
    background-color: #f4f4f4;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.calendar-body {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.calendar-event {
    background: linear-gradient(135deg, #002147, #0055a4);
    color: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.calendar-event:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.25);
}

.event-date, .event-location {
    font-size: 0.95rem;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.event-title {
    font-size: 1.4rem;
    font-weight: bold;
    margin: 12px 0;
}

.event-date i, .event-location i {
    color: #ffc107;
}

</style>

<!-- Announcements Filter Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterType = document.getElementById('filter-type');
    const filterDate = document.getElementById('filter-date');
    const announcementsContainer = document.getElementById('announcements-container');
    const announcementCards = document.querySelectorAll('.announcement-card');
    
    // Filter by type
    filterType.addEventListener('change', function() {
        const type = this.value;
        
        announcementCards.forEach(card => {
            if (type === 'all' || card.dataset.type === type) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    });
    
    // Sort by date
    filterDate.addEventListener('change', function() {
        const sortBy = this.value;
        const cardsArray = Array.from(announcementCards);
        
        cardsArray.sort((a, b) => {
            const dateAText = a.querySelector('.date-item:nth-child(2)').textContent.replace('Cierre:', '').trim();
            const dateBText = b.querySelector('.date-item:nth-child(2)').textContent.replace('Cierre:', '').trim();
            
            const dateA = new Date(dateAText.split('/').reverse().join('-'));
            const dateB = new Date(dateBText.split('/').reverse().join('-'));
            
            if (sortBy === 'closest') {
                return dateA - dateB;
            } else if (sortBy === 'farthest') {
                return dateB - dateA;
            } else if (sortBy === 'newest') {
                const publishDateAText = a.querySelector('.announcement-date').textContent.replace('Publicada:', '').trim();
                const publishDateBText = b.querySelector('.announcement-date').textContent.replace('Publicada:', '').trim();
                
                const publishDateA = new Date(publishDateAText.split('/').reverse().join('-'));
                const publishDateB = new Date(publishDateBText.split('/').reverse().join('-'));
                
                return publishDateB - publishDateA;
            }
        });
        
        // Remove all cards
        announcementCards.forEach(card => {
            card.remove();
        });
        
        // Append sorted cards
        cardsArray.forEach(card => {
            announcementsContainer.appendChild(card);
        });
    });
});
</script>

<?php include '../../includes/footer.php'; ?>
