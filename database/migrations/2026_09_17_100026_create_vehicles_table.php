<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table): void {
            $table->id();
            $table->string('registration_no')->unique();
            $table->string('model')->nullable();
            $table->unsignedInteger('capacity')->default(4);
            $table->string('driver_name')->nullable();
            $table->string('driver_phone')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};