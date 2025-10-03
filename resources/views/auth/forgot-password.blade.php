<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Restablecer Contraseña - Despapela</title>
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
    <section class="vh-100 d-flex justify-content-center align-items-center">
        <div class="col-12 col-md-8 col-lg-6 col-xl-5" style="max-width: 500px;">
            <div class="card shadow-lg border-0" style="border-radius: 1rem;">
                
                <div class="card-header card-header-despapela text-center">
                    <img src="/logo-blanco.png" alt="Logo Despapela" style="max-height: 60px;" class="mb-3">
                    <h3 class="fw-bold mb-0">Restablecer Contraseña</h3>
                </div>

                <div class="card-body p-5">
                    <p class="text-muted text-center mb-4">
                        ¿Olvidaste tu contraseña? No hay problema. Ingresa tu email y te enviaremos un enlace para que elijas una nueva.
                    </p>

                    <x-auth-session-status class="mb-4 alert alert-success" :status="session('status')" />

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="input-group mb-4">
                            <span class="input-group-text"><i class="fas fa-envelope fa-fw"></i></span>
                            <div class="form-floating flex-grow-1">
                                <input type="email" id="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="Email" required autofocus />
                                <label for="email">Email</label>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-n3 mb-3 text-danger small text-start" />
                        
                        <div class="d-grid gap-2">
                            <button class="btn btn-despapela-green btn-lg" type="submit">
                                Enviar Enlace de Recuperación
                            </button>
                        </div>
                        
                        <div class="text-center mt-4">
                            <p class="small text-muted">¿Recordaste tu contraseña? <a href="{{ route('login') }}" class="fw-bold link-despapela-blue">Volver a Iniciar sesión</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</body>
</html>