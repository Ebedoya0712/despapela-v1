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
                    {{-- Selector de Empresa --}}
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

                    {{-- Selector de Archivo --}}
                    <div class="col-md-4 mb-3">
                        <label for="document_file" class="form-label">Archivo PDF</label>
                        <input class="form-control @error('document_file') is-invalid @enderror" type="file" id="document_file" name="document_file" required accept=".pdf">
                        @error('document_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="expiration_period" class="form-label">Caducidad (desde la firma)</label>
                        <select class="form-select @error('expiration_period') is-invalid @enderror" id="expiration_period" name="expiration_period" required>
                            <option value="1_month" selected>1 Mes</option>
                            <option value="2_months">2 Meses</option>
                            <option value="6_months">6 Meses</option>
                            <option value="1_year">1 Año</option>
                            <option value="2_years">2 Años</option>
                        </select>
                        @error('expiration_period')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary" {{ $companies->isEmpty() ? 'disabled' : '' }}>Subir Documento</button>
                    <a href="{{ route('tecnico.documents.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>