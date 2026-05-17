<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800">Edit User</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <x-alert />
            <div class="card p-6">
                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
                    @csrf
                    @method('PUT')
                    @include('admin.users._form', ['user' => $user, 'managers' => $managers])
                    <div class="flex items-center gap-3 pt-2">
                        <x-primary-button>Update User</x-primary-button>
                        <a href="{{ route('admin.users.index') }}" class="btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
