@extends('layouts.template')

@section('content')

@php
    $company    = auth()->user()->company ?? null;
    $nomCompany = $company?->name ?? 'SummitRise';
    $baseGlobal = 'https://app.powerbi.com/reportEmbed?reportId=0643e799-939f-40b1-a5ba-be88ef722437&autoAuth=true&ctid=b7bd4715-4217-48c7-919e-2ea97f592fa7';
    $baseDetail = 'https://app.powerbi.com/reportEmbed?reportId=fea33fef-12d4-44ba-88f9-0f1429d34768&autoAuth=true&ctid=b7bd4715-4217-48c7-919e-2ea97f592fa7';
    $filtre     = '&filterPaneEnabled=false&navContentPaneEnabled=false&filter=dim_employer/nom_company eq \'' . urlencode($nomCompany) . '\'';
    $urlGlobal  = $baseGlobal . $filtre;
    $urlDetail  = $baseDetail . $filtre;
@endphp

<div class="d-flex align-items-center justify-content-between mb-3">
    <ul class="nav nav-tabs border-0" id="dashboardTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="global-tab"
                data-bs-toggle="tab" data-bs-target="#global"
                type="button" role="tab">
                📊 Vue Globale
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="detaille-tab"
                data-bs-toggle="tab" data-bs-target="#detaille"
                type="button" role="tab">
                🔍 Vue Détaillée
            </button>
        </li>
    </ul>
</div>

<div class="tab-content" id="dashboardTabsContent">

    {{-- Vue Globale --}}
    <div class="tab-pane fade show active" id="global" role="tabpanel">
        <iframe
            title="Dashboard Rh"
            src="{{ $urlGlobal }}"
            frameborder="0"
            allowFullScreen="true"
            style="width:100%; height:calc(100vh - 120px); min-height:700px; display:block; border:none;">

        </iframe>
    </div>

    {{-- Vue Détaillée --}}
    <div class="tab-pane fade" id="detaille" role="tabpanel">
        <iframe
            title="Dashboard Rh Détaillé"
            src="{{ $urlDetail }}"
            frameborder="0"
            allowFullScreen="true"
            style="width:100%; height:calc(100vh - 120px); min-height:700px; display:block; border:none;">
        </iframe>
    </div>

</div>

@endsection