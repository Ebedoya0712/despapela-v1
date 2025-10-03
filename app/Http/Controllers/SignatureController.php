<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\UniqueLink;
use App\Models\DocumentSignature;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use setasign\Fpdi\Fpdi;

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
    // En app/Http/Controllers/SignatureController.php

public function show(UniqueLink $uniqueLink)
{
    $document = $uniqueLink->document;
    $worker = $uniqueLink->user;

    $fields = $document->fields()->get();

    foreach ($fields as $field) {
        //  --- CORRECCIÓN FINAL: Comparamos con el texto EXACTO de la base de datos --- 
        switch (trim($field->name)) {
            case 'NOMBRE Y APELLIDOS':
                $field->value = $worker->name;
                break;
            case 'DNI':
                $field->value = $worker->dni;
                break;
            case 'EMAIL':
                $field->value = $worker->email;
                break;
            case 'TELÉFONO': 
                $field->value = $worker->phone;
                break;
            case 'NÚMERO DE CUENTA BANCARIA':
                $field->value = $worker->bank_account;
                break;
            case 'DIRECCIÓN': 
                $field->value = $worker->address;
                break;
        }
    }

    $documentUrl = Storage::disk('documents')->url($document->storage_path);

    return view('signatures.show', compact('document', 'documentUrl', 'uniqueLink', 'fields'));
}

    /**
     * Procesa y guarda la firma y su posición.
     */
    public function store(Request $request, UniqueLink $uniqueLink)
    {
        $document = $uniqueLink->document;
        $validated = $request->validate([
            'signature' => 'required|string',
            'signature_position' => 'required|json',
        ]);
        
        // 1. Decodificar la firma y guardarla como imagen
        $signatureData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $validated['signature']));
        $signatureFileName = 'signatures/' . Str::uuid() . '.png';
        Storage::disk('public')->put($signatureFileName, $signatureData);

        // 2. Crear el registro de la firma en la base de datos
        // NOTA: Aquí se asume que un documento solo lo firma el trabajador.
        // Si el técnico también necesita un registro de firma formal, esta lógica necesitaría ampliarse.
        DocumentSignature::create([
            'document_id' => $document->id,
            'company_id' => $document->company_id,
            'signer_id' => Auth::id(),
            'filled_data' => json_decode($validated['signature_position'], true),
            'signature_image_path' => $signatureFileName,
            'signed_at' => now(),
        ]);
        
        // 3. Actualizar el estado del documento a 'firmado'
        // (Considerar una lógica más avanzada si se requieren múltiples firmas)
        $document->update(['status' => 'signed']);
        
        // 4. Invalidar el enlace de firma
        $uniqueLink->delete();

        return redirect()->route('signatures.index')->with('success', 'Documento firmado y enviado con éxito.');
    }

    /**
     * Muestra la lista de documentos que el usuario ya ha firmado.
     */
    public function signedIndex()
    {
        $signatures = DocumentSignature::where('signer_id', Auth::id())
                                        ->with('document.company')
                                        ->latest('signed_at')
                                        ->get();
        
        return view('signatures.signed-index', compact('signatures'));
    }


    public function downloadSignedPdf(DocumentSignature $documentSignature)
    {
        $document = $documentSignature->document;
        $worker = $documentSignature->signer;
        $filePath = Storage::disk('documents')->path($document->storage_path);
        
        $pdf = new Fpdi();
        
        try {
            define('PT_TO_MM', 25.4 / 72);
            $pageCount = $pdf->setSourceFile($filePath);

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);
                
                $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
                $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);

                $fieldsForPage = $document->fields()->where('coordinates->page', $pageNo)->get();

                foreach ($fieldsForPage as $field) {
                    $coords = $field->coordinates;
                    $scale = 1.5;
                    
                    $x_pt = $coords['x'] / $scale;
                    $y_pt = $coords['y'] / $scale;
                    $width_pt = $coords['width'] / $scale;
                    $height_pt = $coords['height'] / $scale;

                    $x_mm = $x_pt * PT_TO_MM;
                    $y_mm = $y_pt * PT_TO_MM;
                    $width_mm = $width_pt * PT_TO_MM;
                    $height_mm = $height_pt * PT_TO_MM;
                    
                    $fieldValue = $field->value;
                    switch (trim($field->name)) {
                        case 'NOMBRE Y APELLIDOS': $fieldValue = $worker->name; break;
                        case 'DNI': $fieldValue = $worker->dni; break;
                        case 'EMAIL': $fieldValue = $worker->email; break;
                        case 'TELÉFONO': $fieldValue = $worker->phone; break;
                        case 'NÚMERO DE CUENTA BANCARIA': $fieldValue = $worker->bank_account; break;
                        case 'DIRECCIÓN': $fieldValue = $worker->address; break;
                    }

                    if ($field->type === 'signature' && !empty($fieldValue)) {
                        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $fieldValue));
                        $pdf->Image('@'.$imageData, $x_mm, $y_mm, $width_mm, $height_mm, 'PNG');
                    } else if ($field->type === 'text') {
                        $pdf->SetFont('helvetica', '', 9);
                        $pdf->SetTextColor(0, 0, 0);
                        $pdf->SetXY($x_mm, $y_mm);
                        $pdf->MultiCell($width_mm, $height_mm, $fieldValue ?? '', 0, 'L');
                    }
                }
                
                if (isset($documentSignature->filled_data['page']) && $documentSignature->filled_data['page'] == $pageNo) {
                    $coords = $documentSignature->filled_data;
                    $signaturePath = Storage::disk('public')->path($documentSignature->signature_image_path);
                    
                    if (file_exists($signaturePath)) {
                        $scale = 1.5;
                        $x_pt = $coords['x'] / $scale;
                        $y_pt = $coords['y'] / $scale;
                        $width_pt = $coords['width'] / $scale;
                        $height_pt = $coords['height'] / $scale;
                        $x_mm = $x_pt * PT_TO_MM;
                        $y_mm = $y_pt * PT_TO_MM;
                        $width_mm = $width_pt * PT_TO_MM;
                        $height_mm = $height_pt * PT_TO_MM;
                        
                        $pdf->Image($signaturePath, $x_mm, $y_mm, $width_mm, $height_mm, 'PNG');
                    }
                }
            }
            
            // 👇 CAMBIO CLAVE: 'D' en lugar de 'I' para forzar la descarga 👇
            $fileName = 'firmado-' . Str::slug($document->original_filename) . '.pdf';
            return response($pdf->Output($fileName, 'D'), 200)
                ->header('Content-Type', 'application/pdf');
                
        } catch (\Exception $e) {
            \Log::error('Error al generar PDF para descarga: ' . $e->getMessage());
            return back()->with('error', 'Error al procesar el PDF.');
        }
    }

    /**
     * Genera y muestra el PDF final con la firma estampada para visualización.
     */
    public function viewSignedPdf(DocumentSignature $documentSignature)
    {
        $document = $documentSignature->document;
        $worker = $documentSignature->signer;
        $filePath = Storage::disk('documents')->path($document->storage_path);
        
        $pdf = new Fpdi();
        
        try {
            $pageCount = $pdf->setSourceFile($filePath);

            // Definimos la conversión estándar de Puntos-PDF a Milímetros
            // 1 pulgada = 72 puntos, 1 pulgada = 25.4 mm
            define('PT_TO_MM', 25.4 / 72);

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);
                
                $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
                $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);

                // --- 1. OBTENEMOS Y RELLENAMOS TODOS LOS CAMPOS PREDEFINIDOS ---
                $fieldsForPage = $document->fields()->where('coordinates->page', $pageNo)->get();

                foreach ($fieldsForPage as $field) {
                    $coords = $field->coordinates;
                    // La escala del visor de JS
                    $scale = 1.5; 
                    
                    // 👇 --- FÓRMULA DE CONVERSIÓN CORREGIDA --- 👇
                    // 1. Convertimos los píxeles del JS a Puntos-PDF (revirtiendo la escala)
                    $x_pt = $coords['x'] / $scale;
                    $y_pt = $coords['y'] / $scale;
                    $width_pt = $coords['width'] / $scale;
                    $height_pt = $coords['height'] / $scale;

                    // 2. Convertimos los Puntos-PDF a Milímetros para FPDI
                    $x_mm = $x_pt * PT_TO_MM;
                    $y_mm = $y_pt * PT_TO_MM;
                    $width_mm = $width_pt * PT_TO_MM;
                    $height_mm = $height_pt * PT_TO_MM;
                    
                    $fieldValue = $field->value;
                    switch (trim($field->name)) {
                        case 'NOMBRE Y APELLIDOS': $fieldValue = $worker->name; break;
                        case 'DNI': $fieldValue = $worker->dni; break;
                        case 'EMAIL': $fieldValue = $worker->email; break;
                        case 'TELÉFONO': $fieldValue = $worker->phone; break;
                        case 'NÚMERO DE CUENTA BANCARIA': $fieldValue = $worker->bank_account; break;
                        case 'DIRECCIÓN': $fieldValue = $worker->address; break;
                    }

                    if ($field->type === 'signature') {
                        if (!empty($fieldValue)) {
                            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $fieldValue));
                            $pdf->Image('@'.$imageData, $x_mm, $y_mm, $width_mm, $height_mm, 'PNG');
                        }
                    } else {
                        $pdf->SetFont('helvetica', '', 9);
                        $pdf->SetTextColor(0, 0, 0);
                        $pdf->SetXY($x_mm, $y_mm);
                        $pdf->MultiCell($width_mm, $height_mm, $fieldValue ?? '', 0, 'L');
                    }
                }
                
                // --- 2. ESTAMPAMOS LA FIRMA DEL TRABAJADOR ---
                if (isset($documentSignature->filled_data['page']) && $documentSignature->filled_data['page'] == $pageNo) {
                    $coords = $documentSignature->filled_data;
                    $signaturePath = Storage::disk('public')->path($documentSignature->signature_image_path);
                    
                    if (file_exists($signaturePath)) {
                        $scale = 1.5; // La escala del visor de JS
                        
                        // Aplicamos la misma fórmula de conversión precisa
                        $x_pt = $coords['x'] / $scale;
                        $y_pt = $coords['y'] / $scale;
                        $width_pt = $coords['width'] / $scale;
                        $height_pt = $coords['height'] / $scale;

                        $x_mm = $x_pt * PT_TO_MM;
                        $y_mm = $y_pt * PT_TO_MM;
                        $width_mm = $width_pt * PT_TO_MM;
                        $height_mm = $height_pt * PT_TO_MM;
                        
                        $pdf->Image($signaturePath, $x_mm, $y_mm, $width_mm, $height_mm, 'PNG');
                    }
                }
            }
            
            return response($pdf->Output('signed_document.pdf', 'I'), 200)
                ->header('Content-Type', 'application/pdf');
                
        } catch (\Exception $e) {
            \Log::error('Error al generar PDF firmado: ' . $e->getMessage());
            return back()->with('error', 'Error al procesar el PDF: ' . $e->getMessage());
        }
    }
}