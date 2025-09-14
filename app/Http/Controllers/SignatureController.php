<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\UniqueLink;
use App\Models\DocumentSignature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SignatureController extends Controller
{
    /**
     * Muestra la lista de documentos pendientes de firmar.
     */
    public function index()
    {
        $pendingLinks = UniqueLink::where('user_id', Auth::id())
                                   ->where('expires_at', '>', now())
                                   ->whereHas('document', fn($q) => $q->where('status', 'pending'))
                                   ->with('document.company')
                                   ->get();
        
        return view('signatures.index', compact('pendingLinks'));
    }

    /**
     * Muestra la página para firmar un documento.
     */
    public function show(UniqueLink $uniqueLink)
    {
        $document = $uniqueLink->document;
        $documentUrl = Storage::disk('documents')->url($document->storage_path);
        
        return view('signatures.show', compact('document', 'documentUrl', 'uniqueLink'));
    }

    /**
     * Procesa y guarda la firma y su posición.
     */
    public function store(Request $request, UniqueLink $uniqueLink)
    {
        $document = $uniqueLink->document;
        $validated = $request->validate([
            'signature' => 'required|string', // La firma vendrá en base64
            'signature_position' => 'required|json', // Las coordenadas de la firma
        ]);

        // 1. Guardar la imagen de la firma en un archivo físico
        $signatureData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $validated['signature']));
        $signatureFileName = 'signatures/' . uniqid() . '.png';
        Storage::disk('public')->put($signatureFileName, $signatureData);

        // 2. Crear el registro de la firma en la base de datos
        DocumentSignature::create([
            'document_id' => $document->id,
            'company_id' => $document->company_id,
            'signer_id' => Auth::id(),
            'filled_data' => json_decode($validated['signature_position'], true), // Guardamos las coordenadas
            'signature_image_path' => $signatureFileName,
            'signed_at' => now(),
        ]);
        
        // 3. Actualizar el estado del documento
        $document->update(['status' => 'signed']);
        
        // 4. Invalidar el enlace
        $uniqueLink->delete();

        return redirect()->route('signatures.index')->with('success', 'Documento firmado y enviado con éxito.');
    }
}