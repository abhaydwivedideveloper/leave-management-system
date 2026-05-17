<x-mail::message>
# Leave Application {{ $application->status->label() }}

Hello {{ $application->user->name }},

Your **{{ $application->leaveType->name }}** request from {{ $application->start_date->format('M d, Y') }} to {{ $application->end_date->format('M d, Y') }} has been **{{ $application->status->label() }}**.

@if($application->manager_comment)
**Comment:** {{ $application->manager_comment }}
@endif

<x-mail::button :url="route('employee.dashboard')">View Dashboard</x-mail::button>

Thanks,<br>{{ config('app.name') }}
</x-mail::message>