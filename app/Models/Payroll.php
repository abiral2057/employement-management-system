<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'month',
        'year',
        'basic',
        'house_rent',
        'medical',
        'transport',
        'phone_bill',
        'internet_bill',
        'special',
        'bonus',
        'overtime_hours',
        'overtime_pay',
        'days_present',
        'days_absent',
        'gross_salary',
        'provident_fund',
        'income_tax',
        'health_insurance',
        'life_insurance',
        'deduction',
        'net_salary',
        'status',           // 'draft', 'pending', 'approved', 'paid'
        'paid_date',
        'payment_method',
        'due_date',
        'paid_amount',
        'payment_status',   // 'unpaid', 'partial', 'paid'
        'last_reminder_at',
        'reminder_count',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_date' => 'date',
        'last_reminder_at' => 'datetime',
        'basic' => 'float',
        'gross_salary' => 'float',
        'deduction' => 'float',
        'net_salary' => 'float',
        'paid_amount' => 'float',
        'overtime_pay' => 'float',
        'bonus' => 'float',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getBalanceDueAttribute(): float
    {
        $net = (float) $this->net_salary;
        $paid = (float) $this->paid_amount;
        return max(0, round($net - $paid, 2));
    }

    public function isOverdue(): bool
    {
        if ($this->payment_status === 'paid') return false;
        if (!$this->due_date) return false;
        return Carbon::parse($this->due_date)->endOfDay()->isPast();
    }
}
