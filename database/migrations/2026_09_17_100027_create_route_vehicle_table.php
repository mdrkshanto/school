<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_vehicle', function (Blueprint $table): void {
            $table->foreignId('route_id')->constrained('transport_routes')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->primary(['route_id', 'vehicle_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_vehicle');
    }
};