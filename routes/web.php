<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ScanController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [EventController::class, 'index']);
Route::get('/e/{slug}', [EventController::class, 'show'])->name('event.show');


Route::get('/e/{slug}/register', [RegisterController::class, 'create'])->name('register.create');
Route::post('/e/{slug}/register', [RegisterController::class, 'store'])->name('register.store');


Route::get('/t/{code}', [TicketController::class, 'show'])->name('ticket.show');


// halaman publik untuk petugas (bisa juga dibuat sebagai Filament Page)
Route::get('/scan', [ScanController::class, 'page'])->name('scan.page');
Route::post('/scan', [ScanController::class, 'scan'])->name('scan.submit');
