<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CompanyController as AdminCompanyController;
use App\Http\Controllers\Gestor\Gestorcontroller;
use App\Http\Controllers\Gestor\StaffController as GestorStaffController;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\Tecnico\DocumentController as TecnicoDocumentController;
use App\Http\Controllers\Tecnico\CompanyController as TecnicoCompanyController;
use App\Http\Controllers\Tecnico\AssignmentController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Aquí se registran las rutas web de la aplicación.
|
*/

// --- Rutas Públicas y de Autenticación Base ---
Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/archived-documents', [App\Http\Controllers\ArchivedDocumentController::class, '__invoke'])->name('documents.archived');
});

// --- Rutas para Firmas ---
Route::middleware('auth')->prefix('signatures')->name('signatures.')->group(function () {
    Route::get('/', [SignatureController::class, 'index'])->name('index');
    // 👇 CORRECCIÓN APLICADA AQUÍ 👇
    Route::get('/{uniqueLink:token}', [SignatureController::class, 'show'])->name('show');
    // 👇 Y AQUÍ 👇
    Route::post('/{uniqueLink:token}', [SignatureController::class, 'store'])->name('store');
});

// --- Rutas del ADMINISTRADOR ---
Route::middleware(['auth', 'can:manage-platform-users'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', UserController::class);
    Route::resource('companies', AdminCompanyController::class);
    Route::get('users/{user}/assign-company', [UserController::class, 'assignCompanyForm'])->name('users.assignCompanyForm');
    Route::post('users/{user}/assign-company', [UserController::class, 'syncCompanies'])->name('users.syncCompanies');
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggleStatus');
});

// --- Rutas del GESTOR ---
Route::middleware(['auth', 'can:manage-technicians'])->prefix('gestor')->name('gestor.')->group(function () {
    Route::get('companies', [Gestorcontroller::class, 'index'])->name('companies.index');
    Route::resource('companies.staff', GestorStaffController::class);
    Route::patch('companies/{company}/staff/{staff}/toggle-status', [GestorStaffController::class, 'toggleStatus'])->name('companies.staff.toggleStatus');
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

    Route::middleware('can:assign-documents')->prefix('assignment')->name('assignment.')->group(function () {
        Route::get('/', [AssignmentController::class, 'listDocuments'])->name('list');
        Route::get('/{document}', [AssignmentController::class, 'showAssignmentForm'])->name('showForm');
        Route::post('/{document}', [AssignmentController::class, 'assignToWorkers'])->name('assign');

        // Nueva ruta para regenerar enlace de un trabajador
        Route::post('/{document}/{worker}/regenerate-link', [AssignmentController::class, 'regenerateLink'])
            ->name('regenerateLink');
    });
});

// --- Rutas para Documentos ya Firmados ---
Route::middleware('can:view-signed-documents')->prefix('signed')->name('signed.')->group(function () {
    Route::get('/', [SignatureController::class, 'signedIndex'])->name('index');
    Route::get('/{documentSignature}/view', [SignatureController::class, 'viewSignedPdf'])->name('view');
    Route::get('/{documentSignature}/download', [SignatureController::class, 'downloadSignedPdf'])->name('download');
    Route::post('/{document}/generate-link', [SignatureController::class, 'regenerateLink'])->name('generateLink');
});

require __DIR__.'/auth.php';
