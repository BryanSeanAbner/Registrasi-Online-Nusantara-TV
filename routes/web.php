<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ScanController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [EventController::class, 'index']);
Route::get('/e/{slug}', [EventController::class, 'show'])->name('event.show');

Route::get('/login', function () {
    return redirect('/admin');
})->name('login');


Route::get('/e/{slug}/register', [RegistrationController::class, 'create'])->name('register.create');
Route::post('/e/{slug}/register', [RegistrationController::class, 'store'])->name('register.store');


Route::get('/t/{code}', [TicketController::class, 'show'])->name('ticket.show');

Route::middleware(['auth'])->group(function () {
    Route::get('/scan', [ScanController::class, 'page'])->name('scan.page');
    Route::post('/scan', [ScanController::class, 'scan'])->name('scan.submit');
}); 