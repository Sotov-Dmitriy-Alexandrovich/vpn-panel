<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\VpnPanelController;

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/status', [App\Http\Controllers\StatusController::class, 'index'])->name('status');
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

use App\Http\Controllers\AddClientController;

// Страница добавления
Route::get('/add', function () {
    return view('pages.add', ['page' => 'add']);
})->name('add');

// Обработка формы (POST)
Route::post('/add', [AddClientController::class, 'store']);

// Скачивание файла (GET)
Route::get('/download/{filename}', function ($filename) {
    // Путь к папке, куда скрипт сохраняет конфиги
    $path = "/opt/vpn-panel/www/downloads/{$filename}";

    if (!file_exists($path)) {
        abort(404, 'Файл конфигурации не найден');
    }

    return response()->download($path, $filename, [
        'Content-Type' => 'application/x-openvpn-profile'
    ]);
})->where('filename', '.*\.ovpn$');
