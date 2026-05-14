@extends('layouts.template')

@section('content')
<h1 class="app-page-title">Mon Contrat</h1>

<div class="row g-4 mt-2">
    <div class="col-12 col-lg-8">
        <div class="app-card shadow-sm h-100">
            <div class="app-card-header p-3">
                <h4 class="app-card-title">Informations du contrat</h4>
            </div>
            <div class="app-card-body p-4">

                <div class="mb-3">
                    <span class="fw-bold">Statut : </span>
                    @if($statut === 'Actif')
                        <span class="badge bg-success fs-6">Actif</span>
                    @elseif($statut === 'Expire bientôt')
                        <span class="badge bg-warning text-dark fs-6">Expire bientôt</span>
                    @else
                        <span class="badge bg-danger fs-6">Expiré</span>
                    @endif
                </div>

                <div class="mb-3">
                    <span class="fw-bold">Type de contrat : </span>
                    {{ $employer->contract_type ?? '—' }}
                </div>

                <div class="mb-3">
                    <span class="fw-bold">Date de début : </span>
                    {{ $employer->start_date ?? '—' }}
                </div>

                <div class="mb-3">
                    <span class="fw-bold">Date de fin : </span>
                    @if($employer->end_date)
                        {{ $employer->end_date }}
                        @if($jours !== null)
                            @if($jours > 0)
                                <span class="text-muted small">(dans {{ $jours }} jours)</span>
                            @elseif($jours == 0)
                                <span class="text-warning small">(expire aujourd'hui)</span>
                            @else
                                <span class="text-danger small">(expiré depuis {{ abs($jours) }} jours)</span>
                            @endif
                        @endif
                    @else
                        <span class="text-muted">CDI — pas de date de fin</span>
                    @endif
                </div>

                <div class="mb-3">
                    <span class="fw-bold">CNSS : </span>
                    {{ $employer->cnss ?? '—' }}
                </div>

            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="app-card shadow-sm h-100">
            <div class="app-card-header p-3">
                <h4 class="app-card-title">Mes informations</h4>
            </div>
            <div class="app-card-body p-4">
                <div class="mb-3">
                    <span class="fw-bold">Nom complet : </span>
                    {{ $employer->first_name }} {{ $employer->last_name }}
                </div>
                <div class="mb-3">
                    <span class="fw-bold">Email : </span>
                    {{ $employer->email }}
                </div>
                <div class="mb-3">
                    <span class="fw-bold">Téléphone : </span>
                    {{ $employer->phone ?? '—' }}
                </div>
                <div class="mb-3">
                    <span class="fw-bold">Département : </span>
                    {{ $employer->departement->name ?? '—' }}
                </div>
                <div class="mb-3">
                    <span class="fw-bold">RIB : </span>
                    {{ $employer->rib ?? '—' }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection