<?php

use App\Models\Booking;
use App\Models\User;
use App\Models\Banner;
use App\Models\Service;
use App\Models\Product;
use App\Models\Barber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

function profileImageUrl(Request $request, ?string $image, ?int $version = null): ?string
{
    if (!$image) {
        return null;
    }

    $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');
    $url = $baseUrl . '/storage/profiles/' . ltrim($image, '/');

    return $version ? $url . '?v=' . $version : $url;
}

function serviceImageUrl(Request $request, ?string $image): ?string
{
    if (!$image) {
        return null;
    }

    if (preg_match('/^https?:\/\//i', $image)) {
        return $image;
    }

    $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');
    $filename = ltrim($image, '/');

    // Prioritas file dari disk public Laravel (storage/app/public/services)
    if (Storage::disk('public')->exists('services/' . $filename)) {
        return $baseUrl . '/storage/services/' . $filename;
    }

    // Fallback jika file disimpan manual di public/services
    if (file_exists(public_path('services/' . $filename))) {
        return $baseUrl . '/services/' . $filename;
    }

    // Default tetap ke path storage agar konsisten dengan proses upload Laravel
    return $baseUrl . '/storage/services/' . $filename;
}

function productImageUrl(Request $request, ?string $image): ?string
{
    if (!$image) {
        return null;
    }

    if (preg_match('/^https?:\/\//i', $image)) {
        return $image;
    }

    $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');
    $filename = ltrim($image, '/');

    if (Storage::disk('public')->exists('products/' . $filename)) {
        return $baseUrl . '/storage/products/' . $filename;
    }

    if (file_exists(public_path('products/' . $filename))) {
        return $baseUrl . '/products/' . $filename;
    }

    return $baseUrl . '/storage/products/' . $filename;
}

function bannerImageUrl(Request $request, ?string $image): ?string
{
    if (!$image) {
        return null;
    }

    if (preg_match('/^https?:\/\//i', $image)) {
        return $image;
    }

    $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');
    $filename = ltrim($image, '/');

    if (Storage::disk('public')->exists('banners/' . $filename)) {
        return $baseUrl . '/storage/banners/' . $filename;
    }

    if (file_exists(public_path('banners/' . $filename))) {
        return $baseUrl . '/banners/' . $filename;
    }

    return $baseUrl . '/storage/banners/' . $filename;
}
/*
|--------------------------------------------------------------------------
| API Routes - Barber Go (FULL FIXED)
|--------------------------------------------------------------------------
*/


Route::post('/update-profile', function (Request $request) {
    try {
        $user = \App\Models\User::find($request->user_id);
        if (!$user) return response()->json(['status' => 'error', 'message' => 'User tidak ditemukan'], 404);

        // Update data teks
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;

        // Logika Ganti Foto Profil
        if ($request->hasFile('image')) {
            // Hapus foto lama jika ada
            if ($user->image) {
                Storage::disk('public')->delete('profiles/' . $user->image);
            }

            // Simpan foto baru ke storage/app/public/profiles
            $file = $request->file('image');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('profiles', $filename, 'public');
            
            $user->image = $filename; // Simpan nama file saja di database
        }

        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Profil berhasil diperbarui',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'image' => $user->image,
                'image_url' => profileImageUrl($request, $user->image, optional($user->updated_at)->timestamp),
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
    }
});

// 1. API BERANDA (Banner, Layanan, Produk, Status Toko, & Antrean Toko)
Route::get('/home-data', function (Request $request) {
    $settings = Schema::hasTable('barbershop_settings')
        ? DB::table('barbershop_settings')->first()
        : null;
    $totalQueue = Schema::hasTable('bookings')
        ? Booking::where('status', 'waiting')->whereDate('booking_date', now())->count()
        : 0;

    // Mapping services untuk menambahkan full URL pada image_url
    $services = Schema::hasTable('services')
        ? Service::all()->map(function ($service) use ($request) {
            $service->price = (int) ($service->price ?? 0);
            $service->image_url = serviceImageUrl($request, $service->image_url);
            return $service;
        })
        : collect();

    $products = collect();
    if (Schema::hasTable('products')) {
        $productQuery = Product::query();
        if (Schema::hasColumn('products', 'is_available')) {
            $productQuery->where('is_available', true);
        }
        $products = $productQuery->orderByDesc('id')->get()->map(function ($product) use ($request) {
            $product->price = (int) ($product->price ?? 0);
            $product->image_url = productImageUrl($request, $product->image_url);
            return $product;
        });
    }

    $banners = collect();
    if (Schema::hasTable('banners')) {
        $bannerQuery = Banner::query();
        if (Schema::hasColumn('banners', 'is_active')) {
            $bannerQuery->where('is_active', true);
        }
        $banners = $bannerQuery->get()->map(function ($banner) use ($request) {
            if (isset($banner->image_url)) {
                $banner->image_url = bannerImageUrl($request, $banner->image_url);
            }
            return $banner;
        });
    }

    return response()->json([
        'status' => 'success',
        'barber_status' => [
            'is_open' => $settings ? (bool)$settings->is_open : true, 
            'total_queue' => $totalQueue,
            'shop_name' => $settings->shop_name ?? 'BARBER GO Barbershop',
            'address' => $settings->address ?? 'Jl. Merdeka No. 123, Jakarta Pusat',
        ],
        'banners' => $banners,
        'services' => $services,
        'products' => $products,
    ]);
});

// 2. API DAFTAR BARBER (Untuk halaman Pilih Barber)
Route::get('/barbers', function () {
    return response()->json([
        'status' => 'success',
        'data' => DB::table('barbers')->get()
    ]);
});

// 3. API CEK JAM TERISI (Kunci Jam di Flutter berdasarkan Tanggal & Barber)
Route::get('/occupied-slots', function (Request $request) {
    $date = $request->query('date');
    $barberQuery = $request->query('barber'); // Bisa ID atau Nama

    $query = Booking::where('booking_date', $date)
        ->where('status', '!=', 'cancelled');

    if (is_numeric($barberQuery)) {
        $query->where('barber_id', $barberQuery);
    } else {
        $barber = \App\Models\Barber::where('name', 'like', '%' . $barberQuery . '%')->first();
        if ($barber) {
            $query->where('barber_id', $barber->id);
        } else {
            // Jika barber tidak ditemukan, asumsikan tidak ada slot terisi untuk "nama" tersebut
            return response()->json([]);
        }
    }

    $slots = $query->pluck('booking_time'); 

    return response()->json($slots);
});

// 4. API SIMPAN BOOKING BARU
Route::post('/bookings', function (Request $request) {
    try {
        $data = $request->all();

        // Normalisasi nama field agar kompatibel dengan berbagai payload mobile
        $serviceName = $data['service_name']
            ?? $data['service']
            ?? $data['selectedService']
            ?? null;
        $barberName = $data['barber_name']
            ?? $data['barber']
            ?? null;
        
        $rawTotalPrice = $data['total_price'] ?? $data['price'] ?? null;
        if ($rawTotalPrice !== null) {
            $data['total_price'] = (int) preg_replace('/[^0-9]/', '', (string) $rawTotalPrice);
        }

        // Cari service_id jika belum ada
        if (!isset($data['service_id']) && $serviceName) {
            $service = Service::where('name', 'like', '%' . $serviceName . '%')->first();
            if ($service) {
                $data['service_id'] = $service->id;
                if (!isset($data['total_price'])) $data['total_price'] = $service->price;
                if (!isset($data['duration'])) $data['duration'] = $service->duration ?? 45;
            }
        }

        // Cari barber_id jika belum ada
        if (!isset($data['barber_id']) && $barberName) {
            $barber = \App\Models\Barber::where('name', 'like', '%' . $barberName . '%')->first();
            if ($barber) {
                $data['barber_id'] = $barber->id;
            }
        }

        // Validasi User ID wajib ada
        if (!isset($data['user_id']) || empty($data['user_id'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sesi Anda tidak valid. Silakan login ulang.'
            ], 401);
        }

        // Generate booking_id unik (Format: BK + Ymd + Random)
        if (!isset($data['booking_id'])) {
            do {
                $bookingId = 'BK' . date('Ymd') . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
            } while (Booking::where('booking_id', $bookingId)->exists());
            $data['booking_id'] = $bookingId;
        }

        // Map status 'waiting' to 'pending' (enum compatibility)
        if (isset($data['status']) && $data['status'] === 'waiting') {
            $data['status'] = 'pending';
        }
        if (!isset($data['status'])) $data['status'] = 'pending';
        
        // Ensure payment_method and payment_status have defaults
        if (!isset($data['payment_method'])) $data['payment_method'] = 'Online';
        if (!isset($data['payment_status'])) $data['payment_status'] = 'unpaid';

        $booking = Booking::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Booking berhasil disimpan',
            'data' => $booking
        ], 201);
    } catch (\Exception $e) {
        \Log::error('API Booking Error: ' . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => 'Gagal menyimpan: ' . $e->getMessage()
        ], 400);
    }
});

// 5. API BOOKING AKTIF (Hanya pesanan milik user yang sedang login)
Route::get('/active-booking', function (Request $request) {
    $userId = $request->query('user_id');

    if (!Schema::hasColumn('bookings', 'user_id')) {
        return response()->json([
            'status' => 'error',
            'message' => 'Kolom user_id pada tabel bookings belum ada. Jalankan migrasi terbaru.'
        ], 500);
    }
    
    $active = Booking::where('user_id', $userId)
        ->whereIn('status', ['waiting', 'processing'])
        ->orderBy('created_at', 'desc')
        ->first();

    return response()->json([
        'status' => 'success',
        'data' => $active
    ]);
});

// 6. API RIWAYAT BOOKING (Hanya riwayat milik user yang login)
Route::get('/booking-history', function (Request $request) {
    $userId = $request->query('user_id');

    if (!Schema::hasColumn('bookings', 'user_id')) {
        return response()->json([
            'status' => 'error',
            'message' => 'Kolom user_id pada tabel bookings belum ada. Jalankan migrasi terbaru.'
        ], 500);
    }
    
    $history = Booking::where('user_id', $userId)
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json([
        'status' => 'success',
        'data' => $history
    ]);
});

// 7. API BATALKAN BOOKING
Route::delete('/bookings/{id}', function ($id) {
    try {
        Booking::where('id', $id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Booking dibatalkan']);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => 'Gagal membatalkan'], 400);
    }
});

// 8. API REGISTER (Menyimpan Nama, Email, Telepon, & Password)
Route::post('/register', function (Request $request) {
    try {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:15',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi Berhasil!',
            'user' => $user
        ], 201);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
    }
});

// 9. API LOGIN (Mengembalikan ID, Nama, Email, & Telepon untuk profil)
Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        return response()->json(['status' => 'error', 'message' => 'Email atau Password salah.'], 401);
    }

    return response()->json([
        'status' => 'success',
        'message' => 'Login Berhasil',
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'image' => $user->image,
            'image_url' => profileImageUrl($request, $user->image, optional($user->updated_at)->timestamp),
        ],
    ], 200);
});

// 10. API NOTIFIKASI
Route::get('/notif-booking', function () {
    return response()->json([
        'id' => 1,
        'title' => 'Barber Go Notif',
        'body' => 'Nikmati potongan harga untuk Haircut Standard hari ini!'
    ]);
});
