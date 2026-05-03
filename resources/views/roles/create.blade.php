@extends('layouts.template')

@section('content')
<h1 class="app-page-title">Gestion des Rôles</h1>
<hr class="mb-4">

<div class="row g-4 settings-section">
    <div class="col-12 col-md-4">
        <h3 class="section-title">Ajouter un membre</h3>
        <div class="section-intro">Définissez le nom, l'email et le rôle du nouveau membre.</div>
    </div>
    <div class="col-12 col-md-8">
        <div class="app-card app-card-settings shadow-sm p-4">
            <div class="app-card-body">
                <form class="settings-form" method="POST" action="{{ route('administrateurs.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Nom complet</label>
                        <input type="text" class="form-control" name="name"
                            placeholder="Entrer le nom complet"
                            value="{{ old('name') }}" required>
                        @error('name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email"
                            placeholder="Entrer l'email"
                            value="{{ old('email') }}" required>
                        @error('email')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Rôle</label>
                        <select name="role" class="form-control">
                            <option value="">-- Sélectionner un rôle --</option>
                            <option value="rh" {{ old('role') == 'rh' ? 'selected' : '' }}>RH</option>
                            <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                            <option value="employer" {{ old('role') == 'employer' ? 'selected' : '' }}>Employé</option>
                        </select>
                        @error('role')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn app-btn-primary">Enregistrer</button>
                    <a href="{{ route('administrateurs.index') }}" class="btn btn-outline-secondary ms-2">Annuler</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection