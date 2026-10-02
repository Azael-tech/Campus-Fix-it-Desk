<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('last_name', 60)->nullable()->after('name');
            $table->string('first_name', 60)->nullable()->after('last_name');
            $table->string('middle_initial', 1)->nullable()->after('first_name');
            $table->string('gender', 20)->nullable()->after('middle_initial');
            $table->string('suffix', 10)->nullable()->after('gender');
        });

        // Fill in the demo staff account if it already exists
        DB::table('users')->where('email', 'staff@school.test')
            ->update(['first_name' => 'Maintenance', 'last_name' => 'Staff']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_name', 'first_name', 'middle_initial', 'gender', 'suffix']);
        });
    }
};