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

        .sikamo-terminal-card {
            width: 100%;
            max-width: 440px;
            background-color: #0b1329;
            border: 1px solid #1e293b;
            border-radius: 24px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 10px 30px -10px rgba(16, 185, 129, 0.15), 0 1px 3px rgba(0, 0, 0, 0.2);
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sikamo-terminal-card:hover {
            box-shadow: 0 15px 40px -10px rgba(16, 185, 129, 0.22);
            border-color: #334155;
            transform: translateY(-2px);
        }

        .sikamo-icon-outer {
            position: relative;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #112d37;
            border: 1px solid rgba(16, 185, 129, 0.25);
            color: #10b981;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
            margin-bottom: 24px;
        }

        .sikamo-icon-check {
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
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
        }

        .sikamo-terminal-title {
            font-size: 24px;
            font-weight: 700;
            color: #ffffff !important;
            margin: 0 0 12px 0;
            letter-spacing: -0.025em;
            line-height: 1.2;
            font-family: system-ui, -apple-system, sans-serif;
        }

        .sikamo-terminal-desc {
            font-size: 14px;
            color: #94a3b8 !important;
            font-weight: 500;
            line-height: 1.6;
            margin: 0 0 32px 0;
            max-width: 320px;
            font-family: system-ui, -apple-system, sans-serif;
        }

        .sikamo-btn-container {
            width: 100%;
            margin-bottom: 24px;
        }

        .sikamo-btn-launch {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 16px 24px;
            background-color: #10b981;
            color: #0b1329 !important;
            font-weight: 700;
            font-size: 14px;
            border-radius: 16px;
            border: none;
            cursor: pointer;
            text-decoration: none !important;
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.25);
            transition: all 0.2s ease;
            box-sizing: border-box;
            font-family: system-ui, -apple-system, sans-serif;
        }

        .sikamo-btn-launch:hover {
            background-color: #34d399;
            box-shadow: 0 0 25px rgba(16, 185, 129, 0.4);
            transform: scale(1.01);
        }

        .sikamo-btn-launch:active {
            transform: scale(0.99);
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
            color: #475569;
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
