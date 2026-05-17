<?php

namespace Tests\Feature;

use App\Enums\LeaveStatus;
use App\Enums\UserRole;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\LeaveNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_submit_leave_application(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager]);
        $employee = User::factory()->create([
            'role' => UserRole::Employee,
            'manager_id' => $manager->id,
        ]);
        $leaveType = LeaveType::create([
            'name' => 'Annual',
            'slug' => 'annual',
            'days_per_year' => 12,
            'is_active' => true,
        ]);

        $response = $this->actingAs($employee)->post(route('employee.leaves.store'), [
            'leave_type_id' => $leaveType->id,
            'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addWeek()->addDays(2)->toDateString(),
            'reason' => 'Family vacation planned in advance.',
        ]);

        $response->assertRedirect(route('employee.dashboard'));
        $this->assertDatabaseHas('leave_applications', [
            'user_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'status' => LeaveStatus::Pending->value,
        ]);
    }

    public function test_manager_can_approve_team_leave_application(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager]);
        $employee = User::factory()->create([
            'role' => UserRole::Employee,
            'manager_id' => $manager->id,
        ]);
        $leaveType = LeaveType::create([
            'name' => 'Annual',
            'slug' => 'annual',
            'days_per_year' => 12,
            'is_active' => true,
        ]);
        $application = LeaveApplication::create([
            'user_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addWeek()->addDays(2)->toDateString(),
            'days_count' => 3,
            'reason' => 'Family event.',
            'status' => LeaveStatus::Pending,
        ]);

        $response = $this->actingAs($manager)->patch(route('manager.leaves.review', $application), [
            'status' => LeaveStatus::Approved->value,
            'manager_comment' => 'Approved. Enjoy your time off.',
        ]);

        $response->assertRedirect(route('manager.dashboard'));
        $this->assertDatabaseHas('leave_applications', [
            'id' => $application->id,
            'status' => LeaveStatus::Approved->value,
            'reviewed_by' => $manager->id,
        ]);
    }

    public function test_manager_dashboard_shows_approve_and_reject_buttons(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager]);
        $employee = User::factory()->create([
            'role' => UserRole::Employee,
            'manager_id' => $manager->id,
        ]);
        $leaveType = LeaveType::create([
            'name' => 'Annual',
            'slug' => 'annual',
            'days_per_year' => 12,
            'is_active' => true,
        ]);
        LeaveApplication::create([
            'user_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addWeek()->addDays(2)->toDateString(),
            'days_count' => 3,
            'reason' => 'Need time off.',
            'status' => LeaveStatus::Pending,
        ]);

        $response = $this->actingAs($manager)->get(route('manager.dashboard'));

        $response->assertOk();
        $response->assertSee('Approve');
        $response->assertSee('Reject');
        $response->assertSee('Need time off.');
    }

    public function test_manager_approval_succeeds_when_email_notification_fails(): void
    {
        $manager = User::factory()->create(['role' => UserRole::Manager]);
        $employee = User::factory()->create([
            'role' => UserRole::Employee,
            'manager_id' => $manager->id,
        ]);
        $leaveType = LeaveType::create([
            'name' => 'Annual',
            'slug' => 'annual',
            'days_per_year' => 12,
            'is_active' => true,
        ]);
        $application = LeaveApplication::create([
            'user_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addWeek()->addDays(2)->toDateString(),
            'days_count' => 3,
            'reason' => 'Family event.',
            'status' => LeaveStatus::Pending,
        ]);

        $this->mock(LeaveNotificationService::class, function ($mock) {
            $mock->shouldReceive('notifyStatusChanged')->once()->andReturn(false);
        });

        $response = $this->actingAs($manager)->patch(route('manager.leaves.review', $application), [
            'status' => LeaveStatus::Approved->value,
        ]);

        $response->assertRedirect(route('manager.dashboard'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('leave_applications', [
            'id' => $application->id,
            'status' => LeaveStatus::Approved->value,
        ]);
    }

    public function test_employee_cannot_apply_with_past_start_date(): void
    {
        $employee = User::factory()->create(['role' => UserRole::Employee]);
        $leaveType = LeaveType::create([
            'name' => 'Annual',
            'slug' => 'annual',
            'days_per_year' => 12,
            'is_active' => true,
        ]);

        $response = $this->actingAs($employee)->post(route('employee.leaves.store'), [
            'leave_type_id' => $leaveType->id,
            'start_date' => now()->subDays(3)->toDateString(),
            'end_date' => now()->subDay()->toDateString(),
            'reason' => 'Attempting to backdate leave request.',
        ]);

        $response->assertSessionHasErrors('start_date');
    }

    public function test_employee_cannot_apply_overlapping_leave(): void
    {
        $employee = User::factory()->create(['role' => UserRole::Employee]);
        $leaveType = LeaveType::create([
            'name' => 'Annual',
            'slug' => 'annual',
            'days_per_year' => 12,
            'is_active' => true,
        ]);

        LeaveApplication::create([
            'user_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addWeek()->addDays(2)->toDateString(),
            'days_count' => 3,
            'reason' => 'Already pending leave.',
            'status' => LeaveStatus::Pending,
        ]);

        $response = $this->actingAs($employee)->post(route('employee.leaves.store'), [
            'leave_type_id' => $leaveType->id,
            'start_date' => now()->addWeek()->addDay()->toDateString(),
            'end_date' => now()->addWeek()->addDays(3)->toDateString(),
            'reason' => 'Overlapping leave dates with pending request.',
        ]);

        $response->assertSessionHasErrors('start_date');
    }

    public function test_employee_cannot_exceed_leave_balance(): void
    {
        $employee = User::factory()->create(['role' => UserRole::Employee]);
        $leaveType = LeaveType::create([
            'name' => 'Annual',
            'slug' => 'annual',
            'days_per_year' => 5,
            'is_active' => true,
        ]);

        $response = $this->actingAs($employee)->post(route('employee.leaves.store'), [
            'leave_type_id' => $leaveType->id,
            'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addWeek()->addDays(9)->toDateString(),
            'reason' => 'Requesting more days than entitlement allows.',
        ]);

        $response->assertSessionHasErrors('start_date');
    }

    public function test_employee_can_cancel_pending_application(): void
    {
        $employee = User::factory()->create(['role' => UserRole::Employee]);
        $leaveType = LeaveType::create([
            'name' => 'Annual',
            'slug' => 'annual',
            'days_per_year' => 12,
            'is_active' => true,
        ]);
        $application = LeaveApplication::create([
            'user_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addWeek()->addDays(2)->toDateString(),
            'days_count' => 3,
            'reason' => 'Changed plans for vacation.',
            'status' => LeaveStatus::Pending,
        ]);

        $response = $this->actingAs($employee)->delete(route('employee.leaves.destroy', $application));

        $response->assertRedirect(route('employee.dashboard'));
        $this->assertDatabaseMissing('leave_applications', ['id' => $application->id]);
    }
}
