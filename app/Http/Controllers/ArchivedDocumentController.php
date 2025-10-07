<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class ArchivedDocumentController extends Controller
{
    /**
     * Muestra los documentos archivados según el rol del usuario.
     */
    public function __invoke(Request $request)
    {
        $user = Auth::user();
        
        // 1. SELECT CRÍTICO: Seleccionamos explícitamente solo las columnas de la tabla 'documents' 
        $query = Document::select('documents.*') 
                        ->where('status', 'archived')
                        ->with('company');

        // Aplicamos un filtro diferente dependiendo del rol del usuario
        match ($user->role->name) {
            'Trabajador' => 
                $query->whereHas('signatures', fn($q) => $q->where('signer_id', $user->id)),
            
            'Técnico' => 
                // 2. CORRECCIÓN CRÍTICA: La ambigüedad proviene de la subconsulta de la relación.
                // Forzamos a que memberOfCompanies() haga el PLUCK sobre 'companies.id'
                $query->whereIn('documents.company_id', $user->memberOfCompanies()->pluck('companies.id')),
            
            'Gestor' => 
                // Aplicamos la misma corrección al gestor por consistencia y seguridad
                $query->whereIn('documents.company_id', $user->managedCompanies()->pluck('companies.id')),
            
            default => null,
        };

        // 3. ORDENACIÓN CRÍTICA: Indicamos explícitamente que la columna 'expires_at' 
        // pertenece a la tabla 'documents'.
        $archivedDocuments = $query->latest('documents.expires_at')->get();

        // Devolvemos la vista con los documentos encontrados
        return view('documents.archived', compact('archivedDocuments'));
    }
}
