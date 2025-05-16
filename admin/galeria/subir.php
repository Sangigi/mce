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
$pageTitle = 'Subir Imágenes';

// Obtener categorías
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
    
    $stmt = $db->query("SELECT * FROM galeria_categorias ORDER BY nombre ASC");
    $categorias = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Error al obtener categorías: " . $e->getMessage();
}

// Procesar formulario
$mensaje = '';
$tipoMensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $categoria_id = $_POST['categoria_id'];
    
    // Validar datos
    if (empty($titulo) || empty($categoria_id)) {
        $mensaje = "Por favor, complete todos los campos obligatorios.";
        $tipoMensaje = "danger";
    } elseif (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] === UPLOAD_ERR_NO_FILE) {
        $mensaje = "Por favor, seleccione una imagen para subir.";
        $tipoMensaje = "danger";
    } else {
        // Validar archivo
        $archivo = $_FILES['imagen'];
        $nombreArchivo = $archivo['name'];
        $tipoArchivo = $archivo['type'];
        $tamanoArchivo = $archivo['size'];
        $archivoTemporal = $archivo['tmp_name'];
        $error = $archivo['error'];
        
        // Obtener extensión
        $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
        
        // Extensiones permitidas
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($extension, $extensionesPermitidas)) {
            $mensaje = "Tipo de archivo no permitido. Solo se permiten imágenes JPG, JPEG, PNG y GIF.";
            $tipoMensaje = "danger";
        } elseif ($tamanoArchivo > 5242880) { // 5MB
            $mensaje = "El archivo es demasiado grande. El tamaño máximo permitido es 5MB.";
            $tipoMensaje = "danger";
        } elseif ($error !== UPLOAD_ERR_OK) {
            $mensaje = "Error al subir el archivo. Por favor, inténtelo de nuevo.";
            $tipoMensaje = "danger";
        } else {
            try {
                // Crear directorio si no existe
                $directorioDestino = '../../uploads/galeria/';
                if (!file_exists($directorioDestino)) {
                    mkdir($directorioDestino, 0755, true);
                }
                
                // Generar nombre único para el archivo
                $nuevoNombreArchivo = uniqid() . '_' . time() . '.' . $extension;
                $rutaDestino = $directorioDestino . $nuevoNombreArchivo;
                
                // Mover archivo
                if (move_uploaded_file($archivoTemporal, $rutaDestino)) {
                    // Insertar en la base de datos
                    $stmt = $db->prepare("
                        INSERT INTO galeria (titulo, descripcion, archivo, categoria_id, usuario_id)
                        VALUES (:titulo, :descripcion, :archivo, :categoria_id, :usuario_id)
                    ");
                    
                    $stmt->execute([
                        'titulo' => $titulo,
                        'descripcion' => $descripcion,
                        'archivo' => $nuevoNombreArchivo,
                        'categoria_id' => $categoria_id,
                        'usuario_id' => $_SESSION['user_id']
                    ]);
                    
                    $mensaje = "Imagen subida correctamente.";
                    $tipoMensaje = "success";
                    
                    // Limpiar formulario
                    $titulo = '';
                    $descripcion = '';
                    $categoria_id = '';
                } else {
                    $mensaje = "Error al mover el archivo. Por favor, inténtelo de nuevo.";
                    $tipoMensaje = "danger";
                }
            } catch (PDOException $e) {
                $mensaje = "Error al guardar la imagen en la base de datos: " . $e->getMessage();
                $tipoMensaje = "danger";
            }
        }
    }
}

// Incluir header
include '../includes/header.php';
?>

<div class="content-header">
    <div class="content-header-title">
        <h1>Subir Imágenes</h1>
        <p>Sube nuevas imágenes a la galería del sitio web.</p>
    </div>
    <div class="content-header-actions">
        <a href="index.php" class="btn btn-exit btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver a la Galería
        </a>
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
        <h2>Formulario de Subida</h2>
    </div>
    <div class="card-body">
        <?php if (count($categorias) > 0): ?>
            <form action="" method="post" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="titulo">Título <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="titulo" name="titulo" value="<?php echo isset($titulo) ? htmlspecialchars($titulo) : ''; ?>" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="categoria_id">Categoría <span class="text-danger">*</span></label>
                        <select class="form-control" id="categoria_id" name="categoria_id" required>
                            <option value="">Seleccione una categoría</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?php echo $categoria['id']; ?>" <?php echo isset($categoria_id) && $categoria_id == $categoria['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($categoria['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo isset($descripcion) ? htmlspecialchars($descripcion) : ''; ?></textarea>
                    <small class="form-text text-muted">Una breve descripción de la imagen (opcional).</small>
                </div>
                
                <div class="form-group">
                    <label for="imagen">Imagen <span class="text-danger">*</span></label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="imagen" name="imagen" accept=".jpg,.jpeg,.png,.gif" required>
                        <label class="custom-file-label" for="imagen">Seleccionar archivo</label>
                    </div>
                    <small class="form-text text-muted">
                        Formatos permitidos: JPG, JPEG, PNG, GIF. Tamaño máximo: 5MB.
                    </small>
                </div>
                
                <div class="form-group d-flex justify-between mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Subir Imagen
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        <?php else: ?>
            <div class="alert alert-warning">
                No hay categorías disponibles. Debe <a href="categorias.php">crear una categoría</a> antes de subir imágenes.
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    /* Layout general */
    .form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    /* Columnas */
    .col-md-6 {
        flex: 1 1 48%;
    }

    .col-md-12 {
        flex: 1 1 100%;
    }

    /* Inputs y Textareas */
    .form-control {
        display: block;
        width: 100%;
        padding: 0.6rem 1rem;
        font-size: 1rem;
        color: #333;
        background-color: #fff;
        border: 1px solid #ccc;
        border-radius: 0.5rem;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
        transition: border-color 0.3s, box-shadow 0.3s;
    }

    .form-control:focus {
        border-color: #004b8d;
        box-shadow: 0 0 0 3px rgba(0, 75, 141, 0.2);
        outline: none;
    }

    /* Etiquetas */
    label {
        font-weight: 600;
        margin-bottom: 0.5rem;
        display: block;
        color: #222;
    }

    /* Botones */
    .btn {
        border-radius: 0.5rem;
        padding: 0.6rem 1.25rem;
        font-weight: 600;
        transition: background-color 0.2s, transform 0.2s;
    }

    .btn-primary:hover {
        background-color: #003f77;
        transform: scale(1.02);
    }

    .btn-secondary:hover {
        background-color: #6c757d;
        transform: scale(1.02);
    }

    /* Inputs de archivo */
    .custom-file {
        position: relative;
        display: block;
        width: 100%;
        height: auto;
    }

    .custom-file-input {
        opacity: 0;
        width: 100%;
        height: 100%;
        position: absolute;
        top: 0;
        left: 0;
        z-index: 2;
        cursor: pointer;
    }

    .custom-file-label {
        display: block;
        width: 100%;
        padding: 0.6rem 1rem;
        border: 1px solid #ccc;
        border-radius: 0.5rem;
        background-color: #f8f9fa;
        color: #555;
        position: relative;
        z-index: 1;
        cursor: pointer;
    }

    .custom-file-label::after {
        content: "📁";
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1rem;
        color: #666;
    }

    /* Textos pequeños */
    .form-text {
        font-size: 0.85rem;
        color: #666;
    }

    /* Alertas */
    .alert {
        border-radius: 0.5rem;
        padding: 1rem;
    }

    /* Utilidades */
    .mt-4 {
        margin-top: 2rem;
    }

    .text-danger {
        color: #d9534f;
    }

    .d-flex {
        display: flex;
        align-items: center;
    }

    .justify-between {
        justify-content: space-between;
    }

    .btn-exit {
        margin: 20px 0;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mostrar nombre del archivo seleccionado
        const fileInput = document.getElementById('imagen');
        const fileLabel = document.querySelector('.custom-file-label');
        
        if (fileInput && fileLabel) {
            fileInput.addEventListener('change', function() {
                let fileName = '';
                if (this.files && this.files.length > 0) {
                    fileName = this.files[0].name;
                }
                fileLabel.textContent = fileName || 'Seleccionar archivo';
            });
        }
    });
</script>

<?php include '../includes/footer.php'; ?>