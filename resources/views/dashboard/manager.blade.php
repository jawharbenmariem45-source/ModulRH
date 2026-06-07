@extends('layouts.template')

@section('content')

@php
    $company    = auth()->user()->company ?? null;
    $nomCompany = $company?->name ?? 'SummitRise';
    $baseManager = 'https://app.powerbi.com/reportEmbed?reportId=d7ccda01-8061-4747-afac-b19ffc24fad8&autoAuth=true&ctid=b7bd4715-4217-48c7-919e-2ea97f592fa7';
    $filtre      = '&filterPaneEnabled=false&navContentPaneEnabled=false&filter=dim_employer/nom_company eq \'' . urlencode($nomCompany) . '\'';
    $urlManager  = $baseManager . $filtre;
@endphp

<iframe
    title="Dashboard Manager"
    src="{{ $urlManager }}"
    frameborder="0"
    allowFullScreen="true"
    style="width:100%; height:calc(100vh - 120px); min-height:700px; display:block; border:none;">
</iframe>

@endsection