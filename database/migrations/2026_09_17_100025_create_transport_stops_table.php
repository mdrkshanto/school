<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_stops', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('route_id')->constrained('transport_routes')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('fare', 10, 2)->default(0);
            $table->unsignedInteger('order_index')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_stops');
    }
};