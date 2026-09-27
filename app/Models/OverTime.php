<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class OverTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'overtime_duration',
        'overtime_date',
        'status', // 'pending', 'approved', 'rejected'
        'rate',
        'amount',
        'reason',
    ];

    protected $casts = [
        'overtime_date' => 'date',
        'rate' => 'float',
        'amount' => 'float',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function getHoursDecimalAttribute(): float
    {
        if (!$this->overtime_duration) return 0.0;
        $parts = explode(':', $this->overtime_duration);
        $hours = isset($parts[0]) ? (int) $parts[0] : 0;
        $minutes = isset($parts[1]) ? (int) $parts[1] : 0;
        return round($hours + ($minutes / 60), 2);
    }
}
