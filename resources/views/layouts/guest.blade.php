<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'RiseTrack') }}</title>

        <!-- Favicon -->
        <link rel="shortcut icon" href="{{ asset('assets/images/logo.png') }}" type="image/png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body {
                background: hsl(210, 29%, 97%) !important;
                font-family: 'Figtree', sans-serif;
            }

            .login-wrapper {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
                background: linear-gradient(135deg, hsl(186, 67%, 94%) 0%, hsl(210, 29%, 97%) 100%);
            }

            .login-box {
                background: #ffffff;
                border-radius: 20px;
                box-shadow: 0 12px 40px rgba(0, 174, 199, 0.15);
                padding: 48px 40px;
                width: 100%;
                max-width: 440px;
            }

            .login-header {
                text-align: center;
                margin-bottom: 36px;
            }

            .login-header img {
                width: 80px;
                height: 80px;
                object-fit: contain;
                margin-bottom: 16px;
            }

            .login-header h1 {
                font-size: 1.8rem;
                font-weight: 700;
                color: hsl(189, 100%, 23%);
                margin: 0 0 6px 0;
            }

            .login-header p {
                color: hsl(189, 60%, 40%);
                font-size: 0.9rem;
                margin: 0;
            }

            /* Inputs */
            .login-box input[type="email"],
            .login-box input[type="password"],
            .login-box input[type="text"] {
                width: 100%;
                padding: 12px 16px;
                border: 1.5px solid hsl(186, 67%, 88%);
                border-radius: 10px;
                font-size: 14px;
                transition: all 0.3s ease;
                background: #fff;
                color: hsl(0, 0%, 10%);
                margin-top: 6px;
                box-sizing: border-box;
            }

            .login-box input[type="email"]:focus,
            .login-box input[type="password"]:focus,
            .login-box input[type="text"]:focus {
                outline: none;
                border-color: hsl(194, 100%, 46%);
                box-shadow: 0 0 0 3px rgba(0, 194, 220, 0.15);
            }

            /* Labels */
            .login-box label {
                font-weight: 600;
                color: hsl(189, 100%, 23%);
                font-size: 0.88rem;
            }

            /* Checkbox */
            .login-box input[type="checkbox"] {
                accent-color: hsl(194, 100%, 46%);
                width: 15px;
                height: 15px;
            }

            /* Button */
            .login-box button[type="submit"] {
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

            .login-box button[type="submit"]:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(0, 174, 199, 0.4);
            }

            /* Links */
            .login-box a {
                color: hsl(194, 100%, 46%);
                text-decoration: none;
                font-size: 0.85rem;
                font-weight: 500;
                transition: color 0.2s ease;
            }

            .login-box a:hover {
                color: hsl(189, 100%, 23%);
                text-decoration: underline;
            }

            /* Divider */
            .login-box hr {
                border-color: hsl(186, 67%, 88%);
                margin: 20px 0;
            }

            /* Error */
            .login-box .text-red-600,
            .login-box p.text-sm {
                font-size: 0.8rem;
                margin-top: 4px;
            }

            /* Remember me text */
            .login-box .remember-text {
                color: hsl(189, 100%, 23%);
                font-size: 0.88rem;
            }
        </style>
    </head>
    <body>
        <div class="login-wrapper">
            <div class="login-box">

                {{-- Header avec logo --}}
                <div class="login-header">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="RiseTrack Logo">
                    <h1>RiseTrack</h1>
                    <p>Connectez-vous à votre espace</p>
                </div>

                {{ $slot }}

            </div>
        </div>
    </body>
</html>