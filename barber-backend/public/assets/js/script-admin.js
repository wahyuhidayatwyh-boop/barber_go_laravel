/* 
================================================================
ADMIN DASHBOARD JAVASCRIPT
Minimal version for basic functionality
================================================================
*/

// Admin guard: checks for authentication and admin role using Laravel's session management
// The actual authentication and role checks are handled by Laravel middleware
try {
    // Check if user is authenticated and has admin privileges
    // This will be handled properly by Laravel which will redirect if unauthorized
    console.info('Admin page loaded. Authentication and authorization handled by Laravel middleware.');
} catch (e) {
    // If there's an error with auth checks, let Laravel handle the redirect
    console.error('Auth check error:', e);
}

// Fungsi untuk menangani navigasi utama (Admin, Produk, Profil)
function setupMainNavigation() {
    const mainNavItems = document.querySelectorAll('.main-nav-item');
    
    mainNavItems.forEach(item => {
        item.addEventListener('click', function(e) {
            // Hapus class active dari semua item navigasi
            mainNavItems.forEach(navItem => navItem.classList.remove('active'));
            
            // Tambahkan class active ke item yang diklik
            this.classList.add('active');
            
            // Tidak perlu mengatur tampilan tab di sini karena masing-masing halaman
            // memiliki struktur sendiri dan navigasi dilakukan via route Laravel
        });
    });
}

// Simple event listener setup for basic functionality
function setupEventListeners() {
    // Event listener for logout confirmation if needed (though we're using direct link now)
    const logoutLink = document.querySelector('.exit');
    if (logoutLink) {
        logoutLink.addEventListener('click', function(e) {
            if (!confirm('Apakah Anda yakin ingin keluar?')) {
                e.preventDefault();
            }
        });
    }
    
    // Setup navigasi utama
    setupMainNavigation();
    
    // Event listener untuk sub navigation (hanya untuk halaman admin utama)
    const subNavItems = document.querySelectorAll('.sub-nav-item');
    subNavItems.forEach(item => {
        item.addEventListener('click', function(e) {
            // Hapus class active dari semua sub-nav
            subNavItems.forEach(navItem => navItem.classList.remove('active'));
            
            // Tambahkan class active ke item yang diklik
            this.classList.add('active');
            
            // Ambil ID tab dari data-tab
            const tabId = this.getAttribute('data-tab');
            
            // Sembunyikan semua tab content
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Tampilkan tab content yang sesuai
            const targetTab = document.getElementById(tabId);
            if (targetTab) {
                targetTab.classList.add('active');
            }
        });
    });
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Setup event listeners
    setupEventListeners();
    
    console.log('Admin dashboard JavaScript initialized');
});