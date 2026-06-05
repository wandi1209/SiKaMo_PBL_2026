<?php

namespace App\Services;

use App\Models\DailyReport;

class DailyReportWhatsappMessageBuilder
{
    public function build(DailyReport $report): string
    {
        $merchant = $report->merchant;

        return implode("\n", [
            'Laporan Harian SiKaMo',
            '',
            'Toko: ' . ($merchant?->name ?? '-'),
            'Tanggal: ' . $report->report_date?->timezone('Asia/Jakarta')->translatedFormat('d M Y'),
            'Total Transaksi: ' . number_format((float) $report->total_transactions, 0, ',', '.'),
            'Total Pendapatan: Rp ' . number_format((float) $report->total_revenue, 0, ',', '.'),
            'Barang Terjual: ' . number_format((float) $report->total_items_sold, 0, ',', '.') . ' item',
            '',
            'Laporan dibuat otomatis oleh SiKaMo.',
        ]);
    }
}
