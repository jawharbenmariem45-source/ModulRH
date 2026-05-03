@extends('layouts.template')

@section('content')
<h1 class="app-page-title">Configurations</h1>
<hr class="mb-4">

@if(session('success_message'))
    <div class="alert alert-success">{{ session('success_message') }}</div>
@endif

<div class="row g-4">
    <div class="col-12 col-md-4">
        <h3 class="section-title">Paramètres</h3>
        <div class="section-intro">Modifiez ici les paramètres de l'application</div>
    </div>

    <div class="col-12 col-md-8">
        <div class="app-card app-card-settings shadow-sm p-4">
            <div class="app-card-body">

                {{-- Jour de paiement --}}
                <form method="POST" action="{{ route('configurations.save') }}" class="mb-4">
                    @csrf
                    <label class="form-label">Jour de paiement <small class="text-muted">(1 - 31)</small></label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="number" name="PAYMENT_DATEE" class="form-control"
                            min="1" max="31" value="{{ $configs['PAYMENT_DATEE'] }}">
                        <button type="submit" class="btn app-btn-primary">Enregistrer</button>
                    </div>
                    @error('PAYMENT_DATEE')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </form>

                {{-- Régime horaire --}}
                <form method="POST" action="{{ route('configurations.save') }}" class="mb-4">
                    @csrf
                    <label class="form-label">Régime horaire</label>
                    <div class="d-flex align-items-center gap-2">
                        <select name="REGIME_HORAIRE" class="form-control">
                            <option value="40h" {{ $configs['REGIME_HORAIRE'] == '40h' ? 'selected' : '' }}>40h</option>
                            <option value="48h" {{ $configs['REGIME_HORAIRE'] == '48h' ? 'selected' : '' }}>48h</option>
                        </select>
                        <button type="submit" class="btn app-btn-primary">Enregistrer</button>
                    </div>
                    @error('REGIME_HORAIRE')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </form>

            </div>
        </div>
    </div>
</div>
@endsection