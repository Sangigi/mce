/**
 * Scripts para el panel de administración
 */

document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar
    const sidebarToggle = document.getElementById('sidebarToggle');
    const adminContainer = document.querySelector('.admin-container');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            adminContainer.classList.toggle('sidebar-collapsed');
            
            // Guardar estado en localStorage
            const isSidebarCollapsed = adminContainer.classList.contains('sidebar-collapsed');
            localStorage.setItem('sidebar-collapsed', isSidebarCollapsed);
        });
    }
    
    // Recuperar estado del sidebar del localStorage
    const isSidebarCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
    if (isSidebarCollapsed) {
        adminContainer.classList.add('sidebar-collapsed');
    }
    
    // Cerrar alertas
    const closeButtons = document.querySelectorAll('.alert .close');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const alert = this.closest('.alert');
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 300);
        });
    });
    
    // Confirmación de eliminación
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');
            const confirmDelete = document.getElementById('confirmDelete');
            
            if (confirmDelete) {
                // Si hay un modal de confirmación
                confirmDelete.href = this.href || this.getAttribute('data-href') || `?delete=${id}`;
                
                // Mostrar modal (simulado con confirm)
                if (confirm('¿Está seguro de que desea eliminar este elemento? Esta acción no se puede deshacer.')) {
                    window.location.href = confirmDelete.href;
                }
            } else {
                // Si no hay modal, usar confirm directamente
                if (confirm('¿Está seguro de que desea eliminar este elemento? Esta acción no se puede deshacer.')) {
                    window.location.href = this.href || this.getAttribute('data-href') || `?delete=${id}`;
                }
            }
        });
    });
    
    // Mostrar nombre de archivo en inputs de tipo file
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const fileName = this.files[0]?.name || 'Ningún archivo seleccionado';
            const label = this.nextElementSibling;
            
            if (label && label.classList.contains('custom-file-label')) {
                label.textContent = fileName;
            }
        });
    });
    
    // Búsqueda en tablas
    const searchInputs = document.querySelectorAll('input[id^="searchInput"]');
    searchInputs.forEach(input => {
        input.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const table = this.closest('.card').querySelector('table');
            
            if (table) {
                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            }
        });
    });
    
    // Inicializar editores de texto enriquecido si existe la librería
    if (typeof ClassicEditor !== 'undefined') {
        const richTextEditors = document.querySelectorAll('.rich-text-editor');
        richTextEditors.forEach(editor => {
            ClassicEditor
                .create(editor)
                .catch(error => {
                    console.error(error);
                });
        });
    }
    
    // Inicializar datepickers si existe la librería
    if (typeof flatpickr !== 'undefined') {
        const datepickers = document.querySelectorAll('.datepicker');
        flatpickr(datepickers, {
            dateFormat: 'Y-m-d',
            locale: 'es'
        });
        
        const datetimepickers = document.querySelectorAll('.datetimepicker');
        flatpickr(datetimepickers, {
            dateFormat: 'Y-m-d H:i',
            enableTime: true,
            time_24hr: true,
            locale: 'es'
        });
    }
    
    // Inicializar select2 si existe la librería
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
        jQuery('.select2').select2({
            width: '100%'
        });
    }
});