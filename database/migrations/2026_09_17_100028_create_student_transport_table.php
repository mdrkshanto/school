<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_transport', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('session_id')->constrained('academic_sessions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('route_id')->constrained('transport_routes')->cascadeOnDelete();
            $table->foreignId('stop_id')->nullable()->constrained('transport_stops')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['session_id', 'student_id'], 'student_transport_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_transport');
    }
};