<?php

namespace App\Services;

use App\Enums\LeaveStatus;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class LeaveApplicationService
{
    public function calculateDaysCount(string $startDate, string $endDate): int
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();

        return $start->diffInDays($end) + 1;
    }

    /**
     * @throws ValidationException
     */
    public function validateApplication(User $user, array $data, ?LeaveApplication $existing = null): array
    {
        $validator = Validator::make($data, [
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'start_date.after_or_equal' => 'Leave cannot start in the past.',
            'end_date.after_or_equal' => 'End date must be on or after the start date.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = $validator->validated();
        $daysCount = $this->calculateDaysCount($validated['start_date'], $validated['end_date']);

        $leaveType = LeaveType::findOrFail($validated['leave_type_id']);

        if (! $leaveType->is_active) {
            throw ValidationException::withMessages([
                'leave_type_id' => 'This leave type is not available.',
            ]);
        }

        $this->assertNoOverlappingLeave(
            $user,
            $validated['start_date'],
            $validated['end_date'],
            $existing?->id
        );

        $this->assertSufficientBalance($user, $leaveType, $daysCount, $existing?->id);

        $validated['days_count'] = $daysCount;

        return $validated;
    }

    /**
     * @throws ValidationException
     */
    public function validateApproval(LeaveApplication $application): void
    {
        $application->loadMissing(['user', 'leaveType']);

        $this->assertNoOverlappingLeave(
            $application->user,
            $application->start_date->toDateString(),
            $application->end_date->toDateString(),
            $application->id
        );

        $this->assertSufficientBalance(
            $application->user,
            $application->leaveType,
            $application->days_count,
            $application->id
        );
    }

    public function assertNoOverlappingLeave(
        User $user,
        string $startDate,
        string $endDate,
        ?int $excludeId = null
    ): void {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $overlap = LeaveApplication::query()
            ->where('user_id', $user->id)
            ->whereIn('status', [LeaveStatus::Approved, LeaveStatus::Pending])
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                    });
            })
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'start_date' => 'These dates overlap with an existing leave application.',
            ]);
        }
    }

    public function assertSufficientBalance(
        User $user,
        LeaveType $leaveType,
        int $requestedDays,
        ?int $excludeApplicationId = null
    ): void {
        if ($leaveType->days_per_year <= 0) {
            return;
        }

        $year = (int) now()->year;
        $reserved = $this->getReservedDaysForType($user, $leaveType->id, $year, $excludeApplicationId);

        if (($reserved + $requestedDays) > $leaveType->days_per_year) {
            $remaining = max(0, $leaveType->days_per_year - $reserved);

            throw ValidationException::withMessages([
                'start_date' => "Insufficient leave balance. You have {$remaining} day(s) remaining for {$leaveType->name}.",
            ]);
        }
    }

    public function getUsedDaysForType(User $user, int $leaveTypeId, int $year, ?int $excludeId = null): int
    {
        return (int) LeaveApplication::query()
            ->where('user_id', $user->id)
            ->where('leave_type_id', $leaveTypeId)
            ->where('status', LeaveStatus::Approved)
            ->forYear($year)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->sum('days_count');
    }

    public function getPendingDaysForType(User $user, int $leaveTypeId, int $year, ?int $excludeId = null): int
    {
        return (int) LeaveApplication::query()
            ->where('user_id', $user->id)
            ->where('leave_type_id', $leaveTypeId)
            ->where('status', LeaveStatus::Pending)
            ->forYear($year)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->sum('days_count');
    }

    public function getReservedDaysForType(User $user, int $leaveTypeId, int $year, ?int $excludeId = null): int
    {
        return $this->getUsedDaysForType($user, $leaveTypeId, $year, $excludeId)
            + $this->getPendingDaysForType($user, $leaveTypeId, $year, $excludeId);
    }

    public function getEntitlementSummary(User $user, int $year): array
    {
        $types = LeaveType::active()->get();
        $summary = [];

        foreach ($types as $type) {
            $used = $this->getUsedDaysForType($user, $type->id, $year);
            $pending = $this->getPendingDaysForType($user, $type->id, $year);

            $summary[] = [
                'leave_type' => $type,
                'entitlement' => $type->days_per_year,
                'used' => $used,
                'pending' => $pending,
                'remaining' => max(0, $type->days_per_year - $used - $pending),
            ];
        }

        return $summary;
    }
}
