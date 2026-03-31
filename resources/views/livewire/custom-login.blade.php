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
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <h2>Welcome Back</h2>
                    <p>Sign in to your account to continue</p>
                </div>

                <form wire:submit.prevent="login">

                    <!-- Email field -->
                    <div class="input-wrap">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="email"
                            class="form-control {{ $errors->has('email') ? 'is-invalid shake' : '' }}"
                            wire:model="email"
                            placeholder="Enter Email"
                            required
                            aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
                        @error('email')
                        <div class="invalid-feedback d-block" style="padding-left:4px;font-size:0.82rem;">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password field -->
                    <div class="input-wrap">
                        <i class="bi bi-lock input-icon"></i>
                        <input type="password"
                            class="form-control {{ $errors->has('password') ? 'is-invalid shake' : '' }}"
                            wire:model="password"
                            placeholder="Enter Password"
                            required
                            aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}">
                        @error('password')
                        <div class="invalid-feedback d-block" style="padding-left:4px;font-size:0.82rem;">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Remember & Forgot options -->
                    <div class="form-options">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" wire:model="remember" id="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link">Forgot Password?</a>
                        @endif
                    </div>

                    <!-- Login button -->
                    <button type="submit" class="login-btn">Login</button>

                </form>
            </div>

        </div>
    </div>