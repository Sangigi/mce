<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$currentPage = 'galeria';
$pageTitle = 'Galería';
$pageDescription = 'Explora nuestra galería de imágenes con eventos, instalaciones y actividades del Centro de Estudios Avanzados.';

// Obtener la conexión a la base de datos
$db = getDB();

// Obtener categorías de galería
$categorias = fetchAll("SELECT * FROM galeria_categorias ORDER BY nombre ASC");

// Obtener imágenes de la galería
$categoria_id = isset($_GET['categoria']) ? (int) cleanInput($_GET['categoria']) : 0;

$where = '';
$params = [];

if ($categoria_id > 0) {
    $where = "WHERE g.categoria_id = :categoria_id";
    $params['categoria_id'] = $categoria_id;
}

$query = "
    SELECT g.*, c.nombre AS categoria_nombre 
    FROM galeria g
    LEFT JOIN galeria_categorias c ON g.categoria_id = c.id
    $where
    ORDER BY g.fecha_creacion DESC
";

$imagenes = fetchAll($query, $params);

include '../includes/header.php';
?>

<!-- Page Header -->
<!-- <section class="page-header">
    <div class="container">
        <h1>Galería</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Inicio</a></li>
                <li class="breadcrumb-item active" aria-current="page">Galería</li>
            </ol>
        </nav>
    </div>
</section> -->

<!-- Page Header -->
<section class="hero" style="padding: var(--space-lg) 0;">
  <div class="container">
    <h1 class="hero-title">Galeria</h1>
    <p class="hero-subtitle">Principios rectores del Centro de Estudios Avanzados</p>
  </div>
</section>

<!-- Gallery Section -->
<section class="section">
    <div class="container">
        <div class="gallery-intro">
            <h2>Nuestra Galería</h2>
            <p>Explora nuestra colección de imágenes que muestran la vida académica, eventos, instalaciones y actividades del Centro de Estudios Avanzados.</p>
        </div>
        
        <div class="gallery-filters">
            <ul class="filter-tabs">
                    <a class="tab-item" href="<?php echo SITE_URL; ?>/pages/gallery.php">Todas</a>
                <?php foreach ($categorias as $categoria): ?>
                        <a class="tab-item" href="<?php echo SITE_URL; ?>/pages/gallery.php?categoria=<?php echo $categoria['id']; ?>">
                            <?php echo htmlspecialchars($categoria['nombre']); ?>
                        </a>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="gallery-grid" id="gallery-container">
            <?php if (!empty($imagenes)): ?>
                <?php foreach ($imagenes as $imagen): ?>
                    <div class="gallery-item" data-category="<?php echo $imagen['categoria_id']; ?>">
                        <a href="<?php echo SITE_URL; ?>/uploads/galeria/<?php echo htmlspecialchars($imagen['archivo']); ?>" class="gallery-link" data-lightbox="gallery" data-title="<?php echo htmlspecialchars($imagen['titulo']); ?>">
                            <img src="<?php echo SITE_URL; ?>/uploads/galeria/<?php echo htmlspecialchars($imagen['archivo']); ?>" alt="Imagen" class="gallery-image">
                            <div class="gallery-overlay">
                                <div class="gallery-info">
                                    <h2><?php echo htmlspecialchars($imagen['titulo']); ?></h2>
                                    <!-- <p><strong>Categoría:</strong> <?php echo htmlspecialchars($imagen['categoria_nombre']); ?></p> -->
                                    <?php if (!empty($imagen['descripcion'])): ?>
                                        <p><?php echo nl2br(htmlspecialchars($imagen['descripcion'])); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info" style="grid-column: 1 / -1;">
                    No hay imágenes disponibles en esta categoría.
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Lightbox Script -->
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/lightbox.min.css">
<style>
    .gallery-item {
    position: relative;
    overflow: hidden;
}

.gallery-image {
    width: 100%;
    height: auto;
    transition: transform 0.3s ease;
}

.gallery-item:hover .gallery-image {
    transform: scale(1.05);
}

.gallery-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    opacity: 0;
    transition: opacity 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    text-align: center;
    padding: 20px;
}

.gallery-info h2 {
    margin: 0;
    font-size: 1.5em;
    color: white;
}

.gallery-info p {
    margin: 0.5em 0 0;
    font-size: 0.85em;
}

.gallery-item:hover .gallery-overlay {
    opacity: 1;
}

/* About Tabs Section */
.about-tabs {
  display: flex;
  justify-content: center;
  margin-bottom: var(--space-lg);
}

.tab-item {
  color: var(--black);
  text-decoration: none;
  padding: var(--space-sm) var(--space-md);
  border: 1px solid var(--black);
  border-radius: var(--radius-md);
  margin: 0 var(--space-sm);
}

.gallery-filters {
  margin-top: 2rem;
  margin-bottom: 2rem;
  text-align: center;
}

.filter-tabs {
  list-style: none;
  padding: 0;
  display: inline-flex;
  flex-wrap: wrap;
  gap: 1rem;
  justify-content: center;
  margin: 0;
}

.tab-item {
  color: var(--black);
  text-decoration: none;
  padding: var(--space-sm) var(--space-md);
  border: 1px solid var(--black);
  border-radius: var(--radius-md);
  background-color: #f5f5f5;
  transition: background-color 0.3s ease;
}

.tab-item:hover {
  background-color: #e0e0e0;
}

</style>
<script src="<?php echo SITE_URL; ?>/assets/js/lightbox.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    lightbox.option({
        'resizeDuration': 200,
        'wrapAround': true,
        'albumLabel': "Imagen %1 de %2",
        'fadeDuration': 300
    });
});
</script>

<?php include '../includes/footer.php'; ?>
