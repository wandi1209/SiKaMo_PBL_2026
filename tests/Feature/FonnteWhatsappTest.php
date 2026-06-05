<?php

use App\Models\DailyReport;
use App\Models\Merchant;
use App\Models\User;
use App\Models\WhatsappLog;
use App\Services\FonnteWhatsappService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function createDailyReportForWhatsapp(array $merchantAttributes = [], array $reportAttributes = []): DailyReport
{
    $owner = User::factory()->create([
        'role' => 'owner',
    ]);

    $merchant = Merchant::create(array_merge([
        'owner_id' => $owner->id,
        'name' => 'Warung Budi',
        'slug' => 'warung-budi',
        'phone' => '08123456789',
        'is_active' => true,
    ], $merchantAttributes));

    return DailyReport::create(array_merge([
        'merchant_id' => $merchant->id,
        'report_date' => '2026-05-24',
        'total_transactions' => 42,
        'total_revenue' => 1250000,
        'total_items_sold' => 88,
        'sent_to_whatsapp' => false,
    ], $reportAttributes));
}

it('normalizes Indonesian WhatsApp numbers and sends Fonnte requests with authorization header', function () {
    config([
        'services.fonnte.token' => 'test-token',
        'services.fonnte.base_url' => 'https://api.fonnte.com',
        'services.fonnte.country_code' => '62',
        'services.fonnte.delay' => '2',
    ]);

    Http::fake([
        'api.fonnte.com/send' => Http::response([
            'status' => true,
        ], 200),
    ]);

    $service = app(FonnteWhatsappService::class);

    expect($service->normalizePhone('08123456789'))->toBe('628123456789')
        ->and($service->normalizePhone('+628123456789'))->toBe('628123456789')
        ->and($service->normalizePhone('628123456789'))->toBe('628123456789');

    $service->sendText('08123456789', 'Tes pesan');

    Http::assertSent(fn ($request): bool =>
        $request->url() === 'https://api.fonnte.com/send'
        && $request->hasHeader('Authorization', 'test-token')
        && $request['target'] === '628123456789'
        && $request['message'] === 'Tes pesan'
        && $request['countryCode'] === '62'
        && $request['delay'] === '2'
    );
});

it('marks a daily report as sent when Fonnte returns success', function () {
    config([
        'services.fonnte.token' => 'test-token',
        'services.fonnte.base_url' => 'https://api.fonnte.com',
    ]);

    $report = createDailyReportForWhatsapp();

    Http::fake([
        'api.fonnte.com/send' => Http::response([
            'detail' => 'success! message in queue',
            'id' => ['80367170'],
            'process' => 'pending',
            'requestid' => 2937124,
            'status' => true,
            'target' => ['628123456789'],
        ], 200),
    ]);

    $this->artisan('reports:send-whatsapp', [
        '--date' => '2026-05-24',
    ])->assertExitCode(0);

    $report->refresh();
    $log = WhatsappLog::first();

    expect($report->sent_to_whatsapp)->toBeTrue()
        ->and($report->sent_at)->not->toBeNull()
        ->and($log->status)->toBe('sent')
        ->and($log->phone_number)->toBe('628123456789')
        ->and($log->provider)->toBe('fonnte')
        ->and($log->provider_request_id)->toBe('2937124')
        ->and($log->provider_message_ids)->toBe(['80367170']);
});

it('creates a failed WhatsApp log when Fonnte returns failure', function () {
    config([
        'services.fonnte.token' => 'test-token',
        'services.fonnte.base_url' => 'https://api.fonnte.com',
    ]);

    $report = createDailyReportForWhatsapp();

    Http::fake([
        'api.fonnte.com/send' => Http::response([
            'status' => false,
            'reason' => 'device disconnect',
        ], 200),
    ]);

    $this->artisan('reports:send-whatsapp', [
        '--date' => '2026-05-24',
    ])->assertExitCode(1);

    $report->refresh();
    $log = WhatsappLog::first();

    expect($report->sent_to_whatsapp)->toBeFalse()
        ->and($report->sent_at)->toBeNull()
        ->and($log->status)->toBe('failed')
        ->and($log->error_message)->toBe('device disconnect');
});

it('does not resend reports that are already marked as sent', function () {
    config([
        'services.fonnte.token' => 'test-token',
        'services.fonnte.base_url' => 'https://api.fonnte.com',
    ]);

    createDailyReportForWhatsapp([], [
        'sent_to_whatsapp' => true,
        'sent_at' => now(),
    ]);

    Http::fake();

    $this->artisan('reports:send-whatsapp', [
        '--date' => '2026-05-24',
    ])->assertExitCode(0);

    Http::assertNothingSent();

    expect(WhatsappLog::count())->toBe(0);
});

it('sends due reports at merchant closing time', function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-24 22:00:00', 'Asia/Jakarta'));
    Storage::fake('local');

    config([
        'services.fonnte.token' => 'test-token',
        'services.fonnte.base_url' => 'https://api.fonnte.com',
    ]);

    $owner = User::factory()->create([
        'role' => 'owner',
    ]);

    $merchant = Merchant::create([
        'owner_id' => $owner->id,
        'name' => 'Warung Tutup Malam',
        'slug' => 'warung-tutup-malam',
        'phone' => '08123456789',
        'operational_start_time' => '07:00',
        'operational_end_time' => '22:00',
        'is_active' => true,
    ]);

    Http::fake([
        'api.fonnte.com/send' => Http::response([
            'id' => ['80367170'],
            'requestid' => 2937124,
            'status' => true,
        ], 200),
    ]);

    $this->artisan('reports:send-whatsapp', [
        '--due-now' => true,
    ])->assertExitCode(0);

    $report = DailyReport::where('merchant_id', $merchant->id)->first();

    expect($report)->not->toBeNull()
        ->and($report->report_date->toDateString())->toBe('2026-05-24')
        ->and($report->sent_to_whatsapp)->toBeTrue()
        ->and(WhatsappLog::where('merchant_id', $merchant->id)->where('status', 'sent')->exists())->toBeTrue();

    CarbonImmutable::setTestNow();
});

it('sends due reports at midnight for merchants without closing time', function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-05-25 00:00:00', 'Asia/Jakarta'));
    Storage::fake('local');

    config([
        'services.fonnte.token' => 'test-token',
        'services.fonnte.base_url' => 'https://api.fonnte.com',
    ]);

    $owner = User::factory()->create([
        'role' => 'owner',
    ]);

    $merchant = Merchant::create([
        'owner_id' => $owner->id,
        'name' => 'Warung Tanpa Jam Tutup',
        'slug' => 'warung-tanpa-jam-tutup',
        'phone' => '08123456789',
        'operational_end_time' => null,
        'is_active' => true,
    ]);

    Http::fake([
        'api.fonnte.com/send' => Http::response([
            'id' => ['80367170'],
            'requestid' => 2937124,
            'status' => true,
        ], 200),
    ]);

    $this->artisan('reports:send-whatsapp', [
        '--due-now' => true,
    ])->assertExitCode(0);

    $report = DailyReport::where('merchant_id', $merchant->id)->first();

    expect($report)->not->toBeNull()
        ->and($report->report_date->toDateString())->toBe('2026-05-24')
        ->and($report->sent_to_whatsapp)->toBeTrue()
        ->and(WhatsappLog::where('merchant_id', $merchant->id)->where('status', 'sent')->exists())->toBeTrue();

    CarbonImmutable::setTestNow();
});
