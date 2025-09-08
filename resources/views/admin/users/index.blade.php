<x-app-layout>
    <x-slot name="header">
        {{ __('Gestionar Usuarios de la Plataforma') }}
    </x-slot>

    {{-- Eliminamos las alertas de Bootstrap de aquí. Ahora las manejará SweetAlert2. --}}

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title mb-0">Todos los Usuarios</h5>
                <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Nuevo Usuario
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-striped" id="usersTable">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Nombre</th>
                            <th scope="col">Email</th>
                            <th scope="col">Rol</th>
                            <th scope="col">Fecha de Registro</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <th scope="row">{{ $user->id }}</th>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if ($user->role)
                                        @if ($user->role->name === 'Administrador')
                                            <span class="badge bg-primary">{{ $user->role->name }}</span>
                                        @elseif ($user->role->name === 'Gestor')
                                            <span class="badge bg-success">{{ $user->role->name }}</span>
                                        @elseif ($user->role->name === 'Técnico')
                                            <span class="badge bg-info text-dark">{{ $user->role->name }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $user->role->name }}</span>
                                        @endif
                                    @else
                                        <span class="badge bg-danger">Sin Rol</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                <td class="text-nowrap">
                                    @if ($user->role?->name === 'Gestor')
                                        <a href="{{ route('admin.users.assignCompanyForm', $user->id) }}" class="btn btn-sm btn-outline-info" title="Asignar Empresa"><i class="fas fa-building"></i></a>
                                    @endif

                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-outline-secondary" title="Editar Usuario"><i class="fas fa-edit"></i></a>
                                    
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar Usuario"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No se encontraron usuarios.</td>
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
            // Inicialización de DataTables (sin cambios)
            $('#usersTable').DataTable({
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

            // --- INICIO DE LA LÓGICA DE SWEETALERT ---

            // 1. Notificación Toast para mensajes de éxito/error
            @if (session('success'))
                Swal.fire({
                    toast: true,
                    position: 'top-end', // Aparece en la esquina superior derecha
                    icon: 'success',
                    title: '{{ session('success') }}',
                    showConfirmButton: false,
                    timer: 5000, // 5 segundos
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
                    text: "¡No podrás revertir esta acción!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
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

