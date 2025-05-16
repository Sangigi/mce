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
$pageTitle = 'Gestión de Imágenes - Sección Acerca de';

// Definir las secciones de "Acerca de" que tienen imágenes
$secciones = [
    [
        'id' => 'historia',
        'nombre' => 'Historia',
        'descripcion' => 'Imágenes de la historia del centro y fundadores.',
        'icono' => 'fas fa-history',
        'color' => '#48bb78',
        'imagenes' => [
            'history-main' => 'Imagen principal de historia',
            'founder1' => 'Fundador 1',
            'founder2' => 'Fundador 2',
            'founder3' => 'Fundador 3'
        ]
    ],
    [
        'id' => 'equipo',
        'nombre' => 'Equipo',
        'descripcion' => 'Fotografías del equipo directivo, académico y administrativo.',
        'icono' => 'fas fa-users',
        'color' => '#4299e1',
        'imagenes' => [
            'director' => 'Director General',
            'subdirectora' => 'Subdirectora Académica',
            'coordinador' => 'Coordinador de Investigación',
            'profesor1' => 'Profesor 1',
            'profesor2' => 'Profesor 2',
            'profesor3' => 'Profesor 3',
            'investigador1' => 'Investigador 1',
            'investigador2' => 'Investigador 2',
            'investigador3' => 'Investigador 3',
            'admin1' => 'Administrativo 1',
            'admin2' => 'Administrativo 2',
            'admin3' => 'Administrativo 3'
        ]
    ],
    [
        'id' => 'instalaciones',
        'nombre' => 'Instalaciones',
        'descripcion' => 'Imágenes de las instalaciones y espacios del centro.',
        'icono' => 'fas fa-building',
        'color' => '#ed8936',
        'imagenes' => [
            'campus' => 'Campus Principal',
            'biblioteca' => 'Biblioteca Central',
            'auditorio' => 'Auditorio Principal',
            'laboratorio' => 'Laboratorios de Investigación',
            'aulas' => 'Aulas de Posgrado',
            'areas-comunes' => 'Áreas Comunes'
        ]
    ]
];

// Procesar formulario de subida de imagen
$mensaje = '';
$tipoMensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subir_imagen'])) {
    $seccion = $_POST['seccion'];
    $imagen_id = $_POST['imagen_id'];
    
    // Validar que se haya seleccionado una sección e imagen válidas
    $seccionValida = false;
    $imagenValida = false;
    
    foreach ($secciones as $s) {
        if ($s['id'] === $seccion) {
            $seccionValida = true;
            if (isset($s['imagenes'][$imagen_id])) {
                $imagenValida = true;
                break;
            }
        }
    }
    
    if (!$seccionValida || !$imagenValida) {
        $mensaje = "Sección o imagen no válida.";
        $tipoMensaje = "danger";
    } elseif (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
        $mensaje = "Error al subir la imagen. Por favor, inténtelo de nuevo.";
        $tipoMensaje = "danger";
    } else {
        // Validar tipo de archivo
        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif'];
        $tipoArchivo = $_FILES['imagen']['type'];
        
        if (!in_array($tipoArchivo, $tiposPermitidos)) {
            $mensaje = "Tipo de archivo no permitido. Solo se permiten imágenes JPG, PNG y GIF.";
            $tipoMensaje = "danger";
        } else {
            // Crear directorio si no existe
            $directorioBase = '../../assets/img/';
            
            if ($seccion === 'equipo') {
                $directorio = $directorioBase . 'team/';
            } elseif ($seccion === 'instalaciones') {
                $directorio = $directorioBase . 'facilities/';
            } else {
                $directorio = $directorioBase;
            }
            
            if (!file_exists($directorio)) {
                mkdir($directorio, 0755, true);
            }
            
            // Determinar extensión del archivo
            $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            
            // Nombre del archivo
            $nombreArchivo = $imagen_id . '.' . $extension;
            
            // Ruta completa
            $rutaArchivo = $directorio . $nombreArchivo;
            
            // Si es una imagen de instalaciones, también crear la miniatura
            if ($seccion === 'instalaciones') {
                // Mover el archivo original
                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaArchivo)) {
                    // Crear miniatura
                    $rutaMiniatura = $directorio . $imagen_id . '-thumb.' . $extension;
                    
                    // Usar GD para crear la miniatura
                    list($ancho, $alto) = getimagesize($rutaArchivo);
                    $anchoMiniatura = 150;
                    $altoMiniatura = ($alto / $ancho) * $anchoMiniatura;
                    
                    $miniatura = imagecreatetruecolor($anchoMiniatura, $altoMiniatura);
                    
                    if ($extension === 'jpg' || $extension === 'jpeg') {
                        $origen = imagecreatefromjpeg($rutaArchivo);
                    } elseif ($extension === 'png') {
                        $origen = imagecreatefrompng($rutaArchivo);
                    } elseif ($extension === 'gif') {
                        $origen = imagecreatefromgif($rutaArchivo);
                    }
                    
                    imagecopyresampled($miniatura, $origen, 0, 0, 0, 0, $anchoMiniatura, $altoMiniatura, $ancho, $alto);
                    
                    if ($extension === 'jpg' || $extension === 'jpeg') {
                        imagejpeg($miniatura, $rutaMiniatura, 90);
                    } elseif ($extension === 'png') {
                        imagepng($miniatura, $rutaMiniatura, 9);
                    } elseif ($extension === 'gif') {
                        imagegif($miniatura, $rutaMiniatura);
                    }
                    
                    imagedestroy($origen);
                    imagedestroy($miniatura);
                    
                    $mensaje = "Imagen y miniatura subidas correctamente.";
                    $tipoMensaje = "success";
                } else {
                    $mensaje = "Error al guardar la imagen. Verifique los permisos de escritura.";
                    $tipoMensaje = "danger";
                }
            } else {
                // Para otras secciones, solo mover el archivo
                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaArchivo)) {
                    $mensaje = "Imagen subida correctamente.";
                    $tipoMensaje = "success";
                } else {
                    $mensaje = "Error al guardar la imagen. Verifique los permisos de escritura.";
                    $tipoMensaje = "danger";
                }
            }
        }
    }
}

// Incluir header
include '../includes/header.php';
?>

<style>
/* Estilos para la página de gestión de imágenes */
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

.card-header h2 {
  margin: 0;
  font-size: 1.25rem;
  color: var(--gray-800);
}

.card-body {
  padding: 1.5rem;
}

/* Estilos para las pestañas */
.nav-tabs {
  border-bottom: 1px solid var(--gray-200);
  margin-bottom: 1.5rem;
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
  cursor: pointer;
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

.col-md-4 {
  flex: 0 0 33.333333%;
  max-width: 33.333333%;
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

.btn-secondary {
  color: white;
  background-color: var(--secondary);
  border-color: var(--secondary);
}

.btn-info {
  color: white;
  background-color: var(--info);
  border-color: var(--info);
}

.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.75rem;
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

/* Estilos para la sección de descripción */
.section-description {
  margin-bottom: 1.5rem;
  padding: 1rem;
  background-color: var(--gray-50);
  border-radius: var(--border-radius);
  border-left: 4px solid var(--primary);
}

.section-description p {
  margin: 0;
  color: var(--gray-700);
}

/* Estilos para la cuadrícula de imágenes */
.images-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1.5rem;
}

.image-card {
  background-color: white;
  border-radius: var(--border-radius);
  box-shadow: var(--box-shadow);
  overflow: hidden;
  transition: all var(--transition-speed);
  border: 1px solid var(--gray-200);
}

.image-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.image-preview {
  height: 180px;
  overflow: hidden;
  background-color: var(--gray-100);
  display: flex;
  align-items: center;
  justify-content: center;
}

.image-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.no-image {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: var(--gray-400);
  height: 100%;
  width: 100%;
}

.no-image i {
  font-size: 3rem;
  margin-bottom: 0.5rem;
}

.image-info {
  padding: 1rem;
  border-bottom: 1px solid var(--gray-200);
}

.image-info h4 {
  margin: 0 0 0.5rem;
  font-size: 1rem;
  color: var(--gray-800);
}

.image-id {
  margin: 0 0 0.5rem;
  font-size: 0.8rem;
  color: var(--gray-500);
}

.image-status {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  border-radius: 1rem;
  font-size: 0.75rem;
  font-weight: 600;
  margin: 0;
}

.status-active {
  background-color: rgba(40, 167, 69, 0.1);
  color: #28a745;
}

.status-pending {
  background-color: rgba(255, 193, 7, 0.1);
  color: #ffc107;
}

.image-actions {
  padding: 1rem;
  display: flex;
  justify-content: space-between;
}

/* Estilos para el modal */
.modal-header {
  background-color: var(--primary);
  color: white;
  border-bottom: none;
  padding: 1rem 1.5rem;
}

.modal-title {
  font-weight: 600;
}

.modal-header .close {
  color: white;
  opacity: 0.8;
  text-shadow: none;
}

.modal-header .close:hover {
  opacity: 1;
}

.modal-body {
  padding: 1.5rem;
}

.modal-footer {
  border-top: 1px solid var(--gray-200);
  padding: 1rem 1.5rem;
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
  .col-md-4 {
    flex: 0 0 100%;
    max-width: 100%;
  }

  .form-row {
    flex-direction: column;
  }
  
  .images-grid {
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  }
}

/* Utilidades */
.mt-4 {
  margin-top: 2rem;
}
</style>

<div class="content-header">
    <div class="content-header-title">
        <h1>Gestión de Imágenes - Sección Acerca de</h1>
        <p>Administra las imágenes utilizadas en las diferentes secciones de "Acerca de".</p>
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
        <h2>Subir Nueva Imagen</h2>
    </div>
    <div class="card-body">
        <form action="" method="post" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="seccion">Sección</label>
                    <select id="seccion" name="seccion" class="form-control" required>
                        <option value="">Seleccione una sección</option>
                        <?php foreach ($secciones as $seccion): ?>
                            <option value="<?php echo $seccion['id']; ?>"><?php echo $seccion['nombre']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group col-md-4">
                    <label for="imagen_id">Imagen</label>
                    <select id="imagen_id" name="imagen_id" class="form-control" required disabled>
                        <option value="">Seleccione primero una sección</option>
                    </select>
                </div>
                
                <div class="form-group col-md-4">
                    <label for="imagen">Archivo de Imagen</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="imagen" name="imagen" accept="image/jpeg,image/png,image/gif" required>
                        <label class="custom-file-label" for="imagen">Seleccionar archivo</label>
                    </div>
                    <small class="form-text text-muted">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 5MB.</small>
                </div>
            </div>
            
            <button type="submit" name="subir_imagen" class="btn btn-primary">
                <i class="fas fa-upload"></i> Subir Imagen
            </button>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h2>Imágenes Actuales</h2>
    </div>
    <div class="card-body">
        <div class="sections-tabs">
            <ul class="nav nav-tabs" id="sectionsTabs" role="tablist">
                <?php foreach ($secciones as $index => $seccion): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $index === 0 ? 'active' : ''; ?>" id="<?php echo $seccion['id']; ?>-tab" data-toggle="tab" href="#<?php echo $seccion['id']; ?>" role="tab" aria-controls="<?php echo $seccion['id']; ?>" aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>">
                            <i class="<?php echo $seccion['icono']; ?>"></i> <?php echo $seccion['nombre']; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            
            <div class="tab-content" id="sectionsTabsContent">
                <?php foreach ($secciones as $index => $seccion): ?>
                    <div class="tab-pane fade <?php echo $index === 0 ? 'show active' : ''; ?>" id="<?php echo $seccion['id']; ?>" role="tabpanel" aria-labelledby="<?php echo $seccion['id']; ?>-tab">
                        <div class="section-description">
                            <p><?php echo $seccion['descripcion']; ?></p>
                        </div>
                        
                        <div class="images-grid">
                            <?php foreach ($seccion['imagenes'] as $imagenId => $imagenNombre): ?>
                                <?php
                                // Determinar la ruta de la imagen
                                if ($seccion['id'] === 'equipo') {
                                    $rutaImagen = SITE_URL . '/assets/img/team/' . $imagenId . '.jpg';
                                    $rutaImagenFisica = '../../assets/img/team/' . $imagenId . '.jpg';
                                } elseif ($seccion['id'] === 'instalaciones') {
                                    $rutaImagen = SITE_URL . '/assets/img/facilities/' . $imagenId . '.jpg';
                                    $rutaImagenFisica = '../../assets/img/facilities/' . $imagenId . '.jpg';
                                } else {
                                    $rutaImagen = SITE_URL . '/assets/img/' . $imagenId . '.jpg';
                                    $rutaImagenFisica = '../../assets/img/' . $imagenId . '.jpg';
                                }
                                
                                // Verificar si existe la imagen
                                $imagenExiste = file_exists($rutaImagenFisica);
                                
                                // Si no existe con extensión jpg, probar con png
                                if (!$imagenExiste) {
                                    if ($seccion['id'] === 'equipo') {
                                        $rutaImagen = SITE_URL . '/assets/img/team/' . $imagenId . '.png';
                                        $rutaImagenFisica = '../../assets/img/team/' . $imagenId . '.png';
                                    } elseif ($seccion['id'] === 'instalaciones') {
                                        $rutaImagen = SITE_URL . '/assets/img/facilities/' . $imagenId . '.png';
                                        $rutaImagenFisica = '../../assets/img/facilities/' . $imagenId . '.png';
                                    } else {
                                        $rutaImagen = SITE_URL . '/assets/img/' . $imagenId . '.png';
                                        $rutaImagenFisica = '../../assets/img/' . $imagenId . '.png';
                                    }
                                    
                                    $imagenExiste = file_exists($rutaImagenFisica);
                                }
                                
                                // Si no existe con extensión png, probar con gif
                                if (!$imagenExiste) {
                                    if ($seccion['id'] === 'equipo') {
                                        $rutaImagen = SITE_URL . '/assets/img/team/' . $imagenId . '.gif';
                                        $rutaImagenFisica = '../../assets/img/team/' . $imagenId . '.gif';
                                    } elseif ($seccion['id'] === 'instalaciones') {
                                        $rutaImagen = SITE_URL . '/assets/img/facilities/' . $imagenId . '.gif';
                                        $rutaImagenFisica = '../../assets/img/facilities/' . $imagenId . '.gif';
                                    } else {
                                        $rutaImagen = SITE_URL . '/assets/img/' . $imagenId . '.gif';
                                        $rutaImagenFisica = '../../assets/img/' . $imagenId . '.gif';
                                    }
                                    
                                    $imagenExiste = file_exists($rutaImagenFisica);
                                }
                                ?>
                                
                                <div class="image-card">
                                    <div class="image-preview">
                                        <?php if ($imagenExiste): ?>
                                            <img src="<?php echo $rutaImagen; ?>" alt="<?php echo $imagenNombre; ?>">
                                        <?php else: ?>
                                            <div class="no-image">
                                                <i class="fas fa-image"></i>
                                                <span>No hay imagen</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="image-info">
                                        <h4><?php echo $imagenNombre; ?></h4>
                                        <p class="image-id">ID: <?php echo $imagenId; ?></p>
                                        <?php if ($imagenExiste): ?>
                                            <p class="image-status status-active">Imagen cargada</p>
                                        <?php else: ?>
                                            <p class="image-status status-pending">Imagen pendiente</p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="image-actions">
                                        <?php if ($imagenExiste): ?>
                                            <a href="<?php echo $rutaImagen; ?>" class="btn btn-sm btn-info" target="_blank">
                                                <i class="fas fa-eye"></i> Ver
                                            </a>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-sm btn-primary upload-btn" 
                                                data-seccion="<?php echo $seccion['id']; ?>" 
                                                data-imagen="<?php echo $imagenId; ?>" 
                                                data-nombre="<?php echo $imagenNombre; ?>">
                                            <i class="fas fa-upload"></i> <?php echo $imagenExiste ? 'Reemplazar' : 'Subir'; ?>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal para subir imagen -->
<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">Subir Imagen</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <p id="modal-description"></p>
                    
                    <input type="hidden" id="modal-seccion" name="seccion">
                    <input type="hidden" id="modal-imagen-id" name="imagen_id">
                    
                    <div class="form-group">
                        <label for="modal-imagen">Archivo de Imagen</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="modal-imagen" name="imagen" accept="image/jpeg,image/png,image/gif" required>
                            <label class="custom-file-label" for="modal-imagen">Seleccionar archivo</label>
                        </div>
                        <small class="form-text text-muted">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 5MB.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" name="subir_imagen" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Subir Imagen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Manejar cambio de sección para cargar las imágenes correspondientes
        const seccionSelect = document.getElementById('seccion');
        const imagenSelect = document.getElementById('imagen_id');
        
        // Datos de las secciones e imágenes
        const secciones = <?php echo json_encode($secciones); ?>;
        
        seccionSelect.addEventListener('change', function() {
            const seccionId = this.value;
            
            // Limpiar select de imágenes
            imagenSelect.innerHTML = '<option value="">Seleccione una imagen</option>';
            
            if (seccionId) {
                // Habilitar select de imágenes
                imagenSelect.disabled = false;
                
                // Buscar la sección seleccionada
                const seccion = secciones.find(s => s.id === seccionId);
                
                if (seccion && seccion.imagenes) {
                    // Agregar opciones de imágenes
                    for (const [id, nombre] of Object.entries(seccion.imagenes)) {
                        const option = document.createElement('option');
                        option.value = id;
                        option.textContent = nombre;
                        imagenSelect.appendChild(option);
                    }
                }
            } else {
                // Deshabilitar select de imágenes
                imagenSelect.disabled = true;
            }
        });
        
        // Manejar cambio de archivo para mostrar el nombre
        const fileInputs = document.querySelectorAll('.custom-file-input');
        fileInputs.forEach(input => {
            input.addEventListener('change', function() {
                const fileName = this.files[0].name;
                const label = this.nextElementSibling;
                label.textContent = fileName;
            });
        });
        
        // Manejar botones de subir imagen
        const uploadButtons = document.querySelectorAll('.upload-btn');
        uploadButtons.forEach(button => {
            button.addEventListener('click', function() {
                const seccion = this.getAttribute('data-seccion');
                const imagen = this.getAttribute('data-imagen');
                const nombre = this.getAttribute('data-nombre');
                
                // Configurar modal
                document.getElementById('modal-seccion').value = seccion;
                document.getElementById('modal-imagen-id').value = imagen;
                document.getElementById('modal-description').textContent = `Subir imagen para: ${nombre} (${imagen})`;
                
                // Mostrar modal (requiere Bootstrap JS)
                $('#uploadModal').modal('show');
            });
        });
        
        // Manejar pestañas
        const tabLinks = document.querySelectorAll('.nav-link');
        const tabPanes = document.querySelectorAll('.tab-pane');
        
        tabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                
                // Desactivar todas las pestañas
                tabLinks.forEach(tab => {
                    tab.classList.remove('active');
                    tab.setAttribute('aria-selected', 'false');
                });
                
                // Ocultar todos los paneles
                tabPanes.forEach(pane => {
                    pane.classList.remove('show', 'active');
                });
                
                // Activar la pestaña actual
                this.classList.add('active');
                this.setAttribute('aria-selected', 'true');
                
                // Mostrar el panel correspondiente
                const target = this.getAttribute('href');
                document.querySelector(target).classList.add('show', 'active');
            });
        });
        
        // Cerrar alertas
        const closeButtons = document.querySelectorAll('.alert .close');
        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const alert = this.closest('.alert');
                alert.classList.remove('show');
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 150);
            });
        });
    });
</script>

<?php include '../includes/footer.php'; ?>