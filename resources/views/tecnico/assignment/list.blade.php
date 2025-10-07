<x-app-layout>
    <x-slot name="header">
        {{ __('Asignar Documentos a Trabajadores') }}
    </x-slot>

    {{-- Eliminamos la alerta de Bootstrap. Ahora la manejará SweetAlert2. --}}

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body">
            <h5 class="card-title mb-4">Selecciona un documento para asignar</h5>

            <!-- INICIO DEL CAMBIO: Botones de Filtro -->
            <div class="mb-4 d-flex justify-content-start">
                <div class="btn-group" role="group" aria-label="Filtro de Documentos">
                    {{-- El filtro 'all' muestra todos los documentos sin restricción de enlaces --}}
                    <a href="{{ route('tecnico.assignment.list', ['filter' => 'all']) }}" 
                       class="btn {{ $filter === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">
                        Todos
                    </a>
                    {{-- El filtro 'pending' muestra solo documentos sin ninguna asignación --}}
                    <a href="{{ route('tecnico.assignment.list', ['filter' => 'pending']) }}" 
                       class="btn {{ $filter === 'pending' ? 'btn-primary' : 'btn-outline-primary' }}">
                        Pendientes de Asignar
                    </a>
                    {{-- El filtro 'assigned' muestra solo documentos con al menos una asignación --}}
                    <a href="{{ route('tecnico.assignment.list', ['filter' => 'assigned']) }}" 
                       class="btn {{ $filter === 'assigned' ? 'btn-primary' : 'btn-outline-primary' }}">
                        Ya Asignados
                    </a>
                </div>
            </div>
            <!-- FIN DEL CAMBIO: Botones de Filtro -->

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
                            {{-- Mostrar un texto descriptivo basado en links_count --}}
                            <td>
                                @if ($document->links_count === 0)
                                    <span class="badge bg-danger">Sin asignar</span>
                                @else
                                    <span class="badge bg-success">{{ $document->links_count }} trabajador(es)</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('tecnico.assignment.showForm', $document->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-user-plus me-1"></i> Asignar / Reasignar
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No hay documentos que coincidan con el filtro seleccionado.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <!-- Asegúrate de tener cargados jQuery, DataTables JS/CSS y SweetAlert2 en tu layout principal -->
    <script>
        $(document).ready(function() {
            // Inicialización de DataTables
            $('#assignmentTable').DataTable({ 
                language: { url: 'https://cdn.datatables.net/plug-ins/1.12.1/i18n/es-ES.json' },
                // Asegurar que el orden inicial no interfiera con el filtro de la URL
                ordering: true, 
                paging: true
            });

            // --- Lógica de SweetAlert2 para mensajes de sesión ---
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
            @if (session('error'))
                // Manejo de errores (añadido para manejar el error del controller)
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: '{{ session('error') }}',
                    showConfirmButton: true,
                    timer: 8000,
                    timerProgressBar: true
                });
            @endif
        });
    </script>
    @endpush
</x-app-layout>
