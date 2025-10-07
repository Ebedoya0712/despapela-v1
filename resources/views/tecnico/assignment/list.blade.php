<x-app-layout>
    <x-slot name="header">
        {{ __('Asignar Documentos a Trabajadores') }}
    </x-slot>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body">
            <h5 class="card-title mb-4">Selecciona un documento para asignar</h5>

            <!-- Botones de Filtro -->
            <div class="mb-4 d-flex justify-content-start">
                <div class="btn-group" role="group" aria-label="Filtro de Documentos">
                    <a href="{{ route('tecnico.assignment.list', ['filter' => 'all']) }}" 
                       class="btn {{ $filter === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">
                        Todos
                    </a>
                    <a href="{{ route('tecnico.assignment.list', ['filter' => 'pending']) }}" 
                       class="btn {{ $filter === 'pending' ? 'btn-primary' : 'btn-outline-primary' }}">
                        Pendientes de Asignar
                    </a>
                    <a href="{{ route('tecnico.assignment.list', ['filter' => 'assigned']) }}" 
                       class="btn {{ $filter === 'assigned' ? 'btn-primary' : 'btn-outline-primary' }}">
                        Ya Asignados
                    </a>
                </div>
            </div>
            <!-- Fin de Botones de Filtro -->

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
                        {{-- SOLUCIÓN: Crear una fila completa con 4 celdas para DataTables --}}
                        <tr>
                            <td class="text-center text-muted" colspan="4">
                                No hay documentos que coincidan con el filtro seleccionado.
                            </td>
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
            // Inicialización de DataTables con configuración mejorada
            $('#assignmentTable').DataTable({ 
                language: { 
                    url: 'https://cdn.datatables.net/plug-ins/1.12.1/i18n/es-ES.json',
                    emptyTable: "No hay documentos que coincidan con el filtro seleccionado." // Mensaje por defecto
                },
                ordering: true, 
                paging: true,
                responsive: true,
                // Configuración específica para columnas
                columnDefs: [
                    { 
                        targets: [2, 3], // Columnas 'Asignado a' y 'Acciones'
                        orderable: false,
                        searchable: false
                    },
                    { 
                        targets: '_all', // Todas las columnas
                        defaultContent: '-' // Valor por defecto si hay celdas vacías
                    }
                ],
                // Ordenar por la primera columna (Documento) por defecto
                order: [[0, 'asc']]
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
