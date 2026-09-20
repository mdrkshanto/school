<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->decimal('full_marks', 5, 2)->default(100);
            $table->decimal('pass_marks', 5, 2)->default(33);
            $table->timestamps();
            $table->unique(['exam_id', 'class_id', 'subject_id'], 'exam_schedule_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_schedules');
    }
};