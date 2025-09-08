<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Gestor\Gestorcontroller;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// --- Grupo de Rutas del Administrador ---
Route::middleware(['auth', 'can:manage-platform-users'])->prefix('admin')->name('admin.')->group(function () {
    
    // INICIO DEL CAMBIO
    // Ruta para mostrar el formulario de asignación
    Route::get('users/{user}/assign-company', [UserController::class, 'assignCompanyForm'])->name('users.assignCompanyForm');
    // Ruta para procesar la asignación
    Route::post('users/{user}/assign-company', [UserController::class, 'syncCompanies'])->name('users.syncCompanies');
    // FIN DEL CAMBIO

    // Ruta de recurso para el CRUD de Usuarios
    Route::resource('users', UserController::class);

    Route::resource('companies', CompanyController::class);

});

Route::middleware(['auth', 'can:manage-technicians'])->prefix('gestor')->name('gestor.')->group(function () {
    
    // Ruta para que el Gestor vea la lista de sus empresas
    Route::get('companies', [Gestorcontroller::class, 'index'])->name('companies.index');

});

require __DIR__.'/auth.php';

