<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promotion extends Model
{
    protected $fillable = [
        'title',
        'description',
        'type',
        'discount_code',
        'discount_percentage',
        'discount_amount',
        'image_url',
        'button_text',
        'button_url',
        'is_active',
        'is_permanent',
        'start_date',
        'end_date',
        'priority',
        'target_audience',
        'max_displays',
        'show_once_per_session',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_permanent' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'target_audience' => 'array',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'show_once_per_session' => 'boolean',
    ];

    /**
     * Get views for this promotion
     */
    public function views(): HasMany
    {
        return $this->hasMany(PromotionView::class);
    }

    /**
     * Check if promotion is currently valid
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->is_permanent) {
            return true;
        }

        $now = now();
        
        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $now->gt($this->end_date->endOfDay())) {
            return false;
        }

        return true;
    }

    /**
     * Check if user can see this promotion
     */
    public function canBeShownToUser(?int $userId, string $sessionId): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        // Check max displays
        if ($this->max_displays) {
            $totalViews = $this->views()->count();
            if ($totalViews >= $this->max_displays) {
                return false;
            }
        }

        // Check if shown once per session
        if ($this->show_once_per_session) {
            $existingView = $this->views()
                ->where(function($query) use ($userId, $sessionId) {
                    if ($userId) {
                        $query->where('user_id', $userId);
                    } else {
                        $query->where('session_id', $sessionId);
                    }
                })
                ->exists();

            if ($existingView) {
                return false;
            }
        }

        // Check target audience
        if ($this->target_audience && !empty($this->target_audience)) {
            $audience = $this->target_audience;
            
            if (in_array('all', $audience)) {
                return true;
            }

            if ($userId && in_array('registered', $audience)) {
                return true;
            }

            if (!$userId && in_array('guest', $audience)) {
                return true;
            }

            return false;
        }

        return true;
    }

    /**
     * Record a view
     */
    public function recordView(?int $userId, string $sessionId, string $ipAddress, string $action = 'viewed'): void
    {
        PromotionView::create([
            'promotion_id' => $this->id,
            'user_id' => $userId,
            'session_id' => $sessionId,
            'ip_address' => $ipAddress,
            'action' => $action,
            'viewed_at' => now(),
        ]);
    }

    /**
     * Get active promotions for display
     */
    public static function getActivePromotions(?int $userId, string $sessionId): ?self
    {
        return self::where('is_active', true)
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->first(function($promotion) use ($userId, $sessionId) {
                return $promotion->canBeShownToUser($userId, $sessionId);
            });
    }
}
