<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

// ==========================================
// 1. FRONTEND & PUBLIC CONTROLLERS
// ==========================================
use App\Http\Controllers\HomeController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\OrganizerRegisterController;
use App\Http\Controllers\OrganizerProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\MyTicketController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\MidtransWebhookController;

// ==========================================
// 2. ADMIN CONTROLLERS
// ==========================================
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController as EventAdminController;
use App\Http\Controllers\Admin\TransactionController;

// ==========================================
// 3. MULTI-TENANT CONTROLLERS
// ==========================================
use App\Http\Controllers\OrganizerDashboardController;
use App\Http\Controllers\SuperadminOrganizerController;


/*
|--------------------------------------------------------------------------
| FRONTEND & PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

// Riwayat transaksi/tiket milik user yang login (dulu halaman mock, sekarang data asli)
Route::middleware('auth')->get('/my-ticket', [MyTicketController::class, 'index'])->name('ticket');

// Checkout Flow
Route::get('/checkout', [EventController::class, 'checkout'])->name('checkout.index');
Route::get('/checkout/{event}', [CheckoutController::class, 'create'])->name('checkout.create');
Route::post('/checkout/{event}', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/payment/{order_id}', [CheckoutController::class, 'payment'])->name('checkout.payment');
Route::get('/success/{order_id}', [CheckoutController::class, 'success'])->name('checkout.success');

// Midtrans Webhook (PENTING: Nonaktifkan CSRF untuk webhook agar tidak ditolak Laravel)
Route::post('/midtrans/callback', [MidtransWebhookController::class, 'handle'])
    ->name('midtrans.callback')
    ->withoutMiddleware([VerifyCsrfToken::class]);

// Reviews (Memerlukan Login)
Route::middleware('auth')->group(function () {
    Route::post('/events/{event}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
});


/*
|--------------------------------------------------------------------------
| AUTHENTICATION REDIRECT (Fallback)
|--------------------------------------------------------------------------
*/
Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');


/*
|--------------------------------------------------------------------------
| GOOGLE SSO (SOCIALITE)
|--------------------------------------------------------------------------
*/
Route::get('/auth/google', [SocialiteController::class, 'redirect'])->name('auth.google');

// Khusus user yang login SSO saat sedang proses checkout
Route::get('/auth/google/checkout/{event}', [SocialiteController::class, 'redirectFromCheckout'])->name('auth.google.checkout');

// Callback dari Google
Route::get('/auth/google/callback', [SocialiteController::class, 'callback'])->name('auth.google.callback');


/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    
    // Guest Routes (Belum Login)
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Protected Routes (Sudah Login & Role Admin)
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        Route::resource('events', EventAdminController::class);
        Route::resource('categories', CategoryController::class)->except(['show']);
        
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    });
});


/*
|--------------------------------------------------------------------------
| ORGANIZER ROUTES (Multi-Tenant)
|--------------------------------------------------------------------------
*/

// A. Public Registration (Siapa saja bisa daftar)
Route::prefix('organizer')->name('organizer.')->group(function () {
    Route::get('/register', [OrganizerRegisterController::class, 'showForm'])->name('register');
    Route::post('/register', [OrganizerRegisterController::class, 'store'])->name('register.store');
    
    // Profil Publik Organizer (untuk Fitur Rating & Review)
    Route::get('/profile/{slug}', [OrganizerProfileController::class, 'show'])->name('profile');
});

// B. Protected Dashboard (Hanya Organizer yang sudah login)
Route::prefix('organizer')->name('organizer.')->middleware(['auth', 'organizer'])->group(function () {
    Route::get('/dashboard', [OrganizerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/analytics', [OrganizerDashboardController::class, 'analytics'])->name('analytics');
});


/*
|--------------------------------------------------------------------------
| SUPERADMIN ROUTES (Multi-Tenant)
|--------------------------------------------------------------------------
*/
Route::prefix('superadmin')->name('superadmin.')->middleware(['auth', 'superadmin'])->group(function () {
    Route::get('/organizers', [SuperadminOrganizerController::class, 'index'])->name('organizers.index');
    Route::patch('/organizers/{organizer}/approve', [SuperadminOrganizerController::class, 'approve'])->name('organizers.approve');
    Route::patch('/organizers/{organizer}/suspend', [SuperadminOrganizerController::class, 'suspend'])->name('organizers.suspend');
});


/*
|--------------------------------------------------------------------------
| PARTNER ROUTES
|--------------------------------------------------------------------------
*/
Route::resource('partners', PartnerController::class)->except(['show']);