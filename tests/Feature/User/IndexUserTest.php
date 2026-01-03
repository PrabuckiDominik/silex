<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);
beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin']);
});
test('it returns all users', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin, ['*']);
    User::create(['name' => 'Jan', 'email' => 'jan@example.com', 'password' => 'secret123',]);
    User::create(['name' => 'Janina', 'email' => 'janina@example.com', 'password' => 'secret123',]);

    $response = $this->getJson('/api/users');

    $response->assertOk()
        ->assertJsonFragment(['name' => 'Jan'])
        ->assertJsonFragment(['name' => 'Janina']);
});

test('it fails to return all users by user', function () {
    $fakeAdmin = User::factory()->create();
    Sanctum::actingAs($fakeAdmin, ['*']);
    User::create(['name' => 'Jan', 'email' => 'jan@example.com', 'password' => 'secret123',]);
    User::create(['name' => 'Janina', 'email' => 'janina@example.com', 'password' => 'secret123',]);

    $response = $this->getJson('/api/users');

    $response->assertStatus(403);
});
