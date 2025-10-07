<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registrar Nuevo Trabajador') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-7">
                {{-- La variable $company debe ser inyectada por el controlador --}}
                @php
                    // Simulación para la vista si $company no está definida (solo para previsualización)
                    $company = $company ?? (object)['id' => 10, 'name' => 'TechSolutions S.L.'];
                @endphp

                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-header bg-white pt-4 pb-3">
                        <h5 class="mb-0 text-dark fw-bold">
                            <i class="fas fa-user-plus me-2" style="color: #004A59;"></i>
                            {{-- Título Actualizado --}}
                            Creación de Cuenta y Perfil Laboral
                        </h5>
                        <small class="text-muted">
                            Asignando trabajador a la empresa: 
                            <span class="fw-bold" style="color: #004A59;">{{ $company->name }}</span>
                        </small>
                    </div>
                    <div class="card-body p-4">
                        {{-- Mostrar errores de validación si existen --}}
                        @if ($errors->any())
                            <div class="alert alert-danger rounded-3 shadow-sm">
                                <h6 class="alert-heading fw-bold"><i class="fas fa-exclamation-triangle me-2"></i> Errores de Validación:</h6>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        {{-- LLAMADA CORREGIDA A LA RUTA STORE: Usamos tecnico.workers.store y pasamos la empresa --}}
                        {{-- La ruta completa será: /tecnico/companies/{company}/workers --}}
                        <form action="{{ route('tecnico.workers.store', $company->id) }}" method="POST">
                            @csrf

                            {{-- Campo oculto para la Empresa (aunque ya va en la URL, es buena práctica) --}}
                            <input type="hidden" name="company_id" value="{{ $company->id }}">

                            <div class="row">
                                <div class="col-md-12 mb-4">
                                    <label for="name" class="form-label fw-bold">Nombre Completo<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror rounded-3" id="name" name="name" value="{{ old('name') }}" placeholder="Nombre y Apellidos del Trabajador" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-4">
                                    <label for="email" class="form-label fw-bold">Email (Credenciales de Acceso)<span class="text-danger">*</span></label>
                                    <input type="email" class="form-control form-control-lg @error('email') is-invalid @enderror rounded-3" id="email" name="email" value="{{ old('email') }}" placeholder="ejemplo@empresa.com" required>
                                    <div class="form-text">El email se usará para enviar las credenciales de acceso.</div>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr class="my-4">

                            <h6 class="fw-bold text-muted mb-3">Contraseña (Opcional)</h6>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="password" class="form-label fw-bold">Contraseña</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror rounded-3" id="password" name="password" placeholder="Mínimo 8 caracteres">
                                    <div class="form-text">Dejar vacío para que el sistema genere una temporal.</div>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="password_confirmation" class="form-label fw-bold">Confirmar Contraseña</label>
                                    <input type="password" class="form-control rounded-3" id="password_confirmation" name="password_confirmation" placeholder="Repetir la contraseña">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-5">
                                <a href="{{ route('tecnico.workers.index') }}" class="btn btn-outline-secondary rounded-pill shadow-sm py-2 px-4">
                                    <i class="fas fa-arrow-left me-2"></i>Volver al Listado
                                </a>
                                <button type="submit" class="btn text-white rounded-pill shadow-lg py-2 px-4" style="background-color: #004A59;">
                                    <i class="fas fa-save me-2"></i>Registrar Trabajador
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
