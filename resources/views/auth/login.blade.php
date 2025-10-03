<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Despapela</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { background-color: #f0f2f5; }
        .card-header-despapela {
            background-color: #006A80; /* Azul de tu marca */
            color: white;
            padding: 2rem;
            border-top-left-radius: 1rem;
            border-top-right-radius: 1rem;
        }
        .btn-despapela-green {
            background-color: #9BCF35; /* Verde de tu marca */
            border-color: #9BCF35;
            color: white;
        }
        .btn-despapela-green:hover {
            background-color: #8ab82e;
            border-color: #8ab82e;
            color: white;
        }
        .link-despapela-blue {
            color: #006A80;
            text-decoration: none;
        }
        .link-despapela-blue:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <section class="vh-100 d-flex justify-content-center align-items-center">
        <div class="col-12 col-md-8 col-lg-6 col-xl-5" style="max-width: 500px;">
            <div class="card shadow-lg border-0" style="border-radius: 1rem;">
                
                <div class="card-header card-header-despapela text-center">
                    <img src="/logo-blanco.png" alt="Logo Despapela" style="max-height: 60px;" class="mb-3">
                    <h3 class="fw-bold mb-0">Bienvenido a Despapela</h3>
                </div>

                <div class="card-body p-5">
                    <p class="text-muted text-center mb-4">Inicia sesión para continuar</p>

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <div class="form-floating flex-grow-1">
                                <input type="email" id="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="Email" required autofocus autocomplete="username" />
                                <label for="email">Email</label>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-n3 mb-3 text-danger small text-start" />

                        <div class="input-group mb-3">
                             <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <div class="form-floating flex-grow-1">
                                <input type="password" id="password" class="form-control" name="password" placeholder="Contraseña" required autocomplete="current-password" />
                                <label for="password">Password</label>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-n3 mb-3 text-danger small text-start" />
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember_me" />
                                <label class="form-check-label small" for="remember_me"> Recuérdame </label>
                            </div>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="small link-despapela-blue">¿Olvidaste tu contraseña?</a>
                            @endif
                        </div>

                        <div class="d-grid gap-2">
                            <button class="btn btn-despapela-green btn-lg" type="submit">Login</button>
                        </div>
                        
                        <div class="text-center mt-4">
                            <p class="small text-muted">¿No tienes una cuenta? <a href="{{ route('register') }}" class="fw-bold link-despapela-blue">Regístrate</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</body>
</html>