<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LeaveStatus;
use App\Http\Controllers\Controller;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\LeaveApplicationService;
use App\Services\LeaveNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeaveApplicationController extends Controller
{
    public function __construct(
        protected LeaveApplicationService $leaveService
    ) {}

    public function index(Request $request): View
    {
        $applications = $this->filteredQuery($request)->paginate(15)->withQueryString();
        $departments = User::whereNotNull('department')->distinct()->pluck('department');

        return view('admin.leaves.index', compact('applications', 'departments'));
    }

    public function updateStatus(Request $request, LeaveApplication $leaveApplication): RedirectResponse
    {
        $this->authorize('overrideStatus', $leaveApplication);

        $validated = $request->validate([
            'status' => ['required', Rule::in(array_column(LeaveStatus::cases(), 'value'))],
            'manager_comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $previousStatus = $leaveApplication->status;

        if ($validated['status'] === LeaveStatus::Approved->value && $previousStatus !== LeaveStatus::Approved) {
            try {
                $this->leaveService->validateApproval($leaveApplication);
            } catch (ValidationException $e) {
                return back()->withErrors($e->errors())->withInput();
            }
        }

        $leaveApplication->update([
            'status' => $validated['status'],
            'manager_comment' => $validated['manager_comment'] ?? $leaveApplication->manager_comment,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $message = 'Leave status updated.';

        if ($previousStatus !== $leaveApplication->status) {
            $leaveApplication->load(['user', 'leaveType']);

            if (! app(LeaveNotificationService::class)->notifyStatusChanged($leaveApplication)) {
                $message .= ' Email notification could not be sent (check mail settings).';
            }
        }

        return back()->with('success', $message);
    }

    public function export(Request $request): StreamedResponse
    {
        $query = $this->filteredQuery($request)->with(['user', 'leaveType']);

        $filename = 'leave-report-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID', 'Employee', 'Department', 'Leave Type', 'Start', 'End',
                'Days', 'Status', 'Reason', 'Reviewed At',
            ]);

            $query->chunk(100, function ($applications) use ($handle) {
                foreach ($applications as $app) {
                    fputcsv($handle, [
                        $app->id,
                        $app->user->name,
                        $app->user->department,
                        $app->leaveType->name,
                        $app->start_date->format('Y-m-d'),
                        $app->end_date->format('Y-m-d'),
                        $app->days_count,
                        $app->status->value,
                        $app->reason,
                        $app->reviewed_at?->format('Y-m-d H:i'),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    protected function filteredQuery(Request $request)
    {
        $query = LeaveApplication::with(['user', 'leaveType', 'reviewer'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department')) {
            $query->whereHas('user', fn ($q) => $q->where('department', $request->department));
        }

        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('end_date', '<=', $request->date_to);
        }

        return $query;
    }
}
