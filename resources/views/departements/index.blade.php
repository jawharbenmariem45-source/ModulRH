@extends('layouts.template')

@section('content')

{{-- Messages --}}
@if(session('success_message'))
    <div class="alert alert-success">{{ session('success_message') }}</div>
@endif
@if(session('error_message'))
    <div class="alert alert-danger">{{ session('error_message') }}</div>
@endif

<div class="row g-3 mb-4 align-items-center justify-content-between">
    <div class="col-auto">
        <h1 class="app-page-title mb-0">Departements</h1>
    </div>
    <div class="col-auto">
        <div class="page-utilities">
            <div class="row g-2 justify-content-start justify-content-md-end align-items-center">
                <div class="col-auto">
                    <form class="table-search-form row gx-1 align-items-center">
                        <div class="col-auto">
                            <input type="text" name="searchorders" class="form-control search-orders" placeholder="Search">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn app-btn-secondary">Search</button>
                        </div>
                    </form>
                </div>
                <div class="col-auto">
                    {{-- Bouton ouvre le modal --}}
                    <button class="btn app-btn-secondary" data-bs-toggle="modal" data-bs-target="#modalAjoutDepartement">
                        <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-plus me-1" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2z"/>
                        </svg>
                        Ajouter Departement
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="tab-content" id="orders-table-tab-content">
    <div class="tab-pane fade show active" id="orders-all" role="tabpanel">
        <div class="app-card app-card-orders-table shadow-sm mb-5">
            <div class="app-card-body">
                <div class="table-responsive">
                    <table class="table app-table-hover mb-0 text-left">
                        <thead>
                            <tr>
                                <th class="cell">#</th>
                                <th class="cell">Nom</th>
                                <th class="cell"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($departements as $departement)
                            <tr>
                                <td class="cell">{{ $departement->id }}</td>
                                <td class="cell"><span class="truncate">{{ $departement->name }}</span></td>
                                <td>
                                    <a class="btn-sm app-btn-secondary" href="{{ route('departement.destroy', $departement->id) }}">
                                        Retirer
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td class="cell" colspan="3">Aucun departement ajouté</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <nav class="app-pagination">
            {{ $departements->links() }}
        </nav>
    </div>
</div>

{{-- ══════════════════════════════════════════════ --}}
{{-- MODAL AJOUT DÉPARTEMENT                        --}}
{{-- ══════════════════════════════════════════════ --}}
<div class="modal fade" id="modalAjoutDepartement" tabindex="-1" aria-labelledby="modalAjoutDepartementLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAjoutDepartementLabel">Ajouter un Département</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('departement.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nom du département</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               placeholder="Ex: Ressources Humaines" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn app-btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection