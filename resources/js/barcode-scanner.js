import { Html5Qrcode, Html5QrcodeSupportedFormats } from 'html5-qrcode';

// Expose Html5Qrcode to window so that gallery scanning works
window.Html5Qrcode = Html5Qrcode;

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
                aspectRatio: 1.777778,
                qrbox: (viewfinderWidth, viewfinderHeight) => {
                    const width = Math.floor(viewfinderWidth * 0.86);
                    const height = Math.max(90, Math.floor(viewfinderHeight * 0.28));

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
