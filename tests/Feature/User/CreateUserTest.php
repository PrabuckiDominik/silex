<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it creates a user with valid data', function () {
    $data = [
        'name' => 'Jan Kowalski',
        'email' => 'jan@example.com',
        'password' => 'secret123',
    ];

    $response = $this->postJson('/api/register', $data);

    $response->assertCreated()
        ->assertJsonFragment(['email' => 'jan@example.com']);

    $this->assertDatabaseHas('users', ['email' => 'jan@example.com']);
});

test('it fails when required fields are missing', function () {
    $response = $this->postJson('/api/register', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

test('it fails when email is invalid', function () {
    $data = [
        'name' => 'Niepoprawny Email',
        'email' => 'not-an-email',
        'password' => 'secret123',
    ];

    $response = $this->postJson('/api/register', $data);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('it fails when password is too short', function () {
    $data = [
        'name' => 'Short Pass',
        'email' => 'short@example.com',
        'password' => '123',
    ];

    $response = $this->postJson('/api/register', $data);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});
