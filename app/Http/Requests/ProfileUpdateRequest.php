<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail) {
                    /** @var User $user */
                    $user = $this->user();
                    $existingUser = User::where('email', $value)->where('id', '!=', $user->getKey())->first();
                    if ($existingUser) {
                        $fail("The {$attribute} has already been taken.");
                    }
                },
            ],
        ];
    }
}
