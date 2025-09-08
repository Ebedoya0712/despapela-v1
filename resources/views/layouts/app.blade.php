<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Despapela') }}</title>
    
    {{-- Estilos de tu aplicación (Vite) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    {{-- Estilos de Font Awesome (CDN) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    {{-- Estilos de DataTables y sus Botones (CDN) --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-bg-color: #004A59;
            --sidebar-link-color: rgba(255, 255, 255, 0.8);
            --sidebar-link-hover-bg: #006A80;
            --sidebar-link-active-bg: #9BCF35;
        }
        #wrapper { display: flex; }
        #sidebar-wrapper {
            width: var(--sidebar-width);
            min-height: 100vh;
            background-color: var(--sidebar-bg-color);
            transition: margin .25s ease-out;
        }
        #page-content-wrapper { flex: 1; min-width: 0; }
        .sidebar-heading { padding: 1rem 1.25rem; font-size: 1.2rem; font-weight: bold; color: white; }
        .list-group-item-action { background-color: transparent; border: 0; color: var(--sidebar-link-color); padding: 1rem 1.25rem; }
        .list-group-item-action:hover { background-color: var(--sidebar-link-hover-bg); color: white; }
        .list-group-item-action.active { background-color: var(--sidebar-link-active-bg); color: #004A59; font-weight: bold; }
        #wrapper.toggled #sidebar-wrapper { margin-left: calc(-1 * var(--sidebar-width)); }

        @media (max-width: 992px) {
            #sidebar-wrapper { margin-left: calc(-1 * var(--sidebar-width)); }
            #wrapper.toggled #sidebar-wrapper { margin-left: 0; }
        }
    </style>
</head>
<body class="bg-light">
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="border-end" id="sidebar-wrapper">
            <div class="sidebar-heading text-center">
                 <img src="/logo-blanco.png" alt="Despapela" style="height: 40px;">
            </div>
            <div class="list-group list-group-flush">
                <a href="{{ route('dashboard') }}" class="list-group-item list-group-item-action {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt fa-fw me-3"></i><span>Dashboard</span>
                </a>

                {{-- MÓDULO DEL ADMINISTRADOR --}}
                @can('manage-companies')
                    <a href="{{ route('admin.companies.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.companies.*') ? 'active' : '' }}">
                        <i class="fas fa-building fa-fw me-3"></i><span>Gestionar Empresas</span>
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="fas fa-users-cog fa-fw me-3"></i><span>Gestionar Usuarios</span>
                    </a>
                @endcan
                
                {{-- MÓDULO DEL GESTOR --}}
                @can('manage-technicians')
                    @cannot('manage-companies') {{-- Asegura que esto solo lo vea el Gestor --}}
                        <a href="{{ route('gestor.companies.index') }}" class="list-group-item list-group-item-action {{ request()->routeIs('gestor.companies.*') ? 'active' : '' }}">
                            <i class="fas fa-briefcase fa-fw me-3"></i><span>Mis Empresas</span>
                        </a>
                    @endcannot
                @endcan

                {{-- MÓDULO DEL TÉCNICO --}}
                @can('manage-documents')
                     <a href="#" class="list-group-item list-group-item-action"><i class="fas fa-tasks fa-fw me-3"></i><span>Empresas Asignadas</span></a>
                @endcan
                 
                 {{-- MÓDULO DEL TRABAJADOR / TÉCNICO --}}
                 @can('sign-documents')
                     <a href="#" class="list-group-item list-group-item-action"><i class="fas fa-file-signature fa-fw me-3"></i><span>Documentos para Firmar</span></a>
                @endcan
            </div>
        </div>

        <!-- Contenido Principal -->
        <div id="page-content-wrapper">
            <!-- Navbar Superior -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
                <div class="container-fluid">
                    <button class="btn btn-sm btn-outline-secondary" id="sidebarToggle"><i class="fas fa-bars"></i></button>
                    <ul class="navbar-nav ms-auto mt-2 mt-lg-0">
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                <i class="fas fa-user-circle fa-fw me-1"></i>
                                {{ Auth::user()->name }}
                            </a>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-user-edit fa-fw me-2"></i>{{ __('Mi Perfil') }}</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ route('logout') }}" style="color: #dc3545;" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt fa-fw me-2"></i>{{ __('Log Out') }}
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Contenido de la Página -->
            <main class="container-fluid p-4">
                @if (isset($header))
                    <h1 class="h3 mb-4 text-dark">{{ $header }}</h1>
                @endif
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        const sidebarToggle = document.body.querySelector('#sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', event => {
                event.preventDefault();
                document.body.querySelector('#wrapper').classList.toggle('toggled');
            });
        }
    </script>
    
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
    
    @stack('scripts')
</body>
</html>

