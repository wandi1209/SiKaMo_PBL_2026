<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FonnteWhatsappService
{
    public function sendText(string $target, string $message): Response
    {
        return $this->send($target, [
            'message' => $message,
        ]);
    }

    public function sendDocumentUrl(string $target, string $message, string $url, string $filename): Response
    {
        return $this->send($target, [
            'message' => $message,
            'url' => $url,
            'filename' => $filename,
        ]);
    }

    public function normalizePhone(?string $phone): string
    {
        $phone = preg_replace('/\D+/', '', (string) $phone);

        if ($phone === '') {
            return '';
        }

        if (Str::startsWith($phone, '0')) {
            return (string) config('services.fonnte.country_code', '62') . substr($phone, 1);
        }

        return $phone;
    }

    public function isValidPhone(?string $phone): bool
    {
        return strlen($this->normalizePhone($phone)) >= 10;
    }

    private function send(string $target, array $payload): Response
    {
        return Http::asForm()
            ->withHeaders([
                'Authorization' => (string) config('services.fonnte.token'),
            ])
            ->timeout(30)
            ->post($this->endpoint(), array_merge($payload, [
                'target' => $this->normalizePhone($target),
                'countryCode' => (string) config('services.fonnte.country_code', '62'),
                'delay' => (string) config('services.fonnte.delay', '2'),
            ]));
    }

    private function endpoint(): string
    {
        return rtrim((string) config('services.fonnte.base_url', 'https://api.fonnte.com'), '/') . '/send';
    }
}
