<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Obtener ID del curso
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    redirect(SITE_URL . '/pages/courses/index.php');
}

// Obtener datos del curso
global $db;
$curso = $db->getRow("SELECT * FROM cursos WHERE id = :id", ['id' => $id]);

if (!$curso || $curso['estado'] !== 'abierto') {
    redirect(SITE_URL . '/pages/courses/index.php');
}

$currentPage = 'curso-inscripcion';
$pageTitle = 'Inscripción: ' . $curso['titulo'];
$pageDescription = 'Formulario de inscripción para el curso ' . $curso['titulo'];

// Procesar formulario de inscripción
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = cleanInput($_POST['nombre']);
    $apellidos = cleanInput($_POST['apellidos']);
    $email = cleanInput($_POST['email']);
    $telefono = cleanInput($_POST['telefono']);
    $institucion = cleanInput($_POST['institucion']);
    
    // Validar campos
    if (empty($nombre) || empty($apellidos) || empty($email) || empty($telefono)) {
        $error = 'Por favor, complete todos los campos obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Por favor, introduce un email válido.';
    } else {
        // En un entorno real, aquí se guardaría la inscripción en la base de datos
        // y se enviaría un email de confirmación
        
        // Simulamos éxito
        $success = true;
    }
}

include '../../includes/header.php';
?>

<!-- Page Header -->
<!-- <section class="page-header">
    <div class="container">
        <h1>Inscripción al Curso</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Inicio</a></li>
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/courses/index.php">Cursos</a></li>
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/courses/detalle.php?id=<?php echo $id; ?>"><?php echo $curso['titulo']; ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Inscripción</li>
            </ol>
        </nav>
    </div>
</section> -->

<!-- Page Header -->
<section class="hero" style="padding: var(--space-lg) 0;">
  <div class="container">
    <h1 class="hero-title">Inscripción al Curso</h1>
    <p class="hero-subtitle">Principios rectores del Centro de Estudios Avanzados</p>
  </div>
</section>

<!-- Registration Section -->
<section class="section">
    <div class="container">
        <?php if ($success): ?>
            <div class="registration-success">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2>¡Inscripción Exitosa!</h2>
                <p>Tu solicitud de inscripción al curso "<?php echo $curso['titulo']; ?>" ha sido recibida correctamente.</p>
                <p>Hemos enviado un correo electrónico a <strong><?php echo $email; ?></strong> con los detalles de tu inscripción y los pasos a seguir.</p>
                <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
                
                <div class="success-actions">
                    <a href="<?php echo SITE_URL; ?>/pages/courses/index.php" class="btn btn-primary">
                        Ver Más Cursos
                    </a>
                    <a href="<?php echo SITE_URL; ?>" class="btn btn-outline">
                        Volver al Inicio
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="registration-container">
                <div class="registration-info">
                    <h2>Información del Curso</h2>
                    
                    <div class="course-summary">
                        <h3><?php echo $curso['titulo']; ?></h3>
                        
                        <div class="summary-details">
                            <div class="summary-item">
                                <div class="summary-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="summary-text">
                                    <span>Instructor:</span>
                                    <strong><?php echo $curso['instructor']; ?></strong>
                                </div>
                            </div>
                            
                            <div class="summary-item">
                                <div class="summary-icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="summary-text">
                                    <span>Fecha:</span>
                                    <strong><?php echo formatDate($curso['fecha_inicio']); ?> - <?php echo formatDate($curso['fecha_fin']); ?></strong>
                                </div>
                            </div>
                            
                            <div class="summary-item">
                                <div class="summary-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="summary-text">
                                    <span>Horario:</span>
                                    <strong><?php echo $curso['horario']; ?></strong>
                                </div>
                            </div>
                            
                            <div class="summary-item">
                                <div class="summary-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="summary-text">
                                    <span>Lugar:</span>
                                    <strong><?php echo $curso['lugar']; ?></strong>
                                </div>
                            </div>
                            
                            <div class="summary-item">
                                <div class="summary-icon">
                                    <i class="fas fa-tag"></i>
                                </div>
                                <div class="summary-text">
                                    <span>Precio:</span>
                                    <strong>
                                        <?php 
                                            if ($curso['precio'] > 0) {
                                                echo '$' . number_format($curso['precio'], 2) . ' MXN';
                                            } else {
                                                echo 'Gratuito';
                                            }
                                        ?>
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="registration-notes">
                        <h4>Notas Importantes:</h4>
                        <ul>
                            <li>La inscripción está sujeta a disponibilidad de cupo.</li>
                            <li>Recibirás un correo de confirmación una vez que tu inscripción sea procesada.</li>
                            <li>Para cualquier duda, puedes contactarnos al correo: <a href="mailto:cursos@cea.edu.mx">cursos@cea.edu.mx</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="registration-form-container">
                    <h2>Formulario de Inscripción</h2>
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form action="" method="post" class="registration-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nombre" class="form-label">Nombre(s) <span class="required">*</span></label>
                                <input type="text" id="nombre" name="nombre" class="form-input" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="apellidos" class="form-label">Apellidos <span class="required">*</span></label>
                                <input type="text" id="apellidos" name="apellidos" class="form-input" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email" class="form-label">Email <span class="required">*</span></label>
                                <input type="email" id="email" name="email" class="form-input" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="telefono" class="form-label">Teléfono <span class="required">*</span></label>
                                <input type="tel" id="telefono" name="telefono" class="form-input" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="institucion" class="form-label">Institución / Empresa</label>
                            <input type="text" id="institucion" name="institucion" class="form-input">
                        </div>
                        
                        <div class="form-group">
                            <label for="comentarios" class="form-label">Comentarios o Requerimientos Especiales</label>
                            <textarea id="comentarios" name="comentarios" class="form-textarea" rows="3"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" id="terminos" name="terminos" required>
                                <label for="terminos">He leído y acepto los <a href="#" target="_blank">términos y condiciones</a> y la <a href="#" target="_blank">política de privacidad</a>. <span class="required">*</span></label>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <a href="<?php echo SITE_URL; ?>/pages/courses/detalle.php?id=<?php echo $id; ?>" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Enviar Inscripción</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../../includes/footer.php'; ?>