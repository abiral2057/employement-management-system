<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->string('status')->default('draft');
            $table->date('paid_date')->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->decimal('bonus', 10, 2)->default(0.00);
            $table->decimal('overtime_hours', 8, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['status', 'paid_date', 'payment_method', 'bonus', 'overtime_hours']);
        });
    }
};