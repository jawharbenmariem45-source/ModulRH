@extends('layouts.template')

@section('content')
   <div class="card">
    <div class="card-header">Nouvelle Demande de Congé</div>
    <div class="card-body">
        <form action="{{ route('employer_space.conges.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Type de congé</label>
                    <select name="type" class="form-control" required>
                        <option value="Congé Annuel">Congé Annuel</option>
                        <option value="Maladie">Maladie</option>
                        <option value="Maternité">Maternité</option>
                        <option value="Sans solde">Sans solde</option>
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label>Date de début</label>
                    <input type="date" name="date_debut" class="form-control" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Date de fin</label>
                    <input type="date" name="date_fin" class="form-control" required>
                </div>
            </div>

            <div class="mb-3">
                <label>Motif</label>
                <textarea name="motif" class="form-control" rows="3"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Envoyer la demande</button>
        </form>
    </div>
</div>

@endsection