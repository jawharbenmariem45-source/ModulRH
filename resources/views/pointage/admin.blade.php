@extends('layouts.template')
@section('content')
@php use Carbon\Carbon; @endphp

<div class="row g-3 mb-4 align-items-center justify-content-between">
    <div class="col-auto">
        <h1 class="app-page-title mb-0">Pointages des employés</h1>
    </div>
</div>

@if(session('success_message'))
    <div class="alert alert-success">{{ session('success_message') }}</div>
@endif
@if(session('error_message'))
    <div class="alert alert-danger">{{ session('error_message') }}</div>
@endif

<form method="GET" action="{{ route('pointage.admin') }}" class="row g-2 mb-4 align-items-center">
    <div class="col-auto">
        <label class="form-label fw-bold mb-1">Date</label>
        <input type="date" name="date" class="form-control" value="{{ $selectedDate }}">
    </div>
    <div class="col-auto">
        <label class="form-label fw-bold mb-1">Rechercher un employé</label>
        <input type="text" name="employer_name" class="form-control"
               placeholder="Nom ou prénom..."
               value="{{ $selectedEmployerName ?? '' }}">
    </div>
    <div class="col-auto" style="margin-top: 24px;">
        <button type="submit" class="btn app-btn-secondary">Filtrer</button>
    </div>
    @if(($selectedEmployerName ?? '') || $selectedDate != Carbon::today()->toDateString())
    <div class="col-auto" style="margin-top: 24px;">
        <a href="{{ route('pointage.admin') }}" class="btn btn-outline-secondary">Réinitialiser</a>
    </div>
    @endif
</form>

@if(count($attendances) > 0)
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="app-card shadow-sm p-3 text-center">
            <div class="fs-4 fw-bold text-success">
                {{ collect($attendances)->filter(fn($a) => $a['entree'] && $a['sortie'])->count() }}
            </div>
            <div class="text-muted small">Présents (entrée + sortie)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="app-card shadow-sm p-3 text-center">
            <div class="fs-4 fw-bold text-warning">
                {{ collect($attendances)->filter(fn($a) => $a['entree'] && !$a['sortie'])->count() }}
            </div>
            <div class="text-muted small">Entrée sans sortie</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="app-card shadow-sm p-3 text-center">
            <div class="fs-4 fw-bold text-danger">
                {{ collect($attendances)->filter(fn($a) => !$a['entree'] && !$a['sortie'])->count() }}
            </div>
            <div class="text-muted small">Absents</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="app-card shadow-sm p-3 text-center">
            <div class="fs-4 fw-bold text-info">
                {{ count($attendances) }}
            </div>
            <div class="text-muted small">Total</div>
        </div>
    </div>
</div>
@endif

<div class="app-card app-card-orders-table shadow-sm mb-5">
    <div class="app-card-body">
        <div class="table-responsive">
            <table class="table app-table-hover mb-0 text-left">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Employé</th>
                        <th>Département</th>
                        <th>Entrée</th>
                        <th>Sortie</th>
                        <th>Heures</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $i => $row)
                    @php
                        $user   = $row['user'];
                        $entree = $row['entree'];
                        $sortie = $row['sortie'];

                        $heures = null;
                        if ($entree && $sortie) {
                            try {
                                $heures = round(
                                    Carbon::parse($entree->pointage_at)
                                        ->diffInMinutes(Carbon::parse($sortie->pointage_at)) / 60,
                                    2
                                );
                            } catch (\Exception $e) {}
                        }

                        if ($entree && $sortie)      $statut = ['label' => 'Présent',           'class' => 'bg-success'];
                        elseif ($entree && !$sortie)  $statut = ['label' => 'Entrée sans sortie', 'class' => 'bg-warning text-dark'];
                        else                          $statut = ['label' => 'Absent',             'class' => 'bg-danger'];
                    @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><strong>{{ $user->last_name ?? '-' }} {{ $user->first_name ?? '' }}</strong></td>
                        <td>{{ $user->departement->name ?? '-' }}</td>
                        <td>
                            @if($entree)
                                <span class="badge bg-success">
                                    {{ Carbon::parse($entree->pointage_at)->format('H:i') }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($sortie)
                                <span class="badge bg-warning text-dark">
                                    {{ Carbon::parse($sortie->pointage_at)->format('H:i') }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($heures !== null)
                                <strong>{{ $heures }} h</strong>
                            @else
                                <span class="text-muted">- h</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $statut['class'] }}">{{ $statut['label'] }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted" style="padding: 3rem;">
                            Aucun pointage pour cette date.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection