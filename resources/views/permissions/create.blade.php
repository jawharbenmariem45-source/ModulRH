@extends('layouts.admin')

@section('title', 'Ajouter une Permission')

@section('content')
<div class="container mt-4">
    <h2 class="mb-3">Ajouter une Permission</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="m-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.permissions.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">Nom de la Permission</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            <small class="form-text text-muted">Utilisez uniquement des lettres, chiffres, espaces, tirets ou underscores.</small>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
            <small class="form-text text-muted">Optionnel : décrivez le but de cette permission (max 500 caractères).</small>
        </div>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <a href="{{ route('admin.permissions.manage') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection