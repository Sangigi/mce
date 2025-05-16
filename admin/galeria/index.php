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
$pageTitle = 'Gestión de Galería';

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
    
    // Obtener categoría seleccionada o usar la primera
    $categoriaId = isset($_GET['categoria']) && is_numeric($_GET['categoria']) 
        ? $_GET['categoria'] 
        : ($categorias[0]['id'] ?? null);
    
    // Obtener imágenes de la categoría seleccionada
    if ($categoriaId) {
        $stmt = $db->prepare("
            SELECT g.*, gc.nombre as categoria_nombre 
            FROM galeria g
            JOIN galeria_categorias gc ON g.categoria_id = gc.id
            WHERE g.categoria_id = :categoria_id
            ORDER BY g.fecha_subida DESC
        ");
        $stmt->execute(['categoria_id' => $categoriaId]);
        $imagenes = $stmt->fetchAll();
        
        // Obtener nombre de la categoría seleccionada
        $stmt = $db->prepare("SELECT nombre FROM galeria_categorias WHERE id = :id");
        $stmt->execute(['id' => $categoriaId]);
        $categoriaSeleccionada = $stmt->fetch();
    } else {
        $imagenes = [];
        $categoriaSeleccionada = ['nombre' => 'Ninguna categoría disponible'];
    }
    
} catch (PDOException $e) {
    $error = "Error al obtener datos de la galería: " . $e->getMessage();
}

// Eliminar imagen si se solicita
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    
    try {
        // Obtener información de la imagen
        $stmt = $db->prepare("SELECT archivo FROM galeria WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $imagen = $stmt->fetch();
        
        if ($imagen) {
            // Eliminar archivo físico
            $rutaArchivo = '../../uploads/galeria/' . $imagen['archivo'];
            if (file_exists($rutaArchivo)) {
                unlink($rutaArchivo);
            }
            
            // Eliminar registro de la base de datos
            $stmt = $db->prepare("DELETE FROM galeria WHERE id = :id");
            $stmt->execute(['id' => $id]);
            
            $mensaje = "Imagen eliminada correctamente.";
            $tipoMensaje = "success";
            
            // Recargar imágenes
            $stmt = $db->prepare("
                SELECT g.*, gc.nombre as categoria_nombre 
                FROM galeria g
                JOIN galeria_categorias gc ON g.categoria_id = gc.id
                WHERE g.categoria_id = :categoria_id
                ORDER BY g.fecha_subida DESC
            ");
            $stmt->execute(['categoria_id' => $categoriaId]);
            $imagenes = $stmt->fetchAll();
        }
    } catch (PDOException $e) {
        $mensaje = "Error al eliminar la imagen: " . $e->getMessage();
        $tipoMensaje = "danger";
    }
}

// Incluir header
include '../includes/header.php';
?>

<div class="content-header">
    <div class="content-header-title">
        <h1>Gestión de Galería</h1>
        <p>Administra las imágenes de la galería del sitio web.</p>
    </div>
    <div class="content-header-actions">
        <a href="subir.php" class="btn btn-options btn-primary">
            <i class="fas fa-upload"></i> Subir Imágenes
        </a>
        <a href="categorias.php" class="btn btn-secondary">
            <i class="fas fa-folder"></i> Gestionar Categorías
        </a>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<?php if (isset($mensaje)): ?>
    <div class="alert alert-<?php echo $tipoMensaje; ?> alert-dismissible fade show" role="alert">
        <?php echo $mensaje; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Selector de categorías -->
<div class="card mb-4">
    <div class="card-header">
        <h2>Categorías</h2>
    </div>
    <div class="card-body">
        <div class="categoria-selector">
            <?php if (count($categorias) > 0): ?>
                <?php foreach ($categorias as $categoria): ?>
                    <a href="?categoria=<?php echo $categoria['id']; ?>" class="categoria-item <?php echo $categoria['id'] == $categoriaId ? 'active' : ''; ?>">
                        <div class="categoria-icon">
                            <i class="fas fa-folder<?php echo $categoria['id'] == $categoriaId ? '-open' : ''; ?>"></i>
                        </div>
                        <div class="categoria-name">
                            <?php echo $categoria['nombre']; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">No hay categorías disponibles. <a href="categorias.php">Crear una categoría</a>.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Galería de imágenes -->
<div class="card">
    <div class="card-header">
        <h2>Imágenes en "<?php echo htmlspecialchars($categoriaSeleccionada['nombre']); ?>"</h2>
        <div class="card-header-actions">
            <div class="form-group flex-grow search-wrapper">
              <i class="fas fa-search search-icon"></i>
              <input type="text" class="form-control search-input" id="searchInput" name="searchInput" placeholder="Buscar imágenes...">
            </div>
        </div>
    </div>
    <div class="card-body">
        <?php if (count($imagenes) > 0): ?>
            <div class="galeria-grid">
                <?php foreach ($imagenes as $imagen): ?>
                    <div class="galeria-item" data-titulo="<?php echo htmlspecialchars($imagen['titulo']); ?>">
                        <div class="galeria-image">
                            <img src="<?php echo SITE_URL; ?>/uploads/galeria/<?php echo $imagen['archivo']; ?>" alt="<?php echo htmlspecialchars($imagen['titulo']); ?>">
                            <div class="galeria-overlay">
                                <a href="<?php echo SITE_URL; ?>/uploads/galeria/<?php echo $imagen['archivo']; ?>" class="btn btn-sm btn-info" target="_blank" title="Ver imagen">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="editar.php?id=<?php echo $imagen['id']; ?>" class="btn btn-sm btn-primary" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="#" class="btn btn-sm btn-danger btn-delete" data-id="<?php echo $imagen['id']; ?>" data-href="?eliminar=<?php echo $imagen['id']; ?>&categoria=<?php echo $categoriaId; ?>" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        <div class="galeria-info">
                            <h3><?php echo htmlspecialchars($imagen['titulo']); ?></h3>
                            <p class="galeria-meta">
                                <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($imagen['fecha_subida'])); ?></span>
                                <span><i class="fas fa-folder"></i> <?php echo htmlspecialchars($imagen['categoria_nombre']); ?></span>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                No hay imágenes en esta categoría. <a href="subir.php">Subir imágenes</a>.
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .search-wrapper {
        position: relative;
        width: 100%;
    }

    .search-wrapper .search-icon {
        position: absolute;
        top: 50%;
        left: 15px;
        transform: translateY(-50%);
        color: #999;
        font-size: 16px;
        pointer-events: none;
    }

    .search-wrapper .search-input {
        padding-left: 40px !important; /* Espacio para el ícono */
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

    .search-input {
        padding-left: 40px !important;
    
    }
    /* Estilos para la galería */
    .categoria-selector {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .categoria-item {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        border-radius: 0.375rem;
        background-color: var(--gray-100);
        color: var(--gray-700);
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .categoria-item:hover {
        background-color: var(--gray-200);
        color: var(--gray-800);
    }
    
    .categoria-item.active {
        background-color: var(--primary-light);
        color: var(--gray-100);
    }
    
    .categoria-icon {
        margin-right: 0.5rem;
        font-size: 1.25rem;
    }
    
    .categoria-name {
        font-weight: 500;
    }
    
    .galeria-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1.5rem;
    }
    
    .galeria-item {
        border-radius: 0.375rem;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        background-color: white;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .galeria-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .galeria-image {
        position: relative;
        height: 200px;
        overflow: hidden;
    }
    
    .galeria-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .galeria-item:hover .galeria-image img {
        transform: scale(1.05);
    }
    
    .galeria-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .galeria-item:hover .galeria-overlay {
        opacity: 1;
    }
    
    .galeria-info {
        padding: 1rem;
    }
    
    .galeria-info h3 {
        margin: 0 0 0.5rem;
        font-size: 1rem;
        font-weight: 600;
        color: var(--gray-800);
    }
    
    .galeria-meta {
        display: flex;
        gap: 1rem;
        font-size: 0.75rem;
        color: var(--gray-600);
        margin: 0;
    }
    
    .galeria-meta span {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    @media (max-width: 768px) {
        .galeria-grid {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        }
    }
    
    @media (max-width: 576px) {
        .galeria-grid {
            grid-template-columns: 1fr;
        }
    }

    .btn-options {
        margin: 20px 0;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Búsqueda de imágenes
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                const galeriaItems = document.querySelectorAll('.galeria-item');
                
                galeriaItems.forEach(item => {
                    const titulo = item.getAttribute('data-titulo').toLowerCase();
                    if (titulo.includes(searchTerm)) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        }
        
        // Confirmación de eliminación
        const deleteButtons = document.querySelectorAll('.btn-delete');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm('¿Está seguro de que desea eliminar esta imagen? Esta acción no se puede deshacer.')) {
                    window.location.href = this.getAttribute('data-href');
                }
            });
        });
    });
</script>

<?php include '../includes/footer.php'; ?>