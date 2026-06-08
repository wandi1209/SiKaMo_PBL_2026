<?php

namespace App\Services;

use App\Events\ProductStockUpdated;
use App\Models\StockLog;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TransactionRefundService
{
    public function refund(Transaction $transaction, User $user, ?string $reason = null): Transaction
    {
        return DB::transaction(function () use ($transaction, $user, $reason): Transaction {
            $transaction = Transaction::query()
                ->with(['items.product'])
                ->lockForUpdate()
                ->findOrFail($transaction->id);

            if ($transaction->status !== 'completed') {
                throw new \RuntimeException('Transaksi ini tidak bisa direfund karena statusnya bukan completed.');
            }

            foreach ($transaction->items as $item) {
                $product = $item->product()
                    ->lockForUpdate()
                    ->first();

                if (! $product) {
                    continue;
                }

                $stockBefore = $product->stock;
                $stockAfter = $stockBefore + $item->quantity;

                app()->instance('audit_context', [
                    'system_actor' => true,
                    'system_reason' => 'refund',
                    'transaction_id' => $transaction->id,
                    'transaction_invoice_number' => $transaction->invoice_number,
                    'triggered_by_user_id' => $user->id,
                    'triggered_by_user_name' => $user->name,
                    'triggered_by_user_role' => $user->role,
                ]);

                try {
                    $product->forceFill(['stock' => $stockAfter])->save();
                    $product->refresh();
                    DB::afterCommit(fn() => ProductStockUpdated::dispatchSafely($product));
                } finally {
                    app()->forgetInstance('audit_context');
                }

                StockLog::create([
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'quantity_change' => $item->quantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'type' => 'in',
                    'note' => 'Refund transaksi ' . $transaction->invoice_number,
                ]);
            }

            $transaction->forceFill([
                'status' => 'refunded',
                'refunded_by' => $user->id,
                'refunded_at' => now(),
                'refund_reason' => $reason,
            ])->save();

            return $transaction->refresh();
        });
    }
}
