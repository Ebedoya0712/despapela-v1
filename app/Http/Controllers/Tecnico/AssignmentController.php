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
use Illuminate\Support\Facades\Log;
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

        $technician = Auth::user();

        try {
            foreach ($request->workers as $workerId) {
                // firstOrCreate se asegura de no crear duplicados y es transaccional
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

                // Si el enlace es nuevo (no existía antes), intentamos enviar el correo.
                if ($linkCreated->wasRecentlyCreated) {
                    $worker = User::find($workerId);
                    if ($worker) {
                        // Uso de Mail::send para mejor manejo de errores de envío
                        Mail::to($worker->email)->send(new DocumentAssigned($document, $technician));
                    }
                }
            }

            return redirect()->route('tecnico.assignment.list')->with('success', 'Documento asignado y trabajadores notificados con éxito.');

        } catch (\Exception $e) {
            // 1. Registrar el error detallado en el log del servidor
            Log::error('Error al asignar documento o enviar correo: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
            
            // 2. Devolver al usuario con un mensaje de error claro
            return redirect()->route('tecnico.assignment.list')->with('error', 'Ocurrió un error al intentar asignar el documento. Por favor, verifica el log de errores.');
        }
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