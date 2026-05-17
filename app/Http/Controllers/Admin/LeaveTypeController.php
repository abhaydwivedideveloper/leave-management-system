<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LeaveTypeController extends Controller
{
    public function index(): View
    {
        $leaveTypes = LeaveType::orderBy('name')->paginate(15);

        return view('admin.leave-types.index', compact('leaveTypes'));
    }

    public function create(): View
    {
        return view('admin.leave-types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateLeaveType($request);
        $validated['slug'] = Str::slug($validated['name']);

        LeaveType::create($validated);

        return redirect()->route('admin.leave-types.index')->with('success', 'Leave type created.');
    }

    public function edit(LeaveType $leaveType): View
    {
        return view('admin.leave-types.edit', compact('leaveType'));
    }

    public function update(Request $request, LeaveType $leaveType): RedirectResponse
    {
        $validated = $this->validateLeaveType($request);
        $validated['slug'] = Str::slug($validated['name']);

        $leaveType->update($validated);

        return redirect()->route('admin.leave-types.index')->with('success', 'Leave type updated.');
    }

    public function destroy(LeaveType $leaveType): RedirectResponse
    {
        if ($leaveType->leaveApplications()->exists()) {
            return back()->with('error', 'Cannot delete leave type with existing applications.');
        }

        $leaveType->delete();

        return redirect()->route('admin.leave-types.index')->with('success', 'Leave type deleted.');
    }

    protected function validateLeaveType(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'days_per_year' => ['required', 'integer', 'min:0', 'max:365'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
