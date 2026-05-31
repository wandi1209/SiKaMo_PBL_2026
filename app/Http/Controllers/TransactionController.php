<?php

namespace App\Http\Controllers;

use App\Events\ProductStockUpdated;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /**
     * Proses checkout dan membuat transaksi baru
     */
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:m_products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'amount_paid' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,debit,credit,transfer',
        ]);

        $user = Auth::user();
        $merchantId = $user?->merchant_id;

        if (!$user || !$merchantId) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak terautentikasi dengan benar'
            ], 401);
        }

        try {
            return DB::transaction(function () use ($validated, $user, $merchantId) {
                // 1. Hitung total dan validasi stock
                $totalAmount = 0;
                $transactionItems = [];

                foreach ($validated['items'] as $item) {
                    $product = Product::query()
                        ->where('merchant_id', $merchantId)
                        ->lockForUpdate()
                        ->find($item['product_id']);

                    if (! $product) {
                        throw new \Exception('Produk tidak termasuk dalam merchant Anda');
                    }

                    // Validasi stock tersedia
                    if ($product->stock < $item['quantity']) {
                        throw new \Exception("Stok {$product->name} tidak mencukupi. Stok tersedia: {$product->stock}");
                    }

                    $subtotal = $item['unit_price'] * $item['quantity'];
                    $unitCost = (float) ($product->cost_price ?? 0);
                    $totalCost = $unitCost * $item['quantity'];
                    $totalAmount += $subtotal;

                    $transactionItems[] = [
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'unit_cost' => $unitCost,
                        'subtotal' => $subtotal,
                        'total_cost' => $totalCost,
                        'gross_profit' => $subtotal - $totalCost,
                    ];
                }

                // 2. Validasi pembayaran
                $amountPaid = $validated['amount_paid'];
                if ($amountPaid < $totalAmount) {
                    throw new \Exception('Jumlah pembayaran kurang dari total transaksi');
                }

                // 3. Buat nomor invoice
                $invoiceNumber = $this->generateInvoiceNumber($merchantId);

                // 4. Simpan transaksi
                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'invoice_number' => $invoiceNumber,
                    'subtotal_amount' => $totalAmount,
                    'discount_amount' => 0,
                    'total_amount' => $totalAmount,
                    'amount_paid' => $amountPaid,
                    'change_amount' => $amountPaid - $totalAmount,
                    'payment_method' => $validated['payment_method'],
                    'status' => 'completed',
                ]);

                // 5. Simpan items transaksi dan kurangi stock
                foreach ($transactionItems as $itemData) {
                    TransactionItem::create([
                        'transaction_id' => $transaction->id,
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'unit_cost' => $itemData['unit_cost'],
                        'subtotal' => $itemData['subtotal'],
                        'total_cost' => $itemData['total_cost'],
                        'gross_profit' => $itemData['gross_profit'],
                    ]);

                    // Kurangi stock produk
                    $product = Product::query()
                        ->where('merchant_id', $merchantId)
                        ->lockForUpdate()
                        ->find($itemData['product_id']);
                    $stockBefore = (int) $product->stock;

                    app()->instance('audit_context', [
                        'system_actor' => true,
                        'system_reason' => 'sale',
                        'transaction_id' => $transaction->id,
                        'transaction_invoice_number' => $transaction->invoice_number,
                        'triggered_by_user_id' => $user->id,
                        'triggered_by_user_name' => $user->name,
                        'triggered_by_user_role' => $user->role,
                    ]);

                    try {
                        $product->decrement('stock', $itemData['quantity']);
                        $product->refresh();
                        DB::afterCommit(fn() => event(ProductStockUpdated::fromProduct($product)));
                    } finally {
                        app()->forgetInstance('audit_context');
                    }

                    // Log perubahan stok
                    $this->logStockChange($product, $itemData['quantity'], $stockBefore, 'sale', $transaction->invoice_number);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Transaksi berhasil disimpan',
                    'data' => [
                        'transaction_id' => $transaction->id,
                        'invoice_number' => $transaction->invoice_number,
                        'total_amount' => $transaction->total_amount,
                        'amount_paid' => $transaction->amount_paid,
                        'change_amount' => $transaction->change_amount,
                        'payment_method' => $transaction->payment_method,
                    ]
                ], 200);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Generate nomor invoice unik
     */
    private function generateInvoiceNumber($merchantId)
    {
        $date = now()->format('Ymd');
        $latestTransaction = Transaction::whereDate('created_at', now())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $latestTransaction ? intval(substr($latestTransaction->invoice_number, -4)) + 1 : 1;
        $sequence = str_pad($sequence, 4, '0', STR_PAD_LEFT);

        return "INV-{$date}-{$sequence}";
    }

    /**
     * Log perubahan stok ke StockLog
     */
    private function logStockChange($product, $quantity, int $stockBefore, $type, $reference)
    {
        StockLog::create([
            'product_id' => $product->id,
            'user_id' => Auth::id(),
            'quantity_change' => -1 * (int) $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => (int) $product->stock,
            'type' => $type === 'sale' ? 'out' : 'adjustment',
            'note' => 'Penjualan ' . $reference,
        ]);
    }

    /**
     * Get transaction details
     */
    public function show($invoiceNumber)
    {
        $user = Auth::user();
        $transaction = Transaction::where('invoice_number', $invoiceNumber)
            ->where('user_id', $user->id)
            ->with('items.product')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $transaction
        ]);
    }
}
