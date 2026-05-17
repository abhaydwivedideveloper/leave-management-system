<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'days_per_year',
        'is_active',
    ];

    protected $casts = [
        'days_per_year' => 'integer',
        'is_active' => 'boolean',
    ];

    public function leaveApplications(): HasMany
    {
        return $this->hasMany(LeaveApplication::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
