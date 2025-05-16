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
$pageTitle = 'Configuración del Sistema';

// Crear la tabla de configuración si no existe
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
    
    // Verificar si la tabla configuracion existe
    $stmt = $db->query("SHOW TABLES LIKE 'configuracion'");
    if ($stmt->rowCount() == 0) {
        // La tabla no existe, crearla
        $db->exec("
            CREATE TABLE configuracion (
                id INT AUTO_INCREMENT PRIMARY KEY,
                clave VARCHAR(100) NOT NULL UNIQUE,
                valor TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Insertar configuraciones predeterminadas
        $defaultConfigs = [
            ['sitio_nombre', 'Centro de Estudios Avanzados'],
            ['sitio_descripcion', 'Centro de investigación y formación de alto nivel'],
            ['sitio_email', 'contacto@cea.edu.mx'],
            ['sitio_telefono', '+52 (55) 1234-5678'],
            ['sitio_direccion', 'Av. Universidad 3000, Ciudad Universitaria, Coyoacán, 04510 Ciudad de México, CDMX'],
            ['tema_color_primario', '#4299e1'],
            ['tema_color_secundario', '#48bb78'],
            ['tema_fuente_principal', 'Arial, sans-serif'],
            ['tema_mostrar_slider', '1'],
            ['tema_items_por_pagina', '10'],
            ['sistema_modo_mantenimiento', '0'],
            ['sistema_mensaje_mantenimiento', 'Estamos realizando tareas de mantenimiento. Por favor, vuelva más tarde.'],
            ['sistema_cache_habilitado', '0'],
            ['sistema_tiempo_cache', '60'],
            ['sistema_registros_por_pagina', '20']
        ];
        
        $stmt = $db->prepare("INSERT INTO configuracion (clave, valor) VALUES (?, ?)");
        foreach ($defaultConfigs as $config) {
            $stmt->execute($config);
        }
    }
    
    // Obtener configuraciones actuales
    $stmt = $db->query("SELECT * FROM configuracion");
    $configuraciones = [];
    
    while ($row = $stmt->fetch()) {
        $configuraciones[$row['clave']] = $row['valor'];
    }
    
} catch (PDOException $e) {
    $error = "Error al configurar la base de datos: " . $e->getMessage();
}

// Procesar formulario
$mensaje = '';
$tipoMensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Configuración general
        if (isset($_POST['general'])) {
            $sitio_nombre = trim($_POST['sitio_nombre']);
            $sitio_descripcion = trim($_POST['sitio_descripcion']);
            $sitio_email = trim($_POST['sitio_email']);
            $sitio_telefono = trim($_POST['sitio_telefono']);
            $sitio_direccion = trim($_POST['sitio_direccion']);
            
            // Validar datos
            if (empty($sitio_nombre)) {
                $mensaje = "El nombre del sitio no puede estar vacío.";
                $tipoMensaje = "danger";
            } else {
                // Actualizar configuraciones
                $configs = [
                    'sitio_nombre' => $sitio_nombre,
                    'sitio_descripcion' => $sitio_descripcion,
                    'sitio_email' => $sitio_email,
                    'sitio_telefono' => $sitio_telefono,
                    'sitio_direccion' => $sitio_direccion
                ];
                
                foreach ($configs as $clave => $valor) {
                    $stmt = $db->prepare("
                        INSERT INTO configuracion (clave, valor) 
                        VALUES (:clave, :valor)
                        ON DUPLICATE KEY UPDATE valor = :valor
                    ");
                    $stmt->execute(['clave' => $clave, 'valor' => $valor]);
                }
                
                $mensaje = "Configuración general actualizada correctamente.";
                $tipoMensaje = "success";
                
                // Actualizar configuraciones en memoria
                $configuraciones = array_merge($configuraciones, $configs);
            }
        }
        
        // Configuración de apariencia
        if (isset($_POST['apariencia'])) {
            $tema_color_primario = trim($_POST['tema_color_primario']);
            $tema_color_secundario = trim($_POST['tema_color_secundario']);
            $tema_fuente_principal = trim($_POST['tema_fuente_principal']);
            $tema_mostrar_slider = isset($_POST['tema_mostrar_slider']) ? '1' : '0';
            $tema_items_por_pagina = trim($_POST['tema_items_por_pagina']);
            
            // Validar datos
            if (!is_numeric($tema_items_por_pagina) || $tema_items_por_pagina < 1) {
                $mensaje = "El número de items por página debe ser un número positivo.";
                $tipoMensaje = "danger";
            } else {
                // Actualizar configuraciones
                $configs = [
                    'tema_color_primario' => $tema_color_primario,
                    'tema_color_secundario' => $tema_color_secundario,
                    'tema_fuente_principal' => $tema_fuente_principal,
                    'tema_mostrar_slider' => $tema_mostrar_slider,
                    'tema_items_por_pagina' => $tema_items_por_pagina
                ];
                
                foreach ($configs as $clave => $valor) {
                    $stmt = $db->prepare("
                        INSERT INTO configuracion (clave, valor) 
                        VALUES (:clave, :valor)
                        ON DUPLICATE KEY UPDATE valor = :valor
                    ");
                    $stmt->execute(['clave' => $clave, 'valor' => $valor]);
                }
                
                // Procesar logo si se ha subido
                if (isset($_FILES['tema_logo']) && $_FILES['tema_logo']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = '../uploads/sistema/';
                    
                    // Crear directorio si no existe
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    $fileName = 'logo.' . pathinfo($_FILES['tema_logo']['name'], PATHINFO_EXTENSION);
                    $uploadFile = $uploadDir . $fileName;
                    
                    // Verificar tipo de archivo
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
                    if (in_array($_FILES['tema_logo']['type'], $allowedTypes)) {
                        if (move_uploaded_file($_FILES['tema_logo']['tmp_name'], $uploadFile)) {
                            // Guardar ruta del logo en la configuración
                            $stmt = $db->prepare("
                                INSERT INTO configuracion (clave, valor) 
                                VALUES ('tema_logo', :valor)
                                ON DUPLICATE KEY UPDATE valor = :valor
                            ");
                            $stmt->execute(['valor' => $fileName]);
                            
                            // Actualizar en memoria
                            $configuraciones['tema_logo'] = $fileName;
                        }
                    }
                }
                
                $mensaje = "Configuración de apariencia actualizada correctamente.";
                $tipoMensaje = "success";
                
                // Actualizar configuraciones en memoria
                $configuraciones = array_merge($configuraciones, $configs);
                
                // Generar archivo CSS con variables personalizadas
                $cssContent = ":root {\n";
                $cssContent .= "  --primary: " . $tema_color_primario . ";\n";
                $cssContent .= "  --secondary: " . $tema_color_secundario . ";\n";
                $cssContent .= "  --font-family: " . $tema_fuente_principal . ";\n";
                $cssContent .= "}\n";
                
                file_put_contents('../assets/css/custom-variables.css', $cssContent);
            }
        }
        
        // Configuración de sistema
        if (isset($_POST['sistema'])) {
            $sistema_modo_mantenimiento = isset($_POST['sistema_modo_mantenimiento']) ? '1' : '0';
            $sistema_mensaje_mantenimiento = trim($_POST['sistema_mensaje_mantenimiento']);
            $sistema_cache_habilitado = isset($_POST['sistema_cache_habilitado']) ? '1' : '0';
            $sistema_tiempo_cache = trim($_POST['sistema_tiempo_cache']);
            $sistema_registros_por_pagina = trim($_POST['sistema_registros_por_pagina']);
            
            // Validar datos
            if (!is_numeric($sistema_tiempo_cache) || $sistema_tiempo_cache < 0) {
                $mensaje = "El tiempo de caché debe ser un número positivo.";
                $tipoMensaje = "danger";
            } elseif (!is_numeric($sistema_registros_por_pagina) || $sistema_registros_por_pagina < 1) {
                $mensaje = "El número de registros por página debe ser un número positivo.";
                $tipoMensaje = "danger";
            } else {
                // Actualizar configuraciones
                $configs = [
                    'sistema_modo_mantenimiento' => $sistema_modo_mantenimiento,
                    'sistema_mensaje_mantenimiento' => $sistema_mensaje_mantenimiento,
                    'sistema_cache_habilitado' => $sistema_cache_habilitado,
                    'sistema_tiempo_cache' => $sistema_tiempo_cache,
                    'sistema_registros_por_pagina' => $sistema_registros_por_pagina
                ];
                
                foreach ($configs as $clave => $valor) {
                    $stmt = $db->prepare("
                        INSERT INTO configuracion (clave, valor) 
                        VALUES (:clave, :valor)
                        ON DUPLICATE KEY UPDATE valor = :valor
                    ");
                    $stmt->execute(['clave' => $clave, 'valor' => $valor]);
                }
                
                $mensaje = "Configuración del sistema actualizada correctamente.";
                $tipoMensaje = "success";
                
                // Actualizar configuraciones en memoria
                $configuraciones = array_merge($configuraciones, $configs);
            }
        }
        
        // Configuración de SEO
        if (isset($_POST['seo'])) {
            $seo_meta_keywords = trim($_POST['seo_meta_keywords']);
            $seo_meta_author = trim($_POST['seo_meta_author']);
            $seo_google_analytics = trim($_POST['seo_google_analytics']);
            $seo_robots_txt = trim($_POST['seo_robots_txt']);
            
            // Actualizar configuraciones
            $configs = [
                'seo_meta_keywords' => $seo_meta_keywords,
                'seo_meta_author' => $seo_meta_author,
                'seo_google_analytics' => $seo_google_analytics
            ];
            
            foreach ($configs as $clave => $valor) {
                $stmt = $db->prepare("
                    INSERT INTO configuracion (clave, valor) 
                    VALUES (:clave, :valor)
                    ON DUPLICATE KEY UPDATE valor = :valor
                ");
                $stmt->execute(['clave' => $clave, 'valor' => $valor]);
            }
            
            // Guardar robots.txt
            if (!empty($seo_robots_txt)) {
                file_put_contents('../robots.txt', $seo_robots_txt);
            }
            
            $mensaje = "Configuración de SEO actualizada correctamente.";
            $tipoMensaje = "success";
            
            // Actualizar configuraciones en memoria
            $configuraciones = array_merge($configuraciones, $configs);
        }
        
    } catch (PDOException $e) {
        $mensaje = "Error al guardar la configuración: " . $e->getMessage();
        $tipoMensaje = "danger";
    }
}

// Incluir header
include 'includes/header.php';
?>

<style>
/* Estilos para la página de configuración */
:root {
    /* Colores principales */
    --primary: #004b8d;
    --primary-dark: #003a6e;
    --primary-light: #e6f0f9;
    --secondary: #6c757d;
    --success: #28a745;
    --danger: #dc3545;
    --warning: #ffc107;
    --info: #17a2b8;
    
    /* Escala de grises */
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
    
    /* Otros */
    --border-radius: 0.375rem;
    --box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    --transition-speed: 0.3s;
}

/* Estilos del encabezado de contenido */
.content-header {
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--gray-200);
}

.content-header-title h1 {
    font-size: 1.75rem;
    font-weight: 600;
    color: var(--gray-800);
    margin-bottom: 0.5rem;
}

.content-header-title p {
    color: var(--gray-600);
    margin: 0;
}

/* Estilos para las tarjetas */
.card {
    background-color: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    margin-bottom: 1.5rem;
    border: 1px solid var(--gray-200);
}

.card-header {
    padding: 1rem 1.5rem;
    background-color: var(--gray-50);
    border-bottom: 1px solid var(--gray-200);
}

.card-body {
    padding: 1.5rem;
}

/* Estilos para las pestañas */
.nav-tabs {
    border-bottom: none;
}

.nav-tabs .nav-item {
    margin-bottom: -1px;
}

.nav-tabs .nav-link {
    border: 1px solid transparent;
    border-top-left-radius: 0.25rem;
    border-top-right-radius: 0.25rem;
    color: var(--gray-600);
    padding: 0.75rem 1.25rem;
    font-weight: 500;
    transition: all var(--transition-speed) ease;
}

.nav-tabs .nav-link:hover {
    color: var(--primary);
    border-color: transparent;
}

.nav-tabs .nav-link.active {
    color: var(--primary);
    background-color: white;
    border-color: var(--gray-200) var(--gray-200) white;
}

.nav-tabs .nav-link i {
    margin-right: 0.5rem;
}

/* Estilos para los formularios */
.form-group {
    margin-bottom: 1.25rem;
}

.form-label, label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--gray-700);
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
    border-radius: var(--border-radius);
    transition: border-color var(--transition-speed) ease-in-out, box-shadow var(--transition-speed) ease-in-out;
}

.form-control:focus {
    color: var(--gray-700);
    background-color: white;
    border-color: var(--primary-light);
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(0, 75, 141, 0.25);
}

.form-text {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.75rem;
}

.text-muted {
    color: var(--gray-600) !important;
}

.form-row {
    display: flex;
    flex-wrap: wrap;
    margin-right: -0.5rem;
    margin-left: -0.5rem;
}

.form-row > .col,
.form-row > [class*="col-"] {
    padding-right: 0.5rem;
    padding-left: 0.5rem;
}

.col-md-6 {
    flex: 0 0 50%;
    max-width: 50%;
}

/* Estilos para los botones */
.btn {
    display: inline-block;
    font-weight: 500;
    text-align: center;
    white-space: nowrap;
    vertical-align: middle;
    user-select: none;
    border: 1px solid transparent;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    line-height: 1.5;
    border-radius: var(--border-radius);
    transition: color var(--transition-speed) ease-in-out, background-color var(--transition-speed) ease-in-out, border-color var(--transition-speed) ease-in-out, box-shadow var(--transition-speed) ease-in-out;
    cursor: pointer;
}

.btn-primary {
    color: white;
    background-color: var(--primary);
    border-color: var(--primary);
}

.btn-primary:hover {
    color: white;
    background-color: var(--primary-dark);
    border-color: var(--primary-dark);
}

.btn i {
    margin-right: 0.5rem;
}

/* Estilos para alertas */
.alert {
    position: relative;
    padding: 1rem;
    margin-bottom: 1rem;
    border: 1px solid transparent;
    border-radius: var(--border-radius);
}

.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}

.alert-danger {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

.alert-dismissible {
    padding-right: 4rem;
}

.alert-dismissible .close {
    position: absolute;
    top: 0;
    right: 0;
    padding: 1rem;
    color: inherit;
    background-color: transparent;
    border: 0;
    cursor: pointer;
}

/* Estilos para inputs de tipo color */
input[type="color"] {
    height: 38px;
    padding: 0.25rem;
    cursor: pointer;
}

.input-group {
    position: relative;
    display: flex;
    flex-wrap: wrap;
    align-items: stretch;
    width: 100%;
}

.input-group-prepend {
    display: flex;
    margin-right: -1px;
}

.input-group-text {
    display: flex;
    align-items: center;
    padding: 0.5rem 0.75rem;
    margin-bottom: 0;
    font-size: 0.875rem;
    font-weight: 400;
    line-height: 1.5;
    color: var(--gray-700);
    text-align: center;
    white-space: nowrap;
    background-color: var(--gray-100);
    border: 1px solid var(--gray-300);
    border-radius: var(--border-radius) 0 0 var(--border-radius);
}

/* Estilos para custom file input */
.custom-file {
    position: relative;
    display: inline-block;
    width: 100%;
    height: calc(2.25rem + 2px);
    margin-bottom: 0;
}

.custom-file-input {
    position: relative;
    z-index: 2;
    width: 100%;
    height: calc(2.25rem + 2px);
    margin: 0;
    opacity: 0;
    cursor: pointer;
}

.custom-file-label {
    position: absolute;
    top: 0;
    right: 0;
    left: 0;
    z-index: 1;
    height: calc(2.25rem + 2px);
    padding: 0.5rem 0.75rem;
    line-height: 1.5;
    color: var(--gray-700);
    background-color: white;
    border: 1px solid var(--gray-300);
    border-radius: var(--border-radius);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.custom-file-label::after {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    z-index: 3;
    display: block;
    height: calc(2.25rem + 2px);
    padding: 0.5rem 0.75rem;
    line-height: 1.5;
    color: var(--gray-700);
    content: "Examinar";
    background-color: var(--gray-100);
    border-left: 1px solid var(--gray-300);
    border-radius: 0 var(--border-radius) var(--border-radius) 0;
}

/* Estilos para custom switch */
.custom-control {
    position: relative;
    display: block;
    min-height: 1.5rem;
    padding-left: 2.5rem;
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
    cursor: pointer;
}

.custom-control-label::before {
    position: absolute;
    top: 0.125rem;
    left: -2.5rem;
    display: block;
    width: 2rem;
    height: 1.25rem;
    pointer-events: none;
    content: "";
    background-color: var(--gray-300);
    border-radius: 1rem;
    transition: background-color var(--transition-speed) ease-in-out, border-color var(--transition-speed) ease-in-out, box-shadow var(--transition-speed) ease-in-out;
}

.custom-control-label::after {
    position: absolute;
    top: 0.25rem;
    left: -2.375rem;
    display: block;
    width: 1rem;
    height: 1rem;
    content: "";
    background-color: white;
    border-radius: 1rem;
    transition: transform var(--transition-speed) ease-in-out, background-color var(--transition-speed) ease-in-out;
}

.custom-switch .custom-control-input:checked ~ .custom-control-label::before {
    background-color: var(--primary);
}

.custom-switch .custom-control-input:checked ~ .custom-control-label::after {
    transform: translateX(0.75rem);
}

/* Estilos para imágenes en miniatura */
.img-thumbnail {
    padding: 0.25rem;
    background-color: white;
    border: 1px solid var(--gray-300);
    border-radius: var(--border-radius);
    max-width: 100%;
    height: auto;
}

/* Estilos para el contenido de las pestañas */
.tab-content > .tab-pane {
    display: none;
}

.tab-content > .active {
    display: block;
}

.fade {
    transition: opacity var(--transition-speed) linear;
}

.fade:not(.show) {
    opacity: 0;
}

.show {
    opacity: 1;
}

/* Responsive */
@media (max-width: 768px) {
    .col-md-6 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    .form-row {
        flex-direction: column;
    }
}
</style>

<div class="content-header">
    <div class="content-header-title">
        <h1>Configuración del Sistema</h1>
        <p>Personaliza la configuración general del sitio web.</p>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <?php echo $error; ?>
    </div>
<?php endif; ?>

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
        <ul class="nav nav-tabs card-header-tabs" id="configTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link <?php echo !isset($_GET['tab']) || $_GET['tab'] === 'general' ? 'active' : ''; ?>" id="general-tab" data-toggle="tab" href="#general" role="tab" aria-controls="general" aria-selected="true">
                    <i class="fas fa-cog"></i> General
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo isset($_GET['tab']) && $_GET['tab'] === 'apariencia' ? 'active' : ''; ?>" id="apariencia-tab" data-toggle="tab" href="#apariencia" role="tab" aria-controls="apariencia" aria-selected="false">
                    <i class="fas fa-paint-brush"></i> Apariencia
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo isset($_GET['tab']) && $_GET['tab'] === 'sistema' ? 'active' : ''; ?>" id="sistema-tab" data-toggle="tab" href="#sistema" role="tab" aria-controls="sistema" aria-selected="false">
                    <i class="fas fa-server"></i> Sistema
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo isset($_GET['tab']) && $_GET['tab'] === 'seo' ? 'active' : ''; ?>" id="seo-tab" data-toggle="tab" href="#seo" role="tab" aria-controls="seo" aria-selected="false">
                    <i class="fas fa-search"></i> SEO
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="configTabsContent">
            <!-- Configuración General -->
            <div class="tab-pane fade <?php echo !isset($_GET['tab']) || $_GET['tab'] === 'general' ? 'show active' : ''; ?>" id="general" role="tabpanel" aria-labelledby="general-tab">
                <form action="?tab=general" method="post">
                    <input type="hidden" name="general" value="1">
                    
                    <div class="form-group">
                        <label for="sitio_nombre">Nombre del Sitio <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="sitio_nombre" name="sitio_nombre" value="<?php echo htmlspecialchars($configuraciones['sitio_nombre'] ?? 'Centro de Estudios Avanzados'); ?>" required>
                        <small class="form-text text-muted">Este nombre aparecerá en el título de las páginas y en el encabezado del sitio.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="sitio_descripcion">Descripción del Sitio</label>
                        <textarea class="form-control" id="sitio_descripcion" name="sitio_descripcion" rows="3"><?php echo htmlspecialchars($configuraciones['sitio_descripcion'] ?? ''); ?></textarea>
                        <small class="form-text text-muted">Una breve descripción del sitio que aparecerá en los resultados de búsqueda.</small>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="sitio_email">Email de Contacto</label>
                            <input type="email" class="form-control" id="sitio_email" name="sitio_email" value="<?php echo htmlspecialchars($configuraciones['sitio_email'] ?? ''); ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="sitio_telefono">Teléfono de Contacto</label>
                            <input type="text" class="form-control" id="sitio_telefono" name="sitio_telefono" value="<?php echo htmlspecialchars($configuraciones['sitio_telefono'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="sitio_direccion">Dirección</label>
                        <textarea class="form-control" id="sitio_direccion" name="sitio_direccion" rows="2"><?php echo htmlspecialchars($configuraciones['sitio_direccion'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Configuración General
                    </button>
                </form>
            </div>
            
            <!-- Configuración de Apariencia -->
            <div class="tab-pane fade <?php echo isset($_GET['tab']) && $_GET['tab'] === 'apariencia' ? 'show active' : ''; ?>" id="apariencia" role="tabpanel" aria-labelledby="apariencia-tab">
                <form action="?tab=apariencia" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="apariencia" value="1">
                    
                    <div class="form-group">
                        <label for="tema_logo">Logo del Sitio</label>
                        <?php if (!empty($configuraciones['tema_logo'])): ?>
                            <div class="mb-2">
                                <img src="<?php echo SITE_URL; ?>/uploads/sistema/<?php echo $configuraciones['tema_logo']; ?>" alt="Logo actual" class="img-thumbnail" style="max-height: 100px;">
                            </div>
                        <?php endif; ?>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="tema_logo" name="tema_logo" accept="image/jpeg,image/png,image/gif,image/svg+xml">
                            <label class="custom-file-label" for="tema_logo">Seleccionar nuevo logo</label>
                        </div>
                        <small class="form-text text-muted">Formatos permitidos: JPG, PNG, GIF, SVG. Tamaño recomendado: 200x60px.</small>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="tema_color_primario">Color Primario</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fas fa-palette"></i>
                                    </span>
                                </div>
                                <input type="color" class="form-control" id="tema_color_primario" name="tema_color_primario" value="<?php echo htmlspecialchars($configuraciones['tema_color_primario'] ?? '#4299e1'); ?>">
                            </div>
                            <small class="form-text text-muted">Este color se utilizará para elementos principales como botones y enlaces.</small>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="tema_color_secundario">Color Secundario</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fas fa-palette"></i>
                                    </span>
                                </div>
                                <input type="color" class="form-control" id="tema_color_secundario" name="tema_color_secundario" value="<?php echo htmlspecialchars($configuraciones['tema_color_secundario'] ?? '#48bb78'); ?>">
                            </div>
                            <small class="form-text text-muted">Este color se utilizará para elementos secundarios y acentos.</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="tema_fuente_principal">Fuente Principal</label>
                        <select class="form-control" id="tema_fuente_principal" name="tema_fuente_principal">
                            <option value="Arial, sans-serif" <?php echo ($configuraciones['tema_fuente_principal'] ?? '') === 'Arial, sans-serif' ? 'selected' : ''; ?>>Arial</option>
                            <option value="'Helvetica Neue', Helvetica, Arial, sans-serif" <?php echo ($configuraciones['tema_fuente_principal'] ?? '') === "'Helvetica Neue', Helvetica, Arial, sans-serif" ? 'selected' : ''; ?>>Helvetica</option>
                            <option value="Georgia, serif" <?php echo ($configuraciones['tema_fuente_principal'] ?? '') === 'Georgia, serif' ? 'selected' : ''; ?>>Georgia</option>
                            <option value="'Times New Roman', Times, serif" <?php echo ($configuraciones['tema_fuente_principal'] ?? '') === "'Times New Roman', Times, serif" ? 'selected' : ''; ?>>Times New Roman</option>
                            <option value="Verdana, Geneva, sans-serif" <?php echo ($configuraciones['tema_fuente_principal'] ?? '') === 'Verdana, Geneva, sans-serif' ? 'selected' : ''; ?>>Verdana</option>
                            <option value="'Inter', sans-serif" <?php echo ($configuraciones['tema_fuente_principal'] ?? '') === "'Inter', sans-serif" ? 'selected' : ''; ?>>Inter</option>
                        </select>
                        <small class="form-text text-muted">Esta fuente se utilizará en todo el sitio web.</small>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="tema_mostrar_slider" name="tema_mostrar_slider" <?php echo ($configuraciones['tema_mostrar_slider'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                <label class="custom-control-label" for="tema_mostrar_slider">Mostrar Slider en la Página Principal</label>
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="tema_items_por_pagina">Items por Página</label>
                            <input type="number" class="form-control" id="tema_items_por_pagina" name="tema_items_por_pagina" value="<?php echo htmlspecialchars($configuraciones['tema_items_por_pagina'] ?? '10'); ?>" min="1">
                            <small class="form-text text-muted">Número de items a mostrar en listados paginados.</small>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Configuración de Apariencia
                    </button>
                </form>
            </div>
            
            <!-- Configuración del Sistema -->
            <div class="tab-pane fade <?php echo isset($_GET['tab']) && $_GET['tab'] === 'sistema' ? 'show active' : ''; ?>" id="sistema" role="tabpanel" aria-labelledby="sistema-tab">
                <form action="?tab=sistema" method="post">
                    <input type="hidden" name="sistema" value="1">
                    
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="sistema_modo_mantenimiento" name="sistema_modo_mantenimiento" <?php echo ($configuraciones['sistema_modo_mantenimiento'] ?? '0') === '1' ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="sistema_modo_mantenimiento">Modo Mantenimiento</label>
                        </div>
                        <small class="form-text text-muted">Cuando está activado, el sitio mostrará un mensaje de mantenimiento a los visitantes.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="sistema_mensaje_mantenimiento">Mensaje de Mantenimiento</label>
                        <textarea class="form-control" id="sistema_mensaje_mantenimiento" name="sistema_mensaje_mantenimiento" rows="3"><?php echo htmlspecialchars($configuraciones['sistema_mensaje_mantenimiento'] ?? 'Estamos realizando tareas de mantenimiento. Por favor, vuelva más tarde.'); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="sistema_cache_habilitado" name="sistema_cache_habilitado" <?php echo ($configuraciones['sistema_cache_habilitado'] ?? '0') === '1' ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="sistema_cache_habilitado">Habilitar Caché</label>
                        </div>
                        <small class="form-text text-muted">Activa el sistema de caché para mejorar el rendimiento.</small>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="sistema_tiempo_cache">Tiempo de Caché (minutos)</label>
                            <input type="number" class="form-control" id="sistema_tiempo_cache" name="sistema_tiempo_cache" value="<?php echo htmlspecialchars($configuraciones['sistema_tiempo_cache'] ?? '60'); ?>" min="0">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="sistema_registros_por_pagina">Registros por Página (Admin)</label>
                            <input type="number" class="form-control" id="sistema_registros_por_pagina" name="sistema_registros_por_pagina" value="<?php echo htmlspecialchars($configuraciones['sistema_registros_por_pagina'] ?? '20'); ?>" min="1">
                            <small class="form-text text-muted">Número de registros a mostrar en las tablas del panel de administración.</small>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Configuración del Sistema
                    </button>
                </form>
            </div>
            
            <!-- Configuración de SEO -->
            <div class="tab-pane fade <?php echo isset($_GET['tab']) && $_GET['tab'] === 'seo' ? 'show active' : ''; ?>" id="seo" role="tabpanel" aria-labelledby="seo-tab">
                <form action="?tab=seo" method="post">
                    <input type="hidden" name="seo" value="1">
                    
                    <div class="form-group">
                        <label for="seo_meta_keywords">Meta Keywords</label>
                        <input type="text" class="form-control" id="seo_meta_keywords" name="seo_meta_keywords" value="<?php echo htmlspecialchars($configuraciones['seo_meta_keywords'] ?? ''); ?>">
                        <small class="form-text text-muted">Palabras clave separadas por comas que describen el contenido del sitio.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="seo_meta_author">Meta Author</label>
                        <input type="text" class="form-control" id="seo_meta_author" name="seo_meta_author" value="<?php echo htmlspecialchars($configuraciones['seo_meta_author'] ?? ''); ?>">
                        <small class="form-text text-muted">Nombre del autor o organización responsable del sitio.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="seo_google_analytics">Código de Google Analytics</label>
                        <textarea class="form-control" id="seo_google_analytics" name="seo_google_analytics" rows="5"><?php echo htmlspecialchars($configuraciones['seo_google_analytics'] ?? ''); ?></textarea>
                        <small class="form-text text-muted">Código de seguimiento de Google Analytics (incluir las etiquetas &lt;script&gt;).</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="seo_robots_txt">Contenido del archivo robots.txt</label>
                        <textarea class="form-control" id="seo_robots_txt" name="seo_robots_txt" rows="5"><?php echo file_exists('../robots.txt') ? htmlspecialchars(file_get_contents('../robots.txt')) : "User-agent: *\nAllow: /"; ?></textarea>
                        <small class="form-text text-muted">Contenido del archivo robots.txt que controla el acceso de los robots de búsqueda.</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Configuración de SEO
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mostrar nombre del archivo seleccionado para el logo
    document.getElementById('tema_logo').addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            var fileName = e.target.files[0].name;
            var label = document.querySelector('label[for="tema_logo"]');
            label.textContent = fileName;

            // Validar tamaño (1MB máximo)
            if (e.target.files[0].size > 1 * 1024 * 1024) {
                alert('El logo es demasiado grande. El tamaño máximo permitido es 1MB.');
                this.value = '';
                label.textContent = 'Seleccionar nuevo logo';
            }
        }
    });

    // Manejo de pestañas con hash
    const tabLinks = document.querySelectorAll('.nav-link');
    const tabPanes = document.querySelectorAll('.tab-pane');

    function activateTabByHash(hash) {
        tabLinks.forEach(link => {
            const target = link.getAttribute('href');
            if (target === hash) {
                link.classList.add('active');
                link.setAttribute('aria-selected', 'true');
            } else {
                link.classList.remove('active');
                link.setAttribute('aria-selected', 'false');
            }
        });

        tabPanes.forEach(pane => {
            if ('#' + pane.id === hash) {
                pane.classList.add('show', 'active');
            } else {
                pane.classList.remove('show', 'active');
            }
        });
    }

    // Al hacer clic en las pestañas, cambiar el hash
    tabLinks.forEach(function(tabLink) {
        tabLink.addEventListener('click', function(e) {
            // No usar preventDefault, se actualiza el hash
        });
    });

    // Activar pestaña correspondiente al hash actual
    const currentHash = window.location.hash || tabLinks[0].getAttribute('href');
    activateTabByHash(currentHash);

    // Cambiar la pestaña cuando cambia el hash manualmente
    window.addEventListener('hashchange', function() {
        activateTabByHash(window.location.hash);
    });

    // Cerrar alertas
    const closeButtons = document.querySelectorAll('.alert .close');
    closeButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const alert = this.closest('.alert');
            alert.classList.remove('show');
            setTimeout(function() {
                alert.style.display = 'none';
            }, 150);
        });
    });
});
</script>


<?php include 'includes/footer.php'; ?>