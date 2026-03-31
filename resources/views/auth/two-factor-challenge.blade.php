<x-guest-layout>
    <style>[x-cloak] { display: none !important; }</style>
    
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
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <h2>Two-Factor Auth</h2>
                    <p id="desc-code">Enter your 6-digit Google Authenticator code</p>
                    <p id="desc-recovery" style="display:none;">Enter your emergency recovery code</p>
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

                <form method="POST" action="{{ route('two-factor.login') }}">
                    @csrf

                    <!-- Auth Code field -->
                    <div class="input-wrap" id="wrap-code">
                        <i class="bi bi-phone input-icon"></i>
                        <input id="code" type="text" inputmode="numeric"
                            class="form-control"
                            name="code"
                            autofocus autocomplete="one-time-code"
                            placeholder="Enter 6-digit PIN">
                    </div>

                    <!-- Recovery Code field -->
                    <div class="input-wrap" id="wrap-recovery" style="display:none;">
                        <i class="bi bi-key input-icon"></i>
                        <input id="recovery_code" type="text"
                            class="form-control"
                            name="recovery_code" autocomplete="one-time-code"
                            placeholder="Enter Recovery Code">
                    </div>

                    <!-- Toggle option -->
                    <div class="form-options mb-4 mt-2" style="justify-content: flex-end;">
                        <button type="button" class="forgot-link bg-transparent border-0 p-0"
                            id="btn-use-recovery"
                            onclick="toggleRecovery(true)">
                            Use a recovery code
                        </button>

                        <button type="button" class="forgot-link bg-transparent border-0 p-0"
                            id="btn-use-code" style="display:none;"
                            onclick="toggleRecovery(false)">
                            Use an authentication code
                        </button>
                    </div>

                    <!-- Login button -->
                    <button type="submit" class="login-btn"><i class="bi bi-shield-check me-2"></i>Verify & Login</button>

                </form>

                <script>
                    function toggleRecovery(showRecovery) {
                        document.getElementById('desc-code').style.display = showRecovery ? 'none' : 'block';
                        document.getElementById('desc-recovery').style.display = showRecovery ? 'block' : 'none';
                        
                        document.getElementById('wrap-code').style.display = showRecovery ? 'none' : 'block';
                        document.getElementById('code').value = '';
                        
                        document.getElementById('wrap-recovery').style.display = showRecovery ? 'block' : 'none';
                        document.getElementById('recovery_code').value = '';
                        
                        document.getElementById('btn-use-recovery').style.display = showRecovery ? 'none' : 'block';
                        document.getElementById('btn-use-code').style.display = showRecovery ? 'block' : 'none';
                        
                        if (showRecovery) {
                            setTimeout(() => document.getElementById('recovery_code').focus(), 50);
                        } else {
                            setTimeout(() => document.getElementById('code').focus(), 50);
                        }
                    }
                </script>
            </div>

        </div>
    </div>
</x-guest-layout>
