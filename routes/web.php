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
use App\Http\Controllers\Tecnico\StaffController as TecnicoStaffController; // ¡Importamos el controlador de Staff para Técnico!
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
    return to_route('login');
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
    Route::get('/{uniqueLink:token}', [SignatureController::class, 'show'])->name('show');
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

    // GESTIÓN DE TRABAJADORES (WORKERS)
    Route::middleware('can:manage-workers')->group(function () {
        // Listado consolidado de todos los trabajadores
        Route::get('workers', [TecnicoStaffController::class, 'index'])->name('workers.index');

        // Formulario de creación de trabajador en una empresa específica
        // técnico/companies/{company}/workers/create -> tecnico.workers.create
        Route::get('companies/{company}/workers/create', [TecnicoStaffController::class, 'create'])->name('workers.create');
        
        // Almacenamiento de nuevo trabajador
        // técnico/companies/{company}/workers -> tecnico.workers.store
        Route::post('companies/{company}/workers', [TecnicoStaffController::class, 'store'])->name('workers.store');
        
        // Formulario de edición de trabajador
        // técnico/companies/{company}/workers/{worker}/edit -> tecnico.workers.edit
        Route::get('companies/{company}/workers/{worker}/edit', [TecnicoStaffController::class, 'edit'])->name('workers.edit');
        
        // Actualización de trabajador (CAMBIADO A Route::put)
        // técnico/companies/{company}/workers/{worker} (PATCH/PUT) -> tecnico.workers.update
        Route::put('companies/{company}/workers/{worker}', [TecnicoStaffController::class, 'update'])->name('workers.update');
        
        // Eliminación de trabajador
        // técnico/companies/{company}/workers/{worker} (DELETE) -> tecnico.workers.destroy
        // Nota: Laravel es inteligente y encuentra el trabajador aunque la ruta no tenga la ID de la empresa en la definición de la ruta de delete.
        Route::delete('workers/{worker}', [TecnicoStaffController::class, 'destroy'])->name('workers.destroy');

        // Ruta para alternar el estado (activo/inactivo)
        // técnico/workers/{worker}/toggle-status -> tecnico.workers.toggleStatus
        Route::patch('workers/{worker}/toggle-status', [TecnicoStaffController::class, 'toggleStatus'])->name('workers.toggleStatus');
    });

    // Rutas de Documentos
    Route::get('documents/{document}/define-fields', [TecnicoDocumentController::class, 'defineFieldsForm'])->name('documents.defineFields');
    Route::post('documents/{document}/save-fields', [TecnicoDocumentController::class, 'saveFields'])->name('documents.saveFields');

    Route::get('documents/{document}/preview-pdf', [TecnicoDocumentController::class, 'previewPdf'])->name('documents.previewPdf');
    
    // Rutas para que el Técnico gestione los documentos DENTRO de una empresa
    Route::resource('documents', TecnicoDocumentController::class);

    // Rutas de Asignación
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
