<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; padding: 20px;">
    <h2 style="color: #e74c3c;">⚠️ Votre contrat expire bientôt</h2>
    <p>Bonjour {{ $employer->nom }} {{ $employer->prenom }},</p>
    <p>Nous vous informons que votre contrat de travail expire bientôt :</p>
    <table style="border-collapse: collapse; width: 100%;">
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd;"><strong>Type de contrat</strong></td>
            <td style="padding: 8px; border: 1px solid #ddd;">{{ $employer->type_contrat }}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd;"><strong>Date de fin</strong></td>
            <td style="padding: 8px; border: 1px solid #ddd; color: #e74c3c;">
                {{ \Carbon\Carbon::parse($employer->date_fin)->format('d/m/Y') }}
            </td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd;"><strong>Jours restants</strong></td>
            <td style="padding: 8px; border: 1px solid #ddd; color: #e74c3c;">
                {{ \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($employer->date_fin)) }} jours
            </td>
        </tr>
    </table>
    <p style="margin-top: 20px;">Pour toute question, contactez votre RH.</p>
    <p>Cordialement,<br><strong>Plateforme Salaire</strong></p>
</body>
</html>