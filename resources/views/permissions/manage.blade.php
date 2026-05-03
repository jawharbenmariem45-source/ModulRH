@extends('layouts.admin')

@section('title', 'Gérer les Permissions')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Gestion des Permissions</h2>
        <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary btn-sm">+ Ajouter une Permission</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <table class="table table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th style="width: 25%;">Nom</th>
                <th style="width: 55%;">Description</th>
                <th style="width: 20%;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($permissions as $permission)
                <tr>
                    <td>{{ $permission->name }}</td>
                    <td class="description-column">{{ $permission->description ?? 'Aucune description' }}</td>
                    <td>
                        <div class="d-flex flex-wrap gap-2 justify-content-center align-items-center">
                            <a href="{{ route('admin.permissions.edit', $permission) }}" class="btn btn-warning btn-sm">
                                Modifier
                            </a>
                            <form action="{{ route('admin.permissions.delete', $permission) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm btn-delete">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<style>
.table td, .table th {
    vertical-align: middle !important;
}
.description-column {
    white-space: normal;
    word-break: break-word;
}
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButtons = document.querySelectorAll('.btn-delete');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('form');

                Swal.fire({
                    title: 'Êtes-vous sûr ?',
                    text: "Cette action est irréversible !",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Oui, supprimer',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>
@endsection
