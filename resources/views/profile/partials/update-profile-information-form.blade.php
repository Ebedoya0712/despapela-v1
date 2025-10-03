<section>
    <header>
        <h5 class="card-title">Información del Perfil</h5>
        <p class="text-muted small mt-1">
            Actualiza la información de perfil y la dirección de correo electrónico de tu cuenta.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-4">
        @csrf
        @method('patch')

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="name" class="form-label">Nombre</label>
                <input id="name" name="name" type="text" class="form-control" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
                @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email</label>
                <input id="email" name="email" type="email" class="form-control" value="{{ old('email', $user->email) }}" required autocomplete="username">
                @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="small mt-2">
                        <p class="text-warning">
                            Tu dirección de correo no está verificada.
                            <button form="send-verification" class="btn btn-link p-0 text-decoration-underline">
                                Clica aquí para reenviar el email de verificación.
                            </button>
                        </p>
                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 text-success">
                                Se ha enviado un nuevo enlace de verificación a tu correo.
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <div class="col-md-6 mb-3">
                <label for="dni" class="form-label">DNI</label>
                <input id="dni" name="dni" type="text" class="form-control" value="{{ old('dni', $user->dni) }}">
                @error('dni')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6 mb-3">
                <label for="phone" class="form-label">Teléfono</label>
                <input id="phone" name="phone" type="tel" class="form-control" value="{{ old('phone', $user->phone) }}">
                @error('phone')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

             <div class="col-12 mb-3">
                <label for="bank_account" class="form-label">Nº de Cuenta Bancaria</label>
                <input id="bank_account" name="bank_account" type="text" class="form-control" value="{{ old('bank_account', $user->bank_account) }}">
                @error('bank_account')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            
            <div class="col-12 mb-3">
                <label for="address" class="form-label">Dirección</label>
                <textarea id="address" name="address" class="form-control" rows="3">{{ old('address', $user->address) }}</textarea>
                @error('address')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="d-flex align-items-center gap-4">
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>

            @if (session('status') === 'profile-updated')
                <span class="text-success small">Guardado.</span>
            @endif
        </div>
    </form>
</section>