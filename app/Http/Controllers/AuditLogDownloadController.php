<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\DailyReport;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class AuditLogDownloadController extends Controller
{
    public function __invoke(AuditLog $auditLog)
    {
        $user = auth()->user();
        $merchantId = $user?->merchant?->id;

        abort_unless($user?->role === 'owner', 403);
        abort_unless($merchantId && $this->belongsToMerchant($auditLog, (int) $merchantId), 403);

        $auditLog->loadMissing(['user', 'merchant', 'reviewedBy']);

        $pdf = Pdf::loadView('pdf.audit-log-entry', [
            'log' => $auditLog,
        ]);

        return $pdf->download("audit-log-{$auditLog->id}.pdf");
    }

    private function belongsToMerchant(AuditLog $auditLog, int $merchantId): bool
    {
        if ((int) $auditLog->merchant_id === $merchantId) {
            return true;
        }

        return match ($auditLog->auditable_type) {
            Merchant::class => (int) $auditLog->auditable_id === $merchantId,
            Product::class => Product::query()->whereKey($auditLog->auditable_id)->where('merchant_id', $merchantId)->exists(),
            Category::class => Category::query()->whereKey($auditLog->auditable_id)->where('merchant_id', $merchantId)->exists(),
            User::class => User::query()->whereKey($auditLog->auditable_id)->where('merchant_id', $merchantId)->exists(),
            DailyReport::class => DailyReport::query()->whereKey($auditLog->auditable_id)->where('merchant_id', $merchantId)->exists(),
            Transaction::class => DB::table('t_transactions')
                ->join('t_transaction_items', 't_transaction_items.transaction_id', '=', 't_transactions.id')
                ->join('m_products', 'm_products.id', '=', 't_transaction_items.product_id')
                ->where('t_transactions.id', $auditLog->auditable_id)
                ->where('m_products.merchant_id', $merchantId)
                ->exists(),
            default => false,
        };
    }
}
