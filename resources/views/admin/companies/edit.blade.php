<x-app-layout>
    <x-slot name="header">
        {{ __('Editar Empresa') }}
    </x-slot>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body">
            <form action="{{ route('admin.companies.update', $company->id) }}" method="POST">
                @csrf
                @method('PUT') {{-- Directiva para indicar que es una petición de actualización --}}
                
                <div class="row">
                    {{-- Campo para el Nombre de la Empresa --}}
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Nombre de la Empresa</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $company->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Campo para Asignar un Gestor --}}
                    <div class="col-md-6 mb-3">
                        <label for="gestor_id" class="form-label">Asignar Gestor Principal</label>
                        <select class="form-select @error('gestor_id') is-invalid @enderror" id="gestor_id" name="gestor_id" required>
                            <option value="" disabled>Selecciona un gestor...</option>
                            @forelse ($gestores as $gestor)
                                <option value="{{ $gestor->id }}" 
                                    {{-- Lógica para pre-seleccionar el gestor actual --}}
                                    {{ old('gestor_id', $company->gestor_id) == $gestor->id ? 'selected' : '' }}>
                                    {{ $gestor->name }}: {{ $gestor->email }}
                                </option>
                            @empty
                                 <option value="" disabled>No hay usuarios con el rol de 'Gestor' disponibles</option>
                            @endforelse
                        </select>
                        @error('gestor_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Botones de Acción --}}
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Actualizar Empresa</button>
                    <a href="{{ route('admin.companies.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

