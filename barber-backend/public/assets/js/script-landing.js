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

    // Dropdown (Booking) toggle for landingpage: always route guests to login (links already point to login.html)
    const navDropdowns = document.querySelectorAll('.nav-dropdown');
    navDropdowns.forEach(dd => {
        const btn = dd.querySelector('button');
        const menu = dd.querySelector('.dropdown-menu');
        if (!btn || !menu) return;

        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            // toggle open state
            dd.classList.toggle('open');
            const expanded = dd.classList.contains('open');
            btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', () => {
        document.querySelectorAll('.nav-dropdown.open').forEach(openDd => {
            openDd.classList.remove('open');
            const btn = openDd.querySelector('button');
            if (btn) btn.setAttribute('aria-expanded', 'false');
        });
    });
});
