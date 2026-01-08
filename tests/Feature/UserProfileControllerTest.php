<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_can_view_their_profile()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJson([
                'message' => __('profile.retrieved'),
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_can_update_their_profile()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $newData = [
            'name' => 'Updated Name',
            'email' => 'new-email@example.com',
        ];

        $response = $this->putJson('/api/profile', $newData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => __('profile.updated'),
                'data' => [
                    'id' => $user->id,
                    'name' => 'Updated Name',
                    'email' => $user->email,
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function guest_cannot_access_profile()
    {
        $response = $this->getJson('/api/profile');
        $response->assertStatus(401);

        $response = $this->putJson('/api/profile', [
            'name' => 'Hack',
            'email' => 'hack@example.com',
        ]);
        $response->assertStatus(401);
    }
}
