<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Définir mon mot de passe - RiseTrack</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/logo.png') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('assets/css/portal.css') }}">
    <style>
        body {
            background: linear-gradient(135deg, hsl(186, 67%, 94%) 0%, hsl(210, 29%, 97%) 100%) !important;
            min-height: 100vh;
        }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-box {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 12px 40px rgba(0, 174, 199, 0.15);
            padding: 48px 40px;
            width: 100%;
            max-width: 460px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-header img {
            width: 75px;
            height: 75px;
            object-fit: contain;
            margin-bottom: 12px;
        }

        .login-header h1 {
            font-size: 1.6rem;
            font-weight: 700;
            color: hsl(189, 100%, 23%);
            margin: 0 0 6px 0;
        }

        .login-header p {
            color: hsl(189, 60%, 40%);
            font-size: 0.9rem;
            margin: 0;
        }

        .form-label {
            font-weight: 600;
            color: hsl(189, 100%, 23%);
            font-size: 0.88rem;
            margin-bottom: 6px;
        }

        .form-control {
            padding: 12px 16px !important;
            border: 1.5px solid hsl(186, 67%, 88%) !important;
            border-radius: 10px !important;
            font-size: 14px !important;
            transition: all 0.3s ease !important;
            background: #fff !important;
            color: hsl(0, 0%, 10%) !important;
        }

        .form-control:focus {
            outline: none !important;
            border-color: hsl(194, 100%, 46%) !important;
            box-shadow: 0 0 0 3px rgba(0, 194, 220, 0.15) !important;
        }

        .form-control[readonly] {
            background: hsl(186, 67%, 94%) !important;
            color: hsl(189, 100%, 23%) !important;
        }

        .btn-submit {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, hsl(194, 100%, 46%) 0%, hsl(189, 100%, 23%) 100%);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            letter-spacing: 0.5px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 174, 199, 0.4);
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            color: #166534;
            border: 1px solid rgba(34, 197, 94, 0.3);
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 0.88rem;
            margin-bottom: 16px;
        }

        .alert-danger {
            background: rgba(220, 38, 38, 0.1);
            color: #991b1b;
            border: 1px solid rgba(220, 38, 38, 0.3);
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 0.88rem;
            margin-bottom: 16px;
        }

        .text-danger {
            color: #dc2626 !important;
            font-size: 0.8rem;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-box">

            {{-- Header --}}
            <div class="login-header">
                <img src="{{ asset('assets/images/logo.png') }}" alt="RiseTrack Logo">
                <h1>RiseTrack</h1>
                <p>Définissez votre mot de passe</p>
            </div>

            @if(session('success_message'))
                <div class="alert-success">{{ session('success_message') }}</div>
            @endif
            @if(session('error_message'))
                <div class="alert-danger">{{ session('error_message') }}</div>
            @endif

            <form method="POST" action="{{ route('employer_space.submitDefinePassword', $email) }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control"
                        value="{{ $email }}" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Code de validation</label>
                    <input type="text" name="code" class="form-control"
                        placeholder="Entrez le code reçu par email"
                        value="{{ old('code') }}">
                    @error('code')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-control"
                        placeholder="Minimum 6 caractères" required>
                    @error('password')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Confirmer le mot de passe</label>
                    <input type="password" name="confirm_password" class="form-control"
                        placeholder="Répétez le mot de passe" required>
                </div>

                <button type="submit" class="btn-submit">
                    Valider
                </button>
            </form>

        </div>
    </div>

    <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
</body>
</html>