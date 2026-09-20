<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('employee_no')->unique();
            $table->string('name');
            $table->string('gender', 20)->default('male');
            $table->date('dob')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('qualification')->nullable();
            $table->string('specialization')->nullable();
            $table->text('address')->nullable();
            $table->date('join_date')->nullable();
            $table->date('leave_date')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};