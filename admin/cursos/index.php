<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/functions.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: " . SITE_URL . "/admin/login.php");
    exit;
}

$pageTitle = 'Gestión de Cursos';

// Conexión a la BD
$db = new PDO(
    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
    DB_USER,
    DB_PASS,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);

// Manejo de eliminación
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];

    try {
        $stmt = $db->prepare("SELECT imagen FROM cursos WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $curso = $stmt->fetch();

        if ($curso && !empty($curso['imagen'])) {
            $imagePath = '../../uploads/cursos/' . $curso['imagen'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $stmt = $db->prepare("DELETE FROM cursos WHERE id = :id");
        $stmt->execute(['id' => $id]);

        $mensaje = "Curso eliminado correctamente.";
        $tipoMensaje = "success";
    } catch (PDOException $e) {
        $mensaje = "Error al eliminar el curso: " . $e->getMessage();
        $tipoMensaje = "danger";
    }
}

// Obtener filtros
$filtroEstado = $_GET['estado'] ?? '';
$filtroBusqueda = $_GET['buscar'] ?? '';

// Construir consulta con filtros
$sql = "SELECT * FROM cursos WHERE 1=1";
$params = [];

if ($filtroEstado === 'activa') {
    $sql .= " AND estado = 'abierto'";
} elseif ($filtroEstado === 'cerrada') {
    $sql .= " AND estado = 'cerrado'";
} elseif ($filtroEstado === 'completado') {
    $sql .= " AND estado = 'completado'";
}

if (!empty($filtroBusqueda)) {
    $sql .= " AND (titulo LIKE :buscar OR descripcion LIKE :buscar)";
    $params['buscar'] = '%' . $filtroBusqueda . '%';
}

$sql .= " ORDER BY fecha_publicacion DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$cursos = $stmt->fetchAll();

// Obtener estadísticas
try {
    $stmtTotal = $db->query("SELECT COUNT(*) AS total FROM cursos");
    $stmtActivas = $db->query("SELECT COUNT(*) AS activas FROM cursos WHERE estado = 'abierto'");
    $stmtCerradas = $db->query("SELECT COUNT(*) AS cerradas FROM cursos WHERE estado = 'cerrado'");
    $stmtCompletadas = $db->query("SELECT COUNT(*) AS completadas FROM cursos WHERE estado = 'completo'");

    $estadisticas = [
        'total' => $stmtTotal->fetch()['total'] ?? 0,
        'activas' => $stmtActivas->fetch()['activas'] ?? 0,
        'cerradas' => $stmtCerradas->fetch()['cerradas'] ?? 0,
        'completadas' => $stmtCompletadas->fetch()['completadas'] ?? 0
    ];
} catch (PDOException $e) {
    $mensaje = "Error al obtener estadísticas: " . $e->getMessage();
    $tipoMensaje = "danger";
    $estadisticas = ['total' => 0, 'activas' => 0, 'cerradas' => 0, 'completadas' => 0];
}

// Incluir header
include '../includes/header.php';
?>

<div class="content-header">
    <div class="content-header-title">
        <h1>Gestión de Cursos</h1>
        <p>Administra los cursos del Centro de Estudios Avanzados.</p>
    </div>
</div>

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
            <i class="fas fa-bullhorn"></i>
        </div>
        <div class="stats-card-content">
            <h3><?php echo $estadisticas['total'] ?? 0; ?></h3>
            <p>Total Convocatorias</p>
        </div>
    </div>
    
    <div class="stats-card">
        <div class="stats-card-icon" style="background-color: #e6f7ff; color: #0088cc;">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stats-card-content">
            <h3><?php echo $estadisticas['activas'] ?? 0; ?></h3>
            <p>Activas</p>
        </div>
    </div>
    
    <div class="stats-card">
        <div class="stats-card-icon" style="background-color: #ffb3b3; color: #ff6666;">
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="stats-card-content">
            <h3><?php echo $estadisticas['cerradas'] ?? 0; ?></h3>
            <p>Cerradas</p>
        </div>
    </div>

    <div class="stats-card">
        <div class="stats-card-icon" style="background-color: #fff3cd; color: #856404;">
            <i class="fas fa-flag-checkered"></i>
        </div>
        <div class="stats-card-content">
            <h3><?php echo $estadisticas['completadas'] ?? 0; ?></h3>
            <p>Completadas</p>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form action="" method="get" class="filtros-form">
            <div class="form-row filter-bar">
                <div class="form-group">
                    <select class="form-control custom-select" id="estado" name="estado">
                        <option value="">Todas</option>
                        <option value="activa" <?php echo $filtroEstado === 'activa' ? 'selected' : ''; ?>>Activas</option>
                        <option value="cerrada" <?php echo $filtroEstado === 'cerrada' ? 'selected' : ''; ?>>Cerradas</option>
                        <option value="completado" <?php echo $filtroEstado === 'completado' ? 'selected' : ''; ?>>Completadas</option>
                    </select>
                </div>

                <div class="form-group flex-grow search-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="form-control search-input" id="buscar" name="buscar"
                        value="<?php echo htmlspecialchars($filtroBusqueda); ?>" placeholder="Buscar título o descripción...">
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-filter btn-block">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="content-header-actions">
    <a href="crear.php" class="btn btn-cv btn-primary">
        <i class="fas fa-plus"></i> Nuevo Curso
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h2>Listado de Cursos</h2>
        <div class="card-header-actions">
            <div class="input-group">
                <input type="text" id="searchInput" class="form-control" placeholder="Buscar cursos...">
                <div class="input-group-append">
                    <span class="input-group-text">
                        <i class="fas fa-search"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <?php if (count($cursos) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Instructor</th>
                            <th>Fecha Inicio</th>
                            <th>Fecha Fin</th>
                            <th>Precio</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cursos as $curso): ?>
                            <tr>
                                <td><?php echo $curso['id']; ?></td>
                                <td><?php echo $curso['titulo']; ?></td>
                                <td><?php echo $curso['instructor']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($curso['fecha_inicio'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($curso['fecha_fin'])); ?></td>
                                <td>$<?php echo number_format($curso['precio'], 2); ?></td>
                                <td>
                                    <?php if ($curso['estado'] == 'abierto'): ?>
                                        <span class="badge badge-success">Abierto</span>
                                    <?php elseif ($curso['estado'] == 'completo'): ?>
                                        <span class="badge badge-warning">Completo</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Cerrado</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="editar.php?id=<?php echo $curso['id']; ?>" class="btn btn-sm btn-info" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="#" class="btn btn-sm btn-danger btn-delete" data-id="<?php echo $curso['id']; ?>" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <a href="<?php echo SITE_URL; ?>/pages/courses/detalle.php?id=<?php echo $curso['id']; ?>" class="btn btn-sm btn-secondary" title="Ver" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                No hay cursos registrados. <a href="crear.php">Crear un nuevo curso</a>.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de confirmación de eliminación -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ¿Está seguro de que desea eliminar este curso? Esta acción no se puede deshacer.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <a href="#" id="confirmDelete" class="btn btn-danger">Eliminar</a>
            </div>
        </div>
    </div>
</div>

<style>
    .fade {
        display: none;
    }

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
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Búsqueda en la tabla
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                const tableRows = document.querySelectorAll('tbody tr');
                
                tableRows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }
        
        // Modal de confirmación de eliminación
        const deleteButtons = document.querySelectorAll('.btn-delete');
        const confirmDeleteButton = document.getElementById('confirmDelete');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                confirmDeleteButton.href = 'index.php?delete=' + id;
                
                // Mostrar modal (simulado)
                if (confirm('¿Está seguro de que desea eliminar este curso? Esta acción no se puede deshacer.')) {
                    window.location.href = confirmDeleteButton.href;
                }
            });
        });
    });
</script>

<?php include '../includes/footer.php'; ?>