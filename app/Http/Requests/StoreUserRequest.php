<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            "name" => ["required", "string", "max:225"],
            "email" => ["required", "string", "email", "max:225", "unique:users,email"],
            "password" => ["required", "confirmed", Password::min(8)],
        ];
    }
}
