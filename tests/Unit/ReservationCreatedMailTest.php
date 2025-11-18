<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Mail\ReservationCreatedMail;
use App\Models\Reservation;
use App\Models\Room;
use Carbon\Carbon;

class ReservationCreatedMailTest extends TestCase
{
    public function test_content_strings()
    {
        $reservation = new Reservation();
        $reservation->start_at = Carbon::parse('2025-10-26 07:11:00');
        $reservation->end_at = Carbon::parse('2025-10-26 08:11:00');
        $reservation->access_token = 'test-token';

        $room = new Room();
        $room->name = 'Test Room';
        $reservation->setRelation('room', $room);

        $mail = new ReservationCreatedMail($reservation);
        $content = $mail->content();

        $textPart = $content->text ?? null;
        $this->assertTrue(
            is_string($textPart) || is_callable($textPart) || (is_object($textPart) && method_exists($textPart, 'toHtml')),
            'text part should be a string, Htmlable, or a Closure that returns one'
        );

        $this->assertIsString($content->htmlString ?? null, 'htmlString should be a string');
    }
}
