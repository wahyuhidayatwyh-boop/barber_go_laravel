document.addEventListener('DOMContentLoaded', function() {
    // Hamburger Menu Logic
    const hamburgerMenu = document.getElementById('hamburgerMenu');
    const navLinks = document.getElementById('navLinks');

    // Toggle menu saat ikon hamburger diklik
    hamburgerMenu.addEventListener('click', function() {
        navLinks.classList.toggle('active');
        // Toggle class 'toggled' untuk animasi X (jika ada CSS-nya)
        hamburgerMenu.classList.toggle('toggled'); 
    });

    // Tutup menu saat link di dalam menu diklik (kecuali tombol Masuk/Daftar)
    navLinks.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            // Periksa apakah link berada di dalam li dengan class 'nav-button'
            const isNavButton = link.closest('.nav-button'); 
            
            // Hanya tutup jika menu aktif dan link BUKAN tombol 'Masuk/Daftar'
            if (navLinks.classList.contains('active') && !isNavButton) {
                navLinks.classList.remove('active');
                hamburgerMenu.classList.remove('toggled'); // Hapus class animasi X
            }
            // Jika itu tombol 'Masuk/Daftar', biarkan menu terbuka atau tertutup sesuai state
        });
    });

    // Auth guard: checks for authentication using Laravel's session management
    // The actual authentication is handled by Laravel middleware
    try {
        // This will be handled properly by Laravel which will redirect if unauthorized
        console.info('Dashboard page loaded. Authentication handled by Laravel middleware.');
    } catch (e) {
        // If there's an error with auth checks, let Laravel handle the redirect
        console.error('Auth check error:', e);
        window.location.href = '/login';
    }

    // Logout button - updated to use Laravel's logout route
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', () => {
            // Redirect to Laravel's logout route which handles session destruction
            window.location.href = '/logout';
        });
    }

    // Dropdown (Booking) toggle for dashboard: links point to actual booking pages
    const navDropdowns = document.querySelectorAll('.nav-dropdown');
    navDropdowns.forEach(dd => {
        const btn = dd.querySelector('button');
        const menu = dd.querySelector('.dropdown-menu');
        if (!btn || !menu) return;

        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            dd.classList.toggle('open');
            const expanded = dd.classList.contains('open');
            btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        });
    });

    document.addEventListener('click', () => {
        document.querySelectorAll('.nav-dropdown.open').forEach(openDd => {
            openDd.classList.remove('open');
            const btn = openDd.querySelector('button');
            if (btn) btn.setAttribute('aria-expanded', 'false');
        });
    });
});