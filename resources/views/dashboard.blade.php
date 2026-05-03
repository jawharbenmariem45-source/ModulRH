@extends('layouts.template')

@section('content')
<h1 class="app-page-title">Dashboard</h1>

<div class="row mt-2 mb-2 p-2">
    @if($paymentNotification)
    <div class="alert alert-warning"><b>Attention : </b>{{ $paymentNotification }}</div>
    @endif
    @if($contratsAlertes > 0)
    <div class="alert alert-danger">
        ⚠️ <b>{{ $contratsAlertes }} contrat(s)</b> expirent dans les 7 prochains jours !
        <a href="{{ route('contrat.index') }}" class="alert-link">Voir les contrats</a>
    </div>
    @endif
</div>

<div class="row g-4 mb-4">
    <div class="col-6 col-lg-3">
        <div class="app-card app-card-stat shadow-sm h-100">
            <div class="app-card-body p-3 p-lg-4">
                <h4 class="stats-type mb-1">Total Departements</h4>
                <div class="stats-figure">{{ $totalDepartements }}</div>
            </div>
            <a class="app-card-link-mask" href="#"></a>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="app-card app-card-stat shadow-sm h-100">
            <div class="app-card-body p-3 p-lg-4">
                <h4 class="stats-type mb-1">Total Employers</h4>
                <div class="stats-figure">{{ $totalEmployers }}</div>
            </div>
            <a class="app-card-link-mask" href="#"></a>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="app-card app-card-stat shadow-sm h-100">
            <div class="app-card-body p-3 p-lg-4">
                <h4 class="stats-type mb-1">Total Administrateurs</h4>
                <div class="stats-figure">{{ $totalAdministrateurs }}</div>
            </div>
            <a class="app-card-link-mask" href="#"></a>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="app-card app-card-stat shadow-sm h-100">
            <div class="app-card-body p-3 p-lg-4">
                <h4 class="stats-type mb-1">Contrats Alertes</h4>
                <div class="stats-figure {{ $contratsAlertes > 0 ? 'text-danger' : '' }}">
                    {{ $contratsAlertes }}
                </div>
            </div>
            <a class="app-card-link-mask" href="{{ route('contrat.index') }}"></a>
        </div>
    </div>
</div>
@endsection