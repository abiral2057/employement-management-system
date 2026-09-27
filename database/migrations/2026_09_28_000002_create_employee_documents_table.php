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
        if (!Schema::hasTable('employee_documents')) {
            Schema::create('employee_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->string('title');
                $table->string('category', 50)->default('contract'); // contract, national_id, resume, academic, certificate, medical, tax, other
                $table->string('file_path');
                $table->string('file_extension', 10)->nullable();
                $table->unsignedBigInteger('file_size_bytes')->nullable();
                $table->date('issue_date')->nullable();
                $table->date('expiry_date')->nullable();
                $table->text('notes')->nullable();
                $table->string('status', 20)->default('active'); // active, expired, pending_review, archived
                $table->string('uploaded_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
    }
};
