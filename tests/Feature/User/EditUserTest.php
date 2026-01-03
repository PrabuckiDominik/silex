<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);
beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin']);
});
test('it updates a user with valid data by admin', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin, ['*']);
    $user = User::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
    ]);

    $data = [
        'name' => 'John Updated',
        'email' => 'john.updated@example.com',
    ];

    $response = $this->putJson("/api/users/{$user->id}", $data);

    $response->assertStatus(201)
        ->assertJsonFragment(['name' => 'John Updated']);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'John Updated',
        'email' => 'john.updated@example.com',
    ]);
});

test('it updates a user with valid data by user', function () {
    $user = User::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
    ]);
    Sanctum::actingAs($user, ['*']);
    $data = [
        'name' => 'John Updated',
        'email' => 'john.updated@example.com',
    ];

    $response = $this->putJson("/api/users/{$user->id}", $data);

    $response->assertStatus(201)
        ->assertJsonFragment(['name' => 'John Updated']);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'John Updated',
        'email' => 'john.updated@example.com',
    ]);
});

test('it fails to update when email is invalid', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin, ['*']);
    $user = User::create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => bcrypt('password'),
    ]);

    $data = [
        'email' => 'not-an-email',
    ];

    $response = $this->putJson("/api/users/{$user->id}", $data);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('it fails to update a user with valid data by other user', function () {
    $fakeAdmin = User::factory()->create();
    Sanctum::actingAs($fakeAdmin, ['*']);
    $user = User::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
    ]);

    $data = [
        'name' => 'John Updated',
        'email' => 'john.updated@example.com',
    ];

    $response = $this->putJson("/api/users/{$user->id}", $data);

    $response->assertStatus(403);
});
