<x-app-layout>
    <x-slot name="header">
        {{ __('Gestionar Empresas') }}
    </x-slot>

    {{-- Eliminamos las alertas de Bootstrap. Ahora las manejará SweetAlert2. --}}

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title mb-0">Todas las Empresas</h5>
                <a href="{{ route('admin.companies.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Nueva Empresa
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-striped" id="companiesTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nombre de la Empresa</th>
                            <th>Gestor Asignado</th>
                            <th>Fecha de Creación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($companies as $company)
                            <tr>
                                <td>{{ $company->id }}</td>
                                <td>{{ $company->name }}</td>
                                <td>
                                    @if ($company->gestor)
                                        <span class="badge bg-secondary">{{ $company->gestor->name }}</span>
                                    @else
                                        <span class="badge bg-warning text-dark">No asignado</span>
                                    @endif
                                </td>
                                <td>{{ $company->created_at->format('d/m/Y') }}</td>
                                <td class="text-nowrap">
                                    <a href="{{ route('admin.companies.edit', $company->id) }}" class="btn btn-sm btn-outline-secondary" title="Editar"><i class="fas fa-edit"></i></a>
                                    
                                    <form action="{{ route('admin.companies.destroy', $company->id) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No hay empresas creadas.</td>
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
            // Inicialización de DataTables
            $('#companiesTable').DataTable({
                dom: '<"row mb-3"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                     '<"row mb-2"<"col-sm-12"B>>' +
                     '<"row"<"col-sm-12"r>>' +
                     '<"row"<"col-sm-12"t>>' +
                     '<"row mt-3"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                buttons: [
                    { extend: 'copy', text: '<i class="fas fa-copy"></i> Copiar', className: 'btn-sm' },
                    { extend: 'excel', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn-sm' },
                    { extend: 'pdf', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn-sm' },
                    { extend: 'print', text: '<i class="fas fa-print"></i> Imprimir', className: 'btn-sm' }
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.12.1/i18n/es-ES.json'
                }
            });

            // --- LÓGICA DE SWEETALERT ---

            // 1. Notificación Toast para mensajes de éxito/error
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
                    title: '¿Estás seguro?',
                    text: "Se eliminará la empresa y todas sus relaciones. ¡No podrás revertir esto!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, ¡bórrala!',
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

