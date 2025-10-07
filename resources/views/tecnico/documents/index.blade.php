<x-app-layout>
    <x-slot name="header">
        {{ __('Gestionar Documentos') }}
    </x-slot>

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
                            {{-- Nueva Columna para Etiqueta --}}
                            <th>Etiqueta</th> 
                            {{-- Fin Nueva Columna --}}
                            <th>Estado</th>
                            <th>Fecha de Subida</th>
                            <th>Caduca el</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($documents as $document)
                            <tr>
                                <td>{{ $document->id }}</td>
                                <td>{{ $document->original_filename ?? 'N/A' }}</td>
                                <td>{{ $document->company->name ?? 'N/A' }}</td>
                                
                                {{-- Implementación de la Etiqueta JSON --}}
                                <td>
                                    @if($document->etiquette && $document->etiquette['name'])
                                        @php
                                            // Asignar un color por defecto si no está definido o es inválido
                                            $etiquetteColor = $document->etiquette['color'] ?? '#6c757d'; // Gris por defecto
                                            $textColor = '#FFFFFF'; // Texto blanco por defecto
                                            
                                            // Lógica básica para mejorar la visibilidad del texto sobre el fondo oscuro
                                            if (in_array(strtolower($etiquetteColor), ['#ffffff', '#fff', '#ffff00', '#ff0'])) {
                                                $textColor = '#000000'; // Texto negro para fondos muy claros/amarillos
                                            }
                                        @endphp
                                        <span class="badge" 
                                              style="background-color: {{ $etiquetteColor }}; color: {{ $textColor }};">
                                            {{ $document->etiquette['name'] }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">Sin etiqueta</span>
                                    @endif
                                </td>
                                {{-- Fin Implementación de la Etiqueta JSON --}}

                                <td>
                                    @php
                                         // Nota: Asumiendo que 'status' existe o se puede calcular
                                        $documentStatus = $document->status ?? 'pendiente'; 
                                        $badgeClass = 'bg-secondary';
                                        if ($documentStatus === 'pendiente') {
                                            $badgeClass = 'bg-warning text-dark';
                                        } elseif ($documentStatus === 'firmado') {
                                            $badgeClass = 'bg-success';
                                        }
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ ucfirst($documentStatus) }}</span>
                                </td>
                                <td>{{ $document->created_at->format('d/m/Y') ?? 'N/A' }}</td>
                                <td>
                                    {{-- VERIFICACIÓN DE expires_at --}}
                                    @if($document->expires_at)
                                        @if ($document->expires_at->isPast())
                                            <span class="badge bg-danger">Caducado ({{ $document->expires_at->format('d/m/Y') }})</span>
                                        @else
                                            <span class="badge bg-info">{{ $document->expires_at->format('d/m/Y') }}</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">Sin fecha</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    <a href="{{ route('tecnico.documents.edit', $document->id) }}" class="btn btn-sm btn-outline-primary" title="Editar / Definir Campos">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <form action="{{ route('tecnico.documents.destroy', $document->id) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Aún no has subido ningún documento.</td>
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
            // Reajustar la configuración de DataTables a 8 columnas
            $('#documentsTable').DataTable({
                language: { 
                    url: 'https://cdn.datatables.net/plug-ins/1.12.1/i18n/es-ES.json' 
                },
                // CONFIGURACIÓN PARA PREVENIR ERRORES
                columnDefs: [
                    {
                        targets: '_all',
                        defaultContent: '-', // Valor por defecto para celdas vacías
                        render: function (data, type, row) {
                            // Manejar datos nulos o indefinidos
                            if (data === null || data === undefined || data === '') {
                                return '-';
                            }
                            // Si es la columna de etiqueta, permitimos que pase el HTML del badge
                            if (type === 'display') {
                                return data;
                            }
                            return data;
                        }
                    }
                ],
                responsive: true,
                autoWidth: false
            });

            // --- LÓGICA DE SWEETALERT ---

            // 1. Notificación Toast para mensajes de éxito
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

            // 2. Alerta de Confirmación antes de Eliminar
            $('.delete-form').on('submit', function(event) {
                event.preventDefault();
                const form = this;

                Swal.fire({
                    title: '¿Estás seguro de eliminar este documento?',
                    text: "¡No podrás revertir esta acción!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, ¡bórralo!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
