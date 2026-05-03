<x-guest-layout>
    <style>
        .verify-text {
            color: hsl(189, 60%, 40%);
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 20px;
            text-align: center;
        }

        .verify-success {
            background: rgba(34, 197, 94, 0.1);
            color: #166534;
            border: 1px solid rgba(34, 197, 94, 0.3);
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 0.88rem;
            margin-bottom: 20px;
            text-align: center;
        }

        .btn-verify {
            padding: 12px 24px;
            background: linear-gradient(135deg, hsl(194, 100%, 46%) 0%, hsl(189, 100%, 23%) 100%);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 174, 199, 0.4);
        }

        .btn-logout {
            background: none;
            border: none;
            color: #dc2626;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: underline;
            transition: color 0.2s ease;
        }

        .btn-logout:hover {
            color: #b91c1c;
        }

        .verify-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 20px;
        }
    </style>

    <p class="verify-text">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="verify-success">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="verify-actions">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn-verify">
                {{ __('Resend Verification Email') }}
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>

</x-guest-layout>