<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h2 class="font-semibold text-xl text-gray-800">Team Leave Approvals</h2>
            <p class="text-sm text-slate-500">{{ $teamCount }} team member{{ $teamCount === 1 ? '' : 's' }}</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-alert />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-200">
                    <p class="text-sm text-slate-500">Pending Approvals</p>
                    <p class="mt-2 text-3xl font-bold text-amber-600">{{ $pending->count() }}</p>
                </div>
                <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-200">
                    <p class="text-sm text-slate-500">Processed This Year</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ $processedCount }}</p>
                </div>
            </div>

            <div class="rounded-xl bg-white shadow-sm border border-slate-200">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="font-semibold text-slate-800">Pending Requests</h3>
                    <p class="text-sm text-slate-500 mt-1">Approve or reject leave applications from your direct reports.</p>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($pending as $application)
                        <div class="p-6">
                            <div>
                                <p class="font-medium text-slate-900">{{ $application->user->name }}</p>
                                <p class="text-sm text-slate-500">{{ $application->user->department }} · {{ $application->leaveType->name }}</p>
                                <p class="mt-2 text-sm">
                                    <span class="font-medium text-slate-700">Dates:</span>
                                    {{ $application->start_date->format('M d, Y') }} – {{ $application->end_date->format('M d, Y') }}
                                    ({{ $application->days_count }} {{ Str::plural('day', $application->days_count) }})
                                </p>
                                <p class="mt-2 text-sm text-slate-600">
                                    <span class="font-medium text-slate-700">Reason:</span> {{ $application->reason }}
                                </p>
                                <p class="mt-1 text-xs text-slate-400">Submitted {{ $application->created_at->diffForHumans() }}</p>
                            </div>

                            <form method="POST" action="{{ route('manager.leaves.review', $application) }}" class="mt-4 rounded-lg border border-slate-200 bg-slate-50 p-4 space-y-4">
                                @csrf
                                @method('PATCH')
                                <div>
                                    <label for="comment-{{ $application->id }}" class="text-sm font-medium text-slate-700">Comment (optional)</label>
                                    <textarea
                                        id="comment-{{ $application->id }}"
                                        name="manager_comment"
                                        rows="2"
                                        class="form-textarea mt-1"
                                        placeholder="Add a note for the employee…"
                                    >{{ old('manager_comment') }}</textarea>
                                </div>
                                <div class="flex flex-wrap gap-3">
                                    <button
                                        type="submit"
                                        name="status"
                                        value="approved"
                                        class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-black hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                                    >
                                        Approve
                                    </button>
                                    <button
                                        type="submit"
                                        name="status"
                                        value="rejected"
                                        class="inline-flex items-center justify-center rounded-lg bg-rose-600 px-5 py-2.5 text-sm font-semibold text-black hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2"
                                        onclick="return confirm('Reject this leave request?')"
                                    >
                                        Reject
                                    </button>
                                </div>
                            </form>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-500">
                            <p class="font-medium">No pending applications</p>
                            <p class="mt-1 text-sm">When employees on your team apply for leave, their requests will appear here for approval.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-xl bg-white shadow-sm border border-slate-200">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="font-semibold text-slate-800">Processed Applications</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-slate-500">Employee</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-500">Type</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-500">Dates</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-500">Status</th>
                                <th class="px-6 py-3 text-left font-medium text-slate-500">Comment</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($history as $application)
                                <tr>
                                    <td class="px-6 py-4">{{ $application->user->name }}</td>
                                    <td class="px-6 py-4">{{ $application->leaveType->name }}</td>
                                    <td class="px-6 py-4">{{ $application->start_date->format('M d') }} – {{ $application->end_date->format('M d') }}</td>
                                    <td class="px-6 py-4"><x-status-badge :status="$application->status" /></td>
                                    <td class="px-6 py-4 text-slate-500">{{ $application->manager_comment ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-8 text-center text-slate-500">No history yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($history->hasPages())
                    <div class="px-6 py-4">{{ $history->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
