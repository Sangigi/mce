<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

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
$pageTitle = 'Dashboard';

// Obtener estadísticas
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
    
    // Contar convocatorias
    $stmt = $db->query("SELECT COUNT(*) as total FROM convocatorias");
    $convocatorias = $stmt->fetch()['total'];
    
    // Contar cursos
    $stmt = $db->query("SELECT COUNT(*) as total FROM cursos");
    $cursos = $stmt->fetch()['total'];
    
    // Contar usuarios
    $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios");
    $usuarios = $stmt->fetch()['total'];
    
    // Contar contactos
    $stmt = $db->query("SELECT COUNT(*) as total FROM contactos");
    $contactos = $stmt->fetch()['total'];
    
    // Obtener últimas convocatorias
    $stmt = $db->query("SELECT * FROM convocatorias ORDER BY fecha_publicacion DESC LIMIT 5");
    $ultimasConvocatorias = $stmt->fetchAll();
    
    // Obtener últimos cursos
    $stmt = $db->query("SELECT * FROM cursos ORDER BY fecha_publicacion DESC LIMIT 5");
    $ultimosCursos = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Error al obtener estadísticas: " . $e->getMessage();
}

// Incluir header
include 'includes/header.php';
?>

<div class="content-header">
    <div class="content-header-title">
        <h1>Dashboard</h1>
        <p>Bienvenido al panel de administración del Centro de Estudios Avanzados.</p>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <?php echo $error; ?>
    </div>
<?php else: ?>
    <!-- Tarjetas de estadísticas -->
    <div class="stats-cards">
        <div class="stats-card">
            <div class="stats-card-icon">
                <i class="fas fa-bullhorn"></i>
            </div>
            <div class="stats-card-content">
                <h3><?php echo $convocatorias; ?></h3>
                <p>Convocatorias</p>
            </div>
        </div>
        
        <div class="stats-card">
            <div class="stats-card-icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="stats-card-content">
                <h3><?php echo $cursos; ?></h3>
                <p>Cursos</p>
            </div>
        </div>
        
        <div class="stats-card">
            <div class="stats-card-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stats-card-content">
                <h3><?php echo $usuarios; ?></h3>
                <p>Usuarios</p>
            </div>
        </div>
        
        <div class="stats-card">
            <div class="stats-card-icon">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="stats-card-content">
                <h3><?php echo $contactos; ?></h3>
                <p>Mensajes</p>
            </div>
        </div>
    </div>
    
    <!-- Contenido principal -->
    <div class="dashboard-content">
        <!-- Últimas convocatorias -->
        <div class="card">
            <div class="card-header">
                <h2>Últimas Convocatorias</h2>
                <a href="convocatorias/" class="btn btn-sm btn-primary">Ver todas</a>
            </div>
            <div class="card-body">
                <?php if (count($ultimasConvocatorias) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Título</th>
                                    <th>Fecha Inicio</th>
                                    <th>Fecha Fin</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimasConvocatorias as $convocatoria): ?>
                                    <tr>
                                        <td><?php echo $convocatoria['titulo']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($convocatoria['fecha_inicio'])); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($convocatoria['fecha_fin'])); ?></td>
                                        <td>
                                            <?php if ($convocatoria['estado'] == 'activa'): ?>
                                                <span class="badge badge-success">Activa</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Cerrada</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="convocatorias/editar.php?id=<?php echo $convocatoria['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No hay convocatorias registradas.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Últimos cursos -->
        <div class="card mt-4">
            <div class="card-header">
                <h2>Últimos Cursos</h2>
                <a href="cursos/" class="btn btn-sm btn-primary">Ver todos</a>
            </div>
            <div class="card-body">
                <?php if (count($ultimosCursos) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Título</th>
                                    <th>Instructor</th>
                                    <th>Fecha Inicio</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimosCursos as $curso): ?>
                                    <tr>
                                        <td><?php echo $curso['titulo']; ?></td>
                                        <td><?php echo $curso['instructor']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($curso['fecha_inicio'])); ?></td>
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
                                            <a href="cursos/editar.php?id=<?php echo $curso['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No hay cursos registrados.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<style>
    /* Estilos para el dashboard */
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
    
    .dashboard-content {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    
    .card {
        background-color: white;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        margin-bottom: 1.5rem;
    }
    
    .card-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--gray-200);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .card-header h2 {
        font-size: 1.25rem;
        margin: 0;
        color: var(--gray-800);
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    .table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .table th, .table td {
        padding: 0.75rem;
        vertical-align: middle;
        border-top: 1px solid var(--gray-200);
    }
    
    .table tbody td, .stats-card-content {
        text-align: center;
    }

    .stats-card {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
    }

    .table thead th {
        vertical-align: bottom;
        border-bottom: 2px solid var(--gray-200);
        background-color: var(--gray-50);
        color: var(--gray-700);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
    }
    
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: var(--gray-50);
    }
    
    .badge {
        display: inline-block;
        padding: 0.25em 0.5em;
        font-size: 0.75em;
        font-weight: 600;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.25rem;
    }
    
    .badge-success {
        background-color: var(--success);
        color: white;
    }
    
    .badge-warning {
        background-color: var(--warning);
        color: white;
    }
    
    .badge-secondary {
        background-color: var(--gray-500);
        color: white;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .text-muted {
        color: var(--gray-600);
    }
    
    .mt-4 {
        margin-top: 1.5rem;
    }
</style>

<?php include 'includes/footer.php'; ?>