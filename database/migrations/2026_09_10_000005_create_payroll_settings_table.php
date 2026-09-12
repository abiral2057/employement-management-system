<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('label', 100)->nullable();
            $table->timestamps();
        });

        $settings = [
            ['working_days', '26', 'Working days per month'],
            ['day_rate_divisor', '26', 'Daily rate divisor (basic / divisor)'],
            ['absent_deduction_enabled', '1', 'Deduct pay for unpaid absences'],
            ['rounding', 'none', 'Net salary rounding (none / nearest)'],
            ['currency', 'रु', 'Currency symbol'],
        ];

        foreach ($settings as [$key, $value, $label]) {
            DB::table('payroll_settings')->insert([
                'key' => $key,
                'value' => $value,
                'label' => $label,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_settings');
    }
};