<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\DB;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Gate::before(function ($user, $ability) {
            return $this->userHasAdminRole($user) ? true : null;
        });

        if (!DB::table('roles')->where('name', 'admin')->exists()) {
            DB::table('roles')->insert(['name' => 'admin']);
        }
    }

    protected function createAdminUser(): User
    {
        $admin = User::factory()->create();
        $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');

        DB::table('role_user')->insert([
            'user_id' => $admin->id,
            'role_id' => $adminRoleId,
        ]);

        return $admin;
    }

    protected function userHasAdminRole(User $user): bool
    {
        $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');
        return DB::table('role_user')
            ->where('user_id', $user->id)
            ->where('role_id', $adminRoleId)
            ->exists();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function non_admin_cannot_access_user_routes()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/admin/users')->assertStatus(403);
        $this->getJson("/api/admin/users/{$otherUser->id}")->assertStatus(403);
        $this->putJson("/api/admin/users/{$otherUser->id}", ['name' => 'Hacker'])->assertStatus(403);
        $this->deleteJson("/api/admin/users/{$otherUser->id}")->assertStatus(403);
    }
}
