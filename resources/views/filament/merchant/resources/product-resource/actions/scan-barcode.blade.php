@php
    $buildManifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
    $scannerScriptUrl = asset('build/' . $buildManifest['resources/js/barcode-scanner.js']['file']);
@endphp

<div
    x-data="{
        createUrl: @js($createUrl),
        scanner: null,
        scanning: false,
        scannerReady: false,
        scannerLoadStarted: false,
        pendingStart: true,
        barcode: '',
        status: 'Menyiapkan scanner dan kamera...',
        init() {
            this.waitUntilScannerReady();
        },
        waitUntilScannerReady() {
            if (window.SiKaMoBarcodeScanner) {
                this.scannerReady = true;
                this.status = 'Scanner siap. Membuka kamera...';

                if (this.pendingStart) {
                    this.pendingStart = false;
                    this.start();
                } else if (!this.scanning) {
                    this.status = 'Scanner siap. Arahkan kamera ke barcode produk.';
                }

                return;
            }

            if (!this.scannerLoadStarted) {
                this.scannerLoadStarted = true;
                this.loadScannerScript();
            }

            setTimeout(() => this.waitUntilScannerReady(), 100);
        },
        loadScannerScript() {
            const scriptUrl = @js($scannerScriptUrl);

            if (document.querySelector('script[data-sikamo-barcode-scanner=true]')) {
                return;
            }

            const script = document.createElement('script');
            script.type = 'module';
            script.src = scriptUrl;
            script.dataset.sikamoBarcodeScanner = 'true';
            script.onerror = () => {
                this.status = 'Scanner gagal dimuat. Refresh halaman lalu coba lagi.';
                this.pendingStart = false;
            };
            document.head.appendChild(script);
        },
        async start() {
            try {
                if (!window.SiKaMoBarcodeScanner) {
                    this.pendingStart = true;
                    this.status = 'Scanner masih dimuat. Kamera akan dibuka otomatis sebentar lagi.';
                    return;
                }

                if (this.scanning) {
                    return;
                }

                this.scanning = true;
                this.status = 'Membuka kamera...';

                this.scanner = await window.SiKaMoBarcodeScanner.start({
                    elementId: 'sikamo-barcode-reader',
                    onStatus: (message) => this.status = message,
                    onDetected: (value) => {
                        this.barcode = value;
                        this.status = `Barcode terbaca: ${this.barcode}`;
                        this.scanning = false;
                        this.goToProductForm();
                    },
                });
            } catch (error) {
                this.scanning = false;
                this.pendingStart = false;
                this.status = error?.message || 'Kamera tidak bisa dibuka. Pastikan izin kamera diberikan dan akses lewat localhost/HTTPS.';
            }
        },
        stop() {
            this.scanning = false;

            if (this.scanner) {
                this.scanner.stop();
                this.scanner = null;
            }
        },
        goToProductForm() {
            if (!this.barcode.trim()) {
                this.status = 'Barcode masih kosong.';
                return;
            }

            const url = new URL(this.createUrl, window.location.origin);
            url.searchParams.set('barcode', this.barcode.trim());
            window.location.href = url.toString();
        },
    }"
    x-on:close-modal.window="stop()"
    class="space-y-4"
>
    <div class="relative overflow-hidden rounded-lg border border-gray-200 bg-gray-950 dark:border-gray-700">
        <div
            id="sikamo-barcode-reader"
            class="min-h-72 w-full"
        ></div>
    </div>

    <p class="text-sm text-gray-600 dark:text-gray-300" x-text="status"></p>

    <div class="flex flex-wrap gap-2">
        <x-filament::button type="button" icon="heroicon-o-camera" x-on:click="start()" x-bind:disabled="scanning">
            Mulai Scan
        </x-filament::button>

        <x-filament::button type="button" color="gray" icon="heroicon-o-stop" x-on:click="stop()" x-bind:disabled="!scanning">
            Stop
        </x-filament::button>
    </div>

    <div class="space-y-2">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-200" for="manual-barcode">
            Barcode Manual
        </label>
        <x-filament::input.wrapper>
            <x-filament::input
                id="manual-barcode"
                x-model="barcode"
                type="text"
                placeholder="Ketik barcode jika kamera tidak membaca"
                x-on:keydown.enter.prevent="goToProductForm()"
            />
        </x-filament::input.wrapper>
    </div>

    <x-filament::button type="button" color="success" icon="heroicon-o-arrow-right" x-on:click="goToProductForm()">
        Lanjutkan
    </x-filament::button>
</div>
