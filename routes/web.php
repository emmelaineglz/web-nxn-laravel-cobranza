<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\TimbradosController;
use App\Http\Controllers\ServerController;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;


Route::get('/', fn() => view('welcome'));

Route::get('/login', [AuthController::class, 'ShowLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/dashboard', function () {
    if (!Auth::check()) return redirect()->route('login');
    return response()
        ->view('front.dashboard')
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->header('Pragma', 'no-cache');
})->middleware('auth');

Route::middleware(['auth'])->group(function () {
    // Vista principal del Kardex (ahora pasa datos)
    Route::get('/kardex', [ClienteController::class, 'kardexView'])
        ->name('kardex.index');

    // API CRUD de clientes (JSON)
    Route::resource('clientes', ClienteController::class)->only([
        'index', 'store', 'show', 'update', 'destroy'
    ]);
});

Route::delete('/clientes/{id}/force', [ClienteController::class, 'forceDestroy'])
    ->name('clientes.forceDestroy')
    ->middleware('auth');

// Vista Blade
Route::get('/empleados', [EmpleadoController::class, 'vista'])->name('empleados.index'); 
// API JSON
Route::get('/api/empleados', [EmpleadoController::class, 'index'])->name('empleados.api'); 

// Vista Blade
Route::get('/timbres', [TimbradosController::class, 'vista'])->name('timbres.index');
// API JSON
Route::get('/api/timbres', [TimbradosController::class, 'index'])->name('timbres.api');

Route::middleware(['auth', 'can:see-servers'])->group(function () {
    Route::resource('servers', ServerController::class)
        ->only(['index','create','store','edit','update','destroy'])
        ->names('servers');
});

