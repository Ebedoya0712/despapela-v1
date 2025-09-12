<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <span>Trabajadores de: <span class="fw-bold">{{ $company->name }}</span></span>
            <a href="{{ route('tecnico.companies.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver a Empresas
            </a>
        </div>
    </x-slot>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title mb-0">Listado de Trabajadores</h5>
                <a href="#" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Nuevo Trabajador
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="workersTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($workers as $worker)
                            <tr>
                                <td>{{ $worker->id }}</td>
                                <td>{{ $worker->name }}</td>
                                <td>{{ $worker->email }}</td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-outline-secondary" title="Editar"><i class="fas fa-edit"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No hay trabajadores asignados a esta empresa.</td>
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
            $('#workersTable').DataTable({
                language: { url: 'https://cdn.datatables.net/plug-ins/1.12.1/i18n/es-ES.json' }
            });
        });
    </script>
    @endpush
</x-app-layout>

