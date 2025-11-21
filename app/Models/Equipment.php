<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'category_id',
        'model',
        'serial_number',
        'rfid_tag',  // Zpětná kompatibilita - alias pro tag_id
        'tag_id',
        'tag_type',
        'quantity_available',
        'is_critical',
        'location',
        'status',
        'purchase_date',
        'purchase_price',
        'warranty_expiry',
        'maintenance_notes',
        'last_maintenance',
        'next_maintenance',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'last_maintenance' => 'date',
            'next_maintenance' => 'date',
            'warranty_expiry' => 'date',
            'is_critical' => 'boolean',
            'quantity_available' => 'integer',
            'purchase_price' => 'decimal:2',
        ];
    }

    /**
     * Equipment belongs to a category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

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
            'audio' => '🔊 Audio - Mikrofony, reproboxы',
            'instrument' => '🎸 Nástroje - Kytary, bicí',
            'lighting' => '💡 Osvětlení',
            'recording' => '🎙️ Nahrávací technika',
            'mixer' => '🎚️ Mixážní pulty',
            'accessory' => '🔌 Příslušenství - Kabely, stojany',
            'furniture' => '🪑 Nábytek',
            'other' => '📦 Ostatní',
        ];
    }

    /**
     * Get status options
     */
    public static function getStatusOptions(): array
    {
        return [
            'available' => '✅ Dostupné',
            'in_use' => '🔵 Používané',
            'maintenance' => '🛠️ Údržba',
            'repair' => '🔧 V opravě',
            'retired' => '❌ Vyřazeno',
        ];
    }

    /**
     * Check if equipment needs maintenance
     */
    public function needsMaintenance(): bool
    {
        if (!$this->next_maintenance) {
            return false;
        }
        return $this->next_maintenance->isPast() || $this->next_maintenance->isToday();
    }

    /**
     * Check if warranty is valid
     */
    public function hasValidWarranty(): bool
    {
        if (!$this->warranty_expiry) {
            return false;
        }
        return $this->warranty_expiry->isFuture();
    }

    /**
     * Accessor pro zpětnou kompatibilitu - rfid_tag alias pro tag_id
     */
    public function getRfidTagAttribute(): ?string
    {
        return $this->tag_id;
    }

    /**
     * Mutator pro zpětnou kompatibilitu - rfid_tag nastaví tag_id
     */
    public function setRfidTagAttribute(?string $value): void
    {
        $this->attributes['tag_id'] = $value;
        
        // Pokud nastavujeme hodnotu a tag_type není nastaven, nastavíme jako rfid
        if ($value && !$this->tag_type) {
            $this->attributes['tag_type'] = 'rfid';
        }
    }

    /**
     * Získání typu tagu jako text
     */
    public function getTagTypeLabel(): string
    {
        return match($this->tag_type) {
            'rfid' => '📡 RFID',
            'nfc' => '📱 NFC',
            default => '🏷️ Tag',
        };
    }

    /**
     * Kontrola zda má zařízení nějaký tag (RFID nebo NFC)
     */
    public function hasTag(): bool
    {
        return !empty($this->tag_id);
    }
}
