@extends('layouts.template')

@section('content')
<div class="container" style="margin-top: 20px;">

    {{-- En-tête --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="app-page-title">Mes Congés</h1>
        <button type="button" class="btn app-btn-secondary" data-bs-toggle="modal" data-bs-target="#modalDemandeConge">
            + Demander un congé
        </button>
    </div>
    <hr class="mb-4">

    {{-- Messages flash --}}
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

    {{-- Cartes solde congés --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="app-card shadow-sm p-3 text-center">
                <div style="font-size: 2rem; font-weight: 700; color: #1a6b8a;">{{ $joursAccordes ?? 0 }}</div>
                <div class="text-muted">Jours accordés / an</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="app-card shadow-sm p-3 text-center">
                <div style="font-size: 2rem; font-weight: 700; color: #e74c3c;">{{ $congesPris ?? 0 }}</div>
                <div class="text-muted">Jours pris</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="app-card shadow-sm p-3 text-center">
                <div style="font-size: 2rem; font-weight: 700; color: {{ ($solde ?? 0) > 5 ? '#27ae60' : '#e67e22' }};">
                    {{ $solde ?? 0 }}
                </div>
                <div class="text-muted">Jours restants</div>
            </div>
        </div>
    </div>

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
                            <th>Nombre de jours</th>
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
                                <span class="badge bg-info text-dark">
                                    {{ $conge->days_count ?? 0 }} j
                                </span>
                            </td>
                            <td>{{ $conge->reason ?? '-' }}</td>
                            <td>
                                @if($conge->document)
                                    <a href="{{ asset('storage/' . $conge->document) }}"
                                       target="_blank"
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
                                          method="POST"
                                          style="display:inline"
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

{{-- ══════════════════════════════════════════════════════════
     MODAL — Nouvelle Demande de Congé
══════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalDemandeConge" tabindex="-1" aria-labelledby="modalDemandeCongeLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header" style="background: #1a6b8a;">
                <h5 class="modal-title text-white" id="modalDemandeCongeLabel">
                    <i class="fas fa-calendar-plus me-2"></i> Nouvelle Demande de Congé
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('employer_space.conges.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="modal-body">

                    {{-- Erreurs de validation --}}
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

                        {{-- Type de congé --}}
                        <div class="col-12">
                            <label class="form-label fw-bold">
                                Type de congé <span class="text-danger">*</span>
                            </label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="">-- Choisir un type --</option>
                                <option value="Congé Annuel" {{ old('type') == 'Congé Annuel' ? 'selected' : '' }}>Congé Annuel</option>
                                <option value="Maladie"      {{ old('type') == 'Maladie'      ? 'selected' : '' }}>Maladie</option>
                                <option value="Maternité"    {{ old('type') == 'Maternité'    ? 'selected' : '' }}>Maternité</option>
                                <option value="Sans solde"   {{ old('type') == 'Sans solde'   ? 'selected' : '' }}>Sans solde</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Date début --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                Date de début <span class="text-danger">*</span>
                            </label>
                            <input type="date"
                                   name="start_date"
                                   id="start_date"
                                   class="form-control @error('start_date') is-invalid @enderror"
                                   value="{{ old('start_date') }}"
                                   min="{{ date('Y-m-d') }}"
                                   required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Date fin --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                Date de fin <span class="text-danger">*</span>
                            </label>
                            <input type="date"
                                   name="end_date"
                                   id="end_date"
                                   class="form-control @error('end_date') is-invalid @enderror"
                                   value="{{ old('end_date') }}"
                                   min="{{ date('Y-m-d') }}"
                                   required>
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Nombre de jours calculé automatiquement --}}
                        <div class="col-12">
                            <div class="alert alert-info py-2 mb-0" id="joursCalcBox" style="display:none;">
                                <i class="fas fa-calendar-check me-1"></i>
                                Durée : <strong id="joursCalcVal">0</strong> jour(s)
                                &nbsp;|&nbsp; Solde restant : <strong>{{ $solde ?? 0 }}</strong> jour(s)
                            </div>
                        </div>

                        {{-- Motif (optionnel) --}}
                        <div class="col-12">
                            <label class="form-label fw-bold">
                                Motif
                                <span class="text-muted fw-normal">(optionnel)</span>
                            </label>
                            <textarea name="reason"
                                      class="form-control @error('reason') is-invalid @enderror"
                                      rows="3"
                                      placeholder="Décrivez brièvement le motif de votre demande...">{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Document justificatif (optionnel) --}}
                        <div class="col-12">
                            <label class="form-label fw-bold">
                                Document justificatif
                                <span class="text-muted fw-normal">(optionnel)</span>
                            </label>
                            <input type="file"
                                   name="document"
                                   class="form-control @error('document') is-invalid @enderror"
                                   accept=".pdf,.jpg,.jpeg,.png">
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Formats acceptés : PDF, JPG, PNG &mdash; Taille max : 2 Mo
                            </div>
                            @error('document')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Annuler
                    </button>
                    <button type="submit" class="btn btn-primary" style="background:#1a6b8a; border-color:#1a6b8a;">
                        <i class="fas fa-paper-plane me-1"></i> Soumettre
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- Rouvrir le modal automatiquement si erreur de validation --}}
@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('modalDemandeConge')).show();
    });
</script>
@endif

{{-- Calcul automatique du nombre de jours --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const dateDebut = document.getElementById('start_date');
        const dateFin   = document.getElementById('end_date');
        const box       = document.getElementById('joursCalcBox');
        const val       = document.getElementById('joursCalcVal');

        function calculerJours() {
            if (dateDebut.value && dateFin.value) {
                const d1   = new Date(dateDebut.value);
                const d2   = new Date(dateFin.value);
                const diff = Math.round((d2 - d1) / (1000 * 60 * 60 * 24)) + 1;
                if (diff > 0) {
                    val.textContent = diff;
                    box.style.display = 'block';
                } else {
                    box.style.display = 'none';
                }
            }
        }

        // Mettre à jour date_fin min quand date_debut change
        dateDebut.addEventListener('change', function () {
            dateFin.min = dateDebut.value;
            calculerJours();
        });

        dateFin.addEventListener('change', calculerJours);
    });
</script>

@endsection