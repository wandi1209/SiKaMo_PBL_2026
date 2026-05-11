<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

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

        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => $event,
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'auditable_label' => $this->label($model),
            'old_values' => $oldValues,
            'new_values' => $newValues,
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
}
