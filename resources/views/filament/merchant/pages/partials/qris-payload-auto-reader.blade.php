<div
    class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-medium text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400"
    x-data="qrisPayloadAutoReader()"
    x-init="init()"
>
    Payload QRIS akan diambil otomatis dari gambar QRIS yang diunggah.
</div>

<script>
    function qrisPayloadAutoReader() {
        return {
            initialized: false,

            init() {
                if (this.initialized || window.__sikamoQrisPayloadAutoReaderInitialized) return;
                this.initialized = true;
                window.__sikamoQrisPayloadAutoReaderInitialized = true;

                document.addEventListener('change', (event) => {
                    const input = event.target;

                    if (!(input instanceof HTMLInputElement) || input.type !== 'file' || !input.files?.length) {
                        return;
                    }

                    this.extractPayload(input.files[0]);
                });
            },

            async extractPayload(file) {
                try {
                    await this.waitForDecoder();

                    const payload = await window.SiKaMoQrisImageDecoder.scanFile(file);

                    if (!this.isQrisPayload(payload)) {
                        return;
                    }

                    this.fillPayload(payload);
                    this.notify('success', 'Payload QRIS berhasil diambil dari gambar.');
                } catch (error) {
                    return;
                }
            },

            async waitForDecoder() {
                for (let i = 0; i < 40; i++) {
                    if (window.SiKaMoQrisImageDecoder) {
                        return;
                    }

                    await new Promise((resolve) => setTimeout(resolve, 100));
                }

                throw new Error('QRIS decoder belum siap.');
            },

            isQrisPayload(payload) {
                return typeof payload === 'string'
                    && payload.startsWith('000201')
                    && payload.includes('5802ID');
            },

            fillPayload(payload) {
                const field = document.querySelector('textarea[name="data.qris_payload"], textarea[name="data[qris_payload]"], textarea[id$="qris_payload"], textarea[wire\\:model*="qris_payload"]');

                if (!field) {
                    return;
                }

                const setter = Object.getOwnPropertyDescriptor(HTMLTextAreaElement.prototype, 'value')?.set;
                if (setter) {
                    setter.call(field, payload);
                } else {
                    field.value = payload;
                }

                field.dispatchEvent(new Event('input', { bubbles: true }));
                field.dispatchEvent(new Event('change', { bubbles: true }));
            },

            notify(type, message) {
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: { type, message },
                }));
            },
        };
    }
</script>
