@props([
    'title' => 'Transaksi',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <meta name="color-scheme" content="light dark" />
        <meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)" />
        <meta name="theme-color" content="#0f172a" media="(prefers-color-scheme: dark)" />
        <meta name="apple-mobile-web-app-status-bar-style" content="default" />
        <link rel="manifest" href="{{ asset('manifest.json') }}" />
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any" />
        <link rel="apple-touch-icon" href="{{ asset('assets/logo.png') }}" />

        <title>{{ $title }} - SiKaMo</title>

        <!-- Theme Initialization Script -->
        <script>
            (function() {
                window.sikamoApplyTheme = function(theme) {
                    const selectedTheme = theme || localStorage.getItem('theme') || 'system';
                    const shouldUseDark = selectedTheme === 'dark'
                        || (selectedTheme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);

                    document.documentElement.classList.toggle('dark', shouldUseDark);
                    document.documentElement.style.colorScheme = shouldUseDark ? 'dark' : 'light';
                };

                window.sikamoApplyTheme();
            })();
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/barcode-scanner.js'])
        @filamentStyles
        @livewireStyles

        <!-- Toast Notification CSS -->
        <style>
            .toast-container {
                position: fixed;
                top: 1rem;
                right: 1rem;
                z-index: 99999;
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
                pointer-events: none;
            }
            .toast-item {
                pointer-events: auto;
                display: flex;
                align-items: center;
                gap: 0.75rem;
                min-width: 300px;
                max-width: 420px;
                padding: 0.875rem 1rem;
                border-radius: 0.75rem;
                color: #fff;
                font-size: 0.875rem;
                font-weight: 500;
                box-shadow: 0 10px 25px -5px rgba(0,0,0,.2), 0 4px 6px -2px rgba(0,0,0,.1);
                animation: toastSlideIn 0.35s cubic-bezier(0.21, 1.02, 0.73, 1) forwards;
                cursor: pointer;
                overflow: hidden;
                position: relative;
            }
            .toast-item.toast-removing {
                animation: toastSlideOut 0.3s ease-in forwards;
            }
            .toast-item.toast-success {
                background: linear-gradient(135deg, #059669, #1D9E75);
            }
            .toast-item.toast-error {
                background: linear-gradient(135deg, #dc2626, #E24B4A);
            }
            .toast-item.toast-warning {
                background: linear-gradient(135deg, #BA7517, #f59e0b);
            }
            .toast-item.toast-info {
                background: linear-gradient(135deg, #0d9488, #14b8a6);
            }
            .toast-icon {
                flex-shrink: 0;
                width: 1.25rem;
                height: 1.25rem;
            }
            .toast-progress {
                position: absolute;
                bottom: 0;
                left: 0;
                height: 3px;
                background: rgba(255,255,255,0.4);
                border-radius: 0 0 0 0.75rem;
                animation: toastProgress 4s linear forwards;
            }
            @keyframes toastSlideIn {
                from { opacity: 0; transform: translateX(100%); }
                to   { opacity: 1; transform: translateX(0); }
            }
            @keyframes toastSlideOut {
                from { opacity: 1; transform: translateX(0); max-height: 100px; margin-bottom: 0; }
                to   { opacity: 0; transform: translateX(100%); max-height: 0; margin-bottom: -0.5rem; }
            }
            @keyframes toastProgress {
                from { width: 100%; }
                to   { width: 0%; }
            }
        </style>
    </head>

    <body class="antialiased bg-gray-50 dark:bg-gray-950">
        <!-- Toast Container -->
        <div id="toast-container" class="toast-container"></div>

        {{ $slot ?? $__env->yieldContent('content') }}

        @stack('scripts')
        @filamentScripts(withCore: true)
        @livewireScripts

        <!-- Toast Notification JS (vanilla, no jQuery needed, with deduplication) -->
        <script>
            (function() {
                const icons = {
                    success: '<svg class="toast-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>',
                    error:   '<svg class="toast-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>',
                    warning: '<svg class="toast-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                    info:    '<svg class="toast-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                };

                // Deduplication: ignore same message+type within 500ms
                const recentToasts = new Map();

                function showToast(type, message, duration) {
                    const key = type + ':' + message;
                    if (recentToasts.has(key)) return; // duplicate — skip
                    recentToasts.set(key, true);
                    setTimeout(function() { recentToasts.delete(key); }, 500);

                    duration = duration || 4000;
                    const container = document.getElementById('toast-container');
                    if (!container) return;

                    const toast = document.createElement('div');
                    toast.className = 'toast-item toast-' + (type || 'info');
                    toast.innerHTML = (icons[type] || icons.info) +
                        '<span>' + message + '</span>' +
                        '<div class="toast-progress" style="animation-duration:' + duration + 'ms"></div>';

                    toast.addEventListener('click', function() { removeToast(toast); });
                    container.appendChild(toast);

                    setTimeout(function() { removeToast(toast); }, duration);
                }

                function removeToast(el) {
                    if (el.classList.contains('toast-removing')) return;
                    el.classList.add('toast-removing');
                    setTimeout(function() { el.remove(); }, 300);
                }

                // Expose as global "toastr" compatible API
                window.toastr = {
                    success: function(msg) { showToast('success', msg); },
                    error:   function(msg) { showToast('error',   msg); },
                    warning: function(msg) { showToast('warning', msg); },
                    info:    function(msg) { showToast('info',    msg); },
                };
            })();
        </script>
    </body>
</html>
