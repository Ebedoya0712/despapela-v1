<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CompanyController as AdminCompanyController;
use App\Http\Controllers\Gestor\Gestorcontroller;
use App\Http\Controllers\Gestor\StaffController as GestorStaffController; // <-- 1. Se añade la importación correcta
use App\Http\Controllers\Tecnico\DocumentController as TecnicoDocumentController;
use App\Http\Controllers\Tecnico\CompanyController as TecnicoCompanyController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// --- Rutas Públicas y de Autenticación Base ---
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

// --- Rutas del ADMINISTRADOR ---
Route::middleware(['auth', 'can:manage-platform-users'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', UserController::class);
    Route::resource('companies', AdminCompanyController::class);
    Route::get('users/{user}/assign-company', [UserController::class, 'assignCompanyForm'])->name('users.assignCompanyForm');
    Route::post('users/{user}/assign-company', [UserController::class, 'syncCompanies'])->name('users.syncCompanies');
});

// --- Rutas del GESTOR ---
Route::middleware(['auth', 'can:manage-technicians'])->prefix('gestor')->name('gestor.')->group(function () {
    Route::get('companies', [Gestorcontroller::class, 'index'])->name('companies.index');

    // 2. Se usa el controlador correcto que hemos importado
    Route::resource('companies.staff', GestorStaffController::class);
});

// --- Rutas del TÉCNICO ---
Route::middleware(['auth', 'can:manage-documents'])->prefix('tecnico')->name('tecnico.')->group(function () {
    
    // Ruta para que el Técnico vea su lista de empresas asignadas
    Route::resource('companies', TecnicoCompanyController::class)->only(['index', 'show']);

    // Ruta para mostrar el editor visual de campos
    Route::get('documents/{document}/define-fields', [TecnicoDocumentController::class, 'defineFieldsForm'])->name('documents.defineFields');
    // Ruta para guardar los campos definidos (se llamará vía AJAX)
    Route::post('documents/{document}/save-fields', [TecnicoDocumentController::class, 'saveFields'])->name('documents.saveFields');

    Route::get('documents/{document}/preview-pdf', [TecnicoDocumentController::class, 'previewPdf'])->name('documents.previewPdf');
    
    // Rutas para que el Técnico gestione los documentos DENTRO de una empresa
    Route::resource('documents', TecnicoDocumentController::class);
});

require __DIR__.'/auth.php';

