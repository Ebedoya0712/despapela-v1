<x-app-layout>
    <x-slot name="header">
        Asignar: <span class="fw-bold">{{ $document->original_filename }}</span>
    </x-slot>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body">
            <form action="{{ route('tecnico.assignment.assign', $document) }}" method="POST">
                @csrf
                <h5 class="card-title mb-3">Selecciona los trabajadores a notificar</h5>
                <div class="mb-3">
                    @forelse ($workers as $worker)
                        <div class="form-check mb-2">
                            <div>
                                <input class="form-check-input" type="checkbox" name="workers[]" value="{{ $worker->id }}" id="worker_{{ $worker->id }}"
                                    {{ in_array($worker->id, $assignedWorkerIds) ? 'checked disabled' : '' }}>
                                <label class="form-check-label" for="worker_{{ $worker->id }}">
                                    {{ $worker->name }}
                                    @if(in_array($worker->id, $assignedWorkerIds))
                                        <span class="text-muted small">(Ya asignado, no se volverá a notificar)</span>
                                    @endif
                                </label>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted">No hay trabajadores en esta empresa.</p>
                    @endforelse
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Asignar y Notificar</button>
                    <a href="{{ route('tecnico.assignment.list') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>