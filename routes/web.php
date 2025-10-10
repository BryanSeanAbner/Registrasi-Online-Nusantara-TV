<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\CheckAnimationController;
use Illuminate\Support\Facades\Response;

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
Route::get('/e/{slug}/register/thanks', function(string $slug) {
    return view('public.register.thanks');
})->name('register.thanks');



Route::get('/t/{code}', [TicketController::class, 'show'])->name('ticket.show');
Route::get('/t/{code}/qrcode/preview', function (string $code) {
    $path = storage_path("app/public/qrcodes/{$code}.png");

    abort_if(! file_exists($path), 404);

    return Response::file($path, [
        'Content-Type' => 'image/png',
        'Content-Disposition' => 'inline; filename="'.$code.'.png"',
    ]);
})->name('ticket.qr');

Route::middleware(['auth'])->group(function () {
    Route::get('/scan', [ScanController::class, 'page'])->name('scan.page');
    Route::post('/scan', [ScanController::class, 'scan'])->name('scan.submit');
    Route::get('/scan/fragment/{code}', [ScanController::class, 'fragment'])->name('scan.fragment');

    Route::get('/events/{event}/seats/map', [SeatController::class,'map'])->name('seats.map');
    Route::post('/events/{event}/seats/assign', [SeatController::class,'assign'])->name('seats.assign');
    Route::delete('/events/{event}/seats/assign', [SeatController::class, 'unassign'])->name('seats.unassign');
}); 

// Endpoint animasi SVG (dipindahkan dari routes/api.php)
Route::get('/api/check-animation', [CheckAnimationController::class, 'svg']);
