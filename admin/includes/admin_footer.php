</main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Hamburger menu functionality
        const hamburgerMenu = document.getElementById('hamburger-menu');
        const adminSidebar = document.getElementById('admin-sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        
        // Toggle sidebar
        hamburgerMenu.addEventListener('click', function() {
            hamburgerMenu.classList.toggle('active');
            adminSidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
        });
        
        // Close sidebar when clicking overlay
        sidebarOverlay.addEventListener('click', function() {
            hamburgerMenu.classList.remove('active');
            adminSidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });
        
        // Close sidebar when clicking outside
        document.addEventListener('click', function(e) {
            if (!adminSidebar.contains(e.target) && 
                !hamburgerMenu.contains(e.target) && 
                adminSidebar.classList.contains('show')) {
                hamburgerMenu.classList.remove('active');
                adminSidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
            }
        });
        
        // Close sidebar when pressing escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && adminSidebar.classList.contains('show')) {
                hamburgerMenu.classList.remove('active');
                adminSidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
            }
        });
        
        // Add pulse effect to hamburger menu on page load
        window.addEventListener('load', function() {
            hamburgerMenu.classList.add('pulse-effect');
            setTimeout(function() {
                hamburgerMenu.classList.remove('pulse-effect');
            }, 4000);
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    </script>
</body>
</html>