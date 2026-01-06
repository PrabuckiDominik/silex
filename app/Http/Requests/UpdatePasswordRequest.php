<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            "current_password" => ["required", "current_password"],
            "new_password" => ["required", "confirmed", Password::min(8)],
        ];
    }

    public function getNewPassword(): string
    {
        return $this->input("new_password");
    }
}
