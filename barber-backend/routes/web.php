<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingStatusController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Public routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', function () {
    if (Auth::check() && Auth::user()->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('login');
});

// Admin routes (protected) - only authenticated admin users can access
Route::middleware(['isAdmin'])->prefix('admin')->name('admin.')->group(function () {
    // Main dashboard and sub-sections
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/data', [AdminController::class, 'getDashboardData'])->name('dashboard.data');
    
    // Admin sub-tabs
    Route::get('/checkin', [AdminController::class, 'showCheckIn'])->name('checkin');
    Route::get('/walkin', [AdminController::class, 'showWalkIn'])->name('walkin');
    Route::get('/schedule', [AdminController::class, 'showSchedule'])->name('schedule');
    Route::get('/report', [AdminController::class, 'showReport'])->name('report');
    Route::get('/reports/export/excel', [AdminController::class, 'exportReportToExcel'])->name('reports.export.excel');
    
    // Products and services
    Route::get('/products', [AdminController::class, 'showProducts'])->name('products');
    Route::get('/profile', [AdminController::class, 'showProfile'])->name('profile');
    
    // User management
    Route::get('/users', [AdminController::class, 'getAllUsers'])->name('users');
    Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
    
    // Product management
    Route::get('/products/{id}', [AdminController::class, 'getProduct'])->name('products.show');
    Route::post('/products', [AdminController::class, 'createProduct'])->name('products.create');
    Route::put('/products/{id}', [AdminController::class, 'updateProduct'])->name('products.update');
    Route::delete('/products/{id}', [AdminController::class, 'deleteProduct'])->name('products.delete');
    
    // Service management
    Route::get('/services', [AdminController::class, 'getAllServices'])->name('services');
    Route::get('/services/{id}', [AdminController::class, 'getService'])->name('services.show');
    Route::post('/services', [AdminController::class, 'createService'])->name('services.create');
    Route::put('/services/{id}', [AdminController::class, 'updateService'])->name('services.update');
    Route::delete('/services/{id}', [AdminController::class, 'deleteService'])->name('services.delete');
    
    // Barber management
    Route::get('/barbers-page', [AdminController::class, 'showBarbers'])->name('barbers');
    Route::get('/barbers', [AdminController::class, 'getAllBarbers'])->name('barbers.index');
    Route::post('/barbers', [AdminController::class, 'createBarber'])->name('barbers.create');
    Route::put('/barbers/{id}', [AdminController::class, 'updateBarber'])->name('barbers.update');
    Route::delete('/barbers/{id}', [AdminController::class, 'deleteBarber'])->name('barbers.delete');
    
    // Banner management
    Route::get('/banners-page', [AdminController::class, 'showBanners'])->name('banners');
    Route::get('/banners', [AdminController::class, 'getAllBanners'])->name('banners.index');
    Route::get('/banners/{id}', [AdminController::class, 'getBanner'])->name('banners.show');
    Route::post('/banners', [AdminController::class, 'createBanner'])->name('banners.create');
    Route::post('/banners/{id}', [AdminController::class, 'updateBanner'])->name('banners.update'); // Using POST for file upload with _method
    Route::delete('/banners/{id}', [AdminController::class, 'deleteBanner'])->name('banners.delete');
    
    // Profile and products data API
    Route::get('/profile/data', [AdminController::class, 'getProfileData'])->name('profile.data');
    Route::get('/products/data', [AdminController::class, 'getAllProductsAndServices'])->name('products.data');
    
    // Booking management
    Route::get('/bookings', [AdminController::class, 'getAllBookings'])->name('bookings');
    Route::post('/bookings', [AdminController::class, 'createBooking'])->name('bookings.create');
    Route::get('/bookings/time-slots', [AdminController::class, 'getAvailableTimeSlots'])->name('bookings.time-slots');
    Route::put('/bookings/{id}/status', [AdminController::class, 'updateBookingStatus'])->name('bookings.update.status');
    Route::post('/bookings/{id}/checkin', [AdminController::class, 'checkInBooking'])->name('bookings.checkin');
    Route::post('/bookings/{id}/complete', [AdminController::class, 'completeBooking'])->name('bookings.complete');
    // Additional booking status routes
    Route::post('/bookings/{id}/cukur', [BookingStatusController::class, 'startCukur'])->name('bookings.cukur');
    Route::post('/bookings/{id}/cancel', [BookingStatusController::class, 'cancel'])->name('bookings.cancel');
});

Route::redirect('/admin/dashboard', '/admin/checkin')->name('admin.dashboard.view');

