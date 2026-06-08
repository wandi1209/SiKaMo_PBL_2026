import { Html5Qrcode, Html5QrcodeSupportedFormats } from 'html5-qrcode';

// Expose Html5Qrcode to window so that gallery scanning works
window.Html5Qrcode = Html5Qrcode;
window.Html5QrcodeSupportedFormats = Html5QrcodeSupportedFormats;

const formatsToSupport = [
    Html5QrcodeSupportedFormats.EAN_13,
    Html5QrcodeSupportedFormats.EAN_8,
    Html5QrcodeSupportedFormats.UPC_A,
    Html5QrcodeSupportedFormats.UPC_E,
    Html5QrcodeSupportedFormats.CODE_128,
    Html5QrcodeSupportedFormats.CODE_39,
    Html5QrcodeSupportedFormats.ITF,
    Html5QrcodeSupportedFormats.QR_CODE,
];

window.SiKaMoBarcodeScanner = {
    async start({ elementId, onDetected, onStatus, facingMode = 'environment' }) {
        if (!navigator.mediaDevices?.getUserMedia) {
            throw new Error('Browser tidak mendukung akses kamera.');
        }

        const scanner = new Html5Qrcode(elementId, {
            formatsToSupport,
            verbose: false,
        });

        await scanner.start(
            { facingMode: facingMode },
            {
                fps: 15,
                qrbox: (viewfinderWidth, viewfinderHeight) => {
                    const width = Math.floor(viewfinderWidth * 0.82);
                    const height = Math.max(120, Math.floor(viewfinderHeight * 0.34));

                    return { width, height };
                },
            },
            async (decodedText) => {
                if (!decodedText) {
                    return;
                }

                await scanner.stop();
                scanner.clear();
                onDetected(decodedText);
            },
        );

        onStatus?.('Kamera aktif. Letakkan barcode di dalam kotak scan.');

        return {
            async stop() {
                if (scanner.isScanning) {
                    await scanner.stop();
                }

                scanner.clear();
            },
            async setTorch(on) {
                try {
                    const track = scanner.getRunningTrack();
                    if (track && track.getCapabilities()?.torch) {
                        await track.applyConstraints({
                            advanced: [{ torch: on }]
                        });
                    }
                } catch (e) {
                    console.warn("Torch control not supported", e);
                }
            }
        };
    },
};

window.SiKaMoQrisImageDecoder = {
    async scanFile(file) {
        if (!file || !file.type?.startsWith('image/')) {
            return null;
        }

        const elementId = 'sikamo-qris-image-decoder';
        let element = document.getElementById(elementId);

        if (!element) {
            element = document.createElement('div');
            element.id = elementId;
            element.hidden = true;
            document.body.appendChild(element);
        }

        const scanner = new Html5Qrcode(elementId, {
            formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
            verbose: false,
        });

        try {
            return await scanner.scanFile(file, true);
        } finally {
            scanner.clear();
        }
    },
};
