<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: " . SITE_URL . "/admin/login.php");
    exit;
}

// Título de la página
$pageTitle = 'Gestión de Eventos';

// Obtener eventos
try {
    $db = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    // Filtros
    $filtroEstado = isset($_GET['estado']) ? $_GET['estado'] : '';
    $filtroBusqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
    
    // Construir consulta
    $sql = "SELECT * FROM eventos";
    $params = [];
    
    if (!empty($filtroEstado)) {
        $sql .= " WHERE estado = :estado";
        $params['estado'] = $filtroEstado;
        
        if (!empty($filtroBusqueda)) {
            $sql .= " AND (titulo LIKE :busqueda OR descripcion LIKE :busqueda OR ubicacion LIKE :busqueda)";
            $params['busqueda'] = "%$filtroBusqueda%";
        }
    } elseif (!empty($filtroBusqueda)) {
        $sql .= " WHERE titulo LIKE :busqueda OR descripcion LIKE :busqueda OR ubicacion LIKE :busqueda";
        $params['busqueda'] = "%$filtroBusqueda%";
    }
    
    $sql .= " ORDER BY fecha_inicio DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $eventos = $stmt->fetchAll();
    
    // Contar eventos por estado
    $stmt = $db->query("
        SELECT 
            SUM(CASE WHEN fecha_inicio > NOW() THEN 1 ELSE 0 END) as proximos,
            SUM(CASE WHEN fecha_inicio <= NOW() AND fecha_fin >= NOW() THEN 1 ELSE 0 END) as actuales,
            SUM(CASE WHEN fecha_fin < NOW() THEN 1 ELSE 0 END) as pasados,
            COUNT(*) as total
        FROM eventos
    ");
    $estadisticas = $stmt->fetch();
    
} catch (PDOException $e) {
    $error = "Error al obtener eventos: " . $e->getMessage();
}

// Eliminar evento
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    
    try {
        $stmt = $db->prepare("DELETE FROM eventos WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        $mensaje = "Evento eliminado correctamente.";
        $tipoMensaje = "success";
        
        // Recargar eventos
        header("Location: " . SITE_URL . "/admin/eventos/");
        exit;
    } catch (PDOException $e) {
        $mensaje = "Error al eliminar el evento: " . $e->getMessage();
        $tipoMensaje = "danger";
    }
}

// Incluir header
include '../includes/header.php';
?>

<div class="content-header">
    <div class="content-header-title">
        <h1>Gestión de Eventos</h1>
        <p>Administra los eventos del centro.</p>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<?php if (isset($mensaje)): ?>
    <div class="alert alert-<?php echo $tipoMensaje; ?> alert-dismissible fade show" role="alert">
        <?php echo $mensaje; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Estadísticas -->
<div class="stats-cards">
    <div class="stats-card">
        <div class="stats-card-icon">
            <i class="fas fa-calendar-alt"></i>
        </div>
        <div class="stats-card-content">
            <h3><?php echo $estadisticas['total']; ?></h3>
            <p>Total Eventos</p>
        </div>
    </div>
    
    <div class="stats-card">
        <div class="stats-card-icon" style="background-color: #e6f7ff; color: #0088cc;">
            <i class="fas fa-calendar-plus"></i>
        </div>
        <div class="stats-card-content">
            <h3><?php echo $estadisticas['proximos']; ?></h3>
            <p>Próximos</p>
        </div>
    </div>
    
    <div class="stats-card">
        <div class="stats-card-icon" style="background-color: #e6f9f6; color: #00b8a9;">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="stats-card-content">
            <h3><?php echo $estadisticas['actuales']; ?></h3>
            <p>Actuales</p>
        </div>
    </div>
    
    <div class="stats-card">
        <div class="stats-card-icon" style="background-color: #f8f9fa; color: #6c757d;">
            <i class="fas fa-calendar-times"></i>
        </div>
        <div class="stats-card-content">
            <h3><?php echo $estadisticas['pasados']; ?></h3>
            <p>Pasados</p>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form action="" method="get" class="filtros-form">
            <div class="form-row">
                <div class="form-group">
                    <select class="form-control custom-select" id="estado" name="estado">
                        <option value="">Todos</option>
                        <option value="proximo" <?php echo $filtroEstado === 'proximo' ? 'selected' : ''; ?>>Próximos</option>
                        <option value="actual" <?php echo $filtroEstado === 'actual' ? 'selected' : ''; ?>>Actuales</option>
                        <option value="pasado" <?php echo $filtroEstado === 'pasado' ? 'selected' : ''; ?>>Pasados</option>
                    </select>
                </div>
                
                <div class="form-group flex-grow search-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="form-control search-input" id="buscar" name="buscar" value="<?php echo htmlspecialchars($filtroBusqueda); ?>" placeholder="Buscar título o descripción o ubicación...">
                </div>
                <div class="form-group col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="content-header-actions">
    <a href="crear.php" class="btn btn-cv btn-primary">
        <i class="fas fa-plus"></i> Nuevo Evento
    </a>
</div>

<!-- Listado de eventos -->
<div class="card">
    <div class="card-header">
        <h2>Eventos</h2>
    </div>
    <div class="card-body">
        <?php if (count($eventos) > 0): ?>
            <div class="eventos-grid">
                <?php foreach ($eventos as $evento): ?>
                    <?php
                    // Determinar estado del evento
                    $fechaInicio = new DateTime($evento['fecha_inicio']);
                    $fechaFin = new DateTime($evento['fecha_fin']);
                    $ahora = new DateTime();
                    
                    if ($fechaInicio > $ahora) {
                        $estado = 'proximo';
                        $estadoTexto = 'Próximo';
                        $estadoClase = 'primary';
                    } elseif ($fechaInicio <= $ahora && $fechaFin >= $ahora) {
                        $estado = 'actual';
                        $estadoTexto = 'Actual';
                        $estadoClase = 'success';
                    } else {
                        $estado = 'pasado';
                        $estadoTexto = 'Pasado';
                        $estadoClase = 'secondary';
                    }
                    ?>
                    <div class="evento-card" data-estado="<?php echo $estado; ?>">
                        <div class="evento-header">
                            <span class="badge badge-<?php echo $estadoClase; ?>"><?php echo $estadoTexto; ?></span>
                            <div class="evento-acciones">
                                <a href="editar.php?id=<?php echo $evento['id']; ?>" class="btn btn-sm btn-info" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="#" class="btn btn-sm btn-danger btn-delete" data-id="<?php echo $evento['id']; ?>" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        <div class="evento-body">
                            <h3 class="evento-titulo"><?php echo htmlspecialchars($evento['titulo']); ?></h3>
                            <div class="evento-fechas">
                                <div class="evento-fecha">
                                    <i class="fas fa-calendar-day"></i>
                                    <span>
                                        <?php 
                                        if (date('Y-m-d', strtotime($evento['fecha_inicio'])) === date('Y-m-d', strtotime($evento['fecha_fin']))) {
                                            echo date('d/m/Y', strtotime($evento['fecha_inicio']));
                                        } else {
                                            echo date('d/m/Y', strtotime($evento['fecha_inicio'])) . ' - ' . date('d/m/Y', strtotime($evento['fecha_fin']));
                                        }
                                        ?>
                                    </span>
                                </div>
                                <div class="evento-hora">
                                    <i class="fas fa-clock"></i>
                                    <span>
                                        <?php 
                                        echo date('H:i', strtotime($evento['fecha_inicio'])) . ' - ' . date('H:i', strtotime($evento['fecha_fin']));
                                        ?>
                                    </span>
                                </div>
                            </div>
                            <?php if (!empty($evento['ubicacion'])): ?>
                                <div class="evento-ubicacion">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo htmlspecialchars($evento['ubicacion']); ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="evento-descripcion">
                                <?php echo nl2br(htmlspecialchars(substr($evento['descripcion'], 0, 150) . (strlen($evento['descripcion']) > 150 ? '...' : ''))); ?>
                            </div>
                        </div>
                        <div class="evento-footer">
                            <a href="editar.php?id=<?php echo $evento['id']; ?>" class="btn btn-sm btn-primary">
                                Ver Detalles
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                No hay eventos que coincidan con los filtros seleccionados.
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    /* Estilos para la página de convocatorias */
    .convocatoria-titulo {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stats-card {
        background-color: white;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        padding: 1.5rem;
        display: flex;
        align-items: center;
    }

    .stats-card-icon {
        width: 3rem;
        height: 3rem;
        border-radius: 0.5rem;
        background-color: var(--primary-light);
        color: var(--gray-200);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    
    .stats-card-content h3 {
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0;
        color: var(--gray-800);
    }
    
    .stats-card-content p {
        margin: 0;
        color: var(--gray-600);
        font-size: 0.875rem;
    }

    .stats-card {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        text-align: center;
    }

    .convocatoria-imagen {
        width: 50px;
        height: 50px;
        border-radius: 0.25rem;
        overflow: hidden;
        flex-shrink: 0;
    }
    
    .convocatoria-imagen img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .convocatoria-fechas {
        font-size: 0.875rem;
        color: var(--gray-700);
    }
    
    .convocatoria-fechas i {
        width: 1rem;
        text-align: center;
        color: var(--primary);
        margin-right: 0.25rem;
    }
    
    .badge {
        display: inline-block;
        padding: 0.25em 0.4em;
        font-size: 75%;
        font-weight: 700;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.25rem;
    }
    
    .badge-success {
        color: #fff;
        background-color: #28a745;
    }
    
    .badge-secondary {
        color: #fff;
        background-color: #6c757d;
    }
    .form-row {
      display: flex;
      flex-wrap: wrap;
      align-items: flex-end;
      gap: 20px;
      margin-bottom: 20px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      min-width: 150px;
    }

    .flex-grow {
      flex: 1;
    }

    /* Estilo para el campo de búsqueda con ícono */
    .search-wrapper {
      position: relative;
    }

    .search-icon {
      position: absolute;
      top: 50%;
      left: 15px;
      transform: translateY(-50%);
      color: #aaa;
      font-size: 1rem;
      pointer-events: none;
    }

    .search-input {
      padding-left: 40px !important;
    }

    /* Estilo general para inputs y selects */
    .form-control {
      border-radius: 30px;
      padding: 10px 15px;
      border: 1px solid #ccc;
      transition: border-color 0.3s ease;
    }

    .form-control:focus {
      border-color: #2980b9;
      outline: none;
      box-shadow: 0 0 5px rgba(41, 128, 185, 0.3);
    }

    .btn-filter {
      border-radius: 30px;
      padding: 10px 20px;
    }

    @media (max-width: 768px) {
      .form-row {
        flex-direction: column;
      }

      .form-group {
        width: 100%;
      }
    }

    .btn-cv {
        display: flex;
        justify-content: center;
        padding: 15px;
        font-size: 15px;
        margin-bottom: 20px;
    }

    .eventos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
    }
    
    .evento-card {
        background-color: white;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .evento-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .evento-header {
        padding: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid var(--gray-200);
    }
    
    .evento-acciones {
        display: flex;
        gap: 0.5rem;
    }
    
    .evento-body {
        padding: 1rem;
        flex: 1;
    }
    
    .evento-titulo {
        margin: 0 0 1rem;
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--gray-800);
    }
    
    .evento-fechas {
        margin-bottom: 1rem;
    }
    
    .evento-fecha, .evento-hora, .evento-ubicacion {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
        color: var(--gray-700);
    }
    
    .evento-fecha i, .evento-hora i, .evento-ubicacion i {
        color: var(--primary);
        width: 1rem;
        text-align: center;
    }
    
    .evento-descripcion {
        font-size: 0.875rem;
        color: var(--gray-600);
        margin-top: 1rem;
        line-height: 1.5;
    }
    
    .evento-footer {
        padding: 1rem;
        border-top: 1px solid var(--gray-200);
        display: flex;
        justify-content: flex-end;
    }
    
    @media (max-width: 768px) {
        .eventos-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Confirmación de eliminación
        const deleteButtons = document.querySelectorAll('.btn-delete');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                if (confirm('¿Está seguro de que desea eliminar este evento? Esta acción no se puede deshacer.')) {
                    window.location.href = `?eliminar=${id}`;
                }
            });
        });
        
        // Filtro por estado (para filtros rápidos)
        const estadoSelect = document.getElementById('estado');
        estadoSelect.addEventListener('change', function() {
            document.querySelector('.filtros-form').submit();
        });
    });
</script>

<?php include '../includes/footer.php'; ?>