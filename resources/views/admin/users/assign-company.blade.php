<x-app-layout>
    <x-slot name="header">
        {{ __('Asignar Empresas a Gestor') }}
    </x-slot>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body">
            <h5 class="card-title mb-4">Asignando empresas a: <span class="fw-bold">{{ $user->name }}</span></h5>

            <form action="{{ route('admin.users.syncCompanies', $user->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Selecciona las empresas que este gestor administrará:</label>
                    
                    @forelse ($companies as $company)
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="companies[]" 
                                   value="{{ $company->id }}" 
                                   id="company_{{ $company->id }}"
                                   {{ in_array($company->id, $managedCompanyIds) ? 'checked' : '' }}
                                   {{-- Deshabilitar si está asignada a OTRO gestor --}}
                                   {{ ($company->gestor_id !== null && $company->gestor_id !== $user->id) ? 'disabled' : '' }}>

                            <label class="form-check-label" for="company_{{ $company->id }}">
                                {{ $company->name }}
                                {{-- Mostrar a quién está asignada si no es a este gestor --}}
                                @if ($company->gestor_id !== null && $company->gestor_id !== $user->id)
                                    <span class="text-muted small">(Asignada a: {{ $company->gestor->name }})</span>
                                @endif
                            </label>
                        </div>
                    @empty
                        <p class="text-muted">No hay empresas creadas en el sistema. Por favor, crea una empresa primero.</p>
                    @endforelse
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary" {{ $companies->isEmpty() ? 'disabled' : '' }}>Guardar Cambios</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

