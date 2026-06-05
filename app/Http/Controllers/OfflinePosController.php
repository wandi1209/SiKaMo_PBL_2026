<?php

namespace App\Http\Controllers;

use App\Events\ProductStockUpdated;
use App\Models\Discount;
use App\Models\Member;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\StockLog;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OfflinePosController extends Controller
{
    public function snapshot(Request $request): JsonResponse
    {
        $user = $request->user();
        $merchant = $this->merchantFor($user);

        abort_unless($user && $merchant, 403);

        $products = Product::query()
            ->with('category')
            ->where('merchant_id', $merchant->id)
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product): array => [
                'id' => $product->id,
                'category_id' => $product->category_id,
                'category_name' => $product->category?->name ?? 'Umum',
                'name' => $product->name,
                'barcode' => $product->barcode,
                'price' => (float) $product->price,
                'stock' => (int) $product->stock,
                'low_stock_threshold' => (int) $product->low_stock_threshold,
                'image_url' => $product->image ? asset('storage/' . $product->image) : null,
                'updated_at' => $product->updated_at?->toIso8601String(),
            ]);

        $categories = $merchant->categories()
            ->whereHas('products')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($category): array => [
                'id' => $category->id,
                'name' => $category->name,
            ]);

        $members = Member::query()
            ->where('merchant_id', $merchant->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'updated_at'])
            ->map(fn (Member $member): array => [
                'id' => $member->id,
                'name' => $member->name,
                'phone' => $member->phone,
                'updated_at' => $member->updated_at?->toIso8601String(),
            ]);

        $discounts = Discount::query()
            ->where('merchant_id', $merchant->id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(fn (Discount $discount): array => [
                'id' => $discount->id,
                'code' => $discount->code,
                'name' => $discount->name,
                'type' => $discount->type,
                'value' => (float) $discount->value,
                'max_discount_amount' => $discount->max_discount_amount !== null ? (float) $discount->max_discount_amount : null,
                'min_purchase_amount' => (float) $discount->min_purchase_amount,
                'quota' => $discount->quota,
                'used_count' => $discount->used_count,
                'starts_at' => $discount->starts_at?->toIso8601String(),
                'ends_at' => $discount->ends_at?->toIso8601String(),
            ]);

        return response()->json([
            'synced_at' => now()->toIso8601String(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
            ],
            'merchant' => [
                'id' => $merchant->id,
                'name' => $merchant->name,
                'address' => $merchant->address,
                'member_discount_is_active' => (bool) $merchant->member_discount_is_active,
                'member_discount_type' => $merchant->member_discount_type,
                'member_discount_value' => (float) $merchant->member_discount_value,
                'member_discount_max_amount' => $merchant->member_discount_max_amount !== null ? (float) $merchant->member_discount_max_amount : null,
                'member_discount_min_purchase' => (float) $merchant->member_discount_min_purchase,
                'qris_static_image_url' => $merchant->qr_code_image ? asset('storage/' . $merchant->qr_code_image) : null,
            ],
            'categories' => $categories,
            'products' => $products,
            'members' => $members,
            'discounts' => $discounts,
            'payment_methods' => [
                ['id' => 'cash', 'name' => 'Tunai', 'offline' => true],
                ['id' => 'qris', 'name' => 'QRIS Statis', 'offline' => (bool) $merchant->qr_code_image],
            ],
        ]);
    }

    public function sync(Request $request): JsonResponse
    {
        $user = $request->user();
        $merchant = $this->merchantFor($user);

        abort_unless($user && $merchant, 403);

        $validated = $request->validate([
            'transactions' => ['required', 'array', 'min:1', 'max:50'],
            'transactions.*.client_id' => ['required', 'string', 'max:120'],
            'transactions.*.created_at' => ['required', 'date'],
            'transactions.*.payment_method' => ['required', 'in:cash,qris'],
            'transactions.*.amount_paid' => ['required', 'numeric', 'min:0'],
            'transactions.*.discount_code' => ['nullable', 'string', 'max:80'],
            'transactions.*.member_id' => ['nullable', 'integer'],
            'transactions.*.member_phone' => ['nullable', 'string', 'max:40'],
            'transactions.*.items' => ['required', 'array', 'min:1'],
            'transactions.*.items.*.product_id' => ['required', 'integer'],
            'transactions.*.items.*.quantity' => ['required', 'integer', 'min:1'],
            'transactions.*.items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $results = [];

        foreach ($validated['transactions'] as $payload) {
            try {
                $transaction = $this->storeOfflineTransaction($payload, $user, $merchant);

                $results[] = [
                    'client_id' => $payload['client_id'],
                    'status' => 'synced',
                    'transaction_id' => $transaction->id,
                    'invoice_number' => $transaction->invoice_number,
                    'synced_at' => $transaction->synced_at?->toIso8601String(),
                ];
            } catch (\Throwable $exception) {
                $results[] = [
                    'client_id' => $payload['client_id'],
                    'status' => 'failed',
                    'message' => $exception->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }

    private function storeOfflineTransaction(array $payload, $user, Merchant $merchant): Transaction
    {
        return DB::transaction(function () use ($payload, $user, $merchant): Transaction {
            $existing = Transaction::query()
                ->where('offline_client_id', $payload['client_id'])
                ->first();

            if ($existing) {
                return $existing;
            }

            $subtotalAmount = 0;
            $transactionItems = [];

            foreach ($payload['items'] as $item) {
                $product = Product::query()
                    ->where('merchant_id', $merchant->id)
                    ->lockForUpdate()
                    ->find($item['product_id']);

                if (! $product) {
                    throw ValidationException::withMessages([
                        'items' => "Produk ID {$item['product_id']} tidak ditemukan di toko ini.",
                    ]);
                }

                if ((int) $product->stock < (int) $item['quantity']) {
                    throw ValidationException::withMessages([
                        'stock' => "Stok {$product->name} tidak mencukupi. Stok tersedia: {$product->stock}.",
                    ]);
                }

                $unitPrice = (float) $item['unit_price'];
                $quantity = (int) $item['quantity'];
                $subtotal = $unitPrice * $quantity;
                $unitCost = (float) ($product->cost_price ?? 0);
                $totalCost = $unitCost * $quantity;
                $subtotalAmount += $subtotal;

                $transactionItems[] = [
                    'product' => $product,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                    'total_cost' => $totalCost,
                ];
            }

            [$discount, $voucherDiscountAmount] = $this->resolveDiscount($payload, $merchant, $subtotalAmount);
            [$member, $memberDiscountAmount] = $this->resolveMemberDiscount($payload, $merchant, $subtotalAmount, $voucherDiscountAmount);

            $totalDiscountAmount = $voucherDiscountAmount + $memberDiscountAmount;
            $totalAmount = max(0, $subtotalAmount - $totalDiscountAmount);
            $amountPaid = $payload['payment_method'] === 'cash'
                ? (float) $payload['amount_paid']
                : $totalAmount;

            if ($amountPaid < $totalAmount) {
                throw ValidationException::withMessages([
                    'amount_paid' => 'Jumlah pembayaran kurang dari total belanja.',
                ]);
            }

            $offlineCreatedAt = Carbon::parse($payload['created_at']);

            $transaction = new Transaction();
            $transaction->forceFill([
                'user_id' => $user->id,
                'discount_id' => $discount?->id,
                'discount_code' => $discount?->code,
                'member_id' => $member?->id,
                'member_name' => $member?->name,
                'member_phone' => $member?->phone,
                'invoice_number' => $this->generateInvoiceNumber($offlineCreatedAt),
                'offline_client_id' => $payload['client_id'],
                'offline_created_at' => $offlineCreatedAt,
                'synced_at' => now(),
                'subtotal_amount' => $subtotalAmount,
                'discount_amount' => $totalDiscountAmount,
                'member_discount_amount' => $memberDiscountAmount,
                'total_amount' => $totalAmount,
                'amount_paid' => $amountPaid,
                'change_amount' => $amountPaid - $totalAmount,
                'payment_method' => $payload['payment_method'],
                'status' => 'completed',
                'created_at' => $offlineCreatedAt,
                'updated_at' => now(),
            ]);
            $transaction->save();

            if ($discount) {
                $discount->increment('used_count');
            }

            $allocatedDiscount = 0;
            $lastIndex = count($transactionItems) - 1;

            foreach ($transactionItems as $index => $itemData) {
                $itemDiscount = 0;

                if ($totalDiscountAmount > 0 && $subtotalAmount > 0) {
                    $itemDiscount = $index === $lastIndex
                        ? $totalDiscountAmount - $allocatedDiscount
                        : round($totalDiscountAmount * ($itemData['subtotal'] / $subtotalAmount), 2);

                    $allocatedDiscount += $itemDiscount;
                }

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'unit_cost' => $itemData['unit_cost'],
                    'subtotal' => $itemData['subtotal'],
                    'total_cost' => $itemData['total_cost'],
                    'gross_profit' => $itemData['subtotal'] - $itemDiscount - $itemData['total_cost'],
                ]);

                $product = $itemData['product'];
                $stockBefore = (int) $product->stock;

                app()->instance('audit_context', [
                    'system_actor' => true,
                    'system_reason' => 'offline-sale-sync',
                    'transaction_id' => $transaction->id,
                    'transaction_invoice_number' => $transaction->invoice_number,
                    'triggered_by_user_id' => $user->id,
                    'triggered_by_user_name' => $user->name,
                    'triggered_by_user_role' => $user->role,
                ]);

                try {
                    $product->decrement('stock', $itemData['quantity']);
                    $product->refresh();

                    DB::afterCommit(fn () => event(ProductStockUpdated::fromProduct($product)));
                } finally {
                    app()->forgetInstance('audit_context');
                }

                StockLog::create([
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'quantity_change' => -1 * (int) $itemData['quantity'],
                    'stock_before' => $stockBefore,
                    'stock_after' => (int) $product->stock,
                    'type' => 'out',
                    'note' => 'Sync penjualan offline ' . $transaction->invoice_number,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $transaction;
        });
    }

    private function resolveDiscount(array $payload, Merchant $merchant, float $subtotal): array
    {
        $code = strtoupper(trim((string) ($payload['discount_code'] ?? '')));

        if ($code === '') {
            return [null, 0];
        }

        $discount = Discount::query()
            ->where('merchant_id', $merchant->id)
            ->where('code', $code)
            ->lockForUpdate()
            ->first();

        if (! $discount || ! $discount->isUsableFor($subtotal)) {
            throw ValidationException::withMessages([
                'discount_code' => 'Kode diskon tidak valid atau sudah tidak bisa digunakan.',
            ]);
        }

        return [$discount, $discount->calculateDiscount($subtotal)];
    }

    private function resolveMemberDiscount(array $payload, Merchant $merchant, float $subtotal, float $voucherDiscountAmount): array
    {
        $member = null;
        $memberId = $payload['member_id'] ?? null;
        $memberPhone = $payload['member_phone'] ?? null;

        if ($memberId) {
            $member = Member::query()
                ->where('merchant_id', $merchant->id)
                ->where('is_active', true)
                ->lockForUpdate()
                ->find($memberId);
        } elseif ($memberPhone) {
            $member = Member::query()
                ->where('merchant_id', $merchant->id)
                ->where('phone', $this->normalizePhone($memberPhone))
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();
        }

        if (! $member) {
            return [null, 0];
        }

        if (! $merchant->member_discount_is_active || $subtotal < (float) $merchant->member_discount_min_purchase) {
            return [$member, 0];
        }

        $discountableAmount = max(0, $subtotal - $voucherDiscountAmount);

        if ($merchant->member_discount_type === 'percent') {
            $discount = $discountableAmount * ((float) $merchant->member_discount_value / 100);

            if ($merchant->member_discount_max_amount !== null) {
                $discount = min($discount, (float) $merchant->member_discount_max_amount);
            }

            return [$member, min($discount, $discountableAmount)];
        }

        return [$member, min((float) $merchant->member_discount_value, $discountableAmount)];
    }

    private function generateInvoiceNumber(Carbon $createdAt): string
    {
        $date = $createdAt->timezone(config('app.timezone'))->format('Ymd');
        $latestTransaction = Transaction::query()
            ->whereDate('created_at', $createdAt->toDateString())
            ->orderByDesc('id')
            ->first();

        $sequence = $latestTransaction ? ((int) substr($latestTransaction->invoice_number, -4)) + 1 : 1;

        return 'INV-' . $date . '-' . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    private function merchantFor($user): ?Merchant
    {
        if (! $user) {
            return null;
        }

        if ($user->merchant_id) {
            return Merchant::find($user->merchant_id);
        }

        if ($user->merchant?->id) {
            return $user->merchant;
        }

        return filament()?->getTenant();
    }

    private function normalizePhone(?string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', (string) $phone);

        if (str_starts_with($phone, '+62')) {
            return '0' . substr($phone, 3);
        }

        if (str_starts_with($phone, '62')) {
            return '0' . substr($phone, 2);
        }

        return $phone;
    }
}
