@extends('layouts.template')

@section('content')

@if(session('success_message'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success_message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error_message'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error_message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-3 mb-4 align-items-center justify-content-between">
    <div class="col-auto">
        <h1 class="app-page-title mb-0">Gestion des Départements</h1>
    </div>
    <div class="col-auto">
        <button class="btn app-btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAjoutDepartement">
            + Ajouter un département
        </button>
    </div>
</div>

<div class="app-card shadow-sm mb-5">
    <div class="app-card-body">
        <div class="table-responsive">
            <table class="table app-table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departements as $departement)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $departement->name }}</strong></td>
                        <td>
                            <button class="btn btn-sm app-btn-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEditDepartement"
                                data-id="{{ $departement->id }}"
                                data-name="{{ $departement->name }}">
                                Éditer
                            </button>
                            <a href="{{ route('departement.destroy', $departement->id) }}"
                                class="btn btn-sm btn-warning"
                                onclick="return confirm('Archiver ce département ?')">
                                Archiver
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">Aucun département enregistré.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $departements->links() }}</div>
    </div>
</div>

{{-- Modal Ajouter --}}
<div class="modal fade" id="modalAjoutDepartement" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Ajouter un département</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('departement.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom du département <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               placeholder="Ex: Ressources Humaines" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
<div class="modal fade" id="modalEditDepartement" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Modifier le département</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditDepartement" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom du département <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit-name" class="form-control" required>
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
document.getElementById('modalEditDepartement').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('edit-name').value           = btn.dataset.name;
    document.getElementById('formEditDepartement').action = '/departements/update/' + btn.dataset.id;
});
</script>

@endsection