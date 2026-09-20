<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_attendance', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('session_id')->constrained('academic_sessions')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('status', 20)->default('present');
            $table->string('remark')->nullable();
            $table->timestamps();
            $table->unique(['session_id', 'student_id', 'date'], 'attendance_unique');
            $table->index(['class_id', 'section_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_attendance');
    }
};