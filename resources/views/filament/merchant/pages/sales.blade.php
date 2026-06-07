<x-filament-panels::page>
    <style>
        .sikamo-terminal-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 2.5rem 1rem;
            background: transparent;
            box-sizing: border-box;
        }

        html:not(.dark) .sikamo-terminal-card {
            width: 100%;
            max-width: 440px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(241, 245, 249, 0.96));
            border: 1px solid rgba(16, 185, 129, 0.16);
            border-radius: 28px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 22px 80px -42px rgba(16, 185, 129, 0.14), 0 6px 22px rgba(15, 23, 42, 0.08);
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: transform 0.28s ease, box-shadow 0.28s ease, border-color 0.28s ease;
        }

        html:not(.dark) .sikamo-terminal-card:hover {
            box-shadow: 0 24px 90px -44px rgba(16, 185, 129, 0.18), 0 10px 26px rgba(15, 23, 42, 0.1);
            border-color: rgba(16, 185, 129, 0.22);
            transform: translateY(-2px);
        }

        html:not(.dark) .sikamo-icon-outer {
            position: relative;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.28);
            color: #16a34a;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: inset 0 2px 8px rgba(15, 23, 42, 0.08);
            margin-bottom: 24px;
        }

        html:not(.dark) .sikamo-icon-check {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background-color: #10b981;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid rgba(16, 185, 129, 0.3);
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.12);
        }

        html:not(.dark) .sikamo-terminal-title {
            font-size: 26px;
            font-weight: 800;
            color: #0f172a !important;
            margin: 0 0 14px 0;
            letter-spacing: -0.03em;
            line-height: 1.2;
            font-family: system-ui, -apple-system, sans-serif;
        }

        html:not(.dark) .sikamo-terminal-desc {
            font-size: 14px;
            color: rgba(51, 65, 85, 0.8) !important;
            font-weight: 500;
            line-height: 1.7;
            margin: 0 0 32px 0;
            max-width: 340px;
            font-family: system-ui, -apple-system, sans-serif;
        }

        html:not(.dark) .sikamo-btn-launch {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 16px 24px;
            background: linear-gradient(135deg, #16a34a, #059669);
            color: #ffffff !important;
            font-weight: 800;
            font-size: 14px;
            border-radius: 18px;
            border: none;
            cursor: pointer;
            text-decoration: none !important;
            box-shadow: 0 18px 48px rgba(16, 185, 129, 0.2);
            transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
            box-sizing: border-box;
            font-family: system-ui, -apple-system, sans-serif;
        }

        html:not(.dark) .sikamo-btn-launch:hover {
            background: linear-gradient(135deg, #22c55e, #10b981);
            box-shadow: 0 20px 58px rgba(16, 185, 129, 0.26);
            transform: translateY(-1px);
        }

        .dark .sikamo-terminal-card {
            width: 100%;
            max-width: 440px;
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.96), rgba(10, 37, 54, 0.96));
            border: 1px solid rgba(16, 185, 129, 0.18);
            border-radius: 28px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 22px 80px -42px rgba(16, 185, 129, 0.28), 0 6px 18px rgba(8, 24, 48, 0.18);
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: transform 0.28s ease, box-shadow 0.28s ease, border-color 0.28s ease;
        }

        .dark .sikamo-terminal-card:hover {
            box-shadow: 0 24px 90px -44px rgba(16, 185, 129, 0.32), 0 10px 26px rgba(8, 24, 48, 0.22);
            border-color: rgba(16, 185, 129, 0.26);
            transform: translateY(-2px);
        }

        .dark .sikamo-icon-outer {
            position: relative;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.32);
            color: #10b981;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.16);
            margin-bottom: 24px;
        }

        .dark .sikamo-icon-check {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background-color: #10b981;
            color: #0b1329;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #0b1329;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.28);
        }

        .dark .sikamo-terminal-title {
            font-size: 26px;
            font-weight: 800;
            color: #f8fafc !important;
            margin: 0 0 14px 0;
            letter-spacing: -0.03em;
            line-height: 1.2;
            font-family: system-ui, -apple-system, sans-serif;
        }

        .dark .sikamo-terminal-desc {
            font-size: 14px;
            color: rgba(226, 232, 240, 0.82) !important;
            font-weight: 500;
            line-height: 1.7;
            margin: 0 0 32px 0;
            max-width: 340px;
            font-family: system-ui, -apple-system, sans-serif;
        }

        .dark .sikamo-btn-launch {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 16px 24px;
            background: linear-gradient(135deg, #16a34a, #059669);
            color: #ffffff !important;
            font-weight: 800;
            font-size: 14px;
            border-radius: 18px;
            border: none;
            cursor: pointer;
            text-decoration: none !important;
            box-shadow: 0 18px 48px rgba(16, 185, 129, 0.24);
            transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
            box-sizing: border-box;
            font-family: system-ui, -apple-system, sans-serif;
        }

        .dark .sikamo-btn-launch:hover {
            background: linear-gradient(135deg, #22c55e, #10b981);
            box-shadow: 0 20px 58px rgba(16, 185, 129, 0.3);
            transform: translateY(-1px);
        }

        .sikamo-btn-launch:active {
            transform: scale(0.99);
        }

        .sikamo-btn-launch:active {
            transform: scale(0.99);
        }

        html:not(.dark) .sikamo-terminal-version {
            color: rgba(15, 23, 42, 0.48);
        }

        .sikamo-terminal-info {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 12px;
            color: #64748b !important;
            font-family: system-ui, -apple-system, sans-serif;
        }

        .sikamo-terminal-version {
            margin-top: 28px;
            font-size: 10px;
            color: rgba(226, 232, 240, 0.68);
            font-family: monospace;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            font-weight: 600;
        }
    </style>

    <div class="sikamo-terminal-wrapper">
        
        <!-- Terminal Container Card -->
        <div class="sikamo-terminal-card">
            
            <!-- Cashier Register Icon in circular badge with check badge -->
            <div class="sikamo-icon-outer">
                <!-- Cashier Register SVG -->
                <svg class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width: 40px; height: 40px;">
                    <rect x="5" y="14" width="14" height="6" rx="1" />
                    <path d="M7 17h2v2H7zM11 17h2v2H11zM15 17h2v2H15z" />
                    <path d="M17 14L15 4H9L7 14" />
                    <rect x="9" y="6" width="6" height="4" rx="0.5" />
                </svg>
                
                <!-- Small Checkmark Badge at bottom-right -->
                <div class="sikamo-icon-check">
                    <svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" style="width: 12px; height: 12px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>

            <!-- Title & Subtitle -->
            <h2 class="sikamo-terminal-title">Kasir Penjualan</h2>
            <p class="sikamo-terminal-desc">
                Buka portal kasir penjualan untuk mulai menginput produk dan memproses pembayaran transaksi pelanggan secara instan.
            </p>

            <!-- Launch Button -->
            <div class="sikamo-btn-container">
                <a href="{{ route('merchant.sales.catalog') }}" target="_blank" class="sikamo-btn-launch">
                    <span>Buka Kasir di Tab Baru</span>
                    <span style="font-size: 16px; font-weight: 700; line-height: 1;">&rarr;</span>
                </a>
            </div>
        </div>

        <!-- Decorative Footer Version -->
        <div class="sikamo-terminal-version">
            SIKAMO POS TERMINAL V1.0.0
        </div>
    </div>
</x-filament-panels::page>
