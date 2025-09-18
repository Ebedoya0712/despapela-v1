<x-mail::message>
# Hola,

El técnico **{{ $technician->name }}** te ha asignado un nuevo documento para firmar:

**Documento:** {{ $document->original_filename }}

Puedes acceder a tu bandeja de entrada de firmas para revisarlo y firmarlo.

{{-- Botón Personalizado con HTML y estilos en línea --}}
<div style="text-align: center; margin: 30px auto; padding: 0;">
    <a href="{{ route('signatures.index') }}" 
       style="
         display: inline-block;
         padding: 12px 24px;
         font-size: 16px;
         font-weight: bold;
         color: #ffffff;
         background-color: #005963;
         border-radius: 6px;
         text-decoration: none;
         font-family: sans-serif;
       ">
        Ver mis documentos pendientes
    </a>
</div>

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>