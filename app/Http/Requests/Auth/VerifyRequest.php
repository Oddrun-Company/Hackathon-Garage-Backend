<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'phone' => ['required', 'exists:users,phone_number'],
            'code' => ['required', 'integer', 'size:4'],
        ];
    }
}
