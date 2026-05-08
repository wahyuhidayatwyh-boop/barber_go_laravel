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
        <a href="{{ route('admin.walkin') }}" class="sub-nav-item" data-tab="walk">Walk-in</a>
        <a href="{{ route('admin.schedule') }}" class="sub-nav-item active" data-tab="jadwal">Jadwal</a>
        <a href="{{ route('admin.report') }}" class="sub-nav-item" data-tab="report">Report</a>
    </nav>

    <div class="sub-content-container">
        <div class="tab-content active" id="jadwal">
            <div class="content-card">
                <h4 id="jadwalHeader" style="color: var(--text-light); text-transform: none;">Jadwal Barber Hari Ini</h4>
                <p style="color: var(--text-grey); margin-bottom: 1.5rem;">Ringkasan aktivitas barber yang sedang berlangsung.</p>
                
                <!-- Tabs for viewing different booking statuses -->
                <div class="booking-status-tabs" style="margin: 1.5rem 0;">
                    <button class="status-tab-btn active" data-status="all">Semua</button>
                    <button class="status-tab-btn" data-status="pending">Menunggu</button>
                    <button class="status-tab-btn" data-status="confirmed">Check-in</button>
                    <button class="status-tab-btn" data-status="in_progress">Cukur</button>
                    <button class="status-tab-btn" data-status="completed">Selesai</button>
                </div>
                
                <div id="barberJadwal">
                    <p style="color: var(--text-grey); text-align: center; padding: 2rem;">Memuat jadwal barber...</p>
                </div>
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
    
    // Load data jadwal ketika halaman dimuat
    loadScheduleData();
    
    // Event listeners for status tabs
    const statusTabButtons = document.querySelectorAll('.status-tab-btn');
    statusTabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            statusTabButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            // Show bookings based on selected status
            const status = this.getAttribute('data-status');
            loadScheduleData(status);
        });
    });
    
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

// Load data jadwal ketika tab jadwal aktif
function loadScheduleData(statusFilter = 'all') {
    // Ambil tanggal terpilih atau gunakan tanggal saat ini
    const selectedDate = document.getElementById('date-selector-input') ? 
        document.getElementById('date-selector-input').value : 
        new Date().toISOString().split('T')[0];
    
    // Update header
    const dateObj = new Date(selectedDate);
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const formattedDate = dateObj.toLocaleDateString('id-ID', options);
    document.getElementById('jadwalHeader').textContent = `Jadwal Barber Tanggal ${formattedDate}`;
    
    // Ambil data dari API
    fetch(`{{ route("admin.dashboard.data") }}?date=${selectedDate}`)
        .then(response => response.json())
        .then(data => {
            // Filter bookings by status if needed
            let bookings = data.todaysBookings;
            if (statusFilter !== 'all') {
                bookings = bookings.filter(booking => booking.status === statusFilter);
            }
            displayScheduleData(bookings, statusFilter);
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('barberJadwal').innerHTML = 
                '<p style="color: var(--button-danger); text-align: center; padding: 2rem;">Gagal memuat jadwal. Silakan coba lagi.</p>';
        });
}

function displayScheduleData(bookings, statusFilter = 'all') {
    const scheduleContainer = document.getElementById('barberJadwal');
    
    if (!bookings || bookings.length === 0) {
        scheduleContainer.innerHTML = 
            '<p style="color: var(--text-grey); text-align: center; padding: 2rem;">Tidak ada jadwal booking untuk filter ini</p>';
        return;
    }
    
    // Kelompokkan booking berdasarkan barber
    const bookingsByBarber = {};
    bookings.forEach(booking => {
        if (!bookingsByBarber[booking.barberName]) {
            bookingsByBarber[booking.barberName] = [];
        }
        bookingsByBarber[booking.barberName].push(booking);
    });
    
    // Buat HTML untuk setiap barber
    let html = '<div class="jadwal-columns">';
    
    for (const [barberName, barberBookings] of Object.entries(bookingsByBarber)) {
        // Urutkan berdasarkan waktu
        barberBookings.sort((a, b) => a.time.localeCompare(b.time));
        
        html += `
        <div class="barber-column">
            <div class="barber-name">
                <img class="barber-icon" src="/assets/img/barber-icon.png" alt="Barber">
                <span>${barberName}</span>
            </div>
            <div class="subtext">${barberBookings.length} booking ${statusFilter !== 'all' ? getStatusText(statusFilter) : ''} hari ini</div>
        `;
        
        barberBookings.forEach(booking => {
            let statusClass = '';
            let statusText = '';
            
            switch(booking.status) {
                case 'pending':
                    statusClass = 'menunggu';
                    statusText = 'Menunggu';
                    break;
                case 'confirmed':
                    statusClass = 'check-in';
                    statusText = 'Check-in';
                    break;
                case 'in_progress':
                    statusClass = 'cukur';
                    statusText = 'Cukur';
                    break;
                case 'completed':
                    statusClass = 'selesai';
                    statusText = 'Selesai';
                    break;
                case 'cancelled':
                    statusClass = 'cancelled';
                    statusText = 'Dibatalkan';
                    break;
                default:
                    statusClass = 'menunggu';
                    statusText = 'Status Tidak Diketahui';
            }
            
            html += `
            <div class="booking-card">
                <div class="booking-left">
                    <div class="booking-code">${booking.id}</div>
                    <div class="booking-service">${booking.serviceName}</div>
                    <div class="booking-name">${booking.userName}</div>
                </div>
                <div class="booking-right">
                    <div class="booking-time">${booking.time}</div>
                    <div class="booking-status ${statusClass}">${statusText}</div>
                </div>
            </div>
            `;
        });
        
        html += '</div>'; // close barber-column
    }
    
    html += '</div>'; // close jadwal-columns
    scheduleContainer.innerHTML = html;
}

function getStatusText(status) {
    switch(status) {
        case 'pending':
            return 'Menunggu';
        case 'confirmed':
            return 'Check-in';
        case 'in_progress':
            return 'Cukur';
        case 'completed':
            return 'Selesai';
        case 'cancelled':
            return 'Dibatalkan';
        default:
            return status;
    }
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
                
                // Reload schedule data
                const statusFilter = document.querySelector('.status-tab-btn.active')?.getAttribute('data-status') || 'all';
                loadScheduleData(statusFilter);
                
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
        
        const statusFilter = document.querySelector('.status-tab-btn.active')?.getAttribute('data-status') || 'all';
        loadScheduleData(statusFilter);
    }, 30000); // 30 seconds
}

// Initialize real-time features
document.addEventListener('DOMContentLoaded', function() {
    setupRealTimeUpdates();
    setupAutomaticRefresh();
});
</script>

<style>
.booking-status-tabs {
    display: flex;
    gap: 5px;
    margin: 1.5rem 0;
    border-bottom: 1px solid var(--border-subtle);
    padding-bottom: 10px;
}

.status-tab-btn {
    padding: 8px 16px;
    background: var(--bg-secondary);
    border: 1px solid var(--border-subtle);
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9rem;
    color: var(--text-light);
}

.status-tab-btn:hover {
    background: var(--bg-hover);
}

.status-tab-btn.active {
    background: var(--accent-gold);
    color: var(--text-dark);
    font-weight: bold;
}

.cukur {
    color: #4A90E2; /* Blue color for cukur status */
}
</style>

@endsection