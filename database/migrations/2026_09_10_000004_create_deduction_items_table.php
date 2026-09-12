<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deduction_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employee_id')->unsigned();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->string('name', 100);
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->string('type', 20)->default('fixed');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deduction_items');
    }
};