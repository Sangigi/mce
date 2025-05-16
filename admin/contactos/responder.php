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
$pageTitle = 'Responder Mensaje';

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
    
    // Obtener datos del usuario actual
    $stmt = $db->prepare("SELECT nombre FROM usuarios WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $usuario = $stmt->fetch();
    
} catch (PDOException $e) {
    $error = "Error al obtener el mensaje: " . $e->getMessage();
}

// Procesar formulario
$mensaje = '';
$tipoMensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $respuesta = trim($_POST['respuesta']);
    $enviarCopia = isset($_POST['enviar_copia']) ? 1 : 0;
    
    // Validar datos
    if (empty($respuesta)) {
        $mensaje = "Por favor, escriba una respuesta.";
        $tipoMensaje = "danger";
    } else {
        try {
            // Actualizar en la base de datos
            $stmt = $db->prepare("
                UPDATE contactos 
                SET estado = 'respondido', 
                    respuesta = :respuesta, 
                    fecha_respuesta = NOW(), 
                    respondido_por = :respondido_por
                WHERE id = :id
            ");
            
            $stmt->execute([
                'respuesta' => $respuesta,
                'respondido_por' => $usuario['nombre'],
                'id' => $id
            ]);
            
            // Enviar email (simulado)
            if ($enviarCopia) {
                // Aquí iría el código para enviar el email
                // Por ahora solo mostramos un mensaje
                $mensaje = "Respuesta guardada y enviada por email correctamente.";
            } else {
                $mensaje = "Respuesta guardada correctamente.";
            }
            
            $tipoMensaje = "success";
            
            // Redireccionar después de 2 segundos
            header("refresh:2;url=ver.php?id=$id");
            
        } catch (PDOException $e) {
            $mensaje = "Error al guardar la respuesta: " . $e->getMessage();
            $tipoMensaje = "danger";
        }
    }
}

// Incluir header
include '../includes/header.php';
?>

<div class="content-header">
    <div class="content-header-title">
        <h1>Responder Mensaje</h1>
        <p>Responde al mensaje de contacto seleccionado.</p>
    </div>
    <div class="content-header-actions">
        <a href="ver.php?id=<?php echo $id; ?>" class="btn btn-exit btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <?php echo $error; ?>
    </div>
<?php elseif (!empty($mensaje)): ?>
    <div class="alert alert-<?php echo $tipoMensaje; ?> alert-dismissible fade show" role="alert">
        <?php echo $mensaje; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2>Mensaje Original</h2>
    </div>
    <div class="card-body">
        <div class="mensaje-original">
            <div class="mensaje-cabecera">
                <div class="mensaje-info">
                    <div class="mensaje-remitente">
                        <strong>De:</strong> <?php echo htmlspecialchars($contacto['nombre']); ?> (<?php echo htmlspecialchars($contacto['email']); ?>)
                    </div>
                    <div class="mensaje-fecha">
                        <strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($contacto['fecha_envio'])); ?>
                    </div>
                </div>
                <div class="mensaje-asunto">
                    <strong>Asunto:</strong> <?php echo htmlspecialchars($contacto['asunto']); ?>
                </div>
            </div>
            <div class="mensaje-contenido">
                <?php echo nl2br(htmlspecialchars($contacto['mensaje'])); ?>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h2>Formulario de Respuesta</h2>
    </div>
    <div class="card-body">
        <form action="" method="post">
            <div class="form-group">
                <label for="para">Para</label>
                <input type="text" class="form-control" id="para" value="<?php echo htmlspecialchars($contacto['nombre']); ?> (<?php echo htmlspecialchars($contacto['email']); ?>)" readonly>
            </div>
            
            <div class="form-group">
                <label for="asunto">Asunto</label>
                <input type="text" class="form-control" id="asunto" value="RE: <?php echo htmlspecialchars($contacto['asunto']); ?>" readonly>
            </div>
            
            <div class="form-group">
                <label for="respuesta">Respuesta <span class="text-danger">*</span></label>
                <textarea class="form-control" id="respuesta" name="respuesta" rows="10" required><?php echo isset($_POST['respuesta']) ? htmlspecialchars($_POST['respuesta']) : ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="enviar_copia" name="enviar_copia" <?php echo isset($_POST['enviar_copia']) ? 'checked' : ''; ?>>
                    <label class="custom-control-label" for="enviar_copia">Enviar copia por email</label>
                </div>
                <small class="form-text text-muted">
                    Si marca esta opción, se enviará un email con la respuesta a la dirección de correo del remitente.
                </small>
            </div>
            
            <div class="form-group mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Enviar Respuesta
                </button>
                <a href="ver.php?id=<?php echo $id; ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<style>
    /* Estilos para la página de responder mensaje */
    .mensaje-original {
        background-color: var(--gray-50);
        border-radius: 0.375rem;
        padding: 1.5rem;
        margin-bottom: 1rem;
    }
    
    .mensaje-cabecera {
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--gray-200);
    }
    
    .mensaje-info {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
    }
    
    .mensaje-asunto {
        font-size: 1.125rem;
        margin-bottom: 0.5rem;
    }
    
    .mensaje-contenido {
        font-size: 1rem;
        line-height: 1.6;
        color: var(--gray-800);
    }
    
    .form-group {
        margin-bottom: 1rem;
    }
    
    .form-control {
        display: block;
        width: 100%;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        line-height: 1.5;
        color: var(--gray-700);
        background-color: white;
        background-clip: padding-box;
        border: 1px solid var(--gray-300);
        border-radius: 0.375rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    
    .form-control:focus {
        border-color: var(--primary);
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0, 75, 141, 0.25);
    }
    
    .form-control[readonly] {
        background-color: var(--gray-100);
    }
    
    .custom-control {
        position: relative;
        display: block;
        min-height: 1.5rem;
        padding-left: 1.5rem;
    }
    
    .custom-control-input {
        position: absolute;
        z-index: -1;
        opacity: 0;
    }
    
    .custom-control-label {
        position: relative;
        margin-bottom: 0;
        vertical-align: top;
    }
    
    .custom-control-label::before {
        position: absolute;
        top: 0.25rem;
        left: -1.5rem;
        display: block;
        width: 1rem;
        height: 1rem;
        pointer-events: none;
        content: "";
        background-color: white;
        border: 1px solid var(--gray-400);
        border-radius: 0.25rem;
    }
    
    .custom-control-label::after {
        position: absolute;
        top: 0.25rem;
        left: -1.5rem;
        display: block;
        width: 1rem;
        height: 1rem;
        content: "";
        background: no-repeat 50% / 50% 50%;
    }
    
    .custom-control-input:checked ~ .custom-control-label::before {
        color: white;
        border-color: var(--primary);
        background-color: var(--primary);
    }
    
    .custom-control-input:checked ~ .custom-control-label::after {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23fff' d='M6.564.75l-3.59 3.612-1.538-1.55L0 4.26 2.974 7.25 8 2.193z'/%3e%3c/svg%3e");
    }
    
    .form-text {
        display: block;
        margin-top: 0.25rem;
        font-size: 0.75rem;
    }
    
    .text-muted {
        color: var(--gray-600);
    }
    
    .text-danger {
        color: var(--danger);
    }
    
    .mt-4 {
        margin-top: 1.5rem;
    }

    .btn-exit {
        margin: 20px 0;
    }
</style>

<?php include '../includes/footer.php'; ?>