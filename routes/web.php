<?php

use App\Http\Controllers\Admin\AdminPanelController;
use App\Http\Controllers\Auth\AuthenticationController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('landing');
Route::view('/menu', 'menu')->name('menu');
Route::view('/cart', 'cart')->name('cart');
Route::view('/about', 'about')->name('about');

Route::controller(AuthenticationController::class)->group(function (): void {
    Route::get('/login', 'showUserLogin')->name('login');
    Route::post('/login', 'loginUser')->name('login.store');
    Route::get('/register', 'showUserRegister')->name('register');
    Route::post('/register', 'registerUser')->name('register.store');
    Route::get('/admin/login', 'showAdminLogin')->name('admin.login');
    Route::post('/admin/login', 'loginAdmin')->name('admin.login.store');
    Route::post('/logout', 'logout')->name('logout');
});

Route::prefix('admin')->middleware('admin.access')->controller(AdminPanelController::class)->group(function (): void {
    Route::get('/', 'dashboard')->name('dashboard');
    Route::get('/global-search', 'globalSearch')->name('admin.global-search');

    Route::get('/reservasi', 'reservations')->name('admin.reservations.index');
    Route::patch('/reservasi/{reservation}/status', 'updateReservationStatus')->name('admin.reservations.status');
    Route::delete('/reservasi/{reservation}', 'destroyReservation')->name('admin.reservations.destroy');

    Route::get('/menu', 'menu')->name('admin.menu.index');
    Route::post('/menu', 'storeMenu')->name('admin.menu.store');
    Route::patch('/menu/{menuItem}', 'updateMenu')->name('admin.menu.update');
    Route::delete('/menu/{menuItem}', 'destroyMenu')->name('admin.menu.destroy');

    Route::get('/meja', 'tables')->name('admin.tables.index');
    Route::post('/meja', 'storeTable')->name('admin.tables.store');
    Route::patch('/meja/{cafeTable}', 'updateTable')->name('admin.tables.update');
    Route::delete('/meja/{cafeTable}', 'destroyTable')->name('admin.tables.destroy');

    Route::get('/slot-reservasi', 'slots')->name('admin.slots.index');
    Route::post('/slot-reservasi', 'storeSlot')->name('admin.slots.store');
    Route::patch('/slot-reservasi/{reservationSlot}', 'updateSlot')->name('admin.slots.update');
    Route::delete('/slot-reservasi/{reservationSlot}', 'destroySlot')->name('admin.slots.destroy');

    Route::get('/pembayaran', 'payments')->name('admin.payments.index');
    Route::patch('/pembayaran/{payment}/status', 'updatePaymentStatus')->name('admin.payments.status');
    Route::delete('/pembayaran/{payment}', 'destroyPayment')->name('admin.payments.destroy');

    Route::get('/profil-cafe', 'profile')->name('admin.profile.index');
    Route::patch('/profil-cafe', 'updateProfile')->name('admin.profile.update');

    Route::get('/pengguna', 'users')->name('admin.users.index');
    Route::post('/pengguna', 'storeUser')->name('admin.users.store');
    Route::patch('/pengguna/{user}', 'updateUser')->name('admin.users.update');
    Route::delete('/pengguna/{user}', 'destroyUser')->name('admin.users.destroy');
});
