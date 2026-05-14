<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Fiche de Paie</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a2e; background: #fff; }
        .header { background: #1a1a2e; color: #fff; padding: 18px 24px; margin-bottom: 18px; }
        .header-top { display: table; width: 100%; }
        .header-left  { display: table-cell; vertical-align: middle; width: 60%; }
        .header-right { display: table-cell; vertical-align: middle; width: 40%; text-align: right; }
        .company-name { font-size: 20px; font-weight: bold; letter-spacing: 1px; }
        .doc-title    { font-size: 13px; color: #a0aec0; margin-top: 4px; }
        .periode-badge { display: inline-block; background: #2d6a4f; color: #fff; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: bold; letter-spacing: 1px; }
        .ref { font-size: 10px; color: #a0aec0; margin-top: 6px; }
        .section-title { background: #2d6a4f; color: #fff; padding: 5px 12px; font-size: 10px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .info-table td { padding: 5px 10px; border: 1px solid #e2e8f0; vertical-align: top; }
        .info-table td.label { background: #f7fafc; color: #718096; font-size: 9.5px; text-transform: uppercase; font-weight: bold; width: 30%; }
        .info-table td.value { color: #1a1a2e; font-weight: bold; }
        .paie-table thead tr th { background: #1a1a2e; color: #fff; padding: 6px 10px; text-align: left; font-size: 9.5px; text-transform: uppercase; }
        .paie-table tbody tr td { padding: 5px 10px; border-bottom: 1px solid #e2e8f0; }
        .paie-table tbody tr:nth-child(even) td { background: #f7fafc; }
        .paie-table tfoot tr td { padding: 6px 10px; font-weight: bold; border-top: 2px solid #2d6a4f; }
        .montant { text-align: right; }
        .positive { color: #2d6a4f; }
        .negative { color: #c0392b; }
        .recap-box { background: #1a1a2e; color: #fff; padding: 14px 20px; margin-bottom: 14px; border-radius: 4px; }
        .recap-table { width: 100%; }
        .recap-table td { padding: 4px 8px; }
        .recap-label  { color: #a0aec0; font-size: 10px; text-transform: uppercase; }
        .recap-value  { text-align: right; font-size: 13px; font-weight: bold; color: #fff; }
        .recap-net    { font-size: 18px; color: #68d391; }
        .recap-net-label { font-size: 12px; color: #68d391; text-transform: uppercase; }
        .footer { border-top: 2px solid #e2e8f0; padding-top: 10px; margin-top: 14px; font-size: 9px; color: #a0aec0; text-align: center; }
        .signature-zone { display: table; width: 100%; margin-top: 20px; }
        .signature-cell { display: table-cell; width: 33%; text-align: center; padding: 0 10px; }
        .signature-line { border-top: 1px solid #cbd5e0; margin-top: 40px; padding-top: 4px; font-size: 9px; color: #718096; }
        .badge { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 9.5px; font-weight: bold; color: #fff; }
        .badge-cdi    { background: #2d6a4f; }
        .badge-cdd    { background: #2b6cb0; }
        .badge-civp   { background: #744210; }
        .badge-karama { background: #553c9a; }
        .two-col { display: table; width: 100%; }
        .col-left  { display: table-cell; width: 50%; padding-right: 8px; vertical-align: top; }
        .col-right { display: table-cell; width: 50%; padding-left: 8px; vertical-align: top; }
    </style>
</head>
<body>

@php
// ✅ Nettoie les montants sales ($, €, DT, virgules, espaces)
function cleanAmount($value): float {
    if (is_null($value)) return 0.0;
    $str   = (string) $value;
    $str   = str_replace(',', '.', $str);
    $str   = preg_replace('/[^0-9.\-]/', '', $str);
    $float = floatval($str);
    return is_nan($float) ? 0.0 : $float;
}

// ✅ Parse une date proprement ou retourne '-'
function parseDate($date, $format = 'd/m/Y'): string {
    if (!$date) return '-';
    try {
        return \Carbon\Carbon::parse($date)->format($format);
    } catch (\Exception $e) {
        return (string) $date;
    }
}

$salaireBase      = cleanAmount($fullPaymentInfo->base_salary);
$salaireProratise = cleanAmount($fullPaymentInfo->salaire_proratise ?? $fullPaymentInfo->gross_salary);
$salaireBrut      = cleanAmount($fullPaymentInfo->gross_salary);
$montantHS        = cleanAmount($fullPaymentInfo->overtime_amount);
$primes           = cleanAmount($fullPaymentInfo->primes);
$indemnites       = cleanAmount($fullPaymentInfo->indemnites);
$cnss             = cleanAmount($fullPaymentInfo->cnss);
$irpp             = cleanAmount($fullPaymentInfo->irpp);
$css              = cleanAmount($fullPaymentInfo->css);
$retenueSansSolde = cleanAmount($fullPaymentInfo->retenue_sans_solde ?? 0);
$amount           = cleanAmount($fullPaymentInfo->amount);
$heuresSup        = cleanAmount($fullPaymentInfo->overtime_hours ?? 0);
$totalRetenues    = $cnss + $irpp + $css + $retenueSansSolde;

$dateDebut = parseDate($fullPaymentInfo->employer->start_date);
$dateFin   = $fullPaymentInfo->employer->end_date
    ? parseDate($fullPaymentInfo->employer->end_date)
    : 'CDI — Indéterminée';
@endphp

{{-- EN-TÊTE --}}
<div class="header">
    <div class="header-top">
        <div class="header-left">
            <div class="company-name">
                {{ strtoupper($fullPaymentInfo->employer->company->name ?? 'ENTREPRISE') }}
            </div>
            <div class="doc-title">Bulletin de Paie</div>
        </div>
        <div class="header-right">
            <div class="periode-badge">
                {{ $fullPaymentInfo->month }} {{ $fullPaymentInfo->year }}
            </div>
            <div class="ref">Réf : {{ $fullPaymentInfo->reference }}</div>
        </div>
    </div>
</div>

{{-- INFORMATIONS EMPLOYÉ + CONTRAT --}}
<div class="two-col">
    <div class="col-left">
        <div class="section-title">Informations Employé</div>
        <table class="info-table">
            <tr>
                <td class="label">Nom & Prénom</td>
                <td class="value">
                    {{ strtoupper($fullPaymentInfo->employer->last_name) }}
                    {{ $fullPaymentInfo->employer->first_name }}
                </td>
            </tr>
            <tr>
                <td class="label">Email</td>
                <td class="value">{{ $fullPaymentInfo->employer->email }}</td>
            </tr>
            <tr>
                <td class="label">Téléphone</td>
                <td class="value">{{ $fullPaymentInfo->employer->phone ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">N° CNSS</td>
                <td class="value">{{ $fullPaymentInfo->employer->cnss ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">RIB</td>
                <td class="value">{{ $fullPaymentInfo->employer->rib ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="col-right">
        <div class="section-title">Informations Contrat</div>
        <table class="info-table">
            <tr>
                <td class="label">Type de contrat</td>
                <td class="value">
                    @php $tc = $fullPaymentInfo->contract_type; @endphp
                    <span class="badge badge-{{ strtolower($tc) }}">{{ $tc }}</span>
                </td>
            </tr>
            <tr>
                <td class="label">Date début</td>
                <td class="value">{{ $dateDebut }}</td>
            </tr>
            <tr>
                <td class="label">Date fin</td>
                <td class="value">{{ $dateFin }}</td>
            </tr>
            <tr>
                <td class="label">Département</td>
                <td class="value">{{ $fullPaymentInfo->employer->departement->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Chef de famille</td>
                <td class="value">{{ $fullPaymentInfo->employer->family_head ? 'Oui' : 'Non' }}</td>
            </tr>
        </table>
    </div>
</div>

{{-- PRÉSENCES --}}
<div class="section-title">Présences du mois</div>
<table class="paie-table">
    <thead>
        <tr>
            <th>Jours ouvrés</th>
            <th>Jours travaillés</th>
            <th>Jours de congé</th>
            <th>Absences</th>
            <th>Jours payés</th>
            <th>Heures sup.</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="montant">26</td>
            <td class="montant">{{ $fullPaymentInfo->jours_travailles ?? 0 }}</td>
            <td class="montant positive">
                {{ $fullPaymentInfo->jours_conge ?? ($conges ? $conges->sum('days_count') : 0) }}
            </td>
            <td class="montant negative">{{ $fullPaymentInfo->jours_sans_solde ?? 0 }}</td>
            <td class="montant">{{ $fullPaymentInfo->jours_payes ?? 0 }}</td>
            <td class="montant">{{ number_format($heuresSup, 2) }} h</td>
        </tr>
    </tbody>
</table>

{{-- DÉTAIL PAIE --}}
<div class="two-col">
    <div class="col-left">
        <div class="section-title">Gains</div>
        <table class="paie-table">
            <thead>
                <tr>
                    <th>Libellé</th>
                    <th style="text-align:right">Montant (TND)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Salaire de base</td>
                    <td class="montant positive">{{ number_format($salaireBase, 3, '.', ' ') }}</td>
                </tr>
                <tr>
                    <td>Salaire proratisé</td>
                    <td class="montant positive">{{ number_format($salaireProratise, 3, '.', ' ') }}</td>
                </tr>
                @if($montantHS > 0)
                <tr>
                    <td>Heures supplémentaires</td>
                    <td class="montant positive">{{ number_format($montantHS, 3, '.', ' ') }}</td>
                </tr>
                @endif
                @if($primes > 0)
                <tr>
                    <td>Primes</td>
                    <td class="montant positive">{{ number_format($primes, 3, '.', ' ') }}</td>
                </tr>
                @endif
                @if($indemnites > 0)
                <tr>
                    <td>Indemnités</td>
                    <td class="montant positive">{{ number_format($indemnites, 3, '.', ' ') }}</td>
                </tr>
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <td>Total brut</td>
                    <td class="montant positive">{{ number_format($salaireBrut, 3, '.', ' ') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="col-right">
        <div class="section-title">Retenues</div>
        <table class="paie-table">
            <thead>
                <tr>
                    <th>Libellé</th>
                    <th style="text-align:right">Montant (TND)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>CNSS salarié</td>
                    <td class="montant negative">{{ number_format($cnss, 3, '.', ' ') }}</td>
                </tr>
                <tr>
                    <td>IRPP</td>
                    <td class="montant negative">{{ number_format($irpp, 3, '.', ' ') }}</td>
                </tr>
                <tr>
                    <td>CSS (0.5%)</td>
                    <td class="montant negative">{{ number_format($css, 3, '.', ' ') }}</td>
                </tr>
                @if($retenueSansSolde > 0)
                <tr>
                    <td>Retenue absence non justifiée</td>
                    <td class="montant negative">{{ number_format($retenueSansSolde, 3, '.', ' ') }}</td>
                </tr>
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <td>Total retenues</td>
                    <td class="montant negative">{{ number_format($totalRetenues, 3, '.', ' ') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- RÉCAPITULATIF NET --}}
<div class="recap-box">
    <table class="recap-table">
        <tr>
            <td class="recap-label">Salaire brut</td>
            <td class="recap-label">Total retenues</td>
            <td class="recap-label recap-net-label">Net à payer</td>
        </tr>
        <tr>
            <td class="recap-value">{{ number_format($salaireBrut, 3, '.', ' ') }} TND</td>
            <td class="recap-value">- {{ number_format($totalRetenues, 3, '.', ' ') }} TND</td>
            <td class="recap-value recap-net">{{ number_format($amount, 3, '.', ' ') }} TND</td>
        </tr>
    </table>
</div>

{{-- CONGÉS --}}
@if($conges && $conges->count() > 0)
<div class="section-title">Congés du mois</div>
<table class="paie-table">
    <thead>
        <tr>
            <th>Type</th>
            <th>Date début</th>
            <th>Date fin</th>
            <th>Jours</th>
            <th>Motif</th>
            <th>Statut</th>
        </tr>
    </thead>
    <tbody>
        @foreach($conges as $c)
        <tr>
            <td>{{ $c->type ?? 'Congé annuel' }}</td>
            <td>{{ parseDate($c->start_date) }}</td>
            <td>{{ parseDate($c->end_date) }}</td>
            <td class="montant">{{ $c->days_count ?? '-' }}</td>
            <td>{{ $c->reason ?? '-' }}</td>
            <td><span style="color:#2d6a4f; font-weight:bold;">{{ $c->status }}</span></td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- INFORMATIONS FISCALES --}}
<div class="section-title">Informations fiscales & sociales</div>
<table class="info-table" style="margin-bottom:14px;">
    <tr>
        <td class="label">Situation familiale</td>
        <td class="value">{{ $fullPaymentInfo->employer->family_head ? 'Chef de famille' : 'Célibataire' }}</td>
        <td class="label">Enfants à charge</td>
        <td class="value">{{ $fullPaymentInfo->employer->last_namebre_enfants ?? 0 }}</td>
    </tr>
    <tr>
        <td class="label">Enfants infirmes</td>
        <td class="value">{{ $fullPaymentInfo->employer->last_namebre_enfants_infirmes ?? 0 }}</td>
        <td class="label">Enfants étudiants</td>
        <td class="value">{{ $fullPaymentInfo->employer->last_namebre_enfants_etudiants ?? 0 }}</td>
    </tr>
    <tr>
        <td class="label">Taux CNSS</td>
        <td class="value">9.18% + 1% maladie</td>
        <td class="label">CSS</td>
        <td class="value">0.5%</td>
    </tr>
    @if($fullPaymentInfo->contract_type === 'CIVP')
    <tr>
        <td class="label" colspan="2">Régime CIVP</td>
        <td class="value" colspan="2" style="color:#744210;">Exonéré CNSS · IRPP · CSS</td>
    </tr>
    @elseif($fullPaymentInfo->contract_type === 'Karama')
    <tr>
        <td class="label" colspan="2">Régime Karama</td>
        <td class="value" colspan="2" style="color:#553c9a;">CNSS réduite 50% · Exonéré IRPP · CSS</td>
    </tr>
    @endif
</table>

{{-- SIGNATURES --}}
<div class="signature-zone">
    <div class="signature-cell">
        <div class="signature-line">L'Employeur</div>
    </div>
    <div class="signature-cell">
        <div class="signature-line">Le Responsable RH</div>
    </div>
    <div class="signature-cell">
        <div class="signature-line">L'Employé(e)</div>
    </div>
</div>

{{-- PIED DE PAGE --}}
<div class="footer">
    Bulletin de paie généré le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }} —
    {{ $fullPaymentInfo->month }} {{ $fullPaymentInfo->year }} —
    Réf. {{ $fullPaymentInfo->reference }} —
    Document confidentiel
</div>

</body>
</html>