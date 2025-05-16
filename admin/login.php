<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si ya está logueado
if (isset($_SESSION['user_id'])) {
    header("Location: " . SITE_URL . "/admin/dashboard.php");
    exit;
}

$error = '';

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Validar campos
    if (empty($username) || empty($password)) {
        $error = 'Por favor, complete todos los campos.';
    } else {
        try {
            // Conexión directa a la base de datos
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
            
            // Consulta directa
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = :username");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();

            //Generar contraseña hash
            // echo password_hash("1234", PASSWORD_BCRYPT);
            // exit;
            if ($user === false) {
                $error = 'El usuario no existe en la base de datos.';
            } else {
                if (password_verify($password, $user['password'])) {
                    // Iniciar sesión y redirigir
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['nombre'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['rol'];
                
                    header("Location: " . SITE_URL . "/admin/dashboard.php");
                    exit;
                } else {
                    $error = 'Contraseña incorrecta.';
                }
            }
                    } catch (PDOException $e) {
                        $error = 'Error de conexión: ' . $e->getMessage();
                    }
                }
            }

// Título de la página
$pageTitle = 'Iniciar Sesión';
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
            --login-card-dark:rgb(180, 138, 0);
            --login-text: #333333;
            --login-muted: #6c757d;
            --login-border: #e1e5eb;
            --login-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --login-input-bg: #f8fafc;
            --login-error: #dc3545;
        }

        body.login-page {
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

        .login-container {
            width: 100%;
            max-width: 420px;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-card {
            background: var(--login-card-bg);
            border-radius: 12px;
            box-shadow: var(--login-shadow);
            overflow: hidden;
        }

        .login-header {
            background: var(--login-primary);
            padding: 2rem;
            text-align: center;
            color: white;
            position: relative;
        }

        .login-header::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            right: 0;
            height: 10px;
            background: var(--login-card-dark);
            border-radius: 50% 50% 0 0;
        }

        .login-header::before {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            right: 0;
            height: 40px;
            background: var(--log);
            border-radius: 50% 50% 0 0;
        }

        .login-logo {
            width: 100px;
            height: 100px;
            object-fit: contain;
            background: white;
            border-radius: 50%;
            padding: 10px;
            margin-bottom: 1rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .login-title {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--login-card-bg);
        }

        .login-subtitle {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .login-body {
            padding: 2rem;
        }

        .login-form .form-group {
            margin-bottom: 1.5rem;
        }

        .login-form .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--login-text);
        }

        .login-form .input-group {
            position: relative;
        }

        .login-form .input-icon {
            position: absolute;
            top: 50%;
            left: 1rem;
            transform: translateY(-50%);
            color: var(--login-muted);
        }

        .login-form .form-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid var(--login-border);
            border-radius: 8px;
            background: var(--login-input-bg);
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .login-form .form-input:focus {
            border-color: var(--login-primary);
            box-shadow: 0 0 0 3px rgba(0, 75, 141, 0.1);
            outline: none;
        }

        .login-form .btn-login {
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

        .login-form .btn-login:hover {
            background: var(--cea-blue-dark, #003A6E);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 75, 141, 0.2);
        }

        .login-form .btn-login:active {
            transform: translateY(0);
        }

        .login-footer {
            text-align: center;
            padding: 1rem 2rem 2rem;
            color: var(--login-muted);
            font-size: 0.9rem;
        }

        .login-footer a {
            color: var(--login-primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .login-footer a:hover {
            color: var(--cea-blue-dark, #003A6E);
            text-decoration: underline;
        }

        .login-alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--login-error);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            animation: shake 0.5s ease-in-out;
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

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .remember-me input {
            width: 16px;
            height: 16px;
        }

        .remember-me label {
            color: var(--login-text);
            font-size: 0.9rem;
        }

        .back-to-site {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
            color: var(--login-muted);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .back-to-site:hover {
            color: var(--login-primary);
        }
        
        .emergency-access {
            margin-top: 1rem;
            padding: 1rem;
            background-color: rgba(255, 193, 7, 0.1);
            border-radius: 8px;
            font-size: 0.9rem;
        }
        
        .emergency-access h4 {
            color: #856404;
            margin-bottom: 0.5rem;
        }
        
        .emergency-access p {
            color: #856404;
            margin-bottom: 0.5rem;
        }
        
        .emergency-credentials {
            background-color: rgba(255, 255, 255, 0.7);
            padding: 0.5rem;
            border-radius: 4px;
            font-family: monospace;
        }
    </style>
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="<?php echo SITE_URL; ?>/assets/img/logo_cea_negro-bg.png" alt="Logo CEA" class="login-logo">
                <h1 class="login-title">Panel de Administración</h1>
                <p class="login-subtitle">Ingrese sus credenciales para acceder</p>
            </div>
            
            <div class="login-body">
                <?php if (!empty($error)): ?>
                    <div class="login-alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>
                
                <form class="login-form" method="post" action="">
                    <div class="form-group">
                        <label for="username" class="form-label">Usuario</label>
                        <div class="input-group">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" id="username" name="username" class="form-input" placeholder="Ingrese su usuario" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="password" name="password" class="form-input" placeholder="Ingrese su contraseña" required>
                            <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                        </div>
                    </div>
                    
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Recordarme</label>
                    </div>
                    
                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Iniciar Sesión</span>
                    </button>
                </form>
                
                <!-- <div class="emergency-access">
                    <h4>Acceso de emergencia</h4>
                    <p>Si no puedes acceder con tus credenciales habituales, usa estas credenciales temporales:</p>
                    <div class="emergency-credentials">
                        <strong>Usuario:</strong> admin<br>
                        <strong>Contraseña:</strong> admin123
                    </div>
                </div> -->
            </div>
            
            <div class="login-footer">
                <p>¿Olvidó su contraseña? <a href="<?php echo SITE_URL; ?>/admin/recuperar-password.php">Recuperar</a></p>
                <a href="<?php echo SITE_URL; ?>" class="back-to-site">
                    <i class="fas fa-arrow-left"></i>
                    <span>Volver al sitio</span>
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        });
    </script>
</body>
</html>