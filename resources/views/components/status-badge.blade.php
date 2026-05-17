@props(['status'])

@php
    $leaveStatus = $status instanceof \App\Enums\LeaveStatus ? $status : \App\Enums\LeaveStatus::from($status);
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium '.$leaveStatus->badgeClass()]) }}>
    {{ $leaveStatus->label() }}
</span>
