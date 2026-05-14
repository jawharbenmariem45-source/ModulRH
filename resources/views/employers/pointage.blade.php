@extends('layouts.template')

@section('content')
@php
use Carbon\Carbon;
$startOfWeek = Carbon::now()->startOfWeek();
$today = Carbon::today()->toDateString();
@endphp

<style>
.week-row {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin: 40px 0 20px 0;
    flex-wrap: wrap;
}
.day-card {
    flex: 1;
    max-width: 130px;
    background: linear-gradient(145deg, #ffffff, #eaf1ff);
    border-radius: 12px;
    padding: 12px;
    text-align: center;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
    text-decoration: none;
    color: inherit;
}
.day-card.selected {
    border: 2px solid #0069d9;
    box-shadow: 0 6px 15px rgba(0,0,0,0.2);
    background: linear-gradient(145deg, #d5e4ff, #ffffff);
}
.day-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.15);
}
.bottom-section {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    gap: 40px;
    flex-wrap: wrap;
    margin-top: 40px;
}
.pointage-column {
    flex: 1 1 300px;
    max-width: 350px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    overflow: hidden;
}
.card-header-pointage {
    border-radius: 12px 12px 0 0;
    padding: 12px;
    font-weight: 600;
    font-size: 1rem;
}
.btn-pointage {
    border-radius: 10px;
    font-weight: 600;
    padding: 10px;
    transition: 0.2s;
    width: 100%;
}
.btn-pointage:hover:not(:disabled) {
    transform: scale(1.03);
}
.btn-pointage:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
.time-label {
    font-size: 0.85rem;
    color: #555;
    margin-top: 4px;
    text-align: center;
}
</style>

<h1 class="app-page-title">Pointage</h1>
<hr class="mb-4">

@if(session('status'))
    <div class="alert alert-info text-center">{{ session('status') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger text-center">{{ session('error') }}</div>
@endif

{{-- Semaine --}}
<div class="week-row">
    @for($i = 0; $i < 5; $i++)
        @php
            $date = $startOfWeek->copy()->addDays($i);
            $dateString = $date->format('Y-m-d');
        @endphp
        <a href="{{ route('employer_space.pointage.index', ['date' => $dateString]) }}"
           class="day-card {{ $dateString == $selectedDate ? 'selected' : '' }}">
            <div class="fw-bold fs-6">{{ $date->locale('fr')->translatedFormat('D d') }}</div>
        </a>
    @endfor
</div>

@php
    $isToday  = $selectedDate == $today;
    $ciDone   = $attendance && $attendance->morning_check_in;
    $coDone   = $attendance && $attendance->morning_check_out;
    $ciPMDone = $attendance && $attendance->afternoon_check_in;
    $coPMDone = $attendance && $attendance->afternoon_check_out;
@endphp

<div class="bottom-section">

    {{-- Matin --}}
    <div class="pointage-column">
        <div class="card-header-pointage text-center text-white" style="background-color:#20c997;">Matin</div>
        <div class="p-4 bg-light">
            <div class="d-flex flex-column gap-3">

                <form method="POST" action="{{ route('employer_space.pointage.check_in_matin') }}">
                    @csrf
                    <button class="btn-pointage btn {{ $ciDone ? 'btn-success' : 'btn-outline-success' }}"
                            {{ $ciDone || !$isToday ? 'disabled' : '' }}>
                        {{ $ciDone ? '✅ Entrée enregistrée' : '⏹ Entrée Matin' }}
                    </button>
                    @if($ciDone)
                        <div class="time-label">🕒 {{ Carbon::parse($attendance->morning_check_in)->format('H:i:s') }}</div>
                    @endif
                </form>

                <form method="POST" action="{{ route('employer_space.pointage.check_out_matin') }}">
                    @csrf
                    <button class="btn-pointage btn {{ $coDone ? 'btn-success' : 'btn-outline-danger' }}"
                            {{ $coDone || !$isToday ? 'disabled' : '' }}>
                        {{ $coDone ? '✅ Sortie enregistrée' : '⏹ Sortie Matin' }}
                    </button>
                    @if($coDone)
                        <div class="time-label">🕒 {{ Carbon::parse($attendance->morning_check_out)->format('H:i:s') }}</div>
                    @endif
                </form>

            </div>
        </div>
    </div>

    {{-- Après-midi --}}
    <div class="pointage-column">
        <div class="card-header-pointage text-center text-white" style="background-color:#007bff;">Après-midi</div>
        <div class="p-4 bg-light">
            <div class="d-flex flex-column gap-3">

                <form method="POST" action="{{ route('employer_space.pointage.check_in_apres_midi') }}">
                    @csrf
                    <button class="btn-pointage btn {{ $ciPMDone ? 'btn-success' : 'btn-outline-success' }}"
                            {{ $ciPMDone || !$isToday ? 'disabled' : '' }}>
                        {{ $ciPMDone ? '✅ Entrée enregistrée' : '⏹ Entrée Après-midi' }}
                    </button>
                    @if($ciPMDone)
                        <div class="time-label">🕒 {{ Carbon::parse($attendance->afternoon_check_in)->format('H:i:s') }}</div>
                    @endif
                </form>

                <form method="POST" action="{{ route('employer_space.pointage.check_out_apres_midi') }}">
                    @csrf
                    <button class="btn-pointage btn {{ $coPMDone ? 'btn-success' : 'btn-outline-danger' }}"
                            {{ $coPMDone || !$isToday ? 'disabled' : '' }}>
                        {{ $coPMDone ? '✅ Sortie enregistrée' : '⏹ Sortie Après-midi' }}
                    </button>
                    @if($coPMDone)
                        <div class="time-label">🕒 {{ Carbon::parse($attendance->afternoon_check_out)->format('H:i:s') }}</div>
                    @endif
                </form>

            </div>
        </div>
    </div>

</div>

{{-- Historique du mois --}}
<div class="app-card shadow-sm mt-5 p-4">
    <h4 class="mb-3">Historique du mois</h4>
    <div class="table-responsive">
        <table class="table app-table-hover mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Entrée Matin</th>
                    <th>Sortie Matin</th>
                    <th>Entrée Après-midi</th>
                    <th>Sortie Après-midi</th>
                    <th>Heures</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                @forelse($historique as $h)
                <tr>
                    <td>{{ Carbon::parse($h->date)->format('d/m/Y') }}</td>
                    <td>{{ $h->morning_check_in ? Carbon::parse($h->morning_check_in)->format('H:i') : '-' }}</td>
                    <td>{{ $h->morning_check_out ? Carbon::parse($h->morning_check_out)->format('H:i') : '-' }}</td>
                    <td>{{ $h->afternoon_check_in ? Carbon::parse($h->afternoon_check_in)->format('H:i') : '-' }}</td>
                    <td>{{ $h->afternoon_check_out ? Carbon::parse($h->afternoon_check_out)->format('H:i') : '-' }}</td>
                    <td>
                        @php
                            $heures = null;
                            try {
                                $debut = $h->morning_check_in ? Carbon::parse($h->morning_check_in) : null;
                                $fin   = $h->afternoon_check_out ? Carbon::parse($h->afternoon_check_out) : null;
                                $heures = ($debut && $fin) ? round($debut->diffInMinutes($fin) / 60, 2) : null;
                            } catch (\Exception $e) {}
                        @endphp
                        {{ $heures !== null ? $heures . ' h' : '- h' }}
                    </td>
                    <td>
                        @if($h->status === 'present')
                            <span class="badge bg-success">Présent</span>
                        @elseif($h->status === 'absent')
                            <span class="badge bg-danger">Absent</span>
                        @elseif($h->status === 'late')
                            <span class="badge bg-warning text-dark">En retard</span>
                        @elseif($h->status === 'on_leave')
                            <span class="badge bg-info">En congé</span>
                        @else
                            <span class="badge bg-secondary">{{ $h->status }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">Aucun pointage ce mois.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection