<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_can_login_with_verified_email()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'token', 'user']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function login_fails_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Nieprawidłowe dane logowania.']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function login_fails_if_email_not_verified()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'email_verified_at' => null,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Adres email nie został zweryfikowany.']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function authenticated_user_can_logout()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Wylogowano pomyślnie.']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function guest_cannot_logout()
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_can_register()
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'user']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function registration_fails_with_missing_fields()
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'errors']);
    }
}
