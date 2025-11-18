<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $action
 * @property string $model_type
 * @property int $model_id
 * @property int|null $user_id
 * @property array|null $old_values
 * @property array|null $new_values
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class AuditLog extends Model
{
    protected $fillable = [
        'action',
        'model_type',
        'model_id',
        'user_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public static function logAction(
        string $action,
        string $modelType,
        int $modelId,
        ?int $userId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
    ): self {
        return static::create([
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'user_id' => $userId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
