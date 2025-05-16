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

// Verificar si el usuario es administrador
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: " . SITE_URL . "/admin/dashboard.php");
    exit;
}

// Título de la página
$pageTitle = 'Nuevo Usuario';

// Procesar formulario
$mensaje = '';
$tipoMensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $nombre = trim($_POST['nombre']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmar_password = trim($_POST['confirmar_password']);
    $rol = $_POST['rol'];
    
    // Validar datos
    if (empty($nombre) || empty($username) || empty($email) || empty($password) || empty($confirmar_password)) {
        $mensaje = "Por favor, complete todos los campos obligatorios.";
        $tipoMensaje = "danger";
    } elseif ($password !== $confirmar_password) {
        $mensaje = "Las contraseñas no coinciden.";
        $tipoMensaje = "danger";
    } elseif (strlen($password) < 6) {
        $mensaje = "La contraseña debe tener al menos 6 caracteres.";
        $tipoMensaje = "danger";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "Por favor, ingrese un correo electrónico válido.";
        $tipoMensaje = "danger";
    } else {
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
            
            // Verificar si el nombre de usuario ya existe
            $stmt = $db->prepare("SELECT id FROM usuarios WHERE username = :username");
            $stmt->execute(['username' => $username]);
            if ($stmt->fetch()) {
                $mensaje = "El nombre de usuario ya está en uso. Por favor, elija otro.";
                $tipoMensaje = "danger";
            } else {
                // Verificar si el correo electrónico ya existe
                $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = :email");
                $stmt->execute(['email' => $email]);
                if ($stmt->fetch()) {
                    $mensaje = "El correo electrónico ya está registrado. Por favor, utilice otro.";
                    $tipoMensaje = "danger";
                } else {
                    // Encriptar contraseña
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insertar usuario en la base de datos
                    $stmt = $db->prepare("
                        INSERT INTO usuarios (nombre, username, email, password, rol)
                        VALUES (:nombre, :username, :email, :password, :rol)
                    ");
                    
                    $stmt->execute([
                        'nombre' => $nombre,
                        'username' => $username,
                        'email' => $email,
                        'password' => $password_hash,
                        'rol' => $rol
                    ]);
                    
                    $mensaje = "Usuario creado correctamente.";
                    $tipoMensaje = "success";
                    
                    // Redireccionar después de 2 segundos
                    header("refresh:2;url=index.php");
                }
            }
        } catch (PDOException $e) {
            $mensaje = "Error al crear el usuario: " . $e->getMessage();
            $tipoMensaje = "danger";
        }
    }
}

// Incluir header
include '../includes/header.php';
?>

<div class="content-header">
    <div class="content-header-title">
        <h1>Nuevo Usuario</h1>
        <p>Crea un nuevo usuario para el sistema.</p>
    </div>
    <div class="content-header-actions">
        <a href="index.php" class="btn btn-exit btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-<?php echo $tipoMensaje; ?> alert-dismissible fade show" role="alert">
        <?php echo $mensaje; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2>Formulario de Usuario</h2>
    </div>
    <div class="card-body">
        <form action="" method="post">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="nombre">Nombre Completo <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="username">Nombre de Usuario <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="email">Correo Electrónico <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="password">Contraseña <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <small class="form-text text-muted">La contraseña debe tener al menos 6 caracteres.</small>
                </div>
                <div class="form-group col-md-6">
                    <label for="confirmar_password">Confirmar Contraseña <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="confirmar_password" name="confirmar_password" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="rol">Rol <span class="text-danger">*</span></label>
                <select class="form-control" id="rol" name="rol" required>
                    <option value="editor">Editor</option>
                    <option value="admin">Administrador</option>
                </select>
                <small class="form-text text-muted">
                    <strong>Editor:</strong> Puede gestionar contenido pero no usuarios.
                    <br>
                    <strong>Administrador:</strong> Acceso completo al sistema.
                </small>
            </div>
            
            <div class="form-group mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar Usuario
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<style>
    /* Layout general */
    .form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    /* Columnas */
    .col-md-6 {
        flex: 1 1 48%;
    }

    .col-md-12 {
        flex: 1 1 100%;
    }

    /* Inputs y Textareas */
    .form-control {
        display: block;
        width: 100%;
        padding: 0.6rem 1rem;
        font-size: 1rem;
        color: #333;
        background-color: #fff;
        border: 1px solid #ccc;
        border-radius: 0.5rem;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
        transition: border-color 0.3s, box-shadow 0.3s;
    }

    .form-control:focus {
        border-color: #004b8d;
        box-shadow: 0 0 0 3px rgba(0, 75, 141, 0.2);
        outline: none;
    }

    /* Etiquetas */
    label {
        font-weight: 600;
        margin-bottom: 0.5rem;
        display: block;
        color: #222;
    }

    /* Botones */
    .btn {
        border-radius: 0.5rem;
        padding: 0.6rem 1.25rem;
        font-weight: 600;
        transition: background-color 0.2s, transform 0.2s;
    }

    .btn-primary:hover {
        background-color: #003f77;
        transform: scale(1.02);
    }

    .btn-secondary:hover {
        background-color: #6c757d;
        transform: scale(1.02);
    }

    /* Inputs de archivo */
    .custom-file {
        position: relative;
        display: block;
        width: 100%;
        height: auto;
    }

    .custom-file-input {
        opacity: 0;
        width: 100%;
        height: 100%;
        position: absolute;
        top: 0;
        left: 0;
        z-index: 2;
        cursor: pointer;
    }

    .custom-file-label {
        display: block;
        width: 100%;
        padding: 0.6rem 1rem;
        border: 1px solid #ccc;
        border-radius: 0.5rem;
        background-color: #f8f9fa;
        color: #555;
        position: relative;
        z-index: 1;
        cursor: pointer;
    }

    .custom-file-label::after {
        content: "📁";
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1rem;
        color: #666;
    }

    /* Textos pequeños */
    .form-text {
        font-size: 0.85rem;
        color: #666;
    }

    /* Alertas */
    .alert {
        border-radius: 0.5rem;
        padding: 1rem;
    }

    /* Utilidades */
    .mt-4 {
        margin-top: 2rem;
    }

    .text-danger {
        color: #d9534f;
    }
    .d-flex {
        display: flex;
        align-items: center;
    }

    .justify-between {
        justify-content: space-between;
    }

    .btn-exit {
        margin: 20px 0;
    }
</style>

<?php include '../includes/footer.php'; ?>