<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'shift_type', // 'day', 'night', 'extra'
        'time_in',
        'time_out',
        'cross_midnight',
        'rate_multiplier',
    ];

    protected $casts = [
        'cross_midnight' => 'boolean',
        'rate_multiplier' => 'float',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function shiftRosters(): HasMany
    {
        return $this->hasMany(ShiftRoster::class);
    }

    public function isNightShift(): bool
    {
        if ($this->shift_type === 'night') return true;
        if ($this->cross_midnight) return true;
        if ($this->time_in && $this->time_out && $this->time_out < $this->time_in) return true;
        return false;
    }

    public function isExtraShift(): bool
    {
        return $this->shift_type === 'extra';
    }

    public function getDurationHoursAttribute(): float
    {
        if (!$this->time_in || !$this->time_out) return 8.0;

        $in = Carbon::parse($this->time_in);
        $out = Carbon::parse($this->time_out);

        if ($this->isNightShift() || $out->lt($in)) {
            $out->addDay();
        }

        return round($in->diffInMinutes($out) / 60, 2);
    }
}
