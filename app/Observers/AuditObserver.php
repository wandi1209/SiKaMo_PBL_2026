<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\DailyReport;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AuditObserver
{
    public function created(Model $model): void
    {
        $this->record('created', $model, null, $this->filterValues($model->getAttributes()));
    }

    public function updated(Model $model): void
    {
        $changes = Arr::except($model->getChanges(), ['updated_at']);

        if ($changes === []) {
            return;
        }

        $oldValues = [];

        foreach (array_keys($changes) as $key) {
            $oldValues[$key] = $model->getOriginal($key);
        }

        $this->record('updated', $model, $this->filterValues($oldValues), $this->filterValues($changes));
    }

    public function deleted(Model $model): void
    {
        $this->record('deleted', $model, $this->filterValues($model->getOriginal()), null);
    }

    private function record(string $event, Model $model, ?array $oldValues, ?array $newValues): void
    {
        if ($model instanceof AuditLog) {
            return;
        }

        $user = auth()->user();
        $changedFields = $this->changedFields($oldValues, $newValues);
        $context = $this->auditContext();

        $actor = $this->shouldAttributeToSystem($model, $event, $changedFields, $context)
            ? null
            : $user;

        $metadata = array_merge(
            $this->metadata($model),
            $this->contextMetadata($context),
        );

        AuditLog::create([
            'user_id' => $actor?->id,
            'merchant_id' => $this->merchantId($model, $user),
            'user_name' => $actor?->name,
            'user_email' => $actor?->email,
            'user_role' => $actor?->role,
            'event' => $event,
            'auditable_type' => $model::class,
            'auditable_table' => $model->getTable(),
            'auditable_id' => $model->getKey(),
            'auditable_label' => $this->label($model),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'changed_fields' => $changedFields,
            'summary' => $this->summary($event, $model, $changedFields, $oldValues, $newValues, $context),
            'request_method' => request()?->method(),
            'request_url' => request()?->fullUrl(),
            'route_name' => request()?->route()?->getName(),
            'panel' => filament()?->getCurrentPanel()?->getId(),
            'request_id' => $this->requestId(),
            'metadata' => $metadata,
            'ip_address' => request()?->ip(),
            'user_agent' => substr((string) request()?->userAgent(), 0, 255),
        ]);
    }

    private function label(Model $model): ?string
    {
        foreach (['name', 'email', 'invoice_number', 'key', 'phone_number'] as $attribute) {
            if (filled($model->getAttribute($attribute))) {
                return (string) $model->getAttribute($attribute);
            }
        }

        return null;
    }

    private function filterValues(array $values): array
    {
        return Arr::except($values, [
            'password',
            'remember_token',
            'email_verified_at',
        ]);
    }

    private function changedFields(?array $oldValues, ?array $newValues): array
    {
        return collect(array_keys($newValues ?? $oldValues ?? []))
            ->reject(fn (string $field): bool => in_array($field, ['created_at', 'updated_at'], true))
            ->values()
            ->all();
    }

    private function summary(string $event, Model $model, array $changedFields, ?array $oldValues, ?array $newValues, array $context = []): string
    {
        $label = $this->label($model) ?: class_basename($model);

        if ($event === 'created') {
            return "Menambahkan {$label}.";
        }

        if ($event === 'deleted') {
            return "Menghapus {$label}.";
        }

        if (array_key_exists('stock', $newValues ?? [])) {
            $oldStock = $oldValues['stock'] ?? '-';
            $newStock = $newValues['stock'] ?? '-';

            // Jika perubahan stok ini adalah otomatis (dipicu dari transaksi/refund),
            // tampilkan sebagai aksi System agar owner tidak salah sangka.
            if (($context['system_actor'] ?? false) === true) {
                $invoice = $context['transaction_invoice_number'] ?? null;
                $triggerName = $context['triggered_by_user_name'] ?? null;
                $triggerRole = $context['triggered_by_user_role'] ?? null;
                $reason = $context['system_reason'] ?? null;

                $who = $triggerName
                    ? " oleh {$triggerName}" . ($triggerRole ? " ({$triggerRole})" : '')
                    : '';

                if ($reason === 'sale') {
                    return $invoice
                        ? "Stok {$label} berkurang otomatis setelah transaksi {$invoice}{$who}."
                        : "Stok {$label} berkurang otomatis setelah transaksi{$who}.";
                }

                if ($reason === 'refund') {
                    return $invoice
                        ? "Stok {$label} bertambah otomatis karena refund transaksi {$invoice}{$who}."
                        : "Stok {$label} bertambah otomatis karena refund{$who}.";
                }

                return "Stok {$label} berubah otomatis (System) dari {$oldStock} menjadi {$newStock}.";
            }

            return "Stok {$label} berubah dari {$oldStock} menjadi {$newStock}.";
        }

        if ($changedFields === []) {
            return "Memperbarui {$label}.";
        }

        $fields = collect($changedFields)
            ->map(fn (string $field): string => Str::headline($field))
            ->take(6)
            ->implode(', ');

        return "Mengubah {$label}: {$fields}.";
    }

    private function auditContext(): array
    {
        if (! app()->bound('audit_context')) {
            return [];
        }

        $context = app('audit_context');

        return is_array($context) ? $context : [];
    }

    private function shouldAttributeToSystem(Model $model, string $event, array $changedFields, array $context): bool
    {
        // Hanya kasus tertentu yang kita ubah menjadi "System".
        if (! ($context['system_actor'] ?? false)) {
            return false;
        }

        // Fokus utama: perubahan stok Produk yang dipicu transaksi/refund.
        if ($model instanceof Product && in_array('stock', $changedFields, true)) {
            return true;
        }

        return false;
    }

    private function contextMetadata(array $context): array
    {
        // Hanya ambil key yang aman untuk disimpan ke metadata.
        $allowed = [
            'system_actor',
            'system_reason',
            'transaction_id',
            'transaction_invoice_number',
            'triggered_by_user_id',
            'triggered_by_user_name',
            'triggered_by_user_role',
        ];

        return collect($context)
            ->only($allowed)
            ->all();
    }

    private function merchantId(Model $model, ?User $user): ?int
    {
        if ($model instanceof Merchant) {
            return $model->id;
        }

        if ($model->getAttribute('merchant_id')) {
            return (int) $model->getAttribute('merchant_id');
        }

        if ($model instanceof User) {
            return $model->merchant_id ?? $model->merchant?->id;
        }

        if ($model instanceof Transaction) {
            return $model->merchant?->id;
        }

        return $user?->merchant_id
            ?? $user?->merchant?->id
            ?? filament()?->getTenant()?->id;
    }

    private function metadata(Model $model): array
    {
        $metadata = [
            'model' => class_basename($model),
            'table' => $model->getTable(),
        ];

        if ($model instanceof Product) {
            $metadata['category_id'] = $model->category_id;
            $metadata['barcode'] = $model->barcode;
            $metadata['price'] = $model->price;
            $metadata['stock'] = $model->stock;
        }

        if ($model instanceof Category) {
            $metadata['description'] = $model->description;
        }

        if ($model instanceof Transaction) {
            $metadata['invoice_number'] = $model->invoice_number;
            $metadata['payment_method'] = $model->payment_method;
            $metadata['total_amount'] = $model->total_amount;
            $metadata['status'] = $model->status;
        }

        if ($model instanceof DailyReport) {
            $metadata['report_date'] = $model->report_date;
            $metadata['total_revenue'] = $model->total_revenue;
        }

        return $metadata;
    }

    private function requestId(): string
    {
        $key = 'audit_request_id';

        if (! app()->bound($key)) {
            app()->instance($key, (string) Str::uuid());
        }

        return app($key);
    }
}
