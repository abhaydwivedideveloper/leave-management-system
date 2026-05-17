<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800">Create User</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <x-alert />
            <div class="card p-6">
                <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
                    @csrf
                    @include('admin.users._form', ['managers' => $managers])
                    <div class="flex items-center gap-3 pt-2">
                        <x-primary-button>Create User</x-primary-button>
                        <a href="{{ route('admin.users.index') }}" class="btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
