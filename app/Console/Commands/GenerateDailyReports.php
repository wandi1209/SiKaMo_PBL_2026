<?php

namespace App\Console\Commands;

use App\Services\DailyReportPdfGenerator;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class GenerateDailyReports extends Command
{
    protected $signature = 'reports:generate-daily
        {--date= : Tanggal laporan format YYYY-MM-DD. Default: kemarin Asia/Jakarta}
        {--merchant= : ID merchant tertentu}';

    protected $description = 'Generate laporan harian otomatis beserta file PDF.';

    public function handle(DailyReportPdfGenerator $generator): int
    {
        $date = $this->option('date')
            ? CarbonImmutable::parse($this->option('date'), 'Asia/Jakarta')
            : CarbonImmutable::now('Asia/Jakarta')->subDay();

        $merchantId = $this->option('merchant') ? (int) $this->option('merchant') : null;

        $reports = $generator->generateForDate($date, $merchantId);

        $this->info("Generated {$reports->count()} laporan harian untuk {$date->toDateString()}.");

        foreach ($reports as $report) {
            $this->line("- Merchant #{$report->merchant_id}: {$report->pdf_path}");
        }

        return self::SUCCESS;
    }
}