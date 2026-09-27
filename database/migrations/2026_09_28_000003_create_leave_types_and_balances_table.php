<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('leave_types')) {
            Schema::create('leave_types', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code', 20)->unique();
                $table->integer('annual_quota_days')->default(14);
                $table->boolean('is_paid')->default(true);
                $table->string('color', 20)->default('#3b82f6');
                $table->text('description')->nullable();
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('employee_leave_balances')) {
            Schema::create('employee_leave_balances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->foreignId('leave_type_id')->constrained('leave_types')->onDelete('cascade');
                $table->integer('year');
                $table->decimal('total_allocated', 5, 1)->default(14.0);
                $table->decimal('used_days', 5, 1)->default(0.0);
                $table->decimal('pending_days', 5, 1)->default(0.0);
                $table->timestamps();

                $table->unique(['employee_id', 'leave_type_id', 'year'], 'emp_leave_yr_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_leave_balances');
        Schema::dropIfExists('leave_types');
    }
};
