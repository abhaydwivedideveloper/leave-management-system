<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_cannot_access_admin_dashboard(): void
    {
        $employee = User::factory()->create(['role' => UserRole::Employee]);

        $response = $this->actingAs($employee)->get(route('admin.dashboard'));

        $response->assertForbidden();
    }

    public function test_manager_cannot_access_admin_users(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager]);

        $response = $this->actingAs($manager)->get(route('admin.users.index'));

        $response->assertForbidden();
    }
}
