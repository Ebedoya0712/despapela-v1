<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <span>Gestionar Personal de: <span class="fw-bold">{{ $company->name }}</span></span>
            <a href="{{ route('gestor.companies.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver a Mis Empresas
            </a>
        </div>
    </x-slot>

    <div class="row">
        <!-- Columna de Técnicos -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Técnicos</h5>
                        <a href="{{ route('gestor.companies.staff.create', ['company' => $company->id, 'role' => 'Técnico']) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-plus"></i> Nuevo Técnico
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <tbody>
                                @forelse ($technicians as $technician)
                                    <tr>
                                        <td>{{ $technician->name }}</td>
                                        <td class="text-end">
                                            {{-- INICIO DEL CAMBIO --}}
                                            <a href="{{ route('gestor.companies.staff.edit', ['company' => $company->id, 'staff' => $technician->id]) }}" class="btn btn-sm btn-outline-secondary" title="Editar"><i class="fas fa-edit"></i></a>
                                            {{-- FIN DEL CAMBIO --}}
                                            <form action="{{ route('gestor.companies.staff.destroy', ['company' => $company->id, 'staff' => $technician->id]) }}" method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td class="text-muted">No hay técnicos en esta empresa.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna de Trabajadores -->
        <div class="col-lg-6 mb-4">
             <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Trabajadores</h5>
                        <a href="{{ route('gestor.companies.staff.create', ['company' => $company->id, 'role' => 'Trabajador']) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-plus"></i> Nuevo Trabajador
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <tbody>
                                @forelse ($workers as $worker)
                                    <tr>
                                        <td>{{ $worker->name }}</td>
                                        <td class="text-end">
                                            {{-- INICIO DEL CAMBIO --}}
                                            <a href="{{ route('gestor.companies.staff.edit', ['company' => $company->id, 'staff' => $worker->id]) }}" class="btn btn-sm btn-outline-secondary" title="Editar"><i class="fas fa-edit"></i></a>
                                            {{-- FIN DEL CAMBIO --}}
                                            <form action="{{ route('gestor.companies.staff.destroy', ['company' => $company->id, 'staff' => $worker->id]) }}" method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td class="text-muted">No hay trabajadores en esta empresa.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
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

        $('.delete-form').on('submit', function(event) {
            event.preventDefault();
            const form = this;
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¡El usuario será eliminado permanentemente!",
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
    </script>
    @endpush
</x-app-layout>

