<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    /**
     * Audit log database table.
     */
    protected $table = 'audit_logs';

    /**
     * Attributes that can be assigned when recording an event.
     */
    protected $fillable = [
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    /**
     * Attribute casting.
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'auditable_id' => 'integer',
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * User who performed the action.
     *
     * Returns null for system-generated events or when the user
     * account has been deleted.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The record affected by the activity.
     *
     * Supports multiple model types, including Order, Payment,
     * MenuItem, MenuCategory and User.
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope: filter audit entries by the user who performed them.
     */
    public function scopePerformedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: filter by action name.
     */
    public function scopeForAction(Builder $query, string $action): Builder
    {
        return $query->where('action', $action);
    }

    /**
     * Scope: retrieve activities recorded within a date range.
     */
    public function scopeBetweenDates(
        Builder $query,
        string $from,
        string $to
    ): Builder {
        return $query
            ->where('created_at', '>=', $from . ' 00:00:00')
            ->where('created_at', '<', date(
                'Y-m-d 00:00:00',
                strtotime($to . ' +1 day')
            ));
    }

    /**
     * Scope: filter entries affecting a particular model record.
     */
    public function scopeForRecord(
        Builder $query,
        Model $record
    ): Builder {
        return $query
            ->where('auditable_type', $record->getMorphClass())
            ->where('auditable_id', $record->getKey());
    }

    /**
     * Record a new audit event.
     *
     * Example:
     * AuditLog::record('order_updated', 'Updated order ORD-1001', $order);
     */
    public static function record(
        string $action,
        string $description,
        ?Model $record = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = null
    ): self {
        $request = app()->bound('request')
            ? request()
            : null;

        return static::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'auditable_type' => $record?->getMorphClass(),
            'auditable_id' => $record?->getKey(),
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request?->ip(),
            'user_agent' => $request
                ? substr((string) $request->userAgent(), 0, 2000)
                : null,
        ]);
    }
}
