@extends('layouts.template')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="app-page-title mb-0">Gestion des Horaires</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddSchedule">
        + Ajouter un horaire
    </button>
</div>

@if(session('success_message'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success_message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Début</th>
                        <th>Fin</th>
                        <th>Pause début</th>
                        <th>Pause fin</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schedules as $schedule)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $schedule->name }}</strong></td>
                        <td>{{ $schedule->start_time }}</td>
                        <td>{{ $schedule->end_time }}</td>
                        <td>{{ $schedule->break_start ?? '-' }}</td>
                        <td>{{ $schedule->break_end ?? '-' }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-success me-1"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEditSchedule"
                                data-id="{{ $schedule->id }}"
                                data-name="{{ $schedule->name }}"
                                data-start="{{ $schedule->start_time }}"
                                data-end="{{ $schedule->end_time }}"
                                data-break-start="{{ $schedule->break_start }}"
                                data-break-end="{{ $schedule->break_end }}">
                                <i class="fas fa-edit"></i> Éditer
                            </button>
                            <form action="{{ route('schedules.destroy', $schedule) }}" method="POST" style="display:inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('Supprimer cet horaire ?')">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Aucun horaire enregistré.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $schedules->links() }}</div>
    </div>
</div>

{{-- Modal Ajouter --}}
<div class="modal fade" id="modalAddSchedule" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Ajouter un horaire</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('schedules.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control"
                            placeholder="Ex: Normal, Matin, Soir..." required>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">Heure début <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" class="form-control" required>
                        </div>
                        <div class="col mb-3">
                            <label class="form-label">Heure fin <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">Pause début</label>
                            <input type="time" name="break_start" class="form-control">
                        </div>
                        <div class="col mb-3">
                            <label class="form-label">Pause fin</label>
                            <input type="time" name="break_end" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Éditer --}}
<div class="modal fade" id="modalEditSchedule" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Modifier l'horaire</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditSchedule" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit-name" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">Heure début <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" id="edit-start" class="form-control" required>
                        </div>
                        <div class="col mb-3">
                            <label class="form-label">Heure fin <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" id="edit-end" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">Pause début</label>
                            <input type="time" name="break_start" id="edit-break-start" class="form-control">
                        </div>
                        <div class="col mb-3">
                            <label class="form-label">Pause fin</label>
                            <input type="time" name="break_end" id="edit-break-end" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('modalEditSchedule').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('edit-name').value        = btn.dataset.name;
    document.getElementById('edit-start').value       = btn.dataset.start;
    document.getElementById('edit-end').value         = btn.dataset.end;
    document.getElementById('edit-break-start').value = btn.dataset.breakStart ?? '';
    document.getElementById('edit-break-end').value   = btn.dataset.breakEnd ?? '';
    document.getElementById('formEditSchedule').action = '/schedules/' + btn.dataset.id;
});
</script>
@endsection