<?php

namespace App\Enums;

enum LeaveStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-100 text-amber-800',
            self::Approved => 'bg-emerald-100 text-emerald-800',
            self::Rejected => 'bg-rose-100 text-rose-800',
        };
    }
}
