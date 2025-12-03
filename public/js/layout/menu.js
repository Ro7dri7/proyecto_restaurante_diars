// Ubicación: public/js/layout/menu.js
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar');
    const closeMenu = document.getElementById('close-menu');

    if (!menuToggle || !sidebar || !closeMenu) {
        console.warn('Elementos del menú no encontrados');
        return;
    }

    // Toggle del menú (abrir/cerrar)
    menuToggle.addEventListener('click', function(e) {
        e.preventDefault();
        if (sidebar.style.transform === 'translateX(0px)' || sidebar.style.transform === 'translateX(0)') {
            sidebar.style.transform = 'translateX(-100%)';
        } else {
            sidebar.style.transform = 'translateX(0)';
        }
    });

    // Cerrar con botón X
    closeMenu.addEventListener('click', function(e) {
        e.preventDefault();
        sidebar.style.transform = 'translateX(-100%)';
    });

    // Cerrar al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
            sidebar.style.transform = 'translateX(-100%)';
        }
    });

    // Toggle de submenús
    const submenuToggles = document.querySelectorAll('.submenu-toggle');
    submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const submenu = this.nextElementSibling;
            
            if (submenu.style.display === 'block') {
                submenu.style.display = 'none';
            } else {
                // Cerrar todos los submenús
                document.querySelectorAll('.submenu').forEach(sm => {
                    if (sm !== submenu) {
                        sm.style.display = 'none';
                    }
                });
                submenu.style.display = 'block';
            }
        });
    });
});