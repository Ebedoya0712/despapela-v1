<x-app-layout>
    <x-slot name="header">
        {{ __('Mi Perfil') }}
    </x-slot>

    <div>
        <div class="row">
            <div class="col-lg-8 mx-auto">
                
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-body p-4 p-md-5">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-body p-4 p-md-5">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body p-4 p-md-5">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>