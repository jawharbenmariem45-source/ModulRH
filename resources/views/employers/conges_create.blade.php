@extends('layouts.template')

@section('content')
<div class="container" style="margin-top: 20px;">
    <h1 class="app-page-title mb-3">Demander un Congé</h1>
    <hr class="mb-4">

    {{-- Solde disponible --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="app-card shadow-sm p-3 text-center">
                <div style="font-size: 2rem; font-weight: 700; color: #1a6b8a;">{{ $joursAccordes }}</div>
                <div class="text-muted">Jours accordés</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="app-card shadow-sm p-3 text-center">
                <div style="font-size: 2rem; font-weight: 700; color: #e74c3c;">{{ $congesPris }}</div>
                <div class="text-muted">Jours pris</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="app-card shadow-sm p-3 text-center">
                <div style="font-size: 2rem; font-weight: 700; color: {{ $solde > 5 ? '#27ae60' : '#e67e22' }};">
                    {{ $solde }}
                </div>
                <div class="text-muted">Jours restants</div>
            </div>
        </div>
    </div>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="app-card shadow-sm p-4">
        <form action="{{ route('employer_space.conges.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Date de début</label>
                    <input type="date" name="start_date" class="form-control"
                           value="{{ old('date_debut') }}"
                           min="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Date de fin</label>
                    <input type="date" name="end_date" class="form-control"
                           value="{{ old('date_fin') }}"
                           min="{{ date('Y-m-d') }}" required>
                </div>
            </div>

            {{-- Calcul dynamique des jours --}}
            <div class="mb-3">
                <div class="alert alert-info py-2" id="jours-info" style="display:none;">
                    Nombre de jours demandés : <strong id="jours-count">0</strong>
                    <span id="jours-warning" class="text-danger ms-2" style="display:none;">
                        ⚠️ Dépasse votre solde de {{ $solde }} jour(s) !
                    </span>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Type de congé</label>
                <select name="type" id="type-conge" class="form-control" required>
                    <option value="">-- Choisir --</option>
                    <option value="Congé Annuel" {{ old('type') == 'Congé Annuel' ? 'selected' : '' }}>Congé Annuel</option>
                    <option value="Maladie" {{ old('type') == 'Maladie' ? 'selected' : '' }}>Maladie</option>
                    <option value="Maternité" {{ old('type') == 'Maternité' ? 'selected' : '' }}>Maternité</option>
                    <option value="Sans solde" {{ old('type') == 'Sans solde' ? 'selected' : '' }}>Sans solde</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Motif</label>
                <textarea name="reason" class="form-control" rows="3"
                          placeholder="Décrivez le motif de votre congé">{{ old('motif') }}</textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary" id="btn-submit">Envoyer la demande</button>
                <a href="{{ route('employer_space.conges') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
    const solde      = {{ $solde }};
    const dateDebut  = document.querySelector('[name="start_date"]');
    const dateFin    = document.querySelector('[name="end_date"]');
    const joursInfo  = document.getElementById('jours-info');
    const joursCount = document.getElementById('jours-count');
    const joursWarn  = document.getElementById('jours-warning');
    const btnSubmit  = document.getElementById('btn-submit');
    const typeConge  = document.getElementById('type-conge');

    function calculerJours() {
        if (!dateDebut.value || !dateFin.value) return;

        const d1   = new Date(dateDebut.value);
        const d2   = new Date(dateFin.value);
        const diff = Math.round((d2 - d1) / (1000 * 60 * 60 * 24)) + 1;

        if (diff <= 0) return;

        joursInfo.style.display = 'block';
        joursCount.textContent  = diff;

        const isMedical = ['Maladie', 'Maternité'].includes(typeConge.value);

        if (!isMedical && diff > solde) {
            joursWarn.style.display = 'inline';
            btnSubmit.disabled      = true;
        } else {
            joursWarn.style.display = 'none';
            btnSubmit.disabled      = false;
        }
    }

    dateDebut.addEventListener('change', calculerJours);
    dateFin.addEventListener('change', calculerJours);
    typeConge.addEventListener('change', calculerJours);
</script>

@endsection