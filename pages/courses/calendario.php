<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

$currentPage = 'calendario';
$pageTitle = 'Calendario de Eventos';
$pageDescription = 'Consulta nuestro calendario de eventos académicos, conferencias, talleres y actividades del Centro de Estudios Avanzados.';

// Obtener mes y año actual o seleccionado
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Validar mes y año
if ($month < 1 || $month > 12) {
    $month = (int)date('m');
}
if ($year < 2000 || $year > 2050) {
    $year = (int)date('Y');
}

// Obtener primer día del mes y número de días
$firstDay = mktime(0, 0, 0, $month, 1, $year);
$numDays = date('t', $firstDay);
$firstDayOfWeek = date('w', $firstDay);

// Nombres de los meses en español
$monthNames = [
    1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
];

// Obtener eventos del mes
$db = getDB();
$startDate = date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
$endDate = date('Y-m-d', mktime(0, 0, 0, $month + 1, 0, $year));

$stmt = $db->prepare("
    SELECT * FROM eventos 
    WHERE fecha_inicio <= :end_date AND fecha_fin >= :start_date
    ORDER BY fecha_inicio ASC
");
$stmt->execute([
    ':start_date' => $startDate,
    ':end_date' => $endDate
]);
$eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Organizar eventos por día
$eventosPorDia = [];
foreach ($eventos as $evento) {
    $fechaInicio = new DateTime($evento['fecha_inicio']);
    $fechaFin = new DateTime($evento['fecha_fin']);
    
    // Iterar por cada día del evento
    $interval = new DateInterval('P1D');
    $dateRange = new DatePeriod($fechaInicio, $interval, $fechaFin->modify('+1 day'));
    
    foreach ($dateRange as $date) {
        // Solo incluir días del mes actual
        if ($date->format('m') == $month && $date->format('Y') == $year) {
            $day = (int)$date->format('d');
            if (!isset($eventosPorDia[$day])) {
                $eventosPorDia[$day] = [];
            }
            $eventosPorDia[$day][] = $evento;
        }
    }
}

// Calcular mes anterior y siguiente
$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}

$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}

include '../../includes/header.php';
?>

<!-- Page Header -->
<!-- <section class="page-header">
    <div class="container">
        <h1>Calendario de Eventos</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Inicio</a></li>
                <li class="breadcrumb-item active" aria-current="page">Calendario</li>
            </ol>
        </nav>
    </div>
</section> -->

<!-- Page Header -->
<section class="hero" style="padding: var(--space-lg) 0;">
  <div class="container">
    <h1 class="hero-title">Calendario de Eventos</h1>
    <p class="hero-subtitle">Principios rectores del Centro de Estudios Avanzados</p>
  </div>
</section>

<!-- Calendar Section -->
<section class="section">
    <div class="container">
        <div class="courses-tabs">
            <a href="<?php echo SITE_URL; ?>/pages/courses/index.php" class="tab-item">Todos los Cursos</a>
            <a href="<?php echo SITE_URL; ?>/pages/courses/proximos.php" class="tab-item">Próximos Cursos</a>
            <a href="<?php echo SITE_URL; ?>/pages/courses/calendario.php" class="tab-item active">Calendario</a>
        </div>
        <div class="calendar-container">
            <div class="calendar-header">
                <div class="calendar-nav">
                    <a href="<?php echo SITE_URL; ?>/pages/calendar.php?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" class="calendar-nav-btn">
                        <i class="fas fa-chevron-left"></i> Mes Anterior
                    </a>
                    <h2><?php echo $monthNames[$month] . ' ' . $year; ?></h2>
                    <a href="<?php echo SITE_URL; ?>/pages/calendar.php?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" class="calendar-nav-btn">
                        Mes Siguiente <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
                <div class="calendar-view-options">
                    <a href="<?php echo SITE_URL; ?>/pages/calendar.php?month=<?php echo date('m'); ?>&year=<?php echo date('Y'); ?>" class="btn btn-sm btn-outline">
                        <i class="fas fa-calendar-day"></i> Hoy
                    </a>
                    <a href="<?php echo SITE_URL; ?>/pages/upcoming.php" class="btn btn-sm btn-outline">
                        <i class="fas fa-list"></i> Vista de Lista
                    </a>
                </div>
            </div>
            
            <div class="calendar-grid">
                <div class="calendar-weekdays">
                    <div>Domingo</div>
                    <div>Lunes</div>
                    <div>Martes</div>
                    <div>Miércoles</div>
                    <div>Jueves</div>
                    <div>Viernes</div>
                    <div>Sábado</div>
                </div>
                
                <div class="calendar-days">
                    <?php
                    // Días vacíos al inicio
                    for ($i = 0; $i < $firstDayOfWeek; $i++) {
                        echo '<div class="calendar-day empty"></div>';
                    }
                    
                    // Días del mes
                    for ($day = 1; $day <= $numDays; $day++) {
                        $isToday = ($day == date('j') && $month == date('m') && $year == date('Y'));
                        $hasEvents = isset($eventosPorDia[$day]) && count($eventosPorDia[$day]) > 0;
                        
                        echo '<div class="calendar-day ' . ($isToday ? 'today' : '') . '">';
                        echo '<div class="day-number">' . $day . '</div>';
                        
                        if ($hasEvents) {
                            echo '<div class="day-events">';
                            foreach ($eventosPorDia[$day] as $evento) {
                                $eventClass = '';
                                switch ($evento['tipo']) {
                                    case 'academico':
                                        $eventClass = 'event-academic';
                                        break;
                                    case 'cultural':
                                        $eventClass = 'event-cultural';
                                        break;
                                    case 'administrativo':
                                        $eventClass = 'event-admin';
                                        break;
                                    default:
                                        $eventClass = 'event-other';
                                }
                                
                                echo '<div class="day-event ' . $eventClass . '">';
                                echo '<a href="#event-' . $evento['id'] . '" class="event-link" data-toggle="modal" data-target="#eventModal-' . $evento['id'] . '">';
                                echo $evento['titulo'];
                                echo '</a>';
                                echo '</div>';
                            }
                            echo '</div>';
                        }
                        
                        echo '</div>';
                    }
                    
                    // Días vacíos al final
                    $lastDayOfWeek = (($firstDayOfWeek + $numDays) % 7);
                    if ($lastDayOfWeek > 0) {
                        for ($i = 0; $i < (7 - $lastDayOfWeek); $i++) {
                            echo '<div class="calendar-day empty"></div>';
                        }
                    }
                    ?>
                </div>
            </div>
            
            <div class="calendar-legend">
                <div class="legend-item">
                    <span class="legend-color event-academic"></span>
                    <span>Académico</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color event-cultural"></span>
                    <span>Cultural</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color event-admin"></span>
                    <span>Administrativo</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color event-other"></span>
                    <span>Otro</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Event Modals -->
<?php foreach ($eventos as $evento): ?>
<div class="modal" id="eventModal-<?php echo $evento['id']; ?>" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo $evento['titulo']; ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php if (!empty($evento['imagen'])): ?>
                    <div class="event-image">
                        <img src="<?php echo SITE_URL; ?>/uploads/eventos/<?php echo $evento['imagen']; ?>" alt="<?php echo $evento['titulo']; ?>">
                    </div>
                <?php endif; ?>
                
                <div class="event-details">
                    <div class="event-detail">
                        <i class="fas fa-calendar"></i>
                        <span>Fecha:</span>
                        <?php
                        $fechaInicio = new DateTime($evento['fecha_inicio']);
                        $fechaFin = new DateTime($evento['fecha_fin']);
                        
                        if ($fechaInicio->format('Y-m-d') == $fechaFin->format('Y-m-d')) {
                            echo $fechaInicio->format('d/m/Y');
                        } else {
                            echo 'Del ' . $fechaInicio->format('d/m/Y') . ' al ' . $fechaFin->format('d/m/Y');
                        }
                        ?>
                    </div>
                    
                    <div class="event-detail">
                        <i class="fas fa-clock"></i>
                        <span>Hora:</span>
                        <?php echo date('H:i', strtotime($evento['fecha_inicio'])); ?> - <?php echo date('H:i', strtotime($evento['fecha_fin'])); ?>
                    </div>
                    
                    <div class="event-detail">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Lugar:</span>
                        <?php echo $evento['ubicacion']; ?>
                    </div>
                    
                    <div class="event-detail">
                        <i class="fas fa-tag"></i>
                        <span>Tipo:</span>
                        <?php echo ucfirst($evento['tipo']); ?>
                    </div>
                </div>
                
                <div class="event-description">
                    <?php echo $evento['descripcion']; ?>
                </div>
                
                <?php if (!empty($evento['enlace'])): ?>
                    <div class="event-link">
                        <a href="<?php echo $evento['enlace']; ?>" class="btn btn-primary btn-sm" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Más información
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <?php if (!empty($evento['enlace_registro'])): ?>
                    <a href="<?php echo $evento['enlace_registro']; ?>" class="btn btn-primary" target="_blank">
                        <i class="fas fa-user-plus"></i> Registrarse
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<style>
    .btn-outline {
        background-color: var(--azul-unam);
    }

    .btn-outline:hover {
        filter: brightness(0.7);
    }

    .modal-dialog {
  max-width: 640px;
  margin: 3rem auto;
}

.modal-content {
  border: none;
  border-radius: 16px;
  overflow: hidden;
  background-color: #ffffff;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
  font-family: 'Segoe UI', sans-serif;
}

.modal-header {
  background: linear-gradient(135deg, #005baa, #0077cc);
  color: white;
  padding: 1.25rem 1.5rem;
  align-items: center;
}

.modal-title {
  font-size: 1.4rem;
  font-weight: bold;
  color: #fff;
  margin: 0;
}

.modal-header .close {
  color: white;
  font-size: 1.5rem;
  opacity: 1;
  background: none;
  border: none;
}

.modal-body {
  padding: 1.5rem;
}

.event-image img {
  width: 100%;
  border-radius: 12px;
  margin-bottom: 1.5rem;
  object-fit: cover;
  max-height: 280px;
}

.event-details {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.8rem;
  margin-bottom: 1.2rem;
}

.event-detail {
  display: flex;
  align-items: center;
  font-size: 0.95rem;
  color: #333;
  background: #f8f9fa;
  padding: 0.6rem 0.8rem;
  border-radius: 8px;
}

.event-detail i {
  color: #005baa;
  margin-right: 0.6rem;
  font-size: 1rem;
}

.event-detail span {
  font-weight: 600;
  margin-right: 0.3rem;
  color: #000;
}

.event-description {
  font-size: 0.95rem;
  color: #444;
  line-height: 1.6;
  padding-top: 0.8rem;
  border-top: 1px solid #e0e0e0;
}

.event-link {
  margin-top: 1.5rem;
  text-align: center;
}

.event-link .btn {
  background-color: #0077cc;
  border: none;
  padding: 0.5rem 1.2rem;
  border-radius: 6px;
  font-size: 0.9rem;
  transition: background-color 0.3s;
}

.event-link .btn:hover {
  background-color: #005baa;
}

.modal-footer {
  padding: 1rem 1.5rem;
  background-color: #f1f1f1;
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  border-top: none;
}

.modal-footer .btn {
  font-size: 0.9rem;
  border-radius: 6px;
  padding: 0.4rem 1rem;
}

.modal-footer .btn-primary {
  background-color: #0077cc;
  border: none;
}

.modal-footer .btn-primary:hover {
  background-color: #005baa;
}

.modal-footer .btn-secondary {
  background-color:rgb(154, 167, 179);
  border: none;
}

.modal-footer .btn-secondary:hover {
  background-color: rgb(131, 142, 153);
}

</style>

<!-- Calendar Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal functionality
    const modals = document.querySelectorAll('.modal');
    const modalTriggers = document.querySelectorAll('[data-toggle="modal"]');
    const modalCloseButtons = document.querySelectorAll('.modal .close, .modal .btn-secondary');
    
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const modalId = this.getAttribute('data-target');
            const modal = document.querySelector(modalId);
            
            if (modal) {
                modal.style.display = 'block';
                document.body.classList.add('modal-open');
            }
        });
    });
    
    modalCloseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            
            if (modal) {
                modal.style.display = 'none';
                document.body.classList.remove('modal-open');
            }
        });
    });
    
    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
            document.body.classList.remove('modal-open');
        }
    });
});
</script>

<?php include '../../includes/footer.php'; ?>