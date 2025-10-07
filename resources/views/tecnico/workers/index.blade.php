<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Trabajadores') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="card shadow-lg border-0 rounded-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center p-4">
                    <h5 class="mb-0 text-dark fw-bold">
                        <i class="fas fa-users me-2" style="color: #004A59;"></i>
                        Listado de Trabajadores Asignados
                    </h5>
                    
                    {{-- Botón que llama a la función JS para seleccionar la empresa antes de crear --}}
                    <button onclick="showCompanySelection()" class="btn text-white rounded-pill shadow-sm flex-shrink-0 px-4 py-2 fw-bold" style="background-color: #004A59; border: 2px solid #004A59;">
                        <i class="fas fa-user-plus me-2"></i>Añadir Personal
                    </button>
                </div>

                <div class="card-body p-4">
                    {{-- El bloque de manejo de mensajes de sesión ahora está en el script con SweetAlert2 --}}

                    <div class="table-responsive">
                        <table id="workersTable" class="table table-striped table-hover table-bordered" style="width:100%">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-center">ID</th>
                                    <th>Nombre Completo</th>
                                    <th>Email</th>
                                    <th>Empresa(s) Asignada(s)</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($workers as $worker)
                                @php
                                    // Necesitamos la ID de una empresa a la que pertenece el trabajador para la ruta de edición/actualización.
                                    // Usamos la ID de la primera empresa encontrada.
                                    $companyId = $worker->memberOfCompanies->first()->id ?? 0;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $worker->id }}</td>
                                    <td>{{ $worker->name }}</td>
                                    <td>{{ $worker->email }}</td>
                                    <td>
                                        @forelse ($worker->memberOfCompanies as $company)
                                            <span class="badge rounded-pill text-white me-1" style="background-color: #004A59;">{{ $company->name }}</span>
                                        @empty
                                            <span class="badge bg-warning text-dark">Sin Empresa</span>
                                        @endforelse
                                    </td>
                                    <td class="text-center">
                                        @if ($worker->is_active)
                                            <span class="badge bg-success py-2 px-3 rounded-pill">Activo</span>
                                        @else
                                            <span class="badge bg-secondary py-2 px-3 rounded-pill">Inactivo</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            
                                            {{-- Botón de Edición: Apunta a la ruta 'tecnico.workers.edit' con company y worker --}}
                                            <a href="{{ route('tecnico.workers.edit', ['company' => $companyId, 'worker' => $worker->id]) }}" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="Editar Trabajador">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            {{-- Botón para Cambiar Estado (toggleStatus) --}}
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-{{ $worker->is_active ? 'warning' : 'success' }}" 
                                                    onclick="toggleWorkerStatus({{ $worker->id }}, '{{ $worker->name }}', {{ $worker->is_active ? 'false' : 'true' }})" 
                                                    title="{{ $worker->is_active ? 'Desactivar Trabajador' : 'Activar Trabajador' }}">
                                                <i class="fas fa-toggle-{{ $worker->is_active ? 'off' : 'on' }}"></i>
                                            </button>

                                            {{-- Botón de Eliminación (Soft Delete) --}}
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteWorker({{ $worker->id }}, '{{ $worker->name }}')" 
                                                    title="Eliminar Trabajador">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="fas fa-exclamation-circle me-2"></i> No hay trabajadores asignados en sus empresas para mostrar.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <!-- Cargar SweetAlert2 desde CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Función para mostrar el modal de selección de empresa (Creación)
        function showCompanySelection() {
            let availableCompanies = @json(Auth::user()?->memberOfCompanies ?? []);
            
            if (!availableCompanies || availableCompanies.length === 0) {
                // Mensaje de advertencia si no hay empresas
                Swal.fire({
                    icon: 'info',
                    title: 'Empresas no Encontradas',
                    text: 'Actualmente no tienes empresas asignadas. No puedes añadir personal.',
                    confirmButtonColor: '#004A59',
                });
                return;
            }

            let companyOptions = '<option value="">Seleccione una empresa...</option>';
            availableCompanies.forEach(c => {
                companyOptions += `<option value="${c.id}">${c.name}</option>`;
            });

            Swal.fire({
                title: 'Seleccionar Empresa para Asignar',
                html: `
                    <p class="text-start text-muted mb-3">¿A qué empresa de tu lista deseas añadir el nuevo trabajador?</p>
                    <select id="swal-company-select" class="form-select">
                        ${companyOptions}
                    </select>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Añadir Personal',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#004A59',
                cancelButtonColor: '#6c757d',
                preConfirm: () => {
                    const companyId = document.getElementById('swal-company-select').value;
                    if (!companyId) {
                        Swal.showValidationMessage('Debe seleccionar una empresa para continuar.');
                        return false;
                    }
                    // Redirección a la ruta de creación
                    window.location.href = `/tecnico/companies/${companyId}/workers/create`; 
                }
            });
        }

        // --- FUNCIÓN PARA TOGGLE STATUS (ACTIVAR/DESACTIVAR) ---
        function toggleWorkerStatus(workerId, workerName, currentStateIsActive) {
            const actionText = currentStateIsActive ? 'activar' : 'desactivar';
            const statusText = currentStateIsActive ? 'Activo' : 'Inactivo';

            Swal.fire({
                title: 'Confirmar Acción',
                text: `¿Estás seguro de que quieres ${actionText} a ${workerName}? Su estado cambiará a ${statusText}.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#004A59',
                cancelButtonColor: '#d33',
                confirmButtonText: `Sí, ${actionText}`,
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Crea un formulario dinámico para enviar la solicitud PATCH
                    const form = document.createElement('form');
                    form.action = `/tecnico/workers/${workerId}/toggle-status`;
                    form.method = 'POST';
                    
                    // Token CSRF
                    const token = document.createElement('input');
                    token.type = 'hidden';
                    token.name = '_token';
                    token.value = '{{ csrf_token() }}';
                    form.appendChild(token);

                    // Método PATCH
                    const method = document.createElement('input');
                    method.type = 'hidden';
                    method.name = '_method';
                    method.value = 'PATCH';
                    form.appendChild(method);

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }


        // --- FUNCIÓN PARA ELIMINAR (SOFT DELETE) ---
        function deleteWorker(workerId, workerName) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `Vas a eliminar (soft delete) al trabajador ${workerName}. Podrás recuperarlo en el futuro.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Crea un formulario dinámico para enviar la solicitud DELETE
                    const form = document.createElement('form');
                    form.action = `/tecnico/workers/${workerId}`;
                    form.method = 'POST'; // Se usa POST para enviar el formulario

                    // Token CSRF
                    const token = document.createElement('input');
                    token.type = 'hidden';
                    token.name = '_token';
                    token.value = '{{ csrf_token() }}';
                    form.appendChild(token);

                    // Método DELETE
                    const method = document.createElement('input');
                    method.type = 'hidden';
                    method.name = '_method';
                    method.value = 'DELETE';
                    form.appendChild(method);

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // --- MANEJO DE MENSAJES DE SESIÓN Y DATATABLES ---
        $(document).ready(function() {
            
            // Bloque para mostrar mensajes de sesión con SweetAlert2
            const successMessage = '{{ session('success') }}';
            const errorMessage = '{{ session('error') }}';

            if (successMessage.trim().length > 0) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Operación Exitosa!',
                    text: successMessage,
                    timer: 3500,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    confirmButtonColor: '#004A59',
                });
            } else if (errorMessage.trim().length > 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage,
                    confirmButtonColor: '#d33',
                });
            }
            
            // Inicialización de DataTables
            $('#workersTable').DataTable({
                // FIX: Se usa el objeto de idioma directamente para evitar el error de carga del archivo i18n
                language: {
                    "processing": "Procesando...",
                    "lengthMenu": "Mostrar _MENU_ registros",
                    "zeroRecords": "No se encontraron resultados",
                    "emptyTable": "Ningún dato disponible en esta tabla",
                    "info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                    "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                    "infoFiltered": "(filtrado de un total de _MAX_ registros)",
                    "infoPostFix": "",
                    "search": "Buscar:", // <--- Traducción de la barra de búsqueda
                    "url": "",
                    "infoThousands": ",",
                    "loadingRecords": "Cargando...",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    },
                    "aria": {
                        "sortAscending": ": Activar para ordenar la columna de manera ascendente",
                        "sortDescending": ": Activar para ordenar la columna de manera descendente"
                    }
                },
                responsive: true,
                dom: "<'row mb-3'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                columnDefs: [
                    // Deshabilita la ordenación y búsqueda para las columnas de Empresas, Estado y Acciones
                    { targets: [3, 4, 5], orderable: false, searchable: false } 
                ]
            });
        });
    </script>
    @endpush
</x-app-layout>
