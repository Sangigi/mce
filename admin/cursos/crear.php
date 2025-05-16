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
$pageTitle = 'Nuevo Curso';

// Procesar formulario
$mensaje = '';
$tipoMensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $instructor = trim($_POST['instructor']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $horario = trim($_POST['horario']);
    $lugar = trim($_POST['lugar']);
    $cupo = intval($_POST['cupo']);
    $precio = floatval($_POST['precio']);
    $estado = $_POST['estado'];
    
    // Validar datos
    if (empty($titulo) || empty($descripcion) || empty($instructor) || empty($fecha_inicio) || empty($fecha_fin) || empty($horario) || empty($lugar)) {
        $mensaje = "Por favor, complete todos los campos obligatorios.";
        $tipoMensaje = "danger";
    } else {
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
            
            // Procesar imagen si se ha subido
            $imagen = '';
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../../uploads/cursos/';
                
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
            
            // Si no hay errores, insertar en la base de datos
            if (empty($mensaje)) {
                $stmt = $db->prepare("
                    INSERT INTO cursos (titulo, descripcion, instructor, fecha_inicio, fecha_fin, horario, lugar, cupo, precio, estado, imagen)
                    VALUES (:titulo, :descripcion, :instructor, :fecha_inicio, :fecha_fin, :horario, :lugar, :cupo, :precio, :estado, :imagen)
                ");
                
                $stmt->execute([
                    'titulo' => $titulo,
                    'descripcion' => $descripcion,
                    'instructor' => $instructor,
                    'fecha_inicio' => $fecha_inicio,
                    'fecha_fin' => $fecha_fin,
                    'horario' => $horario,
                    'lugar' => $lugar,
                    'cupo' => $cupo,
                    'precio' => $precio,
                    'estado' => $estado,
                    'imagen' => $imagen
                ]);
                
                $mensaje = "Curso creado correctamente.";
                $tipoMensaje = "success";
                
                // Redireccionar después de 2 segundos
                header("refresh:2;url=index.php");
            }
        } catch (PDOException $e) {
            $mensaje = "Error al crear el curso: " . $e->getMessage();
            $tipoMensaje = "danger";
        }
    }
}

// Incluir header
include '../includes/header.php';
?>

<div class="content-header">
    <div class="content-header-title">
        <h1>Nuevo Curso</h1>
        <p>Crea un nuevo curso para el Centro de Estudios Avanzados.</p>
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
        <h2>Formulario de Curso</h2>
    </div>
    <div class="card-body">
        <form action="" method="post" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group col-md-12">
                    <label for="titulo">Título <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="titulo" name="titulo" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="descripcion">Descripción <span class="text-danger">*</span></label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="5" required></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="instructor">Instructor <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="instructor" name="instructor" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="lugar">Lugar <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="lugar" name="lugar" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="fecha_inicio">Fecha de Inicio <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="fecha_fin">Fecha de Fin <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                </div>
                <div class  id="fecha_fin" name="fecha_fin" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="horario">Horario <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="horario" name="horario" placeholder="Ej: Lunes a Viernes, 18:00 - 20:00" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="cupo">Cupo <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="cupo" name="cupo" min="1" value="20" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="precio">Precio <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input type="number" class="form-control" id="precio" name="precio" min="0" step="0.01" value="0.00" required>
                    </div>
                </div>
                <div class="form-group col-md-4">
                    <label for="estado">Estado <span class="text-danger">*</span></label>
                    <select class="form-control" id="estado" name="estado" required>
                        <option value="abierto">Abierto</option>
                        <option value="completo">Completo</option>
                        <option value="cerrado">Cerrado</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-12">
                    <label for="imagen">Imagen</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="imagen" name="imagen">
                        <label class="custom-file-label" for="imagen">Seleccionar archivo</label>
                    </div>
                    <small class="form-text text-muted">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 2MB.</small>
                </div>
            </div>
            
            <div class="form-group mt-4 d-flex justify-between">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar Curso
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
.form-row {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 20px;
}

.form-group.col-md-4 {
    flex: 1 1 calc(33.333% - 20px);
    min-width: 220px;
    display: flex;
    flex-direction: column;
}

.form-group label {
    margin-bottom: 6px;
    font-weight: 500;
    color: #333;
}

.form-group input,
.form-group select {
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 16px;
    transition: border-color 0.3s;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #0d47a1;
    outline: none;
}

/* Input-group para el campo precio */
.input-group {
    display: flex;
    align-items: center;
}

.input-group-prepend .input-group-text {
    background-color: #eee;
    border: 1px solid #ccc;
    border-right: none;
    padding: 10px 12px;
    border-radius: 8px 0 0 8px;
}

.input-group .form-control {
    border-radius: 0 8px 8px 0;
    border-left: none;
}

/* Responsive */
@media (max-width: 768px) {
    .form-group.col-md-4 {
        flex: 1 1 100%;
    }
}

.btn-exit {
        margin: 20px 0;
    }
</style>

<script>
    // Script para mostrar el nombre del archivo seleccionado
    document.addEventListener('DOMContentLoaded', function() {
        // Imagen
        document.getElementById('imagen').addEventListener('change', function(e) {
            var fileName = e.target.files[0].name;
            var label = document.querySelector('label[for="imagen"]');
            label.textContent = fileName;
        });
    });
</script>

<?php include '../includes/footer.php'; ?>