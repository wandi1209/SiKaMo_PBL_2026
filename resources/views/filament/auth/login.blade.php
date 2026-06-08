<div class="sikamo-login-root">
    <style>
        .fi-simple-layout {
            min-height: 100vh;
            background:
                radial-gradient(circle at 50% 0%, rgba(34, 197, 94, 0.12), transparent 30rem),
                linear-gradient(180deg, #f8fafc 0%, #f0fdf4 100%);
        }

        .fi-simple-main-ctn {
            padding: 1.25rem;
        }

        .fi-simple-main {
            overflow: hidden;
            padding: 0 !important;
            border: 1px solid rgba(148, 163, 184, 0.22);
            border-radius: 1.5rem !important;
            background: rgba(255, 255, 255, 0.96) !important;
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.1) !important;
            backdrop-filter: blur(16px);
        }

        .sikamo-login {
            padding: clamp(2rem, 7vw, 3rem);
        }

        .sikamo-login__header {
            margin-bottom: 2rem;
            text-align: center;
        }

        .sikamo-login__logo {
            width: 4.5rem;
            height: 4.5rem;
            margin: 0 auto 1.25rem;
            object-fit: contain;
        }

        .sikamo-login__header h1 {
            margin: 0;
            color: #0f172a;
            font-size: clamp(1.85rem, 8vw, 2.35rem);
            font-weight: 900;
            line-height: 1.05;
            letter-spacing: 0;
        }

        .sikamo-login__header p {
            max-width: 24rem;
            margin: 0.85rem auto 0;
            color: #64748b;
            font-size: 0.95rem;
            line-height: 1.65;
        }

        .sikamo-login .fi-fo-field-wrp-label {
            color: #0f172a;
            font-weight: 700;
        }

        .sikamo-login :is(.fi-input-wrp, .fi-checkbox-input) {
            border-radius: 0.95rem;
        }

        .sikamo-login .fi-input-wrp {
            border: 1.5px solid rgba(100, 116, 139, 0.38);
            background: #ffffff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .sikamo-login .fi-input-wrp:focus-within {
            border-color: #16a34a;
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.12);
        }

        .sikamo-login .fi-btn {
            min-height: 3.2rem;
            border-radius: 0.95rem;
            background: linear-gradient(135deg, #15803d 0%, #22c55e 100%) !important;
            color: #ffffff !important;
            font-weight: 800;
            box-shadow: 0 14px 32px rgba(21, 128, 61, 0.22);
        }

        .sikamo-login .fi-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 18px 38px rgba(21, 128, 61, 0.28);
        }

        .sikamo-login__back {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            margin-top: 1.5rem;
            color: #15803d;
            font-size: 0.9rem;
            font-weight: 700;
            text-decoration: none;
        }

        .sikamo-login__back:hover {
            color: #166534;
        }

        @media (max-width: 480px) {
            .fi-simple-main-ctn {
                padding: 1rem;
            }

            .sikamo-login {
                padding: 2rem;
            }
        }

        @media (prefers-color-scheme: dark) {
            .fi-simple-layout {
                background:
                    radial-gradient(circle at 50% 0%, rgba(34, 197, 94, 0.14), transparent 28rem),
                    linear-gradient(180deg, #020617 0%, #052e16 100%);
            }

            .fi-simple-main {
                border-color: rgba(148, 163, 184, 0.18);
                background: rgba(15, 23, 42, 0.94) !important;
            }

            .sikamo-login__header h1,
            .sikamo-login .fi-fo-field-wrp-label {
                color: #f8fafc;
            }

            .sikamo-login__header p {
                color: #94a3b8;
            }

            .sikamo-login .fi-input-wrp {
                border-color: rgba(148, 163, 184, 0.42);
                background: rgba(2, 6, 23, 0.52);
            }
        }
    </style>

    <section class="sikamo-login">
        <div class="sikamo-login__header">
            <img src="{{ asset('assets/logo.png') }}" alt="SiKaMo" class="sikamo-login__logo">
            <h1>{{ $this->getHeading() }}</h1>
            <p>{{ $this->getSubheading() }}</p>
        </div>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

        <x-filament-panels::form id="form" wire:submit="authenticate">
            {{ $this->form }}

            <x-filament-panels::form.actions
                :actions="$this->getCachedFormActions()"
                :full-width="$this->hasFullWidthFormActions()"
            />
        </x-filament-panels::form>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}

        <a href="/" class="sikamo-login__back">
            <span aria-hidden="true">&larr;</span>
            Kembali ke beranda
        </a>
    </section>

    <x-filament-actions::modals />
</div>
