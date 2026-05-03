<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300" name="remember">
                <span class="ms-2 text-sm" style="color: hsl(189, 100%, 23%);">{{ __('Remember me') }}</span>
            </label>
        </div>

        <!-- Forgot + Button -->
        <div class="mt-4">
            @if (Route::has('password.request'))
                <div class="text-right mb-3">
                    <a href="{{ route('password.request') }}" style="color: hsl(194, 100%, 46%); font-size:0.85rem; font-weight:500;">
                        {{ __('Forgot your password?') }}
                    </a>
                </div>
            @endif

            <button type="submit" class="w-full py-3" 
                style="background: linear-gradient(135deg, hsl(194, 100%, 46%) 0%, hsl(189, 100%, 23%) 100%); color:#fff; border:none; border-radius:10px; font-size:15px; font-weight:700; cursor:pointer; transition: all 0.3s ease; letter-spacing:0.5px;">
                {{ __('Log in') }}
            </button>
        </div>

    </form>
</x-guest-layout>