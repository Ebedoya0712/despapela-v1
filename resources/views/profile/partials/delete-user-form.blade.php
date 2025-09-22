<section>
    <header>
        <h5 class="card-title text-danger">Eliminar Cuenta</h5>
        <p class="text-muted small mt-1">
            Una vez que tu cuenta sea eliminada, todos sus recursos y datos serán borrados permanentemente. Antes de eliminar tu cuenta, por favor descarga cualquier dato o información que desees conservar.
        </p>
    </header>

    <button type="button" class="btn btn-danger mt-3" id="delete-account-btn">
        Eliminar Mi Cuenta
    </button>
    
    <form id="delete-account-form" action="{{ route('profile.destroy') }}" method="POST" class="d-none">
        @csrf
        @method('delete')
    </form>
</section>

@push('scripts')
<script>
    document.getElementById('delete-account-btn').addEventListener('click', function() {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡No podrás revertir esta acción! Se te pedirá tu contraseña para confirmar.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, ¡elimínala!',
            cancelButtonText: 'Cancelar',
            input: 'password',
            inputPlaceholder: 'Escribe tu contraseña para confirmar',
            inputValidator: (value) => {
                if (!value) {
                    return '¡Necesitas escribir tu contraseña!'
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Añadimos la contraseña al formulario antes de enviarlo
                const form = document.getElementById('delete-account-form');
                const passwordInput = document.createElement('input');
                passwordInput.type = 'hidden';
                passwordInput.name = 'password';
                passwordInput.value = result.value;
                form.appendChild(passwordInput);
                
                form.submit();
            }
        });
    });
</script>
@endpush