<?php

declare(strict_types=1);

namespace App\Enums;

enum Role: string
{
    case User = "user";
    case Administrator = "admin";

    public static function casesToSelect(): array
    {
        $cases = collect(Role::cases());

        return $cases->map(
            fn(Role $enum): array => [
                "label" => $enum->label(),
                "value" => $enum->value,
            ],
        )->toArray();
    }

    public function label(): string
    {
        return $this->value;
    }
}
