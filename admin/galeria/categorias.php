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
$pageTitle = 'Categorías de Galería';

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

// Procesar formulario de nueva categoría
$mensaje = '';
$tipoMensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    
    if (empty($nombre)) {
        $mensaje = "Por favor, ingrese un nombre para la categoría.";
        $tipoMensaje = "danger";
    } else {
        try {
            // Verificar si ya existe una categoría con ese nombre
            $stmt = $db->prepare("SELECT id FROM galeria_categorias WHERE nombre = :nombre");
            $stmt->execute(['nombre' => $nombre]);
            
            if ($stmt->fetch()) {
                $mensaje = "Ya existe una categoría con ese nombre. Por favor, elija otro.";
                $tipoMensaje = "danger";
            } else {
                // Insertar nueva categoría
                $stmt = $db->prepare("
                    INSERT INTO galeria_categorias (nombre, descripcion)
                    VALUES (:nombre, :descripcion)
                ");
                
                $stmt->execute([
                    'nombre' => $nombre,
                    'descripcion' => $descripcion
                ]);
                
                $mensaje = "Categoría creada correctamente.";
                $tipoMensaje = "success";
                
                // Recargar categorías
                $stmt = $db->query("SELECT * FROM galeria_categorias ORDER BY nombre ASC");
                $categorias = $stmt->fetchAll();
            }
        } catch (PDOException $e) {
            $mensaje = "Error al crear la categoría: " . $e->getMessage();
            $tipoMensaje = "danger";
        }
    }
}

// Procesar formulario de edición de categoría
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'editar') {
    $id = $_POST['id'];
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    
    if (empty($nombre)) {
        $mensaje = "Por favor, ingrese un nombre para la categoría.";
        $tipoMensaje = "danger";
    } else {
        try {
            // Verificar si ya existe otra categoría con ese nombre
            $stmt = $db->prepare("SELECT id FROM galeria_categorias WHERE nombre = :nombre AND id != :id");
            $stmt->execute(['nombre' => $nombre, 'id' => $id]);
            
            if ($stmt->fetch()) {
                $mensaje = "Ya existe otra categoría con ese nombre. Por favor, elija otro.";
                $tipoMensaje = "danger";
            } else {
                // Actualizar categoría
                $stmt = $db->prepare("
                    UPDATE galeria_categorias
                    SET nombre = :nombre, descripcion = :descripcion
                    WHERE id = :id
                ");
                
                $stmt->execute([
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                    'id' => $id
                ]);
                
                $mensaje = "Categoría actualizada correctamente.";
                $tipoMensaje = "success";
                
                // Recargar categorías
                $stmt = $db->query("SELECT * FROM galeria_categorias ORDER BY nombre ASC");
                $categorias = $stmt->fetchAll();
            }
        } catch (PDOException $e) {
            $mensaje = "Error al actualizar la categoría: " . $e->getMessage();
            $tipoMensaje = "danger";
        }
    }
}

// Eliminar categoría
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    
    try {
        // Verificar si hay imágenes en esta categoría
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM galeria WHERE categoria_id = :id");
        $stmt->execute(['id' => $id]);
        $total = $stmt->fetch()['total'];
        
        if ($total > 0) {
            $mensaje = "No se puede eliminar la categoría porque contiene imágenes. Mueva o elimine las imágenes primero.";
            $tipoMensaje = "danger";
        } else {
            // Eliminar categoría
            $stmt = $db->prepare("DELETE FROM galeria_categorias WHERE id = :id");
            $stmt->execute(['id' => $id]);
            
            $mensaje = "Categoría eliminada correctamente.";
            $tipoMensaje = "success";
            
            // Recargar categorías
            $stmt = $db->query("SELECT * FROM galeria_categorias ORDER BY nombre ASC");
            $categorias = $stmt->fetchAll();
        }
    } catch (PDOException $e) {
        $mensaje = "Error al eliminar la categoría: " . $e->getMessage();
        $tipoMensaje = "danger";
    }
}

// Incluir header
include '../includes/header.php';
?>

<div class="content-header">
    <div class="content-header-title">
        <h1>Categorías de Galería</h1>
        <p>Administra las categorías para organizar las imágenes de la galería.</p>
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

<div>
    <button type="button" class="btn btn-cv btn-primary" data-toggle="modal" data-target="#nuevaCategoriaModal">
        <i class="fas fa-plus"></i> Nueva Categoría
    </button>
</div>

<div class="card">
    <div class="card-header">
        <h2>Listado de Categorías</h2>
        <div class="card-header-actions">
            <div class="input-group">
                <div class="form-group flex-grow search-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="form-control search-input" id="searchInput" name="searchInput" placeholder="Buscar categorías...">
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <?php if (count($categorias) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Imágenes</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categorias as $categoria): ?>
                            <?php
                            // Contar imágenes en esta categoría
                            $stmt = $db->prepare("SELECT COUNT(*) as total FROM galeria WHERE categoria_id = :id");
                            $stmt->execute(['id' => $categoria['id']]);
                            $totalImagenes = $stmt->fetch()['total'];
                            ?>
                            <tr>
                                <td><?php echo $categoria['id']; ?></td>
                                <td><?php echo htmlspecialchars($categoria['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($categoria['descripcion'] ?? 'Sin descripción'); ?></td>
                                <td>
                                    <a href="index.php?categoria=<?php echo $categoria['id']; ?>">
                                        <?php echo $totalImagenes; ?> imágenes
                                    </a>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-info btn-editar" 
                                                data-id="<?php echo $categoria['id']; ?>"
                                                data-nombre="<?php echo htmlspecialchars($categoria['nombre']); ?>"
                                                data-descripcion="<?php echo htmlspecialchars($categoria['descripcion'] ?? ''); ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="#" class="btn btn-sm btn-danger btn-delete" data-id="<?php echo $categoria['id']; ?>" data-imagenes="<?php echo $totalImagenes; ?>">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                No hay categorías disponibles. Cree una nueva categoría para comenzar.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Nueva Categoría -->
<div class="modal fade" id="nuevaCategoriaModal" tabindex="-1" role="dialog" aria-labelledby="nuevaCategoriaModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-between">
                <h5 class="modal-title" id="nuevaCategoriaModalLabel">Nueva Categoría</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="post">
                <input type="hidden" name="accion" value="crear">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nombre">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                        <small class="form-text text-muted">Una breve descripción de la categoría (opcional).</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Categoría -->
<div class="modal fade" id="editarCategoriaModal" tabindex="-1" role="dialog" aria-labelledby="editarCategoriaModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header d-flex justify-between">
                <h5 class="modal-title" id="editarCategoriaModalLabel">Editar Categoría</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="post">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="id" id="editar_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="editar_nombre">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editar_nombre" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="editar_descripcion">Descripción</label>
                        <textarea class="form-control" id="editar_descripcion" name="descripcion" rows="3"></textarea>
                        <small class="form-text text-muted">Una breve descripción de la categoría (opcional).</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    :root {
        --primary: #002147; /* Azul UNAM */
        --primary-light: #003366;
        --accent: #ffcc00;
        --gray-200: #e9ecef;
        --gray-600: #6c757d;
        --gray-700: #495057;
        --gray-800: #343a40;
    }

    .fade {
        display: none;
    }

    /* MODAL ESTILOS MAMALONES */
    .modal-content {
        border-radius: 0.3rem;
        box-shadow: 0 1px 15px rgba(0, 0, 0, 0.2);
        overflow: hidden;
    }

    .modal-header {
        background: linear-gradient(90deg, var(--primary), var(--primary-light));
        color: #fff;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #e0e0e0;
    }

    .modal-header .modal-title {
        font-weight: bold;
        font-size: 1.25rem;
    }

    .modal-header .close {
        font-size: 1.4rem;
        padding: 0 1rem;
    }

    .close {
        color: #fff;
        background-color: #f00;
        border: 2px solid #fff;
        border-radius: 5px;
        cursor: pointer;
        transition: filter 0.3s;
    }

    .close:hover {
        filter: opacity(0.78);
    }

    .modal-header .close:hover {
        opacity: 1;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-body .form-group {
        margin-bottom: 1.25rem;
    }

    .modal-body label {
        font-weight: 600;
    }

    .form-control {
        border-radius: 0.5rem;
        border: 1px solid #ccc;
        transition: border-color 0.2s ease-in-out;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.15rem rgba(0, 33, 71, 0.2);
    }

    .form-text.text-muted {
        font-size: 0.85rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }

    .modal-footer {
        padding: 1rem 1.5rem;
        background-color: #f9f9f9;
        border-top: 1px solid #e0e0e0;
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
    }

    .modal-footer .btn {
        min-width: 100px;
        border-radius: 0.5rem;
        font-weight: 500;
    }

    .modal-footer .btn-secondary {
        background-color: #e4e4e4;
        color: #333;
        border: none;
    }

    .modal-footer .btn-secondary:hover {
        background-color: #d0d0d0;
    }

    .modal-footer .btn-primary {
        background-color: var(--primary);
        border: none;
    }

    .modal-footer .btn-primary:hover {
        background-color: var(--primary-light);
    }

    /* ESTILOS CONVOCATORIAS */
    .convocatoria-titulo {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stats-card {
        background-color: white;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
        padding: 1.5rem;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        text-align: center;
    }

    .stats-card-icon {
        width: 3rem;
        height: 3rem;
        border-radius: 0.5rem;
        background-color: var(--primary-light);
        color: var(--gray-200);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .stats-card-content h3 {
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0;
        color: var(--gray-800);
    }

    .stats-card-content p {
        margin: 0;
        color: var(--gray-600);
        font-size: 0.875rem;
    }

    .convocatoria-imagen {
        width: 50px;
        height: 50px;
        border-radius: 0.25rem;
        overflow: hidden;
        flex-shrink: 0;
    }

    .convocatoria-imagen img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .convocatoria-fechas {
        font-size: 0.875rem;
        color: var(--gray-700);
    }

    .convocatoria-fechas i {
        width: 1rem;
        text-align: center;
        color: var(--primary);
        margin-right: 0.25rem;
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

    .form-row {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 20px;
        margin-bottom: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        min-width: 150px;
    }

    .flex-grow {
        flex: 1;
    }

    .search-wrapper {
        position: relative;
    }

    .search-icon {
        position: absolute;
        top: 50%;
        left: 15px;
        transform: translateY(-50%);
        color: #aaa;
        font-size: 1rem;
        pointer-events: none;
    }

    .search-input {
        padding-left: 40px !important;
    }

    .form-control {
        border-radius: 30px;
        padding: 10px 15px;
        border: 1px solid #ccc;
        transition: border-color 0.3s ease;
    }

    .form-control:focus {
        border-color: #2980b9;
        outline: none;
        box-shadow: 0 0 5px rgba(41, 128, 185, 0.3);
    }

    .btn-filter {
        border-radius: 30px;
        padding: 10px 20px;
    }

    @media (max-width: 768px) {
        .form-row {
            flex-direction: column;
        }

        .form-group {
            width: 100%;
        }
    }

    .btn-exit {
        margin-top: 20px;
    }

    .btn-cv {
        display: flex;
        justify-content: center;
        padding: 15px;
        font-size: 15px;
        margin: 20px 0; 
        width: 100%;
    }

    .d-flex {
        display: flex;
        align-items: center;
    }

    .justify-between {
        justify-content: space-between;
    }
</style>

<!-- Requiere jQuery y Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JS (usa versión compatible con tu CSS) -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>

// Búsqueda de categorías
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                const tableRows = document.querySelectorAll('tbody tr');
                
                tableRows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
        }

$(document).ready(function () {
    // Asegurar que solo un modal esté abierto a la vez
    $('#nuevaCategoriaModal').on('show.bs.modal', function () {
        $('#editarCategoriaModal').modal('hide');
    });

    $('#editarCategoriaModal').on('show.bs.modal', function () {
        $('#nuevaCategoriaModal').modal('hide');
    });

    // Editar categoría: poblar modal con datos
    $('.btn-editar').on('click', function () {
        var id = $(this).data('id');
        var nombre = $(this).data('nombre');
        var descripcion = $(this).data('descripcion');

        $('#editar_id').val(id);
        $('#editar_nombre').val(nombre);
        $('#editar_descripcion').val(descripcion);

        $('#editarCategoriaModal').modal('show');
    });

    // Confirmar eliminación (solo si es necesario mostrar alerta)
    $('.btn-delete').on('click', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        var imagenes = $(this).data('imagenes');

        if (imagenes > 0) {
            alert('No se puede eliminar la categoría porque contiene imágenes.');
        } else if (confirm('¿Estás seguro de que deseas eliminar esta categoría?')) {
            window.location.href = '?eliminar=' + id;
        }
    });
});
</script>



<?php include '../includes/footer.php'; ?>