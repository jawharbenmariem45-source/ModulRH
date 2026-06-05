@extends('layouts.template')

@section('content')
<h1 class="app-page-title mb-0">Mes Paiements</h1>
<hr class="mb-4">

@if(Session::get('success_message'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ Session::get('success_message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Filtres --}}
<form method="GET" action="{{ route('employer_space.paiements') }}" class="row g-2 mb-4 align-items-center">
    <div class="col-auto">
        <select name="month" class="form-select">
            <option value="">Tous les mois</option>
            @foreach([1=>'JANVIER',2=>'FEVRIER',3=>'MARS',4=>'AVRIL',5=>'MAI',6=>'JUIN',7=>'JUILLET',8=>'AOUT',9=>'SEPTEMBRE',10=>'OCTOBRE',11=>'NOVEMBRE',12=>'DECEMBRE'] as $num => $nom)
                <option value="{{ $num }}" {{ request('month') == $num ? 'selected' : '' }}>{{ $nom }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <select name="year" class="form-select">
            <option value="">Toutes les années</option>
            @for($y = date('Y') + 1; $y >= date('Y') - 5; $y--)
                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn app-btn-secondary">Filtrer</button>
    </div>
    @if(request('month') || request('year'))
    <div class="col-auto">
        <a href="{{ route('employer_space.paiements') }}" class="btn btn-outline-secondary">Réinitialiser</a>
    </div>
    @endif
</form>

<div class="app-card app-card-orders-table shadow-sm mb-5">
    <div class="app-card-body">
        <div class="table-responsive">
            <table class="table app-table-hover mb-0 text-left">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Référence</th>
                        <th>Montant</th>
                        <th>Date</th>
                        <th>Mois</th>
                        <th>Année</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $moisNoms = [
                            1=>'JANVIER',2=>'FEVRIER',3=>'MARS',4=>'AVRIL',
                            5=>'MAI',6=>'JUIN',7=>'JUILLET',8=>'AOUT',
                            9=>'SEPTEMBRE',10=>'OCTOBRE',11=>'NOVEMBRE',12=>'DECEMBRE'
                        ];
                    @endphp
                    @forelse($payments as $payment)
                    <tr>
                        <td>{{ ($payments->currentPage() - 1) * $payments->perPage() + $loop->iteration }}</td>
                        <td>{{ $payment->reference }}</td>
                        <td>{{ number_format($payment->amount, 3, '.', ' ') }} DT</td>
                        <td>{{ date('d/m/Y', strtotime($payment->done_time)) }}</td>
                        <td>{{ $moisNoms[$payment->month] ?? $payment->month }}</td>
                        <td>{{ $payment->year }}</td>
                        <td><span class="badge bg-success">{{ $payment->status }}</span></td>
                        <td>
                            <a href="{{ route('employer_space.paiements.preview', $payment->id) }}"
                               title="Voir PDF" target="_blank"
                               class="btn btn-sm btn-outline-info me-1">
                                <i class="fa fa-eye"></i>
                            </a>
                            <a href="{{ route('employer_space.paiements.pdf', $payment->id) }}"
                               title="Télécharger"
                               class="btn btn-sm btn-outline-success">
                                <i class="fa fa-download"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted" style="padding: 3rem;">
                            Aucune transaction effectuée
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<nav class="app-pagination">
    {{ $payments->links() }}
</nav>

@endsection