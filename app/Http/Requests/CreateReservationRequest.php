<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $minDuration = config('reservations.min_duration_minutes', 15);
        
        return [
            'room_id' => ['required','exists:rooms,id'],
            'start_at' => ['required','date','after:now'],
            'end_at' => [
                'required',
                'date',
                "after_or_equal:start_at",
                function ($attribute, $value, $fail) use ($minDuration) {
                    // Calculate minutes between start and end
                    $startTime = \Carbon\Carbon::parse($this->start_at);
                    $endTime = \Carbon\Carbon::parse($value);
                    $diffMinutes = $endTime->diffInMinutes($startTime);
                    
                    if ($diffMinutes < $minDuration) {
                        $fail("Minimální délka rezervace je $minDuration minut.");
                    }
                },
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'room_id.required' => 'Vyberte prosím místnost',
            'room_id.exists' => 'Vybraná místnost neexistuje',
            'start_at.required' => 'Zadejte prosím začátek rezervace',
            'start_at.date' => 'Neplatné datum začátku rezervace',
            'start_at.after' => 'Začátek rezervace musí být v budoucnosti',
            'end_at.required' => 'Zadejte prosím konec rezervace',
            'end_at.date' => 'Neplatné datum konce rezervace',
            'end_at.after_or_equal' => 'Konec rezervace musí být po začátku rezervace nebo ve stejný čas',
        ];
    }
}
