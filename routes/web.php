<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('landing');
Route::view('/menu', 'menu')->name('menu');
Route::view('/cart', 'cart')->name('cart');
Route::view('/about', 'about')->name('about');
