<?php
// Prevenir acceso directo al archivo
if (!defined('SITE_URL')) {
    define('SITE_URL', 'http://localhost/cea-website');
}

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'cea_website');

// Información del sitio
define('SITE_NAME', 'CEA - Centro de Estudios Avanzados');
define('SITE_DESCRIPTION', 'Centro de formación especializada en estudios avanzados');
define('SITE_EMAIL', 'info@cea.edu');
define('SITE_PHONE', '+34 912 345 678');
define('SITE_ADDRESS', 'Calle Principal 123, 28001 Madrid, España');

// Redes sociales
define('SOCIAL_FACEBOOK', 'https://facebook.com/cea');
define('SOCIAL_TWITTER', 'https://twitter.com/cea');
define('SOCIAL_INSTAGRAM', 'https://instagram.com/cea');
define('SOCIAL_LINKEDIN', 'https://linkedin.com/company/cea');

// Configuración de correo
define('MAIL_HOST', 'smtp.example.com');
define('MAIL_USER', 'no-reply@cea.edu');
define('MAIL_PASS', 'password');
define('MAIL_PORT', 587);

// Rutas del sistema
define('ROOT_PATH', dirname(dirname(__FILE__)));
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('ADMIN_PATH', ROOT_PATH . '/admin');

// Variables del tema
define('THEME_COLOR', '#004B8D'); // CEA Blue
define('THEME_COLOR_SECONDARY', '#00A651'); // CEA Green

// Configuración de sesión
session_start();

// Conexión a la base de datos
function getDB() {
    static $db = null;
    
    if ($db === null) {
        try {
            $db = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die('Error de conexión a la base de datos: ' . $e->getMessage());
        }
    }
    return $db;
}

// Variables CSS
$cssVariables = <<<CSS
:root {
    /* Colores */
    --white: #ffffff;
    --black: #000000;
    --gray-50: #f9fafb;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-300: #d1d5db;
    --gray-400: #9ca3af;
    --gray-500: #6b7280;
    --gray-600: #4b5563;
    --gray-700: #374151;
    --gray-800: #1f2937;
    --gray-900: #111827;
    
    /* Colores CEA */
    --cea-blue: #004B8D;
    --cea-blue-dark: #003A6E;
    --cea-blue-light: #0067C5;
    --cea-blue-lighter: #E6F0F9;
    --cea-green: #00A651;
    --cea-green-dark: #008542;
    --cea-green-light: #00C561;
    --cea-green-lighter: #E6F7EF;
    
    /* Colores de estado */
    --red: #dc2626;
    --red-dark: #b91c1c;
    --red-light: #fee2e2;
    --green: #16a34a;
    --green-light: #dcfce7;
    --yellow: #eab308;
    --yellow-light: #fef9c3;
    --purple: #8b5cf6;
    --purple-light: #f3e8ff;
    
    /* Espaciado */
    --space-xs: 0.25rem;
    --space-sm: 0.5rem;
    --space-md: 1rem;
    --space-lg: 1.5rem;
    --space-xl: 2rem;
    --space-xxl: 3rem;
    
    /* Bordes */
    --radius-sm: 0.25rem;
    --radius-md: 0.375rem;
    --radius-lg: 0.5rem;
    --radius-xl: 1rem;
    
    /* Sombras */
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    
    /* Tipografía */
    --font-sans: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
    
    /* Transiciones */
    --transition: 0.2s ease;
    --transition-slow: 0.3s ease;
}
CSS;
?>