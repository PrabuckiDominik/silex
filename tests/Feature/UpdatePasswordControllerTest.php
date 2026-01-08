<?php

namespace Tests\Feature;

use App\Models\User;
use App\Http\Actions\ThrottleAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Tests\TestCase;

class UpdatePasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $route = '/api/auth/change-password';

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_can_update_password_successfully()
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        Sanctum::actingAs($user);

        // Mock ThrottleAction to skip throttling
        $throttleMock = Mockery::mock(ThrottleAction::class);
        $throttleMock->shouldReceive('handle')->once();
        $this->app->instance(ThrottleAction::class, $throttleMock);

        $response = $this->putJson($this->route, [
            'current_password' => 'old-password',          // ✅ must include
            'new_password' => 'new-password',
            'new_password_confirmation' => 'new-password',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => __('passwords.updated_successfully'),
            ]);

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_cannot_update_password_to_same_as_current()
    {
        $user = User::factory()->create([
            'password' => Hash::make('same-password'),
        ]);

        Sanctum::actingAs($user);

        $throttleMock = Mockery::mock(ThrottleAction::class);
        $throttleMock->shouldReceive('handle')->once();
        $this->app->instance(ThrottleAction::class, $throttleMock);

        $response = $this->putJson($this->route, [
            'current_password' => 'same-password',        // ✅ must include
            'new_password' => 'same-password',
            'new_password_confirmation' => 'same-password',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => __('passwords.same_as_current'),
            ]);

        // Password should remain unchanged
        $this->assertTrue(Hash::check('same-password', $user->fresh()->password));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function guest_cannot_update_password()
    {
        $response = $this->putJson($this->route, [
            'current_password' => 'anything',
            'new_password' => 'hack-password',
            'new_password_confirmation' => 'hack-password',
        ]);

        $response->assertStatus(401);
    }
}
