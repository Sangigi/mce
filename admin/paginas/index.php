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

// Establecer la página actual para el menú
$currentPage = 'paginas';

// Título de la página
$pageTitle = 'Gestión de Páginas';

// Definir las secciones del sitio (igual que en index.php)
$secciones = [
    [
        'id' => 'inicio',
        'nombre' => 'Página de Inicio',
        'descripcion' => 'Página principal del sitio web.',
        'icono' => 'fas fa-home',
        'color' => '#4299e1',
        'archivo' => 'index.php',
        'subsecciones' => [
            'index' => 'Inicio'
        ]
    ],
    [
        'id' => 'nosotros',
        'nombre' => 'Sobre Nosotros',
        'descripcion' => 'Información sobre el centro, misión, visión e historia.',
        'icono' => 'fas fa-building',
        'color' => '#48bb78',
        'archivo' => 'mision-vision.php',
        'subsecciones' => [
            'mision-vision' => 'Misión y Visión',
            'historia' => 'Historia',
            'equipo' => 'Nuestro Equipo',
            'instalaciones' => 'Instalaciones'
        ]
    ],
    [
        'id' => 'cursos',
        'nombre' => 'Cursos y Programas',
        'descripcion' => 'Información sobre los cursos y programas ofrecidos.',
        'icono' => 'fas fa-graduation-cap',
        'color' => '#ed8936',
        'archivo' => 'index.php',
        'subsecciones' => [
            'index' => 'Cursos Actuales',
            'proximos' => 'Próximos Cursos'
        ]
    ],
    [
        'id' => 'convocatorias',
        'nombre' => 'Convocatorias',
        'descripcion' => 'Sección de convocatorias y oportunidades.',
        'icono' => 'fas fa-bullhorn',
        'color' => '#9f7aea',
        'archivo' => 'index.php',
        'subsecciones' => [
            'index' => 'Convocatorias Activas',
            'pasadas' => 'Archivo de Convocatorias'
        ]
    ],
    [
        'id' => 'eventos',
        'nombre' => 'Eventos',
        'descripcion' => 'Calendario y detalles de eventos.',
        'icono' => 'fas fa-calendar-alt',
        'color' => '#f56565',
        'archivo' => 'calendario.php',
        'subsecciones' => [
            'calendario' => 'Calendario de Eventos',
        ]
    ],
    [
        'id' => 'galeria',
        'nombre' => 'Galería',
        'descripcion' => 'Galería de imágenes y multimedia.',
        'icono' => 'fas fa-images',
        'color' => '#38b2ac',
        'archivo' => 'gallery.php',
        'subsecciones' => [
            'gallery' => 'Fotografías'
        ]
    ],
    [
        'id' => 'contacto',
        'nombre' => 'Contacto',
        'descripcion' => 'Información de contacto y formulario.',
        'icono' => 'fas fa-envelope',
        'color' => '#4c51bf',
        'archivo' => 'contact.php',
        'subsecciones' => [
            'contact' => 'Formulario de Contacto',
        ]
    ]
];

// Mensaje de éxito si viene de editar
if (isset($_GET['actualizado']) && $_GET['actualizado'] === 'true') {
    $mensaje = "Sección actualizada correctamente.";
    $tipoMensaje = "success";
}

// Incluir header
include '../includes/header.php';
?>

<div class="content-header">
    <div class="content-header-title">
        <h1>Gestión de Páginas</h1>
        <p>Administra el contenido de las diferentes secciones del sitio web.</p>
    </div>
</div>

<?php if (isset($mensaje)): ?>
    <div class="alert alert-<?php echo $tipoMensaje; ?> alert-dismissible fade show" role="alert">
        <?php echo $mensaje; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<div class="secciones-grid">
    <?php foreach ($secciones as $seccion): ?>
        <div class="seccion-card">
            <div class="seccion-header" style="background-color: <?php echo $seccion['color']; ?>">
                <div class="seccion-icon">
                    <i class="<?php echo $seccion['icono']; ?>"></i>
                </div>
                <h2 class="seccion-title"><?php echo $seccion['nombre']; ?></h2>
            </div>
            <div class="seccion-body">
                <p class="seccion-description"><?php echo $seccion['descripcion']; ?></p>
                
                <h3 class="subsecciones-title">Subsecciones:</h3>
                <ul class="subsecciones-list">
                    <?php foreach ($seccion['subsecciones'] as $id => $nombre): ?>
                        <li>
                            <a href="editar.php?seccion=<?php echo $seccion['id']; ?>&subseccion=<?php echo $id; ?>">
                                <?php echo $nombre; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="seccion-footer">
                <a href="editar.php?seccion=<?php echo $seccion['id']; ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Editar Sección
                </a>
                <?php
                // Determinar la URL correcta para ver la página
                $urlVer = '';
                if ($seccion['id'] === 'inicio') {
                    $urlVer = SITE_URL . '/';
                } else {
                    // Asignar carpeta dependiendo del ID
                    switch ($seccion['id']) {
                        case 'nosotros':
                            $carpeta = 'pages/about/';
                            break;
                        case 'cursos':
                            $carpeta = 'pages/courses/';
                            break;
                        case 'convocatorias':
                            $carpeta = 'pages/announcements/';
                            break;
                        case 'eventos':
                            $carpeta = 'pages/courses/';
                            break;
                        case 'galeria':
                            $carpeta = 'pages/';
                            break;
                        case 'contacto':
                            $carpeta = 'pages/';
                            break;
                        default:
                            $carpeta = ''; // o 'pages/' si quieres una carpeta general por defecto
                            break;
                    }
                
                    $urlVer = SITE_URL . '/' . $carpeta . $seccion['archivo'];
                }

                ?>
                <a href="<?php echo $urlVer; ?>" class="btn btn-secondary" target="_blank">
                    <i class="fas fa-eye"></i> Ver Página
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<style>
    /* Estilos para la gestión de páginas */
    .secciones-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-top: 1.5rem;
    }
    
    .seccion-card {
        background-color: white;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .seccion-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .seccion-header {
        padding: 1.5rem;
        color: white;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .seccion-icon {
        font-size: 2rem;
        width: 3rem;
        height: 3rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
    }
    
    .seccion-title {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
    }
    
    .seccion-body {
        padding: 1.5rem;
        flex: 1;
    }
    
    .seccion-description {
        margin: 0 0 1.5rem;
        color: var(--gray-600);
        font-size: 0.875rem;
        line-height: 1.5;
    }
    
    .subsecciones-title {
        font-size: 1rem;
        font-weight: 600;
        margin: 0 0 0.75rem;
        color: var(--gray-800);
    }
    
    .subsecciones-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .subsecciones-list li {
        margin-bottom: 0.5rem;
    }
    
    .subsecciones-list a {
        display: block;
        padding: 0.5rem;
        color: var(--gray-700);
        text-decoration: none;
        border-radius: 0.25rem;
        transition: background-color 0.2s ease;
    }
    
    .subsecciones-list a:hover {
        background-color: var(--gray-100);
        color: var(--primary);
    }
    
    .seccion-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--gray-200);
        display: flex;
        justify-content: space-between;
        gap: 0.5rem;
    }
    
    .seccion-footer .btn {
        flex: 1;
    }
    
    @media (max-width: 768px) {
        .secciones-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php include '../includes/footer.php'; ?>