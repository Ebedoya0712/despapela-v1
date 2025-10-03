<x-app-layout>
    <x-slot name="header">
        {{ __('Mis Empresas Asignadas') }}
    </x-slot>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body">
            <h5 class="card-title mb-4">Empresas que puedo gestionar</h5>
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="tecnicoCompaniesTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nombre de la Empresa</th>
                            <th>Gestor Principal</th>
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
                                        {{ $company->gestor->name }}
                                    @else
                                        <span class="text-muted">No asignado</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    {{-- INICIO DEL CAMBIO --}}
                                    <a href="{{ route('tecnico.companies.show', $company->id) }}" class="btn btn-sm btn-primary" title="Gestionar Trabajadores">
                                        <i class="fas fa-users me-1"></i> Ver Trabajadores
                                    </a>
                                    {{-- FIN DEL CAMBIO --}}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center">No tienes ninguna empresa asignada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#tecnicoCompaniesTable').DataTable({
                language: { url: 'https://cdn.datatables.net/plug-ins/1.12.1/i18n/es-ES.json' }
            });
        });
    </script>
    @endpush
</x-app-layout>
