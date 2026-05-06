@extends('layouts.template') 
@section('content')
<div class="container-fluid">
    @if(auth()->user()->hasRole('employer'))
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 mb-0 text-gray-800">Gestion des Congés</h2>
            <a href="{{ route('conge.create') }}" class="btn btn-primary">Nouvelle Demande</a>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Employé</th>
                            <th>Type</th>
                            <th>Période</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($conges as $c)
                        <tr>
                            <td>{{ $c->employer->nom ?? 'N/A' }} {{ $c->employer->prenom ?? '' }}</td>
                            <td>
                                @if($c->type)
                                    <span class="badge bg-info">{{ $c->type }}</span>
                                @else
                                    <span class="badge bg-secondary">N/A</span>
                                @endif
                            </td>
                            <td>Du {{ $c->date_debut }} au {{ $c->date_fin }}</td>
                            <td>
                                @php
                                    $statut = strtolower(trim($c->statut ?? ''));
                                @endphp

                                @if(in_array($statut, ['en attente', 'en_attente']))
                                    <span class="badge bg-warning text-dark">En attente</span>
                                @elseif(in_array($statut, ['approuvé', 'approuve', 'accepté', 'accepte']))
                                    <span class="badge bg-success">Approuvé</span>
                                @elseif(in_array($statut, ['refusé', 'refuse', 'rejeté', 'rejete']))
                                    <span class="badge bg-danger">Refusé</span>
                                @elseif(is_null($c->statut))
                                    <span class="badge bg-secondary">N/A</span>
                                @else
                                    <span class="badge bg-secondary">{{ $c->statut }}</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statutRaw = strtolower(trim($c->statut ?? ''));
                                @endphp
                                @if(in_array($statutRaw, ['en attente', 'en_attente']))
                                    <form action="{{ route('conge.accepter', $c->id) }}" method="POST" style="display:inline">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-sm btn-success" title="Accepter">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('conge.rejeter', $c->id) }}" method="POST" style="display:inline">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-sm btn-danger" title="Refuser">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection