<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('specialty', 50)->nullable()->after('role'); // type of problem a staff member handles
        });

        Schema::table('maintenance_reports', function (Blueprint $table) {
            $table->foreignId('assigned_user_id')->nullable()->after('assigned_to')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_user_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('specialty');
        });
    }
};