<?php

namespace Database\Seeders;

use App\Enums\LeaveStatus;
use App\Enums\UserRole;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@leavems.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
            'department' => 'HR',
            'email_verified_at' => now(),
        ]);

        $manager = User::create([
            'name' => 'Jane Manager',
            'email' => 'manager@leavems.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Manager,
            'department' => 'Engineering',
            'email_verified_at' => now(),
        ]);

        $manager2 = User::create([
            'name' => 'Bob Manager',
            'email' => 'manager2@leavems.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Manager,
            'department' => 'Marketing',
            'email_verified_at' => now(),
        ]);

        $employee = User::create([
            'name' => 'John Employee',
            'email' => 'employee@leavems.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Employee,
            'department' => 'Engineering',
            'manager_id' => $manager->id,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Sarah Employee',
            'email' => 'sarah@leavems.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Employee,
            'department' => 'Marketing',
            'manager_id' => $manager2->id,
            'email_verified_at' => now(),
        ]);

        $sick = LeaveType::create([
            'name' => 'Sick Leave',
            'slug' => 'sick',
            'description' => 'Medical or health-related absence',
            'days_per_year' => 10,
            'is_active' => true,
        ]);

        $casual = LeaveType::create([
            'name' => 'Casual Leave',
            'slug' => 'casual',
            'description' => 'Short personal leave',
            'days_per_year' => 6,
            'is_active' => true,
        ]);

        $annual = LeaveType::create([
            'name' => 'Annual Leave',
            'slug' => 'annual',
            'description' => 'Planned vacation time',
            'days_per_year' => 12,
            'is_active' => true,
        ]);

        LeaveApplication::create([
            'user_id' => $employee->id,
            'leave_type_id' => $annual->id,
            'start_date' => now()->addDays(14)->toDateString(),
            'end_date' => now()->addDays(16)->toDateString(),
            'days_count' => 3,
            'reason' => 'Family vacation planned several months ago.',
            'status' => LeaveStatus::Pending,
        ]);

        LeaveApplication::create([
            'user_id' => $employee->id,
            'leave_type_id' => $sick->id,
            'start_date' => now()->subDays(30)->toDateString(),
            'end_date' => now()->subDays(28)->toDateString(),
            'days_count' => 3,
            'reason' => 'Recovered from flu, doctor advised rest.',
            'status' => LeaveStatus::Approved,
            'reviewed_by' => $manager->id,
            'reviewed_at' => now()->subDays(31),
            'manager_comment' => 'Approved. Get well soon.',
        ]);

        $this->command?->info('Seeded users: admin@leavems.test, manager@leavems.test, employee@leavems.test (password: password)');
    }
}
