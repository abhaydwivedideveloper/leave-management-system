<x-app-layout>
    <x-slot name="header"><div class="flex justify-between"><h2 class="font-semibold text-xl">Leave Types</h2>
        <a href="{{ route('admin.leave-types.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm text-white">Add</a></div></x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8"><x-alert />
        <div class="rounded-xl bg-white border overflow-hidden"><table class="min-w-full text-sm"><thead class="bg-slate-50"><tr>
            <th class="px-6 py-3 text-left">Name</th><th class="px-6 py-3 text-left">Days/Year</th>
            <th class="px-6 py-3 text-left">Active</th><th class="px-6 py-3 text-right">Actions</th></tr></thead><tbody>
        @foreach($leaveTypes as $type)<tr class="divide-y"><td class="px-6 py-4">{{ $type->name }}</td>
            <td class="px-6 py-4">{{ $type->days_per_year }}</td>
            <td class="px-6 py-4">{{ $type->is_active ? 'Yes' : 'No' }}</td>
            <td class="px-6 py-4 text-right"><a href="{{ route('admin.leave-types.edit', $type) }}" class="text-indigo-600">Edit</a>
            <form method="POST" action="{{ route('admin.leave-types.destroy', $type) }}" class="inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')
            <button class="text-rose-600 ml-2">Delete</button></form></td></tr>@endforeach</tbody></table>
        {{ $leaveTypes->links() }}</div></div></x-app-layout>