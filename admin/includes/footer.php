            </main>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar Toggle
            const sidebarToggle = document.getElementById('sidebarToggle');
            const adminSidebar = document.getElementById('adminSidebar');
            const adminContent = document.getElementById('adminContent');
            
            sidebarToggle.addEventListener('click', function() {
                adminSidebar.classList.toggle('collapsed');
                adminContent.classList.toggle('expanded');
                
                // Guardar estado en localStorage
                const isCollapsed = adminSidebar.classList.contains('collapsed');
                localStorage.setItem('sidebarCollapsed', isCollapsed);
            });
            
            // Restaurar estado del sidebar
            const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (sidebarCollapsed) {
                adminSidebar.classList.add('collapsed');
                adminContent.classList.add('expanded');
            }
            
            // Mobile Sidebar Toggle
            const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
            const mobileOverlay = document.getElementById('mobileOverlay');
            
            if (mobileSidebarToggle) {
                mobileSidebarToggle.addEventListener('click', function() {
                    adminSidebar.classList.add('mobile-open');
                    mobileOverlay.classList.add('active');
                });
            }
            
            mobileOverlay.addEventListener('click', function() {
                adminSidebar.classList.remove('mobile-open');
                mobileOverlay.classList.remove('active');
            });
            
            // Responsive adjustments
            function handleResize() {
                if (window.innerWidth <= 1024) {
                    adminSidebar.classList.remove('collapsed');
                    adminContent.classList.remove('expanded');
                }
            }
            
            window.addEventListener('resize', handleResize);
            handleResize();
        });
    </script>
    
    <?php if (isset($extraJS)) echo $extraJS; ?>
</body>
</html>