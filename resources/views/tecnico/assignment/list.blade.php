<x-app-layout>
    <x-slot name="header">
        {{ __('Asignar Documentos a Trabajadores') }}
    </x-slot>

    {{-- Eliminamos la alerta de Bootstrap. Ahora la manejará SweetAlert2. --}}

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body">
            <h5 class="card-title mb-4">Selecciona un documento para asignar</h5>
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="assignmentTable">
                    <thead class="table-light">
                        <tr>
                            <th>Documento</th>
                            <th>Empresa</th>
                            <th>Asignado a</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($documents as $document)
                        <tr>
                            <td>{{ $document->original_filename }}</td>
                            <td>{{ $document->company->name }}</td>
                            <td>{{ $document->links_count }} trabajador(es)</td>
                            <td>
                                <a href="{{ route('tecnico.assignment.showForm', $document->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-user-plus me-1"></i> Asignar
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No hay documentos listos para asignar.</td>
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
            $('#assignmentTable').DataTable({ 
                language: { url: 'https://cdn.datatables.net/plug-ins/1.12.1/i18n/es-ES.json' } 
            });

            // --- INICIO DEL CAMBIO: Lógica de SweetAlert2 ---
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
            // --- FIN DEL CAMBIO ---
        });
    </script>
    @endpush
</x-app-layout>