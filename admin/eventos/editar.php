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
    header("Location: " . SITE_URL . "/admin/eventos/");
    exit;
}

$id = $_GET['id'];

// Título de la página
$pageTitle = 'Editar Evento';

// Obtener datos del evento
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
    
    $stmt = $db->prepare("SELECT * FROM eventos WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $evento = $stmt->fetch();
    
    if (!$evento) {
        header("Location: " . SITE_URL . "/admin/eventos/");
        exit;
    }
    
    // Formatear fechas y horas para el formulario
    $fecha_inicio = date('Y-m-d', strtotime($evento['fecha_inicio']));
    $hora_inicio = date('H:i', strtotime($evento['fecha_inicio']));
    $fecha_fin = date('Y-m-d', strtotime($evento['fecha_fin']));
    $hora_fin = date('H:i', strtotime($evento['fecha_fin']));
    
} catch (PDOException $e) {
    $error = "Error al obtener el evento: " . $e->getMessage();
}

// Procesar formulario
$mensaje = '';
$tipoMensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $fecha_inicio_form = $_POST['fecha_inicio'];
    $hora_inicio_form = $_POST['hora_inicio'];
    $fecha_fin_form = $_POST['fecha_fin'];
    $hora_fin_form = $_POST['hora_fin'];
    $ubicacion = trim($_POST['ubicacion']);
    $cupo_maximo = !empty($_POST['cupo_maximo']) ? $_POST['cupo_maximo'] : null;
    $destacado = isset($_POST['destacado']) ? 1 : 0;
    $tipo = $_POST['tipo'] ?? '';
    $enlace = trim($_POST['enlace'] ?? '');
    $enlace_registro = trim($_POST['enlace_registro'] ?? '');

    // Validar datos
    if (empty($titulo) || empty($descripcion) || empty($fecha_inicio_form) || empty($hora_inicio_form) || empty($fecha_fin_form) || empty($hora_fin_form)) {
        $mensaje = "Por favor, complete todos los campos obligatorios.";
        $tipoMensaje = "danger";
    } else {
        $fecha_inicio_completa = $fecha_inicio_form . ' ' . $hora_inicio_form . ':00';
        $fecha_fin_completa = $fecha_fin_form . ' ' . $hora_fin_form . ':00';

        if (strtotime($fecha_fin_completa) <= strtotime($fecha_inicio_completa)) {
            $mensaje = "La fecha y hora de finalización debe ser posterior a la fecha y hora de inicio.";
            $tipoMensaje = "danger";
        } else {
            try {
                // Procesar imagen si se subió una nueva
                $nombre_imagen = $evento['imagen']; // valor por defecto: conservar imagen actual

                if (!empty($_FILES['imagen']['name'])) {
                    $nombre_original = $_FILES['imagen']['name'];
                    $temporal = $_FILES['imagen']['tmp_name'];
                    $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
                    $nombre_imagen = uniqid('evento_') . '.' . $extension;
                    $ruta_destino = __DIR__ . '/../../uploads/eventos/' . $nombre_imagen;

                    // Mover la imagen
                    if (!move_uploaded_file($temporal, $ruta_destino)) {
                        throw new Exception("No se pudo guardar la imagen.");
                    }

                    // Opcional: eliminar imagen anterior
                    if (!empty($evento['imagen'])) {
                        $imagen_anterior = __DIR__ . '/../../uploads/eventos/' . $evento['imagen'];
                        if (file_exists($imagen_anterior)) {
                            unlink($imagen_anterior);
                        }
                    }
                }

                // Actualizar evento
                $stmt = $db->prepare("
                    UPDATE eventos SET 
                        titulo = :titulo, 
                        descripcion = :descripcion, 
                        fecha_inicio = :fecha_inicio, 
                        fecha_fin = :fecha_fin, 
                        ubicacion = :ubicacion, 
                        cupo_maximo = :cupo_maximo, 
                        destacado = :destacado,
                        tipo = :tipo,
                        imagen = :imagen,
                        enlace = :enlace,
                        enlace_registro = :enlace_registro
                    WHERE id = :id
                ");

                $stmt->execute([
                    'titulo' => $titulo,
                    'descripcion' => $descripcion,
                    'fecha_inicio' => $fecha_inicio_completa,
                    'fecha_fin' => $fecha_fin_completa,
                    'ubicacion' => $ubicacion,
                    'cupo_maximo' => $cupo_maximo,
                    'destacado' => $destacado,
                    'tipo' => $tipo,
                    'imagen' => $nombre_imagen,
                    'enlace' => $enlace,
                    'enlace_registro' => $enlace_registro,
                    'id' => $id
                ]);

                $mensaje = "Evento actualizado correctamente.";
                $tipoMensaje = "success";

                // Actualizar datos del evento para mostrar los cambios
                $stmt = $db->prepare("SELECT * FROM eventos WHERE id = :id");
                $stmt->execute(['id' => $id]);
                $evento = $stmt->fetch();

                $fecha_inicio = date('Y-m-d', strtotime($evento['fecha_inicio']));
                $hora_inicio = date('H:i', strtotime($evento['fecha_inicio']));
                $fecha_fin = date('Y-m-d', strtotime($evento['fecha_fin']));
                $hora_fin = date('H:i', strtotime($evento['fecha_fin']));
            } catch (Exception $e) {
                $mensaje = "Error al actualizar el evento: " . $e->getMessage();
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
        <h1>Editar Evento</h1>
        <p>Modifica los datos del evento seleccionado.</p>
    </div>
    <div class="content-header-actions">
        <a href="index.php" class="btn btn-exit btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
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
        <h2>Formulario de Evento</h2>
    </div>
    <div class="card-body">
        <form action="" method="post" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="titulo">Título <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="titulo" name="titulo" value="<?php echo htmlspecialchars($evento['titulo']); ?>" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="ubicacion">Ubicación</label>
                    <input type="text" class="form-control" id="ubicacion" name="ubicacion" value="<?php echo htmlspecialchars($evento['ubicacion'] ?? ''); ?>">
                    <small class="form-text text-muted">Lugar donde se realizará el evento.</small>
                </div>
            </div>
        
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="fecha_inicio">Fecha de Inicio <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="hora_inicio">Hora de Inicio <span class="text-danger">*</span></label>
                    <input type="time" class="form-control" id="hora_inicio" name="hora_inicio" value="<?php echo $hora_inicio; ?>" required>
                </div>
            </div>
        
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="fecha_fin">Fecha de Fin <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?php echo $fecha_fin; ?>" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="hora_fin">Hora de Fin <span class="text-danger">*</span></label>
                    <input type="time" class="form-control" id="hora_fin" name="hora_fin" value="<?php echo $hora_fin; ?>" required>
                </div>
            </div>
        
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="cupo_maximo">Cupo Máximo</label>
                    <input type="number" class="form-control" id="cupo_maximo" name="cupo_maximo" min="1" value="<?php echo $evento['cupo_maximo'] ?? ''; ?>">
                    <small class="form-text text-muted">Dejar en blanco si no hay límite.</small>
                </div>
                <div class="form-group col-md-6">
                    <label for="destacado">Destacar evento</label>
                    <div class="custom-control custom-checkbox mt-2">
                        <input type="checkbox" class="custom-control-input" id="destacado" name="destacado" <?php echo ($evento['destacado'] ?? 0) ? 'checked' : ''; ?>>
                        <label class="custom-control-label" for="destacado">Mostrar en página principal</label>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-12">
                    <label for="tipo">Tipo de evento</label>
                    <select class="form-control" id="tipo" name="tipo">
                        <option value="academico" <?php echo ($evento['tipo'] == 'academico') ? 'selected' : ''; ?>>Académico</option>
                        <option value="cultural" <?php echo ($evento['tipo'] == 'cultural') ? 'selected' : ''; ?>>Cultural</option>
                        <option value="administrativo" <?php echo ($evento['tipo'] == 'administrativo') ? 'selected' : ''; ?>>Administrativo</option>
                        <option value="otro" <?php echo ($evento['tipo'] == 'otro') ? 'selected' : ''; ?>>Otro</option>
                    </select>
                </div>
                <div class="mb-2 mt-3">
                    <img id="previewImagen" src="<?php echo !empty($evento['imagen']) 
                        ? SITE_URL . '/uploads/eventos/' . $evento['imagen'] 
                        : 'ruta/icono_archivo.png'; ?>" 
                        alt="Vista previa" 
                        class="img-thumbnail" 
                        style="max-height: 100px;">
                    <?php if (!empty($evento['imagen'])): ?>
                        <p class="mb-0 mt-1">
                            <small>Imagen actual: <?php echo $evento['imagen']; ?></small>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="imagen" name="imagen" accept="image/*">
                    <label class="custom-file-label" for="imagen">Seleccionar nueva imagen</label>
                </div>
                <small class="form-text text-muted">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 2MB.</small>
                
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="enlace">Enlace del evento</label>
                    <input type="url" class="form-control" id="enlace" name="enlace" value="<?php echo htmlspecialchars($evento['enlace'] ?? ''); ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="enlace_registro">Enlace de registro</label>
                    <input type="url" class="form-control" id="enlace_registro" name="enlace_registro" value="<?php echo htmlspecialchars($evento['enlace_registro'] ?? ''); ?>">
                </div>
            </div>
        
            <div class="form-group">
                <label for="descripcion">Descripción <span class="text-danger">*</span></label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="5" required><?php echo htmlspecialchars($evento['descripcion']); ?></textarea>
            </div>
        
            <div class="form-group mt-4 d-flex justify-between">
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
    document.addEventListener('DOMContentLoaded', function() {
        // Validar que la fecha de fin sea posterior a la de inicio
        const fechaInicio = document.getElementById('fecha_inicio');
        const horaInicio = document.getElementById('hora_inicio');
        const fechaFin = document.getElementById('fecha_fin');
        const horaFin = document.getElementById('hora_fin');
        
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            const fechaInicioCompleta = new Date(fechaInicio.value + 'T' + horaInicio.value);
            const fechaFinCompleta = new Date(fechaFin.value + 'T' + horaFin.value);
            
            if (fechaFinCompleta <= fechaInicioCompleta) {
                e.preventDefault();
                alert('La fecha y hora de finalización debe ser posterior a la fecha y hora de inicio.');
            }
        });
    });
</script>

<?php include '../includes/footer.php'; ?>