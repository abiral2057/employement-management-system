<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        // 'user_id',
        'department_id',
        'designation_id',
        'schedule_id',
        'firstname',
        'lastname',
        'unique_id',
        'avatar',
        'email',
        'phone',
        'address',
        'dob',
        'joining_date',
        'emergency_contact_name',
        'emergency_contact_phone',
        'employment_type',
        'gender',
        'religion',
        'marital',
        'status'
    ];

    protected $casts = [
        'dob' => 'date',
        'joining_date' => 'date',
        'status' => 'integer',
    ];
// Define relationships
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo {
        return $this->belongsTo(Department::class);
    }

    public function designation(): BelongsTo {
        return $this->belongsTo(Designation::class);
    }

    public function attendances(): HasMany {
        return $this->hasMany(Attendance::class); // Replace 'Attendance' with your actual attendance model name
    }

    public function schedule(): BelongsTo {
        return $this->belongsTo(Schedule::class); // Replace 'Attendance' with your actual attendance model name
    }

    public function departs(): HasMany {
        return $this->hasMany(Depart::class);
    }

    public function check(): HasMany {
        return $this->hasMany(Check::class);
    }

    public function salary(): HasOne {
        return $this->hasOne(Salary::class);
    }

    public function leaves(): HasMany {
        return $this->hasMany(Leave::class);
    }

    public function lateTime(): HasMany {
        return $this->hasMany(LateTime::class);
    }

    public function overTime(): HasMany {
        return $this->hasMany(OverTime::class);
    }

    public function overtimes(): HasMany {
        return $this->hasMany(OverTime::class);
    }

    public function payrolls(): HasMany {
        return $this->hasMany(Payroll::class);
    }

    public function shiftRosters(): HasMany {
        return $this->hasMany(ShiftRoster::class);
    }

    public function histories(): HasMany {
        return $this->hasMany(EmployeeHistory::class)->orderBy('created_at', 'desc');
    }

    public function documents(): HasMany {
        return $this->hasMany(EmployeeDocument::class)->orderBy('created_at', 'desc');
    }

    public function leaveBalances(): HasMany {
        return $this->hasMany(EmployeeLeaveBalance::class);
    }

    public function getFullNameAttribute(): string {
        return trim($this->firstname . ' ' . $this->lastname);
    }
}

