<x-app-layout>
    <x-slot name="header">
        {{ __('Documentos Pendientes de Firma') }}
    </x-slot>

    {{-- Las notificaciones de éxito ahora se manejarán con el script de abajo --}}

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body">
            <h5 class="card-title mb-4">Mis Documentos Pendientes</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Documento</th>
                            <th>Empresa</th>
                            <th class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pendingLinks as $link)
                            <tr>
                                <td><i class="fas fa-file-alt text-primary me-2"></i> {{ $link->document->original_filename }}</td>
                                <td><span class="badge bg-secondary">{{ $link->document->company->name }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('signatures.show', $link->token) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-pencil-alt me-1"></i> Rellenar y Firmar
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">No tienes documentos pendientes de firma.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Notificación Toast para mostrar el mensaje de éxito
            @if (session('success'))
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
        </script>
    @endpush
</x-app-layout>

