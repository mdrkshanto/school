<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_structures', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('session_id')->constrained('academic_sessions')->cascadeOnDelete();
            $table->foreignId('class_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type', 20)->default('monthly');
            $table->decimal('amount', 10, 2);
            $table->boolean('is_optional')->default(false);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->index(['session_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_structures');
    }
};