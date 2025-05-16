<!-- Footer -->
<footer class="footer">
    <div class="container footer-container">
        <div>
            <div class="footer-logo">
                <img src="<?php echo SITE_URL; ?>/assets/img/logo_cea_negro-bg.png" alt="Logo CEA" class="invert-color">
                <span>Centro de Estudios Avanzados</span>
            </div>
            <p class="footer-about">El Centro de Estudios Avanzados (CEA) se especializa en la formación y capacitación en el área de Carnes y Embutidos, brindando a nuestros estudiantes las herramientas necesarias para desarrollar sus habilidades en esta importante industria.</p>
            <div class="footer-social">
                <a href="#" class="footer-social-icon"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="footer-social-icon"><i class="fab fa-twitter"></i></a>
                <a href="#" class="footer-social-icon"><i class="fab fa-instagram"></i></a>
                <a href="#" class="footer-social-icon"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
        
        <div>
            <h3 class="footer-title">Enlaces Rápidos</h3>
            <ul class="footer-links">
                <li><a href="<?php echo SITE_URL; ?>"><i class="fas fa-chevron-right"></i> Inicio</a></li>
                <li><a href="<?php echo SITE_URL; ?>/pages/about/mision-vision.php"><i class="fas fa-chevron-right"></i> Acerca de</a></li>
                <li><a href="<?php echo SITE_URL; ?>/pages/courses/index.php"><i class="fas fa-chevron-right"></i> Cursos</a></li>
                <li><a href="<?php echo SITE_URL; ?>/pages/announcements/index.php"><i class="fas fa-chevron-right"></i> Convocatorias</a></li>
                <li><a href="<?php echo SITE_URL; ?>/pages/contact.php"><i class="fas fa-chevron-right"></i> Contacto</a></li>
            </ul>
        </div>
        
        <div>
            <h3 class="footer-title">Contacto</h3>
            <div class="footer-contact-item">
                <div class="footer-contact-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div>
                    Av. Universidad 3000, Ciudad Universitaria, Coyoacán, 04510 Ciudad de México, CDMX
                </div>
            </div>
            
            <div class="footer-contact-item">
                <div class="footer-contact-icon">
                    <i class="fas fa-phone"></i>
                </div>
                <div>
                    +52 (55) 1234-5678
                </div>
            </div>
            
            <div class="footer-contact-item">
                <div class="footer-contact-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div>
                    contacto@cea.edu.mx
                </div>
            </div>
        </div>
        
        <div>
            <h3 class="footer-title">Boletín Informativo</h3>
            <p class="footer-about">Suscríbete a nuestro boletín para recibir las últimas noticias y actualizaciones sobre el área de Carnes y Embutidos, así como otras áreas del CEA.</p>
            <div class="footer-newsletter">
                <form class="newsletter-form" action="<?php echo SITE_URL; ?>/subscribe.php" method="post">
                    <input type="email" name="email" class="newsletter-input" placeholder="Tu correo electrónico" required>
                    <button type="submit" class="newsletter-btn">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="footer-bottom">
        <div class="container">
            <p>&copy; <span id="copyright-year"><?php echo date('Y'); ?></span> Centro de Estudios Avanzados. Todos los derechos reservados.</p>
            <div id="admin-access" style="display: none;">
                <a href="<?php echo SITE_URL; ?>/admin/login.php" class="admin-access-link">
                    <i class="fas fa-lock"></i> Acceso Administrador
                </a>
            </div>
        </div>
    </div>
</footer>

<script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>

<!-- Script para acceso oculto al panel de administrador -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const copyrightYear = document.getElementById('copyright-year');
    const adminAccess = document.getElementById('admin-access');
    let clickCount = 0;
    let lastClickTime = 0;
    
    // Añadir efecto de pulso al año del copyright
    copyrightYear.addEventListener('mouseenter', function() {
        this.classList.add('copyright-pulse');
    });
    
    copyrightYear.addEventListener('mouseleave', function() {
        this.classList.remove('copyright-pulse');
    });
    
    copyrightYear.addEventListener('click', function(e) {
        e.preventDefault();
        
        const currentTime = new Date().getTime();
        
        // Reiniciar contador si pasaron más de 2 segundos desde el último clic
        if (currentTime - lastClickTime > 2000) {
            clickCount = 0;
        }
        
        clickCount++;
        lastClickTime = currentTime;
        
        // Efecto visual al hacer clic
        this.classList.add('copyright-clicked');
        setTimeout(() => {
            this.classList.remove('copyright-clicked');
        }, 300);
        
        // Mostrar acceso admin después de 5 clics rápidos
        if (clickCount >= 5) {
            // Animación de aparición
            adminAccess.style.display = 'block';
            adminAccess.classList.add('admin-access-show');
            
            // Ocultar después de 5 segundos
            setTimeout(function() {
                adminAccess.classList.remove('admin-access-show');
                setTimeout(() => {
                    adminAccess.style.display = 'none';
                    clickCount = 0;
                }, 500);
            }, 5000);
        }
    });
    
    // Combinación de teclas: Ctrl + Alt + A
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.altKey && e.key === 'a') {
            window.location.href = '<?php echo SITE_URL; ?>/admin/login.php';
        }
    });
});
</script>

<style>
/* Estilos para el acceso de administrador */
#copyright-year {
    position: relative;
    transition: color 0.3s ease;
}

#admin-access {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: rgba(0, 0, 0, 0.8);
    padding: 12px 20px;
    border-radius: 30px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    z-index: 1000;
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.5s ease, transform 0.5s ease;
}

.admin-access-show {
    opacity: 1 !important;
    transform: translateY(0) !important;
}

.admin-access-link {
    color: white;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.admin-access-link i {
    font-size: 1.2rem;
}
</style>

<?php if (isset($extraJS)) echo $extraJS; ?>
</body>
</html>
