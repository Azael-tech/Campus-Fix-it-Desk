<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_reports', function (Blueprint $table) {
            $table->id();
            $table->string('reporter_name', 100);
            $table->string('reporter_role', 30);          // Student, Teacher, Staff, Parent
            $table->string('building', 100);
            $table->string('room', 50);
            $table->string('category', 50);
            $table->string('title', 120);
            $table->text('description');
            $table->string('priority', 20)->default('medium'); // low, medium, high, urgent
            $table->string('status', 20)->default('pending');  // pending, in_progress, resolved
            $table->string('assigned_to', 100)->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_reports');
    }
};
