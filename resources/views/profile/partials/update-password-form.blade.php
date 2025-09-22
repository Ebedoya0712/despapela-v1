<section>
    <header>
        <h5 class="card-title">Actualizar Contraseña</h5>
        <p class="text-muted small mt-1">
            Asegúrate de que tu cuenta utiliza una contraseña larga y aleatoria para mantenerla segura.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-4">
        @csrf
        @method('put')

        <div class="mb-3">
            <label for="current_password" class="form-label">Contraseña Actual</label>
            <input id="current_password" name="current_password" type="password" class="form-control" autocomplete="current-password">
            @error('current_password', 'updatePassword')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Nueva Contraseña</label>
            <input id="password" name="password" type="password" class="form-control" autocomplete="new-password">
            @error('password', 'updatePassword')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password">
            @error('password_confirmation', 'updatePassword')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        <div class="d-flex align-items-center gap-4">
            <button type="submit" class="btn btn-primary">Guardar</button>

            @if (session('status') === 'password-updated')
                <span class="text-success small">Guardado.</span>
            @endif
        </div>
    </form>
</section>