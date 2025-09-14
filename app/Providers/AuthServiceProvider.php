<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\User;
use App\Models\Document;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // ROL 1: SUPER-ADMINISTRADOR (Acceso total)
        Gate::before(function (User $user) {
            if ($user->role->name === 'Administrador') {
                return true;
            }
        });

        // --- GATES ESPECÍFICOS POR ROL ---

        // PERMISO PARA GESTIONAR EMPRESAS (Solo Administrador)
        Gate::define('manage-companies', function (User $user) {
            return $user->role->name === 'Administrador';
        });

        // PERMISO PARA GESTIONAR TÉCNICOS (Solo Gestor)
        Gate::define('manage-technicians', function (User $user) {
            return $user->role->name === 'Gestor';
        });

        // PERMISO PARA GESTIONAR TRABAJADORES (Gestor y Técnico)
        Gate::define('manage-workers', function (User $user) {
            return in_array($user->role->name, ['Gestor', 'Técnico']);
        });

        // PERMISO PARA GESTIONAR DOCUMENTOS (Solo Técnico)
        Gate::define('manage-documents', function (User $user) {
            return $user->role->name === 'Técnico';
        });

        Gate::define('assign-documents', fn(User $user) => $user->role->name === 'Técnico');
        
        // Permiso para que el Trabajador firme (el Técnico también puede, por herencia)
        Gate::define('sign-documents', fn(User $user) => in_array($user->role->name, ['Trabajador', 'Técnico']));

        // Nuevo permiso para ver la bandeja de entrada "Documentos para Firmar" (SOLO TRABAJADOR)
        Gate::define('view-signature-inbox', fn(User $user) => $user->role->name === 'Trabajador');

        // PERMISO PARA FIRMAR DOCUMENTOS (Trabajador y Técnico)
        Gate::define('sign-documents', function (User $user) {
            return in_array($user->role->name, ['Trabajador', 'Técnico']);
        });

        // PERMISO PARA GESTIONAR TODOS LOS USUARIOS (Solo Administrador)
        Gate::define('manage-platform-users', function (User $user) {
            return $user->role->name === 'Administrador';
        });

        // Permiso para ver un documento específico (seguridad a nivel de fila)
        Gate::define('view-assigned-document', function (User $user, Document $document) {
            if (! in_array($user->role->name, ['Trabajador', 'Técnico'])) {
                return false;
            }
            // Lógica futura: verificar si el documento está asignado a este usuario.
            // Por ahora, verificamos que pertenezca a una de sus empresas.
            return $user->companies->contains($document->company_id);
        });
    }
}
