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
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'joining_date')) {
                $table->date('joining_date')->nullable()->after('dob');
            }
            if (!Schema::hasColumn('employees', 'emergency_contact_name')) {
                $table->string('emergency_contact_name', 100)->nullable()->after('phone');
            }
            if (!Schema::hasColumn('employees', 'emergency_contact_phone')) {
                $table->string('emergency_contact_phone', 50)->nullable()->after('emergency_contact_name');
            }
            if (!Schema::hasColumn('employees', 'employment_type')) {
                $table->string('employment_type', 50)->default('full_time')->after('status');
            }
            if (!Schema::hasColumn('employees', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'joining_date',
                'emergency_contact_name',
                'emergency_contact_phone',
                'employment_type',
            ]);
        });
    }
};
