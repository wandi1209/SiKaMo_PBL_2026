<?php

namespace App\Exports;

use App\Models\DailyReport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DailyReportExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, ShouldQueue
{
     protected ?string $startDate = null;
     protected ?string $endDate = null;

     public function __construct(?string $startDate = null, ?string $endDate = null)
     {
          $this->startDate = $startDate;
          $this->endDate = $endDate;
     }

     public function query()
     {
          $query = DailyReport::query();

          if ($this->startDate) {
               $query->whereDate('report_date', '>=', $this->startDate);
          }

          if ($this->endDate) {
               $query->whereDate('report_date', '<=', $this->endDate);
          }

          return $query->orderBy('report_date', 'desc');
     }

     public function headings(): array
     {
          return [
               'Tanggal Laporan',
               'Total Transaksi',
               'Total Pendapatan',
               'Barang Terjual',
               'Status WhatsApp',
               'Waktu Terkirim',
               'Dibuat',
          ];
     }

     public function map($row): array
     {
          return [
               is_string($row->report_date) ? \Carbon\Carbon::parse($row->report_date)->format('d M Y') : $row->report_date->format('d M Y'),
               $row->total_transactions,
               'Rp ' . number_format($row->total_revenue, 0, ',', '.'),
               $row->total_items_sold,
               $row->sent_to_whatsapp ? 'Terkirim' : 'Belum',
               $row->sent_at ? (is_string($row->sent_at) ? \Carbon\Carbon::parse($row->sent_at)->format('d M Y H:i') : $row->sent_at->format('d M Y H:i')) : '-',
               is_string($row->created_at) ? \Carbon\Carbon::parse($row->created_at)->format('d M Y H:i') : $row->created_at->format('d M Y H:i'),
          ];
     }

     public function styles(Worksheet $sheet)
     {
          return [
               1 => [
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '366092']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
               ],
          ];
     }
}
