@extends('layouts.template')

@section('content')
<h1 class="app-page-title mb-0">Mes Paiements</h1>
<hr class="mb-4">

@if(Session::get('success_message'))
    <div class="alert alert-success">{{ Session::get('success_message') }}</div>
@endif

{{-- Filtres --}}
<form method="GET" action="{{ route('employer_space.paiements') }}" class="row g-2 mb-4 align-items-center">
    <div class="col-auto">
        <select name="month" class="form-select">
            <option value="">Tous les mois</option>
            @foreach(['JANVIER','FEVRIER','MARS','AVRIL','MAI','JUIN','JUILLET','AOUT','SEPTEMBRE','OCTOBRE','NOVEMBRE','DECEMBRE'] as $m)
                <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ $m }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <select name="year" class="form-select">
            <option value="">Toutes les années</option>
            @for($y = date('Y'); $y >= date('Y') - 3; $y--)
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
                        <th>Reference</th>
                        <th>Montant</th>
                        <th>Date</th>
                        <th>Mois</th>
                        <th>Année</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->reference }}</td>
                        <td>{{ $payment->amount }} DT</td>
                        <td>{{ date('d-m-Y', strtotime($payment->done_time)) }}</td>
                        <td>{{ $payment->month }}</td>
                        <td>{{ $payment->year }}</td>
                        <td><span class="badge bg-success">{{ $payment->status }}</span></td>
                        <td>
                            <a href="{{ route('employer_space.paiements.preview', $payment->id) }}"
                               title="Voir PDF"
                               target="_blank"
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
                        <td colspan="7" class="text-center text-muted" style="padding: 3rem;">
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