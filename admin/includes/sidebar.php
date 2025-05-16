<div class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <img src="<?php echo SITE_URL; ?>/assets/img/logo.png" alt="CEA Logo">
            <h1>CEA Admin</h1>
        </div>
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    
    <div class="sidebar-user">
        <div class="sidebar-user-info">
            <div class="sidebar-user-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="sidebar-user-details">
                <h4><?php echo $_SESSION['user_name']; ?></h4>
                <p><?php echo $_SESSION['user_role'] === 'admin' ? 'Administrador' : 'Editor'; ?></p>
            </div>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul class="sidebar-menu">
            <li class="sidebar-menu-item <?php echo $pageTitle === 'Dashboard' ? 'active' : ''; ?>">
                <a href="<?php echo SITE_URL; ?>/admin/dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li class="sidebar-menu-item <?php echo strpos($_SERVER['PHP_SELF'], '/admin/convocatorias/') !== false ? 'active' : ''; ?>">
                <a href="<?php echo SITE_URL; ?>/admin/convocatorias/">
                    <i class="fas fa-bullhorn"></i>
                    <span>Convocatorias</span>
                </a>
            </li>
            
            <li class="sidebar-menu-item <?php echo strpos($_SERVER['PHP_SELF'], '/admin/cursos/') !== false ? 'active' : ''; ?>">
                <a href="<?php echo SITE_URL; ?>/admin/cursos/">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Cursos</span>
                </a>
            </li>
            
            <li class="sidebar-menu-item <?php echo strpos($_SERVER['PHP_SELF'], '/admin/eventos/') !== false ? 'active' : ''; ?>">
                <a href="<?php echo SITE_URL; ?>/admin/eventos/">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Eventos</span>
                </a>
            </li>
            
            <li class="sidebar-menu-item <?php echo strpos($_SERVER['PHP_SELF'], '/admin/galeria/') !== false ? 'active' : ''; ?>">
                <a href="<?php echo SITE_URL; ?>/admin/galeria/">
                    <i class="fas fa-images"></i>
                    <span>Galería</span>
                </a>
            </li>
            
            <li class="sidebar-menu-item <?php echo strpos($_SERVER['PHP_SELF'], '/admin/paginas/') !== false ? 'active' : ''; ?>">
                <a href="<?php echo SITE_URL; ?>/admin/paginas/">
                    <i class="fas fa-file-alt"></i>
                    <span>Páginas</span>
                </a>
            </li>
            
            <li class="sidebar-menu-item <?php echo strpos($_SERVER['PHP_SELF'], '/admin/about/') !== false ? 'active' : ''; ?>">
                <a href="<?php echo SITE_URL; ?>/admin/about/">
                    <i class="fas fa-envelope"></i>
                    <span>Acerca de</span>
                </a>
            </li>

            <li class="sidebar-menu-item <?php echo strpos($_SERVER['PHP_SELF'], '/admin/contactos/') !== false ? 'active' : ''; ?>">
                <a href="<?php echo SITE_URL; ?>/admin/contactos/">
                    <i class="fas fa-envelope"></i>
                    <span>Contactos</span>
                </a>
            </li>
            
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <li class="sidebar-menu-item <?php echo strpos($_SERVER['PHP_SELF'], '/admin/usuarios/') !== false ? 'active' : ''; ?>">
                    <a href="<?php echo SITE_URL; ?>/admin/usuarios/">
                        <i class="fas fa-users"></i>
                        <span>Usuarios</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
        
        <div class="sidebar-divider"></div>
        
        <ul class="sidebar-menu">
            <li class="sidebar-menu-item">
                <a href="<?php echo SITE_URL; ?>/" target="_blank">
                    <i class="fas fa-external-link-alt"></i>
                    <span>Ver Sitio</span>
                </a>
            </li>
            
            <li class="sidebar-menu-item">
                <a href="<?php echo SITE_URL; ?>/admin/logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Cerrar Sesión</span>
                </a>
            </li>
        </ul>
    </nav>
</div>