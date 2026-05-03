@extends('layouts.template')

@section('content')
<h1 class="app-page-title">Modifier le membre</h1>
<hr class="mb-4">

<div class="row g-4">
    <div class="col-12 col-md-4">
        <h3 class="section-title">Modifier le rôle</h3>
        <div class="section-intro">Modifiez le nom et le rôle du membre.</div>
    </div>
    <div class="col-12 col-md-8">
        <div class="app-card app-card-settings shadow-sm p-4">
            <form method="POST" action="{{ route('administrateurs.update', $administrateur->id) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Nom complet</label>
                    <input type="text" class="form-control" name="name"
                        value="{{ old('name', $administrateur->name) }}" required>
                    @error('name')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control"
                        value="{{ $administrateur->email }}" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Rôle</label>
                    <select name="role" class="form-control">
                        <option value="">-- Sélectionner --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}"
                                {{ $administrateur->hasRole($role->name) ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn app-btn-primary">Mettre à jour</button>
                <a href="{{ route('administrateurs.index') }}" class="btn btn-outline-secondary ms-2">Annuler</a>
            </form>
        </div>
    </div>
</div>
@endsection