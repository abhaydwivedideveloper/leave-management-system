<?php

namespace App\Http\Controllers\Manager;

use App\Enums\LeaveStatus;
use App\Http\Controllers\Controller;
use App\Models\LeaveApplication;
use App\Services\LeaveApplicationService;
use App\Services\LeaveNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LeaveReviewController extends Controller
{
    public function __construct(
        protected LeaveApplicationService $leaveService
    ) {}

    public function update(Request $request, LeaveApplication $leaveApplication): RedirectResponse
    {
        $this->authorize('review', $leaveApplication);

        $validated = $request->validate([
            'status' => ['required', Rule::in([LeaveStatus::Approved->value, LeaveStatus::Rejected->value])],
            'manager_comment' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validated['status'] === LeaveStatus::Approved->value) {
            try {
                $this->leaveService->validateApproval($leaveApplication);
            } catch (ValidationException $e) {
                return back()->withErrors($e->errors())->withInput();
            }
        }

        $leaveApplication->update([
            'status' => $validated['status'],
            'manager_comment' => $validated['manager_comment'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $leaveApplication->load(['user', 'leaveType']);

        $emailSent = app(LeaveNotificationService::class)->notifyStatusChanged($leaveApplication);

        $action = $validated['status'] === LeaveStatus::Approved->value ? 'approved' : 'rejected';
        $message = "Leave application {$action} successfully.";

        if (! $emailSent) {
            $message .= ' The employee was not emailed (mail is not configured on this server).';
        }

        return redirect()
            ->route('manager.dashboard')
            ->with('success', $message);
    }
}
