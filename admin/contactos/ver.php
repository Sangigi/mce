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

// Verificar si se proporcionó un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: " . SITE_URL . "/admin/contactos/");
    exit;
}

$id = $_GET['id'];

// Título de la página
$pageTitle = 'Ver Mensaje';

// Obtener mensaje
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
    
    $stmt = $db->prepare("SELECT * FROM contactos WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $contacto = $stmt->fetch();
    
    if (!$contacto) {
        header("Location: " . SITE_URL . "/admin/contactos/");
        exit;
    }
    
    // Si el mensaje es nuevo, marcarlo como leído
    if ($contacto['estado'] === 'nuevo') {
        $stmt = $db->prepare("UPDATE contactos SET estado = 'leido' WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $contacto['estado'] = 'leido';
    }
    
} catch (PDOException $e) {
    $error = "Error al obtener el mensaje: " . $e->getMessage();
}

// Incluir header
include '../includes/header.php';
?>

<div class="content-header">
    <div class="content-header-title">
        <h1>Ver Mensaje</h1>
        <p>Detalles del mensaje de contacto seleccionado.</p>
    </div>
    <div class="content-header-actions">
        <a href="index.php" class="btn btn-exit btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
        <a href="responder.php?id=<?php echo $contacto['id']; ?>" class="btn btn-primary">
            <i class="fas fa-reply"></i> Responder
        </a>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <?php echo $error; ?>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-header">
            <h2><?php echo htmlspecialchars($contacto['asunto']); ?></h2>
            <div class="mensaje-meta">
                <span class="mensaje-fecha">
                    <i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y H:i', strtotime($contacto['fecha_envio'])); ?>
                </span>
                <span class="mensaje-estado">
                    <?php if ($contacto['estado'] === 'nuevo'): ?>
                        <span class="badge badge-primary">Nuevo</span>
                    <?php elseif ($contacto['estado'] === 'leido'): ?>
                        <span class="badge badge-info">Leído</span>
                    <?php elseif ($contacto['estado'] === 'respondido'): ?>
                        <span class="badge badge-success">Respondido</span>
                    <?php else: ?>
                        <span class="badge badge-secondary">Archivado</span>
                    <?php endif; ?>
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="mensaje-info">
                <div class="mensaje-remitente">
                    <div class="remitente-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="remitente-datos">
                        <h3><?php echo htmlspecialchars($contacto['nombre']); ?></h3>
                        <p>
                            <a href="mailto:<?php echo htmlspecialchars($contacto['email']); ?>">
                                <?php echo htmlspecialchars($contacto['email']); ?>
                            </a>
                        </p>
                        <?php if (!empty($contacto['telefono'])): ?>
                            <p>
                                <a href="tel:<?php echo htmlspecialchars($contacto['telefono']); ?>">
                                    <?php echo htmlspecialchars($contacto['telefono']); ?>
                                </a>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="mensaje-contenido">
                    <div class="mensaje-texto">
                        <?php echo nl2br(htmlspecialchars($contacto['mensaje'])); ?>
                    </div>
                </div>
            </div>
            
            <?php if ($contacto['estado'] === 'respondido'): ?>
                <div class="mensaje-respuesta">
                    <h4>Respuesta</h4>
                    <div class="respuesta-meta">
                        <span>
                            <i class="fas fa-user"></i> Respondido por: <?php echo htmlspecialchars($contacto['respondido_por']); ?>
                        </span>
                        <span>
                            <i class="fas fa-calendar-alt"></i> Fecha: <?php echo date('d/m/Y H:i', strtotime($contacto['fecha_respuesta'])); ?>
                        </span>
                    </div>
                    <div class="respuesta-texto">
                        <?php echo nl2br(htmlspecialchars($contacto['respuesta'])); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="card-footer">
            <div class="btn-group">
                <a href="responder.php?id=<?php echo $contacto['id']; ?>" class="btn btn-primary">
                    <i class="fas fa-reply"></i> Responder
                </a>
                <a href="index.php?archivar=<?php echo $contacto['id']; ?>" class="btn btn-secondary">
                    <i class="fas fa-archive"></i> Archivar
                </a>
                <a href="#" class="btn btn-danger btn-delete" data-id="<?php echo $contacto['id']; ?>">
                    <i class="fas fa-trash"></i> Eliminar
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>

<style>
    /* Estilos para la página de ver mensaje */
    .mensaje-meta {
        display: flex;
        align-items: center;
        gap: 1rem;
        font-size: 0.875rem;
        color: var(--gray-600);
    }
    
    .mensaje-info {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    
    .mensaje-remitente {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid var(--gray-200);
    }
    
    .remitente-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: var(--primary-light);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    
    .remitente-datos h3 {
        margin: 0 0 0.5rem;
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--gray-800);
    }
    
    .remitente-datos p {
        margin: 0 0 0.25rem;
        font-size: 0.875rem;
        color: var(--gray-600);
    }
    
    .mensaje-contenido {
        padding-top: 1rem;
    }
    
    .mensaje-texto {
        font-size: 1rem;
        line-height: 1.6;
        color: var(--gray-800);
    }
    
    .mensaje-respuesta {
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--gray-200);
    }
    
    .mensaje-respuesta h4 {
        margin: 0 0 1rem;
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--gray-800);
    }
    
    .respuesta-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1rem;
        font-size: 0.875rem;
        color: var(--gray-600);
    }
    
    .respuesta-texto {
        font-size: 1rem;
        line-height: 1.6;
        color: var(--gray-800);
        background-color: var(--gray-50);
        padding: 1rem;
        border-radius: 0.375rem;
        border-left: 3px solid var(--primary);
    }
    
    .card-footer {
        padding: 1rem 1.5rem;
        background-color: var(--gray-50);
        border-top: 1px solid var(--gray-200);
    }
    
    @media (max-width: 768px) {
        .mensaje-remitente {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
    }

    .btn-exit {
        margin: 20px 0;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Confirmación de eliminación
        const deleteButton = document.querySelector('.btn-delete');
        if (deleteButton) {
            deleteButton.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                if (confirm('¿Está seguro de que desea eliminar este mensaje? Esta acción no se puede deshacer.')) {
                    window.location.href = `index.php?eliminar=${id}`;
                }
            });
        }
    });
</script>

<?php include '../includes/footer.php'; ?>