<?php

namespace App\Http\Controllers\Tecnico;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\UniqueLink;
use App\Models\User;
use App\Mail\DocumentAssigned;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AssignmentController extends Controller
{
    /**
     * Muestra la lista de documentos, permitiendo filtrar por estado de asignación.
     */
    public function listDocuments(Request $request)
    {
        // Obtener el filtro de la consulta, por defecto es 'all' (todos)
        $filter = $request->query('filter', 'all');

        // Empezar la consulta, siempre contando los enlaces (asignaciones)
        $query = Document::withCount('links')->orderBy('id', 'desc');

        if ($filter === 'pending') {
            // Filtrar por documentos que NO tienen ningún enlace (pendientes de asignar)
            $query->has('links', '=', 0);
        } elseif ($filter === 'assigned') {
            // Filtrar por documentos que tienen AL MENOS un enlace (ya asignados)
            $query->has('links', '>', 0);
        }
        // Si $filter es 'all', no se aplica ningún filtro de asignación.
        
        $documents = $query->get();

        // Pasamos tanto los documentos como el filtro actual a la vista
        return view('tecnico.assignment.list', compact('documents', 'filter'));
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
}
