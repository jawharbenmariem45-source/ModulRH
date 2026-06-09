@extends('layouts.template')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="app-page-title mb-0">Contrats</h1>
    <button type="button" class="btn app-btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAjoutContrat">
        + Ajouter un contrat
    </button>
</div>
<hr class="mb-4">

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('success_message'))
<div class="alert alert-success alert-dismissible fade show">
    {{ session('success_message') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($alertes->count() > 0)
<div class="alert alert-warning alert-dismissible fade show">
    <strong>⚠️ {{ $alertes->count() }} contrat(s) expirent dans les 7 prochains jours :</strong>
    <ul class="mb-0 mt-1">
        @foreach($alertes as $alerte)
        <li>
            <strong>{{ $alerte->last_name }} {{ $alerte->first_name }}</strong>
            — {{ $alerte->contract_type }}
            — expire le <strong>{{ $alerte->end_date ? \Carbon\Carbon::parse($alerte->end_date)->format('d/m/Y') : '-' }}</strong>
        </li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Filtres --}}
<form method="GET" action="{{ route('contrat.index') }}" class="row g-2 mb-4 align-items-center">
    <div class="col-auto">
        <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="{{ request('search') }}">
    </div>
    <div class="col-auto">
        <select name="type_contrat" class="form-select">
            <option value="">Tous les types</option>
            @foreach(['CDI','CDD','CIVP','Karama'] as $type)
            <option value="{{ $type }}" {{ request('type_contrat') == $type ? 'selected' : '' }}>{{ $type }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <select name="department_id" class="form-select">
            <option value="">Tous les départements</option>
            @foreach($departements as $dep)
            <option value="{{ $dep->id }}" {{ request('department_id') == $dep->id ? 'selected' : '' }}>{{ $dep->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
    </div>
    <div class="col-auto">
        <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn app-btn-secondary">Filtrer</button>
    </div>
    @if(request('search') || request('type_contrat') || request('department_id') || request('date_debut') || request('date_fin'))
    <div class="col-auto">
        <a href="{{ route('contrat.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
    </div>
    @endif
</form>

<div class="app-card shadow-sm mb-5">
    <div class="app-card-body">
        <div class="table-responsive">
            <table class="table app-table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Employé</th>
                        <th>Département</th>
                        <th>Statut</th>
                        <th>Date début</th>
                        <th>Date fin</th>
                        <th>RIB</th>
                        <th>CNSS</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contrats as $contrat)
                    @php
                        $today   = \Carbon\Carbon::today();
                        $dateFin = null;
                        $jours   = null;
                        if ($contrat->end_date) {
                            try {
                                $dateFin = \Carbon\Carbon::parse($contrat->end_date);
                                $jours   = $today->diffInDays($dateFin, false);
                            } catch (\Exception $e) {}
                        }
                        if (!$dateFin) {
                            $badge = '<span class="badge" style="background:#19a891">Actif</span>';
                        } elseif ($jours < 0) {
                            $badge = '<span class="badge bg-danger">Expiré</span>';
                        } elseif ($jours <= 7) {
                            $badge = '<span class="badge bg-warning text-dark">Expire dans '.$jours.'j</span>';
                        } else {
                            $badge = '<span class="badge" style="background:#19a891">Actif</span>';
                        }
                        $startFormatted = $contrat->start_date ? \Carbon\Carbon::parse($contrat->start_date)->format('Y-m-d') : '';
                        $endFormatted   = $contrat->end_date   ? \Carbon\Carbon::parse($contrat->end_date)->format('Y-m-d')   : '';
                    @endphp
                    <tr class="{{ $jours !== null && $jours <= 7 && $jours >= 0 ? 'table-warning' : ($jours !== null && $jours < 0 ? 'table-danger' : '') }}">
                        <td>{{ ($contrats->currentPage() - 1) * $contrats->perPage() + $loop->iteration }}</td>
                        <td><strong>{{ $contrat->last_name }} {{ $contrat->first_name }}</strong></td>
                        <td>{{ $contrat->departement->name ?? '-' }}</td>
                        <td>{!! $badge !!}</td>
                        <td>{{ $contrat->start_date ? \Carbon\Carbon::parse($contrat->start_date)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $contrat->end_date ? \Carbon\Carbon::parse($contrat->end_date)->format('d/m/Y') : '—' }}</td>
                        <td>
                            @if($contrat->rib_image)
                                @php $ext = pathinfo($contrat->rib_image, PATHINFO_EXTENSION); @endphp
                                @if(in_array(strtolower($ext), ['jpg','jpeg','png','gif','webp']))
                                    <a href="{{ asset('storage/' . $contrat->rib_image) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $contrat->rib_image) }}" alt="RIB"
                                             style="width:60px; height:40px; object-fit:cover; border-radius:4px; border:1px solid #ddd;">
                                    </a>
                                @else
                                    <a href="{{ asset('storage/' . $contrat->rib_image) }}" target="_blank"
                                       class="btn btn-sm btn-outline-secondary">📄 PDF</a>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $contrat->cnss ?? '-' }}</td>
                        <td>{{ $contrat->contract_type ?? '-' }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <button type="button"
                                    class="btn btn-sm app-btn-primary"
                                    data-id="{{ $contrat->id }}"
                                    data-type="{{ $contrat->contract_type }}"
                                    data-cnss="{{ $contrat->cnss ?? '' }}"
                                    data-rib="{{ $contrat->rib ?? '' }}"
                                    data-debut="{{ $startFormatted }}"
                                    data-fin="{{ $endFormatted }}"
                                    onclick="openEditContrat(this)">Éditer</button>
                                <a href="{{ route('contrat.delete', $contrat->id) }}"
                                    class="btn btn-sm btn-warning"
                                    onclick="return confirm('Archiver ce contrat ?')">Archiver</a>
                                <a href="{{ route('contrat.pdf', $contrat->id) }}"
                                    class="btn btn-sm btn-outline-info"
                                    title="Visualiser PDF" target="_blank">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                                        <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-5">Aucun contrat trouvé.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $contrats->links() }}</div>
    </div>
</div>

{{-- Modal Ajout Contrat --}}
<div class="modal fade" id="modalAjoutContrat" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Ajouter un contrat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Employé <span class="text-danger">*</span></label>
                        <select id="add_employer_id" class="form-select">
                            <option value="">-- Choisir un employé --</option>
                            @foreach($employers as $employer)
                                <option value="{{ $employer->id }}">{{ $employer->last_name }} {{ $employer->first_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Type de contrat <span class="text-danger">*</span></label>
                        <select id="add_type_contrat" class="form-select" onchange="toggleDateFin('add_date_fin', this.value)">
                            <option value="">-- Choisir --</option>
                            <option value="CDI">CDI</option>
                            <option value="CDD">CDD</option>
                            <option value="CIVP">CIVP</option>
                            <option value="Karama">Karama</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date de début <span class="text-danger">*</span></label>
                        <input type="date" id="add_date_debut" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date de fin <small class="text-muted">(optionnel)</small></label>
                        <input type="date" id="add_date_fin" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn app-btn-primary" onclick="submitAddContrat()">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Edit Contrat --}}
<div class="modal fade" id="modalEditContrat" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Modifier le contrat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Type de contrat <span class="text-danger">*</span></label>
                        <select id="edit_type_contrat" class="form-select" onchange="toggleDateFin('edit_date_fin', this.value)">
                            <option value="">-- Choisir --</option>
                            @foreach(['CDI','CDD','CIVP','Karama'] as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Numéro CNSS</label>
                        <input type="text" id="edit_cnss" class="form-control" inputmode="numeric"
                            oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">RIB bancaire</label>
                        <input type="text" id="edit_rib" class="form-control" maxlength="23">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date de début <span class="text-danger">*</span></label>
                        <input type="date" id="edit_date_debut" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date de fin <small class="text-muted">(optionnel)</small></label>
                        <input type="date" id="edit_date_fin" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn app-btn-primary" onclick="submitEditContrat()">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<script>
var csrfToken     = '{{ csrf_token() }}';
var storeUrl      = '{{ route("contrat.store") }}';
var editContratId = null;

function toggleDateFin(inputId, type) {
    const input = document.getElementById(inputId);
    if (type === 'CDI') {
        input.value = ''; input.readOnly = true;
        input.style.background = '#f0f0f0'; input.style.cursor = 'not-allowed';
    } else {
        input.readOnly = false; input.style.background = ''; input.style.cursor = '';
    }
}

function openEditContrat(btn) {
    editContratId = btn.dataset.id;
    document.getElementById('edit_type_contrat').value = btn.dataset.type;
    document.getElementById('edit_cnss').value         = btn.dataset.cnss;
    document.getElementById('edit_rib').value          = btn.dataset.rib;
    document.getElementById('edit_date_debut').value   = btn.dataset.debut;
    document.getElementById('edit_date_fin').value     = btn.dataset.fin;
    toggleDateFin('edit_date_fin', btn.dataset.type);
    new bootstrap.Modal(document.getElementById('modalEditContrat')).show();
}

function submitAddContrat() {
    const employerId  = document.getElementById('add_employer_id').value;
    const typeContrat = document.getElementById('add_type_contrat').value;
    const dateDebut   = document.getElementById('add_date_debut').value;
    const dateFin     = document.getElementById('add_date_fin').value;
    if (!employerId || !typeContrat || !dateDebut) {
        alert('Veuillez remplir tous les champs obligatoires.'); return;
    }
    const data = new FormData();
    data.append('_token', csrfToken); data.append('employer_id', employerId);
    data.append('type_contrat', typeContrat); data.append('date_debut', dateDebut);
    data.append('date_fin', dateFin);
    fetch(storeUrl, { method: 'POST', body: data, redirect: 'follow' })
    .then(() => { window.location.href = '/contrats'; })
    .catch(err => { alert('Erreur: ' + err); });
}

function submitEditContrat() {
    const typeContrat = document.getElementById('edit_type_contrat').value;
    const cnss        = document.getElementById('edit_cnss').value;
    const rib         = document.getElementById('edit_rib').value;
    const dateDebut   = document.getElementById('edit_date_debut').value;
    const dateFin     = document.getElementById('edit_date_fin').value;
    if (!typeContrat || !dateDebut) {
        alert('Veuillez remplir tous les champs obligatoires.'); return;
    }
    const data = new FormData();
    data.append('_token', csrfToken); data.append('type_contrat', typeContrat);
    data.append('cnss', cnss); data.append('rib', rib);
    data.append('date_debut', dateDebut); data.append('date_fin', dateFin);
    fetch('/contrats/update/' + editContratId, { method: 'POST', body: data, redirect: 'follow' })
    .then(() => { window.location.href = '/contrats'; })
    .catch(err => { alert('Erreur: ' + err); });
}
</script>

@endsection