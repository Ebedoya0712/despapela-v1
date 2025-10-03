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
        // --- GATES ESPECÍFICOS POR ROL ---

        // PERMISOS DE ADMINISTRADOR
        Gate::define('manage-companies', fn(User $user) => $user->role->name === 'Administrador');
        Gate::define('manage-platform-users', fn(User $user) => $user->role->name === 'Administrador');

        // PERMISOS DE GESTOR
        Gate::define('manage-technicians', fn(User $user) => $user->role->name === 'Gestor');

        // PERMISOS DE TÉCNICO
        Gate::define('view-assigned-companies', fn(User $user) => $user->role->name === 'Técnico'); // <-- NUEVO PERMISO
        Gate::define('assign-documents', fn(User $user) => $user->role->name === 'Técnico');

        // PERMISOS DE TRABAJADOR
        Gate::define('view-signature-inbox', fn(User $user) => $user->role->name === 'Trabajador');
        Gate::define('view-signed-documents', fn(User $user) => $user->role->name === 'Trabajador');

        // --- GATES COMPARTIDOS ---
        
        // El Administrador y el Técnico pueden gestionar documentos
        Gate::define('manage-documents', fn(User $user) => in_array($user->role->name, ['Administrador', 'Técnico']));
        
        // El Gestor y el Técnico pueden gestionar trabajadores
        Gate::define('manage-workers', fn(User $user) => in_array($user->role->name, ['Gestor', 'Técnico']));

        // Todos los roles pueden ver el módulo de documentos archivados
        Gate::define('view-archived-documents', function (User $user) {
            return in_array($user->role->name, ['Administrador', 'Gestor', 'Técnico', 'Trabajador']);
        });
    }
}