<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('admission_no')->unique();
            $table->foreignId('class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('session_id')->nullable()->constrained('academic_sessions')->nullOnDelete();
            $table->string('name');
            $table->string('gender', 20)->default('male');
            $table->date('dob')->nullable();
            $table->string('blood_group', 10)->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('avatar')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->index(['class_id', 'section_id']);
            $table->index('session_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};