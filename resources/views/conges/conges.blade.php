@extends('layouts.template')

@section('content')
<div class="container" style="margin-top: 20px;">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="app-page-title">Mes Congés</h1>
        <button type="button" class="btn app-btn-secondary" data-bs-toggle="modal" data-bs-target="#modalDemandeConge">
            + Demander un congé
        </button>
    </div>
    <hr class="mb-4">

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @php
        $droits  = $soldeConges['droits_annuels']   ?? 0;
        $acquis  = $soldeConges['jours_acquis']     ?? 0;
        $pris    = $soldeConges['jours_pris']       ?? 0;
        $restant = $soldeConges['solde']            ?? 0;
        $bonus   = $soldeConges['bonus_anciennete'] ?? 0;
        $note    = $soldeConges['note']             ?? '';
        $isCivp  = ($employer->contract_type ?? '') === 'CIVP';
    @endphp

    @if($isCivp)
    <div class="alert alert-warning mb-4">
        <strong>Contrat CIVP :</strong> Pas de congé payé légal (stage).
        Les permissions exceptionnelles sont accordées à la discrétion de l'employeur.
    </div>
    @else
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="app-card shadow-sm p-3 text-center">
                <div style="font-size: 2rem; font-weight: 700; color: #1a6b8a;">{{ $droits }}</div>
                <div class="text-muted small">Jours accordés / an</div>
                @if($bonus > 0)
                    <div class="text-success" style="font-size:10px;">dont +{{ $bonus }}j ancienneté</div>
                @endif
            </div>
        </div>
        <div class="col-md-3">
            <div class="app-card shadow-sm p-3 text-center">
                <div style="font-size: 2rem; font-weight: 700; color: #2d6a4f;">{{ $acquis }}</div>
                <div class="text-muted small">Jours acquis</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="app-card shadow-sm p-3 text-center">
                <div style="font-size: 2rem; font-weight: 700; color: #e74c3c;">{{ $pris }}</div>
                <div class="text-muted small">Jours pris (ouvrés)</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="app-card shadow-sm p-3 text-center">
                <div style="font-size: 2rem; font-weight: 700; color: {{ $restant > 5 ? '#27ae60' : '#e67e22' }};">
                    {{ $restant }}
                </div>
                <div class="text-muted small">Jours restants</div>
            </div>
        </div>
    </div>
    @if($note)
    <div class="alert alert-info py-2 mb-3" style="font-size:12px;">
        <i class="fas fa-info-circle me-1"></i> {{ $note }}
    </div>
    @endif
    @endif

    {{-- Tableau --}}
    <div class="app-card shadow-sm mb-5">
        <div class="app-card-body">
            <div class="table-responsive">
                <table class="table app-table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Type</th>
                            <th>Date début</th>
                            <th>Date fin</th>
                            <th>Jours ouvrés</th>
                            <th>Motif</th>
                            <th>Document</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($conges as $conge)
                        @php
                            $dateDebut = $conge->start_date ? \Carbon\Carbon::parse($conge->start_date)->format('d/m/Y') : '-';
                            $dateFin   = $conge->end_date   ? \Carbon\Carbon::parse($conge->end_date)->format('d/m/Y')   : '-';
                            $dateDebutInput = $conge->start_date ? \Carbon\Carbon::parse($conge->start_date)->format('Y-m-d') : '';
                            $dateFinInput   = $conge->end_date   ? \Carbon\Carbon::parse($conge->end_date)->format('Y-m-d')   : '';
                            $statut = strtolower(trim($conge->status ?? ''));
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><span class="badge bg-info text-dark">{{ $conge->type ?? '-' }}</span></td>
                            <td>{{ $dateDebut }}</td>
                            <td>{{ $dateFin }}</td>
                            <td><span class="badge bg-secondary">{{ $conge->days_count ?? 0 }} j</span></td>
                            <td>{{ $conge->reason ?? '-' }}</td>
                            <td>
                                @if($conge->document)
                                    <a href="{{ asset('storage/' . $conge->document) }}" target="_blank"
                                       class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-file"></i> Voir
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($statut === 'pending')
                                    <span class="badge bg-warning text-dark">En attente</span>
                                @elseif($statut === 'approved')
                                    <span class="badge bg-success">Approuvé</span>
                                @elseif($statut === 'rejected')
                                    <span class="badge bg-danger">Refusé</span>
                                @else
                                    <span class="badge bg-secondary">{{ $conge->status ?? 'N/A' }}</span>
                                @endif
                            </td>
                            <td>
                                @if($statut === 'pending')
                                    <button type="button"
                                            class="btn btn-sm btn-warning me-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditConge"
                                            data-id="{{ $conge->id }}"
                                            data-start="{{ $dateDebutInput }}"
                                            data-end="{{ $dateFinInput }}"
                                            data-type="{{ $conge->type }}"
                                            data-reason="{{ $conge->reason }}">
                                        Modifier
                                    </button>
                                    <form action="{{ route('employer_space.conges.delete', $conge->id) }}"
                                          method="POST" style="display:inline"
                                          onsubmit="return confirm('Annuler cette demande ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Annuler</button>
                                    </form>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">Aucune demande de congé.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ══ MODAL NOUVELLE DEMANDE ══ --}}
<div class="modal fade" id="modalDemandeConge" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: #1a6b8a;">
                <h5 class="modal-title text-white">
                    <i class="fas fa-calendar-plus me-2"></i> Nouvelle Demande de Congé
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('employer_space.conges.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                    @endif
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Type de congé <span class="text-danger">*</span></label>
                            <select name="type" id="new_type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="">-- Choisir --</option>
                                <option value="Congé Annuel" {{ old('type') == 'Congé Annuel' ? 'selected' : '' }}>Congé Annuel</option>
                                <option value="Maladie"      {{ old('type') == 'Maladie'      ? 'selected' : '' }}>Maladie</option>
                                <option value="Maternité"    {{ old('type') == 'Maternité'    ? 'selected' : '' }}>Maternité</option>
                                <option value="Sans solde"   {{ old('type') == 'Sans solde'   ? 'selected' : '' }}>Sans solde</option>
                            </select>
                            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date de début <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="new_start"
                                   class="form-control @error('start_date') is-invalid @enderror"
                                   value="{{ old('start_date') }}" min="{{ date('Y-m-d') }}" required>
                            @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date de fin <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" id="new_end"
                                   class="form-control @error('end_date') is-invalid @enderror"
                                   value="{{ old('end_date') }}" min="{{ date('Y-m-d') }}" required>
                            @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <div class="alert py-2 mb-0" id="newJoursBox" style="display:none;">
                                <i class="fas fa-calendar-check me-1"></i>
                                Jours ouvrés déduits : <strong id="newJoursVal">0</strong>
                                &nbsp;|&nbsp; Solde après congé : <strong id="newSoldeVal">{{ $restant }}</strong> j
                                <br><small>Samedis, dimanches et jours fériés non comptés.</small>
                                <div id="newAlertSolde" style="display:none; color:#c0392b; font-weight:bold; margin-top:4px;">
                                    ⚠ Solde insuffisant — maximum {{ $restant }} jour(s).
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Motif <span class="text-muted fw-normal">(optionnel)</span></label>
                            <textarea name="reason" class="form-control" rows="3">{{ old('reason') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Document <span class="text-muted fw-normal">(optionnel)</span></label>
                            <input type="file" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            <div class="form-text">PDF, JPG, PNG — Max 2 Mo</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" id="newBtnSubmit" class="btn btn-primary" style="background:#1a6b8a; border-color:#1a6b8a;">
                        <i class="fas fa-paper-plane me-1"></i> Soumettre
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══ MODAL MODIFIER DEMANDE ══ --}}
<div class="modal fade" id="modalEditConge" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: #e67e22;">
                <h5 class="modal-title text-white">
                    <i class="fas fa-edit me-2"></i> Modifier ma demande
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEditConge" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold">Type de congé <span class="text-danger">*</span></label>
                            <select name="type" id="edit_type" class="form-select" required>
                                <option value="Congé Annuel">Congé Annuel</option>
                                <option value="Maladie">Maladie</option>
                                <option value="Maternité">Maternité</option>
                                <option value="Sans solde">Sans solde</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date de début <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="edit_start"
                                   class="form-control" min="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date de fin <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" id="edit_end"
                                   class="form-control" min="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12">
                            <div class="alert py-2 mb-0" id="editJoursBox" style="display:none;">
                                <i class="fas fa-calendar-check me-1"></i>
                                Jours ouvrés : <strong id="editJoursVal">0</strong>
                                &nbsp;|&nbsp; Solde après : <strong id="editSoldeVal">{{ $restant }}</strong> j
                                <br><small>Samedis, dimanches et jours fériés non comptés.</small>
                                <div id="editAlertSolde" style="display:none; color:#c0392b; font-weight:bold; margin-top:4px;">
                                    ⚠ Solde insuffisant — maximum {{ $restant }} jour(s).
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Motif <span class="text-muted fw-normal">(optionnel)</span></label>
                            <textarea name="reason" id="edit_reason" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Nouveau document <span class="text-muted fw-normal">(optionnel)</span></label>
                            <input type="file" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            <div class="form-text">Laissez vide pour garder l'ancien document.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" id="editBtnSubmit" class="btn" style="background:#e67e22; color:white; border-color:#e67e22;">
                        <i class="fas fa-save me-1"></i> Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('modalDemandeConge')).show();
    });
</script>
@endif

<script>
const FERIES_FIXES = ['01-01','03-20','04-09','05-01','06-01','07-25','08-13','10-15','12-17'];
const FERIES_VAR   = ['2026-03-20','2026-03-21','2026-05-26','2026-05-27','2026-06-15','2026-08-24'];
const TYPES_SANS_DEDUCTION = ['Maladie', 'Maternité'];
const SOLDE = {{ $restant }};

function estJourFerie(date) {
    const mm = String(date.getMonth()+1).padStart(2,'0');
    const dd = String(date.getDate()).padStart(2,'0');
    if (FERIES_FIXES.includes(mm+'-'+dd)) return true;
    return FERIES_VAR.includes(date.getFullYear()+'-'+mm+'-'+dd);
}

function compterJoursOuvres(d1, d2) {
    let jours = 0, cur = new Date(d1), fin = new Date(d2);
    cur.setHours(0,0,0,0); fin.setHours(0,0,0,0);
    while (cur <= fin) {
        if (cur.getDay() !== 0 && cur.getDay() !== 6 && !estJourFerie(cur)) jours++;
        cur.setDate(cur.getDate()+1);
    }
    return jours;
}

function mettreAJourCalc(startId, endId, typeId, boxId, joursId, soldeId, alertId, btnId) {
    const start = document.getElementById(startId);
    const end   = document.getElementById(endId);
    const type  = document.getElementById(typeId);
    const box   = document.getElementById(boxId);
    const jVal  = document.getElementById(joursId);
    const sVal  = document.getElementById(soldeId);
    const alert = document.getElementById(alertId);
    const btn   = document.getElementById(btnId);
    if (!start?.value || !end?.value) return;
    const jours      = compterJoursOuvres(start.value, end.value);
    const typeVal    = type?.value || '';
    const sansDeduct = TYPES_SANS_DEDUCTION.includes(typeVal);
    const soldeApres = sansDeduct ? SOLDE : Math.max(SOLDE - jours, 0);
    jVal.textContent  = jours;
    sVal.textContent  = soldeApres.toFixed(1);
    box.style.display = jours > 0 ? 'block' : 'none';
    if (sansDeduct || jours <= SOLDE) {
        box.className         = 'alert alert-info py-2 mb-0';
        alert.style.display   = 'none';
        if (btn) btn.disabled = false;
    } else {
        box.className         = 'alert alert-danger py-2 mb-0';
        alert.style.display   = 'block';
        if (btn) btn.disabled = true;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const newStart = document.getElementById('new_start');
    const newEnd   = document.getElementById('new_end');
    const newType  = document.getElementById('new_type');
    function calcNew() {
        mettreAJourCalc('new_start','new_end','new_type','newJoursBox','newJoursVal','newSoldeVal','newAlertSolde','newBtnSubmit');
    }
    newStart?.addEventListener('change', function() { newEnd.min = newStart.value; calcNew(); });
    newEnd?.addEventListener('change', calcNew);
    newType?.addEventListener('change', calcNew);

    const editStart = document.getElementById('edit_start');
    const editEnd   = document.getElementById('edit_end');
    const editType  = document.getElementById('edit_type');
    function calcEdit() {
        mettreAJourCalc('edit_start','edit_end','edit_type','editJoursBox','editJoursVal','editSoldeVal','editAlertSolde','editBtnSubmit');
    }
    editStart?.addEventListener('change', function() { editEnd.min = editStart.value; calcEdit(); });
    editEnd?.addEventListener('change', calcEdit);
    editType?.addEventListener('change', calcEdit);

    document.getElementById('modalEditConge').addEventListener('show.bs.modal', function(e) {
        const btn = e.relatedTarget;
        const id  = btn.dataset.id;
        document.getElementById('formEditConge').action = '{{ url("espace-employe/conges/update") }}/' + id;
        document.getElementById('edit_start').value  = btn.dataset.start;
        document.getElementById('edit_end').value    = btn.dataset.end;
        document.getElementById('edit_type').value   = btn.dataset.type;
        document.getElementById('edit_reason').value = btn.dataset.reason || '';
        setTimeout(calcEdit, 100);
    });
});
</script>

@endsection