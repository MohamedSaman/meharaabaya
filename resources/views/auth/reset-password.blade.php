<x-guest-layout>
    <div class="login-container">
        @php
        $shopPhoneForWa = preg_replace('/\D+/', '', config('shop.whatsapp'));
        @endphp

        <!-- Full-screen background -->
        <div class="background-image"></div>

        <!-- Split card -->
        <div class="login-card">

            <!-- ===== LEFT: Branding panel ===== -->
            <div class="brand-panel">
                <img src="{{ asset('images/logo.png') }}" alt="{{ config('shop.name') }}" class="brand-logo">
                <h1>{{ config('shop.name') }}</h1>
                <p class="brand-tagline">{{ config('shop.tagline') }}</p>

                <div class="brand-divider"></div>

                <div class="brand-meta">
                    <p><i class="bi bi-telephone-fill"></i>{{ config('shop.phone') }}</p>
                    <p><i class="bi bi-geo-alt-fill"></i>{{ config('shop.address') }}</p>
                </div>

                <div class="brand-connect">
                    <a href="mailto:{{ config('shop.email') }}" class="connect-icon email" title="Email us">
                        <i class="bi bi-envelope-fill"></i>
                    </a>
                    <a href="https://api.whatsapp.com/send/?phone={{ $shopPhoneForWa }}&text=Hi%2C+I%27m+interested+in+your+bathware+products.&type=phone_number&app_absent=0"
                        target="_blank" rel="noopener noreferrer"
                        class="connect-icon whatsapp" title="WhatsApp us">
                        <i class="bi bi-whatsapp"></i>
                    </a>
                </div>
            </div>

            <!-- ===== RIGHT: Form panel ===== -->
            <div class="form-panel">
                <div class="panel-heading">
                    <div class="user-ring">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h2>Reset Password</h2>
                    <p>Enter your new password below.</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger mb-4 rounded-3 border-0 shadow-sm" style="font-size: 0.85rem; padding: 12px; background-color: #f8dbdb; color: #b02a37;">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <!-- Email field -->
                    <div class="input-wrap mb-3">
                        <i class="bi bi-envelope input-icon"></i>
                        <input id="email" type="email"
                            class="form-control"
                            name="email" value="{{ old('email', $request->email) }}"
                            required autofocus autocomplete="username"
                            placeholder="Email address">
                    </div>

                    <!-- Password field -->
                    <div class="input-wrap mb-3">
                        <i class="bi bi-lock input-icon"></i>
                        <input id="password" type="password"
                            class="form-control"
                            name="password" required autocomplete="new-password"
                            placeholder="New Password">
                    </div>

                    <!-- Confirm Password field -->
                    <div class="input-wrap mb-4">
                        <i class="bi bi-shield-lock-fill input-icon"></i>
                        <input id="password_confirmation" type="password"
                            class="form-control"
                            name="password_confirmation" required autocomplete="new-password"
                            placeholder="Confirm New Password">
                    </div>

                    <!-- Submit button -->
                    <button type="submit" class="login-btn">
                        <i class="bi bi-check2-circle me-2"></i>Reset Password
                    </button>

                </form>
            </div>

        </div>
    </div>
</x-guest-layout>
