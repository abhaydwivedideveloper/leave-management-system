<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Admin Dashboard</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-alert />
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-200">
                    <p class="text-sm text-slate-500">Employees</p>
                    <p class="mt-2 text-3xl font-bold">{{ $stats['employees'] }}</p>
                </div>
                <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-200">
                    <p class="text-sm text-slate-500">Managers</p>
                    <p class="mt-2 text-3xl font-bold">{{ $stats['managers'] }}</p>
                </div>
                <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-200">
                    <p class="text-sm text-slate-500">Pending Leaves</p>
                    <p class="mt-2 text-3xl font-bold text-amber-600">{{ $stats['pending_leaves'] }}</p>
                </div>
                <div class="rounded-xl bg-white p-6 shadow-sm border border-slate-200">
                    <p class="text-sm text-slate-500">Leave Types</p>
                    <p class="mt-2 text-3xl font-bold">{{ $stats['leave_types'] }}</p>
                </div>
            </div>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('admin.users.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm text-white">Add User</a>
                <a href="{{ route('admin.leave-types.create') }}" class="rounded-lg bg-slate-800 px-4 py-2 text-sm text-white">Add Leave Type</a>
                <a href="{{ route('admin.leaves.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700">View All Leaves</a>
                <a href="{{ route('admin.leaves.export') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700">Export CSV</a>
            </div>
        </div>
    </div>
</x-app-layout>
