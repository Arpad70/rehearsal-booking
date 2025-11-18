<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    /**
     * Determine whether the user can view the reservation.
     */
    public function view(User $user, Reservation $reservation): bool
    {
        return $user->id === $reservation->user_id;
    }

    /**
     * Determine whether the user can update the reservation.
     */
    public function update(User $user, Reservation $reservation): bool
    {
        // Only allow update if not yet used and not started
        if ($reservation->used_at !== null) {
            return false;
        }

        if ($reservation->start_at <= now()) {
            return false;
        }

        return $user->id === $reservation->user_id;
    }

    /**
     * Determine whether the user can delete the reservation.
     */
    public function delete(User $user, Reservation $reservation): bool
    {
        // Only allow delete if not yet used and not started
        if ($reservation->used_at !== null) {
            return false;
        }

        if ($reservation->start_at <= now()) {
            return false;
        }

        return $user->id === $reservation->user_id;
    }

    /**
     * Determine whether the user can view QR code for the reservation.
     */
    public function viewQr(User $user, Reservation $reservation): bool
    {
        return $user->id === $reservation->user_id;
    }
}
