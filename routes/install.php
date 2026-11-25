<?php

use App\Http\Controllers\InstallController;
use Illuminate\Support\Facades\Route;

// ওয়েব ইনস্টলার — 'web' মিডলওয়্যার ছাড়া (bootstrap/app.php দেখুন)
Route::get('/install', [InstallController::class, 'show'])->name('install.show');
Route::post('/install', [InstallController::class, 'run'])->name('install.run');
