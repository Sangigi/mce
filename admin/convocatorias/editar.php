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
    header("Location: " . SITE_URL . "/admin/convocatorias/");
    exit;
}

$id = $_GET['id'];

// Título de la página
$pageTitle = 'Editar Convocatoria';

// Obtener datos de la convocatoria
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
    
    $stmt = $db->prepare("SELECT * FROM convocatorias WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $convocatoria = $stmt->fetch();
    
    if (!$convocatoria) {
        header("Location: " . SITE_URL . "/admin/convocatorias/");
        exit;
    }
} catch (PDOException $e) {
    $mensaje = "Error al obtener la convocatoria: " . $e->getMessage();
    $tipoMensaje = "danger";
}

// Procesar formulario
$mensaje = '';
$tipoMensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $estado = $_POST['estado'];
    
    // Validar datos
    if (empty($titulo) || empty($descripcion) || empty($fecha_inicio) || empty($fecha_fin)) {
        $mensaje = "Por favor, complete todos los campos obligatorios.";
        $tipoMensaje = "danger";
    } else {
        try {
            // Procesar imagen si se ha subido
            $imagen = $convocatoria['imagen']; // Mantener la imagen actual por defecto
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../../uploads/convocatorias/';
                
                // Crear directorio si no existe
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileName = uniqid() . '_' . basename($_FILES['imagen']['name']);
                $uploadFile = $uploadDir . $fileName;
                
                // Verificar tipo de archivo
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (in_array($_FILES['imagen']['type'], $allowedTypes)) {
                    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $uploadFile)) {
                        // Eliminar imagen anterior si existe
                        if (!empty($convocatoria['imagen'])) {
                            $oldFile = $uploadDir . $convocatoria['imagen'];
                            if (file_exists($oldFile)) {
                                unlink($oldFile);
                            }
                        }
                        $imagen = $fileName;
                    } else {
                        $mensaje = "Error al subir la imagen.";
                        $tipoMensaje = "danger";
                    }
                } else {
                    $mensaje = "Tipo de archivo no permitido. Solo se permiten imágenes JPG, PNG y GIF.";
                    $tipoMensaje = "danger";
                }
            }
            
            // Procesar documento si se ha subido
            $documento = $convocatoria['documento']; // Mantener el documento actual por defecto
            if (isset($_FILES['documento']) && $_FILES['documento']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../../uploads/documentos/';
                
                // Crear directorio si no existe
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileName = uniqid() . '_' . basename($_FILES['documento']['name']);
                $uploadFile = $uploadDir . $fileName;
                
                // Verificar tipo de archivo
                $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                if (in_array($_FILES['documento']['type'], $allowedTypes)) {
                    if (move_uploaded_file($_FILES['documento']['tmp_name'], $uploadFile)) {
                        // Eliminar documento anterior si existe
                        if (!empty($convocatoria['documento'])) {
                            $oldFile = $uploadDir . $convocatoria['documento'];
                            if (file_exists($oldFile)) {
                                unlink($oldFile);
                            }
                        }
                        $documento = $fileName;
                    } else {
                        $mensaje = "Error al subir el documento.";
                        $tipoMensaje = "danger";
                    }
                } else {
                    $mensaje = "Tipo de archivo no permitido. Solo se permiten documentos PDF y Word.";
                    $tipoMensaje = "danger";
                }
            }
            
            // Si no hay errores, actualizar en la base de datos
            if (empty($mensaje)) {
                $stmt = $db->prepare("
                    UPDATE convocatorias 
                    SET titulo = :titulo, 
                        descripcion = :descripcion, 
                        fecha_inicio = :fecha_inicio, 
                        fecha_fin = :fecha_fin, 
                        estado = :estado, 
                        imagen = :imagen, 
                        documento = :documento
                    WHERE id = :id
                ");
                
                $stmt->execute([
                    'titulo' => $titulo,
                    'descripcion' => $descripcion,
                    'fecha_inicio' => $fecha_inicio,
                    'fecha_fin' => $fecha_fin,
                    'estado' => $estado,
                    'imagen' => $imagen,
                    'documento' => $documento,
                    'id' => $id
                ]);
                
                $mensaje = "Convocatoria actualizada correctamente.";
                $tipoMensaje = "success";
                
                // Actualizar datos de la convocatoria
                $stmt = $db->prepare("SELECT * FROM convocatorias WHERE id = :id");
                $stmt->execute(['id' => $id]);
                $convocatoria = $stmt->fetch();
            }
        } catch (PDOException $e) {
            $mensaje = "Error al actualizar la convocatoria: " . $e->getMessage();
            $tipoMensaje = "danger";
        }
    }
}

// Incluir header
include '../includes/header.php';
?>

<div class="content-header">
    <div class="content-header-title">
        <h1>Editar Convocatoria</h1>
        <p>Modifica los datos de la convocatoria seleccionada.</p>
    </div>
    <div class="content-header-actions">
        <a href="index.php" class="btn btn-exit btn-secondary">
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

<div class="card">
    <div class="card-header">
        <h2>Formulario de Convocatoria</h2>
    </div>
    <div class="card-body">
        <form action="" method="post" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group col-md-12">
                    <label for="titulo">Título <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="titulo" name="titulo" value="<?php echo htmlspecialchars($convocatoria['titulo']); ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="descripcion">Descripción <span class="text-danger">*</span></label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="5" required><?php echo htmlspecialchars($convocatoria['descripcion']); ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="fecha_inicio">Fecha de Inicio <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?php echo $convocatoria['fecha_inicio']; ?>" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="fecha_fin">Fecha de Fin <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?php echo $convocatoria['fecha_fin']; ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="estado">Estado <span class="text-danger">*</span></label>
                    <select class="form-control" id="estado" name="estado" required>
                        <option value="activa" <?php echo $convocatoria['estado'] === 'activa' ? 'selected' : ''; ?>>Activa</option>
                        <option value="cerrada" <?php echo $convocatoria['estado'] === 'cerrada' ? 'selected' : ''; ?>>Cerrada</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group col-md-6">
                <label for="imagen">Imagen</label>
                <div class="mb-2">
                    <img id="previewImagen" src="<?php echo !empty($convocatoria['imagen']) 
                        ? SITE_URL . '/uploads/convocatorias/' . $convocatoria['imagen'] 
                        : 'ruta/icono_archivo.png'; ?>" 
                        alt="Vista previa" 
                        class="img-thumbnail" 
                        style="max-height: 100px;">
                    <?php if (!empty($convocatoria['imagen'])): ?>
                        <p class="mb-0 mt-1">
                            <small>Imagen actual: <?php echo $convocatoria['imagen']; ?></small>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="imagen" name="imagen" accept="image/*">
                    <label class="custom-file-label" for="imagen">Seleccionar nueva imagen</label>
                </div>
                <small class="form-text text-muted">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 2MB.</small>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="documento" name="documento">
                    <label class="custom-file-label" for="documento">Seleccionar nuevo documento</label>
                </div>
                <small class="form-text text-muted">Formatos permitidos: PDF, DOC, DOCX. Tamaño máximo: 5MB.</small>
            </div>
            <div class="form-group mt-4 d-flex justify-between">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Actualizar Convocatoria
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
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

    .card img {
      object-fit: cover;
      border-top-left-radius: 1rem;
      border-top-right-radius: 1rem;
    }

    .btn-exit {
        margin: 20px 0;
    }
</style>

<script>
  document.getElementById('imagen').addEventListener('change', function (e) {
    const file = e.target.files[0];
    const preview = document.getElementById('previewImagen');
    const label = e.target.nextElementSibling;

    if (file) {
      label.textContent = file.name; // actualiza la etiqueta del input

      if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function (e) {
          preview.src = e.target.result;
        };
        reader.readAsDataURL(file);
      } else {
        preview.src = 'ruta/icono_archivo.png'; // opcional: para otro tipo de archivo
      }
    }
  });
</script>

<script>
    // Script para mostrar el nombre del archivo seleccionado
    document.addEventListener('DOMContentLoaded', function() {
        // Imagen
        document.getElementById('imagen').addEventListener('change', function(e) {
            var fileName = e.target.files[0].name;
            var label = document.querySelector('label[for="imagen"]');
            label.textContent = fileName;
        });
        
        // Documento
        document.getElementById('documento').addEventListener('change', function(e) {
            var fileName = e.target.files[0].name;
            var label = document.querySelector('label[for="documento"]');
            label.textContent = fileName;
        });
    });
</script>

<?php include '../includes/footer.php'; ?>