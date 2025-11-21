<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GuestVerification extends Model
{
    protected $fillable = [
        'email',
        'phone',
        'email_code',
        'phone_code',
        'email_verified',
        'phone_verified',
        'email_verified_at',
        'phone_verified_at',
        'expires_at',
        'session_token',
    ];

    protected $casts = [
        'email_verified' => 'boolean',
        'phone_verified' => 'boolean',
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Generate a 6-digit verification code
     */
    public static function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate a unique session token
     */
    public static function generateSessionToken(): string
    {
        return Str::random(64);
    }

    /**
     * Check if verification is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if both email and phone are verified
     */
    public function isFullyVerified(): bool
    {
        return $this->email_verified && $this->phone_verified;
    }

    /**
     * Mark email as verified
     */
    public function markEmailAsVerified(): void
    {
        $this->update([
            'email_verified' => true,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Mark phone as verified
     */
    public function markPhoneAsVerified(): void
    {
        $this->update([
            'phone_verified' => true,
            'phone_verified_at' => now(),
        ]);
    }

    /**
     * Verify email code
     */
    public function verifyEmailCode(string $code): bool
    {
        if ($this->email_code === $code && !$this->isExpired()) {
            $this->markEmailAsVerified();
            return true;
        }
        return false;
    }

    /**
     * Verify phone code
     */
    public function verifyPhoneCode(string $code): bool
    {
        if ($this->phone_code === $code && !$this->isExpired()) {
            $this->markPhoneAsVerified();
            return true;
        }
        return false;
    }
}
