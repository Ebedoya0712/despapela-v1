<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArchivedDocumentController extends Controller
{
    /**
     * Muestra los documentos archivados según el rol del usuario.
     */
    public function __invoke(Request $request)
    {
        $user = Auth::user();
        // Preparamos la consulta base para documentos archivados
        $query = Document::where('status', 'archived')->with('company');

        // Aplicamos un filtro diferente dependiendo del rol del usuario
        match ($user->role->name) {
            'Trabajador' => $query->whereHas('signatures', fn($q) => $q->where('signer_id', $user->id)),
            'Técnico' => $query->whereIn('company_id', $user->memberOfCompanies()->pluck('id')),
            'Gestor' => $query->whereIn('company_id', $user->managedCompanies()->pluck('id')),
            // El Administrador no necesita filtro, ve todos.
            default => null,
        };

        // Obtenemos los resultados ordenados por la fecha de caducidad más reciente
        $archivedDocuments = $query->latest('expires_at')->get();

        // Devolvemos la vista con los documentos encontrados
        return view('documents.archived', compact('archivedDocuments'));
    }
}