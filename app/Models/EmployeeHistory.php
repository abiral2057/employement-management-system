<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeHistory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'event_type', // 'shift_change', 'salary_update', 'promotion', 'department_change', 'payment_reminder', 'status_change'
        'title',
        'description',
        'performed_by',
        'meta_data',
        'created_at',
    ];

    protected $casts = [
        'meta_data' => 'array',
        'created_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Helper to log an event for an employee.
     */
    public static function log($employeeId, $eventType, $title, $description = null, $meta = null, $performedBy = null)
    {
        return self::create([
            'employee_id' => $employeeId,
            'event_type' => $eventType,
            'title' => $title,
            'description' => $description,
            'performed_by' => $performedBy ?? (auth()->check() ? auth()->user()->name : 'System'),
            'meta_data' => $meta,
            'created_at' => now(),
        ]);
    }
}
