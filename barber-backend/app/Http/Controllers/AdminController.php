<?php

namespace App\Http\Controllers;

use App\Events\BookingStatusUpdated;
use App\Models\Barber;
use App\Models\Booking;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function __construct()
    {
        // Middleware 'isAdmin' is applied at the route level in web.php
        // All methods in this controller require admin access
    }

    public function dashboard()
    {
        return redirect()->route('admin.checkin');
    }

    public function getDashboardData(Request $request)
    {
        $date = $request->query('date', date('Y-m-d'));
        
        // Get booking summary for the date
        $summary = [
            'total' => Booking::where('booking_date', $date)->count(),
            'online' => Booking::where('booking_date', $date)
                              ->where('payment_method', '!=', 'Walk-in')
                              ->count(),
            'walkIn' => Booking::where('booking_date', $date)
                               ->where('payment_method', 'Walk-in')
                               ->count(),
            'menunggu' => Booking::where('booking_date', $date)
                                 ->where('status', 'pending')
                                 ->count(),
            'checkIn' => Booking::where('booking_date', $date)
                                ->where('status', 'confirmed')
                                ->count(),
            'cukur' => Booking::where('booking_date', $date)
                              ->where('status', 'in_progress')
                              ->count(),
            'selesai' => Booking::where('booking_date', $date)
                                ->where('status', 'completed')
                                ->count(),
        ];
        
        // Get waiting queue for the date
        $waitingQueue = Booking::where('booking_date', $date)
                              ->where('status', 'pending')
                              ->with(['user', 'service', 'barber'])
                              ->get()
                              ->map(function ($booking) {
                                  return [
                                      'id' => $booking->booking_id,
                                      'userName' => $booking->user ? $booking->user->name : 'Unknown User',
                                      'serviceName' => $booking->service ? $booking->service->name : 'Unknown Service',
                                      'barberName' => $booking->barber ? $booking->barber->name : 'Unknown Barber',
                                      'time' => $booking->booking_time,
                                      'status' => $booking->status,
                                      'phone' => $booking->phone,
                                      'booking_date' => $booking->booking_date,
                                  ];
                              });
        
        // Get today's bookings
        $todaysBookings = Booking::where('booking_date', $date)
                                ->with(['user', 'service', 'barber'])
                                ->get()
                                ->map(function ($booking) {
                                    return [
                                        'id' => $booking->booking_id,
                                        'userName' => $booking->user ? $booking->user->name : 'Unknown User',
                                        'serviceName' => $booking->service ? $booking->service->name : 'Unknown Service',
                                        'barberName' => $booking->barber ? $booking->barber->name : 'Unknown Barber',
                                        'totalPrice' => $booking->total_price,
                                        'time' => $booking->booking_time,
                                        'status' => $booking->status,
                                        'paymentStatus' => $booking->payment_status,
                                        'phone' => $booking->phone,
                                        'booking_date' => $booking->booking_date,
                                    ];
                                });
        
        // Get booking stats for chart (last 7 days)
        $bookingStats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $bookingStats[] = [
                'date' => $date,
                'count' => Booking::where('booking_date', $date)->count(),
            ];
        }
        
        // Calculate revenue data for reports
        $completedBookings = Booking::where('booking_date', $date)
                                   ->where('status', 'completed')
                                   ->with(['barber', 'service'])
                                   ->get();
        
        $totalRevenue = $completedBookings->sum('total_price');
        
        // Calculate revenue by barber
        $revenueByBarber = [];
        foreach ($completedBookings as $booking) {
            $barberName = $booking->barber ? $booking->barber->name : 'Unknown Barber';
            if (!isset($revenueByBarber[$barberName])) {
                $revenueByBarber[$barberName] = [
                    'bookingCount' => 0,
                    'revenue' => 0
                ];
            }
            $revenueByBarber[$barberName]['bookingCount']++;
            $revenueByBarber[$barberName]['revenue'] += $booking->total_price;
        }
        
        // Calculate revenue by service
        $revenueByService = [];
        foreach ($completedBookings as $booking) {
            $serviceName = $booking->service ? $booking->service->name : 'Unknown Service';
            if (!isset($revenueByService[$serviceName])) {
                $revenueByService[$serviceName] = [
                    'bookingCount' => 0,
                    'revenue' => 0
                ];
            }
            $revenueByService[$serviceName]['bookingCount']++;
            $revenueByService[$serviceName]['revenue'] += $booking->total_price;
        }
        
        return response()->json([
            'summary' => $summary,
            'waitingQueue' => $waitingQueue,
            'todaysBookings' => $todaysBookings,
            'bookingStats' => $bookingStats,
            'revenue' => [
                'total' => $totalRevenue,
                'byBarber' => $revenueByBarber,
                'byService' => $revenueByService,
            ],
        ]);
    }

    public function getAllUsers()
    {
        $users = User::all();
        return response()->json($users);
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'sometimes|string|max:15',
            'role' => 'sometimes|in:user,admin',
        ]);
        
        $user->update($request->only(['name', 'email', 'phone', 'role']));
        
        return response()->json($user);
    }

    public function getAllProducts()
    {
        $products = Product::all();
        return response()->json($products);
    }

    public function createProduct(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'stock_quantity' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $data = $request->only(['name', 'price', 'description', 'stock_quantity', 'status']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->handleImageUpload($request->file('image'), 'products');
        }

        $product = Product::create($data);

        return response()->json($product, 201);
    }

    public function updateProduct(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'price' => 'sometimes|integer|min:0',
            'description' => 'sometimes|string',
            'stock_quantity' => 'sometimes|integer|min:0',
            'status' => 'sometimes|in:active,inactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);
        
        $data = $request->only(['name', 'price', 'description', 'stock_quantity', 'status']);
        
        if ($request->hasFile('image')) {
            $data['image_path'] = $this->handleImageUpload($request->file('image'), 'products');
        }
        
        $product->update($data);
        
        return response()->json($product);
    }

    public function getProduct($id)
    {
        $product = Product::findOrFail($id);
        return response()->json($product);
    }

    public function deleteProduct($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        
        return response()->json(['message' => 'Product deleted successfully']);
    }

    public function getAllServices()
    {
        $services = Service::all();
        return response()->json($services);
    }

    public function getService($id)
    {
        $service = Service::findOrFail($id);
        return response()->json($service);
    }

    public function createService(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|integer|min:0',
            'duration' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $data = $request->only(['name', 'price', 'duration', 'description']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->handleImageUpload($request->file('image'), 'services');
        }

        $service = Service::create($data);

        return response()->json($service, 201);
    }

    public function updateService(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'price' => 'sometimes|integer|min:0',
            'duration' => 'sometimes|integer|min:0',
            'description' => 'sometimes|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);
        
        $data = $request->only(['name', 'price', 'duration', 'description']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->handleImageUpload($request->file('image'), 'services');
        }
        
        $service->update($data);
        
        return response()->json($service);
    }

    public function deleteService($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();
        
        return response()->json(['message' => 'Service deleted successfully']);
    }

    public function showBarbers()
    {
        $barbers = Barber::all();
        return view('admin.dashboard.barbers', compact('barbers'));
    }

    public function getAllBarbers()
    {
        $barbers = Barber::all();
        return response()->json($barbers);
    }

    public function createBarber(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'specialty' => 'nullable|string|max:255',
            'rating' => 'sometimes|numeric|min:0|max:5',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $data = $request->only(['name', 'specialty', 'rating', 'status']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->handleImageUpload($request->file('image'), 'barbers');
        }

        $barber = Barber::create($data);

        return response()->json($barber, 201);
    }

    public function updateBarber(Request $request, $id)
    {
        $barber = Barber::findOrFail($id);
        
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'specialty' => 'sometimes|string|max:255',
            'rating' => 'sometimes|numeric|min:0|max:5',
            'status' => 'sometimes|in:active,inactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);
        
        $data = $request->only(['name', 'specialty', 'rating', 'status']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->handleImageUpload($request->file('image'), 'barbers');
        }
        
        $barber->update($data);
        
        return response()->json($barber);
    }

    public function deleteBarber($id)
    {
        $barber = Barber::findOrFail($id);
        $barber->delete();
        
        return response()->json(['message' => 'Barber deleted successfully']);
    }

    public function getAllBookings()
    {
        $bookings = Booking::with(['user', 'service', 'barber'])->get();
        return response()->json($bookings);
    }

    public function updateBookingStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,in_progress,completed,cancelled',
        ]);

        $booking = Booking::where('booking_id', $id)->firstOrFail();
        $booking->update(['status' => $request->status]);

        return response()->json($booking);
    }

    public function checkInBooking($id)
    {
        $booking = Booking::where('booking_id', $id)->firstOrFail();
        $booking->update(['status' => 'confirmed']);

        // Broadcast the status update
        event(new BookingStatusUpdated($booking));

        return response()->json($booking);
    }

    public function createBooking(Request $request)
    {
        
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'booking_date' => 'required|date',
            'booking_time' => 'required|date_format:H:i',
            'barber_id' => 'required|exists:barbers,id',
            'service_id' => 'required|exists:services,id',
            'payment_method' => 'required|in:Online,Walk-in',
            'status' => 'required|in:pending,confirmed,completed,cancelled',
        ]);
        
        $bookingDate = $request->booking_date;
        $bookingTime = $request->booking_time;
        
        // Gabungkan tanggal dan waktu untuk booking_datetime
        $bookingDateTime = $bookingDate . ' ' . $bookingTime;
        
        // Ambil layanan untuk mendapatkan durasi dan harga
        $service = Service::find($request->service_id);
        $duration = $service && isset($service->duration) ? $service->duration : 45; // Default 45 menit jika tidak ditemukan
        $price = $service && isset($service->price) ? $service->price : 0; // Default harga jika tidak ditemukan
        
        // Cek apakah slot waktu tersedia
        if (!$this->isTimeSlotAvailable($request->barber_id, $bookingDate, $bookingTime, $duration)) {
            return response()->json(['error' => 'Slot waktu tidak tersedia'], 422);
        }
        
        // Ambil user terkait (untuk walk-in bisa buat user sementara atau cari berdasarkan nama)
        $user = User::where('name', $request->customer_name)->first();
        if (!$user) {
            // Jika user tidak ditemukan, buat user sementara dengan role user
            $user = User::create([
                'name' => $request->customer_name,
                'email' => strtolower(str_replace(' ', '', $request->customer_name)) . '@walkin.cukurmen',
                'password' => bcrypt('defaultpassword'),
                'phone' => $request->phone, // Gunakan nomor HP dari request
                'role' => 'user',
            ]);
        } else {
            // Jika user ditemukan, update nomor HP-nya
            $user->update(['phone' => $request->phone]);
        }
        
        // Generate booking_id unik
        do {
            $bookingId = 'BK' . date('Ymd') . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
        } while (Booking::where('booking_id', $bookingId)->exists());
        
        $booking = Booking::create([
            'booking_id' => $bookingId, // Generate booking_id unik
            'user_id' => $user->id,
            'service_id' => $request->service_id,
            'barber_id' => $request->barber_id,
            'booking_date' => $bookingDate,
            'booking_time' => $bookingTime,
            'booking_datetime' => $bookingDateTime,
            'total_price' => $price, // Harga dari layanan
            'duration' => $duration, // Durasi dari layanan
            'payment_method' => $request->payment_method === 'Walk-in' ? 'Bayar di Tempat' : $request->payment_method,
            'status' => $request->status,
            'payment_status' => $request->payment_method === 'Online' ? 'paid' : 'unpaid', // Default payment status
            'phone' => $request->phone, // Simpan nomor HP di booking
        ]);
        
        return response()->json($booking);
    }

    /**
     * Mendapatkan slot waktu yang tersedia untuk barber tertentu pada tanggal tertentu
     */
    public function getAvailableTimeSlots(Request $request)
    {
        
        $request->validate([
            'date' => 'required|date',
            'barber_id' => 'required|integer|exists:barbers,id',
            'service_id' => 'required|integer|exists:services,id',
        ]);

        $date = $request->date;
        $barberId = $request->barber_id;
        $serviceId = $request->service_id;

        // Ambil durasi dari service
        $service = Service::find($serviceId);
        $duration = $service ? $service->duration : 45; // Default durasi jika tidak ditemukan

        // Waktu operasional barbershop
        $openTime = '10:00';
        $closeTime = '21:45';

        // Konversi waktu operasional ke menit dari awal hari
        $openMinutes = $this->timeToMinutes($openTime);
        $closeMinutes = $this->timeToMinutes($closeTime);

        // Membuat slot waktu berdasarkan durasi layanan
        $slots = [];
        $currentMinutes = $openMinutes;

        while ($currentMinutes < $closeMinutes) {
            // Tambahkan slot ke daftar
            $slots[] = $this->minutesToTime($currentMinutes);
            $currentMinutes += $duration;
        }

        // Ambil booking yang sudah ada untuk barber dan tanggal tersebut
        $existingBookings = Booking::where('barber_id', $barberId)
            ->where('booking_date', $date)
            ->where('status', '!=', 'cancelled') // Tidak termasuk booking yang dibatalkan
            ->pluck('booking_time')
            ->toArray();

        // Hapus slot yang sudah terisi
        $availableSlots = [];
        foreach ($slots as $slot) {
            $isSlotAvailable = true;
            
            // Periksa apakah slot ini atau slot berdekatan sudah terisi
            foreach ($existingBookings as $existingTime) {
                // Konversi waktu booking ke menit
                $existingMinutes = $this->timeToMinutes($existingTime);
                $slotMinutes = $this->timeToMinutes($slot);
                
                // Jika slot beririsan dengan booking yang sudah ada
                if (($slotMinutes < $existingMinutes + $duration) && ($slotMinutes + $duration > $existingMinutes)) {
                    $isSlotAvailable = false;
                    break;
                }
            }
            
            // Jika tanggal adalah hari ini dan slot sudah lewat, maka tidak tersedia
            if ($date === date('Y-m-d')) {
                $currentTime = strtotime(date('H:i'));
                $slotTime = strtotime($slot);
                
                // Tambahkan beberapa menit sebagai margin
                if ($slotTime < ($currentTime + 15 * 60)) { // 15 menit margin
                    $isSlotAvailable = false;
                }
            }

            if ($isSlotAvailable) {
                $availableSlots[] = $slot;
            }
        }

        return response()->json([
            'availableSlots' => $availableSlots
        ]);
    }

    /**
     * Cek apakah slot waktu tersedia untuk barber tertentu
     *
     * @param int $barberId
     * @param string $date
     * @param string $time
     * @param int $duration
     * @return bool
     */
    private function isTimeSlotAvailable($barberId, $date, $time, $duration)
    {
        // Konversi waktu booking ke menit dari awal hari
        $timeParts = explode(':', $time);
        $bookingStartMinutes = intval($timeParts[0]) * 60 + intval($timeParts[1]);
        $bookingEndMinutes = $bookingStartMinutes + $duration;

        // Ambil semua booking yang aktif (tidak dibatalkan) untuk barber dan tanggal tersebut
        $existingBookings = Booking::where('barber_id', $barberId)
            ->where('booking_date', $date)
            ->where('status', '!=', 'cancelled')
            ->get();

        // Cek apakah ada bentrok waktu
        foreach ($existingBookings as $existingBooking) {
            $existingTimeParts = explode(':', $existingBooking->booking_time);
            $existingStartMinutes = intval($existingTimeParts[0]) * 60 + intval($existingTimeParts[1]);
            $existingEndMinutes = $existingStartMinutes + ($existingBooking->duration ?? 45);

            // Cek apakah waktu booking baru beririsan dengan booking yang sudah ada
            if (($bookingStartMinutes < $existingEndMinutes) && ($bookingEndMinutes > $existingStartMinutes)) {
                return false; // Ada bentrok
            }
        }

        return true; // Slot tersedia
    }
    
    /**
     * Konversi waktu ke menit dari awal hari
     * 
     * @param string $time Format HH:MM
     * @return int Jumlah menit
     */
    private function timeToMinutes($time)
    {
        $parts = explode(':', $time);
        return intval($parts[0]) * 60 + intval($parts[1]);
    }

    /**
     * Konversi menit ke format waktu
     * 
     * @param int $minutes Jumlah menit
     * @return string Waktu dalam format HH:MM
     */
    private function minutesToTime($minutes)
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%02d:%02d', $hours, $mins);
    }

    public function completeBooking($id)
    {
        $booking = Booking::where('booking_id', $id)->firstOrFail();
        $booking->update(['status' => 'completed']);

        // Broadcast the status update
        event(new BookingStatusUpdated($booking));

        return response()->json($booking);
    }
    
    public function showCheckIn()
    {
        return view('admin.dashboard.tab-admin.checkin');
    }

    public function showWalkIn()
    {
        return view('admin.dashboard.tab-admin.walkin');
    }

    public function showSchedule()
    {
        return view('admin.dashboard.tab-admin.schedule');
    }

    public function showReport()
    {
        return view('admin.dashboard.tab-admin.report');
    }

    public function exportReportToExcel(Request $request)
    {
        // Get the report date and type from the request, default to today and daily
        $date = $request->query('date', date('Y-m-d'));
        $reportType = $request->query('report_type', 'Harian'); // 'Harian' or 'Bulanan' from the UI

        // For now, we'll focus on daily report based on the selected date
        // If needed, we can expand this to support monthly reports

        // Get the same data used in the dashboard
        $summary = [
            'total' => Booking::where('booking_date', $date)->count(),
            'online' => Booking::where('booking_date', $date)
                               ->where('payment_method', '!=', 'Walk-in')
                               ->count(),
            'walkIn' => Booking::where('booking_date', $date)
                                ->where('payment_method', 'Walk-in')
                                ->count(),
            'menunggu' => Booking::where('booking_date', $date)
                                  ->where('status', 'pending')
                                  ->count(),
            'checkIn' => Booking::where('booking_date', $date)
                                 ->where('status', 'confirmed')
                                 ->count(),
            'cukur' => Booking::where('booking_date', $date)
                               ->where('status', 'in_progress')
                               ->count(),
            'selesai' => Booking::where('booking_date', $date)
                                 ->where('status', 'completed')
                                 ->count(),
        ];

        // Get completed bookings to calculate revenue
        $completedBookings = Booking::where('booking_date', $date)
                                    ->where('status', 'completed')
                                    ->with(['barber', 'service'])
                                    ->get();

        $totalRevenue = $completedBookings->sum('total_price');

        // Calculate revenue by barber
        $revenueByBarber = [];
        foreach ($completedBookings as $booking) {
            $barberName = $booking->barber ? $booking->barber->name : 'Unknown Barber';
            if (!isset($revenueByBarber[$barberName])) {
                $revenueByBarber[$barberName] = [
                    'bookingCount' => 0,
                    'revenue' => 0
                ];
            }
            $revenueByBarber[$barberName]['bookingCount']++;
            $revenueByBarber[$barberName]['revenue'] += $booking->total_price;
        }

        // Calculate revenue by service
        $revenueByService = [];
        foreach ($completedBookings as $booking) {
            $serviceName = $booking->service ? $booking->service->name : 'Unknown Service';
            if (!isset($revenueByService[$serviceName])) {
                $revenueByService[$serviceName] = [
                    'bookingCount' => 0,
                    'revenue' => 0
                ];
            }
            $revenueByService[$serviceName]['bookingCount']++;
            $revenueByService[$serviceName]['revenue'] += $booking->total_price;
        }

        // Generate CSV content
        $csvContent = "Laporan Booking - Tanggal: " . $date . "\n";
        $csvContent .= "\n";
        $csvContent .= "Statistik Umum:\n";
        $csvContent .= "Total Booking," . $summary['total'] . "\n";
        $csvContent .= "Online," . $summary['online'] . "\n";
        $csvContent .= "Walk-in," . $summary['walkIn'] . "\n";
        $csvContent .= "Menunggu," . $summary['menunggu'] . "\n";
        $csvContent .= "Check-in," . $summary['checkIn'] . "\n";
        $csvContent .= "Cukur," . $summary['cukur'] . "\n";
        $csvContent .= "Selesai," . $summary['selesai'] . "\n";
        $csvContent .= "Total Revenue,\"Rp " . number_format($totalRevenue, 0, ',', '.') . "\"\n";
        $csvContent .= "\n";
        $csvContent .= "Performa Barber:\n";
        $csvContent .= "Barber,Booking Count,Revenue\n";
        foreach ($revenueByBarber as $barberName => $data) {
            $csvContent .= "\"" . $barberName . "\"," . $data['bookingCount'] . ",\"Rp " . number_format($data['revenue'], 0, ',', '.') . "\"\n";
        }
        $csvContent .= "\n";
        $csvContent .= "Performa Layanan:\n";
        $csvContent .= "Layanan,Booking Count,Revenue\n";
        foreach ($revenueByService as $serviceName => $data) {
            $csvContent .= "\"" . $serviceName . "\"," . $data['bookingCount'] . ",\"Rp " . number_format($data['revenue'], 0, ',', '.') . "\"\n";
        }

        // Set headers for CSV download
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="laporan_booking_' . $date . '.csv"',
            'Pragma' => 'no-cache',
        ];

        return response($csvContent, 200, $headers);
    }

    public function showProducts()
    {
        
        try {
            // Get all services and products to pass to the view
            $services = Service::all();
            $products = Product::all();
            
            return view('admin.dashboard.products', compact('services', 'products'));
        } catch (\Exception $e) {
            \Log::error('Error loading products page: ' . $e->getMessage());
            // Jika terjadi error, kembalikan ke dashboard dengan pesan error
            return redirect()->route('admin.checkin')->with('error', 'Terjadi kesalahan saat memuat halaman produk.');
        }
    }

    public function showProfile()
    {
        
        try {
            // Get the authenticated user
            $user = auth()->user();
            
            // Pass profile data to the view
            $profileData = [
                'user' => $user,
                'namaBarbershop' => 'BARBER GO BARBERSHOP',
                'jamOperasional' => '10:00 - 21:45 (tanpa istirahat)',
                'alamat' => 'Jl. Profesor DR. HR Boenyamin No.152, Sumampir Wetan, Pabuaran, Kec. Purwokerto Utara, Kabupaten Banyumas, Jawa Tengah 53124',
                'namaAdmin' => $user->name,
                'emailAdmin' => $user->email
            ];
            
            return view('admin.dashboard.profile', $profileData);
        } catch (\Exception $e) {
            \Log::error('Error loading profile page: ' . $e->getMessage());
            // Jika terjadi error, kembalikan ke dashboard dengan pesan error
            return redirect()->route('admin.checkin')->with('error', 'Terjadi kesalahan saat memuat halaman profil.');
        }
    }

    public function getProfileData()
    {
        
        $user = auth()->user();
        
        return response()->json([
            'namaBarbershop' => 'BARBER GO BARBERSHOP',
            'jamOperasional' => '10:00 - 21:45 (tanpa istirahat)',
            'alamat' => 'Jl. Profesor DR. HR Boenyamin No.152, Sumampir Wetan, Pabuaran, Kec. Purwokerto Utara, Kabupaten Banyumas, Jawa Tengah 53124',
            'namaAdmin' => $user->name,
            'emailAdmin' => $user->email,
            'user' => $user
        ]);
    }

    public function getAllProductsAndServices()
    {
        
        $products = Product::all();
        $services = Service::all();
        
        // Combine both with a type indicator
        $allItems = [];
        
        foreach ($services as $service) {
            $allItems[] = [
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description,
                'price' => $service->price,
                'type' => 'service',
                'category' => 'layanancukur'
            ];
        }
        
        foreach ($products as $product) {
            $allItems[] = [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'status' => $product->status,
                'type' => 'product',
                'category' => 'ritel'
            ];
        }
        
        return response()->json($allItems);
    }

    // Banner Management
    public function showBanners()
    {
        $banners = \App\Models\Banner::all();
        return view('admin.dashboard.banners', compact('banners'));
    }

    public function getAllBanners()
    {
        $banners = \App\Models\Banner::all();
        return response()->json($banners);
    }

    public function getBanner($id)
    {
        $banner = \App\Models\Banner::findOrFail($id);
        return response()->json($banner);
    }

    public function createBanner(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $data = $request->only(['title', 'description', 'is_active']);
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->handleImageUpload($request->file('image'), 'banners');
        }

        $banner = \App\Models\Banner::create($data);

        return response()->json($banner, 201);
    }

    public function updateBanner(Request $request, $id)
    {
        $banner = \App\Models\Banner::findOrFail($id);

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $data = $request->only(['title', 'description', 'is_active']);
        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->handleImageUpload($request->file('image'), 'banners');
        }

        $banner->update($data);

        return response()->json($banner);
    }

    public function deleteBanner($id)
    {
        $banner = \App\Models\Banner::findOrFail($id);
        $banner->delete();

        return response()->json(['message' => 'Banner deleted successfully']);
    }

    /**
     * Helper to handle image uploads
     */
    private function handleImageUpload($file, $folder)
    {
        try {
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs($folder, $filename, 'public');
            
            \Log::info("Image uploaded successfully: {$path} in folder {$folder}");
            
            return 'storage/' . $path;
        } catch (\Exception $e) {
            \Log::error("Error uploading image to {$folder}: " . $e->getMessage());
            throw $e;
        }
    }
}
