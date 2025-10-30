<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\CheckAnimationController;
use Illuminate\Support\Facades\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [EventController::class, 'index']);
Route::get('/e/{slug}', [EventController::class, 'show'])->name('event.show');

Route::get('/e/{slug}/participants', [EventController::class, 'participants'])->name('event.participants');
Route::get('/events/{event}/registrations/{registration}.json', [EventController::class, 'participantRowJson'])->name('participants.row.json');

// Stream event-related public images via controller (avoid exposing raw storage paths)
Route::get('/images/{img}', [EventController::class, 'eventImage'])
    ->where('img', '.*')
    ->name('event.image');

Route::get('/login', function () {
    return redirect('/admin');
})->name('login');

// Store active event selection for Filament admin panel
Route::post('/admin/set-active-event', function (\Illuminate\Http\Request $request) {
    $id = $request->input('event_id');
    if ($id === null || $id === '') {
        session()->forget('active_event_id');
    } else {
        session(['active_event_id' => (int) $id]);
    }
    return back();
})->name('admin.setActiveEvent');


Route::get('/e/{slug}/register', [RegistrationController::class, 'create'])->name('register.create');
Route::post('/e/{slug}/register', [RegistrationController::class, 'store'])->name('register.store');
Route::get('/e/{slug}/register/thanks', function(string $slug) {
    return view('public.register.thanks');
})->name('register.thanks');
Route::get('/e/{slug}/register/qrcode.png', function (string $slug) {
    $url = route('register.create', ['slug' => $slug]);
    $png = QrCode::format('png')->size(600)->margin(1)->generate($url);

    $filename = 'register-' . $slug . '.png';
    return response($png, 200, [
        'Content-Type' => 'image/png',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        'Pragma' => 'no-cache',
    ]);
})->name('event.register.qr');

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
