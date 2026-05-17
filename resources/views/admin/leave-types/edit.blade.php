<x-app-layout><x-slot name="header"><h2 class="font-semibold text-xl">Edit Leave Type</h2></x-slot>
<div class="py-8 max-w-2xl mx-auto sm:px-6 lg:px-8"><x-alert /><div class="rounded-xl bg-white border p-6">
<form method="POST" action="{{ route('admin.leave-types.update', $leaveType) }}">@csrf @method('PUT') @include('admin.leave-types._form', ['leaveType' => $leaveType])
<div class="mt-4"><x-primary-button>Update</x-primary-button></div></form></div></div></x-app-layout>