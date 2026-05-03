@extends('layouts.template')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="app-page-title mb-0">Types de Contrats</h1>
    <button type="button" class="btn app-btn-secondary" data-bs-toggle="modal" data-bs-target="#modalAjout">
        + Ajouter un type
    </button>
</div>
<hr class="mb-4">

@if(session('success_message'))
    <div class="alert alert-success">{{ session('success_message') }}</div>
@endif

<div class="app-card shadow-sm mb-5">
    <div class="app-card-body">
        <div class="table-responsive">
            <table class="table app-table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Détails</th>
                        <th>Durée (jours)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contracts as $contract)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><span class="badge bg-primary">{{ $contract->name }}</span></td>
                        <td>{{ $contract->details ?? '-' }}</td>
                        <td>{{ $contract->duration_days ?? 'Illimité' }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-secondary me-1"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEdit{{ $contract->id }}">
                                Éditer
                            </button>
                            <form action="{{ route('contracts.toggle', $contract->id) }}" method="POST" style="display:inline">
    @csrf
    @method('PATCH')
    <button type="submit" class="ios-toggle {{ $contract->active ? 'on' : 'off' }}" title="{{ $contract->active ? 'Désactiver' : 'Activer' }}">
        <span class="ios-knob"></span>
    </button>
</form>
                            
                        
                        </td>
                    </tr>

                    {{-- Modal Edit --}}
                    <div class="modal fade" id="modalEdit{{ $contract->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Modifier — {{ $contract->name }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('contracts.update', $contract->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Nom <span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control"
                                                value="{{ $contract->name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Détails</label>
                                            <textarea name="details" class="form-control" rows="3">{{ $contract->details }}</textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Durée (jours)</label>
                                            <input type="number" name="duration_days" class="form-control"
                                                value="{{ $contract->duration_days }}" min="1">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                        <button type="submit" class="btn app-btn-primary">Mettre à jour</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">Aucun type de contrat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Ajout --}}
<div class="modal fade" id="modalAjout" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un type de contrat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('contracts.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Détails</label>
                        <textarea name="details" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Durée (jours)</label>
                        <input type="number" name="duration_days" class="form-control" min="1">
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