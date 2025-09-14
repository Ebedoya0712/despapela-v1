<?php

namespace App\Http\Controllers\Tecnico;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\User;
use App\Models\UniqueLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AssignmentController extends Controller
{
    /**
     * Muestra una lista de documentos listos para ser asignados.
     */
    public function listDocuments()
    {
        $documents = Document::where('status', 'pending')
                               ->withCount('links') // Contamos cuántos enlaces ya tiene
                               ->get();
        return view('tecnico.assignment.list', compact('documents'));
    }

    /**
     * Muestra el formulario para asignar un documento a los trabajadores de su empresa.
     */
    public function showAssignmentForm(Document $document)
    {
        // Obtenemos los trabajadores de la empresa del documento
        $workers = $document->company->staff()
                        ->whereHas('role', fn($q) => $q->where('name', 'Trabajador'))
                        ->get();
        
        // Obtenemos los IDs de los trabajadores que ya tienen un enlace para este documento
        $assignedWorkerIds = $document->links()->pluck('user_id')->toArray();

        return view('tecnico.assignment.form', compact('document', 'workers', 'assignedWorkerIds'));
    }

    /**
     * Crea los enlaces únicos para los trabajadores seleccionados.
     */
    public function assignToWorkers(Request $request, Document $document)
    {
        $request->validate(['workers' => 'required|array']);

        foreach ($request->workers as $workerId) {
            // Creamos el enlace solo si no existe ya uno para ese usuario y documento
            UniqueLink::firstOrCreate(
                [
                    'document_id' => $document->id,
                    'user_id' => $workerId,
                ],
                [
                    'company_id' => $document->company_id,
                    'token' => Str::random(40),
                    'expires_at' => now()->addDays(30),
                ]
            );
        }
        return redirect()->route('tecnico.assignment.list')->with('success', 'Documento asignado con éxito.');
    }
}
