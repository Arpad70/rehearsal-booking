<?php

namespace Tests\Feature;

use App\Models\AccessLog;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessValidationTest extends TestCase
{
    use RefreshDatabase;

    protected Room $room;
    protected User $user;
    protected Reservation $reservation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->room = Room::factory()->create();
        $this->user = User::factory()->create();

        $startTime = now()->addMinutes(10);
        $endTime = $startTime->addHours(1);

        $this->reservation = Reservation::create([
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'start_at' => $startTime,
            'end_at' => $endTime,
            'status' => 'pending',
            'token_valid_from' => $startTime->subMinutes(5),
            'token_expires_at' => $endTime->addMinutes(5),
        ]);
    }

    /**
     * Test that invalid token is rejected.
     */
    public function test_invalid_token_rejected(): void
    {
        $response = $this->postJson('/api/access/validate', [
            'token' => 'invalid_token_format',
            'room_id' => $this->room->id,
        ]);

        $response->assertForbidden();
        $response->assertJson(['allowed' => false]);

        $this->assertDatabaseHas('access_logs', [
            'result' => 'invalid_token_format',
        ]);
    }

    /**
     * Test that non-existent token is rejected.
     */
    public function test_nonexistent_token_rejected(): void
    {
        $fakeToken = bin2hex(random_bytes(32));

        $response = $this->postJson('/api/access/validate', [
            'token' => $fakeToken,
            'room_id' => $this->room->id,
        ]);

        $response->assertForbidden();
        $response->assertJson(['allowed' => false, 'reason' => 'invalid_token']);

        $this->assertDatabaseHas('access_logs', [
            'result' => 'invalid_token',
        ]);
    }

    /**
     * Test that valid token is accepted.
     */
    public function test_valid_token_accepted(): void
    {
        $response = $this->postJson('/api/access/validate', [
            'token' => $this->reservation->access_token,
            'room_id' => $this->room->id,
        ]);

        $response->assertOk();
        $response->assertJson(['allowed' => true]);

        $this->assertDatabaseHas('access_logs', [
            'result' => 'allowed',
            'reservation_id' => $this->reservation->id,
        ]);

        // Check that used_at was updated
        $this->assertNotNull($this->reservation->refresh()->used_at);
    }

    /**
     * Test that expired token is rejected.
     */
    public function test_expired_token_rejected(): void
    {
        $this->reservation->update([
            'token_expires_at' => now()->subMinutes(1),
        ]);

        $response = $this->postJson('/api/access/validate', [
            'token' => $this->reservation->access_token,
            'room_id' => $this->reservation->room_id,
        ]);

        $response->assertForbidden();
        $response->assertJson(['allowed' => false, 'reason' => 'expired_or_outside_window']);
    }

    /**
     * Test that token outside valid window is rejected.
     */
    public function test_token_outside_valid_window_rejected(): void
    {
        $this->reservation->update([
            'token_valid_from' => now()->addMinutes(10),
        ]);

        $response = $this->postJson('/api/access/validate', [
            'token' => $this->reservation->access_token,
            'room_id' => $this->reservation->room_id,
        ]);

        $response->assertForbidden();
        $response->assertJson(['allowed' => false, 'reason' => 'expired_or_outside_window']);
    }

    /**
     * Test that access logs are created for all attempts.
     */
    public function test_access_logs_created(): void
    {
        $this->postJson('/api/access/validate', [
            'token' => 'invalid_token_format',
            'room_id' => $this->room->id,
        ]);

        $this->assertDatabaseHas('access_logs', [
            'result' => 'invalid_token_format',
        ]);

        $this->postJson('/api/access/validate', [
            'token' => $this->reservation->access_token,
            'room_id' => $this->room->id,
        ]);

        $this->assertDatabaseHas('access_logs', [
            'result' => 'allowed',
        ]);

        $this->assertCount(2, AccessLog::all());
    }

    /**
     * Test rate limiting on access validation.
     */
    public function test_rate_limiting_on_access_validation(): void
    {
        $token = bin2hex(random_bytes(32));

        // Make 61 requests (limit is 60 per minute)
        for ($i = 0; $i < 61; $i++) {
            $response = $this->postJson('/api/access/validate', [
                'token' => $token,
                'room_id' => $this->room->id,
            ]);

            if ($i < 60) {
                $this->assertNotSame(429, $response->status());
            }
        }

        // 61st request should be rate limited
        $response = $this->postJson('/api/access/validate', [
            'token' => $token,
            'room_id' => $this->room->id,
        ]);

        $this->assertEquals(429, $response->status());
    }
}
