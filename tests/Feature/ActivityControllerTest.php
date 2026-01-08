<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Activity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class ActivityControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_activity()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/activities/add', [
            'name' => 'Morning Run',
            'type' => 'run',
            'note' => 'Felt good',
            'photo_path' => null,
            'distance' => 5000,
            'time' => 1500,
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'Morning Run',
                'type' => 'run',
            ]);

        $this->assertDatabaseHas('activities', [
            'user_id' => $user->id,
            'name' => 'Morning Run',
        ]);
    }

    public function test_user_can_update_own_activity()
    {
        $user = User::factory()->create();

        $activity = Activity::factory()->create([
            'user_id' => $user->id,
            'name' => 'Old Name',
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/activities/{$activity->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertOk()
            ->assertJsonFragment([
                'name' => 'Updated Name',
            ]);
    }

    public function test_user_cannot_update_other_users_activity()
    {
        $activity = Activity::factory()->create();
        $attacker = User::factory()->create();

        Sanctum::actingAs($attacker);

        $this->putJson("/api/activities/{$activity->id}", [
            'name' => 'Hacked',
        ])->assertStatus(403);
    }

    public function test_user_can_delete_own_activity()
    {
        $user = User::factory()->create();

        $activity = Activity::factory()->create([
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $this->deleteJson("/api/activities/{$activity->id}")
            ->assertOk()
            ->assertJson([
                'message' => 'Activity deleted successfully',
            ]);

        $this->assertDatabaseMissing('activities', [
            'id' => $activity->id,
        ]);
    }

    public function test_user_cannot_delete_other_users_activity()
    {
        $activity = Activity::factory()->create();
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->deleteJson("/api/activities/{$activity->id}")
            ->assertStatus(403);
    }

    public function test_user_can_list_their_activities()
    {
        $user = User::factory()->create();

        Activity::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        Activity::factory()->count(2)->create(); // other users

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/activities');

        $response->assertOk()
            ->assertJsonCount(3);
    }

    public function test_user_can_view_own_activity()
    {
        $user = User::factory()->create();

        $activity = Activity::factory()->create([
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $this->getJson("/api/activities/{$activity->id}")
            ->assertOk()
            ->assertJsonFragment([
                'id' => $activity->id,
            ]);
    }

    public function test_user_cannot_view_other_users_activity()
    {
        $activity = Activity::factory()->create();
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson("/api/activities/{$activity->id}")
            ->assertStatus(403);
    }

    public function test_stats_endpoint_returns_expected_structure()
    {
        $user = User::factory()->create();

        Activity::factory()->create([
            'user_id' => $user->id,
            'type' => 'run',
            'distance' => 5000,
            'time' => 1800,
            'created_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/activities/stats');

        $response->assertOk()
            ->assertJsonStructure([
                'overall' => [
                    'activities',
                    'distance',
                    'time_seconds',
                    'time_human',
                    'types',
                ],
                'monthly',
            ]);
    }

    public function test_user_can_view_activity_photo()
    {
        Storage::fake('private');

        $user = User::factory()->create();

        $activity = Activity::factory()->create([
            'user_id' => $user->id,
            'photo_path' => 'photos/test.jpg',
        ]);

        Storage::disk('private')->put('photos/test.jpg', 'fake-image');

        Sanctum::actingAs($user);

        $this->get("/api/activities/{$activity->id}/photo")
            ->assertOk();
    }

    public function test_activity_photo_returns_404_when_missing()
    {
        Storage::fake('private');

        $user = User::factory()->create();

        $activity = Activity::factory()->create([
            'user_id' => $user->id,
            'photo_path' => 'photos/missing.jpg',
        ]);

        Sanctum::actingAs($user);

        $this->get("/api/activities/{$activity->id}/photo")
            ->assertStatus(404);
    }
}
