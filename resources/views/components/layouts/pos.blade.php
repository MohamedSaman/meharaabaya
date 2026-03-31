<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'POS' }} | {{ config('shop.name', 'POS') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
            font-family: 'Inter', sans-serif;
            background: #fffaf0;
        }

        @media (max-width: 992px) {

            html,
            body {
                height: auto;
                min-height: 100%;
                overflow-x: hidden;
                overflow-y: auto;
            }
        }

        body[data-theme='dark'] {
            background: #111827;
            color: #e5e7eb;
        }

        body[data-theme='dark'] .card,
        body[data-theme='dark'] .modal-content,
        body[data-theme='dark'] .table,
        body[data-theme='dark'] .dropdown-menu,
        body[data-theme='dark'] .list-group-item {
            background-color: #1f2937 !important;
            color: #e5e7eb !important;
            border-color: #374151 !important;
        }

        body[data-theme='dark'] .form-control,
        body[data-theme='dark'] .form-select {
            background-color: #111827;
            color: #e5e7eb;
            border-color: #374151;
        }

        body[data-theme='dark'] .btn-close {
            filter: invert(1);
        }

        .theme-toggle-btn {
            border: 1px solid #d4a63d !important;
            color: #8a6114 !important;
            background: rgba(255, 250, 240, 0.92) !important;
            border-radius: 9999px;
            min-width: 40px;
            font-weight: 600;
            box-shadow: 0 6px 18px rgba(138, 97, 20, 0.08);
        }

        .theme-toggle-btn:hover,
        .theme-toggle-btn:focus {
            background: #d4a63d !important;
            color: #ffffff !important;
            border-color: #d4a63d !important;
        }

        body[data-theme='dark'] .theme-toggle-btn {
            border-color: #d4a63d !important;
            color: #facc15 !important;
            background: rgba(255, 255, 255, 0.06) !important;
            box-shadow: none;
        }

        body[data-theme='dark'] .theme-toggle-btn:hover,
        body[data-theme='dark'] .theme-toggle-btn:focus {
            background: #d4a63d !important;
            color: #111827 !important;
        }
    </style>

    @livewireStyles
    @stack('styles')
</head>

<body>
    {{ $slot }}

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggleBtn = document.getElementById('themeToggleBtn');
            const themeToggleIcon = document.getElementById('themeToggleIcon');
            const themeToggleText = document.getElementById('themeToggleText');
            const themeStorageKey = 'phoenix-theme';
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            const savedTheme = localStorage.getItem(themeStorageKey);
            const darkThemeOverrideId = 'phoenix-dark-overrides';
            const darkThemeOverridesCss = `
                body[data-theme='dark'] .container,
                body[data-theme='dark'] .container-fluid,
                body[data-theme='dark'] .card,
                body[data-theme='dark'] .card-body,
                body[data-theme='dark'] .card-header,
                body[data-theme='dark'] .card-footer,
                body[data-theme='dark'] .modal-content,
                body[data-theme='dark'] .table,
                body[data-theme='dark'] .table-responsive,
                body[data-theme='dark'] .dropdown-menu,
                body[data-theme='dark'] .list-group-item,
                body[data-theme='dark'] .bg-white,
                body[data-theme='dark'] .bg-light,
                body[data-theme='dark'] [style*='background-color:#fff'],
                body[data-theme='dark'] [style*='background-color: #fff'],
                body[data-theme='dark'] [style*='background-color:#ffffff'],
                body[data-theme='dark'] [style*='background-color: #ffffff'],
                body[data-theme='dark'] [style*='background-color:#f8f9fa'],
                body[data-theme='dark'] [style*='background-color: #f8f9fa'],
                body[data-theme='dark'] [style*='background-color:#fffaf0'],
                body[data-theme='dark'] [style*='background-color: #fffaf0'],
                body[data-theme='dark'] [style*='background: #fff'],
                body[data-theme='dark'] [style*='background:#fff'],
                body[data-theme='dark'] [style*='background: white'],
                body[data-theme='dark'] [style*='background:white'] {
                    background: #1f2937 !important;
                    background-color: #1f2937 !important;
                    color: #e5e7eb !important;
                    border-color: #374151 !important;
                }

                body[data-theme='dark'] .table {
                    --bs-table-bg: #1f2937;
                    --bs-table-color: #e5e7eb;
                    --bs-table-border-color: #374151;
                    --bs-table-striped-bg: #111827;
                    --bs-table-striped-color: #e5e7eb;
                    --bs-table-active-bg: #111827;
                    --bs-table-active-color: #f9fafb;
                    --bs-table-hover-bg: #273449;
                    --bs-table-hover-color: #f9fafb;
                }

                body[data-theme='dark'] .table > :not(caption) > * > * {
                    color: var(--bs-table-color) !important;
                    background-color: var(--bs-table-bg) !important;
                    border-bottom-color: #374151 !important;
                }

                body[data-theme='dark'] h1,
                body[data-theme='dark'] h2,
                body[data-theme='dark'] h3,
                body[data-theme='dark'] h4,
                body[data-theme='dark'] h5,
                body[data-theme='dark'] h6,
                body[data-theme='dark'] p,
                body[data-theme='dark'] span,
                body[data-theme='dark'] label,
                body[data-theme='dark'] td,
                body[data-theme='dark'] th,
                body[data-theme='dark'] .text-dark,
                body[data-theme='dark'] [style*='color:#212529'],
                body[data-theme='dark'] [style*='color: #212529'],
                body[data-theme='dark'] [style*='color:#495057'],
                body[data-theme='dark'] [style*='color: #495057'] {
                    color: #e5e7eb !important;
                }

                body[data-theme='dark'] .text-muted,
                body[data-theme='dark'] [style*='color:#6c757d'],
                body[data-theme='dark'] [style*='color: #6c757d'] {
                    color: #9ca3af !important;
                }

                body[data-theme='dark'] .form-control,
                body[data-theme='dark'] .form-select,
                body[data-theme='dark'] textarea,
                body[data-theme='dark'] input {
                    background-color: #111827 !important;
                    color: #e5e7eb !important;
                    border-color: #374151 !important;
                }

                body[data-theme='dark'] .swal2-popup {
                    background: #1f2937 !important;
                    color: #e5e7eb !important;
                }
            `;

            function syncDarkThemeOverrides(currentTheme) {
                const existingStyle = document.getElementById(darkThemeOverrideId);
                if (currentTheme === 'dark') {
                    if (!existingStyle) {
                        const styleEl = document.createElement('style');
                        styleEl.id = darkThemeOverrideId;
                        styleEl.textContent = darkThemeOverridesCss;
                        document.head.appendChild(styleEl);
                    }
                } else if (existingStyle) {
                    existingStyle.remove();
                }
            }

            const darkInlineOriginalAttr = 'data-dark-inline-original';
            const colorResolveCache = new Map();

            function resolveRgb(colorValue) {
                if (!colorValue) return null;

                const key = colorValue.trim().toLowerCase();
                if (colorResolveCache.has(key)) {
                    return colorResolveCache.get(key);
                }

                const probe = document.createElement('span');
                probe.style.color = key;
                probe.style.display = 'none';
                document.body.appendChild(probe);
                const resolved = window.getComputedStyle(probe).color;
                probe.remove();

                const match = resolved.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/i);
                const rgb = match ? {
                    r: Number(match[1]),
                    g: Number(match[2]),
                    b: Number(match[3])
                } : null;

                colorResolveCache.set(key, rgb);
                return rgb;
            }

            function luminance(rgb) {
                if (!rgb) return 0;
                return (0.2126 * rgb.r + 0.7152 * rgb.g + 0.0722 * rgb.b) / 255;
            }

            function shouldDarkenBackground(value) {
                if (!value) return false;
                const normalized = value.toLowerCase();
                if (normalized.includes('gradient') || normalized.includes('transparent')) return false;
                const rgb = resolveRgb(value);
                if (!rgb) return false;
                return luminance(rgb) > 0.62;
            }

            function shouldLightenText(value) {
                if (!value) return false;
                const rgb = resolveRgb(value);
                if (!rgb) return false;
                return luminance(rgb) < 0.48;
            }

            function normalizeInlineStylesForDark() {
                const scope = document.body;
                scope.querySelectorAll('[style]').forEach(el => {
                    if (!el.hasAttribute(darkInlineOriginalAttr)) {
                        el.setAttribute(darkInlineOriginalAttr, el.getAttribute('style') || '');
                    }

                    const bgColor = el.style.getPropertyValue('background-color');
                    if (shouldDarkenBackground(bgColor)) {
                        el.style.setProperty('background-color', '#1f2937', 'important');
                    }

                    const bg = el.style.getPropertyValue('background');
                    if (bg && !bg.toLowerCase().includes('gradient') && shouldDarkenBackground(bg)) {
                        el.style.setProperty('background', '#1f2937', 'important');
                    }

                    const color = el.style.getPropertyValue('color');
                    if (shouldLightenText(color)) {
                        el.style.setProperty('color', '#e5e7eb', 'important');
                    }

                    const borderColor = el.style.getPropertyValue('border-color');
                    if (borderColor && shouldDarkenBackground(borderColor)) {
                        el.style.setProperty('border-color', '#374151', 'important');
                    }
                });
            }

            function restoreInlineStylesFromDark() {
                document.querySelectorAll('[' + darkInlineOriginalAttr + ']').forEach(el => {
                    const originalStyle = el.getAttribute(darkInlineOriginalAttr);
                    if (originalStyle === '') {
                        el.removeAttribute('style');
                    } else {
                        el.setAttribute('style', originalStyle);
                    }
                    el.removeAttribute(darkInlineOriginalAttr);
                });
            }

            function refreshThemeNormalization(currentTheme) {
                if (currentTheme === 'dark') {
                    normalizeInlineStylesForDark();
                } else {
                    restoreInlineStylesFromDark();
                }
            }

            function scheduleThemeNormalization() {
                const currentTheme = document.body.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
                window.requestAnimationFrame(() => {
                    refreshThemeNormalization(currentTheme);
                });
            }

            function applyTheme(currentTheme) {
                document.body.setAttribute('data-theme', currentTheme);
                document.documentElement.setAttribute('data-theme', currentTheme);
                syncDarkThemeOverrides(currentTheme);
                refreshThemeNormalization(currentTheme);

                if (themeToggleIcon) {
                    themeToggleIcon.className = currentTheme === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
                }

                if (themeToggleText) {
                    themeToggleText.textContent = currentTheme === 'dark' ? 'Light' : 'Dark';
                }
            }

            const initialTheme = savedTheme === 'dark' || savedTheme === 'light' ? savedTheme : (prefersDark ? 'dark' : 'light');
            applyTheme(initialTheme);

            if (themeToggleBtn) {
                themeToggleBtn.addEventListener('click', function() {
                    const currentTheme = document.body.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
                    const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
                    applyTheme(nextTheme);
                    localStorage.setItem(themeStorageKey, nextTheme);
                });
            }

            const themeMutationObserver = new MutationObserver(function() {
                if (document.body.getAttribute('data-theme') === 'dark') {
                    scheduleThemeNormalization();
                }
            });
            themeMutationObserver.observe(document.body, {
                childList: true,
                subtree: true
            });

            document.addEventListener('livewire:load', function() {
                if (typeof Livewire !== 'undefined' && Livewire.hook) {
                    Livewire.hook('message.processed', () => {
                        scheduleThemeNormalization();
                    });
                }
            });
        });
    </script>

    @livewireScripts
    @stack('scripts')
</body>

</html>