<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Obtener ID de la convocatoria
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    redirect(SITE_URL . '/pages/announcements/index.php');
}

// Obtener datos de la convocatoria
global $db;
$convocatoria = $db->getRow("SELECT * FROM convocatorias WHERE id = :id", ['id' => $id]);

if (!$convocatoria) {
    redirect(SITE_URL . '/pages/announcements/index.php');
}

$currentPage = 'convocatoria-detalle';
$pageTitle = $convocatoria['titulo'];
$pageDescription = substr(strip_tags($convocatoria['descripcion']), 0, 160);

include '../../includes/header.php';
?>



<!-- Announcement Detail Section -->
<section class="announcement-section">
  <div class="container">
    <div class="announcement-card">

      <!-- Header Section -->
      <header class="announcement-header">
        <h1 class="announcement-title"><?php echo $convocatoria['titulo']; ?></h1>
        <p class="announcement-meta">
          <span class="announcement-date"><i class="fas fa-calendar-alt"></i> Publicada: <?php echo formatDate($convocatoria['fecha_publicacion']); ?></span>
          <span class="announcement-status <?php echo ($convocatoria['estado'] === 'activa') ? 'active' : 'closed'; ?>">
            <i class="fas <?php echo ($convocatoria['estado'] === 'activa') ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
            <?php echo ($convocatoria['estado'] === 'activa') ? 'Convocatoria Activa' : 'Convocatoria Cerrada'; ?>
          </span>
        </p>
      </header>

      <!-- Image Section -->
      <?php if (!empty($convocatoria['imagen'])): ?>
        <div class="announcement-image">
          <img src="<?php echo SITE_URL; ?>/uploads/convocatorias/<?php echo $convocatoria['imagen']; ?>" alt="Imagen de la convocatoria">
        </div>
      <?php endif; ?>

      <!-- Description Section -->
      <div class="announcement-description">
        <h3>Descripción:</h3>
        <p><?php echo nl2br($convocatoria['descripcion']); ?></p>
      </div>

      <!-- Call to Action Buttons -->
      <div class="announcement-actions">
        <a href="<?php echo SITE_URL; ?>/pages/announcements/index.php" class="btn btn-secondary">
          <i class="fas fa-arrow-left"></i> Volver a Convocatorias
        </a>
        <!-- Document Section -->
      <?php if (!empty($convocatoria['documento'])): ?>
        <div class="announcement-document">
          <a href="<?php echo SITE_URL; ?>/uploads/documentos/<?php echo $convocatoria['documento']; ?>" class="btn btn-download" target="_blank">
            <i class="fas fa-file-pdf"></i> Descargar Documento
          </a>
        </div>
      <?php endif; ?>
        <?php if ($convocatoria['estado'] === 'activa'): ?>
          <a href="<?php echo SITE_URL; ?>/pages/contact.php" class="btn btn-primary">
            <i class="fas fa-envelope"></i> Solicitar Información
          </a>
        <?php endif; ?>
      </div>

    </div>
  </div>
</section>

<style>
/* Announcement Detail Styles */
:root {
  --primary-color: #2c7a7b;
  --primary-hover: #38a169;
  --secondary-color: #718096;
  --secondary-hover: #4a5568;
  --success-color: #38a169;
  --danger-color: #e53e3e;
  --light-bg: #f7fafc;
  --border-color: #e2e8f0;
  --text-color: #2d3748;
  --text-muted: #718096;
  --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  --border-radius: 8px;
  --transition: all 0.3s ease;
}

.announcement-section {
  padding: 3rem 0;
  background-color: var(--light-bg);
}

/* .container {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1.5rem;
} */

.announcement-card {
  background-color: white;
  border-radius: var(--border-radius);
  box-shadow: var(--shadow);
  overflow: hidden;
  margin-bottom: 2rem;
  padding: 2rem;
}

/* Header Styles */
.announcement-header {
  margin-bottom: 1.5rem;
  border-bottom: 1px solid var(--border-color);
  padding-bottom: 1.5rem;
}

.announcement-title {
  font-size: 2rem;
  color: var(--text-color);
  margin-bottom: 1rem;
  line-height: 1.2;
}

.announcement-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  color: var(--text-muted);
  font-size: 0.95rem;
}

.announcement-date,
.announcement-status {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.announcement-status {
  padding: 0.25rem 0.75rem;
  border-radius: 50px;
  font-weight: 500;
}

.announcement-status.active {
  background-color: rgba(56, 161, 105, 0.1);
  color: var(--success-color);
}

.announcement-status.closed {
  background-color: rgba(229, 62, 62, 0.1);
  color: var(--danger-color);
}

/* Image Styles */
.announcement-image {
  margin: 2rem 0;
  border-radius: var(--border-radius);
  overflow: hidden;
  height: auto;
  border: 1px solid var(--border-color);
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.announcement-image img {
  width: 100%;
  object-fit: scale-down;
  display: block;
  max-height: 500px;
    background:rgb(85, 107, 131) !important;

}

/* Description Styles */
.announcement-description {
  margin: 2rem 0;
  line-height: 1.7;
  background-color: #f9fafb;
  padding: 2rem;
  border-radius: var(--border-radius);
  border-top: 4px solid var(--primary-color);
  border-bottom: 4px solid var(--primary-color);
}

.announcement-description h3 {
  font-size: 1.5rem;
  margin-bottom: 1.25rem;
  color: var(--primary-color);
  position: relative;
  padding-bottom: 0.75rem;
}

.announcement-description h3:after {
  content: "";
  position: absolute;
  bottom: 0;
  left: 0;
  width: 50px;
  height: 3px;
  background-color: var(--primary-color);
}

.announcement-description p {
  color: var(--text-color);
  margin-bottom: 1rem;
  font-size: 1.05rem;
}

/* Add some spacing between paragraphs for better readability */
.announcement-description p + p {
  margin-top: 1rem;
}

/* Actions Styles */
.announcement-actions {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--border-color);
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border-radius: var(--border-radius);
  font-weight: 500;
  text-decoration: none;
  transition: var(--transition);
  cursor: pointer;
}

.btn i {
  font-size: 1rem;
}

.btn-primary {
  background-color: var(--azul-unam);
  color: white;
}

.btn-primary:hover {
  background-color: var(--azul-unam-dark);
}

.btn-secondary {
  background-color: var(--gold-unam);
  color: var(--azul-unam);
}

.btn-secondary:hover {
  background-color: var(--azul-unam);
}

.btn-download {
  background-color: white;
  color: var(--text-color);
  border: 1px solid var(--border-color);
}

.btn-download:hover {
  background-color: var(--light-bg);
}

/* Document Section */
.announcement-document {
  display: inline-block;
}

/* Responsive Styles */
@media (max-width: 768px) {
  .announcement-title {
    font-size: 1.75rem;
  }

  .announcement-meta {
    flex-direction: column;
    gap: 0.5rem;
  }

  .announcement-actions {
    flex-direction: column;
  }

  .btn {
    width: 100%;
    justify-content: center;
  }

  .announcement-description {
    padding: 1.5rem;
  }

  .announcement-description h3 {
    font-size: 1.3rem;
  }

  .announcement-image img {
    max-height: 350px;
  }
}

@media (max-width: 480px) {
  .announcement-card {
    padding: 1.5rem;
  }

  .announcement-title {
    font-size: 1.5rem;
  }
}

</style>

<?php include '../../includes/footer.php'; ?>