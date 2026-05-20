<?php

use App\Http\Controllers\GeminiChatbotController;
use App\Http\Controllers\PresensiController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WaNotificationController;

Route::get('/', [WaNotificationController::class, 'index'])->name('wa.index');
Route::post('/send', [WaNotificationController::class, 'send'])->name('wa.send');
// Rute untuk menerima pesan masuk dari Fonnte (harus POST)
// Route::match(['get', 'post'], '/fonnte-webhook', [WaNotificationController::class, 'webhook']);

// Rute Dashboard Geofencing
Route::get('/admin/geofencing', [WaNotificationController::class, 'geofencingDashboard'])->name('admin.geofencing');
Route::post('/admin/geofencing/update', [WaNotificationController::class, 'updateGeofencing'])->name('admin.geofencing.update');


Route::get('/presensi/{wa}', [PresensiController::class, 'create'])->name('presensi.create');
Route::post('/presensi/store', [PresensiController::class, 'store'])->name('presensi.store');

Route::match(['get', 'post'], '/webhook-fonnte', [GeminiChatbotController::class, 'webhook']);
