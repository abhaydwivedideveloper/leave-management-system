<?php

namespace App\Mail;

use App\Models\LeaveApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaveStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public LeaveApplication $application
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Leave Application '.$this->application->status->label(),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.leave-status-changed',
        );
    }
}
