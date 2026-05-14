@extends('layouts.template')

@section('content')
<h1 class="app-page-title">Configurations</h1>
<hr class="mb-4">

@if(session('success_message'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success_message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    <div class="col-12 col-md-4">
        <h3 class="section-title">Paramètres</h3>
        <div class="section-intro">Modifiez ici les paramètres de l'entreprise</div>
        @if($company)
            <div class="mt-3 text-muted small">
                <i class="fas fa-building me-1"></i> {{ $company->name }}
            </div>
        @endif
    </div>

    <div class="col-12 col-md-8">
        <div class="app-card app-card-settings shadow-sm p-4">
            <div class="app-card-body">

                @if(!$company)
                    <div class="alert alert-warning">
                        Aucune entreprise associée à votre compte.
                    </div>
                @else
                    <form method="POST" action="{{ route('configurations.save') }}">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-bold">Jour de paiement <small class="text-muted fw-normal">(1 - 31)</small></label>
                            <input type="number"
                                   name="payment_date"
                                   class="form-control @error('payment_date') is-invalid @enderror"
                                   min="1" max="31"
                                   value="{{ old('payment_date', $company->payment_date ?? 30) }}">
                            @error('payment_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Régime horaire</label>
                            <select name="work_schedule" class="form-select @error('work_schedule') is-invalid @enderror">
                                <option value="40h" {{ old('work_schedule', $company->work_schedule ?? '40h') == '40h' ? 'selected' : '' }}>40 heures / semaine</option>
                                <option value="48h" {{ old('work_schedule', $company->work_schedule ?? '40h') == '48h' ? 'selected' : '' }}>48 heures / semaine</option>
                            </select>
                            @error('work_schedule')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn app-btn-primary">
                            <i class="fas fa-save me-1"></i> Enregistrer
                        </button>

                    </form>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection