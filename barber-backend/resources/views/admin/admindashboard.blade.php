<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barber Go - Admin Dashboard</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Oswald:wght@500;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link rel="stylesheet" href="/assets/css/style-admin.css">
</head>
<body>

    <header class="admin-header">
        <a href="#" class="admin-logo">
            <img src="/assets/img/logoa.png" alt="Logo Cukur Men">
            <div class="logo-text-container"><span class="cukur-text">BARBER</span><span class="men-text">GO</span></div>
        </a>
        <div class="admin-actions">
            <button class="btn-base admin">Admin</button>
            <a href="/" class="exit"><i class="fas fa-sign-out-alt"></i> Keluar</a>
        </div>
    </header>

    <div class="admin-wrapper">
        <nav class="main-nav" id="main-nav">
            <a href="{{ route('admin.checkin') }}" class="nav-item main-nav-item {{ request()->routeIs('admin.checkin', 'admin.walkin', 'admin.schedule', 'admin.report', 'admin.dashboard') ? 'active' : '' }}" data-main-tab="admin">
                <i class="fas fa-cog"></i>
                <span>Admin</span>
            </a>
            <a href="{{ route('admin.products') }}" class="nav-item main-nav-item {{ request()->routeIs('admin.products') ? 'active' : '' }}" data-main-tab="produk">
                <i class="fas fa-box"></i>
                <span>Produk</span>
            </a>
            <a href="{{ route('admin.banners') }}" class="nav-item main-nav-item {{ request()->routeIs('admin.banners') ? 'active' : '' }}" data-main-tab="banners">
                <i class="fas fa-image"></i>
                <span>Banners</span>
            </a>
            <a href="{{ route('admin.barbers') }}" class="nav-item main-nav-item {{ request()->routeIs('admin.barbers') ? 'active' : '' }}" data-main-tab="barbers">
                <i class="fas fa-users"></i>
                <span>Barbers</span>
            </a>
            <a href="{{ route('admin.profile') }}" class="nav-item main-nav-item {{ request()->routeIs('admin.profile') ? 'active' : '' }}" data-main-tab="profil">
                <i class="fas fa-user-circle"></i>
                <span>Profil</span>
            </a>
        </nav>

        <main class="admin-content">
            @yield('content')
        </main>
    </div>

    <footer class="admin-footer">
        Copyright © 2025 BARBER GO Barbershop. All rights reserved.
    </footer>

    <script src="/assets/js/script-admin.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</body>
</html>