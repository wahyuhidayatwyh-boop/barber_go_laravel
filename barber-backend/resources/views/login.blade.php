<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Cukur Men</title>
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link rel="stylesheet" href="/assets/css/style-login.css">
</head>
<body>

    <div class="login-container">
        
        <h2 class="page-title">Booking Layanan Cukur Profesional</h2>

        <div class="login-card">
            
            <div class="login-header">
                <h3>Masuk atau Daftar</h3>
                <p>Akses sistem booking dan loyalty program</p>
            </div>

            @if ($errors->any())
                <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #f5c6cb;">
                    <ul style="margin-bottom: 0;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #c3e6cb;">
                    {{ session('success') }}
                </div>
            @endif

            <div class="login-tabs">
                <button class="tab-btn active" data-tab="masuk">Masuk</button>
                <button class="tab-btn" data-tab="daftar">Daftar</button>
            </div>

            <form id="masuk" class="login-form active" action="/login" method="POST">
                @csrf
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="login@gmail.com" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="••••••••••" required>
                </div>
                <button type="submit" class="btn-gold">Masuk</button> 
            </form>

            <form id="daftar" class="login-form" action="/register" method="POST">
                @csrf
                <div class="form-group">
                    <label for="reg-name">Nama Lengkap</label> <input type="text" id="reg-name" name="name" placeholder="Nama Anda" required>
                </div>
                <div class="form-group">
                    <label for="reg-email">Email</label>
                    <input type="email" id="reg-email" name="email" placeholder="emailanda@gmail.com" required>
                </div>

                <div class="form-group">
                    <label for="reg-password">Buat Password</label>
                    <input type="password" id="reg-password" name="password" placeholder="••••••••••" required>
                </div>
                 <div class="form-group">
                    <label for="reg-password-confirm">Konfirmasi Password</label>
                    <input type="password" id="reg-password-confirm" name="password_confirmation" placeholder="••••••••••" required>
                </div>
                <button type="submit" class="btn-gold">Daftar</button>
            </form>

            <hr class="divider">

            <div class="feature-list">
                <h4>Fitur Member:</h4>
                <ul>
                    <li><i class="fas fa-calendar-check"></i>Booking online dengan slot real-time</li>
                    <li><i class="fas fa-star"></i>Program loyalitas: 5x cukur = 1x gratis</li>
                    <li><i class="fab fa-whatsapp"></i>Reminder WhatsApp otomatis</li>
                    <li><i class="fas fa-receipt"></i>Struk digital dan riwayat booking</li>
                    <li><i class="fas fa-shopping-bag"></i>Katalog produk barbershop</li>
                </ul>
            </div>

        </div>
    </div>

    <script src="/assets/js/script-login.js" defer></script>

</body>
</html>
