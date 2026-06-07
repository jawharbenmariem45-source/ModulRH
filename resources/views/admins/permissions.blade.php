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

<div class="app-card shadow-sm mb-5">
    <div class="app-card-body">
        <div class="table-responsive">
            <table class="table app-table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width:120px">Rôle</th>
                        @foreach($categories as $cat => $perms)
                            <th class="text-center" style="font-size:12px">{{ $cat }}</th>
                        @endforeach
                        <th style="width:120px">Action</th>
                    </tr>
                    <tr class="table-light" style="font-size:10px">
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
                    @foreach($roles as $role)
                    @php
                        $rolePerms = $role->permissions->pluck('name');
                    @endphp

                    {{-- Le formulaire est OUTSIDE du tr, les inputs sont liés par form= --}}
                    <form id="form-role-{{ $role->id }}"
                          action="{{ route('permissions.updateRole', $role) }}"
                          method="POST">
                        @csrf
                        @method('PUT')
                    </form>

                    <tr>
                        <td>
                            @if($role->name === 'admin')
                                <span class="badge" style="background:#6c3483;color:white;font-size:13px">Admin</span>
                            @elseif($role->name === 'rh')
                                <span class="badge" style="background:#19a891;color:white;font-size:13px">RH</span>
                            @elseif($role->name === 'manager')
                                <span class="badge" style="background:#e67e22;color:white;font-size:13px">Manager</span>
                            @elseif($role->name === 'employer')
                                <span class="badge" style="background:#2980b9;color:white;font-size:13px">Employé</span>
                            @else
                                <span class="badge bg-secondary" style="font-size:13px">{{ ucfirst($role->name) }}</span>
                            @endif
                        </td>

                        @foreach($categories as $cat => $perms)
                        <td>
                            <div class="d-flex flex-column gap-1">
                                @foreach($perms as $perm)
                                <div class="form-check" style="min-height:20px">
                                    {{-- form= lie cet input au formulaire externe --}}
                                    <input class="form-check-input"
                                        type="checkbox"
                                        name="permissions[]"
                                        value="{{ $perm }}"
                                        form="form-role-{{ $role->id }}"
                                        {{ $rolePerms->contains($perm) ? 'checked' : '' }}
                                        style="accent-color:#19a891">
                                </div>
                                @endforeach
                            </div>
                        </td>
                        @endforeach

                        <td>
                            <button type="submit"
                                form="form-role-{{ $role->id }}"
                                class="btn btn-sm app-btn-primary">
                                Enregistrer
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection