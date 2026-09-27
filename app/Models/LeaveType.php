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
        'code',
        'annual_quota_days',
        'is_paid',
        'color',
        'description',
        'status',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'status' => 'boolean',
        'annual_quota_days' => 'integer',
    ];

    public function balances(): HasMany
    {
        return $this->hasMany(EmployeeLeaveBalance::class);
    }
}
