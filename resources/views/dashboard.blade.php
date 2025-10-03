<x-app-layout>
    <x-slot name="header">
        {{ __('Dashboard') }}
    </x-slot>

    <div>
        @switch(Auth::user()->role->name)
            @case('Administrador')
                @include('dashboard.partials.admin')
                @break
            @case('Gestor')
                @include('dashboard.partials.gestor')
                @break
            @case('Técnico')
                @include('dashboard.partials.tecnico')
                @break
            @case('Trabajador')
                @include('dashboard.partials.trabajador')
                @break
            @default
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body p-4">
                        <p class="text-muted">¡Bienvenido de nuevo, {{ Auth::user()->name }}!</p>
                    </div>
                </div>
        @endswitch
    </div>
</x-app-layout>