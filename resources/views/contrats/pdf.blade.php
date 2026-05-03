<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #19a891; padding-bottom: 15px; }
        .header h1 { color: #19a891; font-size: 24px; margin: 0; }
        .section { margin-bottom: 20px; }
        .section h3 { background: #19a891; color: white; padding: 8px 12px; margin: 0 0 10px 0; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; }
        table td { padding: 8px 12px; border-bottom: 1px solid #eee; }
        table td:first-child { font-weight: bold; color: #555; width: 40%; }
        .footer { text-align: center; margin-top: 50px; font-size: 11px; color: #999; }
        .badge { background: #19a891; color: white; padding: 3px 10px; border-radius: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CONTRAT DE TRAVAIL</h1>
        <p>Généré le {{ \Carbon\Carbon::now()->format('d/m/Y') }}</p>
    </div>

    <div class="section">
        <h3>Informations de l'employé</h3>
        <table>
            <tr><td>Nom complet</td><td>{{ $employer->nom }} {{ $employer->prenom }}</td></tr>
            <tr><td>Email</td><td>{{ $employer->email }}</td></tr>
            <tr><td>Contact</td><td>{{ $employer->contact ?? '-' }}</td></tr>
            <tr><td>Département</td><td>{{ $employer->departement->name ?? '-' }}</td></tr>
        </table>
    </div>

    <div class="section">
        <h3>Informations du contrat</h3>
        <table>
            <tr><td>Type de contrat</td><td><span class="badge">{{ $employer->type_contrat }}</span></td></tr>
            <tr><td>Date de début</td><td>{{ $employer->date_debut ? \Carbon\Carbon::parse($employer->date_debut)->format('d/m/Y') : '-' }}</td></tr>
            <tr><td>Date de fin</td><td>{{ $employer->date_fin ? \Carbon\Carbon::parse($employer->date_fin)->format('d/m/Y') : 'Indéterminée (CDI)' }}</td></tr>
            <tr><td>Numéro CNSS</td><td>{{ $employer->cnss ?? '-' }}</td></tr>
            <tr><td>RIB bancaire</td><td>{{ $employer->rib ?? '-' }}</td></tr>
            <tr><td>Salaire mensuel</td><td>{{ $employer->montant_journalier ?? '-' }} DT</td></tr>
        </table>
    </div>

    <div class="footer">
        <p>Ce document est généré automatiquement par le système TP APP</p>
    </div>
</body>
</html>