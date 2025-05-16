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

// Verificar si se proporcionó un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: " . SITE_URL . "/admin/galeria/");
    exit;
}

$id = $_GET['id'];

// Título de la página
$pageTitle = 'Editar Imagen';

// Obtener datos de la imagen
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
    
    // Verificar si la columna 'estado' existe en la tabla galeria
    $columnaEstadoExiste = false;
    $stmt = $db->prepare("SHOW COLUMNS FROM galeria LIKE 'estado'");
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        $columnaEstadoExiste = true;
    }
    
    $stmt = $db->prepare("
        SELECT g.*, gc.nombre as categoria_nombre 
        FROM galeria g
        JOIN galeria_categorias gc ON g.categoria_id = gc.id
        WHERE g.id = :id
    ");
    $stmt->execute(['id' => $id]);
    $imagen = $stmt->fetch();
    
    if (!$imagen) {
        header("Location: " . SITE_URL . "/admin/galeria/");
        exit;
    }
    
    // Si la columna estado no existe, asignar un valor predeterminado
    if (!$columnaEstadoExiste && !isset($imagen['estado'])) {
        $imagen['estado'] = 1; // Activo por defecto
    }
    
    // Obtener todas las categorías para el selector
    $stmt = $db->query("SELECT * FROM galeria_categorias ORDER BY nombre ASC");
    $categorias = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $mensaje = "Error al obtener la imagen: " . $e->getMessage();
    $tipoMensaje = "danger";
}

// Procesar formulario
$mensaje = '';
$tipoMensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $categoria_id = $_POST['categoria_id'];
    $estado = isset($_POST['estado']) ? 1 : 0;
    
    // Validar datos
    if (empty($titulo) || !is_numeric($categoria_id)) {
        $mensaje = "Por favor, complete todos los campos obligatorios.";
        $tipoMensaje = "danger";
    } else {
        try {
            // Procesar nueva imagen si se ha subido
            $archivo = $imagen['archivo']; // Mantener la imagen actual por defecto
            if (isset($_FILES['nueva_imagen']) && $_FILES['nueva_imagen']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../../uploads/galeria/';
                
                // Crear directorio si no existe
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileName = uniqid() . '_' . basename($_FILES['nueva_imagen']['name']);
                $uploadFile = $uploadDir . $fileName;
                
                // Verificar tipo de archivo
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $fileType = $_FILES['nueva_imagen']['type'];
                
                // Si el tipo MIME no es confiable, verificar la extensión
                if (!in_array($fileType, $allowedTypes)) {
                    $ext = strtolower(pathinfo($_FILES['nueva_imagen']['name'], PATHINFO_EXTENSION));
                    $isValidExt = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
                    
                    if (!$isValidExt) {
                        $mensaje = "Tipo de archivo no permitido. Solo se permiten imágenes JPG, PNG y GIF.";
                        $tipoMensaje = "danger";
                    } else {
                        // Forzar el tipo MIME basado en la extensión
                        switch ($ext) {
                            case 'jpg':
                            case 'jpeg':
                                $fileType = 'image/jpeg';
                                break;
                            case 'png':
                                $fileType = 'image/png';
                                break;
                            case 'gif':
                                $fileType = 'image/gif';
                                break;
                        }
                    }
                }
                
                if (empty($mensaje)) {
                    if (move_uploaded_file($_FILES['nueva_imagen']['tmp_name'], $uploadFile)) {
                        // Eliminar imagen anterior
                        $oldFile = $uploadDir . $imagen['archivo'];
                        if (file_exists($oldFile)) {
                            unlink($oldFile);
                        }
                        $archivo = $fileName;
                    } else {
                        $mensaje = "Error al subir la imagen. Código: " . $_FILES['nueva_imagen']['error'];
                        $tipoMensaje = "danger";
                    }
                }
            }
            
            // Si no hay errores, actualizar en la base de datos
            if (empty($mensaje)) {
                // Preparar la consulta SQL según si existe la columna estado
                if ($columnaEstadoExiste) {
                    $stmt = $db->prepare("
                        UPDATE galeria 
                        SET titulo = :titulo, 
                            descripcion = :descripcion, 
                            categoria_id = :categoria_id, 
                            archivo = :archivo,
                            estado = :estado
                        WHERE id = :id
                    ");
                    
                    $params = [
                        'titulo' => $titulo,
                        'descripcion' => $descripcion,
                        'categoria_id' => $categoria_id,
                        'archivo' => $archivo,
                        'estado' => $estado,
                        'id' => $id
                    ];
                } else {
                    $stmt = $db->prepare("
                        UPDATE galeria 
                        SET titulo = :titulo, 
                            descripcion = :descripcion, 
                            categoria_id = :categoria_id, 
                            archivo = :archivo
                        WHERE id = :id
                    ");
                    
                    $params = [
                        'titulo' => $titulo,
                        'descripcion' => $descripcion,
                        'categoria_id' => $categoria_id,
                        'archivo' => $archivo,
                        'id' => $id
                    ];
                }
                
                $stmt->execute($params);
                
                $mensaje = "Imagen actualizada correctamente.";
                $tipoMensaje = "success";
                
                // Actualizar datos de la imagen
                $stmt = $db->prepare("
                    SELECT g.*, gc.nombre as categoria_nombre 
                    FROM galeria g
                    JOIN galeria_categorias gc ON g.categoria_id = gc.id
                    WHERE g.id = :id
                ");
                $stmt->execute(['id' => $id]);
                $imagen = $stmt->fetch();
                
                // Si la columna estado no existe, asignar un valor predeterminado
                if (!$columnaEstadoExiste && !isset($imagen['estado'])) {
                    $imagen['estado'] = 1; // Activo por defecto
                }
            }
        } catch (PDOException $e) {
            $mensaje = "Error al actualizar la imagen: " . $e->getMessage();
            $tipoMensaje = "danger";
        }
    }
}

// Incluir header
include '../includes/header.php';
?>

<div class="content-header">
    <div class="content-header-title">
        <h1>Editar Imagen</h1>
        <p>Modifica los datos de la imagen seleccionada.</p>
    </div>
    <div class="content-header-actions">
        <a href="index.php?categoria=<?php echo $imagen['categoria_id']; ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
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

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h2>Formulario de Imagen</h2>
            </div>
            <div class="card-body">
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="titulo">Título <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="titulo" name="titulo" value="<?php echo htmlspecialchars($imagen['titulo']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo htmlspecialchars($imagen['descripcion'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="categoria_id">Categoría <span class="text-danger">*</span></label>
                        <select class="form-control" id="categoria_id" name="categoria_id" required>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?php echo $categoria['id']; ?>" <?php echo $imagen['categoria_id'] == $categoria['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($categoria['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="nueva_imagen">Reemplazar Imagen</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="nueva_imagen" name="nueva_imagen" accept="image/jpeg,image/png,image/gif">
                            <label class="custom-file-label" for="nueva_imagen">Seleccionar nueva imagen</label>
                        </div>
                        <small class="form-text text-muted">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 5MB.</small>
                    </div>
                    
                    <?php if ($columnaEstadoExiste): ?>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="estado" name="estado" <?php echo isset($imagen['estado']) && $imagen['estado'] ? 'checked' : ''; ?>>
                            <label class="custom-control-label" for="estado">Imagen Activa</label>
                        </div>
                        <small class="form-text text-muted">Las imágenes inactivas no se mostrarán en la galería pública.</small>
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Actualizar Imagen
                        </button>
                        <a href="index.php?categoria=<?php echo $imagen['categoria_id']; ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h2>Vista Previa</h2>
            </div>
            <div class="card-body text-center">
                <img src="<?php echo SITE_URL; ?>/uploads/galeria/<?php echo $imagen['archivo']; ?>" alt="<?php echo htmlspecialchars($imagen['titulo']); ?>" class="img-fluid img-thumbnail mb-3">
                
                <div class="image-info">
                    <p><strong>Nombre del archivo:</strong> <?php echo $imagen['archivo']; ?></p>
                    <p><strong>Fecha de subida:</strong> <?php echo date('d/m/Y H:i', strtotime($imagen['fecha_subida'])); ?></p>
                    <p><strong>Categoría:</strong> <?php echo htmlspecialchars($imagen['categoria_nombre']); ?></p>
                    <?php if ($columnaEstadoExiste): ?>
                    <p><strong>Estado:</strong> 
                        <?php if (isset($imagen['estado']) && $imagen['estado']): ?>
                            <span class="badge badge-success">Activa</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">Inactiva</span>
                        <?php endif; ?>
                    </p>
                    <?php endif; ?>
                </div>
                
                <div class="mt-3">
                    <a href="<?php echo SITE_URL; ?>/uploads/galeria/<?php echo $imagen['archivo']; ?>" class="btn btn-info btn-sm" target="_blank">
                        <i class="fas fa-external-link-alt"></i> Ver Tamaño Completo
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos para la página de edición de imagen */
    .row {
        display: flex;
        flex-wrap: wrap;
        margin-right: -15px;
        margin-left: -15px;
    }
    
    .col-md-8 {
        flex: 0 0 66.666667%;
        max-width: 66.666667%;
        padding-right: 15px;
        padding-left: 15px;
    }
    
    .col-md-4 {
        flex: 0 0 33.333333%;
        max-width: 33.333333%;
        padding-right: 15px;
        padding-left: 15px;
    }
    
    .form-group {
        margin-bottom: 1rem;
    }
    
    .custom-file {
        position: relative;
        display: inline-block;
        width: 100%;
        height: calc(1.5em + 0.75rem + 2px);
        margin-bottom: 0;
    }
    
    .custom-file-input {
        position: relative;
        z-index: 2;
        width: 100%;
        height: calc(1.5em + 0.75rem + 2px);
        margin: 0;
        opacity: 0;
    }
    
    .custom-file-label {
        position: absolute;
        top: 0;
        right: 0;
        left: 0;
        z-index: 1;
        height: calc(1.5em + 0.75rem + 2px);
        padding: 0.5rem 0.75rem;
        font-weight: 400;
        line-height: 1.5;
        color: var(--gray-700);
        background-color: white;
        border: 1px solid var(--gray-300);
        border-radius: 0.375rem;
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
        height: calc(1.5em + 0.75rem);
        padding: 0.5rem 0.75rem;
        line-height: 1.5;
        color: var(--gray-700);
        content: "Examinar";
        background-color: var(--gray-100);
        border-left: inherit;
        border-radius: 0 0.375rem 0.375rem 0;
    }
    
    .custom-switch {
        padding-left: 2.25rem;
    }
    
    .custom-control {
        position: relative;
        display: block;
        min-height: 1.5rem;
        padding-left: 1.5rem;
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
    }
    
    .custom-control-label::before {
        position: absolute;
        top: 0.25rem;
        left: -1.5rem;
        display: block;
        width: 1rem;
        height: 1rem;
        pointer-events: none;
        content: "";
        background-color: #fff;
        border: 1px solid #adb5bd;
    }
    
    .custom-control-label::after {
        position: absolute;
        top: 0.25rem;
        left: -1.5rem;
        display: block;
        width: 1rem;
        height: 1rem;
        content: "";
        background: no-repeat 50% / 50% 50%;
    }
    
    .custom-switch .custom-control-label::before {
        left: -2.25rem;
        width: 1.75rem;
        pointer-events: all;
        border-radius: 0.5rem;
    }
    
    .custom-switch .custom-control-label::after {
        top: calc(0.25rem + 2px);
        left: calc(-2.25rem + 2px);
        width: calc(1rem - 4px);
        height: calc(1rem - 4px);
        background-color: #adb5bd;
        border-radius: 0.5rem;
        transition: transform 0.15s ease-in-out, background-color 0.15s ease-in-out;
    }
    
    .custom-switch .custom-control-input:checked ~ .custom-control-label::after {
        background-color: #fff;
        transform: translateX(0.75rem);
    }
    
    .img-fluid {
        max-width: 100%;
        height: auto;
    }
    
    .img-thumbnail {
        padding: 0.25rem;
        background-color: #fff;
        border: 1px solid var(--gray-300);
        border-radius: 0.375rem;
        max-width: 100%;
        height: auto;
    }
    
    .text-center {
        text-align: center;
    }
    
    .mb-3 {
        margin-bottom: 1rem;
    }
    
    .mt-3 {
        margin-top: 1rem;
    }
    
    .image-info {
        text-align: left;
        background-color: var(--gray-100);
        padding: 1rem;
        border-radius: 0.375rem;
        margin-bottom: 1rem;
    }
    
    .image-info p {
        margin-bottom: 0.5rem;
    }
    
    .image-info p:last-child {
        margin-bottom: 0;
    }
    
    .badge {
        display: inline-block;
        padding: 0.25em 0.4em;
        font-size: 75%;
        font-weight: 700;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.25rem;
    }
    
    .badge-success {
        color: #fff;
        background-color: #28a745;
    }
    
    .badge-secondary {
        color: #fff;
        background-color: #6c757d;
    }
    
    /* Estilos para el formulario */
    .form-control {
        display: block;
        width: 100%;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        line-height: 1.5;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
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
        font-size: 0.875rem;
        line-height: 1.5;
        border-radius: 0.375rem;
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
    
    .card {
        position: relative;
        display: flex;
        flex-direction: column;
        min-width: 0;
        word-wrap: break-word;
        background-color: #fff;
        background-clip: border-box;
        border: 1px solid rgba(0, 0, 0, 0.125);
        border-radius: 0.375rem;
        margin-bottom: 1.5rem;
    }
    
    .card-header {
        padding: 0.75rem 1.25rem;
        margin-bottom: 0;
        background-color: rgba(0, 0, 0, 0.03);
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
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
        border-radius: 0.375rem;
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
    
    .content-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    
    .content-header-title h1 {
        margin-bottom: 0.25rem;
        font-size: 1.5rem;
    }
    
    .content-header-title p {
        margin-bottom: 0;
        color: #6c757d;
    }
    
    .content-header-actions {
        display: flex;
        gap: 0.5rem;
    }
    
    .mt-4 {
        margin-top: 1.5rem;
    }
    
    .text-danger {
        color: #dc3545;
    }
</style>

<script>
    // Script para mostrar el nombre del archivo seleccionado
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('nueva_imagen').addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                var fileName = e.target.files[0].name;
                var label = document.querySelector('label[for="nueva_imagen"]');
                label.textContent = fileName;
                
                // Validar tamaño (5MB máximo)
                if (e.target.files[0].size > 5 * 1024 * 1024) {
                    alert('La imagen es demasiado grande. El tamaño máximo permitido es 5MB.');
                    this.value = '';
                    label.textContent = 'Seleccionar nueva imagen';
                }
            }
        });
    });
</script>

<?php include '../includes/footer.php'; ?>