<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftRoster extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'schedule_id',
        'shift_date',
        'shift_type', // 'day', 'night', 'extra'
        'status',     // 'scheduled', 'completed', 'swapped', 'absent'
        'hours',
        'extra_allowance',
        'notes',
    ];

    protected $casts = [
        'shift_date' => 'date',
        'hours' => 'float',
        'extra_allowance' => 'float',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function scopeDay($query)
    {
        return $query->where('shift_type', 'day');
    }

    public function scopeNight($query)
    {
        return $query->where('shift_type', 'night');
    }

    public function scopeExtra($query)
    {
        return $query->where('shift_type', 'extra');
    }
}
