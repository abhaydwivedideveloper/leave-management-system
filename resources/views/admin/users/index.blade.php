<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-slate-800">Users</h2>
            <a href="{{ route('admin.users.create') }}" class="btn-primary">Add User</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-alert />

            <form method="GET" class="mb-4 flex flex-wrap gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email..." class="form-input w-auto min-w-[200px]">
                <x-form-select name="role" class="w-auto min-w-[140px]">
                    <option value="">All roles</option>
                    @foreach(\App\Enums\UserRole::cases() as $role)
                        <option value="{{ $role->value }}" @selected(request('role') === $role->value)>{{ $role->label() }}</option>
                    @endforeach
                </x-form-select>
                <button type="submit" class="btn-primary">Filter</button>
            </form>

            <div class="card overflow-hidden">
                <table class="min-w-full">
                    <thead>
                        <tr class="table-head">
                            <th class="px-6 py-3">Name</th>
                            <th class="px-6 py-3">Email</th>
                            <th class="px-6 py-3">Role</th>
                            <th class="px-6 py-3">Department</th>
                            <th class="px-6 py-3">Manager</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($users as $user)
                            <tr class="hover:bg-slate-50">
                                <td class="table-cell font-medium text-slate-900">{{ $user->name }}</td>
                                <td class="table-cell">{{ $user->email }}</td>
                                <td class="table-cell">{{ $user->role->label() }}</td>
                                <td class="table-cell">{{ $user->department ?? '—' }}</td>
                                <td class="table-cell">{{ $user->manager?->name ?? '—' }}</td>
                                <td class="table-cell text-right space-x-3">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Edit</a>
                                    @if($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" onsubmit="return confirm('Delete this user?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-rose-600 hover:text-rose-800 font-medium">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="table-cell text-center text-slate-500 py-8">No users found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t border-slate-100">{{ $users->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
