@extends('admin.admindashboard')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <a href="{{ route('admin.schedule') }}" class="sub-nav-item" data-tab="jadwal">Jadwal</a>
        <a href="{{ route('admin.report') }}" class="sub-nav-item active" data-tab="report">Report</a>
    </nav>

    <div class="sub-content-container">
        <div class="tab-content active" id="report">
            <div class="content-card">
                <h4>Laporan & Analisis</h4>
                <p style="color: var(--text-grey); margin-bottom: 1.5rem;">Atur parameter laporan dan lakukan generate data.</p>
                <div class="form-group" style="display: flex; gap: 15px;">
                    <div style="flex: 1;">
                        <label for="tipe-laporan">Periode Laporan</label>
                        <select id="tipe-laporan">
                            <option value="Harian">Harian</option>
                            <option value="Bulanan">Bulanan</option>
                        </select>
                    </div>
                    <div style="flex: 1;">
                        <label for="tanggal-laporan">Tanggal / Bulan</label>
                        <input type="date" id="tanggal-laporan" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div style="display: flex; gap: 10px; margin-bottom: 2rem;">
                    <button class="btn-base btn-primary" id="generateReportButton" style="flex: 1;"><i class="fas fa-filter"></i> Generate</button>
                    <button class="btn-secondary-outline" id="exportExcelButton" style="flex: 1; font-weight: 500;"><i class="fas fa-download"></i> Export Excel</button>
                </div>

                <div class="report-summary-grid" id="reportSummaryGrid">
                    <div class="summary-card report-summary-card" style="border-left-color: var(--button-info);"><p>Total Booking</p><h3 id="reportTotalBooking">...</h3></div>
                    <div class="summary-card report-summary-card" style="border-left-color: var(--accent-gold);"><p>Total Revenue</p><h3 class="revenue" id="reportTotalRevenue">...</h3></div>
                    <div class="summary-card report-summary-card" style="border-left-color: var(--button-success);"><p>Selesai</p><h3 class="selesai" id="reportTotalSelesai">...</h3></div>
                </div>

                <div class="report-tabs" id="reportTabs">
                    <button class="active" data-report="performa">Performa Barber</button>
                    <button data-report="tren">Tren Booking</button>
                    <button data-report="grafik">Grafik</button>
                </div>
                
                <div class="report-tab-content active" id="performa">
                    <div class="content-card" style="padding: 1.5rem; margin-top: 0;">
                        <h5 style="font-family: var(--font-heading); font-size: 1.2rem; color: var(--accent-gold); margin-bottom: 1.5rem;">Performa Barber Detail</h5>
                        <table class="performance-table" id="performanceTable">
                            <thead>
                                <tr>
                                    <th>Barber</th><th>Booking</th><th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody id="performanceTableBody">
                                <tr><td colspan="3" style="text-align: center;">Memuat data...</td></tr>
                            </tbody>
                        </table>
                        
                        <h5 style="font-family: var(--font-heading); font-size: 1.2rem; color: var(--accent-gold); margin: 2rem 0 1.5rem;">Performa Layanan Detail</h5>
                        <table class="performance-table" id="serviceTable">
                            <thead>
                                <tr>
                                    <th>Layanan</th><th>Terjual</th><th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody id="serviceTableBody">
                                <tr><td colspan="3" style="text-align: center;">Memuat data...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="report-tab-content" id="tren">
                    <div class="content-card" style="margin-top: 0;">
                        <h5 style="font-family: var(--font-heading); font-size: 1.2rem; color: var(--accent-gold); margin-bottom: 1.5rem;">Analisis Tren Harian</h5>
                        <div class="trend-card conversion">
                            <h5>Conversion Rate (Booking Selesai / Total)</h5>
                            <strong id="trendConversionRate">...</strong>
                        </div>
                        <div class="trend-card revenue">
                            <h5>Rata-rata Revenue per Booking</h5>
                            <strong id="trendAvgRevenue">...</strong>
                        </div>
                        <div class="trend-card rasio">
                            <h5>Rasio Online : Walk-in</h5>
                            <strong id="trendRasio">...</strong>
                        </div>
                        <div class="trend-card top-barber">
                            <h5>Top Performer Hari Ini</h5>
                            <strong id="trendTopBarber">...</strong>
                        </div>
                    </div>
                </div>

                <div class="report-tab-content" id="grafik">
                    <div class="content-card" style="margin-top: 0;">
                        <h5 style="font-family: var(--font-heading); font-size: 1.2rem; color: var(--accent-gold); margin-bottom: 1.5rem;">Visualisasi Data</h5>
                        
                        <!-- Chart Type Tabs -->
                        <div class="chart-tabs" style="display: flex; gap: 5px; margin: 1.5rem 0;">
                            <button class="chart-tab-btn active" data-chart-type="barber-revenue">Pendapatan Barber</button>
                            <button class="chart-tab-btn" data-chart-type="service-revenue">Pendapatan Layanan</button>
                            <button class="chart-tab-btn" data-chart-type="booking-time">Booking Berdasarkan Waktu</button>
                        </div>
                        
                        <div>
                            <canvas id="revenueChart" width="400" height="300"></canvas>
                        </div>
                        <p style="margin-top: 15px; font-size: 0.9rem; color: var(--text-grey);">Grafik akan menunjukkan perbandingan pendapatan antar barber.</p>
                    </div>
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
    
    // Load data laporan
    loadReportData();
    
    // Event listener untuk generate button
    document.getElementById('generateReportButton').addEventListener('click', function() {
        loadReportData();
    });
    
    // Event listener untuk export excel button
    document.getElementById('exportExcelButton').addEventListener('click', function() {
        const reportType = document.getElementById('tipe-laporan').value;
        const reportDate = document.getElementById('tanggal-laporan').value;
        
        // Redirect ke route untuk export excel dengan parameter
        window.location.href = `/admin/reports/export/excel?report_type=${reportType}&date=${reportDate}`;
    });
    
    // Event listener untuk report tabs
    const reportTabs = document.querySelectorAll('#reportTabs button');
    reportTabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            // Hapus class active dari semua tab
            reportTabs.forEach(t => t.classList.remove('active'));
            // Tambahkan class active ke tab yang diklik
            this.classList.add('active');
            
            // Sembunyikan semua content
            document.querySelectorAll('.report-tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Tampilkan content yang sesuai
            const targetContent = document.getElementById(this.getAttribute('data-report'));
            if (targetContent) {
                targetContent.classList.add('active');
            }
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

// Load data laporan
function loadReportData() {
    const reportType = document.getElementById('tipe-laporan').value;
    const reportDate = document.getElementById('tanggal-laporan').value;
    
    // Ambil data dari API
    fetch(`{{ route("admin.dashboard.data") }}?date=${reportDate}`)
        .then(response => response.json())
        .then(data => {
            // Store revenue data in a variable for later use
            loadReportData.revenue = data.revenue;
    
            updateReportSummary(data.summary, data.revenue);
            updatePerformanceReport(data.todaysBookings, data.revenue);
            updateTrendReport(data.todaysBookings, data.summary, data.revenue);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal memuat data laporan');
        });
}

function updateReportSummary(summary, revenue) {
    document.getElementById('reportTotalBooking').textContent = summary.total;
    document.getElementById('reportTotalSelesai').textContent = summary.selesai;
    
    // Update total revenue if available in the response
    if (revenue && revenue.total !== undefined) {
        document.getElementById('reportTotalRevenue').textContent = 'Rp ' + revenue.total.toLocaleString('id-ID');
    } else {
        document.getElementById('reportTotalRevenue').textContent = 'Rp 0';
    }
}

function updatePerformanceReport(bookings, revenue) {     
    if (!bookings || bookings.length === 0) {
        document.getElementById('performanceTableBody').innerHTML = 
            '<tr><td colspan="3" style="text-align: center;">Tidak ada data booking</td></tr>';
        document.getElementById('serviceTableBody').innerHTML = 
            '<tr><td colspan="3" style="text-align: center;">Tidak ada data booking</td></tr>';
        return;
    }
    
    // If revenue data is available, use it for more accurate reporting
    if (revenue && revenue.byBarber) {
        // Display barber performance from revenue data
        let barberRows = '';
        for (const [barberName, performance] of Object.entries(revenue.byBarber)) {
            barberRows += `
            <tr>
                <td>${barberName}</td>
                <td>${performance.bookingCount}</td>
                <td>Rp ${performance.revenue.toLocaleString('id-ID')}</td>
            </tr>
            `;
        }
        document.getElementById('performanceTableBody').innerHTML = barberRows;
    } else {
        // Fallback to previous method if revenue data not available
        const barberPerformance = {};
        const servicePerformance = {};
        
        bookings.forEach(booking => {
            // Tambahkan ke performa barber
            if (!barberPerformance[booking.barberName]) {
                barberPerformance[booking.barberName] = {
                    bookingCount: 0,
                    revenue: 0
                };
            }
            barberPerformance[booking.barberName].bookingCount++;
            barberPerformance[booking.barberName].revenue += (booking.totalPrice || 0);
            
            // Tambahkan ke performa layanan
            if (!servicePerformance[booking.serviceName]) {
                servicePerformance[booking.serviceName] = {
                    bookingCount: 0,
                    revenue: 0
                };
            }
            servicePerformance[booking.serviceName].bookingCount++;
            servicePerformance[booking.serviceName].revenue += (booking.totalPrice || 0);
        });
        
        // Tampilkan performa barber
        let barberRows = '';
        for (const [barberName, performance] of Object.entries(barberPerformance)) {
            barberRows += `
            <tr>
                <td>${barberName}</td>
                <td>${performance.bookingCount}</td>
                <td>Rp ${performance.revenue.toLocaleString('id-ID')}</td>
            </tr>
            `;
        }
        document.getElementById('performanceTableBody').innerHTML = barberRows;
    }
    
    // Display service performance
    if (revenue && revenue.byService) {
        let serviceRows = '';
        for (const [serviceName, performance] of Object.entries(revenue.byService)) {
            serviceRows += `
            <tr>
                <td>${serviceName}</td>
                <td>${performance.bookingCount}</td>
                <td>Rp ${performance.revenue.toLocaleString('id-ID')}</td>
            </tr>
            `;
        }
        document.getElementById('serviceTableBody').innerHTML = serviceRows;
    } else {
        // Fallback to previous method if revenue data not available
        const servicePerformance = {};
        
        bookings.forEach(booking => {
            if (!servicePerformance[booking.serviceName]) {
                servicePerformance[booking.serviceName] = {
                    bookingCount: 0,
                    revenue: 0
                };
            }
            servicePerformance[booking.serviceName].bookingCount++;
            servicePerformance[booking.serviceName].revenue += (booking.totalPrice || 0);
        });
        
        let serviceRows = '';
        for (const [serviceName, performance] of Object.entries(servicePerformance)) {
            serviceRows += `
            <tr>
                <td>${serviceName}</td>
                <td>${performance.bookingCount}</td>
                <td>Rp ${performance.revenue.toLocaleString('id-ID')}</td>
            </tr>
            `;
        }
        document.getElementById('serviceTableBody').innerHTML = serviceRows;
    }
}

function updateTrendReport(bookings, summary, revenue) {
    // Hitung conversion rate
    const conversionRate = summary.total > 0 ? (summary.selesai / summary.total * 100).toFixed(2) : 0;
    document.getElementById('trendConversionRate').textContent = `${conversionRate}%`;
    
    // Hitung rata-rata revenue per booking
    if (summary.selesai > 0 && revenue && revenue.total !== undefined) {
        const avgRevenue = revenue.total / summary.selesai;
        document.getElementById('trendAvgRevenue').textContent = 'Rp ' + avgRevenue.toLocaleString('id-ID', {maximumFractionDigits: 0});
    } else {
        document.getElementById('trendAvgRevenue').textContent = 'Rp 0';
    }
    
    // Hitung rasio online : walk-in
    if (summary.online + summary.walkIn > 0) {
        const onlinePercent = (summary.online / (summary.online + summary.walkIn) * 100).toFixed(0);
        const walkInPercent = (summary.walkIn / (summary.online + summary.walkIn) * 100).toFixed(0);
        document.getElementById('trendRasio').textContent = `${onlinePercent}% : ${walkInPercent}%`;
    } else {
        document.getElementById('trendRasio').textContent = '0% : 0%';
    }
    
    // Tentukan top performer berdasarkan revenue
    if (revenue && revenue.byBarber) {
        let topBarber = '';
        let maxRevenue = 0;
        for (const [barber, performance] of Object.entries(revenue.byBarber)) {
            if (performance.revenue > maxRevenue) {
                maxRevenue = performance.revenue;
                topBarber = barber;
            }
        }
        
        document.getElementById('trendTopBarber').textContent = topBarber || 'Tidak ada data';
    } else {
        // Fallback to previous method if revenue data not available
        if (bookings.length > 0) {
            // Kelompokkan booking per barber
            const bookingsByBarber = {};
            bookings.forEach(booking => {
                if (!bookingsByBarber[booking.barberName]) {
                    bookingsByBarber[booking.barberName] = 0;
                }
                bookingsByBarber[booking.barberName]++;
            });
            
            // Cari barber dengan booking terbanyak
            let topBarber = '';
            let maxBookings = 0;
            for (const [barber, count] of Object.entries(bookingsByBarber)) {
                if (count > maxBookings) {
                    maxBookings = count;
                    topBarber = barber;
                }
            }
            
            document.getElementById('trendTopBarber').textContent = topBarber || 'Tidak ada data';
        } else {
            document.getElementById('trendTopBarber').textContent = 'Tidak ada data';
        }
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
                
                // Update report data
                loadReportData();
                
                // Update the last updated time
                updateLastUpdatedTime();
            });
    }
}

// Set up automatic refresh every 60 seconds for reports (less frequent than other pages)
function setupAutomaticRefresh() {
    setInterval(() => {
        const currentDate = document.getElementById('date-selector-input').value;
        loadDashboardData(currentDate);
        loadReportData();
    }, 60000); // 60 seconds
}

// Initialize chart
let revenueChart = null;

function initRevenueChart(revenueData, bookingsData, chartType = 'barber-revenue') {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    // Destroy existing chart if it exists
    if (revenueChart) {
        revenueChart.destroy();
    }
    
    let chartData = null;
    let chartTitle = '';
    
    switch(chartType) {
        case 'barber-revenue':
            chartTitle = 'Perbandingan Pendapatan Antar Barber';
            if (!revenueData || !revenueData.byBarber) {
                // Create empty chart with placeholder data
                chartData = {
                    labels: ['Tidak ada data'],
                    datasets: [{
                        label: 'Pendapatan (Rp)',
                        data: [0],
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }]
                };
            } else {
                // Prepare data for the chart
                const barbers = Object.keys(revenueData.byBarber);
                const revenues = barbers.map(barber => revenueData.byBarber[barber].revenue);
                
                // Generate colors for each barber
                const backgroundColors = barbers.map((_, index) => {
                    const hue = (index * 137.508) % 360; // Golden angle for color distribution
                    return `hsla(${hue}, 70%, 60%, 0.6)`;
                });
                
                const borderColors = barbers.map((_, index) => {
                    const hue = (index * 137.508) % 360;
                    return `hsla(${hue}, 70%, 40%, 1)`;
                });
                
                chartData = {
                    labels: barbers,
                    datasets: [{
                        label: 'Pendapatan (Rp)',
                        data: revenues,
                        backgroundColor: backgroundColors,
                        borderColor: borderColors,
                        borderWidth: 1
                    }]
                };
            }
            break;
            
        case 'service-revenue':
            chartTitle = 'Perbandingan Pendapatan Antar Layanan';
            if (!revenueData || !revenueData.byService) {
                // Create empty chart with placeholder data
                chartData = {
                    labels: ['Tidak ada data'],
                    datasets: [{
                        label: 'Pendapatan (Rp)',
                        data: [0],
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }]
                };
            } else {
                // Prepare data for the chart
                const services = Object.keys(revenueData.byService);
                const revenues = services.map(service => revenueData.byService[service].revenue);
                
                // Generate colors for each service
                const backgroundColors = services.map((_, index) => {
                    const hue = (index * 137.508) % 360; // Golden angle for color distribution
                    return `hsla(${hue}, 70%, 60%, 0.6)`;
                });
                
                const borderColors = services.map((_, index) => {
                    const hue = (index * 137.508) % 360;
                    return `hsla(${hue}, 70%, 40%, 1)`;
                });
                
                chartData = {
                    labels: services,
                    datasets: [{
                        label: 'Pendapatan (Rp)',
                        data: revenues,
                        backgroundColor: backgroundColors,
                        borderColor: borderColors,
                        borderWidth: 1
                    }]
                };
            }
            break;
            
        case 'booking-time':
            chartTitle = 'Total Booking Berdasarkan Waktu Booking';
            if (!bookingsData || bookingsData.length === 0) {
                // Create empty chart with placeholder data
                chartData = {
                    labels: ['Tidak ada data'],
                    datasets: [{
                        label: 'Jumlah Booking',
                        data: [0],
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                };
            } else {
                // Group bookings by time
                const timeCounts = {};
                bookingsData.forEach(booking => {
                    const time = booking.time;
                    if (!timeCounts[time]) {
                        timeCounts[time] = 0;
                    }
                    timeCounts[time]++;
                });
                
                // Sort times chronologically
                const sortedTimes = Object.keys(timeCounts).sort();
                
                // Generate colors for each time slot
                const backgroundColors = sortedTimes.map((_, index) => {
                    const hue = (index * 137.508) % 360; // Golden angle for color distribution
                    return `hsla(${hue}, 70%, 60%, 0.6)`;
                });
                
                const borderColors = sortedTimes.map((_, index) => {
                    const hue = (index * 137.508) % 360;
                    return `hsla(${hue}, 70%, 40%, 1)`;
                });
                
                chartData = {
                    labels: sortedTimes,
                    datasets: [{
                        label: 'Jumlah Booking',
                        data: sortedTimes.map(time => timeCounts[time]),
                        backgroundColor: backgroundColors,
                        borderColor: borderColors,
                        borderWidth: 1
                    }]
                };
            }
            break;
    }
    
    revenueChart = new Chart(ctx, {
        type: 'bar',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            if (chartType === 'barber-revenue' || chartType === 'service-revenue') {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            } else {
                                return value;
                            }
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: chartTitle
                }
            }
        }
    });
}

// Initialize chart type tabs
document.addEventListener('DOMContentLoaded', function() {
    const chartTabButtons = document.querySelectorAll('.chart-tab-btn');
    chartTabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            chartTabButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            // Get the chart type
            const chartType = this.getAttribute('data-chart-type');
            
            // Update the chart with the selected type
            if (loadReportData.revenue && loadReportData.bookings) {
                initRevenueChart(loadReportData.revenue, loadReportData.bookings, chartType);
            } else {
                // If data is not loaded yet, use empty data
                initRevenueChart(null, null, chartType);
            }
        });
    });
    
    // Initialize real-time features
    setupRealTimeUpdates();
    setupAutomaticRefresh();
});

// Update the loadReportData function to include chart initialization
function loadReportData() {
    const reportType = document.getElementById('tipe-laporan').value;
    const reportDate = document.getElementById('tanggal-laporan').value;
    
    // Ambil data dari API
    fetch(`{{ route("admin.dashboard.data") }}?date=${reportDate}`)
        .then(response => response.json())
        .then(data => {
            // Store revenue and bookings data in variables for later use
            loadReportData.revenue = data.revenue;
            loadReportData.bookings = data.todaysBookings;
    
            updateReportSummary(data.summary, data.revenue);
            updatePerformanceReport(data.todaysBookings, data.revenue);
            updateTrendReport(data.todaysBookings, data.summary, data.revenue);
            
            // Determine which chart type is currently active and update the chart
            const activeChartTab = document.querySelector('.chart-tab-btn.active');
            const chartType = activeChartTab ? activeChartTab.getAttribute('data-chart-type') : 'barber-revenue';
            
            // Initialize or update the chart
            initRevenueChart(data.revenue, data.todaysBookings, chartType);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal memuat data laporan');
        });
}
</script>

<style>
.cukur {
    color: #4A90E2; /* Blue color for cukur count */
}

.chart-tabs {
    display: flex;
    gap: 5px;
    margin: 1.5rem 0;
    border-bottom: 1px solid var(--border-subtle);
    padding-bottom: 10px;
}

.chart-tab-btn {
    padding: 8px 16px;
    background: var(--bg-secondary);
    border: 1px solid var(--border-subtle);
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9rem;
    color: var(--text-light);
}

.chart-tab-btn:hover {
    background: var(--bg-hover);
}

.chart-tab-btn.active {
    background: var(--accent-gold);
    color: var(--text-dark);
    font-weight: bold;
}
</style>

@endsection