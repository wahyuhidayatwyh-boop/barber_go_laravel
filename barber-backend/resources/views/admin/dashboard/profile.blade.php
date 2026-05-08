@extends('admin.admindashboard')

@section('content')
<div class="main-tab-content active" id="profil-tab-content">
    <div class="content-card">
        <h1 class="dashboard-header">Profil <span>Barber Go</span> Barbershop</h1>
        <p style="color: var(--text-grey); margin-bottom: 2rem;">Anda hanya dapat melihat informasi akun admin dan detail barbershop di halaman ini.</p>

        <h4 style="font-size: 1.5rem; color: var(--accent-gold); margin-bottom: 1.5rem;">Informasi Barbershop</h4>
        <div class="form-group">
            <label>Nama Barbershop</label>
            <span class="view-only-text" id="profilNamaBarbershop">{{ $namaBarbershop ?? 'Barber Go Barbershop' }}</span>
        </div>
        <div class="form-group">
            <label>Jam Operasional</label>
            <span class="view-only-text" id="profilJamOperasional">{{ $jamOperasional ?? '10:00 - 21:45 (tanpa istirahat)' }}</span>
        </div>
        <div class="form-group">
            <label>Alamat & Lokasi</label>
            <span class="view-only-text" id="profilAlamat">{{ $alamat ?? 'Jl. Profesor DR. HR Boenyamin No.152, Sumampir Wetan, Pabuaran, Kec. Purwokerto Utara, Kabupaten Banyumas, Jawa Tengah 53124' }}</span>
        </div>
        
        <h4 style="font-size: 1.5rem; border-top: var(--border-subtle); padding-top: 2rem; margin-top: 2rem; color: var(--accent-gold);">Pengaturan Akun Admin</h4>
        <div class="form-group">
            <label>Nama Admin</label>
            <span class="view-only-text" id="profilNamaAdmin">{{ $user->name ?? 'Administrator' }}</span>
        </div>
        <div class="form-group">
            <label>Email Login</label>
            <span class="view-only-text" style="color: var(--text-dark-contrast); font-weight: 400;" id="profilEmailAdmin">{{ $user->email ?? 'admin@cukurmen.com' }}</span>
        </div>
        <p style="font-size: 0.9rem; color: var(--text-grey); margin-top: 15px;">Hubungi Super Admin untuk melakukan perubahan pada data di atas.</p>
    </div>
</div>

@endsection