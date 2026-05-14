@extends('layouts.template')

@section('content')
<h1 class="app-page-title">Dashboard</h1>

<div class="row g-4 mb-4">

    {{-- Mon Contrat --}}
    <div class="col-6 col-lg-3">
        <div class="app-card app-card-stat shadow-sm h-100">
            <div class="app-card-body p-3 p-lg-4">
                <h4 class="stats-type mb-1">Mon Contrat</h4>
                <div class="stats-figure">
                    @if($contrat)
                        <span class="badge bg-success">Actif</span>
                    @else
                        <span class="badge bg-secondary">Aucun</span>
                    @endif
                </div>
            </div>
            <a class="app-card-link-mask" href="{{ route('employer_space.contrat') }}"></a>
        </div>
    </div>

    {{-- Congés en attente --}}
    <div class="col-6 col-lg-3">
        <div class="app-card app-card-stat shadow-sm h-100">
            <div class="app-card-body p-3 p-lg-4">
                <h4 class="stats-type mb-1">Congés En Attente</h4>
                <div class="stats-figure {{ $congesEnAttente > 0 ? 'text-warning' : '' }}">
                    {{ $congesEnAttente }}
                </div>
            </div>
            <a class="app-card-link-mask" href="{{ route('employer_space.conges') }}"></a>
        </div>
    </div>

    {{-- Congés approuvés --}}
    <div class="col-6 col-lg-3">
        <div class="app-card app-card-stat shadow-sm h-100">
            <div class="app-card-body p-3 p-lg-4">
                <h4 class="stats-type mb-1">Congés Approuvés</h4>
                <div class="stats-figure {{ $congesApprouves > 0 ? 'text-success' : '' }}">
                    {{ $congesApprouves }}
                </div>
            </div>
            <a class="app-card-link-mask" href="{{ route('employer_space.conges') }}"></a>
        </div>
    </div>

    {{-- Paiements --}}
    <div class="col-6 col-lg-3">
        <div class="app-card app-card-stat shadow-sm h-100">
            <div class="app-card-body p-3 p-lg-4">
                <h4 class="stats-type mb-1">Mes Paiements</h4>
                <div class="stats-figure">{{ $totalPaiements }}</div>
            </div>
            <a class="app-card-link-mask" href="{{ route('employer_space.paiements') }}"></a>
        </div>
    </div>

</div>

{{-- Derniers congés + derniers paiements --}}
<div class="row g-4">

    <div class="col-12 col-lg-6">
        <div class="app-card shadow-sm h-100">
            <div class="app-card-header p-3">
                <h4 class="app-card-title">Mes derniers congés</h4>
            </div>
            <div class="app-card-body p-3">
                @if($dernierConges->isEmpty())
                    <p class="text-muted">Aucun congé enregistré.</p>
                @else
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Du</th>
                                <th>Au</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dernierConges as $conge)
                            <tr>
                                <td>{{ $conge->start_date }}</td>
                                <td>{{ $conge->end_date }}</td>
                                <td>
                                    @if($conge->status === 'Approuvé')
                                        <span class="badge bg-success">Approuvé</span>
                                    @elseif($conge->status === 'Refusé')
                                        <span class="badge bg-danger">Refusé</span>
                                    @else
                                        <span class="badge bg-warning text-dark">En attente</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="app-card shadow-sm h-100">
            <div class="app-card-header p-3">
                <h4 class="app-card-title">Mes derniers paiements</h4>
            </div>
            <div class="app-card-body p-3">
                @if($dernierPaiements->isEmpty())
                    <p class="text-muted">Aucun paiement enregistré.</p>
                @else
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Mois</th>
                                <th>Montant net</th>
                                <th>Fiche</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dernierPaiements as $paiement)
                            <tr>
                                <td>{{ $paiement->month }} {{ $paiement->year }}</td>
                                <td>{{ number_format((float)$paiement->amount, 2) }} TND</td>
                                <td>
                                    <a href="{{ route('employer_space.paiements.pdf', $paiement->id) }}">
                                        <i class="fas fa-file-pdf"></i> PDF
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection