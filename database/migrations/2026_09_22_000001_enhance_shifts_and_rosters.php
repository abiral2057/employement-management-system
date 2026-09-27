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
        // Add shift attributes to schedules
        Schema::table('schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('schedules', 'shift_type')) {
                $table->string('shift_type', 30)->default('day')->after('title'); // 'day', 'night', 'extra'
            }
            if (!Schema::hasColumn('schedules', 'cross_midnight')) {
                $table->boolean('cross_midnight')->default(false)->after('time_out');
            }
            if (!Schema::hasColumn('schedules', 'rate_multiplier')) {
                $table->decimal('rate_multiplier', 5, 2)->default(1.00)->after('cross_midnight');
            }
        });

        // Create shift_rosters table for scheduling Day, Night, and Extra shifts
        if (!Schema::hasTable('shift_rosters')) {
            Schema::create('shift_rosters', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
                $table->date('shift_date');
                $table->string('shift_type', 30)->default('day'); // 'day', 'night', 'extra'
                $table->string('status', 30)->default('scheduled'); // 'scheduled', 'completed', 'swapped', 'absent'
                $table->decimal('hours', 5, 2)->default(8.00);
                $table->decimal('extra_allowance', 10, 2)->default(0.00);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['employee_id', 'shift_date']);
                $table->index(['shift_date', 'shift_type']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_rosters');

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn(['shift_type', 'cross_midnight', 'rate_multiplier']);
        });
    }
};
