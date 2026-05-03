@extends('layouts.template')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="app-page-title mb-0">Gestion des Rôles</h1>
    <button type="button" class="btn app-btn-secondary" data-bs-toggle="modal" data-bs-target="#modalAjout">
        + Ajouter un membre
    </button>
</div>
<hr class="mb-4">

@if(session('success_message'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success_message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error_message'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error_message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="app-card shadow-sm mb-5">
    <div class="app-card-body">
        <div class="table-responsive">
            <table class="table app-table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom complet</th>
                        <th>Rôle</th>
                        <th>Permissions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($admins as $admin)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $admin->name }}</td>
                        <td>
                            @php $role = $admin->getRoleNames()->first() @endphp
                            @if($role === 'admin')
                                <span class="badge" style="background:#6c3483; color:white">Admin</span>
                            @elseif($role === 'rh')
                                <span class="badge" style="background:#19a891; color:white">RH</span>
                            @elseif($role === 'manager')
                                <span class="badge" style="background:#e67e22; color:white">Manager</span>
                            @else
                                <span class="badge bg-secondary">Aucun rôle</span>
                            @endif
                        </td>
                        <td>
                            @php $permissions = $admin->getAllPermissions()->pluck('name') @endphp
                            @if($permissions->isEmpty())
                                <span class="text-muted small">Aucune permission</span>
                            @else
                                <div style="display:flex; flex-wrap:wrap; gap:4px;">
                                    @foreach($permissions as $permission)
                                        <span class="badge" style="background:#f0f0f0; color:#333; font-size:10px; border:1px solid #ddd">
                                            {{ $permission }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-secondary me-1"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEdit{{ $admin->id }}">
                                Éditer
                            </button>
                            <form action="{{ route('administrateurs.delete', $admin->id) }}"
                                method="POST" style="display:inline"
                                onsubmit="return confirm('Supprimer ce membre ?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                            </form>
                        </td>
                    </tr>

                    {{-- Modal Edit --}}
                    <div class="modal fade" id="modalEdit{{ $admin->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Modifier — {{ $admin->name }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('administrateurs.update', $admin->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Nom</label>
                                            <input type="text" name="name" class="form-control"
                                                value="{{ $admin->name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" name="email" class="form-control"
                                                value="{{ $admin->email }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Rôle</label>
                                            <select name="role" class="form-select" required>
                                                <option value="admin" {{ $admin->hasRole('admin') ? 'selected' : '' }}>Admin</option>
                                                <option value="rh" {{ $admin->hasRole('rh') ? 'selected' : '' }}>RH</option>
                                                <option value="manager" {{ $admin->hasRole('manager') ? 'selected' : '' }}>Manager</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                        <button type="submit" class="btn app-btn-primary">Mettre à jour</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">Aucun membre ajouté</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<nav class="app-pagination">
    {{ $admins->links() }}
</nav>

{{-- Modal Ajout --}}
<div class="modal fade" id="modalAjout" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un membre</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('administrateurs.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mot de passe</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rôle</label>
                        <select name="role" class="form-select" required>
                            <option value="">-- Choisir --</option>
                            <option value="admin">Admin</option>
                            <option value="rh">RH</option>
                            <option value="manager">Manager</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn app-btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection