<?php require_once __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <meta name="description" content="<?php echo isset($pageDescription) ? $pageDescription : 'Centro de Estudios Avanzados'; ?>">
    <link rel="stylesheet" href="/cea-website/assets/css/variables.css">
    <link rel="stylesheet" href="/cea-website/assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="icon" type="image/png" href="../assets/img/logo-cea-bg.png">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <?php if (isset($extraCSS)) echo $extraCSS; ?>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-top">
            <div class="container header-top-container">
                <div class="header-contact">
                    <div class="header-contact-item">
                        <i class="fas fa-phone"></i>
                        <span>+52 (55) 1234-5678</span>
                    </div>
                    <div class="header-contact-item">
                        <i class="fas fa-envelope"></i>
                        <span>contacto@cea.edu.mx</span>
                    </div>
                </div>
                <div class="header-social">
                    <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
        
        <div class="container header-container">
            <div class="logo">
                <img src="<?php echo SITE_URL; ?>/assets/img/logo_cea_negro-bg.png" alt="Logo CEA" class="invert-color">
                <div class="logo-text">Centro de Estudios Avanzados</div>
            </div>
            
            <button class="mobile-menu-toggle" id="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <nav class="nav" id="main-nav">
                <div class="nav-item">
                    <a href="<?php echo SITE_URL; ?>" class="nav-link <?php echo ($currentPage == 'home') ? 'active' : ''; ?>">Inicio</a>
                </div>
                
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle <?php echo (in_array($currentPage, ['mision-vision', 'historia', 'equipo', 'instalaciones'])) ? 'active' : ''; ?>">
                        Acerca de <i class="fas fa-chevron-down"></i>
                    </a>
                    <div class="dropdown-menu">
                        <a href="<?php echo SITE_URL; ?>/pages/about/mision-vision.php" class="dropdown-item">Misión y Visión</a>
                        <a href="<?php echo SITE_URL; ?>/pages/about/historia.php" class="dropdown-item">Historia</a>
                        <a href="<?php echo SITE_URL; ?>/pages/about/equipo.php" class="dropdown-item">Equipo</a>
                        <a href="<?php echo SITE_URL; ?>/pages/about/instalaciones.php" class="dropdown-item">Instalaciones</a>
                    </div>
                </div>
                
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle <?php echo (in_array($currentPage, ['cursos', 'curso-detalle'])) ? 'active' : ''; ?>">
                        Cursos <i class="fas fa-chevron-down"></i>
                    </a>
                    <div class="dropdown-menu">
                        <a href="<?php echo SITE_URL; ?>/pages/courses/index.php" class="dropdown-item">Todos los Cursos</a>
                        <a href="<?php echo SITE_URL; ?>/pages/courses/proximos.php" class="dropdown-item">Próximos Cursos</a>
                        <a href="<?php echo SITE_URL; ?>/pages/courses/calendario.php" class="dropdown-item">Calendario</a>
                    </div>
                </div>
                
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle <?php echo (in_array($currentPage, ['convocatorias', 'convocatoria-detalle'])) ? 'active' : ''; ?>">
                        Convocatorias <i class="fas fa-chevron-down"></i>
                    </a>
                    <div class="dropdown-menu">
                        <a href="<?php echo SITE_URL; ?>/pages/announcements/index.php" class="dropdown-item">Convocatorias Actuales</a>
                        <a href="<?php echo SITE_URL; ?>/pages/announcements/pasadas.php" class="dropdown-item">Convocatorias Pasadas</a>
                    </div>
                </div>
                
                <div class="nav-item">
                    <a href="<?php echo SITE_URL; ?>/pages/gallery.php" class="nav-link <?php echo ($currentPage == 'gallery') ? 'active' : ''; ?>">Galeria</a>
                </div>

                <div class="nav-item">
                    <a href="<?php echo SITE_URL; ?>/pages/contact.php" class="nav-link <?php echo ($currentPage == 'contacto') ? 'active' : ''; ?>">Contacto</a>
                </div>
            </nav>
        </div>
    </header>
    
    <?php displayAlert(); ?>