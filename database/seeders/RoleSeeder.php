<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Enums\Role as RoleEnum;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (RoleEnum::casesToSelect() as $role) {
            Role::firstOrCreate(["name" => $role["label"],
                "guard_name" => "web",
            ]);
        }
    }
}
