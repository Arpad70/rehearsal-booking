<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationCreatedMail;
use App\Models\User;
use App\Models\Room;
use Carbon\Carbon;

class ReservationMailIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_reservation_mail_sent_with_qr_attachment()
    {
        Mail::fake();
        \Illuminate\Support\Facades\Log::info('Test: Mail facade root', ['class' => is_object(Mail::getFacadeRoot()) ? get_class(Mail::getFacadeRoot()) : null]);

        $user = User::factory()->create();
        $room = Room::factory()->create();

        $this->actingAs($user);

        $start = Carbon::now()->addMinutes(10)->format('Y-m-d H:i');
        $end = Carbon::now()->addMinutes(70)->format('Y-m-d H:i');

        $response = $this->post(route('reservations.store'), [
            'room_id' => $room->id,
            'start_at' => $start,
            'end_at' => $end,
        ]);

        \Illuminate\Support\Facades\Log::info('Test: response status', ['status' => $response->status()]);
        \Illuminate\Support\Facades\Log::info('Test: session errors', ['errors' => $response->original?->getSession()?->get('errors')?->all() ?? null]);
        $response->assertRedirect();

            // Debug: inspect MailFake internal mailables array
            try {
                $fake = Mail::getFacadeRoot();
                $ref = new \ReflectionClass($fake);
                $prop = $ref->getProperty('mailables');
                $prop->setAccessible(true);
                \Illuminate\Support\Facades\Log::info('Test: internal mailables count', ['count' => count($prop->getValue($fake))]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Test: failed to inspect MailFake', ['err' => $e->getMessage()]);
            }

            Mail::assertSent(ReservationCreatedMail::class, function ($mail) use ($user) {
            // Ensure mail is addressed to the correct user
            $this->assertTrue($mail->hasTo($user->email));

            // The Mailable attachments are available via attachments() method
            $attachments = $mail->attachments();
            $this->assertIsArray($attachments);
            $this->assertNotEmpty($attachments, 'Expected at least one attachment');

            // Look for qr.png
            $names = array_map(fn($a) => $a->as ?? null, $attachments);
            $this->assertContains('qr.png', $names);

            // Validate attachment content and mime
            foreach ($attachments as $attachment) {
                $parts = $attachment->attachWith(
                    fn ($path) => [$path],
                    fn ($data) => [$data(), ['as' => $attachment->as, 'mime' => $attachment->mime]]
                );

                // data and options returned by our dataStrategy
                $data = $parts[0] ?? null;
                $options = $parts[1] ?? [];

                // mime should be image/png for our QR attachment
                if (($options['as'] ?? null) === 'qr.png' || ($attachment->as ?? null) === 'qr.png') {
                    $this->assertEquals('image/png', $options['mime'] ?? $attachment->mime);
                    $this->assertNotEmpty($data, 'QR attachment data should not be empty');
                    $this->assertGreaterThan(0, strlen($data));
                }
            }

            return true;
        });
    }
}
