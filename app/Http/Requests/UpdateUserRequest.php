<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            "name" => ["sometimes", "string", "max:225"],
            "email" => [
                "sometimes",
                "email",
                "max:225",
                Rule::unique("users", "email")->ignore($this->route("user")?->id),
            ],
        ];
    }
}
