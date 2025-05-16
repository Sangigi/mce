<?php
// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: " . SITE_URL . "/admin/login.php");
    exit;
}

// Obtener información del usuario actual
$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];
$userRole = $_SESSION['user_role'];

// Verificar si hay notificaciones (ejemplo)
$notificaciones = 0; // Aquí se podria consultar la base de datos para obtener notificaciones reales
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../../assets/img/logo_cea_negro-bg.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Panel de Administración | CEA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/admin/assets/css/admin.css">
    <?php if (isset($extraCSS)) echo $extraCSS; ?>
    <style>
        :root {
            --primary: #004B8D;
            --primary-dark: #003A6E;
            --primary-light: #0067C5;
            --secondary: #00A651;
            --secondary-dark: #008542;
            --secondary-light: #00C561;
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
            --danger: #dc2626;
            --success: #16a34a;
            --warning: #eab308;
            --info: #0ea5e9;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            background-color: var(--gray-100);
            color: var(--gray-800);
            line-height: 1.5;
        }
        
        /* Layout */
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            width: 280px;
            background-color: white;
            border-right: 1px solid var(--gray-200);
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 100;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        
        .fa-bars {
            transform: translateX(-4px);
        }

        .admin-sidebar.collapsed {
            width: 80px;
        }
        
        .admin-content {
            flex: 1;
            margin-left: 280px;
            transition: margin-left 0.3s ease;
        }
        
        .admin-content.expanded {
            margin-left: 80px;
        }
        
        /* Sidebar */
        .sidebar-header {
            padding: 1.5rem;
            display: flex;
            flex-direction: column-reverse;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .sidebar-logo img {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }
        
        .sidebar-logo-text {
            font-weight: 600;
            font-size: 1.25rem;
            color: var(--primary);
            white-space: nowrap;
            transition: opacity 0.3s ease;
        }
        
        .collapsed .sidebar-logo-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }
        
        .sidebar-toggle {
            background: none;
            border: none;
            color: var(--gray-500);
            cursor: pointer;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .sidebar-toggle:hover {
            background-color: var(--gray-100);
            color: var(--gray-800);
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .sidebar-user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-light);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1rem;
        }
        
        .sidebar-user-info {
            transform: translateX(10px);
            transition: opacity 0.3s ease;
        }
        
        .collapsed .sidebar-user-info {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }
        
        .sidebar-user-name {
            font-weight: 600;
            color: var(--gray-800);
        }
        
        .sidebar-user-role {
            font-size: 0.875rem;
            transform: translateX(-10px);
            color: var(--gray-500);
        }
        
        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 1rem 0;
        }
        
        .nav-section {
            margin-bottom: 1rem;
        }
        
        .nav-section-title {
            padding: 0.5rem 1.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: opacity 0.3s ease;
        }
        
        .collapsed .nav-section-title {
            opacity: 0;
            height: 0;
            overflow: hidden;
            padding: 0;
            margin: 0;
        }
        
        .nav-item {
            position: relative;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.5rem;
            color: var(--gray-700);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .nav-link:hover {
            background-color: var(--gray-100);
            color: var(--primary);
        }
        
        .nav-link.active {
            background-color: var(--gray-100);
            color: var(--primary);
            border-left-color: var(--primary);
            font-weight: 500;
        }
        
        .nav-icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .nav-text {
            transition: opacity 0.3s ease;
            white-space: nowrap;
        }
        
        .collapsed .nav-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }
        
        .nav-badge {
            position: absolute;
            top: 50%;
            right: 1.5rem;
            transform: translateY(-50%);
            background-color: var(--danger);
            color: white;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.125rem 0.375rem;
            border-radius: 9999px;
            transition: opacity 0.3s ease;
        }
        
        .collapsed .nav-badge {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }
        
        .sidebar-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .sidebar-footer-link {
            color: var(--gray-500);
            text-decoration: none;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: color 0.3s ease;
        }
        
        .sidebar-footer-link:hover {
            color: var(--primary);
        }
        
        .sidebar-footer-text {
            transition: opacity 0.3s ease;
            white-space: nowrap;
        }
        
        .collapsed .sidebar-footer-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }
        
        /* Header */
        .admin-header {
            background-color: white;
            border-bottom: 1px solid var(--gray-200);
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        
        .header-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-800);
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .header-action {
            background: none;
            border: none;
            color: var(--gray-500);
            cursor: pointer;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .header-action:hover {
            background-color: var(--gray-100);
            color: var(--gray-800);
        }
        
        .header-action-badge {
            position: absolute;
            top: 0;
            right: 0;
            background-color: var(--danger);
            color: white;
            font-size: 0.75rem;
            font-weight: 600;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .header-user {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.375rem;
            transition: all 0.3s ease;
        }
        
        .header-user:hover {
            background-color: var(--gray-100);
        }
        
        .header-user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: var(--primary-light);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        .header-user-info {
            display: flex;
            flex-direction: column;
        }
        
        .header-user-name {
            font-weight: 500;
            color: var(--gray-800);
            font-size: 0.875rem;
        }
        
        .header-user-role {
            font-size: 0.75rem;
            color: var(--gray-500);
        }
        
        /* Main Content */
        .admin-main {
            padding: 2rem;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .admin-sidebar {
                transform: translateX(-100%);
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            }
            
            .admin-sidebar.mobile-open {
                transform: translateX(0);
            }
            
            .admin-content {
                margin-left: 0;
            }
            
            .admin-content.expanded {
                margin-left: 0;
            }
            
            .mobile-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 90;
                display: none;
            }
            
            .mobile-overlay.active {
                display: block;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <img src="<?php echo SITE_URL; ?>/assets/img/logo_cea_negro-bg.png" alt="Logo CEA">
                    <span class="sidebar-logo-text">CEA Admin</span>
                </div>
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <div class="sidebar-user">
                <div class="sidebar-user-avatar">
                    <?php echo substr($userName, 0, 1); ?>
                </div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name"><?php echo $userName; ?></div>
                    <div class="sidebar-user-role"><?php echo ucfirst($userRole); ?></div>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Principal</div>
                    <div class="nav-item">
                        <a href="<?php echo SITE_URL; ?>/admin/dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                            <div class="nav-icon"><i class="fas fa-tachometer-alt"></i></div>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </div>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Contenido</div>
                    <div class="nav-item">
                        <a href="<?php echo SITE_URL; ?>/admin/convocatorias/" class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/admin/convocatorias/') !== false ? 'active' : ''; ?>">
                            <div class="nav-icon"><i class="fas fa-bullhorn"></i></div>
                            <span class="nav-text">Convocatorias</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="<?php echo SITE_URL; ?>/admin/cursos/" class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/admin/cursos/') !== false ? 'active' : ''; ?>">
                            <div class="nav-icon"><i class="fas fa-graduation-cap"></i></div>
                            <span class="nav-text">Cursos</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="<?php echo SITE_URL; ?>/admin/eventos/" class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/admin/eventos/') !== false ? 'active' : ''; ?>">
                            <div class="nav-icon"><i class="fas fa-calendar-alt"></i></div>
                            <span class="nav-text">Eventos</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="<?php echo SITE_URL; ?>/admin/paginas/" class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/admin/paginas/') !== false ? 'active' : ''; ?>">
                            <div class="nav-icon"><i class="fas fa-file-alt"></i></div>
                            <span class="nav-text">Páginas</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="<?php echo SITE_URL; ?>/admin/galeria/" class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/admin/galeria/') !== false ? 'active' : ''; ?>">
                            <div class="nav-icon"><i class="fas fa-images"></i></div>
                            <span class="nav-text">Galería</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="<?php echo SITE_URL; ?>/admin/about/imagenes.php" class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/admin/about/imagenes.php') !== false ? 'active' : ''; ?>">
                            <div class="nav-icon"><i class="fas fa-images"></i></div>
                            <span class="nav-text">Acerca de</span>
                        </a>
                    </div>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Comunicación</div>
                    <div class="nav-item">
                        <a href="<?php echo SITE_URL; ?>/admin/contactos/" class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/admin/contactos/') !== false ? 'active' : ''; ?>">
                            <div class="nav-icon"><i class="fas fa-envelope"></i></div>
                            <span class="nav-text">Contactos</span>
                            <?php if ($notificaciones > 0): ?>
                                <span class="nav-badge"><?php echo $notificaciones; ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Administración</div>
                    <div class="nav-item">
                        <a href="<?php echo SITE_URL; ?>/admin/usuarios/" class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/admin/usuarios/') !== false ? 'active' : ''; ?>">
                            <div class="nav-icon"><i class="fas fa-users"></i></div>
                            <span class="nav-text">Usuarios</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="<?php echo SITE_URL; ?>/admin/configuracion.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'configuracion.php' ? 'active' : ''; ?>">
                            <div class="nav-icon"><i class="fas fa-cog"></i></div>
                            <span class="nav-text">Configuración</span>
                        </a>
                    </div>
                </div>
            </nav>
            
            <div class="sidebar-footer">
                <a href="<?php echo SITE_URL; ?>" class="sidebar-footer-link" target="_blank">
                    <i class="fas fa-external-link-alt"></i>
                    <span class="sidebar-footer-text">Ver sitio</span>
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/logout.php" class="sidebar-footer-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="sidebar-footer-text">Cerrar sesión</span>
                </a>
            </div>
        </aside>
        
        <!-- Mobile Overlay -->
        <div class="mobile-overlay" id="mobileOverlay"></div>
        
        <!-- Main Content -->
        <div class="admin-content" id="adminContent">
            <header class="admin-header">
                <div class="header-title">
                    <?php echo isset($pageTitle) ? $pageTitle : 'Panel de Administración'; ?>
                </div>
                
                <div class="header-actions">
                    <button class="header-action d-lg-none" id="mobileSidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <button class="header-action">
                        <i class="fas fa-bell"></i>
                        <?php if ($notificaciones > 0): ?>
                            <span class="header-action-badge"><?php echo $notificaciones; ?></span>
                        <?php endif; ?>
                    </button>
                    
                    <div class="header-user">
                        <div class="header-user-avatar">
                            <?php echo substr($userName, 0, 1); ?>
                        </div>
                        <div class="header-user-info">
                            <div class="header-user-name"><?php echo $userName; ?></div>
                            <div class="header-user-role"><?php echo ucfirst($userRole); ?></div>
                        </div>
                    </div>
                </div>
            </header>
            
            <main class="admin-main">