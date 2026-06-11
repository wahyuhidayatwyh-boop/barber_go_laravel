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

function profileImageUrl(Request $request, ?string $image, ?int $version = null, $id = null): ?string
{
    if (!$image) return null;
    if (str_starts_with($image, 'data:image')) {
        $url = $id ? rtrim($request->getSchemeAndHttpHost(), '/') . "/api/image?type=profile&id={$id}" : $image;
        return ($id && $version) ? $url . "&v={$version}" : $url;
    }
    if (preg_match('/^https?:\/\//i', $image)) return $image;

    $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');
    $cleanPath = ltrim($image, '/');
    if (!str_starts_with($cleanPath, 'storage/')) {
        $cleanPath = 'storage/profiles/' . basename($cleanPath);
    }
    
    $url = $baseUrl . '/' . $cleanPath;
    return $version ? $url . '?v=' . $version : $url;
}

function serviceImageUrl(Request $request, ?string $image, $id = null): ?string
{
    if (!$image) return null;
    if (str_starts_with($image, 'data:image')) return $id ? rtrim($request->getSchemeAndHttpHost(), '/') . "/api/image?type=service&id={$id}" : $image;
    if (preg_match('/^https?:\/\//i', $image)) return $image;

    $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');
    $cleanPath = ltrim($image, '/');
    if (!str_starts_with($cleanPath, 'storage/')) {
        $cleanPath = 'storage/services/' . basename($cleanPath);
    }

    return $baseUrl . '/' . $cleanPath;
}

function productImageUrl(Request $request, ?string $image, $id = null): ?string
{
    if (!$image) return null;
    if (str_starts_with($image, 'data:image')) return $id ? rtrim($request->getSchemeAndHttpHost(), '/') . "/api/image?type=product&id={$id}" : $image;
    if (preg_match('/^https?:\/\//i', $image)) return $image;

    $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');
    $cleanPath = ltrim($image, '/');
    if (!str_starts_with($cleanPath, 'storage/')) {
        $cleanPath = 'storage/products/' . basename($cleanPath);
    }

    return $baseUrl . '/' . $cleanPath;
}

function bannerImageUrl(Request $request, ?string $image, $id = null): ?string
{
    if (!$image) return null;
    if (str_starts_with($image, 'data:image')) return $id ? rtrim($request->getSchemeAndHttpHost(), '/') . "/api/image?type=banner&id={$id}" : $image;
    if (preg_match('/^https?:\/\//i', $image)) return $image;

    $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');
    $cleanPath = ltrim($image, '/');
    if (!str_starts_with($cleanPath, 'storage/')) {
        $cleanPath = 'storage/banners/' . basename($cleanPath);
    }

    return $baseUrl . '/' . $cleanPath;
}
/*
|--------------------------------------------------------------------------
| API Routes - Barber Go (FULL FIXED)
|--------------------------------------------------------------------------
*/

// STREAM BASE64 GAMBAR LANGSUNG - MENGHINDARI LIMIT 4.5MB VERCEL PAYLOAD!
Route::get('/image', function (Request $request) {
    $type = $request->query('type');
    $id = $request->query('id');

    $modelClass = match($type) {
        'barber' => \App\Models\Barber::class,
        'product' => \App\Models\Product::class,
        'service' => \App\Models\Service::class,
        'banner' => \App\Models\Banner::class,
        'profile' => \App\Models\User::class,
        default => null
    };

    if (!$modelClass) return response('Invalid type', 404);

    $record = $modelClass::find($id);
    if (!$record) return response('Not found', 404);

    $imageField = $type === 'profile' ? $record->image : ($record->image_path ?? $record->image_url ?? $record->image);
    if (!$imageField || !str_starts_with($imageField, 'data:image')) {
        return response('No base64 image', 404);
    }

    if (preg_match('/^data:(image\/[a-zA-Z0-9\-\+]+);base64,(.+)$/', $imageField, $matches)) {
        $mime = $matches[1];
        $data = base64_decode($matches[2]);
        return response($data)
            ->header('Content-Type', $mime)
            ->header('Cache-Control', 'public, max-age=86400');
    }

    return response('Invalid format', 404);
});
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
            $file = $request->file('image');
            $mime = $file->getMimeType();
            $data = file_get_contents($file->getRealPath());
            $base64 = base64_encode($data);
            
            // Simpan base64 string langsung ke database
            $user->image = 'data:' . $mime . ';base64,' . $base64;
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
                'image_url' => profileImageUrl($request, $user->image, optional($user->updated_at)->timestamp, $user->id),
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
    }
});

// 1. API BERANDA (Banner, Layanan, Produk, Status Toko, & Antrean Toko)
Route::get('/home-data', function (Request $request) {
    try {
        $settings = DB::table('barbershop_settings')->first();
        $today = now()->timezone('Asia/Jakarta')->format('Y-m-d');
        $totalQueue = Booking::whereIn('status', ['pending', 'confirmed', 'in_progress'])
                             ->whereDate('booking_date', $today)
                             ->count();

        // Mapping services untuk menambahkan full URL pada image_url
        $services = Service::all()->map(function ($service) use ($request) {
            $service->price = (int) ($service->price ?? 0);
            $service->image_url = serviceImageUrl($request, $service->image_url, $service->id);
            return $service;
        });

        $products = Product::whereRaw('"is_available" = true')
            ->orderByDesc('id')->get()->map(function ($product) use ($request) {
                $product->price = (int) ($product->price ?? 0);
                $product->image_url = productImageUrl($request, $product->image_url, $product->id);
                return $product;
            });

        $banners = Banner::whereRaw('"is_active" = true')->get()->map(function ($banner) use ($request) {
            // Always regenerate image_url from image_path to avoid stale localhost URLs
            $imageSrc = $banner->image_path ?? $banner->image_url ?? null;
            $banner->image_url = bannerImageUrl($request, $imageSrc, $banner->id);
            return $banner;
        });

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
    } catch (\Exception $e) {
        \Log::error('API Home Data Error: ' . $e->getMessage());
        return response()->json(['status' => 'error', 'message' => 'Gagal memuat data: ' . $e->getMessage()], 500);
    }
});

// 2. API DAFTAR BARBER (Untuk halaman Pilih Barber)
Route::get('/barbers', function (Request $request) {
    try {
        $barbers = Barber::all()->map(function ($barber) use ($request) {
            $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');
            $imageSrc = $barber->image_path ?? $barber->image_url ?? null;
            if ($imageSrc && str_starts_with($imageSrc, 'data:image')) {
                $barber->image_url = $baseUrl . "/api/image?type=barber&id={$barber->id}";
            } elseif ($imageSrc && !preg_match('/^https?:\/\//i', $imageSrc)) {
                $cleanPath = ltrim($imageSrc, '/');
                if (!str_starts_with($cleanPath, 'storage/')) {
                    $cleanPath = 'storage/barbers/' . basename($cleanPath);
                }
                $barber->image_url = $baseUrl . '/' . $cleanPath;
            } else {
                $barber->image_url = $imageSrc;
            }
            return [
                'id' => $barber->id,
                'name' => $barber->name,
                'specialty' => $barber->specialty ?? 'Barber',
                'rating' => (float) ($barber->rating ?? 0),
                'image_url' => $barber->image_url,
                'status' => $barber->status ?? 'active',
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $barbers
        ]);
    } catch (\Exception $e) {
        \Log::error('API Barbers Error: ' . $e->getMessage());
        return response()->json(['status' => 'error', 'message' => 'Gagal memuat barbers: ' . $e->getMessage()], 500);
    }
});

// 3. API CEK JAM TERISI (Kunci Jam di Flutter berdasarkan Tanggal & Barber)
Route::get('/occupied-slots', function (Request $request) {
    try {
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
                return response()->json([]);
            }
        }

        $slots = $query->pluck('booking_time'); 

        return response()->json($slots);
    } catch (\Exception $e) {
        \Log::error('API Occupied Slots Error: ' . $e->getMessage());
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
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

        // Reload with relationships for complete response
        $booking->load(['service', 'barber']);

        $queuePosition = 1;
        $peopleAhead = 0;
        if ($booking->booking_date) {
            $dateStr = $booking->booking_date->format('Y-m-d');
            $activeBookingsToday = Booking::where('booking_date', $dateStr)
                ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
                ->orderBy('booking_time', 'asc')
                ->orderBy('created_at', 'asc')
                ->pluck('id')
                ->toArray();
            
            $pos = array_search($booking->id, $activeBookingsToday);
            if ($pos !== false) {
                $queuePosition = $pos + 1;
                $peopleAhead = $pos;
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Booking berhasil disimpan',
            'data' => [
                'id' => $booking->id,
                'booking_id' => $booking->booking_id,
                'user_id' => $booking->user_id,
                'service_id' => $booking->service_id,
                'barber_id' => $booking->barber_id,
                'service_name' => $booking->service->name ?? $serviceName ?? '-',
                'barber_name' => $booking->barber->name ?? $barberName ?? '-',
                'booking_date' => $booking->booking_date ? $booking->booking_date->format('Y-m-d') : null,
                'booking_time' => $booking->booking_time,
                'total_price' => $booking->total_price,
                'duration' => $booking->duration,
                'status' => $booking->status,
                'payment_method' => $booking->payment_method,
                'payment_status' => $booking->payment_status,
                'created_at' => $booking->created_at->format('Y-m-d H:i:s'),
                'queue_number' => $queuePosition,
                'people_ahead' => $peopleAhead,
            ]
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
    try {
        $userId = $request->query('user_id');

        // user_id column sudah ada di tabel bookings
        
        // Status aktif: pending (menunggu), confirmed (check-in), in_progress (sedang dicukur)
        $active = Booking::with(['service', 'barber'])
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'confirmed', 'in_progress', 'waiting', 'processing'])
            ->orderBy('created_at', 'desc')
            ->first();

        if ($active) {
            $queuePosition = 1;
            $peopleAhead = 0;
            if ($active->booking_date) {
                $dateStr = $active->booking_date->format('Y-m-d');
                $activeBookingsToday = Booking::where('booking_date', $dateStr)
                    ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
                    ->orderBy('booking_time', 'asc')
                    ->orderBy('created_at', 'asc')
                    ->pluck('id')
                    ->toArray();
                
                $pos = array_search($active->id, $activeBookingsToday);
                if ($pos !== false) {
                    $queuePosition = $pos + 1;
                    $peopleAhead = $pos;
                }
            }

            $active = [
                'id' => $active->id,
                'booking_id' => $active->booking_id,
                'user_id' => $active->user_id,
                'service_id' => $active->service_id,
                'barber_id' => $active->barber_id,
                'service_name' => $active->service->name ?? '-',
                'barber_name' => $active->barber->name ?? '-',
                'booking_date' => $active->booking_date ? $active->booking_date->format('Y-m-d') : null,
                'booking_time' => $active->booking_time,
                'total_price' => $active->total_price,
                'duration' => $active->duration,
                'status' => $active->status,
                'payment_method' => $active->payment_method,
                'payment_status' => $active->payment_status,
                'created_at' => $active->created_at->format('Y-m-d H:i:s'),
                'queue_number' => $queuePosition,
                'people_ahead' => $peopleAhead,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $active
        ]);
    } catch (\Exception $e) {
        \Log::error('API Active Booking Error: ' . $e->getMessage());
        return response()->json(['status' => 'error', 'message' => 'Gagal memuat booking aktif: ' . $e->getMessage()], 500);
    }
});

// 6. API RIWAYAT BOOKING (Hanya riwayat milik user yang login)
Route::get('/booking-history', function (Request $request) {
    try {
        $userId = $request->query('user_id');

        // user_id column sudah ada di tabel bookings
        
        $history = Booking::with(['service', 'barber'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($b) {
                return [
                    'id' => $b->id,
                    'booking_id' => $b->booking_id,
                    'user_id' => $b->user_id,
                    'service_id' => $b->service_id,
                    'barber_id' => $b->barber_id,
                    'service_name' => $b->service->name ?? '-',
                    'barber_name' => $b->barber->name ?? '-',
                    'booking_date' => $b->booking_date ? $b->booking_date->format('Y-m-d') : null,
                    'booking_time' => $b->booking_time,
                    'total_price' => $b->total_price,
                    'duration' => $b->duration,
                    'status' => $b->status,
                    'payment_method' => $b->payment_method,
                    'payment_status' => $b->payment_status,
                    'created_at' => $b->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $history
        ]);
    } catch (\Exception $e) {
        \Log::error('API Booking History Error: ' . $e->getMessage());
        return response()->json(['status' => 'error', 'message' => 'Gagal memuat riwayat: ' . $e->getMessage()], 500);
    }
});

// 7. API BATALKAN BOOKING
Route::delete('/bookings/{id}', function ($id) {
    try {
        $booking = Booking::findOrFail($id);
        $booking->update(['status' => 'cancelled']);
        return response()->json(['status' => 'success', 'message' => 'Booking dibatalkan']);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => 'Gagal membatalkan: ' . $e->getMessage()], 400);
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
            'image_url' => profileImageUrl($request, $user->image, optional($user->updated_at)->timestamp, $user->id),
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
