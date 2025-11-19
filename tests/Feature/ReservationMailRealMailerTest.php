<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Room;
use Carbon\Carbon;

class ReservationMailRealMailerTest extends TestCase
{
    use RefreshDatabase;

    public function test_sending_mail_with_log_mailer_writes_log_including_qr()
    {
        // Use the log mailer to simulate real sending via configured transport
        config(['mail.default' => 'log']);

        // Ensure log file is empty before the request
        $logPath = storage_path('logs/laravel.log');
        if (file_exists($logPath)) {
            @file_put_contents($logPath, '');
        }

        $user = User::factory()->create();
        $room = Room::factory()->create();

        $this->actingAs($user);

        $start = Carbon::now()->addMinutes(10)->format('Y-m-d H:i');
        $end = Carbon::now()->addMinutes(100)->format('Y-m-d H:i'); // 90 minutes duration (> 60 min minimum)

        $response = $this->post(route('reservations.store'), [
            'room_id' => $room->id,
            'start_at' => $start,
            'end_at' => $end,
        ]);

        $response->assertRedirect();

        // Read the log and assert it contains evidence the mail was rendered/sent
        $log = file_exists($logPath) ? file_get_contents($logPath) : '';

        $this->assertStringContainsString('Potvrzení rezervace', $log);
        $this->assertStringContainsString('Content-Type: image/png', $log);
    }
}
