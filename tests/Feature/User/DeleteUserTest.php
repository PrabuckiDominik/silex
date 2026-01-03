<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);
beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin']);
});
test('it deletes a user by id by user', function () {
    $user = User::create([
        'name' => 'Lux', 'email' => 'lux@lux.com', 'password' => 'password',
        ]);
    Sanctum::actingAs($user, ['*']);

    $response = $this->deleteJson("/api/users/{$user->id}");

    $response->assertNoContent();

    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
    ]);
});
test('it deletes a user by id by admin', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin, ['*']);
    $user = User::create([
        'name' => 'Lux', 'email' => 'lux@lux.com', 'password' => 'password',
    ]);

    $response = $this->deleteJson("/api/users/{$user->id}");

    $response->assertNoContent();

    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
    ]);
});
test('it fails to delete a user by id by other user', function () {
    $fakeAdmin = User::factory()->create();
    Sanctum::actingAs($fakeAdmin, ['*']);
    $user = User::create([
        'name' => 'Lux', 'email' => 'lux@lux.com', 'password' => 'password',
    ]);

    $response = $this->deleteJson("/api/users/{$user->id}");

    $response->assertStatus(403);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
    ]);
});


