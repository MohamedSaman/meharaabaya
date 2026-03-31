@php
    // Determine which layout to use based on user role
    $layout = auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.staff';
@endphp

<x-dynamic-component :component="$layout">
    <x-slot name="title">
        Profile
    </x-slot>

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1 fw-bold">My Profile</h4>
                <p class="text-muted mb-0">Manage your account information and security settings</p>
            </div>
        </div>

        <!-- Profile Information -->
        @if (Laravel\Fortify\Features::canUpdateProfileInformation())
            <div class="card mb-4">
                <div class="card-body">
                    @livewire('profile.update-profile-information-form')
                </div>
            </div>
        @endif

        <!-- Update Password -->
        @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
            <div class="card mb-4">
                <div class="card-body">
                    @livewire('profile.update-password-form')
                </div>
            </div>
        @endif

        <!-- Two Factor Authentication -->
        @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
            <div class="card mb-4">
                <div class="card-body">
                    @livewire('profile.two-factor-authentication-form')
                </div>
            </div>
        @endif

        <!-- Browser Sessions -->
        <div class="card mb-4">
            <div class="card-body">
                @livewire('profile.logout-other-browser-sessions-form')
            </div>
        </div>

        <!-- Delete Account -->
        @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
            <div class="card mb-4">
                <div class="card-body">
                    @livewire('profile.delete-user-form')
                </div>
            </div>
        @endif
    </div>

    @push('styles')
    <style>
        /* Additional profile page styling */
        .card {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-radius: 10px;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        /* Override Jetstream default styles to match Bootstrap */
        .mt-1 { margin-top: 0.25rem !important; }
        .mt-2 { margin-top: 0.5rem !important; }
        .mt-3 { margin-top: 1rem !important; }
        .me-3 { margin-right: 1rem !important; }
        
        /* Form styling */
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="file"] {
            width: 100%;
        }
    </style>
    @endpush
</x-dynamic-component>
