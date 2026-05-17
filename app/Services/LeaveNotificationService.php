<?php

namespace App\Services;

use App\Mail\LeaveStatusChanged;
use App\Models\LeaveApplication;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class LeaveNotificationService
{
    public function notifyStatusChanged(LeaveApplication $application): bool
    {
        try {
            Mail::to($application->user->email)->send(new LeaveStatusChanged($application));

            return true;
        } catch (Throwable $e) {
            Log::warning('Leave status email could not be sent.', [
                'leave_application_id' => $application->id,
                'recipient' => $application->user->email,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
