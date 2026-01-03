<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);
beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin']);
});
test('it shows a champion by id', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin, ['*']);
    $user = User::create([
        'name' => 'Lux', 'email' => 'lux@lux.com', 'password' => 'password',
    ]);

    $response = $this->getJson("/api/users/{$user->id}");

    $response->assertOk()
        ->assertJsonFragment([
            'name' => 'Lux',
            'email' => 'lux@lux.com',
        ]);
});

