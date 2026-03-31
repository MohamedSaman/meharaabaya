<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        :root {
            --phoenix-black: #050505;
            --phoenix-gold: #d4a63d;
            --phoenix-gold-dark: #8a6114;
            --phoenix-gold-soft: #f2dfb0;
        }

        /* Full-screen page */
        .login-container {
            min-height: 100vh;
            width: 100vw;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            position: relative;
            overflow: hidden;
        }

        .background-image {
            position: fixed;
            inset: 0;
            background-color: var(--phoenix-black);
            /* background-image: url('{{ asset('/images/bg.jpg') }}'); */
            background-size: cover;
            background-position: center;
            z-index: 0;
        }

        /* ---- Split card ---- */
        .login-card {
            position: relative;
            z-index: 1;
            display: flex;
            width: 100%;
            max-width: 900px;
            min-height: 560px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 32px 80px rgba(0, 0, 0, 0.55);
        }

        /* Left branding panel */
        .brand-panel {
            flex: 1 1 42%;
            background: linear-gradient(160deg, #050505 0%, #1a1a1a 45%, #8a6114 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 36px;
            text-align: center;
        }

        .brand-panel img.brand-logo {
            height: 88px;
            width: auto;
            object-fit: contain;
            margin-bottom: 20px;
            filter: drop-shadow(0 4px 16px rgba(0, 0, 0, 0.4));
        }

        .brand-panel h1 {
            color: #ffffff;
            font-size: 1.45rem;
            font-weight: 800;
            margin: 0 0 8px;
            letter-spacing: -0.02em;
        }

        .brand-panel .brand-tagline {
            color: var(--phoenix-gold-soft);
            font-size: 0.78rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin: 0 0 32px;
            line-height: 1.6;
        }

        .brand-divider {
            width: 48px;
            height: 2px;
            background: rgba(255, 255, 255, 0.25);
            border-radius: 2px;
            margin: 0 auto 28px;
        }

        .brand-meta {
            color: rgba(255, 255, 255, 0.75);
            font-size: 0.82rem;
            line-height: 2;
        }

        .brand-meta p {
            margin: 0;
        }

        .brand-meta i {
            margin-right: 6px;
            color: var(--phoenix-gold);
        }

        .brand-connect {
            display: flex;
            gap: 14px;
            justify-content: center;
            margin-top: 28px;
        }

        .connect-icon {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: #fff;
            font-size: 20px;
            text-decoration: none;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.3);
        }

        .connect-icon.email {
            background: #e84b3c;
        }

        .connect-icon.whatsapp {
            background: #25d366;
        }

        .connect-icon:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
        }

        /* Right form panel */
        .form-panel {
            flex: 1 1 58%;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 56px 52px;
        }

        .form-panel .panel-heading {
            margin-bottom: 36px;
        }

        .form-panel .panel-heading .user-ring {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #fff8e6;
            border: 2px solid #efd18d;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 18px;
        }

        .form-panel .panel-heading .user-ring i {
            font-size: 2rem;
            color: var(--phoenix-gold-dark);
        }

        .form-panel .panel-heading h2 {
            font-size: 1.6rem;
            font-weight: 700;
            color: #111;
            margin: 0 0 6px;
        }

        .form-panel .panel-heading p {
            color: #6b7280;
            font-size: 0.88rem;
            margin: 0;
        }

        /* Inputs */
        .input-wrap {
            position: relative;
            margin-bottom: 18px;
        }

        .input-wrap .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 1rem;
            pointer-events: none;
        }

        .input-wrap .form-control {
            padding: 14px 18px 14px 44px;
            border-radius: 10px;
            border: 1.5px solid #e5e7eb;
            font-size: 0.95rem;
            width: 100%;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: #f9fafb;
            color: #111;
        }

        .input-wrap .form-control:focus {
            outline: none;
            border-color: var(--phoenix-gold);
            box-shadow: 0 0 0 3px rgba(212, 166, 61, 0.22);
            background: #fff;
        }

        .input-wrap .form-control.is-invalid {
            border-color: #dc3545;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
        }

        @keyframes shake {
            0% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-6px);
            }

            50% {
                transform: translateX(6px);
            }

            75% {
                transform: translateX(-4px);
            }

            100% {
                transform: translateX(0);
            }
        }

        .shake {
            animation: shake 0.45s cubic-bezier(.36, .07, .19, .97);
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            font-size: 0.875rem;
        }

        .form-options .form-check-label {
            color: #374151;
        }

        .forgot-link {
            color: var(--phoenix-gold-dark);
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .login-btn {
            width: 100%;
            padding: 14px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--phoenix-gold-dark), var(--phoenix-gold));
            border: none;
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            cursor: pointer;
            transition: filter 0.2s, transform 0.15s;
            box-shadow: 0 4px 18px rgba(138, 97, 20, 0.35);
        }

        .login-btn:hover {
            filter: brightness(1.08);
            transform: translateY(-1px);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        /* Responsive: stack on small screens */
        @media (max-width: 680px) {
            .login-card {
                flex-direction: column;
                max-width: 420px;
            }

            .brand-panel {
                padding: 36px 28px 28px;
            }

            .brand-panel .brand-connect {
                margin-top: 20px;
            }

            .form-panel {
                padding: 36px 28px;
            }

            .brand-panel h1 {
                font-size: 1.2rem;
            }
        }
    </style>

</head>

<body>
    <div class="font-sans text-gray-900 antialiased">
        {{ $slot }}
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>