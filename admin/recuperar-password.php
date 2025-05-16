<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';

// Verificar si ya está logueado
if (isLoggedIn()) {
    redirect(SITE_URL . '/admin/dashboard.php');
}

$error = '';
$success = '';
$step = isset($_GET['step']) ? $_GET['step'] : 'request';

// Procesar solicitud de recuperación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 'request') {
        $email = cleanInput($_POST['email']);
        
        // Validar email
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Por favor, ingrese un email válido.';
        } else {
            // Verificar si el email existe
            global $db;
            $user = $db->getRow("SELECT * FROM usuarios WHERE email = :email", ['email' => $email]);
            
            if ($user) {
                // Generar token de recuperación
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Guardar token en la base de datos
                $db->update('usuarios', 
                    [
                        'reset_token' => $token,
                        'reset_expiry' => $expiry
                    ], 
                    'id = :id', 
                    ['id' => $user['id']]
                );
                
                // Codigo comentado para desarrollo NO USAR EN PRODUCCION
                // $resetLink = SITE_URL . '/admin/recuperar-password.php?step=reset&token=' . $token;
                
                // $success = 'Se ha enviado un enlace de recuperación a su correo electrónico. Por favor, revise su bandeja de entrada.';
                
                // // Para propósitos de demostración, mostramos el enlace
                // $success .= '<br><br><strong>Enlace de recuperación (solo para demostración):</strong><br>';
                // $success .= '<a href="' . $resetLink . '">' . $resetLink . '</a>';
                require_once '../includes/email.php';

                $resetLink = SITE_URL . '/admin/recuperar-password.php?step=reset&token=' . $token;
                $nombre = $user['nombre'] ?? 'Usuario';

                if (enviarCorreoRecuperacion($email, $nombre, $resetLink)) {
                    $success = 'Se ha enviado un enlace de recuperación a su correo electrónico. Por favor, revise su bandeja de entrada.';
                } else {
                    $error = 'Ocurrió un error al enviar el correo. Inténtelo más tarde.';
                }

            } else {
                // No informamos si el email existe o no por seguridad
                $success = 'Si el email está registrado, recibirá un enlace para restablecer su contraseña.';
            }
        }
    } elseif ($step === 'reset') {
        $token = isset($_GET['token']) ? $_GET['token'] : '';
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // Validar contraseñas
        if (empty($password) || strlen($password) < 6) {
            $error = 'La contraseña debe tener al menos 6 caracteres.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Las contraseñas no coinciden.';
        } else {
            // Verificar token
            global $db;
            $user = $db->getRow("SELECT * FROM usuarios WHERE reset_token = :token AND reset_expiry > NOW()", ['token' => $token]);
            
            if ($user) {
                // Actualizar contraseña
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                $db->update('usuarios', 
                    [
                        'password' => $hashedPassword,
                        'reset_token' => null,
                        'reset_expiry' => null
                    ], 
                    'id = :id', 
                    ['id' => $user['id']]
                );
                
                $success = 'Su contraseña ha sido actualizada correctamente. Ahora puede iniciar sesión con su nueva contraseña.';
            } else {
                $error = 'El enlace de recuperación es inválido o ha expirado.';
            }
        }
    }
}

// Título de la página
$pageTitle = 'Recuperar Contraseña';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | Centro de Estudios Avanzados</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --login-primary: var(--cea-blue, #004B8D);
            --login-secondary: var(--cea-green, #00A651);
            --login-bg: #f5f8fb;
            --login-card-bg: #ffffff;
            --login-text: #333333;
            --login-muted: #6c757d;
            --login-border: #e1e5eb;
            --login-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --login-input-bg: #f8fafc;
            --login-error: #dc3545;
            --login-success: #28a745;
        }

        body.recovery-page {
            background: var(--login-bg);
            background-image: linear-gradient(135deg, rgba(0, 75, 141, 0.05) 0%, rgba(0, 166, 81, 0.05) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            margin: 0;
            font-family: var(--font-sans);
        }

        .recovery-container {
            width: 100%;
            max-width: 480px;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .recovery-card {
            background: var(--login-card-bg);
            border-radius: 12px;
            box-shadow: var(--login-shadow);
            overflow: hidden;
        }

        .recovery-header {
            background: var(--login-primary);
            padding: 2rem;
            text-align: center;
            color: white;
            position: relative;
        }

        .recovery-header::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 0;
            right: 0;
            height: 40px;
            background: var(--login-card-bg);
            border-radius: 50% 50% 0 0;
        }

        .recovery-logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
            background: white;
            border-radius: 50%;
            padding: 10px;
            margin-bottom: 1rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .recovery-title {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .recovery-subtitle {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .recovery-body {
            padding: 2rem;
        }

        .recovery-form .form-group {
            margin-bottom: 1.5rem;
        }

        .recovery-form .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--login-text);
        }

        .recovery-form .input-group {
            position: relative;
        }

        .recovery-form .input-icon {
            position: absolute;
            top: 50%;
            left: 1rem;
            transform: translateY(-50%);
            color: var(--login-muted);
        }

        .recovery-form .form-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid var(--login-border);
            border-radius: 8px;
            background: var(--login-input-bg);
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .recovery-form .form-input:focus {
            border-color: var(--login-primary);
            box-shadow: 0 0 0 3px rgba(0, 75, 141, 0.1);
            outline: none;
        }

        .recovery-form .btn-recovery {
            width: 100%;
            padding: 0.75rem;
            background: var(--login-primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .recovery-form .btn-recovery:hover {
            background: var(--cea-blue-dark, #003A6E);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 75, 141, 0.2);
        }

        .recovery-form .btn-recovery:active {
            transform: translateY(0);
        }

        .recovery-footer {
            text-align: center;
            padding: 1rem 2rem 2rem;
            color: var(--login-muted);
            font-size: 0.9rem;
        }

        .recovery-footer a {
            color: var(--login-primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .recovery-footer a:hover {
            color: var(--cea-blue-dark, #003A6E);
            text-decoration: underline;
        }

        .recovery-alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .recovery-alert.error {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--login-error);
            animation: shake 0.5s ease-in-out;
        }

        .recovery-alert.success {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--login-success);
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 1rem;
            transform: translateY(-50%);
            color: var(--login-muted);
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: var(--login-primary);
        }

        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.85rem;
        }

        .password-strength-meter {
            height: 4px;
            background-color: var(--login-border);
            border-radius: 2px;
            margin-top: 0.25rem;
            overflow: hidden;
        }

        .password-strength-meter-bar {
            height: 100%;
            border-radius: 2px;
            transition: width 0.3s ease, background-color 0.3s ease;
        }

        .password-strength-text {
            margin-top: 0.25rem;
            font-size: 0.75rem;
        }

        .strength-weak .password-strength-meter-bar {
            width: 25%;
            background-color: #dc3545;
        }

        .strength-medium .password-strength-meter-bar {
            width: 50%;
            background-color: #ffc107;
        }

        .strength-strong .password-strength-meter-bar {
            width: 75%;
            background-color: #17a2b8;
        }

        .strength-very-strong .password-strength-meter-bar {
            width: 100%;
            background-color: #28a745;
        }

        .back-to-login {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1rem;
            color: var(--login-muted);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .back-to-login:hover {
            color: var(--login-primary);
        }
    </style>
</head>
<body class="recovery-page">
    <div class="recovery-container">
        <div class="recovery-card">
            <div class="recovery-header">
                <img src="<?php echo SITE_URL; ?>/assets/img/logo_cea_negro-bg.png" alt="Logo CEA" class="recovery-logo">
                <h1 class="recovery-title">Recuperar Contraseña</h1>
                <p class="recovery-subtitle">
                    <?php echo ($step === 'request') ? 'Ingrese su correo electrónico para recibir instrucciones' : 'Ingrese su nueva contraseña'; ?>
                </p>
            </div>
            
            <div class="recovery-body">
                <?php if (!empty($error)): ?>
                    <div class="recovery-alert error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="recovery-alert success">
                        <i class="fas fa-check-circle"></i>
                        <span><?php echo $success; ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (empty($success)): ?>
                    <?php if ($step === 'request'): ?>
                        <form class="recovery-form" method="post" action="">
                            <div class="form-group">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <div class="input-group">
                                    <i class="fas fa-envelope input-icon"></i>
                                    <input type="email" id="email" name="email" class="form-input" placeholder="Ingrese su correo electrónico" required>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn-recovery">
                                <i class="fas fa-paper-plane"></i>
                                <span>Enviar Instrucciones</span>
                            </button>
                        </form>
                    <?php elseif ($step === 'reset'): ?>
                        <form class="recovery-form" method="post" action="">
                            <div class="form-group">
                                <label for="password" class="form-label">Nueva Contraseña</label>
                                <div class="input-group">
                                    <i class="fas fa-lock input-icon"></i>
                                    <input type="password" id="password" name="password" class="form-input" placeholder="Ingrese su nueva contraseña" required>
                                    <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                                </div>
                                <div class="password-strength">
                                    <div class="password-strength-meter">
                                        <div class="password-strength-meter-bar"></div>
                                    </div>
                                    <div class="password-strength-text">Fortaleza de la contraseña: <span id="strengthText">No ingresada</span></div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                                <div class="input-group">
                                    <i class="fas fa-lock input-icon"></i>
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-input" placeholder="Confirme su nueva contraseña" required>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn-recovery">
                                <i class="fas fa-save"></i>
                                <span>Cambiar Contraseña</span>
                            </button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <div class="recovery-footer">
                <a href="<?php echo SITE_URL; ?>/admin/login.php" class="back-to-login">
                    <i class="fas fa-arrow-left"></i>
                    <span>Volver a Iniciar Sesión</span>
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            
            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    this.classList.toggle('fa-eye');
                    this.classList.toggle('fa-eye-slash');
                });
            }
            
            // Password strength meter
            if (passwordInput) {
                const strengthMeter = document.querySelector('.password-strength-meter-bar');
                const strengthText = document.getElementById('strengthText');
                
                passwordInput.addEventListener('input', function() {
                    const password = this.value;
                    let strength = 0;
                    
                    // Length check
                    if (password.length >= 8) {
                        strength += 1;
                    }
                    
                    // Uppercase check
                    if (/[A-Z]/.test(password)) {
                        strength += 1;
                    }
                    
                    // Number check
                    if (/[0-9]/.test(password)) {
                        strength += 1;
                    }
                    
                    // Special character check
                    if (/[^A-Za-z0-9]/.test(password)) {
                        strength += 1;
                    }
                    
                    // Update strength meter
                    const strengthClass = ['strength-weak', 'strength-medium', 'strength-strong', 'strength-very-strong'][strength - 1] || '';
                    const strengthLabel = ['Débil', 'Media', 'Fuerte', 'Muy Fuerte'][strength - 1] || 'No ingresada';
                    
                    document.querySelector('.password-strength').className = 'password-strength ' + strengthClass;
                    strengthText.textContent = strengthLabel;
                });
            }
        });
    </script>
</body>
</html>