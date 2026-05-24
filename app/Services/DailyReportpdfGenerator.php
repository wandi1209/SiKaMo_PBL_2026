<?php

namespace App\Services;

use App\Models\DailyReport;
use App\Models\Merchant;
use App\Models\TransactionItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DailyReportPdfGenerator
{
    public function generateForDate(CarbonImmutable $reportDate, ?int $merchantId = null): Collection
    {
        return Merchant::query()
            ->when($merchantId, fn ($query) => $query->whereKey($merchantId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Merchant $merchant): DailyReport => $this->generateForMerchant($merchant, $reportDate));
    }

    public function generateForMerchant(Merchant $merchant, CarbonImmutable $reportDate): DailyReport
    {
        $period = $this->periodForJakartaDate($reportDate);

        $items = TransactionItem::query()
            ->join('t_transactions', 't_transactions.id', '=', 't_transaction_items.transaction_id')
            ->join('m_products', 'm_products.id', '=', 't_transaction_items.product_id')
            ->where('m_products.merchant_id', $merchant->id)
            ->where('t_transactions.status', 'completed')
            ->whereBetween('t_transactions.created_at', [$period['start'], $period['end']]);

        $summary = [
            'total_transactions' => (clone $items)->distinct('t_transaction_items.transaction_id')->count('t_transaction_items.transaction_id'),
            'total_revenue' => (clone $items)->sum('t_transaction_items.subtotal'),
            'total_items_sold' => (clone $items)->sum('t_transaction_items.quantity'),
        ];

        $paymentMethods = (clone $items)
            ->selectRaw('
                t_transactions.payment_method,
                COUNT(DISTINCT t_transaction_items.transaction_id) as total_transactions,
                SUM(t_transaction_items.subtotal) as total_revenue
            ')
            ->groupBy('t_transactions.payment_method')
            ->orderBy('t_transactions.payment_method')
            ->get();

        $topProducts = (clone $items)
            ->selectRaw('m_products.name, SUM(t_transaction_items.quantity) as total_quantity, SUM(t_transaction_items.subtotal) as total_revenue')
            ->groupBy('m_products.id', 'm_products.name')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();

        $report = DailyReport::query()->updateOrCreate(
            [
                'merchant_id' => $merchant->id,
                'report_date' => $reportDate->toDateString(),
            ],
            $summary,
        );

        $pdfPath = $this->storePdf($report, $merchant, $reportDate, $paymentMethods, $topProducts);

        $report->forceFill([
            'pdf_path' => $pdfPath,
            'pdf_generated_at' => now(),
        ])->save();

        return $report->refresh();
    }

    private function periodForJakartaDate(CarbonImmutable $reportDate): array
    {
        $date = $reportDate->setTimezone('Asia/Jakarta');

        return [
            'start' => $date->startOfDay()->setTimezone(config('app.timezone')),
            'end' => $date->endOfDay()->setTimezone(config('app.timezone')),
        ];
    }

    private function storePdf(
        DailyReport $report,
        Merchant $merchant,
        CarbonImmutable $reportDate,
        Collection $paymentMethods,
        Collection $topProducts,
    ): string {
        $path = sprintf(
            'daily-reports/%s/laporan-harian-%s.pdf',
            Str::slug($merchant->slug ?: $merchant->name),
            $reportDate->format('Y-m-d'),
        );

        $pdf = Pdf::loadView('pdf.daily-report', [
            'merchant' => $merchant,
            'report' => $report,
            'paymentMethods' => $paymentMethods,
            'topProducts' => $topProducts,
            'generatedAt' => now()->timezone('Asia/Jakarta'),
        ])->setPaper('a4');

        Storage::disk('local')->put($path, $pdf->output());

        return $path;
    }
}