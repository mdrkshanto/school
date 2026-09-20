<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->decimal('obtained_marks', 5, 2)->nullable();
            $table->string('grade', 10)->nullable();
            $table->decimal('grade_point', 3, 1)->nullable();
            $table->boolean('is_absent')->default(false);
            $table->timestamps();
            $table->unique(['exam_id', 'student_id', 'subject_id'], 'result_unique');
            $table->index(['exam_id', 'class_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};