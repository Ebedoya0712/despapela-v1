<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait Multitenantable
{
    /**
     * Este "boot" method se ejecuta automáticamente cuando se usa el Trait en un modelo.
     * Aplica el scope de forma automática a cada consulta.
     */
    protected static function bootMultitenantable()
    {
        static::addGlobalScope('by_company', function (Builder $builder) {
            $user = Auth::user();

            // Si no hay un usuario autenticado o si el usuario es Administrador, no se aplica ningún filtro.
            if (! $user || $user->role->name === 'Administrador') {
                return;
            }

            // Si el usuario es Gestor, filtramos por las empresas que posee.
            if ($user->role->name === 'Gestor') {
                $companyIds = $user->managedCompanies->pluck('id');
                $builder->whereIn('company_id', $companyIds);
            }

            // Si el usuario es Técnico o Trabajador, filtramos por las empresas de las que es miembro.
            if (in_array($user->role->name, ['Técnico', 'Trabajador'])) {
                $companyIds = $user->memberOfCompanies->pluck('id');
                $builder->whereIn('company_id', $companyIds);
            }
        });
    }
}
