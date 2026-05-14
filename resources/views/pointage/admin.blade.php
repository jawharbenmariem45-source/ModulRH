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

@if($attendances->count() > 0)
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="app-card shadow-sm p-3 text-center">
            <div class="fs-4 fw-bold text-success">
                {{ $attendances->filter(fn($a) => strtolower(trim($a->status)) === 'present')->count() }}
            </div>
            <div class="text-muted small">Présents</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="app-card shadow-sm p-3 text-center">
            <div class="fs-4 fw-bold text-danger">
                {{ $attendances->filter(fn($a) => strtolower(trim($a->status)) === 'absent')->count() }}
            </div>
            <div class="text-muted small">Absents</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="app-card shadow-sm p-3 text-center">
            <div class="fs-4 fw-bold text-warning">
                {{ $attendances->filter(fn($a) => strtolower(trim($a->status)) === 'late')->count() }}
            </div>
            <div class="text-muted small">En retard</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="app-card shadow-sm p-3 text-center">
            <div class="fs-4 fw-bold text-info">
                {{ $attendances->filter(fn($a) => strtolower(trim($a->status)) === 'on_leave')->count() }}
            </div>
            <div class="text-muted small">En congé</div>
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
                        <th>Check-in Matin</th>
                        <th>Check-out Matin</th>
                        <th>Check-in AM</th>
                        <th>Check-out AM</th>
                        <th>Heures</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $attendance)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <strong>{{ $attendance->employer->last_name ?? '-' }} {{ $attendance->employer->first_name ?? '' }}</strong>
                        </td>
                        <td>{{ $attendance->employer->departement->name ?? '-' }}</td>

                        {{-- Check-in Matin --}}
                        <td>
                            @if($attendance->morning_check_in)
                                @php
                                    try { $time = Carbon::parse($attendance->morning_check_in)->format('H:i'); }
                                    catch (\Exception $e) { $time = $attendance->morning_check_in; }
                                @endphp
                                <span class="badge bg-success">{{ $time }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- Check-out Matin --}}
                        <td>
                            @if($attendance->morning_check_out)
                                @php
                                    try { $time = Carbon::parse($attendance->morning_check_out)->format('H:i'); }
                                    catch (\Exception $e) { $time = $attendance->morning_check_out; }
                                @endphp
                                <span class="badge bg-warning text-dark">{{ $time }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- Check-in Après-midi --}}
                        <td>
                            @if($attendance->afternoon_check_in)
                                @php
                                    try { $time = Carbon::parse($attendance->afternoon_check_in)->format('H:i'); }
                                    catch (\Exception $e) { $time = $attendance->afternoon_check_in; }
                                @endphp
                                <span class="badge bg-success">{{ $time }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- Check-out Après-midi --}}
                        <td>
                            @if($attendance->afternoon_check_out)
                                @php
                                    try { $time = Carbon::parse($attendance->afternoon_check_out)->format('H:i'); }
                                    catch (\Exception $e) { $time = $attendance->afternoon_check_out; }
                                @endphp
                                <span class="badge bg-warning text-dark">{{ $time }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        {{-- Heures totales --}}
                        <td>
                            @php
                                try {
                                    $checkIn  = $attendance->morning_check_in
                                        ? Carbon::parse($attendance->morning_check_in) : null;
                                    $checkOut = $attendance->afternoon_check_out
                                        ? Carbon::parse($attendance->afternoon_check_out) : null;
                                    $heures = ($checkIn && $checkOut)
                                        ? round($checkIn->diffInMinutes($checkOut) / 60, 2)
                                        : null;
                                } catch (\Exception $e) {
                                    $heures = null;
                                }
                            @endphp
                            @if($heures !== null)
                                <strong>{{ $heures }} h</strong>
                            @else
                                <span class="text-muted">- h</span>
                            @endif
                        </td>

                        {{-- Statut --}}
                        <td>
                            @php $status = strtolower(trim($attendance->status ?? '')); @endphp
                            @if($status === 'present')
                                <span class="badge bg-success">Présent</span>
                            @elseif($status === 'absent')
                                <span class="badge bg-danger">Absent</span>
                            @elseif($status === 'late')
                                <span class="badge bg-warning text-dark">En retard</span>
                            @elseif($status === 'on_leave')
                                <span class="badge bg-info">En congé</span>
                            @else
                                <span class="badge bg-secondary">{{ $attendance->status ?? 'N/A' }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted" style="padding: 3rem;">
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