@extends('layouts.template')

@section('content')
<h1 class="app-page-title">Dashboard</h1>

<div class="row g-4 mb-4">
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
                <h4 class="stats-type mb-1">Congés en attente</h4>
                <div class="stats-figure {{ $congesEnAttente > 0 ? 'text-warning' : '' }}">
                    {{ $congesEnAttente }}
                </div>
            </div>
            <a class="app-card-link-mask" href="{{ route('conge.index') }}"></a>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="app-card app-card-stat shadow-sm h-100">
            <div class="app-card-body p-3 p-lg-4">
                <h4 class="stats-type mb-1">Congés Approuvés</h4>
                <div class="stats-figure text-success">{{ $congesApprouves }}</div>
            </div>
            <a class="app-card-link-mask" href="#"></a>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="app-card app-card-stat shadow-sm h-100">
            <div class="app-card-body p-3 p-lg-4">
                <h4 class="stats-type mb-1">Congés Refusés</h4>
                <div class="stats-figure text-danger">{{ $congesRefuses }}</div>
            </div>
            <a class="app-card-link-mask" href="#"></a>
        </div>
    </div>
</div>
@endsection