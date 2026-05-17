<?php

namespace App\Http\Controllers\Employee;

use App\Enums\LeaveStatus;
use App\Http\Controllers\Controller;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Services\LeaveApplicationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected LeaveApplicationService $leaveService
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $year = (int) ($request->get('year') ?: now()->year);

        $query = LeaveApplication::with('leaveType')
            ->where('user_id', $user->id)
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }

        $applications = $query->paginate(10)->withQueryString();
        $leaveTypes = LeaveType::active()->orderBy('name')->get();
        $entitlementSummary = $this->leaveService->getEntitlementSummary($user, $year);

        $totalTaken = LeaveApplication::where('user_id', $user->id)
            ->approved()
            ->forYear($year)
            ->sum('days_count');

        $totalPending = LeaveApplication::where('user_id', $user->id)
            ->pending()
            ->forYear($year)
            ->sum('days_count');

        $totalEntitlement = LeaveType::active()->sum('days_per_year');
        $yearOptions = range(now()->year - 2, now()->year + 1);

        return view('employee.dashboard', compact(
            'applications',
            'leaveTypes',
            'entitlementSummary',
            'totalTaken',
            'totalPending',
            'totalEntitlement',
            'year',
            'yearOptions'
        ));
    }
}
