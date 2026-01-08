<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class VerifyEmailApiTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_can_verify_email_via_api()
    {
        Event::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $url = URL::signedRoute(
            'verification.verify',
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->getJson($url);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Email successfully verified.',
            ]);

        $this->assertTrue($user->fresh()->hasVerifiedEmail());

        Event::assertDispatched(Verified::class);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function already_verified_email_returns_200_without_event()
    {
        Event::fake();

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $url = URL::signedRoute(
            'verification.verify',
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->getJson($url);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Email already verified.',
            ]);

        Event::assertNotDispatched(Verified::class);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function invalid_verification_link_returns_403()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $url = URL::signedRoute(
            'verification.verify',
            ['id' => $user->id, 'hash' => 'invalidhash']
        );

        $response = $this->actingAs($user)->getJson($url);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Invalid verification link.',
            ]);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }
}
