<?php

use App\Services\QrisPayloadService;

it('generates a dynamic qris payload with amount and fresh crc', function () {
    $basePayload = '0002010102115204000053033605802ID5908TOKO ABC6006MALANG6304A13A';

    $payload = app(QrisPayloadService::class)->withAmount($basePayload, 80000);

    expect($payload)
        ->not->toBeNull()
        ->toContain('010212')
        ->toContain('540880000.00');

    $payloadWithoutCrcValue = substr($payload, 0, -4);
    $expectedCrc = strtoupper(str_pad(dechex(testCrc16($payloadWithoutCrcValue)), 4, '0', STR_PAD_LEFT));

    expect(substr($payload, -8, 4))->toBe('6304')
        ->and(substr($payload, -4))->toBe($expectedCrc);
});

function testCrc16(string $payload): int
{
    $crc = 0xFFFF;

    for ($i = 0, $length = strlen($payload); $i < $length; $i++) {
        $crc ^= ord($payload[$i]) << 8;

        for ($bit = 0; $bit < 8; $bit++) {
            $crc = (($crc & 0x8000) !== 0)
                ? (($crc << 1) ^ 0x1021) & 0xFFFF
                : ($crc << 1) & 0xFFFF;
        }
    }

    return $crc;
}
