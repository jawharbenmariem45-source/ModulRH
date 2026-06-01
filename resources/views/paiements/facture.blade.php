<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Bulletin de Paie</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #000; background: #fff; }

        /* ── En-tête ── */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .header-table td { vertical-align: top; padding: 4px; }
        .company-box { border: 1px solid #000; padding: 6px; width: 45%; }
        .company-name { font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .company-sub  { font-size: 9px; margin-top: 2px; }
        .bulletin-title { font-size: 16px; font-weight: bold; color: #c00; text-align: right; text-transform: uppercase; }
        .mois-box { text-align: right; font-size: 10px; margin-top: 4px; }

        /* ── Infos employé / société ── */
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .info-table td { border: 1px solid #aaa; padding: 3px 5px; font-size: 8.5px; }
        .info-table .lbl { background: #eee; font-weight: bold; width: 30%; }

        /* ── Tableau principal ── */
        .main-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .main-table th {
            background: #1a1a2e; color: #fff;
            padding: 4px 5px; font-size: 8px;
            text-align: center; border: 1px solid #000;
        }
        .main-table td {
            border: 1px solid #aaa; padding: 3px 5px;
            font-size: 8.5px; vertical-align: middle;
        }
        .main-table .num { text-align: right; }
        .main-table .lbl-col { width: 30%; }
        .main-table .total-row td { background: #f0f0f0; font-weight: bold; }
        .main-table .section-row td { background: #dde; font-weight: bold; font-size: 8px; text-transform: uppercase; }
        .gain { color: #1a6b3a; }
        .retenue { color: #c00; }

        /* ── Récapitulatif ── */
        .recap-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .recap-table th { background: #1a1a2e; color: #fff; padding: 4px; font-size: 8px; border: 1px solid #000; text-align: center; }
        .recap-table td { border: 1px solid #aaa; padding: 3px 6px; font-size: 8.5px; text-align: center; }
        .net-cell { font-size: 13px; font-weight: bold; color: #c00; }

        /* ── Signatures ── */
        .sig-table { width: 100%; margin-top: 14px; }
        .sig-table td { text-align: center; font-size: 8px; padding-top: 30px; border-top: 1px solid #aaa; width: 33%; }

        /* ── Pied ── */
        .footer { font-size: 7px; color: #666; text-align: center; margin-top: 10px; border-top: 1px solid #ccc; padding-top: 4px; }

        .badge-contrat { display: inline-block; padding: 1px 6px; border-radius: 3px; color: #fff; font-size: 8px; font-weight: bold; }
        .badge-cdi    { background: #2d6a4f; }
        .badge-cdd    { background: #2b6cb0; }
        .badge-civp   { background: #744210; }
        .badge-karama { background: #553c9a; }
        .note-box { border: 1px solid #9ae6b4; background: #f0fff4; padding: 4px 8px; font-size: 8px; color: #276749; margin-bottom: 6px; border-radius: 3px; }
        .note-box-karama { border-color: #d6bcfa; background: #faf5ff; color: #44337a; }
    </style>
</head>
<body>

@php
function cleanAmt($v): float {
    if (is_null($v)) return 0.0;
    $s = preg_replace('/[^0-9.\-]/', '', str_replace(',', '.', (string)$v));
    return is_nan(floatval($s)) ? 0.0 : floatval($s);
}
function fmt($v): string { return number_format(cleanAmt($v), 3, '.', ' '); }
function parseD($d, $f='d/m/Y'): string {
    if (!$d) return '-';
    try { return \Carbon\Carbon::parse($d)->format($f); } catch(\Exception $e) { return (string)$d; }
}

$emp   = $fullPaymentInfo->employer;
$tc    = $fullPaymentInfo->contract_type ?? 'CDI';
$co    = $emp->company ?? null;

$base      = cleanAmt($fullPaymentInfo->base_salary);
$prorat    = cleanAmt($fullPaymentInfo->salaire_proratise ?? $fullPaymentInfo->gross_salary);
$brut      = cleanAmt($fullPaymentInfo->gross_salary);
$hs        = cleanAmt($fullPaymentInfo->overtime_hours ?? 0);
$hsAmt     = cleanAmt($fullPaymentInfo->overtime_amount);
$primes    = cleanAmt($fullPaymentInfo->bonuses ?? 0);
$indemn    = cleanAmt($fullPaymentInfo->allowances ?? 0);
$cnss      = cleanAmt($fullPaymentInfo->cnss);
$irpp      = cleanAmt($fullPaymentInfo->irpp);
$css       = cleanAmt($fullPaymentInfo->css);
$retenue   = cleanAmt($fullPaymentInfo->retenue_sans_solde ?? 0);
$net       = cleanAmt($fullPaymentInfo->amount);
$totalRet  = $cnss + $irpp + $css + $retenue;

$joursOuvres     = $fullPaymentInfo->jours_ouvres ?? 22;
$joursTravailles = $fullPaymentInfo->jours_travailles ?? 0;
$joursConge      = $fullPaymentInfo->jours_conge ?? 0;
$joursSansSolde  = $fullPaymentInfo->jours_sans_solde ?? 0;
$joursPayes      = $fullPaymentInfo->jours_payes ?? ($joursTravailles + $joursConge);

$isCivp   = $tc === 'CIVP';
$isKarama = $tc === 'Karama';
@endphp

{{-- ══ EN-TÊTE ══ --}}
<table class="header-table">
    <tr>
        <td style="width:48%;">
            <div class="company-box">
                <div class="company-name">{{ strtoupper($co->name ?? 'ENTREPRISE') }}</div>
                <div class="company-sub">Régime : {{ $co->work_schedule ?? '40h' }} / semaine</div>
                <div class="company-sub">Jour de paie : {{ $co->payment_date ?? '-' }}</div>
            </div>
        </td>
        <td style="width:4%;"></td>
        <td style="width:48%; vertical-align:top;">
            <div class="bulletin-title">Bulletin de Paie</div>
            <div class="mois-box">
                <strong>Période :</strong> {{ $fullPaymentInfo->month }} {{ $fullPaymentInfo->year }}
                &nbsp;&nbsp;
                <strong>Réf :</strong> {{ $fullPaymentInfo->reference }}
            </div>
        </td>
    </tr>
</table>

{{-- ══ INFOS EMPLOYÉ + CONTRAT ══ --}}
<table class="info-table">
    <tr>
        <td class="lbl">Matricule / N° CNSS</td>
        <td>{{ $emp->cnss ?? '-' }}</td>
        <td class="lbl">Type de contrat</td>
        <td>
            <span class="badge-contrat badge-{{ strtolower($tc) }}">{{ $tc }}</span>
        </td>
    </tr>
    <tr>
        <td class="lbl">Nom & Prénom</td>
        <td><strong>{{ strtoupper($emp->last_name) }} {{ $emp->first_name }}</strong></td>
        <td class="lbl">Date début</td>
        <td>{{ parseD($emp->start_date) }}</td>
    </tr>
    <tr>
        <td class="lbl">Email</td>
        <td>{{ $emp->email }}</td>
        <td class="lbl">Date fin</td>
        <td>{{ $emp->end_date ? parseD($emp->end_date) : 'CDI — Indéterminée' }}</td>
    </tr>
    <tr>
        <td class="lbl">Téléphone</td>
        <td>{{ $emp->phone ?? '-' }}</td>
        <td class="lbl">Département</td>
        <td>{{ $emp->departement->name ?? '-' }}</td>
    </tr>
    <tr>
        <td class="lbl">RIB</td>
        <td>{{ $emp->rib ?? '-' }}</td>
        <td class="lbl">Situation familiale</td>
        <td>
            {{ $emp->family_head ? 'Chef de famille' : 'Célibataire' }}
            @if(($emp->children_count ?? 0) > 0)
                — {{ $emp->children_count }} enfant(s)
            @endif
        </td>
    </tr>
</table>

{{-- ══ TABLEAU PRINCIPAL ══ --}}
<table class="main-table">
    <thead>
        <tr>
            <th style="width:5%;">N°</th>
            <th class="lbl-col" style="width:32%;">Libellé</th>
            <th style="width:8%;">Nombre</th>
            <th style="width:10%;">Base (TND)</th>
            <th style="width:10%;">Taux</th>
            <th style="width:12%;">Gain (TND)</th>
            <th style="width:12%;">Retenue (TND)</th>
        </tr>
    </thead>
    <tbody>

        {{-- Section Gains --}}
        <tr class="section-row"><td colspan="7">Éléments de rémunération</td></tr>

        <tr>
            <td class="num">101</td>
            <td>Salaire de base</td>
            <td class="num">{{ $joursOuvres }}j</td>
            <td class="num">{{ fmt($base) }}</td>
            <td class="num">-</td>
            <td class="num gain">{{ fmt($base) }}</td>
            <td></td>
        </tr>

        @if($prorat != $base)
        <tr>
            <td class="num">102</td>
            <td>Salaire proratisé ({{ $joursPayes }}j payés)</td>
            <td class="num">{{ $joursPayes }}j</td>
            <td class="num">{{ fmt($base) }}</td>
            <td class="num">-</td>
            <td class="num gain">{{ fmt($prorat) }}</td>
            <td></td>
        </tr>
        @endif

        @if($hs > 0)
        <tr>
            <td class="num">103</td>
            <td>Heures supplémentaires</td>
            <td class="num">{{ number_format($hs, 2) }}h</td>
            <td class="num">{{ fmt($base / ($joursOuvres * 8)) }}/h</td>
            <td class="num">+25%</td>
            <td class="num gain">{{ fmt($hsAmt) }}</td>
            <td></td>
        </tr>
        @endif

        @if($primes > 0)
        <tr>
            <td class="num">104</td>
            <td>Primes</td>
            <td class="num">-</td>
            <td class="num">-</td>
            <td class="num">-</td>
            <td class="num gain">{{ fmt($primes) }}</td>
            <td></td>
        </tr>
        @endif

        @if($indemn > 0)
        <tr>
            <td class="num">105</td>
            <td>Indemnités</td>
            <td class="num">-</td>
            <td class="num">-</td>
            <td class="num">-</td>
            <td class="num gain">{{ fmt($indemn) }}</td>
            <td></td>
        </tr>
        @endif

        @if($isKarama)
        <tr>
            <td class="num">106</td>
            <td>Subvention État (Karama)</td>
            <td class="num">-</td>
            <td class="num">-</td>
            <td class="num">Exo.</td>
            <td class="num" style="color:#553c9a;">400.000</td>
            <td></td>
        </tr>
        @endif

        @if($isCivp)
        <tr>
            <td class="num">106</td>
            <td>Bourse ANETI (CIVP)</td>
            <td class="num">-</td>
            <td class="num">-</td>
            <td class="num">Exo.</td>
            <td class="num" style="color:#744210;">200.000</td>
            <td></td>
        </tr>
        @endif

        <tr class="total-row">
            <td colspan="5"><strong>Total Brut</strong></td>
            <td class="num gain"><strong>{{ fmt($brut) }}</strong></td>
            <td></td>
        </tr>

        {{-- Section Cotisations --}}
        <tr class="section-row"><td colspan="7">Cotisations & Retenues</td></tr>

        <tr>
            <td class="num">351</td>
            <td>
                CNSS salariale
                @if($isCivp || $isKarama)
                    <span style="color:#2d6a4f;">(État)</span>
                @endif
            </td>
            <td class="num">-</td>
            <td class="num">{{ fmt(min($brut, 6000)) }}</td>
            <td class="num">{{ ($isCivp || $isKarama) ? '0%' : '9.68%' }}</td>
            <td></td>
            <td class="num retenue">{{ fmt($cnss) }}</td>
        </tr>

        <tr>
            <td class="num">352</td>
            <td>
                Salaire Net Imposable
                @if(!$isCivp)
                    <span style="font-size:7px;">(après abat. 20%)</span>
                @endif
            </td>
            <td class="num">-</td>
            <td class="num">{{ fmt($brut - $cnss) }}</td>
            <td class="num">{{ $isCivp ? '0%' : '20%' }}</td>
            <td></td>
            <td></td>
        </tr>

        <tr>
            <td class="num">353</td>
            <td>
                IRPP
                @if($isCivp)<span style="color:#2d6a4f;">(exonéré)</span>@endif
            </td>
            <td class="num">-</td>
            <td class="num">-</td>
            <td class="num">Barème</td>
            <td></td>
            <td class="num retenue">{{ fmt($irpp) }}</td>
        </tr>

        <tr>
            <td class="num">354</td>
            <td>
                CSS (Contribution Solidarité)
                @if($isCivp)<span style="color:#2d6a4f;">(exonérée)</span>@endif
            </td>
            <td class="num">-</td>
            <td class="num">-</td>
            <td class="num">0.5%</td>
            <td></td>
            <td class="num retenue">{{ fmt($css) }}</td>
        </tr>

        @if($retenue > 0)
        <tr>
            <td class="num">355</td>
            <td>Retenue absence non justifiée</td>
            <td class="num">{{ $joursSansSolde }}j</td>
            <td class="num">-</td>
            <td class="num">-</td>
            <td></td>
            <td class="num retenue">{{ fmt($retenue) }}</td>
        </tr>
        @endif

        <tr class="total-row">
            <td colspan="5"><strong>Total Cotisations</strong></td>
            <td></td>
            <td class="num retenue"><strong>{{ fmt($totalRet) }}</strong></td>
        </tr>

    </tbody>
</table>

{{-- ══ PRÉSENCES ══ --}}
<table class="main-table" style="margin-bottom:6px;">
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
            <td class="num">{{ $joursOuvres }}</td>
            <td class="num">{{ $joursTravailles }}</td>
            <td class="num" style="color:#2d6a4f;">{{ $joursConge }}</td>
            <td class="num" style="color:#c00;">{{ $joursSansSolde }}</td>
            <td class="num">{{ $joursPayes }}</td>
            <td class="num">{{ number_format($hs, 2) }} h</td>
        </tr>
    </tbody>
</table>

{{-- ══ RÉCAPITULATIF NET ══ --}}
<table class="recap-table">
    <thead>
        <tr>
            <th>Salaire Brut (TND)</th>
            <th>Charges Salariales (TND)</th>
            <th>Charges Patronales</th>
            <th>CNSS</th>
            <th>IRPP</th>
            <th>CSS</th>
            <th>Retenues</th>
            <th style="background:#c00;">NET À PAYER (TND)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ fmt($brut) }}</td>
            <td>{{ fmt($totalRet) }}</td>
            <td>0.000</td>
            <td>{{ fmt($cnss) }}</td>
            <td>{{ fmt($irpp) }}</td>
            <td>{{ fmt($css) }}</td>
            <td>{{ fmt($retenue) }}</td>
            <td class="net-cell">{{ fmt($net) }}</td>
        </tr>
    </tbody>
</table>

{{-- Note CIVP / Karama --}}
@if($isCivp)
<div class="note-box">
    <strong>Contrat CIVP :</strong> Exonération totale (CNSS = IRPP = CSS = 0).
    Le stagiaire reçoit en plus <strong>200 TND</strong> de bourse versée directement par l'ANETI.
    Revenu net total : <strong>{{ fmt($net + 200) }} TND</strong>.
</div>
@elseif($isKarama)
<div class="note-box note-box-karama">
    <strong>Contrat Karama :</strong> CNSS prise en charge par l'État.
    Part employeur nette : <strong>{{ fmt($net - 400) }} TND</strong> +
    Subvention État : <strong>400.000 TND</strong> =
    Net total : <strong>{{ fmt($net) }} TND</strong>.
</div>
@endif

{{-- Congés du mois --}}
@if($conges && $conges->count() > 0)
<table class="main-table" style="margin-bottom:6px;">
    <thead>
        <tr>
            <th>Type de congé</th>
            <th>Date début</th>
            <th>Date fin</th>
            <th>Jours ouvrés</th>
            <th>Motif</th>
            <th>Statut</th>
        </tr>
    </thead>
    <tbody>
        @foreach($conges as $c)
        <tr>
            <td>{{ $c->type ?? '-' }}</td>
            <td>{{ parseD($c->start_date) }}</td>
            <td>{{ parseD($c->end_date) }}</td>
            <td class="num">{{ $c->days_count ?? '-' }}</td>
            <td>{{ $c->reason ?? '-' }}</td>
            <td style="color:#2d6a4f; font-weight:bold;">{{ $c->status }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- ══ SIGNATURES ══ --}}
<table class="sig-table">
    <tr>
        <td>L'Employeur</td>
        <td>Le Responsable RH</td>
        <td>L'Employé(e)</td>
    </tr>
</table>

{{-- ══ PIED ══ --}}
<div class="footer">
    Bulletin de paie généré le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }} —
    {{ $fullPaymentInfo->month }} {{ $fullPaymentInfo->year }} —
    Réf. {{ $fullPaymentInfo->reference }} — Document confidentiel
</div>

</body>
</html>