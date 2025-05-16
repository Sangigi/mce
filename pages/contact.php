<?php
require_once __DIR__ . '/../includes/config.php';
require_once '../includes/functions.php';

$currentPage = 'contacto';
$pageTitle = 'Contacto';
$pageDescription = 'Contacta con el Centro de Estudios Avanzados para más información sobre nuestros cursos y convocatorias.';

// Procesar formulario de contacto
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = cleanInput($_POST['nombre']);
    $email = cleanInput($_POST['email']);
    $asunto = cleanInput($_POST['asunto']);
    $mensaje = cleanInput($_POST['mensaje']);
    
    // Validar campos
    if (empty($nombre) || empty($email) || empty($asunto) || empty($mensaje)) {
        $error = 'Por favor, complete todos los campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Por favor, introduce un email válido.';
    } else {
        // Enviar email (en un entorno real)
        // mail(ADMIN_EMAIL, "Contacto Web: $asunto", "Nombre: $nombre\nEmail: $email\nMensaje: $mensaje");
        
        // Simulamos éxito
        $success = true;
    }
}

include '../includes/header.php';
?>

<!-- Page Header -->
<section class="hero" style="padding: var(--space-lg) 0;">
  <div class="container">
    <h1 class="hero-title">Contacto</h1>
    <p class="hero-subtitle">Principios rectores del Centro de Estudios Avanzados</p>
  </div>
</section>

<!-- Contact Section -->
<section class="section">
    <div class="container">
        <div class="contact-container">
            <div class="contact-info">
                <h2 class="section-title">Información de Contacto</h2>
                <p class="mb-4">Estamos aquí para responder cualquier pregunta que puedas tener sobre nuestros cursos, convocatorias o cualquier otra información.</p>
                
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="contact-text">
                        <h3>Dirección</h3>
                        <p>Av. Universidad 3000, Ciudad Universitaria, Coyoacán, 04510 Ciudad de México, CDMX</p>
                    </div>
                </div>
                
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="contact-text">
                        <h3>Teléfono</h3>
                        <p>+52 (55) 1234-5678</p>
                    </div>
                </div>
                
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="contact-text">
                        <h3>Email</h3>
                        <p>contacto@cea.edu.mx</p>
                    </div>
                </div>
                
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="contact-text">
                        <h3>Horario de Atención</h3>
                        <p>Lunes a Viernes: 9:00 AM - 6:00 PM</p>
                    </div>
                </div>
                
                <!-- <div class="contact-social">
                    <h3>Síguenos</h3>
                    <div class="social-icons">
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
                    </div>
                </div> -->
            </div>
            
            <div class="contact-form-container">
                <h2 class="section-title">Envíanos un Mensaje</h2>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Tu mensaje ha sido enviado con éxito. Nos pondremos en contacto contigo lo antes posible.
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form action="" method="post" class="contact-form">
                    <div class="form-group">
                        <label for="nombre" class="form-label">Nombre Completo <span class="required">*</span></label>
                        <input type="text" id="nombre" name="nombre" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="asunto" class="form-label">Asunto <span class="required">*</span></label>
                        <input type="text" id="asunto" name="asunto" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="mensaje" class="form-label">Mensaje <span class="required">*</span></label>
                        <textarea id="mensaje" name="mensaje" class="form-textarea" rows="5" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Enviar Mensaje</button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="map-section">
    <div class="container">
        <h2 class="section-title text-center">Nuestra Ubicación</h2>
        <div class="map-container">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3765.2129192570397!2d-99.18956492394826!3d19.690973632193377!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x85d1f5d67d469fd5%3A0x3fd44c85e38cd76e!2sFacultad%20de%20Estudios%20Superiores%20Cuautitl%C3%A1n%20Campo%204!5e0!3m2!1ses-419!2smx!4v1682458123456!5m2!1ses-419!2smx" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>