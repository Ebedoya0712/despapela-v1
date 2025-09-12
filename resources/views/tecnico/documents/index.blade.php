<x-app-layout>
    <x-slot name="header">
        {{ __('Gestionar Documentos') }}
    </x-slot>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title mb-0">Mis Documentos</h5>
                <a href="{{ route('tecnico.documents.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-upload me-1"></i> Subir Nuevo Documento
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-striped" id="documentsTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nombre del Archivo</th>
                            <th>Empresa</th>
                            <th>Estado</th>
                            <th>Fecha de Subida</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($documents as $document)
                            <tr>
                                <td>{{ $document->id }}</td>
                                <td>{{ $document->original_filename }}</td>
                                <td>{{ $document->company->name }}</td>
                                <td><span class="badge bg-secondary">{{ ucfirst($document->status) }}</span></td>
                                <td>{{ $document->created_at->format('d/m/Y') }}</td>
                                <td class="text-nowrap">
                                    {{-- INICIO DEL CAMBIO --}}
                                    <a href="{{ route('tecnico.documents.defineFields', $document->id) }}" class="btn btn-sm btn-outline-primary" title="Definir Campos"><i class="fas fa-edit"></i></a>
                                    {{-- FIN DEL CAMBIO --}}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Aún no has subido ningún documento.</td>
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
            $('#documentsTable').DataTable({
                language: { url: 'https://cdn.datatables.net/plug-ins/1.12.1/i18n/es-ES.json' }
            });
        });
    </script>
    @endpush
</x-app-layout>

