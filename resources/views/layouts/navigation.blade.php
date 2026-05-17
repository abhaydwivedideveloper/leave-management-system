<nav x-data="{ open: false }" class="bg-slate-900 border-b border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-8">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-500 text-sm font-bold text-white">LM</span>
                    <span class="text-white font-semibold hidden sm:block">{{ config('app.name') }}</span>
                </a>

                <div class="hidden sm:flex sm:gap-6">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" class="!text-slate-300 hover:!text-white">Dashboard</x-nav-link>
                            <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')" class="!text-slate-300 hover:!text-white">Users</x-nav-link>
                            <x-nav-link :href="route('admin.leave-types.index')" :active="request()->routeIs('admin.leave-types.*')" class="!text-slate-300 hover:!text-white">Leave Types</x-nav-link>
                            <x-nav-link :href="route('admin.leaves.index')" :active="request()->routeIs('admin.leaves.*')" class="!text-slate-300 hover:!text-white">All Leaves</x-nav-link>
                        @elseif(auth()->user()->isManager())
                            <x-nav-link :href="route('manager.dashboard')" :active="request()->routeIs('manager.*')" class="!text-slate-300 hover:!text-white">Team Leaves</x-nav-link>
                        @else
                            <x-nav-link :href="route('employee.dashboard')" :active="request()->routeIs('employee.*')" class="!text-slate-300 hover:!text-white">My Leaves</x-nav-link>
                        @endif
                    @endauth
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:gap-4">
                @auth
                    <span class="text-xs text-slate-400 uppercase tracking-wide">{{ auth()->user()->role->label() }}</span>
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-slate-800 px-3 py-2 text-sm text-slate-200 hover:bg-slate-700">
                                {{ Auth::user()->name }}
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">Profile</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Log Out</x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @endauth
            </div>

            <div class="flex items-center sm:hidden">
                <button type="button" @click="open = ! open" class="p-2 rounded-md text-slate-400 hover:text-white">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-slate-800">
        <div class="px-4 pt-2 pb-4 space-y-1">
            @auth
                @if(auth()->user()->isAdmin())
                    <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">Dashboard</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">Users</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.leave-types.index')" :active="request()->routeIs('admin.leave-types.*')">Leave Types</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.leaves.index')" :active="request()->routeIs('admin.leaves.*')">All Leaves</x-responsive-nav-link>
                @elseif(auth()->user()->isManager())
                    <x-responsive-nav-link :href="route('manager.dashboard')" :active="request()->routeIs('manager.*')">Team Leaves</x-responsive-nav-link>
                @else
                    <x-responsive-nav-link :href="route('employee.dashboard')" :active="request()->routeIs('employee.*')">My Leaves</x-responsive-nav-link>
                @endif
                <x-responsive-nav-link :href="route('profile.edit')">Profile</x-responsive-nav-link>
            @endauth
        </div>
    </div>
</nav>
