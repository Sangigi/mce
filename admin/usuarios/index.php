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

// Verificar si el usuario tiene permisos de administrador
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
    
    $stmt = $db->prepare("SELECT rol FROM usuarios WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $usuario = $stmt->fetch();
    
    if (!$usuario || $usuario['rol'] !== 'admin') {
        header("Location: " . SITE_URL . "/admin/dashboard.php");
        exit;
    }
} catch (PDOException $e) {
    $error = "Error al verificar permisos: " . $e->getMessage();
}

// Título de la página
$pageTitle = 'Gestión de Usuarios';

// Obtener usuarios
try {
    // Filtros
    $filtroRol = isset($_GET['rol']) ? $_GET['rol'] : '';
    $filtroBusqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
    
    // Construir consulta
    $sql = "SELECT * FROM usuarios";
    $params = [];
    
    if (!empty($filtroRol)) {
        $sql .= " WHERE rol = :rol";
        $params['rol'] = $filtroRol;
        
        if (!empty($filtroBusqueda)) {
            $sql .= " AND (nombre LIKE :busqueda OR username LIKE :busqueda OR email LIKE :busqueda)";
            $params['busqueda'] = "%$filtroBusqueda%";
        }
    } elseif (!empty($filtroBusqueda)) {
        $sql .= " WHERE nombre LIKE :busqueda OR username LIKE :busqueda OR email LIKE :busqueda";
        $params['busqueda'] = "%$filtroBusqueda%";
    }
    
    $sql .= " ORDER BY fecha_creacion DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $usuarios = $stmt->fetchAll();
    
    // Contar usuarios por rol
    $stmt = $db->query("
        SELECT 
            SUM(CASE WHEN rol = 'admin' THEN 1 ELSE 0 END) as admins,
            SUM(CASE WHEN rol = 'editor' THEN 1 ELSE 0 END) as editores,
            COUNT(*) as total
        FROM usuarios
    ");
    $estadisticas = $stmt->fetch();
    
} catch (PDOException $e) {
    $error = "Error al obtener usuarios: " . $e->getMessage();
}

// Eliminar usuario
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    
    // No permitir eliminar al propio usuario
    if ($id == $_SESSION['user_id']) {
        $mensaje = "No puede eliminar su propio usuario.";
        $tipoMensaje = "danger";
    } else {
        try {
            $stmt = $db->prepare("DELETE FROM usuarios WHERE id = :id");
            $stmt->execute(['id' => $id]);
            
            $mensaje = "Usuario eliminado correctamente.";
            $tipoMensaje = "success";
            
            // Recargar usuarios
            header("Location: " . SITE_URL . "/admin/usuarios/");
            exit;
        } catch (PDOException $e) {
            $mensaje = "Error al eliminar el usuario: " . $e->getMessage();
            $tipoMensaje = "danger";
        }
    }
}

// Incluir header
include '../includes/header.php';
?>

<div class="content-header">
    <div class="content-header-title">
        <h1>Gestión de Usuarios</h1>
        <p>Administra los usuarios del sistema.</p>
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
            <i class="fas fa-users"></i>
        </div>
        <div class="stats-card-content">
            <h3><?php echo $estadisticas['total'] ?? 0; ?></h3>
            <p>Total Usuarios</p>
        </div>
    </div>
    
    <div class="stats-card">
        <div class="stats-card-icon" style="background-color: #e6f7ff; color: #0088cc;">
            <i class="fas fa-user-shield"></i>
        </div>
        <div class="stats-card-content">
            <h3><?php echo $estadisticas['admins'] ?? 0; ?></h3>
            <p>Administradores</p>
        </div>
    </div>
    
    <div class="stats-card">
        <div class="stats-card-icon" style="background-color: #e6f9f6; color: #00b8a9;">
            <i class="fas fa-user-edit"></i>
        </div>
        <div class="stats-card-content">
            <h3><?php echo $estadisticas['editores'] ?? 0; ?></h3>
            <p>Editores</p>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form action="" method="get" class="filtros-form">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="rol">Rol</label>
                    <select class="form-control" id="rol" name="rol">
                        <option value="">Todos</option>
                        <option value="admin" <?php echo $filtroRol === 'admin' ? 'selected' : ''; ?>>Administradores</option>
                        <option value="editor" <?php echo $filtroRol === 'editor' ? 'selected' : ''; ?>>Editores</option>
                    </select>
                </div>
                <div class="form-group flex-grow search-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="form-control search-input" id="buscar" name="buscar" value="<?php echo htmlspecialchars($filtroBusqueda); ?>" placeholder="Nombre, usuario o email...">
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
        <i class="fas fa-user-plus"></i> Nuevo Usuario
    </a>
</div>

<!-- Listado de usuarios -->
<div class="card">
    <div class="card-header">
        <h2>Usuarios</h2>
    </div>
    <div class="card-body">
        <?php if (isset($usuarios) && count($usuarios) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Fecha de Creación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($usuario['username']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                <td>
                                    <?php if ($usuario['rol'] === 'admin'): ?>
                                        <span class="badge badge-primary">Administrador</span>
                                    <?php else: ?>
                                        <span class="badge badge-info">Editor</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($usuario['fecha_creacion'])); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="editar.php?id=<?php echo $usuario['id']; ?>" class="btn btn-sm btn-info" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($usuario['id'] != $_SESSION['user_id']): ?>
                                            <a href="#" class="btn btn-sm btn-danger btn-delete" data-id="<?php echo $usuario['id']; ?>" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                No hay usuarios que coincidan con los filtros seleccionados.
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
                if (confirm('¿Está seguro de que desea eliminar este usuario? Esta acción no se puede deshacer.')) {
                    window.location.href = `?eliminar=${id}`;
                }
            });
        });
        
        // Filtro por rol (para filtros rápidos)
        const rolSelect = document.getElementById('rol');
        rolSelect.addEventListener('change', function() {
            document.querySelector('.filtros-form').submit();
        });
    });
</script>

<?php include '../includes/footer.php'; ?>