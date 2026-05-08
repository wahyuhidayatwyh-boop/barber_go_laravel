<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barber Go - The Premium Vibe</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link rel="stylesheet" href="/assets/css/style-landing.css">
</head>
<body>

    <header>
      <a href="#" class="logo">
        <img src="/assets/img/logo.png" alt="Logo Barber Go">
        <div class="logo-text-container">
            <span class="cukur-text">BARBER</span><span class="men-text">GO</span>
        </div>
      </a>
        <nav>
            <ul class="nav-links" id="navLinks">
                <li><a href="#home">Beranda</a></li>
                <li><a href="#about-us">Tentang Kami</a></li>
                <li><a href="#layanan">Layanan</a></li>
                <li><a href="#barber">Barber</a></li>
                <li><a href="#produk">Produk</a></li>
                @auth
                    <li class="nav-button"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="nav-button">
                        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" style="background: none; border: none; color: inherit; cursor: pointer; font-family: inherit; font-size: inherit; text-transform: inherit; letter-spacing: inherit; padding: 0.6rem 1.5rem; background-color: #DAA520; color: #111111; border-radius: 4px; font-weight: 500;">Keluar</button>
                        </form>
                    </li>
                @else
                    <li class="nav-button"><a href="/login">Masuk / Daftar</a></li>
                @endauth
            </ul>
        </nav>
        <div class="hamburger-menu" id="hamburgerMenu">
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
        </div>
    </header>

    @if (session('error'))
        <div style="background-color: #f8d7da; color: #721c24; padding: 1rem; text-align: center; font-family: sans-serif;">
            {{ session('error') }}
        </div>
    @endif

    @if (session('success'))
        <div style="background-color: #d4edda; color: #155724; padding: 1rem; text-align: center; font-family: sans-serif;">
            {{ session('success') }}
        </div>
    @endif

    <main>
        
        <section class="hero" id="home">
            <div class="container hero-container">
                <div class="hero-main-text">
                    <h1>STYLE & PRESISI</h1>
                    <p>Pengalaman Potong Rambut Klasik Terbaik</p>
                </div>
                
                <a href="/login" class="btn-gold">Booking Sekarang</a> </div>
        </section>

        <section class="about-us" id="about-us">
            <div class="container">
                <h2>MENGAPA <span>BARBER GO?</span></h2>
                <p style="text-align:center; margin-bottom: 2rem;">Kami memberikan lebih dari sekadar potong rambut. Kami memberikan pengalaman dan layanan digital terbaik.</p>
                
                <div class="about-card-container">
                    
                    <div class="about-card">
                        <i class="fas fa-mobile-alt"></i>
                        <h3>Manajemen Janji Temu Digital</h3>
                        <p>Dapatkan kepastian waktu cukur tanpa antri panjang. Kami mendukung sistem digital penuh.</p>
                        <ul>
                            <li><i class="fas fa-check"></i>Booking online dengan slot real-time</li>
                            <li><i class="fab fa-whatsapp"></i>Reminder WhatsApp otomatis</li>
                        </ul>
                    </div>

                    <div class="about-card">
                        <i class="fas fa-medal"></i>
                        <h3>Program Loyalitas Eksklusif</h3>
                        <p>Kami menghargai setiap kunjungan Anda dengan program loyalitas yang jelas dan mudah diakses.</p>
                        <ul>
                            <li><i class="fas fa-check"></i>Program loyalitas: 5x cukur = 1x gratis</li>
                            <li><i class="fas fa-receipt"></i>Struk digital dan riwayat potong</li>
                        </ul>
                    </div>

                    <div class="about-card">
                        <i class="fas fa-store"></i>
                        <h3>Katalog Produk dan Barber Ahli</h3>
                        <p>Jaminan kualitas melalui produk perawatan premium dan keterampilan barber terbaik kami.</p>
                        <ul>
                            <li><i class="fas fa-shopping-bag"></i>Katalog produk barbershop</li>
                            <li><i class="fas fa-user-tie"></i>Barber ahli di berbagai style</li>
                        </ul>
                    </div>
                     </div>
            </div>
        </section>

        <section class="layanan" id="layanan">
            <div class="container">
                <h2>DAFTAR <span>LAYANAN</span></h2>
                <p style="margin-bottom: 3rem;">Kami fokus pada dua layanan premium inti: Cukur dan Pewarnaan Rambut.</p>
                
                <div class="layanan-card-full-list">
                    
                    <div class="layanan-card-full">
                        <div class="layanan-info-full">
                            <h4><i class="fas fa-cut"></i>Premium Haircut Experience</h4>
                            <p>Layanan cukur lengkap yang mencakup konsultasi gaya, teknik cukur presisi (fade, classic, undercut), pencucian rambut, aplikasi vitamin premium, dan pijat bahu relaksasi.</p>
                            <p class="tag">Estimasi: 60 - 75 menit</p>
                        </div>
                    </div>

                    <div class="layanan-card-full">
                        <div class="layanan-info-full">
                            <h4><i class="fas fa-palette"></i>Professional Hair Coloring</h4>
                            <p>Layanan pewarnaan profesional, mulai dari basic color, highlight, hingga balayage. Kami menggunakan produk berkualitas tinggi untuk menjaga kesehatan dan kilau rambut Anda.</p>
                            <p class="tag">Wajib Konsultasi</p>
                        </div>
                    </div>
                </div>

                <div class="info-tambahan-container">
                    <div class="info-card">
                        <h4><i class="fas fa-mug-hot"></i>Fasilitas Barber</h4>
                        <p><i class="fas fa-wind"></i>Ruangan Full AC</p>
                        <p><i class="fas fa-coffee"></i>Minuman Gratis</p>
                    </div>
                    
                    <div class="info-card">
                        <h4><i class="fas fa-clock"></i>Informasi Order</h4>
                        <p><i class="fas fa-door-closed"></i>Last order cukur 21.45 WIB</p>
                        <p><i class="fas fa-info-circle"></i>*Sesuai kondisi dan antrian</p>
                    </div>
                </div>
                
            </div>
        </section>

        <section class="barber" id="barber">
            <div class="container">
                <h2>PILIH <span>BARBER ANDA</span></h2>
                <p>Para ahli kami siap melayani Anda dengan presisi dan style.</p>
                <div class="barber-container">
                    
                    <div class="barber-card">
                        <div class="barber-img-container">
                            <img src="/assets/img/barber1.jpg" alt="Foto Barber A">
                        </div>
                        <div class="barber-info">
                            <h3>ZAKI</h3>
                            <p>Ahli di Semua Jenis Potongan & Gaya</p>
                        </div>
                    </div>
                    <div class="barber-card">
                        <div class="barber-img-container">
                            <img src="/assets/img/barber2.jpg" alt="Foto Barber B">
                        </div>
                        <div class="barber-info">
                            <h3>SAFIK</h3>
                            <p>Ahli di Semua Jenis Potongan & Gaya</p>
                        </div>
                    </div>
                    
                    
                    </div>
            </div>
        </section>

        <section class="produk" id="produk">
            <div class="container">
                <h2>PRODUK <span>KAMI</span></h2>
                <p>Bawa pulang style Anda dengan produk perawatan premium kami. Geser ke kanan untuk melihat lebih banyak!</p>
                
                <div class="produk-container">
                    
                   <div class="produk-card">
                        <img src="/assets/img/pom.jpg" alt="Cream Pomade Barber Go"> 
                        <h3>Premium Pomade</h3>
                        <span class="product-price">Rp 65.000</span>
                        <span class="stock-status">Ready Stock</span>
                        <p class="purchase-info"><i class="fas fa-store"></i> Beli di tempat</p>
                    </div>

                    <div class="produk-card">
                        <img src="/assets/img/hair.jpg" alt="Hair Tonic">
                        <h3>Hair Tonic</h3>
                         <span class="product-price">Rp 45.000</span>
                         <span class="stock-status">Ready Stock</span>
                        <p class="purchase-info"><i class="fas fa-store"></i> Beli di tempat</p>
                    </div>

                    <div class="produk-card">
                        <img src="/assets/img/vit.jpg" alt="Vitamin">
                        <h3>Vitamin Rambut</h3>
                         <span class="product-price">Rp 75.000</span>
                        <span class="stock-status">Ready Stock</span>
                         <p class="purchase-info"><i class="fas fa-store"></i> Beli di tempat</p>
                    </div>

                    <div class="produk-card">
                        <img src="/assets/img/vow.jpg" alt="Powder">
                        <h3>Hair Powder</h3>
                        <span class="product-price">Rp 55.000</span>
                        <span class="stock-status">Ready Stock</span>
                         <p class="purchase-info"><i class="fas fa-store"></i> Beli di tempat</p>
                    </div>
                    
                    </div>
            </div>
        </section>
    </main>

    <footer class="new-footer">
        <div class="footer-grid">
            <div class="footer-about">
                    <a href="#" class="footer-logo">
                        <img src="/assets/img/logo.png" alt="Logo CUKURMEN">
                        <div class="logo-text-container"><span class="cukur-text">BARBER</span><span class="men-text">GO</span></div>
                    </a>
                    <p>BARBER GO BARBERSHOP</p>
                    <p>Since 2025</p>

                    <div class="footer-socials">
                        <a href="javascript:void(0);"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://www.instagram.com/cukurmen.barber?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram"></i></a>
                        <a href="javascript:void(0);"><i class="fab fa-tiktok"></i></a>
                        <a href="javascript:void(0);"><i class="fab fa-linkedin-in"></i></a>
                    </div>
            </div>
            <div class="footer-links">
                <h4>COMPANY</h4>
                <ul>
                    <li><a href="#about-us">Tentang Kami</a></li>
                    <li><a href="#layanan">Layanan</a></li>
                    <li><a href="#barber">Barber</a></li>
                    <li><a href="#produk">Produk</a></li>
                </ul>
            </div>
            <div class="footer-links">
                <h4>OTHER</h4>
                <ul>
                    <li><a href="#">Trend Rambut</a></li>
                    <li><a href="#">Galeri</a></li>
                    <li><a href="#">Karir</a></li>
                    <li><a href="#">Kontak</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Term of Service</a></li>
                </ul>
            </div>
            <div class="footer-support">
                <h4>SUPPORT</h4>
                <div class="map-container">
                    <iframe src="https://www.google.com/maps?q=Jl.%20Profesor%20DR.%20HR%20Boenyamin%20No.152%20Sumampir%20Wetan%20Purwokerto%20Banyumas&z=15&output=embed" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
                <p><i class="fas fa-map-marker-alt"></i>Jl. Profesor DR. HR Boenyamin No.152, Sumampir Wetan, Pabuaran, Kec. Purwokerto Utara, Kabupaten Banyumas, Jawa Tengah 53124</p>
                <p><i class="fab fa-whatsapp"></i>085228938097</p>
                <p><i class="fab fa-instagram"></i> cukurmen.barber</p>
                
            </div>
        </div>
    <div class="sub-footer">
        Copyright © 2025 All rights reserved | <span>BARBER GO BARBERSHOP</span>
    </div>
    </footer>

    <script src="/assets/js/script-landing.js" defer></script>

</body>
</html>
