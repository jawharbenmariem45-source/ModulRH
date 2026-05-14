@extends('layouts.template')

@section('content')
<h1 class="app-page-title">Modifier le contrat</h1>
<hr class="mb-4">

<div class="row g-4">
    <div class="col-12 col-md-4">
        <h3 class="section-title">{{ $employer->last_name }} {{ $employer->first_name }}</h3>
        <div class="section-intro">Modifier les informations du contrat</div>
    </div>
    <div class="col-12 col-md-8">
        <div class="app-card app-card-settings shadow-sm p-4">
            <div class="app-card-body">
                <form method="POST" action="{{ route('contrat.update', $employer->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Type de contrat</label>
                            <select name="type_contrat" class="form-control">
                                @foreach(['CDI','CDD','CIVP','Karama'] as $type)
                                <option value="{{ $type }}" {{ $employer->contract_type == $type ? 'selected' : '' }}>
                                    {{ $type }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Numéro CNSS</label>
                            <input type="text" name="cnss" class="form-control"
                                value="{{ old('cnss', $employer->cnss) }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">RIB bancaire</label>
                        <input type="text" name="rib" class="form-control"
                            value="{{ old('rib', $employer->rib) }}">
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Date de début</label>
                            <input type="date" name="date_debut" class="form-control"
                                value="{{ old('date_debut', $employer->start_date) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date de fin <small class="text-muted">(optionnel)</small></label>
                            <input type="date" name="date_fin" class="form-control"
                                value="{{ old('date_fin', $employer->end_date) }}">
                        </div>
                    </div>

                    <button type="submit" class="btn app-btn-primary">Enregistrer</button>
                    <a href="{{ route('contrat.index') }}" class="btn btn-outline-secondary ms-2">Annuler</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection