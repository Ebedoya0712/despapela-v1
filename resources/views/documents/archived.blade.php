<x-app-layout>
    <x-slot name="header">
        {{ __('Documentos Archivados') }}
    </x-slot>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body">
            <h5 class="card-title mb-4">Historial de Documentos Archivados</h5>
            <p class="text-muted small">Estos documentos han cumplido su ciclo de vida y ya no están activos.</p>
            <div class="table-responsive mt-4">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Documento</th>
                            <th>Empresa</th>
                            <th>Fecha de Caducidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($archivedDocuments as $document)
                            <tr>
                                <td><i class="fas fa-archive text-secondary me-2"></i> {{ $document->original_filename }}</td>
                                <td><span class="badge bg-secondary">{{ $document->company->name }}</span></td>
                                <td>{{ $document->expires_at->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">No tienes documentos archivados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>