<?php

namespace App\Filament\Merchant\Resources\AuditLogResource\Pages;

use App\Filament\Merchant\Resources\AuditLogResource;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\DailyReport;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ViewAuditLog extends ViewRecord
{
    protected static string $resource = AuditLogResource::class;

    protected static string $view = 'filament.merchant.resources.audit-log-resource.pages.view-audit-log';

    public ?string $reviewNote = null;

    private ?AuditLog $cachedSessionLogin = null;

    private ?AuditLog $cachedSessionLogout = null;

    private ?Collection $cachedSessionLogs = null;

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->reviewNote = $this->record->review_note;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function markReviewed(): void
    {
        if (! $this->canMarkReviewed()) {
            return;
        }

        $this->record->forceFill([
            'review_note' => $this->reviewNote,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ])->save();

        $this->record->refresh();

        Notification::make()
            ->title('Audit log ditandai sudah ditinjau')
            ->success()
            ->send();
    }

    public function saveReviewNote(): void
    {
        if (! Schema::hasColumn('audit_logs', 'review_note')) {
            return;
        }

        $this->record->forceFill([
            'review_note' => $this->reviewNote,
        ])->save();

        $this->record->refresh();

        Notification::make()
            ->title('Catatan peninjau disimpan')
            ->success()
            ->send();
    }

    public function canMarkReviewed(): bool
    {
        return Schema::hasColumn('audit_logs', 'reviewed_at')
            && AuditLogResource::isFlagged($this->record)
            && blank($this->record->reviewed_at);
    }

    public function isFlagged(): bool
    {
        return AuditLogResource::isFlagged($this->record);
    }

    public function flagDescription(): ?string
    {
        return AuditLogResource::flagDescription($this->record);
    }

    public function eventLabel(?string $event = null): string
    {
        $event ??= $this->record->event;

        return match ($event) {
            'created' => 'Tambah',
            'updated' => 'Update',
            'deleted' => 'Hapus',
            'login' => 'Login',
            'logout' => 'Logout',
            null, '' => '-',
            default => Str::headline($event),
        };
    }

    public function roleLabel(?string $role = null): string
    {
        $role ??= $this->record->user_role;

        return match ($role) {
            'cashier' => 'Kasir',
            'owner' => 'Owner',
            'admin' => 'Admin',
            null, '' => 'System',
            default => Str::headline($role),
        };
    }

    public function modelLabel(?string $model = null): string
    {
        $model ??= $this->record->auditable_type;

        return match ($model) {
            Product::class => 'Produk',
            Category::class => 'Kategori',
            User::class => 'Kasir/User',
            Transaction::class => 'Transaksi',
            DailyReport::class => 'Laporan Harian',
            null, '' => '-',
            default => class_basename($model),
        };
    }

    public function changedOldValues(): array
    {
        return $this->onlyChangedValues($this->record, (array) ($this->record->old_values ?? []));
    }

    public function changedNewValues(): array
    {
        return $this->onlyChangedValues($this->record, (array) ($this->record->new_values ?? []));
    }

    /**
     * Timeline sesi untuk user dari log ini: Login → aksi → Logout.
     * Sesi dihitung dari login terakhir sebelum log ini, sampai logout pertama setelahnya.
     */
    public function sessionLogin(): ?AuditLog
    {
        $this->buildSessionTimelineIfNeeded();

        return $this->cachedSessionLogin;
    }

    public function sessionLogout(): ?AuditLog
    {
        $this->buildSessionTimelineIfNeeded();

        return $this->cachedSessionLogout;
    }

    public function sessionLogs(): Collection
    {
        $this->buildSessionTimelineIfNeeded();

        return $this->cachedSessionLogs ?? collect();
    }

    private function buildSessionTimelineIfNeeded(): void
    {
        if ($this->cachedSessionLogs !== null) {
            return;
        }

        $record = $this->record;

        // Jika tidak ada user (System), tidak perlu timeline sesi.
        if (blank($record->user_id) && blank($record->user_name)) {
            $this->cachedSessionLogs = collect();

            return;
        }

        $base = AuditLog::query()
            ->when(
                filled($record->merchant_id),
                fn (Builder $query): Builder => $query->where('merchant_id', $record->merchant_id),
            )
            ->when(
                filled($record->user_id),
                fn (Builder $query): Builder => $query->where('user_id', $record->user_id),
                fn (Builder $query): Builder => $query->where('user_name', $record->user_name),
            );

        $this->cachedSessionLogin = (clone $base)
            ->where('event', 'login')
            ->where('created_at', '<=', $record->created_at)
            ->latest('created_at')
            ->first();

        $startAt = $this->cachedSessionLogin?->created_at ?? Carbon::parse($record->created_at)->subHours(2);

        $this->cachedSessionLogout = (clone $base)
            ->where('event', 'logout')
            ->where('created_at', '>=', $startAt)
            ->orderBy('created_at')
            ->first();

        $endAt = $this->cachedSessionLogout?->created_at ?? Carbon::parse($record->created_at)->addHours(2);

        $this->cachedSessionLogs = (clone $base)
            ->whereBetween('created_at', [$startAt, $endAt])
            ->orderBy('created_at')
            ->limit(200)
            ->get();
    }

    public function fieldLabel(string $field): string
    {
        return Str::headline(str_replace('_', ' ', $field));
    }

    public function valueLabel(mixed $value, ?string $field = null): string
    {
        if (is_bool($value)) {
            return $value ? 'Ya' : 'Tidak';
        }

        if ($value === null || $value === '') {
            return '-';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        if ($field && is_numeric($value) && str_contains(strtolower($field), 'price')) {
            return 'Rp' . number_format((float) $value, 0, ',', '.');
        }

        if ($field && is_numeric($value) && str_contains(strtolower($field), 'harga')) {
            return 'Rp' . number_format((float) $value, 0, ',', '.');
        }

        return (string) $value;
    }

    private function onlyChangedValues(AuditLog $record, array $values): array
    {
        $fields = $record->changed_fields ?? [];

        if (! is_array($fields) || $fields === []) {
            return $values;
        }

        return collect($fields)
            ->mapWithKeys(fn (string $field): array => [$field => $values[$field] ?? null])
            ->all();
    }
}
