<x-app-layout>

    {{-- CSS Personalizado para Sobrescribir el color Primario de Bootstrap --}}
    <style>
        :root {
            /* Color Primario: Azul Petróleo Oscuro del logo */
            --bs-primary: #005963; 
            /* Color de Acento: Verde Lima Brillante del logo */
            --despapela-accent: #8AC53F; 
            --bs-primary-rgb: 0, 89, 99;
        }

        /* Estilo para los inputs con foco y hover para que usen el color primario */
        .form-control:focus, .form-select:focus {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 0.25rem rgba(0, 89, 99, 0.25); /* Sombra suave del color primario */
        }
        
        /* Aseguramos que los íconos usen el color de acento o el primario */
        .text-accent-despapela {
            color: var(--despapela-accent) !important;
        }
    </style>

    {{-- Encabezado de la página: Diseño limpio con borde inferior --}}
    <x-slot name="header">
        <div class="py-4 border-bottom border-light">
            <h2 class="fw-bolder fs-4 text-dark mb-0">
                {{-- Ícono con el color de acento (Verde Lima) --}}
                <i class="fas fa-edit me-3 text-accent-despapela"></i>
                {{ __('Editar Trabajador') }}: 
                <span class="text-primary">{{ $worker->name }}</span>
            </h2>
        </div>
    </x-slot>

    {{-- Contenido principal --}}
    <div class="py-5">
        <div class="container my-4">
            
            {{-- Botón de retorno: Botón con estilo "ghost" y borde marcado (Color Primario) --}}
            <div class="d-flex justify-content-start mb-4">
                <a href="{{ route('tecnico.workers.index', $company->id ?? null) }}" 
                    class="btn btn-outline-primary btn-sm fw-semibold d-inline-flex align-items-center rounded-pill px-3 py-2">
                    <i class="fas fa-arrow-left me-2"></i> Volver al Listado
                </a>
            </div>

            {{-- Contenedor del formulario: Tarjeta limpia, esquinas bien redondeadas y sombra sutil --}}
            <div class="card shadow-lg border-0 p-4 p-md-5 rounded-4 bg-white">
                
                {{-- Formulario de Edición --}}
                <form action="{{ route('tecnico.workers.update', ['company' => $company->id, 'worker' => $worker->id]) }}" method="POST">
                    @csrf
                    @method('PUT') 

                    {{-- Título de Sección: Texto fuerte con línea de acento del color primario (Azul Oscuro) --}}
                    <h3 class="h4 fw-bolder text-dark mb-4 pb-2 border-bottom border-primary border-3">
                        Datos del Perfil Laboral
                    </h3>

                    {{-- Errores de validación: Se mantiene el estilo de alerta de peligro estándar --}}
                    @if ($errors->any())
                        <div class="alert alert-danger border-start border-4 border-danger rounded-3 p-3 mb-4 shadow-sm" role="alert">
                            <h5 class="alert-heading fw-bold mb-1">¡Revisa estos errores!</h5>
                            <ul class="mt-2 ms-3 small">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif


                    <div class="row g-4 mb-4">
                        
                        {{-- Campo Nombre Completo --}}
                        <div class="col-12">
                            <label for="name" class="form-label fw-semibold text-secondary-emphasis">
                                {{-- Ícono con el color de acento (Verde Lima) --}}
                                <i class="fas fa-signature me-1 text-accent-despapela"></i>Nombre Completo
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name', $worker->name) }}" required
                                class="form-control form-control-lg rounded-3 @error('name') is-invalid @enderror"
                                placeholder="Ej: Juan Pérez Gómez">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Campo Email (Identificación Única) --}}
                        <div class="col-12 col-md-6">
                            <label for="email" class="form-label fw-semibold text-secondary-emphasis">
                                <i class="fas fa-envelope me-1 text-accent-despapela"></i>Correo Electrónico
                            </label>
                            <input type="email" name="email" id="email" value="{{ old('email', $worker->email) }}" required
                                class="form-control form-control-lg rounded-3 @error('email') is-invalid @enderror"
                                placeholder="Ej: trabajador@empresa.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- Campo Rol --}}
                        <div class="col-12 col-md-6">
                            <label for="role_id" class="form-label fw-semibold text-secondary-emphasis">
                                <i class="fas fa-user-tag me-1 text-accent-despapela"></i>Rol
                            </label>
                            <select name="role_id" id="role_id" required
                                class="form-select form-select-lg rounded-3 @error('role_id') is-invalid @enderror">
                                <option value="">Seleccione un Rol</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" 
                                        {{ old('role_id', $worker->role_id) == $role->id ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Separador --}}
                    <hr class="my-5 border-light-subtle">

                    {{-- Campo Empresa (Relación N:M) --}}
                    <div class="mb-4">
                        <label for="company_id" class="form-label fw-semibold text-secondary-emphasis">
                            <i class="fas fa-building me-1 text-accent-despapela"></i>Empresa Asignada
                        </label>
                        
                        @php
                            // Lógica Blade para obtener el ID de la empresa actual
                            $currentCompanyId = $worker->memberOfCompanies->first() ? $worker->memberOfCompanies->first()->id : null;
                        @endphp
                        <select name="company_id" id="company_id" required
                                class="form-select form-select-lg rounded-3 @error('company_id') is-invalid @enderror">
                            <option value="">Seleccione la Empresa</option>
                            @foreach($companies as $comp)
                                <option value="{{ $comp->id }}" 
                                    {{ old('company_id', $currentCompanyId) == $comp->id ? 'selected' : '' }}>
                                    {{ $comp->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('company_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Botón de Guardar: Botón principal de alto impacto con el color Primario (Azul Oscuro) --}}
                    <div class="d-flex justify-content-end pt-3">
                        <button type="submit"
                                class="btn btn-primary btn-lg fw-bold shadow-sm rounded-3 px-5 py-3 text-uppercase">
                            <i class="fas fa-save me-2"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
