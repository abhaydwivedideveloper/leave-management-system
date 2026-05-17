<?php

namespace App\Http\Controllers\Manager;

use App\Enums\LeaveStatus;
use App\Http\Controllers\Controller;
use App\Models\LeaveApplication;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $manager = $request->user();

        $teamMemberIds = $manager->employees()->pluck('id');

        $pending = LeaveApplication::with(['user', 'leaveType'])
            ->whereIn('user_id', $teamMemberIds)
            ->pending()
            ->latest()
            ->get();

        $historyQuery = LeaveApplication::with(['user', 'leaveType', 'reviewer'])
            ->whereIn('user_id', $teamMemberIds)
            ->whereIn('status', [LeaveStatus::Approved, LeaveStatus::Rejected])
            ->latest('reviewed_at');

        $history = $historyQuery->paginate(10)->withQueryString();

        $teamCount = $teamMemberIds->count();

        $processedCount = LeaveApplication::whereIn('user_id', $teamMemberIds)
            ->whereIn('status', [LeaveStatus::Approved, LeaveStatus::Rejected])
            ->whereYear('reviewed_at', now()->year)
            ->count();

        return view('manager.dashboard', compact('pending', 'history', 'teamCount', 'processedCount'));
    }
}
