<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/home.php');
});
Route::get('/index.php', function () {
    return redirect('/home.php');
});
Route::get('/home.php', [PublicController::class, 'home']);
Route::get('/tentang.php', [PublicController::class, 'about']);
Route::get('/galeri.php', [PublicController::class, 'gallery']);
Route::get('/harga.php', [PublicController::class, 'prices']);
Route::get('/layanan.php', [PublicController::class, 'services']);
Route::get('/kontak.php', [PublicController::class, 'contact']);
Route::get('/sitemap.xml', [PublicController::class, 'sitemap']);
Route::post('/track-activity.php', [PublicController::class, 'trackActivity']);

Route::get('/admin', [AdminController::class, 'entry']);
Route::get('/admin/index.php', [AdminController::class, 'entry']);
Route::match(['get', 'post'], '/admin/login.php', [AdminController::class, 'login']);
Route::get('/admin/logout.php', [AdminController::class, 'logout'])->middleware('admin.auth');
Route::get('/admin/dashboard.php', [AdminController::class, 'dashboard'])->middleware('admin.auth');

Route::match(['get', 'post'], '/admin/gallery.php', [AdminController::class, 'gallery'])->middleware('admin.auth');
Route::match(['get', 'post'], '/admin/gallery-form.php', [AdminController::class, 'galleryForm'])->middleware('admin.auth');

Route::match(['get', 'post'], '/admin/home-slides.php', [AdminController::class, 'homeSlides'])->middleware('admin.auth');
Route::match(['get', 'post'], '/admin/home-slides-form.php', [AdminController::class, 'homeSlidesForm'])->middleware('admin.auth');

Route::match(['get', 'post'], '/admin/prices.php', [AdminController::class, 'prices'])->middleware('admin.auth');
Route::match(['get', 'post'], '/admin/prices-form.php', [AdminController::class, 'pricesForm'])->middleware('admin.auth');

Route::match(['get', 'post'], '/admin/services.php', [AdminController::class, 'services'])->middleware('admin.auth');
Route::match(['get', 'post'], '/admin/services-form.php', [AdminController::class, 'servicesForm'])->middleware('admin.auth');

// Settings pages (terpisah)
Route::match(['get', 'post'], '/admin/settings-profile.php', [AdminController::class, 'settingsProfile'])->name('admin.settings.profile')->middleware('admin.auth');
Route::match(['get', 'post'], '/admin/settings-password.php', [AdminController::class, 'settingsPassword'])->name('admin.settings.password')->middleware('admin.auth');
Route::match(['get', 'post'], '/admin/settings-contact.php', [AdminController::class, 'settingsContact'])->name('admin.settings.contact')->middleware('admin.auth');

// Main settings menu
Route::match(['get', 'post'], '/admin/settings.php', [AdminController::class, 'settings'])->middleware('admin.auth');

// Password Recovery Routes
Route::match(['get', 'post'], '/admin/recovery/request.php', [AdminController::class, 'recoveryRequest']);
Route::match(['get', 'post'], '/admin/recovery/verify.php', [AdminController::class, 'recoveryVerify']);
Route::match(['get', 'post'], '/admin/recovery/reset.php', [AdminController::class, 'recoveryReset']);

// Email Change Verification Route
Route::match(['get', 'post'], '/admin/settings/verify-email.php', [AdminController::class, 'verifyEmailChange']);
