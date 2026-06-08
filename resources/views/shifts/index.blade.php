@extends('layouts.template')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="app-page-title mb-0">Gestion des Horaires</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddShift">
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
                        <th>Type</th>
                        <th>Début</th>
                        <th>Fin</th>
                        <th>Pause début</th>
                        <th>Pause fin</th>
                        <th>Par défaut</th>
                        <th>Actif</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shifts as $shift)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $shift->name }}</strong></td>
                        <td>{{ $shift->type }}</td>
                        <td>{{ $shift->starts_at }}</td>
                        <td>{{ $shift->ends_at }}</td>
                        <td>{{ $shift->pause_start ?? '-' }}</td>
                        <td>{{ $shift->pause_end ?? '-' }}</td>
                        <td>
                            @if($shift->is_default)
                                <span class="badge bg-success">Oui</span>
                            @else
                                <span class="badge bg-secondary">Non</span>
                            @endif
                        </td>
                        <td>
                            @if($shift->actif)
                                <span class="badge bg-success">Actif</span>
                            @else
                                <span class="badge bg-danger">Inactif</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-success me-1"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEditShift"
                                data-id="{{ $shift->id }}"
                                data-name="{{ $shift->name }}"
                                data-type="{{ $shift->type }}"
                                data-starts="{{ $shift->starts_at }}"
                                data-ends="{{ $shift->ends_at }}"
                                data-pause-start="{{ $shift->pause_start }}"
                                data-pause-end="{{ $shift->pause_end }}"
                                data-is-default="{{ $shift->is_default ? '1' : '0' }}"
                                data-actif="{{ $shift->actif ? '1' : '0' }}">
                                <i class="fas fa-edit"></i> Éditer
                            </button>
                            <form action="{{ route('shifts.destroy', $shift) }}" method="POST" style="display:inline">
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
                        <td colspan="10" class="text-center text-muted py-4">Aucun horaire enregistré.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($shifts, 'links'))
        <div class="mt-3">{{ $shifts->links() }}</div>
        @endif
    </div>
</div>

{{-- Modal Ajouter --}}
<div class="modal fade" id="modalAddShift" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Ajouter un horaire</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('shifts.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control"
                            placeholder="Ex: Matin, Soir, Journée..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="morning">Matin</option>
                            <option value="afternoon">Après-midi</option>
                            <option value="night">Nuit</option>
                            <option value="two_shifts">Journée complète</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">Heure début <span class="text-danger">*</span></label>
                            <input type="time" name="starts_at" class="form-control" required>
                        </div>
                        <div class="col mb-3">
                            <label class="form-label">Heure fin <span class="text-danger">*</span></label>
                            <input type="time" name="ends_at" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">Pause début</label>
                            <input type="time" name="pause_start" class="form-control">
                        </div>
                        <div class="col mb-3">
                            <label class="form-label">Pause fin</label>
                            <input type="time" name="pause_end" class="form-control">
                        </div>
                    </div>
                    <div class="form-check mb-2">
                        <input type="checkbox" name="is_default" value="1" class="form-check-input" id="is_default">
                        <label class="form-check-label" for="is_default">Par défaut</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="actif" value="1" class="form-check-input" id="actif" checked>
                        <label class="form-check-label" for="actif">Actif</label>
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
<div class="modal fade" id="modalEditShift" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Modifier l'horaire</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditShift" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit-name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="type" id="edit-type" class="form-select" required>
                            <option value="morning">Matin</option>
                            <option value="afternoon">Après-midi</option>
                            <option value="night">Nuit</option>
                            <option value="two_shifts">Journée complète</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">Heure début <span class="text-danger">*</span></label>
                            <input type="time" name="starts_at" id="edit-starts" class="form-control" required>
                        </div>
                        <div class="col mb-3">
                            <label class="form-label">Heure fin <span class="text-danger">*</span></label>
                            <input type="time" name="ends_at" id="edit-ends" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">Pause début</label>
                            <input type="time" name="pause_start" id="edit-pause-start" class="form-control">
                        </div>
                        <div class="col mb-3">
                            <label class="form-label">Pause fin</label>
                            <input type="time" name="pause_end" id="edit-pause-end" class="form-control">
                        </div>
                    </div>
                    <div class="form-check mb-2">
                        <input type="checkbox" name="is_default" value="1" class="form-check-input" id="edit-is-default">
                        <label class="form-check-label" for="edit-is-default">Par défaut</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="actif" value="1" class="form-check-input" id="edit-actif">
                        <label class="form-check-label" for="edit-actif">Actif</label>
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
document.getElementById('modalEditShift').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('edit-name').value        = btn.dataset.name;
    document.getElementById('edit-type').value        = btn.dataset.type;
    document.getElementById('edit-starts').value      = btn.dataset.starts;
    document.getElementById('edit-ends').value        = btn.dataset.ends;
    document.getElementById('edit-pause-start').value = btn.dataset.pauseStart ?? '';
    document.getElementById('edit-pause-end').value   = btn.dataset.pauseEnd ?? '';
    document.getElementById('edit-is-default').checked = btn.dataset.isDefault === '1';
    document.getElementById('edit-actif').checked      = btn.dataset.actif === '1';
    document.getElementById('formEditShift').action    = '/shifts/' + btn.dataset.id;
});
</script>
@endsection