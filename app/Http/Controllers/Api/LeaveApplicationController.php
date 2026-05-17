<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeaveApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveApplicationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = LeaveApplication::with(['leaveType', 'user:id,name,email,department'])
            ->latest();

        if ($user->isEmployee()) {
            $query->where('user_id', $user->id);
        } elseif ($user->isManager()) {
            $query->whereHas('user', fn ($q) => $q->where('manager_id', $user->id));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $applications = $query->paginate($request->integer('per_page', 15));

        return response()->json($applications);
    }
}
