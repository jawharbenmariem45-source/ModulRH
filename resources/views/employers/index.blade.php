@extends('layouts.template')

@section('content')
<div class="row g-3 mb-4 align-items-center justify-content-between">
    <div class="col-auto">
        <h1 class="app-page-title mb-0">Employers</h1>
    </div>
    <div class="col-auto">
        <form class="row g-2 align-items-center" method="GET" action="{{ route('employer.index') }}">
            <div class="col-auto">
                <input type="text" name="searchorders" class="form-control"
                    placeholder="Rechercher..." value="{{ request('searchorders') }}">
            </div>
            <div class="col-auto">
                <select name="departement" class="form-select">
                    <option value="">Tous les départements</option>
                    @foreach($departements as $dep)
                        <option value="{{ $dep->id }}" {{ request('departement') == $dep->id ? 'selected' : '' }}>
                            {{ $dep->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn app-btn-secondary">Filtrer</button>
            </div>
            @if(request('searchorders') || request('departement'))
            <div class="col-auto">
                <a href="{{ route('employer.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
            </div>
            @endif
            <div class="col-auto">
                <button type="button" class="btn app-btn-secondary" data-bs-toggle="modal" data-bs-target="#createModal">
                    + Ajouter Employer
                </button>
            </div>
        </form>
    </div>
</div>

@if(Session::get('success_message'))
    <div class="alert alert-success">{{ Session::get('success_message') }}</div>
@endif
@if(Session::get('error_message'))
    <div class="alert alert-danger">{{ Session::get('error_message') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="app-card app-card-orders-table shadow-sm mb-5">
    <div class="app-card-body">
        <div class="table-responsive">
            <table class="table app-table-hover mb-0 text-left">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Département</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Contrat</th>
                        <th>Fin contrat</th>
                        <th>Salaire</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employers as $employer)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $employer->departement->name ?? '-' }}</td>
                        <td>{{ $employer->nom }}</td>
                        <td>{{ $employer->prenom }}</td>
                        <td>{{ $employer->email }}</td>
                        <td>{{ $employer->numero_telephone }}</td>
                        <td>
                            <span class="badge
                                {{ $employer->type_contrat == 'CDI' ? 'bg-success' :
                                   ($employer->type_contrat == 'CDD' ? 'bg-primary' : 'bg-secondary') }}">
                                {{ $employer->type_contrat }}
                            </span>
                        </td>
                        <td>
                            @if($employer->date_fin)
                                @php
                                    $jours = \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($employer->date_fin), false);
                                @endphp
                                <span class="{{ $jours <= 30 ? 'text-danger fw-bold' : '' }}">
                                    {{ \Carbon\Carbon::parse($employer->date_fin)->format('d/m/Y') }}
                                    @if($jours <= 30 && $jours >= 0)
                                        <span class="badge bg-danger">{{ $jours }}j</span>
                                    @endif
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-success">{{ $employer->salaire }} DT</span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary me-1"
                                onclick="openEditModal({{ $employer->id }})">
                                <i class="fa fa-edit"></i> Éditer
                            </button>
                            <a class="btn btn-sm btn-outline-danger"
                               href="{{ route('employer.delete', $employer->id) }}"
                               onclick="return confirm('Supprimer cet employé ?')">
                                <i class="fa fa-trash"></i> Supprimer
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted" style="padding: 3rem;">
                            Aucun employé ajouté
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<nav class="app-pagination">
    {{ $employers->links() }}
</nav>

{{-- =============================== --}}
{{-- MODAL CREATE --}}
{{-- =============================== --}}
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">Ajouter un Employé</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('employer.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nom</label>
                            <input type="text" name="nom" class="form-control {{ $errors->has('nom') ? 'is-invalid' : '' }}"
                                   value="{{ old('nom') }}" required>
                            @error('nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Prénom</label>
                            <input type="text" name="prenom" class="form-control {{ $errors->has('prenom') ? 'is-invalid' : '' }}"
                                   value="{{ old('prenom') }}" required>
                            @error('prenom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                   value="{{ old('email') }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Numéro de Téléphone</label>
                            <input type="text" name="numero_telephone"
                                   class="form-control {{ $errors->has('numero_telephone') ? 'is-invalid' : '' }}"
                                   inputmode="numeric"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8);"
                                   value="{{ old('numero_telephone') }}" required>
                            @error('numero_telephone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Numéro RIB (23 caractères)</label>
                            <input type="text" name="rib" class="form-control"
                                   maxlength="23" value="{{ old('rib') }}"
                                   placeholder="xxxxxxxxxxxxxxxxxxxxxxx">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Photo RIB</label>
                            <input type="file" name="rib_image" id="rib_image_create"
                                   class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                            <div id="rib-preview-create" class="mt-2" style="display:none;">
                                <img id="rib-img-create" src="" alt="RIB"
                                     style="max-width:200px; border:1px solid #ddd; border-radius:4px;">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Département</label>
                            <select name="department_id" class="form-control {{ $errors->has('department_id') ? 'is-invalid' : '' }}" required>
                                @foreach($departements as $dep)
                                    <option value="{{ $dep->id }}" {{ old('department_id') == $dep->id ? 'selected' : '' }}>
                                        {{ $dep->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Type de Contrat</label>
                            <select name="type_contrat" id="type_contrat_create"
                                    class="form-control {{ $errors->has('type_contrat') ? 'is-invalid' : '' }}" required>
                                @foreach($contracts as $contract)
                                    <option value="{{ $contract->name }}"
                                        {{ old('type_contrat') == $contract->name ? 'selected' : '' }}>
                                        {{ $contract->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type_contrat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Date de début</label>
                            <input type="date" name="date_debut" id="date_debut_create"
                                   class="form-control {{ $errors->has('date_debut') ? 'is-invalid' : '' }}"
                                   value="{{ old('date_debut') }}" required>
                            @error('date_debut') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Date de fin</label>
                            <input type="date" name="date_fin" id="date_fin_create"
                                   class="form-control {{ $errors->has('date_fin') ? 'is-invalid' : '' }}"
                                   value="{{ old('date_fin') }}">
                            @error('date_fin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Salaire mensuel (DT)</label>
                            <input type="number" name="salaire" class="form-control"
                                   placeholder="Ex: 1500" value="{{ old('salaire') }}"
                                   step="0.001" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Chef de famille</label>
                            <select name="chef_famille" class="form-control">
                                <option value="0" {{ old('chef_famille') == '0' ? 'selected' : '' }}>Non</option>
                                <option value="1" {{ old('chef_famille') == '1' ? 'selected' : '' }}>Oui</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Nombre d'enfants (max 4)</label>
                            <input type="number" name="nombre_enfants" class="form-control"
                                   min="0" max="4" value="{{ old('nombre_enfants', 0) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Enfants infirmes</label>
                            <input type="number" name="nombre_enfants_infirmes" class="form-control"
                                   min="0" value="{{ old('nombre_enfants_infirmes', 0) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Enfants étudiants sans bourse</label>
                            <input type="number" name="nombre_enfants_etudiants" class="form-control"
                                   min="0" value="{{ old('nombre_enfants_etudiants', 0) }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Numéro CNSS</label>
                            <input type="text" name="cnss" class="form-control"
                                   inputmode="numeric"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);"
                                   placeholder="10 chiffres" value="{{ old('cnss') }}">
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- =============================== --}}
{{-- MODAL EDIT --}}
{{-- =============================== --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Modifier l'Employé</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" action="" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nom</label>
                            <input type="text" name="nom" id="edit_nom" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Prénom</label>
                            <input type="text" name="prenom" id="edit_prenom" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Numéro de Téléphone</label>
                            <input type="text" name="numero_telephone" id="edit_telephone" class="form-control"
                                   inputmode="numeric"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8);" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Numéro RIB (23 caractères)</label>
                            <input type="text" name="rib" id="edit_rib" class="form-control"
                                   maxlength="23" placeholder="xxxxxxxxxxxxxxxxxxxxxxx">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Photo RIB</label>
                            <input type="file" name="rib_image" id="rib_image_edit"
                                   class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                            <div id="rib-current" class="mt-2"></div>
                            <div id="rib-preview-edit" class="mt-2" style="display:none;">
                                <img id="rib-img-edit" src="" alt="RIB"
                                     style="max-width:200px; border:1px solid #ddd; border-radius:4px;">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Département</label>
                            <select name="department_id" id="edit_department" class="form-control" required>
                                @foreach($departements as $dep)
                                    <option value="{{ $dep->id }}">{{ $dep->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Type de Contrat</label>
                            <select name="type_contrat" id="edit_type_contrat" class="form-control" required>
                                @foreach($contracts as $contract)
                                    <option value="{{ $contract->name }}">{{ $contract->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Date de début</label>
                            <input type="date" name="date_debut" id="edit_date_debut" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Date de fin</label>
                            <input type="date" name="date_fin" id="edit_date_fin" class="form-control">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Salaire mensuel (DT)</label>
                            <input type="number" name="salaire" id="edit_salaire" class="form-control"
                                   step="0.001" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Chef de famille</label>
                            <select name="chef_famille" id="edit_chef_famille" class="form-control">
                                <option value="0">Non</option>
                                <option value="1">Oui</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Nombre d'enfants (max 4)</label>
                            <input type="number" name="nombre_enfants" id="edit_nombre_enfants"
                                   class="form-control" min="0" max="4">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Enfants infirmes</label>
                            <input type="number" name="nombre_enfants_infirmes" id="edit_nombre_enfants_infirmes"
                                   class="form-control" min="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Enfants étudiants sans bourse</label>
                            <input type="number" name="nombre_enfants_etudiants" id="edit_nombre_enfants_etudiants"
                                   class="form-control" min="0">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Numéro CNSS</label>
                            <input type="text" name="cnss" id="edit_cnss" class="form-control"
                                   inputmode="numeric"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);"
                                   placeholder="10 chiffres">
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const employers = {
    @foreach($employers as $employer)
    {{ $employer->id }}: {
        nom: "{{ $employer->nom }}",
        prenom: "{{ $employer->prenom }}",
        email: "{{ $employer->email }}",
        numero_telephone: "{{ $employer->numero_telephone }}",
        rib: "{{ $employer->rib }}",
        rib_image: "{{ $employer->rib_image ? asset('storage/' . $employer->rib_image) : '' }}",
        department_id: "{{ $employer->department_id }}",
        type_contrat: "{{ $employer->type_contrat }}",
        date_debut: "{{ $employer->date_debut }}",
        date_fin: "{{ $employer->date_fin }}",
        salaire: "{{ $employer->salaire }}",
        chef_famille: "{{ $employer->chef_famille ? 1 : 0 }}",
        nombre_enfants: "{{ $employer->nombre_enfants ?? 0 }}",
        nombre_enfants_infirmes: "{{ $employer->nombre_enfants_infirmes ?? 0 }}",
        nombre_enfants_etudiants: "{{ $employer->nombre_enfants_etudiants ?? 0 }}",
        cnss: "{{ $employer->cnss }}",
        update_url: "{{ route('employer.update', $employer->id) }}",
    },
    @endforeach
};

function openEditModal(id) {
    const e = employers[id];
    if (!e) return;

    document.getElementById('edit_nom').value                      = e.nom;
    document.getElementById('edit_prenom').value                   = e.prenom;
    document.getElementById('edit_email').value                    = e.email;
    document.getElementById('edit_telephone').value                = e.numero_telephone;
    document.getElementById('edit_rib').value                      = e.rib;
    document.getElementById('edit_date_debut').value               = e.date_debut;
    document.getElementById('edit_date_fin').value                 = e.date_fin;
    document.getElementById('edit_salaire').value                  = e.salaire;
    document.getElementById('edit_chef_famille').value             = e.chef_famille;
    document.getElementById('edit_nombre_enfants').value           = e.nombre_enfants;
    document.getElementById('edit_nombre_enfants_infirmes').value  = e.nombre_enfants_infirmes;
    document.getElementById('edit_nombre_enfants_etudiants').value = e.nombre_enfants_etudiants;
    document.getElementById('edit_cnss').value                     = e.cnss;
    document.getElementById('edit_department').value               = e.department_id;
    document.getElementById('edit_type_contrat').value             = e.type_contrat;

    const ribCurrent = document.getElementById('rib-current');
    ribCurrent.innerHTML = e.rib_image
        ? `<img src="${e.rib_image}" alt="RIB actuel" style="max-width:200px; border:1px solid #ddd; border-radius:4px;">`
        : '';

    document.getElementById('editForm').action = e.update_url;

    updateDateFin();

    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function updateDateFin() {
    const typeContrat = document.getElementById('edit_type_contrat');
    const dateFin     = document.getElementById('edit_date_fin');
    if (typeContrat.value === 'CDI') {
        dateFin.value = '';
        dateFin.disabled = true;
        dateFin.style.backgroundColor = '#eaecf4';
    } else {
        dateFin.disabled = false;
        dateFin.style.backgroundColor = '#fff';
    }
}

document.getElementById('edit_type_contrat').addEventListener('change', updateDateFin);

document.getElementById('rib_image_create').addEventListener('change', function() {
    const file = this.files[0];
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('rib-img-create').src = e.target.result;
            document.getElementById('rib-preview-create').style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        document.getElementById('rib-preview-create').style.display = 'none';
    }
});

document.getElementById('rib_image_edit').addEventListener('change', function() {
    const file = this.files[0];
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('rib-img-edit').src = e.target.result;
            document.getElementById('rib-preview-edit').style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        document.getElementById('rib-preview-edit').style.display = 'none';
    }
});

document.getElementById('type_contrat_create').addEventListener('change', function() {
    const dateFin = document.getElementById('date_fin_create');
    if (this.value === 'CDI') {
        dateFin.value = '';
        dateFin.disabled = true;
        dateFin.style.backgroundColor = '#eaecf4';
    } else {
        dateFin.disabled = false;
        dateFin.style.backgroundColor = '#fff';
    }
});

// Rouvrir la modal si erreur de validation
@if($errors->any())
    document.addEventListener('DOMContentLoaded', function() {
        new bootstrap.Modal(document.getElementById('createModal')).show();
    });
@endif
</script>

@endsection