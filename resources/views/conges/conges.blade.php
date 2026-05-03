@extends('layouts.template')

@section('content')
<div class="container" style="margin-top: 20px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="app-page-title">Mes Congés</h1>
        <a href="{{ route('employer_space.conges.create') }}" class="btn app-btn-secondary">
            + Demander un congé
        </a>
    </div>
    <hr class="mb-4">

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

    {{-- Cartes solde congés --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="app-card shadow-sm p-3 text-center">
                <div style="font-size: 2rem; font-weight: 700; color: #1a6b8a;">{{ $joursAccordes ?? 0 }}</div>
                <div class="text-muted">Jours accordés / an</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="app-card shadow-sm p-3 text-center">
                <div style="font-size: 2rem; font-weight: 700; color: #e74c3c;">{{ $congesPris ?? 0 }}</div>
                <div class="text-muted">Jours pris</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="app-card shadow-sm p-3 text-center">
                <div style="font-size: 2rem; font-weight: 700; color: {{ ($solde ?? 0) > 5 ? '#27ae60' : '#e67e22' }};">
                    {{ $solde ?? 0 }}
                </div>
                <div class="text-muted">Jours restants</div>
            </div>
        </div>
    </div>

    {{-- Tableau --}}
    <div class="app-card shadow-sm mb-5">
        <div class="app-card-body">
            <div class="table-responsive">
                <table class="table app-table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date début</th>
                            <th>Date fin</th>
                            <th>Nombre de jours</th>
                            <th>Motif</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($conges as $conge)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ \Carbon\Carbon::parse($conge->date_debut)->format('d/m/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($conge->date_fin)->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge bg-info text-dark">
                                    {{ $conge->nombre_jours ?? \Carbon\Carbon::parse($conge->date_debut)->diffInDays(\Carbon\Carbon::parse($conge->date_fin)) }} j
                                </span>
                            </td>
                            <td>{{ $conge->motif ?? '-' }}</td>
                            <td>
                                @if($conge->statut === 'en_attente')
                                    <span class="badge bg-warning text-dark">En attente</span>
                                @elseif($conge->statut === 'accepte')
                                    <span class="badge bg-success">Approuvé</span>
                                @elseif($conge->statut === 'rejete')
                                    <span class="badge bg-danger">Refusé</span>
                                @else
                                    <span class="badge bg-secondary">{{ $conge->statut }}</span>
                                @endif
                            </td>
                            <td>
                                @if($conge->statut === 'en_attente')
                                    <a href="{{ route('employer_space.conges.edit', $conge->id) }}"
                                        class="btn btn-sm btn-warning me-1">Modifier</a>
                                    <form action="{{ route('employer_space.conges.delete', $conge->id) }}"
                                        method="POST" style="display:inline"
                                        onsubmit="return confirm('Annuler cette demande ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Annuler</button>
                                    </form>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Aucune demande de congé.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection