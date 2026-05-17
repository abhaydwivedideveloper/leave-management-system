<?php

namespace App\Http\Controllers\Employee;

use App\Enums\LeaveStatus;
use App\Http\Controllers\Controller;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Services\LeaveApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveApplicationController extends Controller
{
    public function __construct(
        protected LeaveApplicationService $leaveService
    ) {}

    public function create(): View
    {
        $leaveTypes = LeaveType::active()->orderBy('name')->get();

        return view('employee.leaves.create', compact('leaveTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', LeaveApplication::class);

        $validated = $this->leaveService->validateApplication($request->user(), $request->all());

        LeaveApplication::create([
            ...$validated,
            'user_id' => $request->user()->id,
            'status' => LeaveStatus::Pending,
        ]);

        return redirect()
            ->route('employee.dashboard')
            ->with('success', 'Leave application submitted successfully.');
    }

    public function destroy(LeaveApplication $leaveApplication): RedirectResponse
    {
        $this->authorize('cancel', $leaveApplication);

        $leaveApplication->delete();

        return redirect()
            ->route('employee.dashboard')
            ->with('success', 'Leave application cancelled.');
    }
}
