<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\VpnPanelController;

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/{page}', [VpnPanelController::class, 'view'])
        ->where('page', 'connected|add|console|status')
        ->name('page');

});

Route::match(['get', 'post'], '/api/{action}', [VpnPanelController::class, 'handleApi'])->name('api');

Route::middleware(['auth'])->group(function () {
});

Route::redirect('/', '/connected');

Route::get('/test-openvpn', function () {
    return response()->json([
        'file' => file_exists('/var/log/openvpn/status.log'),
        'size' => filesize('/var/log/openvpn/status.log'),
        'content' => substr(file_get_contents('/var/log/openvpn/status.log'), 0, 500)
    ]);
});
