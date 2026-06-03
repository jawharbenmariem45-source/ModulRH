@extends('layouts.template')
@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="app-page-title mb-0">
            @if(auth()->user()->hasRole('manager'))
                Demandes de congés à valider
            @else
                Gestion des congés
            @endif
        </h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Employé</th>
                            <th>Type</th>
                            <th>Période</th>
                            <th>Jours</th>
                            <th>Motif</th>
                            <th>Statut</th>
                            @if(auth()->user()->hasRole('manager'))
                                <th>Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($conges as $c)
                        @php
                            $employe   = $c->user ?? $c->employer ?? null;
                            $dateDebut = $c->start_date ? \Carbon\Carbon::parse($c->start_date)->format('d/m/Y') : '-';
                            $dateFin   = $c->end_date   ? \Carbon\Carbon::parse($c->end_date)->format('d/m/Y')   : '-';
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $employe->last_name ?? 'N/A' }}</strong>
                                {{ $employe->first_name ?? '' }}
                                <div class="text-muted small">{{ $employe->departement->name ?? '' }}</div>
                            </td>
                            <td>
                                @if($c->type)
                                    <span class="badge bg-info text-dark">{{ $c->type }}</span>
                                @else
                                    <span class="badge bg-secondary">N/A</span>
                                @endif
                            </td>
                            <td>Du {{ $dateDebut }}<br>au {{ $dateFin }}</td>
                            <td>
                                @if($c->days_count)
                                    <span class="badge bg-primary">{{ $c->days_count }}j</span>
                                @else -
                                @endif
                            </td>
                            <td>{{ $c->reason ?? '-' }}</td>
                            <td>
                                @php $statut = strtolower(trim($c->status ?? '')); @endphp
                                @if($statut === 'pending')
                                    <span class="badge bg-warning text-dark">En attente</span>
                                @elseif($statut === 'approved')
                                    <span class="badge bg-success">Approuvé</span>
                                @elseif($statut === 'rejected')
                                    <span class="badge bg-danger">Refusé</span>
                                @else
                                    <span class="badge bg-secondary">{{ $c->status ?? 'N/A' }}</span>
                                @endif
                            </td>
                            @if(auth()->user()->hasRole('manager'))
                            <td>
                                @if(strtolower(trim($c->status ?? '')) === 'pending')
                                    <button type="button" class="btn btn-sm btn-success mb-1"
                                        data-bs-toggle="modal" data-bs-target="#modalApprouver"
                                        data-id="{{ $c->id }}"
                                        data-nom="{{ $employe->last_name ?? '' }} {{ $employe->first_name ?? '' }}"
                                        data-debut="{{ $dateDebut }}"
                                        data-fin="{{ $dateFin }}">
                                        <i class="fas fa-check"></i> Approuver
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger"
                                        data-bs-toggle="modal" data-bs-target="#modalRefuser"
                                        data-id="{{ $c->id }}"
                                        data-nom="{{ $employe->last_name ?? '' }} {{ $employe->first_name ?? '' }}"
                                        data-debut="{{ $dateDebut }}"
                                        data-fin="{{ $dateFin }}">
                                        <i class="fas fa-times"></i> Refuser
                                    </button>
                                @else
                                    <span class="text-muted small">Déjà traité</span>
                                @endif
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Aucune demande de congé.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $conges->links() }}</div>
        </div>
    </div>
</div>

{{-- Modal Approuver --}}
<div class="modal fade" id="modalApprouver" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title text-success">
                    <i class="fas fa-check-circle me-2"></i> Approuver le congé
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Approuver le congé de <strong id="approuver-nom"></strong> ?</p>
                <p class="text-muted small">
                    Du <strong id="approuver-debut"></strong>
                    au <strong id="approuver-fin"></strong>
                </p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="formApprouver" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Confirmer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal Refuser --}}
<div class="modal fade" id="modalRefuser" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title text-danger">
                    <i class="fas fa-times-circle me-2"></i> Refuser le congé
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Refuser le congé de <strong id="refuser-nom"></strong> ?</p>
                <p class="text-muted small">
                    Du <strong id="refuser-debut"></strong>
                    au <strong id="refuser-fin"></strong>
                </p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="formRefuser" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Confirmer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('modalApprouver').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('approuver-nom').textContent   = btn.dataset.nom;
    document.getElementById('approuver-debut').textContent = btn.dataset.debut;
    document.getElementById('approuver-fin').textContent   = btn.dataset.fin;
    document.getElementById('formApprouver').action = '/conges/' + btn.dataset.id + '/accepter';
});
document.getElementById('modalRefuser').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('refuser-nom').textContent   = btn.dataset.nom;
    document.getElementById('refuser-debut').textContent = btn.dataset.debut;
    document.getElementById('refuser-fin').textContent   = btn.dataset.fin;
    document.getElementById('formRefuser').action = '/conges/' + btn.dataset.id + '/rejeter';
});
</script>
@endsection