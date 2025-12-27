<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HouseController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\RentalHistoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Auth Routes
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// House Routes
Route::get('/houses', [HouseController::class, 'index'])->name('houses.index');
Route::get('/houses/{id}', [HouseController::class, 'show'])->name('houses.show');

// Terms of Service
Route::get('/terms', [\App\Http\Controllers\TermsController::class, 'index'])->name('terms.index');

// Privacy Policy
Route::get('/privacy', [\App\Http\Controllers\PrivacyController::class, 'index'])->name('privacy.index');

// Booking Routes
Route::get('/booking/{id}', [BookingController::class, 'show'])->name('booking.show');
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
Route::middleware('auth')->get('/booking/{id}/success', [BookingController::class, 'success'])->name('booking.success');

// Payment Routes
Route::middleware('auth')->group(function () {
    Route::get('/payment/{bookingId}', [\App\Http\Controllers\PaymentController::class, 'create'])->name('payment.create');
    Route::post('/payment/{bookingId}/confirm', [\App\Http\Controllers\PaymentController::class, 'confirm'])->name('payment.confirm');
    
    // Invoice Payment Routes
    Route::get('/payment/invoice/{invoiceId}', [\App\Http\Controllers\PaymentController::class, 'createInvoice'])->name('payment.invoice.create');
    Route::post('/payment/invoice/{invoiceId}/confirm', [\App\Http\Controllers\PaymentController::class, 'confirmInvoice'])->name('payment.invoice.confirm');
});

// Bank Routes (public API)
Route::get('/api/banks', [\App\Http\Controllers\BankController::class, 'index'])->name('api.banks');

// Payment Routes
// Payment create route (requires auth)
Route::middleware('auth')->group(function () {
    // Note: VNPay payment routes have been removed
});

// Rental History (requires auth)
Route::middleware('auth')->group(function () {
    Route::get('/history', [RentalHistoryController::class, 'index'])->name('history.index');
    Route::get('/my-rentals', [RentalHistoryController::class, 'index'])->name('my-rentals');
    Route::get('/my-rentals/{booking}', [RentalHistoryController::class, 'show'])->name('my-rentals.show');
    
    // Contract download
    Route::get('/contract/{booking}', [\App\Http\Controllers\ContractController::class, 'download'])->name('contract.download');
    Route::get('/contract/{booking}/preview', [\App\Http\Controllers\ContractController::class, 'preview'])->name('contract.preview');
    Route::get('/contract/{booking}/sign', [\App\Http\Controllers\ContractController::class, 'showSign'])->name('contract.sign.show');
    Route::post('/contract/{booking}/sign', [\App\Http\Controllers\ContractController::class, 'sign'])->name('contract.sign');
    
    // Invoices
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->name('invoices.show');
    
    // Reviews
    Route::post('/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])->name('reviews.store');
});

// Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/personal-info', [\App\Http\Controllers\ProfileController::class, 'updatePersonalInfo'])->name('profile.update-personal-info');
});

// Admin Routes
Route::middleware(['auth', 'role:manager'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');

    // User Management
    Route::prefix('admin/users')->name('admin.users.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('store');
        Route::get('/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('show');
        Route::post('/{user}/ban', [\App\Http\Controllers\Admin\UserController::class, 'ban'])->name('ban');
        Route::post('/{user}/unban', [\App\Http\Controllers\Admin\UserController::class, 'unban'])->name('unban');
    });

    // House Management
    Route::prefix('admin/houses')->name('admin.houses.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\HouseController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\HouseController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\HouseController::class, 'store'])->name('store');
        Route::get('/{house}', [\App\Http\Controllers\Admin\HouseController::class, 'show'])->name('show');
        Route::get('/{house}/edit', [\App\Http\Controllers\Admin\HouseController::class, 'edit'])->name('edit');
        Route::get('/{house}/manage', [\App\Http\Controllers\Admin\HouseController::class, 'manage'])->name('manage');
        Route::put('/{house}', [\App\Http\Controllers\Admin\HouseController::class, 'update'])->name('update');
        Route::delete('/{house}', [\App\Http\Controllers\Admin\HouseController::class, 'destroy'])->name('destroy');

        // Floor Management
        Route::post('/{house}/floors', [\App\Http\Controllers\Admin\HouseController::class, 'addFloor'])->name('floors.add');
        Route::delete('/{house}/floors', [\App\Http\Controllers\Admin\HouseController::class, 'removeFloor'])->name('floors.remove');

        // Room Management
        Route::post('/{house}/rooms', [\App\Http\Controllers\Admin\RoomController::class, 'store'])->name('rooms.store');
        Route::put('/{house}/rooms/{room}', [\App\Http\Controllers\Admin\RoomController::class, 'update'])->name('rooms.update');
        Route::delete('/{house}/rooms/{room}', [\App\Http\Controllers\Admin\RoomController::class, 'destroy'])->name('rooms.destroy');
        Route::get('/{house}/rooms/{room}/invoices', [\App\Http\Controllers\Admin\RoomController::class, 'getInvoices'])->name('rooms.invoices.index');
        Route::post('/{house}/rooms/{room}/invoices', [\App\Http\Controllers\Admin\RoomController::class, 'createInvoice'])->name('rooms.invoices.create');
    });

    // Settings
    Route::prefix('admin/settings')->name('admin.settings.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('index');
        Route::put('/', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('update');
        Route::post('/', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('update.post'); // For file uploads
        Route::post('/clear-cache', [\App\Http\Controllers\Admin\SettingsController::class, 'clearCache'])->name('clear-cache');
        
        // Social Links
        Route::post('/social-links', [\App\Http\Controllers\Admin\SettingsController::class, 'storeSocialLink'])->name('social-links.store');
        Route::put('/social-links/{id}', [\App\Http\Controllers\Admin\SettingsController::class, 'updateSocialLink'])->name('social-links.update');
        Route::delete('/social-links/{id}', [\App\Http\Controllers\Admin\SettingsController::class, 'destroySocialLink'])->name('social-links.destroy');
    });

    // God System
    Route::prefix('admin/god-system')->name('admin.god-system.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\GodSystemController::class, 'index'])->name('index');
        Route::post('/system-time', [\App\Http\Controllers\Admin\GodSystemController::class, 'updateSystemTime'])->name('system-time.update');
        Route::post('/system-time/reset', [\App\Http\Controllers\Admin\GodSystemController::class, 'resetSystemTime'])->name('system-time.reset');
        Route::post('/trigger-update-room', [\App\Http\Controllers\Admin\GodSystemController::class, 'triggerUpdateRoom'])->name('trigger-update-room');
        Route::get('/system-time/info', [\App\Http\Controllers\Admin\GodSystemController::class, 'getSystemTimeInfo'])->name('system-time.info');
    });

    // Company Information
    Route::prefix('admin/company-information')->name('admin.company-information.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\CompanyInformationController::class, 'index'])->name('index');
        Route::put('/', [\App\Http\Controllers\Admin\CompanyInformationController::class, 'update'])->name('update');
    });

    // Banner Management
    // Additional route for POST update (for file uploads) - must be before resource route
    Route::post('admin/banners/{banner}', [\App\Http\Controllers\Admin\BannerController::class, 'update'])->name('admin.banners.update.post');
    
    Route::resource('admin/banners', \App\Http\Controllers\Admin\BannerController::class)->names([
        'index' => 'admin.banners.index',
        'create' => 'admin.banners.create',
        'store' => 'admin.banners.store',
        'show' => 'admin.banners.show',
        'edit' => 'admin.banners.edit',
        'update' => 'admin.banners.update',
        'destroy' => 'admin.banners.destroy',
    ]);

    // Address Management
    Route::prefix('admin/addresses')->name('admin.addresses.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AddressController::class, 'index'])->name('index');
        Route::get('/by-parent', [\App\Http\Controllers\Admin\AddressController::class, 'getByParent'])->name('by-parent');
        Route::post('/', [\App\Http\Controllers\Admin\AddressController::class, 'store'])->name('store');
        Route::put('/{address}', [\App\Http\Controllers\Admin\AddressController::class, 'update'])->name('update');
        Route::delete('/{address}', [\App\Http\Controllers\Admin\AddressController::class, 'destroy'])->name('destroy');
    });

    // Review Management
    Route::prefix('admin/reviews')->name('admin.reviews.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('index');
        Route::post('/{review}/respond', [\App\Http\Controllers\Admin\ReviewController::class, 'respond'])->name('respond');
        Route::match(['put', 'post'], '/{review}/response', [\App\Http\Controllers\Admin\ReviewController::class, 'updateResponse'])->name('update-response');
        Route::delete('/{review}/response', [\App\Http\Controllers\Admin\ReviewController::class, 'deleteResponse'])->name('delete-response');
        Route::delete('/{review}', [\App\Http\Controllers\Admin\ReviewController::class, 'destroy'])->name('destroy');
    });
});
