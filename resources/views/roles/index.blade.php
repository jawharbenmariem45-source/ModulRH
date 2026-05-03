@extends('layouts.template')

@section('content')
<div class="row g-3 mb-4 align-items-center justify-content-between">
    <div class="col-auto">
        <h1 class="app-page-title mb-0">Gestion des Rôles</h1>
    </div>
    <div class="col-auto">
        <a class="btn app-btn-secondary" href="{{ route('administrateurs.create') }}">
            <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-plus me-1" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
            </svg>
            Ajouter un membre
        </a>
    </div>
</div>

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

<div class="app-card app-card-orders-table shadow-sm mb-5">
    <div class="app-card-body">
        <div class="table-responsive">
            <table class="table app-table-hover mb-0 text-left">
                <thead>
                    <tr>
                        <th class="cell">#</th>
                        <th class="cell">Nom complet</th>
                        <th class="cell">Rôle</th>
                        <th class="cell">Permissions</th>
                        <th class="cell">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($admins as $admin)
                    <tr>
                        <td class="cell">{{ $loop->iteration }}</td>
                        <td class="cell">{{ $admin->name }}</td>
                        <td class="cell">
                            @php $role = $admin->getRoleNames()->first() @endphp
                            @if($role === 'admin')
                                <span class="badge" style="background:#6c3483; color:white">Admin</span>
                            @elseif($role === 'rh')
                                <span class="badge" style="background:#19a891; color:white">RH</span>
                            @elseif($role === 'manager')
                                <span class="badge" style="background:#e67e22; color:white">Manager</span>
                            @elseif($role === 'employer')
                                <span class="badge" style="background:#2980b9; color:white">Employé</span>
                            @else
                                <span class="badge bg-secondary">Aucun rôle</span>
                            @endif
                        </td>
                        <td class="cell">
                            @php
                                $permissions = $admin->getAllPermissions()->pluck('name');
                            @endphp
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
                        <td class="cell">
                            <a href="{{ route('administrateurs.edit', $admin->id) }}"
                                class="btn btn-sm app-btn-secondary me-1">Editer</a>
                            <form action="{{ route('administrateurs.delete', $admin->id) }}"
                                method="POST" style="display:inline;"
                                onsubmit="return confirm('Supprimer ce membre ?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                            </form>
                        </td>
                    </tr>
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
@endsection