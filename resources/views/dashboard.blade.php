<x-app-layout>
    <x-slot name="header">
        {{ __('Dashboard') }}
    </x-slot>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                     <p class="text-muted">¡Bienvenido de nuevo, {{ Auth::user()->name }}!</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>