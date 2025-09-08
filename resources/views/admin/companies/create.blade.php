<x-app-layout>
    <x-slot name="header">
        {{ __('Crear Nueva Empresa') }}
    </x-slot>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body">
            <form action="{{ route('admin.companies.store') }}" method="POST">
                @csrf
                <div class="row">
                    {{-- Campo para el Nombre de la Empresa --}}
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Nombre de la Empresa</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                            name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Campo para Asignar un Gestor --}}
                    <div class="col-md-6 mb-3">
                        <label for="gestor_id" class="form-label">Asignar Gestor Principal</label>
                        <select class="form-select @error('gestor_id') is-invalid @enderror" id="gestor_id"
                            name="gestor_id" required>
                            <option value="" disabled selected>Selecciona un gestor...</option>
                            @forelse ($gestores as $gestor)
                                <option value="{{ $gestor->id }}" {{ old('gestor_id') == $gestor->id ? 'selected' : '' }}>
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
                    <button type="submit" class="btn btn-primary">Crear Empresa</button>
                    <a href="{{ route('admin.companies.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>