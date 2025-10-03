<div class="row">
    <div class="col-md-4">
        <div class="card text-white bg-primary mb-3 shadow">
            <div class="card-body text-center">
                <i class="fas fa-users fa-3x mb-2"></i>
                <h5 class="card-title">{{ $totalUsers }}</h5>
                <p class="card-text">Usuarios Totales</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success mb-3 shadow">
            <div class="card-body text-center">
                <i class="fas fa-building fa-3x mb-2"></i>
                <h5 class="card-title">{{ $totalCompanies }}</h5>
                <p class="card-text">Empresas Registradas</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-info mb-3 shadow">
            <div class="card-body text-center">
                <i class="fas fa-file-alt fa-3x mb-2"></i>
                <h5 class="card-title">{{ $totalDocuments }}</h5>
                <p class="card-text">Documentos en el Sistema</p>
            </div>
        </div>
    </div>
</div>