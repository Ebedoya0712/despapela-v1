<div class="row">
    <div class="col-md-6">
        <div class="card text-white bg-warning mb-3 shadow">
            <div class="card-body text-center">
                <i class="fas fa-paper-plane fa-3x mb-2"></i>
                <h5 class="card-title">{{ $pendingAssignment }}</h5>
                <p class="card-text">Documentos Pendientes de Asignar</p>
                <a href="{{ route('tecnico.assignment.list') }}" class="btn btn-sm btn-light">Ir a Asignar</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card text-white bg-danger mb-3 shadow">
            <div class="card-body text-center">
                <i class="fas fa-clock fa-3x mb-2"></i>
                <h5 class="card-title">{{ $pendingSignatures }}</h5>
                <p class="card-text">Documentos Esperando Firma</p>
            </div>
        </div>
    </div>
</div>