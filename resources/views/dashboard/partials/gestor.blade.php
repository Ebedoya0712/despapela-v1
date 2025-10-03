<div class="row">
    <div class="col-md-4">
        <div class="card text-white bg-info mb-3 shadow">
            <div class="card-body text-center">
                <i class="fas fa-user-tie fa-3x mb-2"></i>
                <h5 class="card-title">{{ $techniciansCount }}</h5>
                <p class="card-text">Técnicos a tu Cargo</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-secondary mb-3 shadow">
            <div class="card-body text-center">
                <i class="fas fa-user-friends fa-3x mb-2"></i>
                <h5 class="card-title">{{ $workersCount }}</h5>
                <p class="card-text">Trabajadores en tus Empresas</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-primary mb-3 shadow">
            <div class="card-body text-center">
                <i class="fas fa-folder-open fa-3x mb-2"></i>
                <h5 class="card-title">{{ $documentsCount }}</h5>
                <p class="card-text">Documentos Totales</p>
            </div>
        </div>
    </div>
</div>