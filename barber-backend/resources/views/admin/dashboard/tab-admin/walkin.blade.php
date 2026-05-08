@extends('admin.admindashboard')

@section('content')
<div class="main-tab-content active" id="admin-tab-content">
    <div class="hero-dashboard">
        <h1 class="dashboard-header">Dashboard <span>Admin</span></h1>
        
        <div class="sync-info">
            <span><i class="fas fa-circle" style="font-size: 0.7rem; color: var(--button-success); margin-right: 5px;"></i> Sinkronisasi Aktif</span>
            <span id="lastUpdate"></span>
            <div class="date-input-wrapper">
                <button id="selectDateButton"><i class="fas fa-calendar-alt"></i> Pilih Tanggal</button>
                <input type="date" id="date-selector-input" value="{{ date('Y-m-d') }}">
            </div>
        </div>
    </div>
    
    <div class="booking-summary-grid">
        <div class="summary-card"><p>Total Booking</p><h3 class="total" id="totalBooking">...</h3></div>
        <div class="summary-card"><p>Online</p><h3 class="online" id="onlineBooking">...</h3></div>
        <div class="summary-card"><p>Walk-in</p><h3 class="walk-in" id="walkInBooking">...</h3></div>
        <div class="summary-card"><p>Menunggu</p><h3 class="menunggu" id="menungguCount">...</h3></div>
        <div class="summary-card"><p>Check-in</p><h3 class="check-in" id="checkInCount">...</h3></div>
        <div class="summary-card"><p>Cukur</p><h3 class="cukur" id="cukurCount">...</h3></div>
        <div class="summary-card"><p>Selesai</p><h3 class="selesai" id="selesaiCount">...</h3></div>
    </div>

    <nav class="sub-nav" id="subNav">
        <a href="{{ route('admin.checkin') }}" class="sub-nav-item" data-tab="check">Check-in</a>
        <a href="{{ route('admin.walkin') }}" class="sub-nav-item active" data-tab="walk">Walk-in</a>
        <a href="{{ route('admin.schedule') }}" class="sub-nav-item" data-tab="jadwal">Jadwal</a>
        <a href="{{ route('admin.report') }}" class="sub-nav-item" data-tab="report">Report</a>
    </nav>

    <div class="sub-content-container">
        <div class="tab-content active" id="walk">
            <div class="content-card">
                <h4>Tambah Walk-in</h4>
                <p style="color: var(--text-grey); margin-bottom: 1.5rem;">Daftarkan pelanggan walk-in (offline) secara manual.</p>
                <div class="form-group">
                    <label for="nama-pelanggan">Nama Pelanggan</label>
                    <input type="text" id="nama-pelanggan" placeholder="Nama pelanggan">
                </div>
                <div class="form-group">
                    <label for="nomor-hp">Nomor HP</label>
                    <input type="tel" id="nomor-hp" placeholder="Nomor HP pelanggan">
                </div>
                <div class="form-group">
                    <label for="tanggal-walkin">Pilih Tanggal</label>
                    <input type="date" id="tanggal-walkin" value="" style="width: 100%; margin-bottom: 15px;">
                    <button type="button" class="btn-base" id="loadTimeSlotsBtn" style="width: 100%;">Muat Jam Tersedia</button>
                </div>
                
                <div class="form-group">
                    <label for="jam-tersedia">Pilih Jam Tersedia</label>
                    <select id="jam-tersedia" style="width: 100%;">
                        <option value="">Pilih jam setelah memilih tanggal, barber, dan layanan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="barber">Barber</label>
                    <select id="barber-select">
                        <option value="">Pilih Barber</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="layanan">Layanan</label>
                    <select id="layanan-select">
                        <option value="">Pilih Layanan</option>
                    </select>
                </div>
                <button class="btn-add-walkin" id="addWalkinButton"><i class="fas fa-plus"></i> Tambah Walk-in</button>
            </div>
        </div>
    </div>
</div>

<script>
// JavaScript untuk halaman admin utama dan semua sub-tab
document.addEventListener('DOMContentLoaded', function() {
    // Ambil data dasbor dari API
    const currentDate = document.getElementById('date-selector-input').value;
    loadDashboardData(currentDate);
    
    // Event listener untuk tombol refresh
    const refreshBtn = document.getElementById('refreshButton');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            const selectedDate = document.getElementById('date-selector-input').value;
            loadDashboardData(selectedDate);
        });
    }
    
    // Tambahkan event listener ke tombol tambah walk-in
    const addWalkinButton = document.getElementById('addWalkinButton');
    if (addWalkinButton) {
        addWalkinButton.addEventListener('click', addWalkInBooking);
    }
    
    // Load data untuk dropdown
    loadWalkInData();
    
    // Update tanggal
    updateLastUpdatedTime();
});

function loadDashboardData(date) {
    fetch(`{{ route("admin.dashboard.data") }}?date=${date}`)
        .then(response => response.json())
        .then(data => {
            updateDashboardSummary(data.summary);
            updateLastUpdatedTime();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat memuat data dashboard.');
        });
}

function updateDashboardSummary(summary) {
    document.getElementById('totalBooking').textContent = summary.total;
    document.getElementById('onlineBooking').textContent = summary.online;
    document.getElementById('walkInBooking').textContent = summary.walkIn;
    document.getElementById('menungguCount').textContent = summary.menunggu;
    document.getElementById('checkInCount').textContent = summary.checkIn;
    document.getElementById('cukurCount').textContent = summary.cukur || summary.inProgress || 0;
    document.getElementById('selesaiCount').textContent = summary.selesai;
}

// Fungsi untuk menambah walk-in booking
function addWalkInBooking() {
    const customerName = document.getElementById('nama-pelanggan').value.trim();
    const phone = document.getElementById('nomor-hp').value.trim();
    const date = document.getElementById('tanggal-walkin').value;
    const time = document.getElementById('jam-tersedia').value;
    const barberId = document.getElementById('barber-select').value;
    const serviceId = document.getElementById('layanan-select').value;
    
    if (!customerName || !phone || !date || !time || !barberId || !serviceId) {
        alert('Silakan lengkapi semua field terlebih dahulu.');
        return;
    }
    
    // Data untuk dikirim - booking baru otomatis masuk ke status 'pending' dan akan muncul di halaman check-in
    const bookingData = {
        customer_name: customerName,
        phone: phone,
        booking_date: date,
        booking_time: time,
        barber_id: barberId,
        service_id: serviceId,
        payment_method: 'Walk-in',
        status: 'pending'  // New walk-in bookings start with 'pending' status to appear in check-in queue
    };
    
    fetch('{{ route("admin.bookings.create") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(bookingData)
    })
    .then(response => {
        // Periksa apakah response berhasil sebelum mencoba menguraikan JSON
        if (!response.ok) {
            // Jika status bukan 2xx, tangani sebagai error
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        alert('Walk-in booking berhasil ditambahkan dan otomatis masuk ke antrian check-in!');
        // Reset form
        document.getElementById('nama-pelanggan').value = '';
        document.getElementById('nomor-hp').value = '';
        document.getElementById('tanggal-walkin').value = '';
        document.getElementById('jam-tersedia').value = '';
        document.getElementById('barber-select').value = '';
        document.getElementById('layanan-select').value = '';
        
        // Load ulang data
        loadDashboardData(date);
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menambahkan walk-in booking. Silakan coba lagi.');
    });
}

// Fungsi untuk memuat jam yang tersedia dari backend
function loadAvailableTimeSlots() {
    const date = document.getElementById('tanggal-walkin').value;
    const barberId = document.getElementById('barber-select').value;
    const serviceId = document.getElementById('layanan-select').value;

    if (!date || !barberId || !serviceId) {
        alert('Silakan pilih tanggal, barber, dan layanan terlebih dahulu.');
        return;
    }

    console.log("Memuat jam tersedia untuk tanggal:", date, "barber:", barberId, "service:", serviceId);

    // Reset dropdown jam
    const jamTersediaSelect = document.getElementById('jam-tersedia');
    jamTersediaSelect.innerHTML = '<option value="">Memuat jam tersedia...</option>';
    
    // Fetch available time slots from the Laravel API
    fetch(`{{ route("admin.bookings.time-slots") }}?date=${date}&barber_id=${barberId}&service_id=${serviceId}`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        // Kosongkan dropdown
        jamTersediaSelect.innerHTML = '<option value="">Pilih jam tersedia</option>';

        if (data.availableSlots.length === 0) {
            jamTersediaSelect.innerHTML = '<option value="">Tidak ada jam tersedia</option>';
            return;
        }

        // Tambahkan setiap slot waktu ke dropdown
        data.availableSlots.forEach(time => {
            const option = document.createElement('option');
            option.value = time;
            option.textContent = time;
            jamTersediaSelect.appendChild(option);
        });

    })
    .catch(error => {
        console.error("Gagal memuat jam tersedia:", error);
        jamTersediaSelect.innerHTML = '<option value="">Gagal memuat jam</option>';
    });
}

// Fungsi untuk mengisi dropdown barber dan layanan saat halaman walk-in dimuat
function loadWalkInData() {
    // Load barbers
    fetch('{{ route("admin.barbers") }}')
        .then(response => response.json())
        .then(barbers => {
            const barberSelect = document.getElementById('barber-select');
            if (barberSelect) {
                barberSelect.innerHTML = '<option value="">Pilih Barber</option>';
                barbers.forEach(barber => {
                    const option = document.createElement('option');
                    option.value = barber.id;
                    option.textContent = barber.name;
                    barberSelect.appendChild(option);
                });
            }
        })
        .catch(error => console.error('Error loading barbers:', error));
    
    // Load services
    fetch('{{ route("admin.services") }}')
        .then(response => response.json())
        .then(services => {
            const serviceSelect = document.getElementById('layanan-select');
            if (serviceSelect) {
                serviceSelect.innerHTML = '<option value="">Pilih Layanan</option>';
                services.forEach(service => {
                    const option = document.createElement('option');
                    option.value = service.id;
                    option.textContent = `${service.name} - Rp ${service.price.toLocaleString('id-ID')}`;
                    serviceSelect.appendChild(option);
                });
            }
        })
        .catch(error => console.error('Error loading services:', error));
}

function updateLastUpdatedTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('id-ID', { 
        hour: '2-digit', 
        minute: '2-digit',
        second: '2-digit' 
    });
    document.getElementById('lastUpdate').textContent = 'Terakhir diperbarui: ' + timeString;
}

// Setup real-time updates using Laravel Echo
function setupRealTimeUpdates() {
    // Listen for booking status updates
    if (typeof Echo !== 'undefined') {
        Echo.channel('bookings')
            .listen('BookingStatusUpdated', (e) => {
                console.log('Booking status updated:', e);
                
                // Update dashboard data
                const currentDate = document.getElementById('date-selector-input').value;
                loadDashboardData(currentDate);
                
                // Update the last updated time
                updateLastUpdatedTime();
            });
    }
}

// Set up automatic refresh every 30 seconds
function setupAutomaticRefresh() {
    setInterval(() => {
        const currentDate = document.getElementById('date-selector-input').value;
        loadDashboardData(currentDate);
    }, 30000); // 30 seconds
}

document.addEventListener('DOMContentLoaded', function() {
    // Ambil data dasbor dari API
    const currentDate = document.getElementById('date-selector-input').value;
    loadDashboardData(currentDate);
    
    // Event listener untuk tombol refresh
    const refreshBtn = document.getElementById('refreshButton');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            const selectedDate = document.getElementById('date-selector-input').value;
            loadDashboardData(selectedDate);
        });
    }
    
    // Tambahkan event listener ke tombol tambah walk-in
    const addWalkinButton = document.getElementById('addWalkinButton');
    if (addWalkinButton) {
        addWalkinButton.addEventListener('click', addWalkInBooking);
    }
    
    // Tambahkan event listener ke tombol muat jam tersedia
    const loadTimeSlotsBtn = document.getElementById('loadTimeSlotsBtn');
    if (loadTimeSlotsBtn) {
        loadTimeSlotsBtn.addEventListener('click', loadAvailableTimeSlots);
    }
    
    // Load data untuk dropdown
    loadWalkInData();
    
    // Update tanggal
    updateLastUpdatedTime();
    
    // Setup real-time updates using Laravel Echo
    setupRealTimeUpdates();
    setupAutomaticRefresh();
});

// Setup real-time updates using Laravel Echo
function setupRealTimeUpdates() {
    // Listen for booking status updates
    if (typeof Echo !== 'undefined') {
        Echo.channel('bookings')
            .listen('BookingStatusUpdated', (e) => {
                console.log('Booking status updated:', e);
                
                // Update dashboard data
                const currentDate = document.getElementById('date-selector-input').value;
                loadDashboardData(currentDate);
                
                // Update the last updated time
                updateLastUpdatedTime();
            });
    }
}

// Set up automatic refresh every 30 seconds
function setupAutomaticRefresh() {
    setInterval(() => {
        const currentDate = document.getElementById('date-selector-input').value;
        loadDashboardData(currentDate);
    }, 30000); // 30 seconds
}
</script>

<style>
.cukur {
    color: #4A90E2; /* Blue color for cukur count */
}
</style>

@endsection