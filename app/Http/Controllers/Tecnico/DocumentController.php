<?php

namespace App\Http\Controllers\Tecnico;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // Importante para generar la URL del PDF
use setasign\Fpdi\Tcpdf\Fpdi;
use Illuminate\Support\Str;
use App\Models\Tag;
use Illuminate\Support\Facades\Log;

class DocumentController extends Controller
{
    /**
     * Muestra la lista de documentos del técnico.
     */
    public function index()
    {
        $documents = Document::with('company')->latest()->get();
        return view('tecnico.documents.index', compact('documents'));
    }

    /**
     * Muestra el formulario para subir un nuevo documento.
     */
    public function create()
    {
        $tecnico = Auth::user();
        $companies = $tecnico->memberOfCompanies()->get();
        return view('tecnico.documents.create', compact('companies'));
    }

    /**
     * Valida y guarda el nuevo documento PDF.
     */
    // En app/Http/Controllers/Tecnico/DocumentController.php

public function store(Request $request)
{
    $request->validate([
        'company_id' => 'required|exists:companies,id',
        'document_file' => 'required|file|mimes:pdf|max:10240',
        'expiration_period' => 'required|string', // Validamos el nuevo campo
    ]);

    // Calculamos la fecha de caducidad
    $expiresAt = now();
    switch ($request->expiration_period) {
        case '2_months':
            $expiresAt->addMonths(2);
            break;
        case '6_months':
            $expiresAt->addMonths(6);
            break;
        case '1_year':
            $expiresAt->addYear();
            break;
        case '2_years':
            $expiresAt->addYears(2);
            break;
        case '1_month':
        default:
            $expiresAt->addMonth();
            break;
    }

    $file = $request->file('document_file');
    $path = $file->store('/', 'documents');

    Document::create([
        'company_id' => $request->company_id,
        'uploader_id' => Auth::id(),
        'original_filename' => $file->getClientOriginalName(),
        'storage_path' => $path,
        'expires_at' => $expiresAt, // Guardamos la fecha calculada
    ]);

    return redirect()->route('tecnico.documents.index')->with('success', 'Documento subido con éxito.');
}


    public function edit(Document $document)
    {
        // Redirigimos al editor de campos que ya hemos construido.
        return redirect()->route('tecnico.documents.defineFields', $document->id);
    }

    /**
     * Elimina el documento y su archivo físico.
     */
    public function destroy(Document $document)
    {
        try {
            // 1. Eliminar el archivo físico del almacenamiento.
            if (Storage::disk('documents')->exists($document->storage_path)) {
                Storage::disk('documents')->delete($document->storage_path);
            }

            // 2. Eliminar el registro de la base de datos (esto eliminará en cascada los campos, firmas, etc.).
            $document->delete();

            return redirect()->route('tecnico.documents.index')->with('success', 'Documento eliminado con éxito.');

        } catch (\Exception $e) {
            Log::error('Error al eliminar documento: ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al intentar eliminar el documento.');
        }
    }

    /**
     * Muestra el editor visual para definir campos en un documento.
     */
    public function defineFieldsForm(Document $document)
{
    $documentUrl = Storage::disk('documents')->url($document->storage_path);
    // Cargamos los campos que ya existen para este documento
    $existingFields = $document->fields;
    $tags = Tag::all();

    return view('tecnico.documents.define-fields', compact('document', 'documentUrl', 'existingFields', 'tags'));
}

    /**
     * Guarda los campos definidos para un documento.
     */
    // En DocumentController.php

public function saveFields(Request $request, Document $document)
{
    $validatedData = $request->validate([
        'fields' => 'present|array',
        'fields.*.name' => 'required|string|max:255',
        'fields.*.type' => 'required|string|in:text,signature',
        'fields.*.value' => 'nullable|string', // Aquí se guarda el texto o la firma
        'fields.*.page' => 'required|integer|min:1',
        'fields.*.x' => 'required|numeric',
        'fields.*.y' => 'required|numeric',
        'fields.*.width' => 'required|numeric',
        'fields.*.height' => 'required|numeric',
    ]);

    try {
        \DB::transaction(function () use ($validatedData, $document) {
            $document->fields()->delete();

            foreach ($validatedData['fields'] as $fieldData) {
                $document->fields()->create([
                    'company_id' => $document->company_id,
                    'name' => $fieldData['name'],
                    'type' => $fieldData['type'],
                    'value' => $fieldData['value'],
                    'coordinates' => [
                        'page' => $fieldData['page'],
                        'x' => $fieldData['x'],
                        'y' => $fieldData['y'],
                        'width' => $fieldData['width'],
                        'height' => $fieldData['height'],
                    ],
                ]);
            }
        });

        return response()->json(['success' => 'Campos guardados con éxito.']);

    } catch (\Exception $e) {
        Log::error('Error al guardar campos del documento: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Ocurrió un error inesperado en el servidor.'
        ], 500);
    }
}

    /**
     * Genera un PDF de previsualización con los campos dibujados.
     */
    // En DocumentController.php

public function previewPdf(Document $document)
{
    $filePath = Storage::disk('documents')->path($document->storage_path);
    $pdf = new Fpdi();
    $pageCount = $pdf->setSourceFile($filePath);

    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        $templateId = $pdf->importPage($pageNo);
        $size = $pdf->getTemplateSize($templateId);

        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($templateId);

        $fieldsForPage = $document->fields()->where('coordinates->page', $pageNo)->get();

        foreach ($fieldsForPage as $field) {
            $coords = $field->coordinates;
            $scale = 1.5;
            $px_to_mm = 0.264583;
            
            $x_mm = ($coords['x'] / $scale) * $px_to_mm;
            $y_mm = ($coords['y'] / $scale) * $px_to_mm;
            $width_mm = ($coords['width'] / $scale) * $px_to_mm;
            $height_mm = ($coords['height'] / $scale) * $px_to_mm;

            switch ($field->type) {
                case 'signature':
                    if (!empty($field->value)) {
                        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $field->value));
                        $pdf->Image('@'.$imageData, $x_mm, $y_mm, $width_mm, $height_mm, 'PNG');
                    }
                    break;
                
                case 'text':
                default:
                    // Definimos el estilo del texto que se va a escribir
                    $pdf->SetFont('helvetica', '', 9);
                    $pdf->SetTextColor(0, 0, 0); // Texto color negro
                    
                    // Posicionamos el cursor
                    $pdf->SetXY($x_mm, $y_mm);
                    
                    // <-- ¡CAMBIO CLAVE AQUÍ! -->
                    // Ahora usamos $field->value, que contiene el texto que escribiste.
                    // Si el valor está vacío, usamos el nombre del campo como placeholder.
                    // También quitamos el borde y el fondo para que se vea como texto normal.
                    $textToWrite = $field->value ?: $field->name;
                    $pdf->MultiCell($width_mm, $height_mm, $textToWrite, 0, 'L', false, 1, '', '', true, 0, false, true, 0, 'M');
                    break;
            }
        }
    }

    $fileName = 'documento_rellenado_' . Str::slug($document->original_filename) . '.pdf';
    return response($pdf->Output($fileName, 'I'), 200) // Cambiado a 'I' para ver en navegador
        ->header('Content-Type', 'application/pdf');
}

}

