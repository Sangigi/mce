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
$pageTitle = 'Nuevo Evento';

// Procesar formulario
$mensaje = '';
$tipoMensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $hora_inicio = $_POST['hora_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $hora_fin = $_POST['hora_fin'];
    $ubicacion = trim($_POST['ubicacion']);
    $cupo_maximo = !empty($_POST['cupo_maximo']) ? $_POST['cupo_maximo'] : null;
    $destacado = isset($_POST['destacado']) ? 1 : 0;
    $enlace = trim($_POST['enlace'] ?? '');
    $enlace_registro = trim($_POST['enlace_registro'] ?? '');
    $imagen = trim($_POST['imagen'] ?? '');
    $tipo = $_POST['tipo'] ?? '';

    // Validar datos
    if (empty($titulo) || empty($descripcion) || empty($fecha_inicio) || empty($hora_inicio) || empty($fecha_fin) || empty($hora_fin)) {
        $mensaje = "Por favor, complete todos los campos obligatorios.";
        $tipoMensaje = "danger";
    } else {
        // Formatear fechas y horas
        $fecha_inicio_completa = $fecha_inicio . ' ' . $hora_inicio . ':00';
        $fecha_fin_completa = $fecha_fin . ' ' . $hora_fin . ':00';
        
        // Validar que la fecha de fin sea posterior a la de inicio
        if (strtotime($fecha_fin_completa) <= strtotime($fecha_inicio_completa)) {
            $mensaje = "La fecha y hora de finalización debe ser posterior a la fecha y hora de inicio.";
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
                
                // Insertar evento en la base de datos
                $stmt = $db->prepare("
                    INSERT INTO eventos (
                        titulo, 
                        descripcion, 
                        fecha_inicio, 
                        fecha_fin, 
                        ubicacion, 
                        cupo_maximo, 
                        destacado, 
                        usuario_id,
                        enlace,
                        enlace_registro,
                        imagen,
                        tipo
                    ) VALUES (
                        :titulo, 
                        :descripcion, 
                        :fecha_inicio, 
                        :fecha_fin, 
                        :ubicacion, 
                        :cupo_maximo, 
                        :destacado, 
                        :usuario_id,
                        :enlace,
                        :enlace_registro,
                        :imagen,
                        :tipo
                    )
                ");
                
                $stmt->execute([
                    'titulo' => $titulo,
                    'descripcion' => $descripcion,
                    'fecha_inicio' => $fecha_inicio_completa,
                    'fecha_fin' => $fecha_fin_completa,
                    'ubicacion' => $ubicacion,
                    'cupo_maximo' => $cupo_maximo,
                    'destacado' => $destacado,
                    'usuario_id' => $_SESSION['user_id'],
                    'enlace' => $enlace,
                    'enlace_registro' => $enlace_registro,
                    'imagen' => $imagen,
                    'tipo' => $tipo
                ]);
                
                $mensaje = "Evento creado correctamente.";
                $tipoMensaje = "success";
                
                // Redireccionar después de 2 segundos
                header("refresh:2;url=index.php");
                
            } catch (PDOException $e) {
                $mensaje = "Error al crear el evento: " . $e->getMessage();
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
        <h1>Nuevo Evento</h1>
        <p>Crea un nuevo evento para el centro.</p>
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
        <h2>Formulario de Evento</h2>
    </div>
    <div class="card-body">
        <form action="" method="post">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="titulo">Título <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="titulo" name="titulo" value="<?php echo isset($titulo) ? htmlspecialchars($titulo) : ''; ?>" required>
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
                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?php echo isset($fecha_inicio) ? $fecha_inicio : date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="hora_inicio">Hora de Inicio <span class="text-danger">*</span></label>
                    <input type="time" class="form-control" id="hora_inicio" name="hora_inicio" value="<?php echo isset($hora_inicio) ? $hora_inicio : '09:00'; ?>" required>
                </div>
            </div>
        
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="fecha_fin">Fecha de Fin <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?php echo isset($fecha_fin) ? $fecha_fin : date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="hora_fin">Hora de Fin <span class="text-danger">*</span></label>
                    <input type="time" class="form-control" id="hora_fin" name="hora_fin" value="<?php echo isset($hora_fin) ? $hora_fin : '18:00'; ?>" required>
                </div>
            </div>
        
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="cupo_maximo">Cupo Máximo</label>
                    <input type="number" class="form-control" id="cupo_maximo" name="cupo_maximo" min="1" value="<?php echo isset($cupo_maximo) ? $cupo_maximo : ''; ?>">
                    <small class="form-text text-muted">Dejar en blanco si no hay límite.</small>
                </div>
                <div class="form-group col-md-6">
                    <label for="destacado">Destacar evento</label>
                    <div class="custom-control custom-checkbox mt-2">
                        <input type="checkbox" class="custom-control-input" id="destacado" name="destacado" <?php echo isset($destacado) && $destacado ? 'checked' : ''; ?>>
                        <label class="custom-control-label" for="destacado">Mostrar en página principal</label>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="tipo">Tipo de Evento</label>
                    <select class="form-control" id="tipo" name="tipo">
                        <option value="academico" <?php if(isset($tipo) && $tipo == 'academico') echo 'selected'; ?>>Académico</option>
                        <option value="cultural" <?php if(isset($tipo) && $tipo == 'cultural') echo 'selected'; ?>>Cultural</option>
                        <option value="administrativo" <?php if(isset($tipo) && $tipo == 'administrativo') echo 'selected'; ?>>Administrativo</option>
                        <option value="otro" <?php if(!isset($tipo) || $tipo == 'otro') echo 'selected'; ?>>Otro</option>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="imagen">Imagen</label>
                    <input type="file" class="form-control" id="imagen" name="imagen" value="<?php echo isset($imagen) ? htmlspecialchars($imagen) : ''; ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="enlace">Enlace</label>
                    <input type="url" class="form-control" id="enlace" name="enlace" value="<?php echo isset($enlace) ? htmlspecialchars($enlace) : ''; ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="enlace_registro">Enlace de Registro</label>
                    <input type="url" class="form-control" id="enlace_registro" name="enlace_registro" value="<?php echo isset($enlace_registro) ? htmlspecialchars($enlace_registro) : ''; ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción <span class="text-danger">*</span></label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="5" required><?php echo isset($descripcion) ? htmlspecialchars($descripcion) : ''; ?></textarea>
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
        // Copiar fecha de inicio a fecha de fin si está vacía
        const fechaInicio = document.getElementById('fecha_inicio');
        const fechaFin = document.getElementById('fecha_fin');
        
        fechaInicio.addEventListener('change', function() {
            if (fechaFin.value === '') {
                fechaFin.value = this.value;
            }
        });
        
        // Validar que la fecha de fin sea posterior a la de inicio
        const horaInicio = document.getElementById('hora_inicio');
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