@extends('layouts.template')
@section('content')

<div class="row g-3 mb-4 align-items-center justify-content-between">
    <div class="col-auto">
        <h1 class="app-page-title mb-0">Permissions par Rôle</h1>
    </div>
    <div class="col-auto d-flex gap-2">
        <a href="{{ route('administrateurs.index') }}" class="btn app-btn-secondary btn-sm">
            Gérer Membres
        </a>
    </div>
</div>

@if(session('success_message'))
<div class="alert alert-success alert-dismissible fade show">
    {{ session('success_message') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@foreach($roles as $role)
@php
    $rolePerms = $role->permissions->pluck('name');
@endphp

<form id="form-role-{{ $role->id }}"
      action="{{ route('permissions.updateRole', $role) }}"
      method="POST">
    @csrf
    @method('PUT')
</form>

<div class="app-card shadow-sm mb-4">
    <div class="app-card-header p-3 d-flex justify-content-between align-items-center">
        <div>
            @if($role->name === 'admin')
                <span class="badge" style="background:#6c3483;color:white;font-size:14px;padding:6px 12px">Admin</span>
            @elseif($role->name === 'rh')
                <span class="badge" style="background:#19a891;color:white;font-size:14px;padding:6px 12px">RH</span>
            @elseif($role->name === 'manager')
                <span class="badge" style="background:#e67e22;color:white;font-size:14px;padding:6px 12px">Manager</span>
            @elseif($role->name === 'employer')
                <span class="badge" style="background:#2980b9;color:white;font-size:14px;padding:6px 12px">Employé</span>
            @else
                <span class="badge bg-secondary" style="font-size:14px;padding:6px 12px">{{ ucfirst($role->name) }}</span>
            @endif
        </div>
        <button type="submit" form="form-role-{{ $role->id }}" class="btn btn-sm app-btn-primary">
            Enregistrer
        </button>
    </div>
    <div class="app-card-body p-3">
        <div class="row g-3">
            @foreach($categories as $cat => $perms)
            <div class="col-md-3 col-6">
                <div class="p-3 rounded" style="background:#f8f9fa;border:1px solid #e9ecef">
                    <h6 class="fw-bold mb-2" style="color:#19a891;font-size:13px">{{ $cat }}</h6>
                    @foreach($perms as $perm)
                    <div class="form-check mb-1">
                        <input class="form-check-input"
                            type="checkbox"
                            name="permissions[]"
                            value="{{ $perm }}"
                            form="form-role-{{ $role->id }}"
                            id="perm-{{ $role->id }}-{{ Str::slug($perm) }}"
                            {{ $rolePerms->contains($perm) ? 'checked' : '' }}
                            style="accent-color:#19a891">
                        <label class="form-check-label small" for="perm-{{ $role->id }}-{{ Str::slug($perm) }}">
                            {{ $perm }}
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endforeach

@endsection