@extends('admin.admindashboard')

@section('content')
    <div class="main-tab-content active" id="admin-tab-content">
        <div class="hero-dashboard">
            <h1 class="dashboard-header">Dashboard <span>Admin</span></h1>

            <div class="sync-info">
                <span><i class="fas fa-circle"
                        style="font-size: 0.7rem; color: var(--button-success); margin-right: 5px;"></i> Sinkronisasi
                    Aktif</span>
                <span id="lastUpdate"></span>
                <div class="date-input-wrapper">
                    <button id="selectDateButton"><i class="fas fa-calendar-alt"></i> Pilih Tanggal</button>
                    <input type="date" id="date-selector-input" value="{{ date('Y-m-d') }}">
                </div>
            </div>
        </div>

        <div class="booking-summary-grid">
            <div class="summary-card">
                <p>Total Booking</p>
                <h3 class="total" id="totalBooking">...</h3>
            </div>
            <div class="summary-card">
                <p>Online</p>
                <h3 class="online" id="onlineBooking">...</h3>
            </div>
            <div class="summary-card">
                <p>Walk-in</p>
                <h3 class="walk-in" id="walkInBooking">...</h3>
            </div>
            <div class="summary-card">
                <p>Menunggu</p>
                <h3 class="menunggu" id="menungguCount">...</h3>
            </div>
            <div class="summary-card">
                <p>Check-in</p>
                <h3 class="check-in" id="checkInCount">...</h3>
            </div>
            <div class="summary-card">
                <p>Cukur</p>
                <h3 class="cukur" id="cukurCount">...</h3>
            </div>
            <div class="summary-card">
                <p>Selesai</p>
                <h3 class="selesai" id="selesaiCount">...</h3>
            </div>
            
        </div>

        </div>

        <nav class="sub-nav" id="subNav">
            <a href="{{ route('admin.checkin') }}" class="sub-nav-item active" data-tab="check">Check-in</a>
            <a href="{{ route('admin.walkin') }}" class="sub-nav-item" data-tab="walk">Walk-in</a>
            <a href="{{ route('admin.schedule') }}" class="sub-nav-item" data-tab="jadwal">Jadwal</a>
            <a href="{{ route('admin.report') }}" class="sub-nav-item" data-tab="report">Report</a>
        </nav>

        <div class="sub-content-container">
            <div class="tab-content active" id="check">
                <div class="content-card">
                    <div style="text-align: right; margin-bottom: 1rem;">
                        <button class="check-actions-btn auto-cancel" id="autoCancelButton"><i
                                class="fas fa-times-circle"></i> Auto Cancel</button>
                        <button class="check-actions-btn refresh" id="refreshButton"><i class="fas fa-sync-alt"></i>
                            Refresh</button>
                    </div>
                    <h4>Check-in Pelanggan</h4>
                    <p style="margin-bottom: 1.5rem; color: var(--text-grey);">Input kode booking (Online/Walk-in) untuk
                        proses check-in.</p>
                    <div class="check-input-group">
                        <input type="text" id="bookingCodeInput" placeholder="Masukkan kode booking (6 karakter)">
                        <button id="checkInButton" class="btn-base"><i class="fas fa-check"></i> Check-in</button>
                    </div>

                    <!-- Tabs for viewing different booking statuses -->
                    <div class="booking-status-tabs" style="margin: 1.5rem 0;">
                        <button class="status-tab-btn active" data-status="waiting">Menunggu
                            ({{ $waitingQueueCount ?? '...' }})</button>
                        <button class="status-tab-btn" data-status="checked-in">Check-in</button>
                        <button class="status-tab-btn" data-status="cukur">Cukur</button>
                        <button class="status-tab-btn" data-status="completed">Selesai</button>
                    </div>

                    <h4 id="bookingListHeader"
                        style="font-size: 1.2rem; border-top: var(--border-subtle); padding-top: 1.5rem; margin-top: 1.5rem; color: var(--accent-gold); text-transform: none;">
                        Menunggu</h4>
                    <div id="bookingList">
                        <!-- Booking list will be populated here -->
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

            (currentDate);

            // Event listener untuk tombol refresh
            const refreshBtn = document.getElementById('refreshButton');
            if (refreshBtn) {
                refreshBtn.addEventListener('click', function() {
                    // Refresh halaman saat tombol refresh ditekan
                    location.reload();
                });
            }

            // Event listener untuk tombol auto cancel
            const autoCancelBtn = document.getElementById('autoCancelButton');
            if (autoCancelBtn) {
                autoCancelBtn.addEventListener('click', function() {
                    autoCancelOverdueBookings();
                });
            }

            // Event listener untuk tombol check-in
            const checkInButton = document.getElementById('checkInButton');
            if (checkInButton) {
                checkInButton.addEventListener('click', function() {
                    const bookingCode = document.getElementById('bookingCodeInput').value.trim();
                    if (bookingCode) {
                        checkInBooking(bookingCode);
                    } else {
                        alert('Silakan masukkan kode booking terlebih dahulu.');
                    }
                });
            }

            // Event listener untuk input booking code (enter key)
            const bookingCodeInput = document.getElementById('bookingCodeInput');
            if (bookingCodeInput) {
                bookingCodeInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        const bookingCode = this.value.trim();
                        if (bookingCode) {
                            checkInBooking(bookingCode);
                        } else {
                            alert('Silakan masukkan kode booking terlebih dahulu.');
                        }
                    }
                });
            }

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
                    showBookingsByStatus(status);
                });
            });

            // Update tanggal
            updateLastUpdatedTime();
        });

        function loadDashboardData(date) {
            fetch(`{{ route('admin.dashboard.data') }}?date=${date}`)
                .then(response => response.json())
                .then(data => {
                    updateDashboardSummary(data.summary);
                    // Initially show waiting bookings
                    updateBookingList(data.waitingQueue, 'waiting');
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

        function updateBookingList(bookings, status) {
            const bookingList = document.getElementById('bookingList');
            const header = document.getElementById('bookingListHeader');

            if (!bookingList) return;

            // Update header based on status
            let headerText = '';
            switch (status) {
                case 'waiting':
                    headerText = 'Antrian Menunggu';
                    break;
                case 'checked-in':
                    headerText = 'Antrian Check-in';
                    break;
                case 'cukur':
                    headerText = 'Antrian Cukur';
                    break;
                case 'completed':
                    headerText = 'Antrian Selesai';
                    break;
                default:
                    headerText = 'Daftar Booking';
            }

            if (bookings.length === 0) {
                bookingList.innerHTML = '<div class="no-booking">Tidak ada booking untuk status ini</div>';
                header.textContent = headerText;
            } else {
                bookingList.innerHTML = '';
                header.textContent = `${headerText} (${bookings.length} pelanggan)`;

                bookings.forEach(booking => {
                    const bookingItem = document.createElement('div');
                    bookingItem.className = 'jadwal-item';

                    // Status-specific buttons
                    let actionButtons = '';
                    switch (booking.status) {
                        case 'pending':
                            actionButtons = `
                        <button class="check-actions-btn" onclick="checkInBooking('${booking.id}')">Check-in</button>
                        <button class="check-actions-btn" style="background-color: var(--button-warning);" onclick="sendReminderWA('${booking.phone}', '${booking.userName}', '${booking.booking_date}', '${booking.time}', '${booking.barberName}')"><i class="fab fa-whatsapp"></i> WA</button>
                        <button class="check-actions-btn" style="background-color: var(--button-danger);" onclick="cancelBooking('${booking.id}')">Cancel</button>
                    `;
                            break;
                        case 'confirmed':
                            actionButtons = `
                        <button class="check-actions-btn" onclick="startCukur('${booking.id}')">Mulai Cukur</button>
                        <button class="check-actions-btn" style="background-color: var(--button-warning);" onclick="cancelBooking('${booking.id}')">Cancel</button>
                    `;
                            break;
                        case 'in_progress':
                            actionButtons = `
                        <button class="check-actions-btn" style="background-color: var(--button-success);" onclick="completeBooking('${booking.id}')">Selesai</button>
                        <button class="check-actions-btn" style="background-color: var(--button-warning);" onclick="cancelBooking('${booking.id}')">Cancel</button>
                    `;
                            break;
                        case 'completed':
                            actionButtons = `
                        <span style="color: var(--button-success); font-weight: bold;">Selesai</span>
                        <button class="check-actions-btn" style="background-color: var(--button-success); margin-top: 10px;" onclick="sendStrukWA('${booking.phone}', '${booking.userName}', '${booking.id}', '${booking.serviceName}', '${booking.barberName}', '${booking.totalPrice}')"><i class="fab fa-whatsapp"></i> Struk WA</button>
                    `;
                            break;
                        case 'cancelled':
                            actionButtons =
                                '<span style="color: var(--button-danger); font-weight: bold;">Dibatalkan</span>';
                            break;
                    }

                    bookingItem.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                    <div>
                        <h5>${booking.userName}</h5>
                        <p style="margin: 5px 0; color: var(--text-grey);">${booking.serviceName} dengan ${booking.barberName}</p>
                        <p style="color: var(--accent-gold); font-weight: 700;">${booking.time}</p>
                        <p style="color: var(--text-grey); font-size: 0.9rem;">Kode: ${booking.id}</p>
                    </div>
                    <div style="text-align: right;">
                        <div style="margin-bottom: 10px;">
                            <span class="booking-status-badge ${booking.status}">
                                ${getStatusText(booking.status)}
                            </span>
                        </div>
                        <div style="margin-top: 10px;">
                            ${actionButtons}
                        </div>
                    </div>
                </div>
            `;
                    bookingList.appendChild(bookingItem);
                });
            }
        }

        function showBookingsByStatus(status) {
            const currentDate = document.getElementById('date-selector-input').value;

            fetch(`{{ route('admin.dashboard.data') }}?date=${currentDate}`)
                .then(response => response.json())
                .then(data => {
                    let filteredBookings = [];
                    let summary = data.summary;

                    // Filter bookings by status
                    switch (status) {
                        case 'waiting':
                            filteredBookings = data.waitingQueue.filter(booking => booking.status === 'pending');
                            break;
                        case 'checked-in':
                            filteredBookings = data.todaysBookings.filter(booking => booking.status === 'confirmed');
                            break;
                        case 'cukur':
                            filteredBookings = data.todaysBookings.filter(booking => booking.status === 'in_progress');
                            break;
                        case 'completed':
                            filteredBookings = data.todaysBookings.filter(booking => booking.status === 'completed');
                            break;
                        default:
                            filteredBookings = data.todaysBookings;
                    }

                    updateBookingList(filteredBookings, status);
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memuat data booking.');
                });
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

        // Function to automatically cancel overdue bookings
        function autoCancelOverdueBookings() {
            if (confirm('Apakah Anda yakin ingin membatalkan semua booking yang sudah lewat waktu?')) {
                const currentDate = document.getElementById('date-selector-input').value;

                fetch(`{{ route('admin.dashboard.data') }}?date=${currentDate}`)
                    .then(response => response.json())
                    .then(data => {
                        // Filter bookings that are pending and overdue
                        const now = new Date();
                        const currentDateString = now.toISOString().split('T')[0]; // Format YYYY-MM-DD
                        const currentTime = now.toTimeString().substr(0, 5); // Format HH:MM

                        // Filter bookings that are pending and their time has passed
                        const overdueBookings = data.waitingQueue.filter(booking => {
                            // If it's not today's date, all pending bookings are overdue
                            if (currentDate !== booking.booking_date) {
                                return booking.status === 'pending' &&
                                    new Date(`${booking.booking_date}T${booking.time}`) < now;
                            } else {
                                // If it's today, only bookings with time that has passed are overdue
                                return booking.status === 'pending' && booking.time < currentTime;
                            }
                        });

                        if (overdueBookings.length === 0) {
                            alert('Tidak ada booking yang melewati waktu.');
                            return;
                        }

                        // Cancel each overdue booking
                        let cancelledCount = 0;
                        const promises = overdueBookings.map(booking => {
                            return fetch(`{{ route('admin.bookings.cancel', ['id' => 'ID_PLACEHOLDER']) }}`
                                    .replace('ID_PLACEHOLDER', booking.id), {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                                .getAttribute('content')
                                        }
                                    })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        cancelledCount++;
                                    }
                                })
                                .catch(error => {
                                    console.error('Error cancelling booking:', booking.id, error);
                                });
                        });

                        Promise.all(promises).then(() => {
                            alert(`Berhasil membatalkan ${cancelledCount} booking yang melewati waktu.`);
                            // Refresh the dashboard data after cancellation
                            loadDashboardData(currentDate);
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat mencari booking yang melewati waktu.');
                    });
            }
        }


        // Initialize real-time features
        document.addEventListener('DOMContentLoaded', function() {
            setupRealTimeUpdates();
            const currentDate = document.getElementById('date-selector-input').value;
            loadDashboardData(currentDate);
        });

        function getStatusText(status) {
            switch (status) {
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

        function checkInBooking(bookingId) {
            fetch(`{{ route('admin.bookings.checkin', ['id' => 'ID_PLACEHOLDER']) }}`.replace('ID_PLACEHOLDER',
                    bookingId), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        alert(data.message || 'Check-in berhasil!');
                        // Refresh data after check-in
                        const selectedDate = document.getElementById('date-selector-input').value;
                        loadDashboardData(selectedDate);
                        // Clear input if this is from input form
                        const bookingCodeInput = document.getElementById('bookingCodeInput');
                        if (bookingCodeInput) {
                            bookingCodeInput.value = '';
                        }
                    } else {
                        alert(data.message || 'Gagal melakukan check-in.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat melakukan check-in.');
                });
        }

        function startCukur(bookingId) {
            fetch(`{{ route('admin.bookings.cukur', ['id' => 'ID_PLACEHOLDER']) }}`.replace('ID_PLACEHOLDER', bookingId), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message || 'Status cukur dimulai!');
                        // Refresh data after updating status
                        const selectedDate = document.getElementById('date-selector-input').value;
                        loadDashboardData(selectedDate);
                    } else {
                        alert(data.message || 'Gagal memperbarui status.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memperbarui status.');
                });
        }

        function completeBooking(bookingId) {
            fetch(`{{ route('admin.bookings.complete', ['id' => 'ID_PLACEHOLDER']) }}`.replace('ID_PLACEHOLDER',
                    bookingId), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message || 'Booking selesai!');
                        // Refresh data after updating status
                        const selectedDate = document.getElementById('date-selector-input').value;

                        (selectedDate);
                    } else {
                        alert(data.message || 'Gagal menyelesaikan booking.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menyelesaikan booking.');
                });
        }

        function cancelBooking(bookingId) {
            if (confirm('Apakah Anda yakin ingin membatalkan booking ini?')) {
                fetch(`{{ route('admin.bookings.cancel', ['id' => 'ID_PLACEHOLDER']) }}`.replace('ID_PLACEHOLDER',
                        bookingId), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message || 'Booking berhasil dibatalkan!');
                            // Refresh data after cancellation
                            const selectedDate = document.getElementById('date-selector-input').value;

                            (selectedDate);
                        } else {
                            alert(data.message || 'Gagal membatalkan booking.');
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

        // Fungsi untuk mengirim pesan reminder WhatsApp
        function sendReminderWA(nomorTujuan, namaPelanggan, tanggalBooking, jamBooking, namaBarber) {
            // Hapus angka 0 di depan nomor telepon jika ada, dan ganti dengan 62
            nomorTujuan = nomorTujuan.replace(/^0/, '62');
            
            // Format tanggal ke dalam format yang lebih ramah
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            const tanggalFormatted = new Date(tanggalBooking).toLocaleDateString('id-ID', options);
            
            const isiPesan = `
Halo, Kak ${namaPelanggan}! ✂️

Ini adalah *pengingat booking* dari *BARBER GO*. Jangan lupa, Kakak punya jadwal cukur:

📅 Tanggal: ${tanggalFormatted}
⏰ Jam: ${jamBooking}
💈 Barberman: ${namaBarber}

Mohon datang tepat waktu ya agar antrian tetap nyaman 😊.
Jika berhalangan hadir, Kakak bisa reschedule melalui aplikasi BARBER GO.

📍 Alamat BARBER GO Barbershop:
https://maps.app.goo.gl/nQEVzTvmseKvNvNP6

Sampai ketemu di kursi cukur terbaik kami! 💇‍♂️🔥
`;

            const url = `https://api.whatsapp.com/send?phone=${nomorTujuan}&text=${encodeURIComponent(isiPesan)}`;
            window.open(url, "_blank");
        }

        // Fungsi untuk mengirim pesan struk WhatsApp
        function sendStrukWA(nomorTujuan, namaPelanggan, kodeBooking, namaService, namaBarber, totalHarga) {
            // Hapus angka 0 di depan nomor telepon jika ada, dan ganti dengan 62
            nomorTujuan = nomorTujuan.replace(/^0/, '62');

            const isiPesan = `
Halo, Kak ${namaPelanggan}! Terima kasih sudah cukur di *BARBER GO* 💈

Berikut adalah *struk pembayaran* Kakak:

🧾 Detail Transaksi:
• ID Booking: ${kodeBooking}
• Layanan: ${namaService}
• Barberman: ${namaBarber}
• Metode Pembayaran: Tunai
• Total: Rp${totalHarga.toLocaleString('id-ID')}

Kami harap Kakak puas dengan hasilnya 😊  
👉 Silakan beri rating dan ulasan singkat kami di Google Maps:  
https://maps.app.goo.gl/nQEVzTvmseKvNvNP6

Terima kasih banyak atas kunjungannya — sampai ketemu lagi di BARBER GO! 😎🔥
`;

            const url = `https://api.whatsapp.com/send?phone=${nomorTujuan}&text=${encodeURIComponent(isiPesan)}`;
            window.open(url, "_blank");
        }
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

        .booking-status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
        }

        .booking-status-badge.pending {
            background: var(--button-warning);
            color: white;
        }

        .booking-status-badge.confirmed {
            background: var(--accent-gold);
            color: white;
        }

        .booking-status-badge.in_progress {
            background: #4A90E2;
            /* Blue color for cukur status */
            color: white;
        }

        .booking-status-badge.completed {
            background: var(--button-success);
            color: white;
        }

        .booking-status-badge.cancelled {
            background: var(--button-danger);
            color: white;
        }

        .cukur {
            color: #4A90E2;
            /* Blue color for cukur count */
        }
    </style>
@endsection
