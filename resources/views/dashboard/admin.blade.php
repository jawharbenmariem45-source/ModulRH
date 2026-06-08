@extends('layouts.template')

@section('content')
<h1 class="app-page-title mb-4">Dashboard Administrateur</h1>

@if($contratsAlertes > 0)
<div class="alert alert-danger alert-dismissible fade show">
    ⚠️ <b>{{ $contratsAlertes }} contrat(s)</b> expirent dans les 7 prochains jours !
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4 mb-4">
    <div class="col-6 col-lg-3">
        <div class="app-card app-card-stat shadow-sm h-100">
            <div class="app-card-body p-3 p-lg-4">
                <h4 class="stats-type mb-1">Companies</h4>
                <div class="stats-figure">{{ $totalCompanies }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="app-card app-card-stat shadow-sm h-100">
            <div class="app-card-body p-3 p-lg-4">
                <h4 class="stats-type mb-1">Total Employers</h4>
                <div class="stats-figure">{{ $totalEmployers }}</div>
            </div>
        </div>
    </div>

</div>

<div class="app-card shadow-sm mb-5">
    <div class="app-card-header p-3">
        <h4 class="app-card-title mb-0">Répartition par Company</h4>
    </div>
    <div class="app-card-body">
        <div class="table-responsive">
            <table class="table app-table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Company</th>
                        <th>Date paiement</th>
                        <th>Régime</th>
                        <th>Total Employers</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($companies as $company)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $company->name }}</strong></td>
                        <td>{{ $company->payment_date ?? '-' }}</td>
                        <td>{{ $company->work_schedule ?? '-' }}</td>
                        <td>
                            <span class="badge" style="background:#19a891;color:white">
                                {{ $company->total_employers }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection