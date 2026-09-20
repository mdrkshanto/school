<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('session_id')->constrained('academic_sessions')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('status', 20)->default('draft');
            $table->timestamps();
            $table->index(['session_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};