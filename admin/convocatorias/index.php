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
$pageTitle = 'Gestión de Convocatorias';

// Obtener convocatorias
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
    $sql = "SELECT * FROM convocatorias";
    $params = [];
    
    if (!empty($filtroEstado)) {
        $sql .= " WHERE estado = :estado";
        $params['estado'] = $filtroEstado;
        
        if (!empty($filtroBusqueda)) {
            $sql .= " AND (titulo LIKE :busqueda OR descripcion LIKE :busqueda)";
            $params['busqueda'] = "%$filtroBusqueda%";
        }
    } elseif (!empty($filtroBusqueda)) {
        $sql .= " WHERE titulo LIKE :busqueda OR descripcion LIKE :busqueda";
        $params['busqueda'] = "%$filtroBusqueda%";
    }
    
    $sql .= " ORDER BY fecha_publicacion DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $convocatorias = $stmt->fetchAll();
    
    // Contar convocatorias por estado
    $stmt = $db->query("
        SELECT 
            SUM(CASE WHEN estado = 'activa' THEN 1 ELSE 0 END) as activas,
            SUM(CASE WHEN estado = 'cerrada' THEN 1 ELSE 0 END) as cerradas,
            COUNT(*) as total
        FROM convocatorias
    ");
    $estadisticas = $stmt->fetch();
    
} catch (PDOException $e) {
    $error = "Error al obtener convocatorias: " . $e->getMessage();
}

// Eliminar convocatoria
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    
    try {
        // Obtener información de la convocatoria para eliminar archivos
        $stmt = $db->prepare("SELECT imagen, documento FROM convocatorias WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $convocatoria = $stmt->fetch();
        
        // Eliminar archivos si existen
        if ($convocatoria) {
            if (!empty($convocatoria['imagen'])) {
                $rutaImagen = '../../uploads/convocatorias/' . $convocatoria['imagen'];
                if (file_exists($rutaImagen)) {
                    unlink($rutaImagen);
                }
            }
            
            if (!empty($convocatoria['documento'])) {
                $rutaDocumento = '../../uploads/convocatorias/documentos/' . $convocatoria['documento'];
                if (file_exists($rutaDocumento)) {
                    unlink($rutaDocumento);
                }
            }
        }
        
        // Eliminar registro de la base de datos
        $stmt = $db->prepare("DELETE FROM convocatorias WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        $mensaje = "Convocatoria eliminada correctamente.";
        $tipoMensaje = "success";
        
        // Recargar convocatorias
        header("Location: " . SITE_URL . "/admin/convocatorias/");
        exit;
    } catch (PDOException $e) {
        $mensaje = "Error al eliminar la convocatoria: " . $e->getMessage();
        $tipoMensaje = "danger";
    }
}

// Incluir header
include '../includes/header.php';
?>

<div class="content-header">
    <div class="content-header-title">
        <h1>Gestión de Convocatorias</h1>
        <p>Administra las convocatorias del centro.</p>
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
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form action="" method="get" class="filtros-form">
            <div class="form-row filter-bar">
            <div class="form-group">
              <select class="form-control custom-select" id="estado" name="estado">
                <option value="activa" <?php echo $filtroEstado === 'activa' ? 'selected' : ''; ?>>Activas</option>
                <option value="cerrada" <?php echo $filtroEstado === 'cerrada' ? 'selected' : ''; ?>>Cerradas</option>
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

<div class="content-footer-actions">
    <a href="crear.php" class="btn btn-cv btn-primary">
        <i class="fas fa-plus"></i> Nueva Convocatoria
    </a>
</div>

<!-- Listado de convocatorias -->
<div class="card">
    <div class="card-header">
        <h2>Convocatorias</h2>
    </div>
    <div class="card-body">
        <?php if (isset($convocatorias) && count($convocatorias) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Fechas</th>
                            <th>Estado</th>
                            <th>Publicación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($convocatorias as $convocatoria): ?>
                            <tr>
                                <td>
                                    <div class="convocatoria-titulo">
                                        <?php if (!empty($convocatoria['imagen'])): ?>
                                            <div class="convocatoria-imagen">
                                                <img src="<?php echo SITE_URL; ?>/uploads/convocatorias/<?php echo $convocatoria['imagen']; ?>" alt="<?php echo htmlspecialchars($convocatoria['titulo']); ?>">
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <?php echo htmlspecialchars($convocatoria['titulo']); ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="convocatoria-fechas">
                                        <div><i class="fas fa-calendar-alt"></i> Inicio: <?php echo date('d/m/Y', strtotime($convocatoria['fecha_inicio'])); ?></div>
                                        <div><i class="fas fa-calendar-check"></i> Fin: <?php echo date('d/m/Y', strtotime($convocatoria['fecha_fin'])); ?></div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($convocatoria['estado'] === 'activa'): ?>
                                        <span class="badge badge-success">Activa</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Cerrada</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo date('d/m/Y', strtotime($convocatoria['fecha_publicacion'])); ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="editar.php?id=<?php echo $convocatoria['id']; ?>" class="btn btn-sm btn-info" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if (!empty($convocatoria['documento'])): ?>
                                            <a href="<?php echo SITE_URL; ?>/uploads/convocatorias/documentos/<?php echo $convocatoria['documento']; ?>" class="btn btn-sm btn-primary" title="Ver documento" target="_blank">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="#" class="btn btn-sm btn-danger btn-delete" data-id="<?php echo $convocatoria['id']; ?>" title="Eliminar">
                                            <i class="fas fa-trash"></i>
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
                No hay convocatorias que coincidan con los filtros seleccionados.
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
</style>

<script>
     document.addEventListener('DOMContentLoaded', function() {
        // Confirmación de eliminación
        const deleteButtons = document.querySelectorAll('.btn-delete');
        deleteButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                if (confirm('¿Estás seguro de que deseas eliminar esta convocatoria? Esta acción no se puede deshacer.')) {
                    window.location.href = '?eliminar=' + id;
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