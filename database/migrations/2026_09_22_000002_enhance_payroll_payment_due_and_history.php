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
        // Add payment due and reminder fields to payrolls
        Schema::table('payrolls', function (Blueprint $table) {
            if (!Schema::hasColumn('payrolls', 'due_date')) {
                $table->date('due_date')->nullable()->after('status');
            }
            if (!Schema::hasColumn('payrolls', 'paid_amount')) {
                $table->decimal('paid_amount', 10, 2)->default(0.00)->after('due_date');
            }
            if (!Schema::hasColumn('payrolls', 'payment_status')) {
                $table->string('payment_status', 30)->default('unpaid')->after('paid_amount'); // 'unpaid', 'partial', 'paid'
            }
            if (!Schema::hasColumn('payrolls', 'last_reminder_at')) {
                $table->timestamp('last_reminder_at')->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('payrolls', 'reminder_count')) {
                $table->integer('reminder_count')->default(0)->after('last_reminder_at');
            }
            if (!Schema::hasColumn('payrolls', 'notes')) {
                $table->text('notes')->nullable()->after('reminder_count');
            }
        });

        // Add status and rate to over_times
        Schema::table('over_times', function (Blueprint $table) {
            if (!Schema::hasColumn('over_times', 'status')) {
                $table->string('status', 30)->default('approved')->after('overtime_date'); // 'pending', 'approved', 'rejected'
            }
            if (!Schema::hasColumn('over_times', 'rate')) {
                $table->decimal('rate', 10, 2)->nullable()->after('status');
            }
            if (!Schema::hasColumn('over_times', 'amount')) {
                $table->decimal('amount', 10, 2)->nullable()->after('rate');
            }
            if (!Schema::hasColumn('over_times', 'reason')) {
                $table->text('reason')->nullable()->after('amount');
            }
        });

        // Create employee_histories table for tracking lifecycle & audit events
        if (!Schema::hasTable('employee_histories')) {
            Schema::create('employee_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->string('event_type', 50); // 'shift_change', 'salary_update', 'promotion', 'department_change', 'payment_reminder', 'status_change'
                $table->string('title', 255);
                $table->text('description')->nullable();
                $table->string('performed_by', 100)->nullable();
                $table->json('meta_data')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['employee_id', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_histories');

        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['due_date', 'paid_amount', 'payment_status', 'last_reminder_at', 'reminder_count', 'notes']);
        });

        Schema::table('over_times', function (Blueprint $table) {
            $table->dropColumn(['status', 'rate', 'amount', 'reason']);
        });
    }
};
