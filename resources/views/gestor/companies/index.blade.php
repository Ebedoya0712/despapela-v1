<x-app-layout>
    <x-slot name="header">
        {{ __('Gestionar Mis Empresas') }}
    </x-slot>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body">
            <h5 class="card-title mb-4">Mis Empresas</h5>

            <div class="table-responsive">
                <table class="table table-hover table-striped" id="gestorCompaniesTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nombre de la Empresa</th>
                            <th>Nº de Técnicos</th>
                            <th>Nº de Trabajadores</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($companies as $company)
                            <tr>
                                <td>{{ $company->id }}</td>
                                <td>{{ $company->name }}</td>
                                {{-- Lógica para contar el personal usando las relaciones del modelo --}}
                                <td>{{ $company->staff()->whereHas('role', fn($q) => $q->where('name', 'Técnico'))->count() }}</td>
                                <td>{{ $company->staff()->whereHas('role', fn($q) => $q->where('name', 'Trabajador'))->count() }}</td>
                                <td class="text-nowrap">
                                    {{-- El botón ahora apunta a la ruta correcta para gestionar el personal de esta empresa --}}
                                    <a href="{{ route('gestor.companies.staff.index', $company->id) }}" class="btn btn-sm btn-primary" title="Gestionar Personal">
                                        <i class="fas fa-users-cog me-1"></i> Gestionar Personal
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Aún no tienes ninguna empresa asignada.</td>
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
            $('#gestorCompaniesTable').DataTable({
                language: { url: 'https://cdn.datatables.net/plug-ins/1.12.1/i18n/es-ES.json' }
            });
        });
    </script>
    @endpush
</x-app-layout>

