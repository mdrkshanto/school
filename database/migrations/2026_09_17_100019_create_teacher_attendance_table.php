<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_attendance', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('status', 20)->default('present');
            $table->string('remark')->nullable();
            $table->timestamps();
            $table->unique(['teacher_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_attendance');
    }
};