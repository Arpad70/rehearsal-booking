<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    protected Room $room;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->room = Room::factory()->create();
        $this->user = User::factory()->create();
    }

    /**
     * Test that overlapping reservations are prevented.
     */
    public function test_prevent_overlapping_reservations(): void
    {
        $startTime = now()->addHours(2);
        $endTime = (clone $startTime)->addHours(2); // 2 hours (> 60 min minimum)

        // Create first reservation
        Reservation::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'start_at' => $startTime,
            'end_at' => $endTime,
            'status' => 'pending',
        ]);

        // Try to create overlapping reservation
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);

        $response = $this->post('/reservations', [
            'room_id' => $this->room->id,
            'start_at' => $startTime->toDateTimeString(),
            'end_at' => $endTime->toDateTimeString(),
        ]);

        $response->assertSessionHasErrors(['slot']);
        $this->assertCount(1, Reservation::all());
    }

    /**
     * Test that minimum reservation duration is enforced.
     */
    public function test_minimum_reservation_duration(): void
    {
        $startTime = now()->addHours(2);
        $endTime = (clone $startTime)->addMinutes(5); // Less than 60 minutes

        $this->actingAs($this->user);

        $response = $this->post('/reservations', [
            'room_id' => $this->room->id,
            'start_at' => $startTime->toDateTimeString(),
            'end_at' => $endTime->toDateTimeString(),
        ]);

        $response->assertSessionHasErrors();
        $this->assertCount(0, Reservation::all());
    }

    /**
     * Test that valid reservations are created successfully.
     */
    public function test_create_valid_reservation(): void
    {
        $startTime = now()->addHours(2);
        $endTime = (clone $startTime)->addMinutes(90); // 90 minutes (> 60 min minimum)

        $this->actingAs($this->user);

        $response = $this->post('/reservations', [
            'room_id' => $this->room->id,
            'start_at' => $startTime->toDateTimeString(),
            'end_at' => $endTime->toDateTimeString(),
        ]);

        $this->assertCount(1, Reservation::all());
        $reservation = Reservation::first();
        $this->assertEquals($this->user->id, $reservation->user_id);
        $this->assertEquals($this->room->id, $reservation->room_id);
    }

    /**
     * Test that only owner can view their reservation.
     */
    public function test_only_owner_can_view_reservation(): void
    {
        $reservation = Reservation::factory()
            ->for($this->user)
            ->for($this->room)
            ->create();

        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);

        $response = $this->get("/reservations/{$reservation->id}");
        $response->assertForbidden();
    }

    /**
     * Test that owner can view their reservation.
     */
    public function test_owner_can_view_reservation(): void
    {
        $reservation = Reservation::factory()
            ->for($this->user)
            ->for($this->room)
            ->create();

        $this->actingAs($this->user);

        $response = $this->get("/reservations/{$reservation->id}");
        $response->assertOk();
    }

    /**
     * Test that only owner can delete their reservation.
     */
    public function test_only_owner_can_delete_reservation(): void
    {
        $reservation = Reservation::factory()
            ->for($this->user)
            ->for($this->room)
            ->create();

        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);

        $response = $this->delete("/reservations/{$reservation->id}");
        $response->assertForbidden();

        $this->assertDatabaseHas('reservations', ['id' => $reservation->id]);
    }

    /**
     * Test that owner can delete their reservation if not yet used.
     */
    public function test_owner_can_delete_pending_reservation(): void
    {
        $startTime = now()->addHours(2);
        $reservation = Reservation::factory()
            ->for($this->user)
            ->for($this->room)
            ->create([
                'start_at' => $startTime,
                'used_at' => null,
            ]);

        $this->actingAs($this->user);

        $response = $this->delete("/reservations/{$reservation->id}");
        $response->assertRedirect();

        $this->assertDatabaseMissing('reservations', ['id' => $reservation->id]);
    }

    /**
     * Test that owner cannot delete already used reservation.
     */
    public function test_owner_cannot_delete_used_reservation(): void
    {
        $reservation = Reservation::factory()
            ->for($this->user)
            ->for($this->room)
            ->create([
                'used_at' => now(),
            ]);

        $this->actingAs($this->user);

        $response = $this->delete("/reservations/{$reservation->id}");
        $response->assertForbidden();

        $this->assertDatabaseHas('reservations', ['id' => $reservation->id]);
    }
}
