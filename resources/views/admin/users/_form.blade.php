@props(['user' => null, 'managers'])

<div class="space-y-5">
    <div>
        <x-input-label for="name" value="Name" />
        <x-text-input id="name" name="name" type="text" class="mt-1" :value="old('name', $user?->name)" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="mt-1" :value="old('email', $user?->email)" required />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password" :value="$user ? 'New password (leave blank to keep current)' : 'Password'" />
        <x-text-input
            id="password"
            name="password"
            type="password"
            class="mt-1"
            autocomplete="new-password"
            :required="! $user"
        />
        <p class="mt-1 text-xs text-slate-500">Minimum 8 characters.</p>
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password_confirmation" value="Confirm password" />
        <x-text-input
            id="password_confirmation"
            name="password_confirmation"
            type="password"
            class="mt-1"
            autocomplete="new-password"
            :required="! $user"
        />
        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="role" value="Role" />
        <x-form-select id="role" name="role" class="mt-1" required>
            @foreach(\App\Enums\UserRole::cases() as $role)
                <option value="{{ $role->value }}" @selected(old('role', $user?->role?->value) === $role->value)>{{ $role->label() }}</option>
            @endforeach
        </x-form-select>
        <x-input-error :messages="$errors->get('role')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="department" value="Department" />
        <x-text-input id="department" name="department" type="text" class="mt-1" :value="old('department', $user?->department)" />
        <x-input-error :messages="$errors->get('department')" class="mt-2" />
    </div>

    <div id="manager-field">
        <x-input-label for="manager_id" value="Manager (required for employees)" />
        <x-form-select id="manager_id" name="manager_id" class="mt-1">
            <option value="">Select a manager</option>
            @foreach($managers as $manager)
                <option value="{{ $manager->id }}" @selected(old('manager_id', $user?->manager_id) == $manager->id)>{{ $manager->name }}</option>
            @endforeach
        </x-form-select>
        <x-input-error :messages="$errors->get('manager_id')" class="mt-2" />
    </div>
</div>