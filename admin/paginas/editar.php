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

// Título de la página
$pageTitle = 'Editar Página';

// Verificar si se proporcionó una sección
if (!isset($_GET['seccion'])) {
    header("Location: " . SITE_URL . "/admin/paginas/");
    exit;
}

$seccion = $_GET['seccion'];
$subseccion = isset($_GET['subseccion']) ? $_GET['subseccion'] : '';

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

// Buscar la sección seleccionada
$seccionActual = null;
foreach ($secciones as $s) {
    if ($s['id'] === $seccion) {
        $seccionActual = $s;
        break;
    }
}

if (!$seccionActual) {
    header("Location: " . SITE_URL . "/admin/paginas/");
    exit;
}

// Determinar el título de la página
if (!empty($subseccion) && isset($seccionActual['subsecciones'][$subseccion])) {
    $pageTitle = 'Editar ' . $seccionActual['subsecciones'][$subseccion];
} else {
    $pageTitle = 'Editar ' . $seccionActual['nombre'];
}

// Obtener el contenido actual de la sección
$contenidoActual = '';
$rutaArchivo = '';

// Determinar la ruta del archivo a editar
if (!empty($subseccion)) {
    // Si es una subsección, buscar el archivo correspondiente
    if ($seccion === 'inicio') {
        $carpeta = ''; // No necesitas carpeta para el inicio, está en el nivel superior
    } else {
        switch ($seccion) {
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
                $carpeta = 'pages/courses/'; // igual que cursos, si así lo necesitas
                break;
            case 'galeria':
            case 'contacto':
                $carpeta = 'pages/';
                break;
            default:
                $carpeta = 'pages/';
                break;
        }
    }

    $rutaArchivo = '../../' . $carpeta . $subseccion . '.php';
}
 else {
    // Si es la sección principal, usar el archivo principal
if ($seccion === 'inicio') {
    $rutaArchivo = '../../index.php';
} else {
    switch ($seccion) {
        case 'nosotros':
            $carpeta = 'pages/about/';
            $archivo = 'mision-vision.php';
            break;
        case 'cursos':
            $carpeta = 'pages/courses/';
            $archivo = 'index.php';
            break;
        case 'convocatorias':
            $carpeta = 'pages/announcements/';
            $archivo = 'index.php';
            break;
        case 'eventos':
            $carpeta = 'pages/courses/';
            $archivo = 'calendario.php';
            break;
        case 'galeria':
            $carpeta = 'pages/';
            $archivo = 'gallery.php';
            break;
        case 'contacto':
            $carpeta = 'pages/';
            $archivo = 'contact.php';
            break;
        default:
            $carpeta = 'pages/';
            $archivo = $seccion . '.php'; // como fallback
            break;
    }

    $rutaArchivo = '../../' . $carpeta . $archivo;
}
}


// Verificar si el archivo existe
if (file_exists($rutaArchivo)) {
    $contenidoActual = file_get_contents($rutaArchivo);
} else {
    // Si no existe, crear una plantilla básica
    $contenidoActual = '<?php include "../includes/header.php"; ?>

<div class="container">
    <h1>' . ($subseccion ? $seccionActual['subsecciones'][$subseccion] : $seccionActual['nombre']) . '</h1>
    <p>Contenido de la página.</p>
</div>

<?php include "../includes/footer.php"; ?>';
}

// Procesar formulario
$mensaje = '';
$tipoMensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevoContenido = $_POST['contenido'];
    
    // Validar que el contenido no esté vacío
    if (empty($nuevoContenido)) {
        $mensaje = "El contenido no puede estar vacío.";
        $tipoMensaje = "danger";
    } else {
        // Crear directorio si no existe
        $directorioBase = dirname($rutaArchivo);
        if (!file_exists($directorioBase)) {
            mkdir($directorioBase, 0755, true);
        }
        
        // Guardar el contenido
        if (file_put_contents($rutaArchivo, $nuevoContenido)) {
            $mensaje = "Contenido guardado correctamente.";
            $tipoMensaje = "success";
            
            // Actualizar la variable de contenido actual
            $contenidoActual = $nuevoContenido;
        } else {
            $mensaje = "Error al guardar el contenido. Verifique los permisos de escritura.";
            $tipoMensaje = "danger";
        }
    }
}

// Incluir header
include '../includes/header.php';
?>

<div class="content-header">
    <div class="content-header-title">
        <h1><?php echo $pageTitle; ?></h1>
        <p>
            <?php if (!empty($subseccion)): ?>
                Edita el contenido de la subsección "<?php echo $seccionActual['subsecciones'][$subseccion]; ?>" de la sección "<?php echo $seccionActual['nombre']; ?>".
            <?php else: ?>
                Edita el contenido principal de la sección "<?php echo $seccionActual['nombre']; ?>".
            <?php endif; ?>
        </p>
    </div>
    <div class="content-header-actions">
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
        <?php if (file_exists($rutaArchivo)): ?>
            <a href="<?php echo SITE_URL . '/' . str_replace('../../', '', $rutaArchivo); ?>" class="btn btn-info" target="_blank">
                <i class="fas fa-eye"></i> Ver Página
            </a>
        <?php endif; ?>
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
        <h2>Editor de Contenido</h2>
        <div class="card-header-actions">
            <div class="btn-group mr-2">
                <button type="button" class="btn btn-sm btn-info" id="toggleView">
                    <i class="fas fa-eye"></i> <span id="viewStatus">Vista Previa</span>
                </button>
                <button type="button" class="btn btn-sm btn-secondary" id="toggleCSS">
                    <i class="fas fa-paint-brush"></i> <span id="cssStatus">Desactivar CSS</span>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <form action="" method="post" id="editorForm">
            <div class="form-group">
                <!-- Editor de código (visible por defecto) -->
                <div id="editor-container">
                    <textarea id="editor" name="contenido" class="form-control" rows="20"><?php echo htmlspecialchars($contenidoActual); ?></textarea>
                </div>
                
                <!-- Vista previa (oculta por defecto) -->
                <div id="preview-container" style="display: none;">
                    <div class="preview-options mb-3">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Esta es una vista previa. Los cambios realizados en el editor se reflejarán aquí.
                        </div>
                    </div>
                    <div class="preview-frame">
                        <iframe id="preview-frame" style="width: 100%; height: 600px; border: 1px solid #ddd;"></iframe>
                    </div>
                </div>
            </div>
            
            <div class="form-group mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<style>
    /* Estilos para el editor */
    #editor {
        font-family: monospace;
        font-size: 14px;
        line-height: 1.5;
        resize: vertical;
    }
    
    .preview-frame {
        background-color: white;
        border-radius: 0.375rem;
        overflow: hidden;
    }
    
    .mt-4 {
        margin-top: 1.5rem;
    }
    
    .mb-3 {
        margin-bottom: 1rem;
    }
    
    .mr-2 {
        margin-right: 0.5rem;
    }
    
    /* Estilos generales */
    .form-control {
        display: block;
        width: 100%;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        line-height: 1.5;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    
    .form-control:focus {
        color: #495057;
        background-color: #fff;
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    .btn {
        display: inline-block;
        font-weight: 400;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
        user-select: none;
        border: 1px solid transparent;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        line-height: 1.5;
        border-radius: 0.25rem;
        transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    
    .btn-primary {
        color: #fff;
        background-color: #007bff;
        border-color: #007bff;
    }
    
    .btn-secondary {
        color: #fff;
        background-color: #6c757d;
        border-color: #6c757d;
    }
    
    .btn-info {
        color: #fff;
        background-color: #17a2b8;
        border-color: #17a2b8;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        line-height: 1.5;
        border-radius: 0.2rem;
    }
    
    .btn-group {
        position: relative;
        display: inline-flex;
        vertical-align: middle;
    }
    
    .card {
        position: relative;
        display: flex;
        flex-direction: column;
        min-width: 0;
        word-wrap: break-word;
        background-color: #fff;
        background-clip: border-box;
        border: 1px solid rgba(0, 0, 0, 0.125);
        border-radius: 0.25rem;
        margin-bottom: 1.5rem;
    }
    
    .card-header {
        padding: 0.75rem 1.25rem;
        margin-bottom: 0;
        background-color: rgba(0, 0, 0, 0.03);
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .card-body {
        flex: 1 1 auto;
        padding: 1.25rem;
    }
    
    .alert {
        position: relative;
        padding: 0.75rem 1.25rem;
        margin-bottom: 1rem;
        border: 1px solid transparent;
        border-radius: 0.25rem;
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
    
    .alert-info {
        color: #0c5460;
        background-color: #d1ecf1;
        border-color: #bee5eb;
    }
    
    .content-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    
    .content-header-title h1 {
        margin-bottom: 0.25rem;
        font-size: 1.75rem;
    }
    
    .content-header-title p {
        margin-bottom: 0;
        color: #6c757d;
    }
    
    .content-header-actions {
        display: flex;
        gap: 0.5rem;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Referencias a elementos del DOM
        const toggleViewBtn = document.getElementById('toggleView');
        const viewStatus = document.getElementById('viewStatus');
        const toggleCSSBtn = document.getElementById('toggleCSS');
        const cssStatus = document.getElementById('cssStatus');
        const editorContainer = document.getElementById('editor-container');
        const previewContainer = document.getElementById('preview-container');
        const editor = document.getElementById('editor');
        const previewFrame = document.getElementById('preview-frame');
        const form = document.getElementById('editorForm');
        
        // Variables para el editor
        let cssEnabled = true;
        let originalContent = editor.value;
        
        // Inicializar la vista previa si se muestra
        if (previewContainer.style.display !== 'none') {
            updatePreview();
        }
        
        // Toggle entre editor y vista previa
        toggleViewBtn.addEventListener('click', function() {
            if (previewContainer.style.display === 'none') {
                // Mostrar vista previa
                editorContainer.style.display = 'none';
                previewContainer.style.display = 'block';
                viewStatus.textContent = 'Editor';
                // Actualizar la vista previa con el contenido actual del editor
                updatePreview();
            } else {
                // Mostrar editor
                previewContainer.style.display = 'none';
                editorContainer.style.display = 'block';
                viewStatus.textContent = 'Vista Previa';
            }
        });
        
        // Toggle CSS
        toggleCSSBtn.addEventListener('click', function() {
            cssEnabled = !cssEnabled;
            if (cssEnabled) {
                cssStatus.textContent = 'Desactivar CSS';
            } else {
                cssStatus.textContent = 'Activar CSS';
            }
            if (previewContainer.style.display !== 'none') {
                updatePreview();
            }
        });
        
        // Actualizar la vista previa cuando cambia el contenido del editor
        editor.addEventListener('input', function() {
            if (previewContainer.style.display !== 'none') {
                updatePreview();
            }
        });
        
        // Confirmar antes de salir si hay cambios sin guardar
        window.addEventListener('beforeunload', function(e) {
            if (editor.value !== originalContent && !form.submitted) {
                e.preventDefault();
                e.returnValue = '¿Está seguro de que desea salir? Los cambios no guardados se perderán.';
                return e.returnValue;
            }
        });
        
        // Marcar el formulario como enviado al guardar
        form.addEventListener('submit', function() {
            form.submitted = true;
        });
        
        // Función para actualizar la vista previa
        function updatePreview() {
            const previewDoc = previewFrame.contentDocument || previewFrame.contentWindow.document;
            previewDoc.open();
            
            // Si CSS está desactivado, eliminar las etiquetas <link> y <style>
            let content = editor.value;
            if (!cssEnabled) {
                // Crear un DOM temporal para manipular el contenido
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = content;
                
                // Eliminar etiquetas link (CSS externos)
                const links = tempDiv.querySelectorAll('link[rel="stylesheet"]');
                links.forEach(link => link.remove());
                
                // Eliminar etiquetas style (CSS internos)
                const styles = tempDiv.querySelectorAll('style');
                styles.forEach(style => style.remove());
                
                // Eliminar atributos style (CSS en línea)
                const elementsWithStyle = tempDiv.querySelectorAll('[style]');
                elementsWithStyle.forEach(el => el.removeAttribute('style'));
                
                // Eliminar clases CSS
                const elementsWithClass = tempDiv.querySelectorAll('[class]');
                elementsWithClass.forEach(el => el.removeAttribute('class'));
                
                content = tempDiv.innerHTML;
            }
            
            // Agregar base href para que los recursos relativos funcionen correctamente
            const baseUrl = '<?php echo SITE_URL; ?>';
            const baseTag = '<base href="' + baseUrl + '/">';
            
            // Insertar la etiqueta base en el head
            if (content.includes('<head>')) {
                content = content.replace('<head>', '<head>' + baseTag);
            } else {
                content = '<head>' + baseTag + '</head>' + content;
            }
            
            previewDoc.write(content);
            previewDoc.close();
        }
    });
</script>

<?php include '../includes/footer.php'; ?>