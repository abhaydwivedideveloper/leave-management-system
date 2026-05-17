<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800">My Leave Dashboard</h2>
            <a href="{{ route('employee.leaves.create') }}" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                Apply for Leave
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-alert />

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-200">
                    <p class="text-sm text-slate-500">Days Taken ({{ $year }})</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ $totalTaken }}</p>
                </div>
                <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-200">
                    <p class="text-sm text-slate-500">Pending ({{ $year }})</p>
                    <p class="mt-2 text-3xl font-bold text-amber-600">{{ $totalPending }}</p>
                </div>
                <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-200">
                    <p class="text-sm text-slate-500">Total Entitlement</p>
                    <p class="mt-2 text-3xl font-bold text-indigo-600">{{ $totalEntitlement }}</p>
                </div>
                <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-200">
                    <p class="text-sm text-slate-500">Remaining</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-600">{{ max(0, $totalEntitlement - $totalTaken - $totalPending) }}</p>
                </div>
            </div>

            <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="font-semibold text-slate-800">Leave Balance by Type ({{ $year }})</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-slate-500">Type</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-500">Entitlement</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-500">Used</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-500">Pending</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-500">Remaining</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($entitlementSummary as $item)
                                <tr>
                                    <td class="px-6 py-3">{{ $item['leave_type']->name }}</td>
                                    <td class="px-6 py-3">{{ $item['entitlement'] }}</td>
                                    <td class="px-6 py-3">{{ $item['used'] }}</td>
                                    <td class="px-6 py-3 text-amber-700">{{ $item['pending'] }}</td>
                                    <td class="px-6 py-3 font-medium text-emerald-700">{{ $item['remaining'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-xl bg-white shadow-sm border border-slate-200">
                <div class="px-6 py-4 border-b border-slate-200 flex flex-wrap gap-4 items-end justify-between">
                    <h3 class="font-semibold text-slate-800">My Applications</h3>
                    <form method="GET" class="flex flex-wrap gap-2">
                        <select name="year" class="form-select w-auto">
                            @foreach($yearOptions as $y)
                                <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                            @endforeach
                        </select>
                        <select name="status" class="form-select w-auto">
                            <option value="">All statuses</option>
                            @foreach(\App\Enums\LeaveStatus::cases() as $status)
                                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                        <select name="leave_type_id" class="form-select w-auto">
                            <option value="">All types</option>
                            @foreach($leaveTypes as $type)
                                <option value="{{ $type->id }}" @selected(request('leave_type_id') == $type->id)>{{ $type->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn-primary">Filter</button>
                    </form>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-slate-500">Type</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-500">Dates</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-500">Days</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-500">Status</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($applications as $application)
                                <tr>
                                    <td class="px-6 py-4">{{ $application->leaveType->name }}</td>
                                    <td class="px-6 py-4">{{ $application->start_date->format('M d') }} – {{ $application->end_date->format('M d, Y') }}</td>
                                    <td class="px-6 py-4">{{ $application->days_count }}</td>
                                    <td class="px-6 py-4"><x-status-badge :status="$application->status" /></td>
                                    <td class="px-6 py-4">
                                        @can('cancel', $application)
                                            <form method="POST" action="{{ route('employee.leaves.destroy', $application) }}" onsubmit="return confirm('Cancel this application?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-rose-600 hover:text-rose-800 text-sm font-medium">Cancel</button>
                                            </form>
                                        @else
                                            <span class="text-slate-400 text-xs">{{ $application->manager_comment ?: '—' }}</span>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-8 text-center text-slate-500">No leave applications yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($applications->hasPages())
                    <div class="px-6 py-4">{{ $applications->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
