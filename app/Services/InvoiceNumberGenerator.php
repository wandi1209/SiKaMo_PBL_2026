<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceNumberGenerator
{
    /**
     * Generate a unique, per-merchant daily invoice number using an atomic
     * counter (INSERT ... ON DUPLICATE KEY UPDATE).
     *
     * IMPORTANT: must be called inside an active DB transaction so the
     * counter row lock is held until commit. This guarantees no duplicate
     * sequence under concurrent checkouts, and the sequence is only
     * consumed if the surrounding transaction commits.
     *
     * Format: INV-{Ymd}-{merchantId}-{0001}
     */
    public function next(int $merchantId, ?Carbon $date = null): string
    {
        $date = ($date ?? Carbon::now())->copy()->timezone(config('app.timezone'));
        $dateKey = $date->format('Ymd');
        $now = Carbon::now();

        // Atomic upsert + increment. On the first row of the day this inserts
        // last_sequence = 1; otherwise it increments the existing row. The row
        // write-lock is held until the surrounding transaction commits, so a
        // concurrent checkout blocks here and then reads the next value.
        DB::statement(
            'INSERT INTO invoice_counters (merchant_id, date_key, last_sequence, created_at, updated_at)
             VALUES (?, ?, 1, ?, ?)
             ON DUPLICATE KEY UPDATE last_sequence = last_sequence + 1, updated_at = VALUES(updated_at)',
            [$merchantId, $dateKey, $now, $now]
        );

        $sequence = (int) DB::table('invoice_counters')
            ->where('merchant_id', $merchantId)
            ->where('date_key', $dateKey)
            ->value('last_sequence');

        return sprintf(
            'INV-%s-%d-%s',
            $dateKey,
            $merchantId,
            str_pad((string) $sequence, 4, '0', STR_PAD_LEFT)
        );
    }
}
