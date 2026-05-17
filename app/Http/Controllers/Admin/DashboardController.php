<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'employees' => User::where('role', UserRole::Employee)->count(),
            'managers' => User::where('role', UserRole::Manager)->count(),
            'pending_leaves' => LeaveApplication::pending()->count(),
            'leave_types' => LeaveType::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
