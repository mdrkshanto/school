<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_teachers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('session_id')->constrained('academic_sessions')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_class_teacher')->default(false);
            $table->timestamps();
            $table->unique(['session_id', 'class_id', 'section_id', 'teacher_id'], 'class_teacher_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_teachers');
    }
};