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
        <div class="summary-card"><p>Selesai</p><h3 class="selesai" id="selesaiCount">...</h3></div>
    </div>

    <div class="whatsapp-bar">
        <div>
            <p style="margin-bottom: 5px; color: var(--text-light);"><i class="fab fa-whatsapp" style="color: var(--button-success); margin-right: 5px;"></i> WhatsApp Barbershop (Barber Coords)</p>
            <p style="font-size: 0.8rem; color: var(--text-grey);">Kontak cepat untuk koordinasi jadwal dan ketersediaan barber.</p>
        </div>
        <button id="waButton" class="btn-base" style="background-color: var(--button-success); color: var(--text-light); font-size: 0.9rem; text-transform: none;"><i class="fas fa-phone-alt"></i> Hubungi</button>
    </div>

    <nav class="sub-nav" id="subNav">
        <a href="{{ route('admin.checkin') }}" class="sub-nav-item {{ request()->routeIs('admin.checkin') ? 'active' : (request()->routeIs('admin.dashboard') ? 'active' : '') }}" data-tab="check">Check-in</a>
        <a href="{{ route('admin.walkin') }}" class="sub-nav-item {{ request()->routeIs('admin.walkin') ? 'active' : '' }}" data-tab="walk">Walk-in</a>
        <a href="{{ route('admin.schedule') }}" class="sub-nav-item {{ request()->routeIs('admin.schedule') ? 'active' : '' }}" data-tab="jadwal">Jadwal</a>
        <a href="{{ route('admin.report') }}" class="sub-nav-item {{ request()->routeIs('admin.report') ? 'active' : '' }}" data-tab="report">Report</a>
    </nav>

    <div class="sub-content-container">
        @yield('sub-content')
    </div>
</div>

<script>
// JavaScript untuk halaman admin utama
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
    
    // Event listener untuk tombol select date
    const selectDateButton = document.getElementById('selectDateButton');
    const dateInput = document.getElementById('date-selector-input');
    if (selectDateButton) {
        selectDateButton.addEventListener('click', function() {
            dateInput.showPicker();
        });
    }
    
    // Update tanggal
    updateLastUpdatedTime();
});

function loadDashboardData(date) {
    fetch(`{{ route("admin.dashboard.data") }}?date=${date}`)
        .then(response => response.json())
        .then(data => {
            updateDashboardSummary(data.summary);
            updateWaitingQueue(data.waitingQueue);
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
    document.getElementById('selesaiCount').textContent = summary.selesai;
}

function updateWaitingQueue(waitingQueue) {
    const waitingList = document.getElementById('waitingQueueList');
    if (!waitingList) return;
    
    if (waitingQueue.length === 0) {
        waitingList.innerHTML = '<div class="no-booking">Tidak ada antrian menunggu</div>';
        document.getElementById('waitingQueueHeader').textContent = 'Tidak ada antrian menunggu';
    } else {
        waitingList.innerHTML = '';
        document.getElementById('waitingQueueHeader').textContent = `Antrian Menunggu (${waitingQueue.length} pelanggan)`;
        
        waitingQueue.forEach(booking => {
            const bookingItem = document.createElement('div');
            bookingItem.className = 'jadwal-item';
            bookingItem.innerHTML = `
                <h5>${booking.userName}</h5>
                <p style="margin: 5px 0; color: var(--text-grey);">${booking.serviceName} dengan ${booking.barberName}</p>
                <p style="color: var(--accent-gold); font-weight: 700;">${booking.time}</p>
                <div style="margin-top: 10px;">
                    <button class="check-actions-btn" onclick="checkInBooking('${booking.id}')">Check-in</button>
                    <button class="check-actions-btn" style="background-color: var(--button-danger);" onclick="cancelBooking('${booking.id}')">Cancel</button>
                </div>
            `;
            waitingList.appendChild(bookingItem);
        });
    }
}

function checkInBooking(bookingId) {
    fetch(`{{ route("admin.bookings.checkin", ["id" => "ID_PLACEHOLDER"]) }}`.replace('ID_PLACEHOLDER', bookingId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data) {
            alert('Check-in berhasil!');
            // Refresh data setelah check-in
            const selectedDate = document.getElementById('date-selector-input').value;
            loadDashboardData(selectedDate);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat melakukan check-in.');
    });
}

function cancelBooking(bookingId) {
    if (confirm('Apakah Anda yakin ingin membatalkan booking ini?')) {
        fetch(`{{ route("admin.bookings.update.status", ["id" => "ID_PLACEHOLDER"]) }}`.replace('ID_PLACEHOLDER', bookingId), {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ status: 'cancelled' })
        })
        .then(response => response.json())
        .then(data => {
            if (data) {
                alert('Booking berhasil dibatalkan!');
                // Refresh data setelah pembatalan
                const selectedDate = document.getElementById('date-selector-input').value;
                loadDashboardData(selectedDate);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat membatalkan booking.');
        });
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

// Initialize real-time features
document.addEventListener('DOMContentLoaded', function() {
    setupRealTimeUpdates();
    setupAutomaticRefresh();
    
    const subNavItems = document.querySelectorAll('.sub-nav-item');
    subNavItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Hapus class active dari semua item
            subNavItems.forEach(navItem => navItem.classList.remove('active'));
            
            // Tambahkan class active ke item yang diklik
            this.classList.add('active');
            
            // Redirect ke halaman yang sesuai
            window.location.href = this.href;
        });
    });
});
</script>
@endsection