<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro - Despapela</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { background-color: #f0f2f5; }
        .card-header-despapela {
            background-color: #006A80;
            color: white;
            padding: 2rem;
            border-top-left-radius: 1rem;
            border-top-right-radius: 1rem;
        }
        .btn-despapela-green {
            background-color: #9BCF35;
            border-color: #9BCF35;
            color: white;
        }
        .btn-despapela-green:hover {
            background-color: #8ab82e;
            border-color: #8ab82e;
            color: white;
        }
        .link-despapela-blue { color: #006A80; text-decoration: none; }
        .link-despapela-blue:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <section class="vh-100 d-flex justify-content-center align-items-center py-5">
        <div class="col-12 col-md-8 col-lg-6 col-xl-5" style="max-width: 600px;">
            <div class="card shadow-lg border-0" style="border-radius: 1rem;">
                
                <div class="card-header card-header-despapela text-center">
                    <img src="{{ asset('images/logo_despapela.png') }}" alt="Logo Despapela" style="max-height: 60px;" class="mb-3">
                    <h3 class="fw-bold mb-0">Crea tu Cuenta</h3>
                </div>

                <div class="card-body p-5">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user fa-fw"></i></span>
                                    <div class="form-floating flex-grow-1">
                                        <input type="text" id="name" class="form-control" name="name" value="{{ old('name') }}" placeholder="Nombre y Apellidos" required autofocus autocomplete="name" />
                                        <label for="name">Nombre y Apellidos</label>
                                    </div>
                                </div>
                                <x-input-error :messages="$errors->get('name')" class="mt-1 text-danger small text-start" />
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-id-card fa-fw"></i></span>
                                    <div class="form-floating flex-grow-1">
                                        <input type="text" id="dni" class="form-control" name="dni" value="{{ old('dni') }}" placeholder="DNI" required />
                                        <label for="dni">DNI</label>
                                    </div>
                                </div>
                                <x-input-error :messages="$errors->get('dni')" class="mt-1 text-danger small text-start" />
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope fa-fw"></i></span>
                                    <div class="form-floating flex-grow-1">
                                        <input type="email" id="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="Email" required autocomplete="username" />
                                        <label for="email">Email</label>
                                    </div>
                                </div>
                                <x-input-error :messages="$errors->get('email')" class="mt-1 text-danger small text-start" />
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone fa-fw"></i></span>
                                    <div class="form-floating flex-grow-1">
                                        <input type="tel" id="phone" class="form-control" name="phone" value="{{ old('phone') }}" placeholder="Teléfono" required />
                                        <label for="phone">Teléfono</label>
                                    </div>
                                </div>
                                <x-input-error :messages="$errors->get('phone')" class="mt-1 text-danger small text-start" />
                            </div>
                            
                            <div class="col-12 mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-credit-card fa-fw"></i></span>
                                    <div class="form-floating flex-grow-1">
                                        <input type="text" id="bank_account" class="form-control" name="bank_account" value="{{ old('bank_account') }}" placeholder="Nº de Cuenta Bancaria" required />
                                        <label for="bank_account">Nº de Cuenta Bancaria</label>
                                    </div>
                                </div>
                                <x-input-error :messages="$errors->get('bank_account')" class="mt-1 text-danger small text-start" />
                            </div>
                            
                            <div class="col-12 mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-map-marker-alt fa-fw"></i></span>
                                    <div class="form-floating flex-grow-1">
                                        <textarea id="address" class="form-control" name="address" placeholder="Dirección" required style="height: 100px">{{ old('address') }}</textarea>
                                        <label for="address">Dirección</label>
                                    </div>
                                </div>
                                <x-input-error :messages="$errors->get('address')" class="mt-1 text-danger small text-start" />
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock fa-fw"></i></span>
                                    <div class="form-floating flex-grow-1">
                                        <input type="password" id="password" class="form-control" name="password" placeholder="Contraseña" required autocomplete="new-password" />
                                        <label for="password">Contraseña</label>
                                    </div>
                                </div>
                                <x-input-error :messages="$errors->get('password')" class="mt-1 text-danger small text-start" />
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock fa-fw"></i></span>
                                    <div class="form-floating flex-grow-1">
                                        <input type="password" id="password_confirmation" class="form-control" name="password_confirmation" placeholder="Confirmar Contraseña" required autocomplete="new-password" />
                                        <label for="password_confirmation">Confirmar Contraseña</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 mt-3">
                            <button class="btn btn-despapela-green btn-lg" type="submit">Registrarse</button>
                        </div>
                        
                        <div class="text-center mt-4">
                            <p class="small text-muted">¿Ya tienes una cuenta? <a href="{{ route('login') }}" class="fw-bold link-despapela-blue">Inicia sesión</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</body>
</html>