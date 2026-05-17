<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800">Apply for Leave</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <x-alert />
            <div class="card p-6">
                <form method="POST" action="{{ route('employee.leaves.store') }}" class="space-y-5">
                    @csrf
                    <div>
                        <x-input-label for="leave_type_id" value="Leave Type" />
                        <x-form-select id="leave_type_id" name="leave_type_id" class="mt-1" required>
                            <option value="">Select type</option>
                            @foreach($leaveTypes as $type)
                                <option value="{{ $type->id }}" @selected(old('leave_type_id') == $type->id)>
                                    {{ $type->name }} ({{ $type->days_per_year }} days/year)
                                </option>
                            @endforeach
                        </x-form-select>
                        <x-input-error :messages="$errors->get('leave_type_id')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="start_date" value="Start Date" />
                        <x-text-input id="start_date" name="start_date" type="date" class="mt-1" :value="old('start_date')" required />
                        <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="end_date" value="End Date" />
                        <x-text-input id="end_date" name="end_date" type="date" class="mt-1" :value="old('end_date')" required />
                        <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="reason" value="Reason" />
                        <textarea id="reason" name="reason" rows="4" class="form-textarea mt-1" required placeholder="Describe the reason for your leave (min. 10 characters)">{{ old('reason') }}</textarea>
                        <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                    </div>
                    <div class="flex gap-3">
                        <x-primary-button>Submit Application</x-primary-button>
                        <a href="{{ route('employee.dashboard') }}" class="btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
