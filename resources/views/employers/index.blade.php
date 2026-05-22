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
                <button type="button" class="btn app-btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                    + Ajouter Employer
                </button>
            </div>
        </form>
    </div>
</div>

@if(Session::get('success_message'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ Session::get('success_message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(Session::get('error_message'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ Session::get('error_message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <ul class="mb-0">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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
                        <td>{{ $employer->last_name }}</td>
                        <td>{{ $employer->first_name }}</td>
                        <td>{{ $employer->email }}</td>
                        <td>{{ $employer->phone }}</td>
                        <td>
                            <span class="badge
                                {{ $employer->contract_type == 'CDI'   ? 'bg-success' :
                                  ($employer->contract_type == 'CDD'   ? 'bg-primary' :
                                  ($employer->contract_type == 'CIVP'  ? 'bg-warning' : 'bg-secondary')) }}">
                                {{ $employer->contract_type }}
                            </span>
                        </td>
                        <td>
                            @if($employer->end_date)
                                @php
                                    try { $jours = \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($employer->end_date), false); }
                                    catch(\Exception $e) { $jours = null; }
                                @endphp
                                <span class="{{ isset($jours) && $jours <= 30 && $jours >= 0 ? 'text-danger fw-bold' : '' }}">
                                    {{ $employer->end_date }}
                                    @if(isset($jours) && $jours <= 30 && $jours >= 0)
                                        <span class="badge bg-danger">{{ $jours }}j</span>
                                    @endif
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td><span class="badge bg-success">{{ $employer->salary }} DT</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary me-1" onclick="openEditModal({{ $employer->id }})">
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
                        <td colspan="10" class="text-center text-muted py-5">Aucun employé ajouté.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<nav class="app-pagination">{{ $employers->links() }}</nav>

{{-- ══════════════════════════════════════
     MODAL CREATE
══════════════════════════════════════ --}}
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Ajouter un Employé</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('employer.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body px-4" style="overflow-y:auto; max-height:calc(100vh - 200px);">

                    {{-- Informations personnelles --}}
                    <p class="text-muted text-uppercase fw-semibold small mb-2 mt-1" style="letter-spacing:.08em;">Informations personnelles</p>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Prénom <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Téléphone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control" inputmode="numeric"
                                oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,8)"
                                value="{{ old('phone') }}" required>
                        </div>
                    </div>

                    <hr class="my-3">

                    {{-- Poste & Organisation --}}
                    <p class="text-muted text-uppercase fw-semibold small mb-2" style="letter-spacing:.08em;">Poste & Organisation</p>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Département <span class="text-danger">*</span></label>
                            <select name="department_id" class="form-select" required>
                                <option value="">-- Choisir --</option>
                                @foreach($departements as $dep)
                                    <option value="{{ $dep->id }}" {{ old('department_id') == $dep->id ? 'selected' : '' }}>{{ $dep->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Poste <span class="text-danger">*</span></label>
                            <select name="post_id" class="form-select" required>
                                <option value="">-- Choisir --</option>
                                @foreach($posts as $post)
                                    <option value="{{ $post->id }}" {{ old('post_id') == $post->id ? 'selected' : '' }}>{{ $post->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Horaire</label>
                            <select name="schedule_id" class="form-select">
                                <option value="">-- Choisir --</option>
                                @foreach($schedules as $schedule)
                                    <option value="{{ $schedule->id }}" {{ old('schedule_id') == $schedule->id ? 'selected' : '' }}>
                                        {{ $schedule->name }} ({{ $schedule->start_time }} - {{ $schedule->end_time }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <hr class="my-3">

                    {{-- Contrat & Salaire --}}
                    <p class="text-muted text-uppercase fw-semibold small mb-2" style="letter-spacing:.08em;">Contrat & Salaire</p>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Type de Contrat <span class="text-danger">*</span></label>
                            <select name="contract_type" id="type_contrat_create" class="form-select" required>
                                <option value="">-- Choisir --</option>
                                @foreach($contracts as $contract)
                                    <option value="{{ $contract->name }}" {{ old('contract_type') == $contract->name ? 'selected' : '' }}>{{ $contract->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date de début <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date de fin</label>
                            <input type="date" name="end_date" id="date_fin_create" class="form-control" value="{{ old('end_date') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Salaire mensuel (DT)</label>
                            <input type="number" name="salary" class="form-control" min="0" value="{{ old('salary') }}">
                        </div>
                    </div>

                    <hr class="my-3">

                    {{-- Situation familiale --}}
                    <p class="text-muted text-uppercase fw-semibold small mb-2" style="letter-spacing:.08em;">Situation familiale</p>
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Chef de famille</label>
                            <select name="family_head" class="form-select">
                                <option value="0">Non</option>
                                <option value="1" {{ old('family_head') == '1' ? 'selected' : '' }}>Oui</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Enfants (max 4)</label>
                            <input type="number" name="children_count" class="form-control" min="0" max="4" value="{{ old('children_count', 0) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Enfants infirmes</label>
                            <input type="number" name="disabled_children_count" class="form-control" min="0" value="{{ old('disabled_children_count', 0) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Enfants étudiants</label>
                            <input type="number" name="student_children_count" class="form-control" min="0" value="{{ old('student_children_count', 0) }}">
                        </div>
                    </div>

                    <hr class="my-3">

                    {{-- Documents & Identifiants --}}
                    <p class="text-muted text-uppercase fw-semibold small mb-2" style="letter-spacing:.08em;">Documents & Identifiants</p>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">CNSS</label>
                            <input type="text" name="cnss" class="form-control" inputmode="numeric"
                                oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)"
                                value="{{ old('cnss') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">RIB (23 chiffres)</label>
                            <input type="text" name="rib" class="form-control" maxlength="23" value="{{ old('rib') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Photo RIB</label>
                            <input type="file" name="rib_image" id="rib_image_create" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                            <div id="rib-preview-create" class="mt-2" style="display:none;">
                                <img id="rib-img-create" src="" style="max-width:180px; border-radius:6px;">
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn app-btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════
     MODAL EDIT
══════════════════════════════════════ --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Modifier l'Employé</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" action="" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="modal-body px-4" style="overflow-y:auto; max-height:calc(100vh - 200px);">

                    {{-- Informations personnelles --}}
                    <p class="text-muted text-uppercase fw-semibold small mb-2 mt-1" style="letter-spacing:.08em;">Informations personnelles</p>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" id="edit_nom" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Prénom <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" id="edit_prenom" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Téléphone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="edit_telephone" class="form-control" inputmode="numeric"
                                oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,8)" required>
                        </div>
                    </div>

                    <hr class="my-3">

                    {{-- Poste & Organisation --}}
                    <p class="text-muted text-uppercase fw-semibold small mb-2" style="letter-spacing:.08em;">Poste & Organisation</p>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Département <span class="text-danger">*</span></label>
                            <select name="department_id" id="edit_department" class="form-select" required>
                                <option value="">-- Choisir --</option>
                                @foreach($departements as $dep)
                                    <option value="{{ $dep->id }}">{{ $dep->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Poste</label>
                            <select name="post_id" id="edit_post" class="form-select">
                                <option value="">-- Choisir --</option>
                                @foreach($posts as $post)
                                    <option value="{{ $post->id }}">{{ $post->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Horaire</label>
                            <select name="schedule_id" id="edit_schedule" class="form-select">
                                <option value="">-- Choisir --</option>
                                @foreach($schedules as $schedule)
                                    <option value="{{ $schedule->id }}">
                                        {{ $schedule->name }} ({{ $schedule->start_time }} - {{ $schedule->end_time }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <hr class="my-3">

                    {{-- Contrat & Salaire --}}
                    <p class="text-muted text-uppercase fw-semibold small mb-2" style="letter-spacing:.08em;">Contrat & Salaire</p>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Type de Contrat <span class="text-danger">*</span></label>
                            <select name="contract_type" id="edit_type_contrat" class="form-select" required>
                                <option value="">-- Choisir --</option>
                                @foreach($contracts as $contract)
                                    <option value="{{ $contract->name }}">{{ $contract->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date de début <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="edit_date_debut" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date de fin</label>
                            <input type="date" name="end_date" id="edit_date_fin" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Salaire mensuel (DT)</label>
                            <input type="number" name="salary" id="edit_salaire" class="form-control" min="0">
                        </div>
                    </div>

                    <hr class="my-3">

                    {{-- Situation familiale --}}
                    <p class="text-muted text-uppercase fw-semibold small mb-2" style="letter-spacing:.08em;">Situation familiale</p>
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Chef de famille</label>
                            <select name="family_head" id="edit_chef_famille" class="form-select">
                                <option value="0">Non</option>
                                <option value="1">Oui</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Enfants (max 4)</label>
                            <input type="number" name="children_count" id="edit_nombre_enfants" class="form-control" min="0" max="4">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Enfants infirmes</label>
                            <input type="number" name="disabled_children_count" id="edit_nombre_enfants_infirmes" class="form-control" min="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Enfants étudiants</label>
                            <input type="number" name="student_children_count" id="edit_nombre_enfants_etudiants" class="form-control" min="0">
                        </div>
                    </div>

                    <hr class="my-3">

                    {{-- Documents & Identifiants --}}
                    <p class="text-muted text-uppercase fw-semibold small mb-2" style="letter-spacing:.08em;">Documents & Identifiants</p>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">CNSS</label>
                            <input type="text" name="cnss" id="edit_cnss" class="form-control" inputmode="numeric"
                                oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">RIB</label>
                            <input type="text" name="rib" id="edit_rib" class="form-control" maxlength="23">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Photo RIB</label>
                            <input type="file" name="rib_image" id="rib_image_edit" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                            <div id="rib-current" class="mt-2"></div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn app-btn-primary">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const employers = {
    @foreach($employers as $employer)
    {{ $employer->id }}: {
        nom:                     "{{ addslashes($employer->last_name) }}",
        prenom:                  "{{ addslashes($employer->first_name) }}",
        email:                   "{{ $employer->email }}",
        phone:                   "{{ $employer->phone }}",
        rib:                     "{{ $employer->rib }}",
        rib_image:               "{{ $employer->rib_image ? asset('storage/' . $employer->rib_image) : '' }}",
        department_id:           "{{ $employer->department_id }}",
        post_id:                 "{{ $employer->post_id }}",
        schedule_id:             "{{ $employer->schedule_id }}",
        contract_type:           "{{ $employer->contract_type }}",
        start_date:              "{{ $employer->start_date }}",
        end_date:                "{{ $employer->end_date }}",
        salary:                  "{{ $employer->salary }}",
        family_head:             "{{ $employer->family_head ? 1 : 0 }}",
        children_count:          "{{ $employer->children_count ?? 0 }}",
        disabled_children_count: "{{ $employer->disabled_children_count ?? 0 }}",
        student_children_count:  "{{ $employer->student_children_count ?? 0 }}",
        cnss:                    "{{ $employer->cnss }}",
        update_url:              "{{ route('employer.update', $employer->id) }}",
    },
    @endforeach
};

function openEditModal(id) {
    const e = employers[id];
    if (!e) return;
    document.getElementById('edit_nom').value                      = e.nom;
    document.getElementById('edit_prenom').value                   = e.prenom;
    document.getElementById('edit_email').value                    = e.email;
    document.getElementById('edit_telephone').value                = e.phone;
    document.getElementById('edit_rib').value                      = e.rib;
    document.getElementById('edit_date_debut').value               = e.start_date;
    document.getElementById('edit_date_fin').value                 = e.end_date;
    document.getElementById('edit_salaire').value                  = e.salary;
    document.getElementById('edit_chef_famille').value             = e.family_head;
    document.getElementById('edit_nombre_enfants').value           = e.children_count;
    document.getElementById('edit_nombre_enfants_infirmes').value  = e.disabled_children_count;
    document.getElementById('edit_nombre_enfants_etudiants').value = e.student_children_count;
    document.getElementById('edit_cnss').value                     = e.cnss;
    document.getElementById('edit_department').value               = e.department_id;
    document.getElementById('edit_post').value                     = e.post_id;
    document.getElementById('edit_schedule').value                 = e.schedule_id;
    document.getElementById('edit_type_contrat').value             = e.contract_type;

    document.getElementById('rib-current').innerHTML = e.rib_image
        ? `<img src="${e.rib_image}" alt="RIB" style="max-width:180px;border-radius:6px;margin-top:6px;">`
        : '';

    document.getElementById('editForm').action = e.update_url;
    toggleDateFin('edit_type_contrat', 'edit_date_fin');
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function toggleDateFin(selectId, inputId) {
    const val   = document.getElementById(selectId).value;
    const input = document.getElementById(inputId);
    if (val === 'CDI') {
        input.value            = '';
        input.disabled         = true;
        input.style.background = '#eaecf4';
    } else {
        input.disabled         = false;
        input.style.background = '';
    }
}

document.getElementById('edit_type_contrat').addEventListener('change', () =>
    toggleDateFin('edit_type_contrat', 'edit_date_fin'));

document.getElementById('type_contrat_create').addEventListener('change', () =>
    toggleDateFin('type_contrat_create', 'date_fin_create'));

document.getElementById('rib_image_create').addEventListener('change', function () {
    const file = this.files[0];
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('rib-img-create').src = e.target.result;
            document.getElementById('rib-preview-create').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

@if($errors->any())
    document.addEventListener('DOMContentLoaded', () =>
        new bootstrap.Modal(document.getElementById('createModal')).show());
@endif
</script>

@endsection