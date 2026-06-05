<?php

namespace App\Console\Commands;

use App\Models\DailyReport;
use App\Models\Merchant;
use App\Models\WhatsappLog;
use App\Services\DailyReportPdfGenerator;
use App\Services\DailyReportWhatsappMessageBuilder;
use App\Services\FonnteWhatsappService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;

class SendDailyReportsToWhatsapp extends Command
{
    protected $signature = 'reports:send-whatsapp
        {--date= : Tanggal laporan format YYYY-MM-DD. Default: kemarin Asia/Jakarta}
        {--merchant= : ID merchant tertentu}
        {--due-now : Kirim hanya untuk merchant yang sudah masuk jam tutup saat ini}
        {--retry-failed : Kirim ulang report yang log terakhirnya failed}';

    protected $description = 'Kirim laporan harian merchant ke WhatsApp memakai Fonnte API.';

    public function handle(
        FonnteWhatsappService $fonnte,
        DailyReportWhatsappMessageBuilder $messageBuilder,
        DailyReportPdfGenerator $reportGenerator,
    ): int {
        $merchantId = $this->option('merchant') ? (int) $this->option('merchant') : null;
        $retryFailed = (bool) $this->option('retry-failed');
        $dueNow = (bool) $this->option('due-now');

        if ($dueNow) {
            $reports = $this->generateDueReports($reportGenerator, $merchantId);
        } else {
            $date = $this->option('date')
                ? CarbonImmutable::parse($this->option('date'), 'Asia/Jakarta')
                : CarbonImmutable::now('Asia/Jakarta')->subDay();

            $reports = DailyReport::query()
                ->with(['merchant', 'whatsappLogs' => fn ($query) => $query->latest()])
                ->whereDate('report_date', $date->toDateString())
                ->when($merchantId, fn (Builder $query) => $query->where('merchant_id', $merchantId))
                ->when(
                    $retryFailed,
                    fn (Builder $query) => $query->whereHas('whatsappLogs', fn (Builder $query) => $query->where('status', 'failed')),
                    fn (Builder $query) => $query->where('sent_to_whatsapp', false),
                )
                ->orderBy('merchant_id')
                ->get();
        }

        $reports = $reports->filter(fn (DailyReport $report): bool => ! $report->sent_to_whatsapp);

        if ($reports->isEmpty()) {
            $this->info($dueNow
                ? 'Tidak ada merchant yang perlu dikirim laporan WhatsApp pada menit ini.'
                : "Tidak ada laporan harian yang perlu dikirim untuk {$date->toDateString()}.");

            return self::SUCCESS;
        }

        $sent = 0;
        $failed = 0;

        foreach ($reports as $report) {
            $message = $messageBuilder->build($report);
            $merchant = $report->merchant;
            $normalizedPhone = $fonnte->normalizePhone($merchant?->phone);

            if (! $merchant) {
                $this->markFailed($report, null, $message, 'Merchant laporan tidak ditemukan.');
                $failed++;
                continue;
            }

            if (! $fonnte->isValidPhone($merchant->phone)) {
                $this->markFailed($report, $normalizedPhone ?: $merchant->phone, $message, 'Nomor WhatsApp merchant kosong atau tidak valid.');
                $failed++;
                continue;
            }

            if (blank(config('services.fonnte.token'))) {
                $this->markFailed($report, $normalizedPhone, $message, 'FONNTE_TOKEN belum dikonfigurasi.');
                $failed++;
                continue;
            }

            $log = WhatsappLog::create([
                'merchant_id' => $report->merchant_id,
                'report_id' => $report->id,
                'phone_number' => $normalizedPhone,
                'message_body' => $message,
                'status' => 'pending',
                'provider' => 'fonnte',
            ]);

            try {
                $response = $fonnte->sendText($merchant->phone, $message);
                $body = $response->json() ?? ['raw' => $response->body()];

                if ($response->successful() && data_get($body, 'status') === true) {
                    $now = now();

                    $log->update([
                        'status' => 'sent',
                        'provider_request_id' => (string) data_get($body, 'requestid'),
                        'provider_message_ids' => (array) data_get($body, 'id', []),
                        'response_body' => $body,
                        'sent_at' => $now,
                    ]);

                    $report->update([
                        'sent_to_whatsapp' => true,
                        'sent_at' => $now,
                    ]);

                    $sent++;
                    $this->info("Terkirim: report #{$report->id} ke {$normalizedPhone}");

                    continue;
                }

                $error = data_get($body, 'reason')
                    ?? data_get($body, 'detail')
                    ?? "Fonnte mengembalikan HTTP {$response->status()}";

                $log->update([
                    'status' => 'failed',
                    'provider_request_id' => (string) data_get($body, 'requestid'),
                    'provider_message_ids' => (array) data_get($body, 'id', []),
                    'response_body' => $body,
                    'error_message' => $error,
                ]);

                Log::warning('Fonnte send failed', [
                    'report_id' => $report->id,
                    'merchant_id' => $report->merchant_id,
                    'response' => $body,
                ]);

                $failed++;
                $this->warn("Gagal: report #{$report->id} - {$error}");
            } catch (ConnectionException $exception) {
                $log->update([
                    'status' => 'failed',
                    'error_message' => $exception->getMessage(),
                ]);

                Log::warning('Fonnte connection failed', [
                    'report_id' => $report->id,
                    'merchant_id' => $report->merchant_id,
                    'error' => $exception->getMessage(),
                ]);

                $failed++;
                $this->warn("Gagal koneksi: report #{$report->id}");
            }
        }

        $this->info("Selesai. Terkirim: {$sent}. Gagal: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function markFailed(DailyReport $report, ?string $phone, string $message, string $error): void
    {
        WhatsappLog::create([
            'merchant_id' => $report->merchant_id,
            'report_id' => $report->id,
            'phone_number' => $phone ?: '-',
            'message_body' => $message,
            'status' => 'failed',
            'provider' => 'fonnte',
            'error_message' => $error,
        ]);

        Log::warning('Daily report WhatsApp send skipped', [
            'report_id' => $report->id,
            'merchant_id' => $report->merchant_id,
            'error' => $error,
        ]);
    }

    private function generateDueReports(DailyReportPdfGenerator $reportGenerator, ?int $merchantId): \Illuminate\Support\Collection
    {
        $now = CarbonImmutable::now('Asia/Jakarta');
        $currentTime = $now->format('H:i');

        return Merchant::query()
            ->when($merchantId, fn (Builder $query) => $query->whereKey($merchantId))
            ->where('is_active', true)
            ->orderBy('id')
            ->get()
            ->filter(fn (Merchant $merchant): bool => $this->merchantIsDueForWhatsapp($merchant, $currentTime))
            ->map(function (Merchant $merchant) use ($reportGenerator, $now): DailyReport {
                return $reportGenerator->generateForMerchant($merchant, $this->reportDateForDueMerchant($merchant, $now));
            })
            ->values();
    }

    private function merchantIsDueForWhatsapp(Merchant $merchant, string $currentTime): bool
    {
        return $this->closingTimeForMerchant($merchant) === $currentTime;
    }

    private function reportDateForDueMerchant(Merchant $merchant, CarbonImmutable $now): CarbonImmutable
    {
        $startTime = $this->timeToMinutes($merchant->operational_start_time);
        $closingTime = $this->timeToMinutes($merchant->operational_end_time);

        if ($closingTime === null || $closingTime === 0 || ($startTime !== null && $closingTime <= $startTime)) {
            return $now->subDay();
        }

        return $now;
    }

    private function closingTimeForMerchant(Merchant $merchant): string
    {
        $time = $merchant->operational_end_time;

        if (blank($time)) {
            return '00:00';
        }

        return substr((string) $time, 0, 5);
    }

    private function timeToMinutes(?string $time): ?int
    {
        if (blank($time)) {
            return null;
        }

        [$hour, $minute] = array_map('intval', explode(':', substr($time, 0, 5)));

        return ($hour * 60) + $minute;
    }
}
