<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('class_id')->constrained()->cascadeOnDelete();
            $table->string('name', 60);
            $table->unsignedInteger('capacity')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->unique(['class_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};