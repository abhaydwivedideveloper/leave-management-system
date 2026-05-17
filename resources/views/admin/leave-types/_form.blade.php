<div class="space-y-5">
    <div>
        <x-input-label for="name" value="Name" />
        <x-text-input id="name" name="name" type="text" class="mt-1" :value="old('name', $leaveType?->name)" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" rows="3" class="form-textarea mt-1">{{ old('description', $leaveType?->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="days_per_year" value="Days per year" />
        <x-text-input id="days_per_year" name="days_per_year" type="number" min="0" class="mt-1" :value="old('days_per_year', $leaveType?->days_per_year ?? 0)" required />
        <x-input-error :messages="$errors->get('days_per_year')" class="mt-2" />
    </div>
    <label class="flex items-center gap-2 text-sm text-slate-700">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $leaveType?->is_active ?? true)) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
        <span>Active</span>
    </label>
</div>
