<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use SoftDeletes;

    protected $table = 'equipment';

    protected $fillable = [
        'name',
        'description',
        'category',
        'model',
        'serial_number',
        'quantity_available',
        'is_critical',
        'location',
        'purchase_date',
        'warranty_expiry',
        'maintenance_notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'is_critical' => 'boolean',
        'quantity_available' => 'decimal:2',
    ];

    /**
     * Equipment belongs to many rooms
     */
    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'room_equipment')
            ->withPivot('quantity', 'installed', 'condition_notes', 'last_inspection', 'status')
            ->withTimestamps();
    }

    /**
     * Get categories list
     */
    public static function getCategories(): array
    {
        return [
            'audio' => '🔊 Zvuk a mikrofony',
            'video' => '📹 Video a projekce',
            'furniture' => '🪑 Nábytek',
            'climate' => '❄️ Klimatizace a vytápění',
            'lighting' => '💡 Osvětlení',
            'other' => '📦 Ostatní',
        ];
    }

    /**
     * Get status options for room_equipment
     */
    public static function getStatusOptions(): array
    {
        return [
            'operational' => '✅ Funkční',
            'needs_repair' => '🔧 Potřebuje opravu',
            'maintenance' => '🛠️ Údržba',
            'removed' => '❌ Odstraněno',
        ];
    }
}
