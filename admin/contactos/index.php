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
$pageTitle = 'Gestión de Contactos';

// Obtener contactos
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
    $sql = "SELECT * FROM contactos";
    $params = [];
    
    if (!empty($filtroEstado)) {
        $sql .= " WHERE estado = :estado";
        $params['estado'] = $filtroEstado;
        
        if (!empty($filtroBusqueda)) {
            $sql .= " AND (nombre LIKE :busqueda OR email LIKE :busqueda OR asunto LIKE :busqueda OR mensaje LIKE :busqueda)";
            $params['busqueda'] = "%$filtroBusqueda%";
        }
    } elseif (!empty($filtroBusqueda)) {
        $sql .= " WHERE nombre LIKE :busqueda OR email LIKE :busqueda OR asunto LIKE :busqueda OR mensaje LIKE :busqueda";
        $params['busqueda'] = "%$filtroBusqueda%";
    }
    
    $sql .= " ORDER BY fecha_envio DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $contactos = $stmt->fetchAll();
    
    // Contar mensajes por estado
    $stmt = $db->query("SELECT estado, COUNT(*) as total FROM contactos GROUP BY estado");
    $estadisticas = [];
    while ($row = $stmt->fetch()) {
        $estadisticas[$row['estado']] = $row['total'];
    }
    
    $totalNuevos = $estadisticas['nuevo'] ?? 0;
    $totalLeidos = $estadisticas['leido'] ?? 0;
    $totalRespondidos = $estadisticas['respondido'] ?? 0;
    $totalArchivados = $estadisticas['archivado'] ?? 0;
    $totalContactos = $totalNuevos + $totalLeidos + $totalRespondidos + $totalArchivados;
    
} catch (PDOException $e) {
    $error = "Error al obtener contactos: " . $e->getMessage();
}

// Marcar como leído
if (isset($_GET['marcar_leido']) && is_numeric($_GET['marcar_leido'])) {
    $id = $_GET['marcar_leido'];
    
    try {
        $stmt = $db->prepare("UPDATE contactos SET estado = 'leido' WHERE id = :id AND estado = 'nuevo'");
        $stmt->execute(['id' => $id]);
        
        $mensaje = "Mensaje marcado como leído.";
        $tipoMensaje = "success";
        
        // Recargar contactos
        header("Location: " . SITE_URL . "/admin/contactos/");
        exit;
    } catch (PDOException $e) {
        $mensaje = "Error al actualizar el estado del mensaje: " . $e->getMessage();
        $tipoMensaje = "danger";
    }
}

// Archivar mensaje
if (isset($_GET['archivar']) && is_numeric($_GET['archivar'])) {
    $id = $_GET['archivar'];
    
    try {
        $stmt = $db->prepare("UPDATE contactos SET estado = 'archivado' WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        $mensaje = "Mensaje archivado correctamente.";
        $tipoMensaje = "success";
        
        // Recargar contactos
        header("Location: " . SITE_URL . "/admin/contactos/");
        exit;
    } catch (PDOException $e) {
        $mensaje = "Error al archivar el mensaje: " . $e->getMessage();
        $tipoMensaje = "danger";
    }
}

// Eliminar mensaje
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    
    try {
        $stmt = $db->prepare("DELETE FROM contactos WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        $mensaje = "Mensaje eliminado correctamente.";
        $tipoMensaje = "success";
        
        // Recargar contactos
        header("Location: " . SITE_URL . "/admin/contactos/");
        exit;
    } catch (PDOException $e) {
        $mensaje = "Error al eliminar el mensaje: " . $e->getMessage();
        $tipoMensaje = "danger";
    }
}

// Incluir header
include '../includes/header.php';
?>

<div class="content-header">
    <div class="content-header-title">
        <h1>Gestión de Contactos</h1>
        <p>Administra los mensajes de contacto recibidos a través del sitio web.</p>
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
            <i class="fas fa-envelope"></i>
        </div>
        <div class="stats-card-content">
            <h3><?php echo $totalContactos; ?></h3>
            <p>Total Mensajes</p>
        </div>
    </div>
    
    <div class="stats-card">
        <div class="stats-card-icon" style="background-color: #e6f7ff; color: #0088cc;">
            <i class="fas fa-envelope-open"></i>
        </div>
        <div class="stats-card-content">
            <h3><?php echo $totalNuevos; ?></h3>
            <p>Nuevos</p>
        </div>
    </div>
    
    <div class="stats-card">
        <div class="stats-card-icon" style="background-color: #e6f9f6; color: #00b8a9;">
            <i class="fas fa-check"></i>
        </div>
        <div class="stats-card-content">
            <h3><?php echo $totalLeidos; ?></h3>
            <p>Leídos</p>
        </div>
    </div>
    
    <div class="stats-card">
        <div class="stats-card-icon" style="background-color: #e6f4e6; color: #28a745;">
            <i class="fas fa-reply"></i>
        </div>
        <div class="stats-card-content">
            <h3><?php echo $totalRespondidos; ?></h3>
            <p>Respondidos</p>
        </div>
    </div>
    
    <div class="stats-card">
        <div class="stats-card-icon" style="background-color: #f8f9fa; color: #6c757d;">
            <i class="fas fa-archive"></i>
        </div>
        <div class="stats-card-content">
            <h3><?php echo $totalArchivados; ?></h3>
            <p>Archivados</p>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form action="" method="get" class="filtros-form">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="estado">Estado</label>
                    <select class="form-control" id="estado" name="estado">
                        <option value="">Todos</option>
                        <option value="nuevo" <?php echo $filtroEstado === 'nuevo' ? 'selected' : ''; ?>>Nuevos</option>
                        <option value="leido" <?php echo $filtroEstado === 'leido' ? 'selected' : ''; ?>>Leídos</option>
                        <option value="respondido" <?php echo $filtroEstado === 'respondido' ? 'selected' : ''; ?>>Respondidos</option>
                        <option value="archivado" <?php echo $filtroEstado === 'archivado' ? 'selected' : ''; ?>>Archivados</option>
                    </select>
                </div>
                <div class="form-group flex-grow search-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="form-control search-input" id="buscar" name="buscar" value="<?php echo htmlspecialchars($filtroBusqueda); ?>" placeholder="Nombre, email, asunto o mensaje...">
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

<!-- Listado de contactos -->
<div class="card">
    <div class="card-header">
        <h2>Mensajes de Contacto</h2>
    </div>
    <div class="card-body">
        <?php if (count($contactos) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Asunto</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contactos as $contacto): ?>
                            <tr class="<?php echo $contacto['estado'] === 'nuevo' ? 'table-row-new' : ''; ?>">
                                <td>
                                    <?php if ($contacto['estado'] === 'nuevo'): ?>
                                        <span class="badge badge-primary">Nuevo</span>
                                    <?php elseif ($contacto['estado'] === 'leido'): ?>
                                        <span class="badge badge-info">Leído</span>
                                    <?php elseif ($contacto['estado'] === 'respondido'): ?>
                                        <span class="badge badge-success">Respondido</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Archivado</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($contacto['fecha_envio'])); ?></td>
                                <td><?php echo htmlspecialchars($contacto['nombre']); ?></td>
                                <td>
                                    <a href="mailto:<?php echo htmlspecialchars($contacto['email']); ?>">
                                        <?php echo htmlspecialchars($contacto['email']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($contacto['asunto']); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="ver.php?id=<?php echo $contacto['id']; ?>" class="btn btn-sm btn-info" title="Ver mensaje">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="responder.php?id=<?php echo $contacto['id']; ?>" class="btn btn-sm btn-primary" title="Responder">
                                            <i class="fas fa-reply"></i>
                                        </a>
                                        <?php if ($contacto['estado'] === 'nuevo'): ?>
                                            <a href="?marcar_leido=<?php echo $contacto['id']; ?>" class="btn btn-sm btn-success" title="Marcar como leído">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="?archivar=<?php echo $contacto['id']; ?>" class="btn btn-sm btn-secondary" title="Archivar">
                                            <i class="fas fa-archive"></i>
                                        </a>
                                        <a href="#" class="btn btn-sm btn-danger btn-delete" data-id="<?php echo $contacto['id']; ?>" title="Eliminar">
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
                No hay mensajes de contacto que coincidan con los filtros seleccionados.
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
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                if (confirm('¿Está seguro de que desea eliminar este mensaje? Esta acción no se puede deshacer.')) {
                    window.location.href = `?eliminar=${id}`;
                }
            });
        });
    });
</script>

<?php include '../includes/footer.php'; ?>