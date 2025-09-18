<x-app-layout>
    <x-slot name="header">
        {{ __('Mis Documentos Firmados') }}
    </x-slot>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body">
            <h5 class="card-title mb-4">Historial de Firmas</h5>
            <div class="table-responsive">
                <table class="table table-hover" id="signedDocsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Documento</th>
                            <th>Empresa</th>
                            <th>Fecha de Firma</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($signatures as $signature)
                            <tr>
                                <td>{{ $signature->document->original_filename }}</td>
                                <td>{{ $signature->document->company->name }}</td>
                                <td>{{ \Carbon\Carbon::parse($signature->signed_at)->format('d/m/Y H:i') }}</td>
                                <td class="text-end text-nowrap">
                                    {{-- Este es el botón que llama a la ruta 'signed.view' --}}
                                    <a href="{{ route('signed.view', $signature->id) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Ver PDF Firmado">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>    
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Aún no has firmado ningún documento.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#signedDocsTable').DataTable({ 
                language: { url: 'https://cdn.datatables.net/plug-ins/1.12.1/i18n/es-ES.json' }
            });
            
            // Script para mostrar el enlace generado en una ventana emergente
            @if (session('success') && Illuminate\Support\Str::startsWith(session('success'), 'http'))
                Swal.fire({
                    title: '¡Enlace Generado!',
                    html: `El nuevo enlace para compartir el documento es:<br><input type="text" value="{{ session('success') }}" class="form-control mt-2" readonly onclick="this.select()">`,
                    icon: 'success',
                    confirmButtonText: 'Cerrar'
                });
            @elseif (session('success'))
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: '{{ session('success') }}',
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true
                });
            @endif
        });
    </script>
    @endpush
</x-app-layout>