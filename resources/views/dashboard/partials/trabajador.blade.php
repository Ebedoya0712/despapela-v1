<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card text-white bg-primary mb-3 shadow text-center">
            <div class="card-body p-4">
                <i class="fas fa-file-signature fa-4x mb-3"></i>
                <h2 class="card-title">{{ $pendingToSign }}</h2>
                <p class="card-text fs-5">Documentos Pendientes de Firma</p>
                <a href="{{ route('signatures.index') }}" class="btn btn-lg btn-light mt-3">
                    Ir a Mis Documentos para Firmar
                </a>
            </div>
        </div>
    </div>
</div>