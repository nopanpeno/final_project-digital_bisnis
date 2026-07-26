<?php

use Illuminate\Support\Facades\Route;

// Frontend Controllers
use App\Http\Controllers\HomeController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\OrganizerRegisterController;

// Admin Controllers
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController as EventAdminController;
use App\Http\Controllers\Admin\TransactionController;

// Multi-Tenant Controllers
use App\Http\Controllers\OrganizerDashboardController;
use App\Http\Controllers\SuperadminOrganizerController;

/*
|--------------------------------------------------------------------------
| Frontend Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/events/{event}', [EventController::class, 'show'])
    ->name('events.show');

Route::get('/checkout', [EventController::class, 'checkout'])
    ->name('checkout.index');

Route::get('/checkout/{event}', [CheckoutController::class, 'create'])
    ->name('checkout.create');

Route::post('/checkout/{event}', [CheckoutController::class, 'store'])
    ->name('checkout.store');

Route::get('/payment/{order_id}', [CheckoutController::class, 'payment'])
    ->name('checkout.payment');

Route::get('/success/{order_id}', [CheckoutController::class, 'success'])
    ->name('checkout.success');

Route::get('/my-ticket', [EventController::class, 'ticket'])
    ->name('ticket');

Route::post('/midtrans/callback', [\App\Http\Controllers\MidtransWebhookController::class, 'handle'])->name('midtrans.callback');
/*
|--------------------------------------------------------------------------
| Authentication Redirect
|--------------------------------------------------------------------------
*/

Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Guest Admin
        Route::get('/login', [AuthController::class, 'showLogin'])
            ->name('login');

        Route::post('/login', [AuthController::class, 'login'])
            ->name('login.post');

        Route::post('/logout', [AuthController::class, 'logout'])
            ->name('logout');

        // Protected Admin
        Route::middleware(['auth', 'admin'])->group(function () {

            Route::get('/dashboard', [DashboardController::class, 'index'])
                ->name('dashboard');

            Route::resource('events', EventAdminController::class);

            Route::resource('categories', CategoryController::class)
                ->except(['show']);

            Route::get('/transactions', [TransactionController::class, 'index'])
                ->name('transactions.index');
        });
    });

/*
|--------------------------------------------------------------------------
| Organizer Registration (Public - HIMA daftar akun sendiri)
|--------------------------------------------------------------------------
*/

Route::prefix('organizer')
    ->name('organizer.')
    ->group(function () {

        Route::get('/register', [OrganizerRegisterController::class, 'showForm'])
            ->name('register');

        Route::post('/register', [OrganizerRegisterController::class, 'store'])
            ->name('register.store');
    });

/*
|--------------------------------------------------------------------------
| Organizer Routes (Multi-Tenant)
|--------------------------------------------------------------------------
*/

Route::prefix('organizer')
    ->name('organizer.')
    ->middleware(['auth', 'organizer'])
    ->group(function () {

        Route::get('/dashboard', [OrganizerDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/analytics', [OrganizerDashboardController::class, 'analytics'])
            ->name('analytics');
    });

/*
|--------------------------------------------------------------------------
| Superadmin Routes (Multi-Tenant)
|--------------------------------------------------------------------------
*/

Route::prefix('superadmin')
    ->name('superadmin.')
    ->middleware(['auth', 'superadmin'])
    ->group(function () {

        Route::get('/organizers', [SuperadminOrganizerController::class, 'index'])
            ->name('organizers.index');

        Route::patch('/organizers/{organizer}/approve', [SuperadminOrganizerController::class, 'approve'])
            ->name('organizers.approve');

        Route::patch('/organizers/{organizer}/suspend', [SuperadminOrganizerController::class, 'suspend'])
            ->name('organizers.suspend');
    });

/*
|--------------------------------------------------------------------------
| Partner Routes
|--------------------------------------------------------------------------
*/

Route::resource('partners', PartnerController::class)
    ->except(['show']);