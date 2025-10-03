<?php

namespace App\Http\Controllers\Tecnico;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\UniqueLink;
use App\Models\User; // Asegúrate de importar User
use App\Mail\DocumentAssigned; // Importa la nueva clase Mailable
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail; // Importa la Facade de Mail
use Illuminate\Support\Str;

class AssignmentController extends Controller
{
    // ... (los otros métodos como listDocuments y showAssignmentForm se quedan igual) ...
    public function listDocuments()
    {
        $documents = Document::where('status', 'pending')
                                ->withCount('links')
                                ->get();
        return view('tecnico.assignment.list', compact('documents'));
    }

    public function showAssignmentForm(Document $document)
    {
        $workers = $document->company->staff()
                        ->whereHas('role', fn($q) => $q->where('name', 'Trabajador'))
                        ->get();

        $assignedWorkerIds = $document->links()->pluck('user_id')->toArray();

        return view('tecnico.assignment.form', compact('document', 'workers', 'assignedWorkerIds'));
    }

    /**
     * Crea los enlaces únicos y notifica a los trabajadores seleccionados.
     */
    public function assignToWorkers(Request $request, Document $document)
    {
        $request->validate(['workers' => 'required|array']);

        $technician = Auth::user(); // Obtenemos al técnico que está realizando la acción

        foreach ($request->workers as $workerId) {
            // firstOrCreate se asegura de no crear duplicados
            $linkCreated = UniqueLink::firstOrCreate(
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

            // Si el enlace es nuevo (no existía antes), enviamos el correo.
            // wasRecentlyCreated es una propiedad mágica de Eloquent.
            if ($linkCreated->wasRecentlyCreated) {
                $worker = User::find($workerId);
                if ($worker) {
                    Mail::to($worker->email)->send(new DocumentAssigned($document, $technician));
                }
            }
        }

        return redirect()->route('tecnico.assignment.list')->with('success', 'Documento asignado y trabajadores notificados con éxito.');
    }

    // El método regenerateLink y su ruta ya no son necesarios con este nuevo flujo.
    // Puedes considerar eliminarlos para limpiar el código.
    /*
    public function regenerateLink(Request $request, $documentId)
    {
        // ...
    }
    */
}