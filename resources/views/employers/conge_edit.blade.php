@extends('layouts.template')

@section('content')
<div class="container" style="margin-top: 20px;">
    <h1 class="app-page-title">Modifier ma demande</h1>
    <hr class="mb-4">

    <div class="row g-4">
        <div class="col-12 col-md-8">
            <div class="app-card shadow-sm p-4">
                <form method="POST" action="{{ route('employer_space.conges.update', $conge->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Date début</label>
                        <input type="date" id="start_date" name="start_date" class="form-control"
                            value="{{ old('start_date', $conge->start_date) }}" required
                            min="{{ date('Y-m-d') }}">
                        @error('start_date')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date fin</label>
                        <input type="date" id="end_date" name="end_date" class="form-control"
                            value="{{ old('end_date', $conge->end_date) }}" required
                            min="{{ old('start_date', $conge->start_date) }}">
                        @error('end_date')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Type de congé</label>
                        <select name="type" class="form-control" required>
                            <option value="">-- Choisir --</option>
                            <option value="Annuel" {{ old('type', $conge->type) == 'Annuel' ? 'selected' : '' }}>Annuel</option>
                            <option value="Maladie" {{ old('type', $conge->type) == 'Maladie' ? 'selected' : '' }}>Maladie</option>
                            <option value="Maternité" {{ old('type', $conge->type) == 'Maternité' ? 'selected' : '' }}>Maternité</option>
                            <option value="Sans solde" {{ old('type', $conge->type) == 'Sans solde' ? 'selected' : '' }}>Sans solde</option>
                        </select>
                        @error('type')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Motif</label>
                        <textarea name="reason" class="form-control" rows="3">{{ old('reason', $conge->reason) }}</textarea>
                        @error('reason')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn app-btn-primary">Mettre à jour</button>
                    <a href="{{ route('employer_space.conges') }}" class="btn btn-outline-secondary ms-2">Annuler</a>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const dateDebut = document.getElementById('start_date');
    const dateFin = document.getElementById('end_date');

    dateDebut.addEventListener('change', function() {
        if (this.value) {
            dateFin.min = this.value;
            if (dateFin.value && dateFin.value < this.value) {
                dateFin.value = '';
            }
        }
    });
</script>
@endsection