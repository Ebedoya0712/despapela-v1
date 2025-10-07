<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <span>Subir Nuevo Documento</span>
            <a href="{{ route('tecnico.documents.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver a Documentos
            </a>
        </div>
    </x-slot>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body">
            <form action="{{ route('tecnico.documents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    {{-- 1. Selector de Empresa --}}
                    <div class="col-md-4 mb-3">
                        <label for="company_id" class="form-label">Empresa</label>
                        <select class="form-select @error('company_id') is-invalid @enderror" id="company_id" name="company_id" required>
                            <option value="" disabled selected>Selecciona...</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('company_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 2. Selector de Archivo --}}
                    <div class="col-md-4 mb-3">
                        <label for="document_file" class="form-label">Archivo PDF</label>
                        <input class="form-control @error('document_file') is-invalid @enderror" type="file" id="document_file" name="document_file" required accept=".pdf">
                        @error('document_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 3. Selector de Caducidad --}}
                    <div class="col-md-4 mb-3">
                        <label for="expiration_period" class="form-label">Caducidad (desde la firma)</label>
                        <select class="form-select @error('expiration_period') is-invalid @enderror" id="expiration_period" name="expiration_period" required>
                            <option value="1_month" {{ old('expiration_period', '1_month') == '1_month' ? 'selected' : '' }}>1 Mes</option>
                            <option value="2_months" {{ old('expiration_period') == '2_months' ? 'selected' : '' }}>2 Meses</option>
                            <option value="6_months" {{ old('expiration_period') == '6_months' ? 'selected' : '' }}>6 Meses</option>
                            <option value="1_year" {{ old('expiration_period') == '1_year' ? 'selected' : '' }}>1 Año</option>
                            <option value="2_years" {{ old('expiration_period') == '2_years' ? 'selected' : '' }}>2 Años</option>
                        </select>
                        @error('expiration_period')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr class="my-4">
                
                <h5 class="mb-3">Etiquetado (Opcional)</h5>
                <p class="text-muted small">Define una etiqueta personalizada para identificar este documento fácilmente en el listado.</p>

                <div class="row">
                    {{-- 4. Nombre de Etiqueta --}}
                    <div class="col-md-6 mb-3">
                        <label for="etiquette_name" class="form-label">Nombre de Etiqueta</label>
                        <input type="text" class="form-control @error('etiquette_name') is-invalid @enderror" id="etiquette_name" name="etiquette_name" value="{{ old('etiquette_name') }}" placeholder="Ej: Contrato Base, Ficha Médica, PRL">
                        @error('etiquette_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 5. Color de Etiqueta --}}
                    <div class="col-md-6 mb-3">
                        <label for="etiquette_color" class="form-label">Color de Etiqueta</label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color @error('etiquette_color') is-invalid @enderror" id="etiquette_color" name="etiquette_color" value="{{ old('etiquette_color', '#007bff') }}">
                            <input type="text" class="form-control @error('etiquette_color') is-invalid @enderror" id="etiquette_color_text" name="etiquette_color_text" value="{{ old('etiquette_color', '#007bff') }}" maxlength="7" style="max-width: 120px;" disabled>
                        </div>
                        <small class="text-muted">El código hexadecimal del color.</small>
                        @error('etiquette_color')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary" {{ $companies->isEmpty() ? 'disabled' : '' }}>Subir Documento</button>
                    <a href="{{ route('tecnico.documents.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const colorInput = document.getElementById('etiquette_color');
            const colorTextInput = document.getElementById('etiquette_color_text');

            // Sincronizar el input de texto con el selector de color
            colorInput.addEventListener('input', function() {
                colorTextInput.value = colorInput.value;
            });

            // En caso de que se haya validado mal y tengamos un valor previo
            colorTextInput.value = colorInput.value;
        });
    </script>
</x-app-layout>
