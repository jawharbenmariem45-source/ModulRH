@extends('layouts.template')
@section('content')

<div class="row g-3 mb-4 align-items-center justify-content-between">
    <div class="col-auto">
        <h1 class="app-page-title mb-0">Permissions des membres</h1>
    </div>
    <div class="col-auto d-flex gap-2">
        <a href="{{ route('roles.manage') }}" class="btn app-btn-secondary btn-sm">Gérer Rôles</a>
    </div>
</div>

@if(session('success_message'))
<div class="alert alert-success alert-dismissible fade show">
    {{ session('success_message') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@php
$categories = [
    'Employers'      => ['voir employers', 'ajouter employer', 'modifier employer', 'supprimer employer'],
    'Contrats'       => ['voir contrats', 'modifier contrat', 'supprimer contrat', 'telecharger contrat pdf'],
    'Paiements'      => ['voir paiements', 'lancer paiements', 'telecharger facture'],
    'Congés'         => ['voir conges', 'valider conge', 'refuser conge'],
    'Départements'   => ['voir departements', 'ajouter departement', 'modifier departement', 'supprimer departement'],
    'Rôles'          => ['voir roles', 'ajouter role', 'supprimer role'],
    'Configurations' => ['voir configurations', 'modifier configurations'],
];
@endphp

<div class="app-card shadow-sm mb-5">
    <div class="app-card-body">
        <div class="table-responsive">
            <table class="table app-table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width:15%">Membre</th>
                        <th style="width:10%">Rôle</th>
                        @foreach($categories as $cat => $perms)
                            <th class="text-center" style="font-size:12px">{{ $cat }}</th>
                        @endforeach
                        <th>Action</th>
                    </tr>
                    {{-- Sous-titres permissions --}}
                    <tr class="table-light" style="font-size:10px">
                        <td></td>
                        <td></td>
                        @foreach($categories as $cat => $perms)
                        <td>
                            <div class="d-flex flex-column gap-1">
                                @foreach($perms as $perm)
                                    <span class="text-muted">{{ $perm }}</span>
                                @endforeach
                            </div>
                        </td>
                        @endforeach
                        <td></td>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <form action="{{ route('permissions.updateUser', $user) }}" method="POST">
                            @csrf @method('PUT')

                            <td>
                                <strong>{{ $user->name }}</strong>
                                <div class="text-muted small">{{ $user->email }}</div>
                            </td>
                            <td>
                                @php $role = $user->getRoleNames()->first() @endphp
                                @if($role === 'admin')
                                    <span class="badge" style="background:#6c3483; color:white">Admin</span>
                                @elseif($role === 'rh')
                                    <span class="badge" style="background:#19a891; color:white">RH</span>
                                @elseif($role === 'manager')
                                    <span class="badge" style="background:#e67e22; color:white">Manager</span>
                                @else
                                    <span class="badge bg-secondary">{{ $role ?? 'Aucun' }}</span>
                                @endif
                            </td>

                            @foreach($categories as $cat => $perms)
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    @foreach($perms as $perm)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                            name="permissions[]" value="{{ $perm }}"
                                            {{ $user->getAllPermissions()->contains('name', $perm) ? 'checked' : '' }}>
                                    </div>
                                    @endforeach
                                </div>
                            </td>
                            @endforeach

                            <td>
                                <button type="submit" class="btn btn-sm app-btn-primary">
                                    Enregistrer
                                </button>
                            </td>
                        </form>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection