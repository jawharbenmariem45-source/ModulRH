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
                        <tr>
                            <td>
                                <strong>{{ $c->employer->last_name ?? 'N/A' }}</strong>
                                {{ $c->employer->first_name ?? '' }}
                                <div class="text-muted small">{{ $c->employer->departement->name ?? '' }}</div>
                            </td>
                            <td>
                                @if($c->type)
                                    <span class="badge bg-info">{{ $c->type }}</span>
                                @else
                                    <span class="badge bg-secondary">N/A</span>
                                @endif
                            </td>
                            <td>
                                Du {{ $c->start_date }}<br>
                                au {{ $c->end_date }}
                            </td>
                            <td>
                                @if($c->days_count)
                                    <span class="badge bg-primary">{{ $c->days_count }}j</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $c->reason ?? '-' }}</td>
                            <td>
                                @php $statut = strtolower(trim($c->status ?? '')); @endphp
                                @if(in_array($statut, ['en attente', 'en_attente']))
                                    <span class="badge bg-warning text-dark">En attente</span>
                                @elseif(in_array($statut, ['approuvé', 'approuve', 'accepté', 'accepte']))
                                    <span class="badge bg-success">Approuvé</span>
                                @elseif(in_array($statut, ['refusé', 'refuse', 'rejeté', 'rejete']))
                                    <span class="badge bg-danger">Refusé</span>
                                @elseif(is_null($c->status))
                                    <span class="badge bg-secondary">N/A</span>
                                @else
                                    <span class="badge bg-secondary">{{ $c->status }}</span>
                                @endif
                            </td>
                            @if(auth()->user()->hasRole('manager'))
                            <td>
                                @php $statutRaw = strtolower(trim($c->status ?? '')); @endphp
                                @if(in_array($statutRaw, ['en attente', 'en_attente']))
                                    <button type="button" class="btn btn-sm btn-success mb-1"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalApprouver"
                                        data-id="{{ $c->id }}"
                                        data-nom="{{ $c->employer->last_name ?? '' }} {{ $c->employer->first_name ?? '' }}"
                                        data-debut="{{ $c->start_date }}"
                                        data-fin="{{ $c->end_date }}">
                                        <i class="fas fa-check"></i> Approuver
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalRefuser"
                                        data-id="{{ $c->id }}"
                                        data-nom="{{ $c->employer->last_name ?? '' }} {{ $c->employer->first_name ?? '' }}"
                                        data-debut="{{ $c->start_date }}"
                                        data-fin="{{ $c->end_date }}">
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
                            <td colspan="7" class="text-center text-muted py-4">
                                @if(auth()->user()->hasRole('manager'))
                                    Aucune demande en attente.
                                @else
                                    Aucun congé enregistré.
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $conges->links() }}
            </div>
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
                <p>Voulez-vous approuver le congé de <strong id="approuver-nom"></strong> ?</p>
                <p class="text-muted small">
                    Du <strong id="approuver-debut"></strong> au <strong id="approuver-fin"></strong>
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
                <p>Voulez-vous refuser le congé de <strong id="refuser-nom"></strong> ?</p>
                <p class="text-muted small">
                    Du <strong id="refuser-debut"></strong> au <strong id="refuser-fin"></strong>
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