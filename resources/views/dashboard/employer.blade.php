@extends('layouts.template')

@section('content')

@php
    $company    = auth()->user()->company ?? null;
    $nomCompany = $company?->name ?? 'SummitRise';
    $baseEmployer = 'https://app.powerbi.com/reportEmbed?reportId=9c4a2902-4135-426b-bd1c-74a778573600&autoAuth=true&ctid=b7bd4715-4217-48c7-919e-2ea97f592fa7';
    $filtre      = '&filterPaneEnabled=false&navContentPaneEnabled=false&filter=dim_employer/nom_company eq \'' . urlencode($nomCompany) . '\'';
    $urlEmployer  = $baseEmployer . $filtre;
@endphp

<iframe
    title="Dashboard Employer"
    src="{{ $urlEmployer }}"
    frameborder="0"
    allowFullScreen="true"
    style="width:100%; height:calc(100vh - 120px); min-height:700px; display:block; border:none;">
</iframe>

@endsection