@extends('layouts.template')

@section('content')
<div class="container" style="margin-top: 20px;">
    <h1 class="app-page-title">Mon Contrat</h1>
    <hr class="mb-4">

    @if(!$employer->type_contrat)
    <div class="alert alert-warning">
        Aucun contrat actif pour le moment.
    </div>
    @else
    @php $contrat = $employer->activeContract(); @endphp

    {{-- Alerte expiration --}}
    @if($jours !== null && $jours <= 30 && $jours >= 0)
    <div class="alert alert-warning">
        ⚠️ Votre contrat expire dans <strong>{{ $jours }} jour(s)</strong> !
    </div>
    @elseif($jours !== null && $jours < 0)
    <div class="alert alert-danger">
        ❌ Votre contrat est <strong>expiré</strong> !
    </div>
    @endif

    <div class="row g-4">

        {{-- Infos employé --}}
        <div class="col-md-6">
            <div class="app-card shadow-sm p-4">
                <h5 class="mb-3" style="color:#19a891">👤 Informations personnelles</h5>
                <table class="table table-borderless">
                    <tr>
                        <td class="text-muted">Nom complet</td>
                        <td><strong>{{ $employer->nom }} {{ $employer->prenom }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Email</td>
                        <td>{{ $employer->email }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Téléphone</td>
                        <td>{{ $employer->numero_telephone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Département</td>
                        <td>{{ $employer->departement->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">N° CNSS</td>
                        <td>{{ $employer->cnss ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Infos contrat --}}
        <div class="col-md-6">
            <div class="app-card shadow-sm p-4">
                <h5 class="mb-3" style="color:#19a891">📄 Détails du contrat</h5>
                <table class="table table-borderless">
                    <tr>
                        <td class="text-muted">Type</td>
                        <td>
                            <span class="badge" style="background:#19a891; color:white">
                                {{ $employer->type_contrat }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Date début</td>
                        <td>{{ $employer->date_debut ? \Carbon\Carbon::parse($employer->date_debut)->format('d/m/Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Date fin</td>
                        <td>
                            {{ $employer->date_fin ? \Carbon\Carbon::parse($employer->date_fin)->format('d/m/Y') : 'CDI — Indéterminée' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Statut</td>
                        <td>
                            @if($jours === null || $jours > 30)
                                <span class="badge" style="background:#19a891">Actif</span>
                            @elseif($jours <= 30 && $jours >= 0)
                                <span class="badge bg-warning text-dark">Expire bientôt</span>
                            @else
                                <span class="badge bg-danger">Expiré</span>
                            @endif
                        </td>
                    </tr>
                    @if($contrat)
                    <tr>
                        <td class="text-muted">Détails</td>
                        <td><small class="text-muted">{{ $contrat->details ?? '-' }}</small></td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        {{-- RIB --}}
        @if($employer->rib || $employer->rib_image)
        <div class="col-md-12">
            <div class="app-card shadow-sm p-4">
                <h5 class="mb-3" style="color:#19a891">🏦 RIB</h5>
                <div class="row">
                    @if($employer->rib)
                    <div class="col-md-6">
                        <p class="text-muted mb-1">Numéro RIB</p>
                        @if($employer->rib_image)
    <a href="{{ asset('storage/' . $employer->rib_image) }}" target="_blank">
        <img src="{{ asset('storage/' . $employer->rib_image) }}"
             style="max-width:200px; border-radius:4px; border:1px solid #ddd;">
    </a>
@endif
@if($employer->rib)
    <p><strong>Numéro :</strong> {{ $employer->rib }}</p>
@endif
                    </div>
                    @endif
                    @if($employer->rib_image)
                    <div class="col-md-6">
                        <p class="text-muted mb-1">Photo RIB</p>
                        <a href="{{ asset('storage/' . $employer->rib_image) }}" target="_blank">
                            <img src="{{ asset('storage/' . $employer->rib_image) }}"
                                 alt="RIB" style="max-width:200px; border:1px solid #ddd; border-radius:4px;">
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

    </div>
    @endif
</div>
@endsection
