<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <span>Editar {{ $staff->role->name }}: <span class="fw-bold">{{ $staff->name }}</span></span>
            <a href="{{ route('gestor.companies.staff.index', $company->id) }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver al Personal
            </a>
        </div>
    </x-slot>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('gestor.companies.staff.update', ['company' => $company->id, 'staff' => $staff->id]) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $staff->name) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $staff->email) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Nueva Contraseña (Opcional)</label>
                        <input type="password" class="form-control" id="password" name="password">
                        <div class="form-text">Deja este campo en blanco para no cambiar la contraseña.</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label">Confirmar Nueva Contraseña</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
                    <a href="{{ route('gestor.companies.staff.index', $company->id) }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
