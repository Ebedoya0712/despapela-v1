<?php

namespace App\Http\Controllers\Tecnico;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // Importante para generar la URL del PDF
use setasign\Fpdi\Tcpdf\Fpdi;
use Illuminate\Support\Str;

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
    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'document_file' => 'required|file|mimes:pdf|max:10240',
        ]);

        $file = $request->file('document_file');
        $path = $file->store('/', 'documents');

        Document::create([
            'company_id' => $request->company_id,
            'uploader_id' => Auth::id(),
            'original_filename' => $file->getClientOriginalName(),
            'storage_path' => $path,
        ]);

        return redirect()->route('tecnico.documents.index')->with('success', 'Documento subido con éxito.');
    }

    /**
     * Muestra el editor visual para definir campos en un documento.
     */
    public function defineFieldsForm(Document $document)
    {
        // Generamos una URL pública para que el frontend pueda acceder al PDF
        $documentUrl = Storage::disk('documents')->url($document->storage_path);

        // Cargamos los campos que ya existen para este documento
        $existingFields = $document->fields;

        return view('tecnico.documents.define-fields', compact('document', 'documentUrl', 'existingFields'));
    }

    /**
     * Guarda los campos definidos para un documento.
     */
    public function saveFields(Request $request, Document $document)
{
    $request->validate([
        'fields' => 'present|array', // 'present' es mejor que 'required' para arrays que pueden estar vacíos
        'fields.*.type' => 'required|string|in:text,signature,date',
        'fields.*.name' => 'required|string|max:255',
        'fields.*.page' => 'required|integer|min:1',
        'fields.*.x' => 'required|numeric',
        'fields.*.y' => 'required|numeric',
        'fields.*.width' => 'required|numeric',
        'fields.*.height' => 'required|numeric',
        'fields.*.value' => 'nullable|string', // <-- AÑADIDO: Validación para el valor de la firma
    ]);

    // Usamos una transacción para asegurar que todo se guarde correctamente
    \DB::transaction(function () use ($request, $document) {
        // Borramos los campos antiguos para reemplazarlos con los nuevos
        $document->fields()->delete();

        // Creamos los nuevos campos en la base de datos
        foreach ($request->fields as $fieldData) {
            $document->fields()->create([
                'company_id' => $document->company_id,
                'type' => $fieldData['type'],
                'name' => $fieldData['name'],
                'value' => $fieldData['value'], // <-- AÑADIDO: Guardamos el valor
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
}

    /**
     * Genera un PDF de previsualización con los campos dibujados.
     */
    public function previewPdf(Document $document)
    {
        $filePath = Storage::disk('documents')->path($document->storage_path);

        // 1. Inicializamos FPDI, que usa TCPDF por debajo
        $pdf = new Fpdi();

        $pageCount = $pdf->setSourceFile($filePath);

        // 2. Recorremos cada página del documento original
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);

            // Añadimos una página al nuevo PDF con las mismas dimensiones que la original
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);

            // 3. Obtenemos los campos guardados para esta página específica
            $fieldsForPage = $document->fields()->where('coordinates->page', $pageNo)->get();

            foreach ($fieldsForPage as $field) {
                $coords = $field->coordinates;

                // 4. Conversión de coordenadas: De píxeles (frontend) a milímetros (PDF)
                // El frontend usa una escala de 1.5 y una resolución de 96 DPI.
                // La fórmula es: mm = (pixels / escala) * (25.4 / 96)
                $scale = 1.5;
                $px_to_mm = 0.264583; // 25.4 / 96
                
                $x_mm = ($coords['x'] / $scale) * $px_to_mm;
                $y_mm = ($coords['y'] / $scale) * $px_to_mm;
                $width_mm = ($coords['width'] / $scale) * $px_to_mm;
                $height_mm = ($coords['height'] / $scale) * $px_to_mm;

                // 5. Dibujamos el campo según su tipo
                switch ($field->type) {
                    case 'signature':
                        if (!empty($field->value)) {
                            // Decodificamos la imagen Base64 de la firma
                            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $field->value));
                            // Incrustamos la imagen directamente en el PDF en las coordenadas exactas
                            // El '@' le dice a TCPDF que los datos son una imagen en memoria
                            $pdf->Image('@'.$imageData, $x_mm, $y_mm, $width_mm, $height_mm, 'PNG');
                        }
                        break;
                    
                    case 'text':
                    case 'date':
                    default:
                        // Para campos de texto/fecha, dibujamos un recuadro con el nombre del campo
                        $pdf->SetFillColor(230, 245, 255); // Un azul muy claro de fondo
                        $pdf->SetDrawColor(150, 180, 220); // Un borde azulado
                        $pdf->SetFont('helvetica', '', 9);
                        $pdf->SetTextColor(80, 80, 80);
                        
                        // Posicionamos el cursor
                        $pdf->SetXY($x_mm, $y_mm);
                        
                        // Usamos MultiCell para un mejor control del texto y el alineamiento
                        $pdf->MultiCell($width_mm, $height_mm, $field->name, 1, 'C', true, 1, '', '', true, 0, false, true, 0, 'M');
                        break;
                }
            }
        }

        // 6. Generamos el PDF y forzamos su descarga en el navegador
        $fileName = 'prueba_' . Str::slug($document->original_filename) . '.pdf';
        return response($pdf->Output($fileName, 'D'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }
}

