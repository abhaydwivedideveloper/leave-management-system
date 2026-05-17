<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
            <h2 class="font-semibold text-xl text-slate-800">All Leave Applications</h2>
            <a href="{{ route('admin.leaves.export', request()->query()) }}" class="btn-secondary">Export CSV</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-alert />

            <form method="GET" class="mb-4 flex flex-wrap gap-2">
                <x-form-select name="status" class="w-auto min-w-[140px]">
                    <option value="">All statuses</option>
                    @foreach(\App\Enums\LeaveStatus::cases() as $s)
                        <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>
                    @endforeach
                </x-form-select>
                <x-form-select name="department" class="w-auto min-w-[160px]">
                    <option value="">All departments</option>
                    @foreach($departments as $d)
                        <option value="{{ $d }}" @selected(request('department') === $d)>{{ $d }}</option>
                    @endforeach
                </x-form-select>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input w-auto">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input w-auto">
                <button type="submit" class="btn-primary">Filter</button>
            </form>

            <div class="card overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="table-head">
                            <th class="px-4 py-3">Employee</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Dates</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Override</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($applications as $app)
                            <tr class="hover:bg-slate-50">
                                <td class="table-cell">
                                    {{ $app->user->name }}
                                    <br><span class="text-xs text-slate-500">{{ $app->user->department }}</span>
                                </td>
                                <td class="table-cell">{{ $app->leaveType->name }}</td>
                                <td class="table-cell">{{ $app->start_date->format('M d') }} – {{ $app->end_date->format('M d, Y') }}</td>
                                <td class="table-cell"><x-status-badge :status="$app->status" /></td>
                                <td class="table-cell">
                                    <form method="POST" action="{{ route('admin.leaves.status', $app) }}" class="flex flex-col gap-2 max-w-xs">
                                        @csrf @method('PATCH')
                                        <x-form-select name="status" class="text-xs">
                                            @foreach(\App\Enums\LeaveStatus::cases() as $s)
                                                <option value="{{ $s->value }}" @selected($app->status === $s)>{{ $s->label() }}</option>
                                            @endforeach
                                        </x-form-select>
                                        <input name="manager_comment" value="{{ $app->manager_comment }}" placeholder="Comment" class="form-input text-xs">
                                        <button type="submit" class="btn-primary text-xs py-1.5">Update</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t border-slate-100">{{ $applications->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
