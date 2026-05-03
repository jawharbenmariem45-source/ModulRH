<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: white; border-radius: 8px; overflow: hidden; }
        .header { background: #19a891; padding: 30px; text-align: center; }
        .header h1 { color: white; margin: 0; font-size: 22px; }
        .body { padding: 30px; }
        .body p { color: #555; line-height: 1.6; }
        .credentials { background: #f8f9fa; border-left: 4px solid #19a891; padding: 20px; margin: 20px 0; border-radius: 4px; }
        .credentials p { margin: 8px 0; font-size: 15px; }
        .credentials strong { color: #19a891; }
        .btn { display: inline-block; background: #19a891; color: white; padding: 12px 30px; border-radius: 5px; text-decoration: none; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #aaa; font-size: 12px; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bienvenue sur TP APP</h1>
        </div>
        <div class="body">
            <p>Bonjour <strong>{{ $employer->prenom }} {{ $employer->nom }}</strong>,</p>
            <p>Votre compte employé a été créé. Voici vos identifiants de connexion :</p>

            <div class="credentials">
                <p>📧 <strong>Email :</strong> {{ $employer->email }}</p>
                <p>🔑 <strong>Mot de passe :</strong> {{ $motDePasse }}</p>
            </div>

            <p>Connectez-vous à votre espace personnel en cliquant sur le bouton ci-dessous :</p>

            <a href="{{ url('/espace-employe/login') }}" class="btn">
                Accéder à mon espace
            </a>

            <p style="color:#e74c3c; font-size:13px;">
                ⚠️ Pour des raisons de sécurité, nous vous recommandons de changer votre mot de passe après votre première connexion.
            </p>
        </div>
        <div class="footer">
            <p>Cet email a été envoyé automatiquement par TP APP — Ne pas répondre</p>
        </div>
    </div>
</body>
</html>