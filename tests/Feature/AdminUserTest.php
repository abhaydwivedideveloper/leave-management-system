<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_employee_with_password(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $manager = User::factory()->create(['role' => UserRole::Manager]);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'New Employee',
            'email' => 'new.employee@leavems.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => UserRole::Employee->value,
            'department' => 'Engineering',
            'manager_id' => $manager->id,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'new.employee@leavems.test',
            'role' => UserRole::Employee->value,
            'manager_id' => $manager->id,
        ]);
    }

    public function test_admin_create_requires_password_confirmation(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $manager = User::factory()->create(['role' => UserRole::Manager]);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'New Employee',
            'email' => 'new.employee@leavems.test',
            'password' => 'password123',
            'password_confirmation' => 'different',
            'role' => UserRole::Employee->value,
            'department' => 'Engineering',
            'manager_id' => $manager->id,
        ]);

        $response->assertSessionHasErrors('password');
    }
}
