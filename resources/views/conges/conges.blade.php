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
                <div style="font-size:10px; color:#718096;">{{ $soldeConges['taux_mensuel'] ?? '1.833' }}j / mois</div>
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
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $conge->start_date }}</td>
                            <td>{{ $conge->end_date }}</td>
                            <td>
                                <span class="badge bg-info text-dark">{{ $conge->days_count ?? 0 }} j</span>
                            </td>
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
                                @php $statut = strtolower(trim($conge->status ?? '')); @endphp
                                @if(in_array($statut, ['en_attente', 'en attente']))
                                    <span class="badge bg-warning text-dark">En attente</span>
                                @elseif(in_array($statut, ['approuvé', 'approuve', 'accepté', 'accepte']))
                                    <span class="badge bg-success">Approuvé</span>
                                @elseif(in_array($statut, ['refusé', 'refuse', 'rejeté', 'rejete']))
                                    <span class="badge bg-danger">Refusé</span>
                                @else
                                    <span class="badge bg-secondary">{{ $conge->status ?? 'N/A' }}</span>
                                @endif
                            </td>
                            <td>
                                @if(in_array(strtolower(trim($conge->status ?? '')), ['en_attente', 'en attente']))
                                    <a href="{{ route('employer_space.conges.edit', $conge->id) }}"
                                       class="btn btn-sm btn-warning me-1">Modifier</a>
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
                            <td colspan="8" class="text-center text-muted">Aucune demande de congé.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL --}}
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
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label fw-bold">Type de congé <span class="text-danger">*</span></label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="">-- Choisir un type --</option>
                                <option value="Congé Annuel" {{ old('type') == 'Congé Annuel' ? 'selected' : '' }}>Congé Annuel</option>
                                <option value="Maladie"      {{ old('type') == 'Maladie'      ? 'selected' : '' }}>Maladie</option>
                                <option value="Maternité"    {{ old('type') == 'Maternité'    ? 'selected' : '' }}>Maternité</option>
                                <option value="Sans solde"   {{ old('type') == 'Sans solde'   ? 'selected' : '' }}>Sans solde</option>
                            </select>
                            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date de début <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="start_date"
                                   class="form-control @error('start_date') is-invalid @enderror"
                                   value="{{ old('start_date') }}" min="{{ date('Y-m-d') }}" required>
                            @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date de fin <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" id="end_date"
                                   class="form-control @error('end_date') is-invalid @enderror"
                                   value="{{ old('end_date') }}" min="{{ date('Y-m-d') }}" required>
                            @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <div class="alert alert-info py-2 mb-0" id="joursCalcBox" style="display:none;">
                                <i class="fas fa-calendar-check me-1"></i>
                                Jours ouvrés déduits : <strong id="joursCalcVal">0</strong>
                                &nbsp;|&nbsp; Solde restant : <strong>{{ $restant }}</strong> jour(s)
                                <br><small class="text-muted">Samedis, dimanches et jours fériés tunisiens non comptés.</small>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Motif <span class="text-muted fw-normal">(optionnel)</span></label>
                            <textarea name="reason" class="form-control @error('reason') is-invalid @enderror"
                                      rows="3" placeholder="Décrivez brièvement le motif...">{{ old('reason') }}</textarea>
                            @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Document justificatif <span class="text-muted fw-normal">(optionnel)</span></label>
                            <input type="file" name="document"
                                   class="form-control @error('document') is-invalid @enderror"
                                   accept=".pdf,.jpg,.jpeg,.png">
                            <div class="form-text">Formats acceptés : PDF, JPG, PNG — Max 2 Mo</div>
                            @error('document')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" style="background:#1a6b8a; border-color:#1a6b8a;">
                        <i class="fas fa-paper-plane me-1"></i> Soumettre
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
// Jours fériés Tunisie fixes (MM-DD)
const FERIES_FIXES = ['01-01','03-20','04-09','05-01','06-01','07-25','08-13','10-15'];
// Jours fériés variables 2026 (religieux)
const FERIES_VAR   = ['2026-03-29','2026-03-30','2026-06-05','2026-06-06','2026-06-25','2026-09-04'];

function estJourFerie(date) {
    const mm   = String(date.getMonth()+1).padStart(2,'0');
    const dd   = String(date.getDate()).padStart(2,'0');
    const mmjj = mm + '-' + dd;
    if (FERIES_FIXES.includes(mmjj)) return true;
    const ymd  = date.getFullYear() + '-' + mm + '-' + dd;
    return FERIES_VAR.includes(ymd);
}

function compterJoursOuvres(d1str, d2str) {
    let jours = 0;
    let cur   = new Date(d1str);
    const fin = new Date(d2str);
    cur.setHours(0,0,0,0);
    fin.setHours(0,0,0,0);
    while (cur <= fin) {
        const dow = cur.getDay();
        if (dow !== 0 && dow !== 6 && !estJourFerie(cur)) jours++;
        cur.setDate(cur.getDate() + 1);
    }
    return jours;
}

document.addEventListener('DOMContentLoaded', function () {
    const dateDebut = document.getElementById('start_date');
    const dateFin   = document.getElementById('end_date');
    const box       = document.getElementById('joursCalcBox');
    const val       = document.getElementById('joursCalcVal');

    function calculer() {
        if (dateDebut.value && dateFin.value) {
            const jours = compterJoursOuvres(dateDebut.value, dateFin.value);
            val.textContent   = jours;
            box.style.display = jours > 0 ? 'block' : 'none';
        }
    }

    dateDebut.addEventListener('change', function () {
        dateFin.min = dateDebut.value;
        calculer();
    });
    dateFin.addEventListener('change', calculer);
});
</script>

@endsection