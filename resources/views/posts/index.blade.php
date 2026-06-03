@extends('layouts.template')
@section('content')

<div class="row g-3 mb-4 align-items-center justify-content-between">
    <div class="col-auto">
        <h1 class="app-page-title mb-0">Gestion des Postes</h1>
    </div>
    <div class="col-auto">
        <button class="btn app-btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddPost">
            + Ajouter un poste
        </button>
    </div>
</div>

@if(session('success_message'))
<div class="alert alert-success alert-dismissible fade show">
    {{ session('success_message') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="app-card shadow-sm mb-5">
    <div class="app-card-body">
        <div class="table-responsive">
            <table class="table app-table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Poste</th>
                        <th>Département</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($postes as $poste)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $poste->name }}</strong></td>
                        <td>
                            <span class="badge" style="background:#19a891; color:white">
                                {{ $poste->departement->name ?? '-' }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ $poste->description ?? '-' }}</td>
                        <td>
                            <button class="btn btn-sm app-btn-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEditPost"
                                data-id="{{ $poste->id }}"
                                data-name="{{ $poste->name }}"
                                data-description="{{ $poste->description }}"
                                data-department="{{ $poste->departement_id }}">
                                Éditer
                            </button>
                            <form action="{{ route('postes.destroy', $poste) }}" method="POST" style="display:inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Supprimer ce poste ?')">
                                    Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Aucun poste enregistré.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $postes->links() }}</div>
    </div>
</div>

{{-- Modal Ajouter --}}
<div class="modal fade" id="modalAddPost" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Ajouter un poste</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('postes.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Département <span class="text-danger">*</span></label>
                        <select name="departement_id" class="form-select" required>
                            <option value="">-- Choisir --</option>
                            @foreach($departements as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nom du poste <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn app-btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Éditer --}}
<div class="modal fade" id="modalEditPost" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Modifier le poste</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditPost" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Département <span class="text-danger">*</span></label>
                        <select name="departement_id" id="edit-department" class="form-select" required>
                            <option value="">-- Choisir --</option>
                            @foreach($departements as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nom du poste <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit-name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="edit-description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn app-btn-primary">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('modalEditPost').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('edit-name').value        = btn.dataset.name;
    document.getElementById('edit-description').value = btn.dataset.description;
    document.getElementById('edit-department').value  = btn.dataset.department;
    document.getElementById('formEditPost').action    = '/postes/' + btn.dataset.id;
});
</script>
@endsection