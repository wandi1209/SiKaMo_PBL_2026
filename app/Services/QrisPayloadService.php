<?php

namespace App\Services;

class QrisPayloadService
{
    public function withAmount(string $payload, int|float $amount): ?string
    {
        $payload = str_replace(["\r", "\n", "\t"], '', trim($payload));

        if ($payload === '' || $amount <= 0) {
            return null;
        }

        $fields = $this->parse($this->withoutCrc($payload));

        if ($fields === null) {
            return null;
        }

        $fields = $this->upsert($fields, '01', '12');
        $fields = $this->upsert($fields, '54', number_format((float) $amount, 2, '.', ''));

        return $this->appendCrc($this->serialize($fields));
    }

    private function withoutCrc(string $payload): string
    {
        $crcPosition = strrpos($payload, '6304');

        if ($crcPosition === false) {
            return $payload;
        }

        return substr($payload, 0, $crcPosition);
    }

    /**
     * @return array<int, array{tag: string, value: string}>|null
     */
    private function parse(string $payload): ?array
    {
        $fields = [];
        $position = 0;
        $length = strlen($payload);

        while ($position < $length) {
            if ($position + 4 > $length) {
                return null;
            }

            $tag = substr($payload, $position, 2);
            $valueLength = substr($payload, $position + 2, 2);

            if (! ctype_digit($tag) || ! ctype_digit($valueLength)) {
                return null;
            }

            $valueLength = (int) $valueLength;
            $valueStart = $position + 4;

            if ($valueStart + $valueLength > $length) {
                return null;
            }

            $fields[] = [
                'tag' => $tag,
                'value' => substr($payload, $valueStart, $valueLength),
            ];

            $position = $valueStart + $valueLength;
        }

        return $fields;
    }

    /**
     * @param array<int, array{tag: string, value: string}> $fields
     * @return array<int, array{tag: string, value: string}>
     */
    private function upsert(array $fields, string $tag, string $value): array
    {
        foreach ($fields as $index => $field) {
            if ($field['tag'] === $tag) {
                $fields[$index]['value'] = $value;

                return $fields;
            }
        }

        $insertAt = count($fields);

        foreach ($fields as $index => $field) {
            if ($tag === '54' && in_array($field['tag'], ['58', '59', '60'], true)) {
                $insertAt = $index;
                break;
            }
        }

        array_splice($fields, $insertAt, 0, [[
            'tag' => $tag,
            'value' => $value,
        ]]);

        return $fields;
    }

    /**
     * @param array<int, array{tag: string, value: string}> $fields
     */
    private function serialize(array $fields): string
    {
        return collect($fields)
            ->map(fn (array $field): string => $field['tag'] . str_pad((string) strlen($field['value']), 2, '0', STR_PAD_LEFT) . $field['value'])
            ->implode('');
    }

    private function appendCrc(string $payload): string
    {
        $payloadForCrc = $payload . '6304';

        return $payloadForCrc . strtoupper(str_pad(dechex($this->crc16($payloadForCrc)), 4, '0', STR_PAD_LEFT));
    }

    private function crc16(string $payload): int
    {
        $crc = 0xFFFF;

        for ($i = 0, $length = strlen($payload); $i < $length; $i++) {
            $crc ^= ord($payload[$i]) << 8;

            for ($bit = 0; $bit < 8; $bit++) {
                if (($crc & 0x8000) !== 0) {
                    $crc = (($crc << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    $crc = ($crc << 1) & 0xFFFF;
                }
            }
        }

        return $crc;
    }
}
