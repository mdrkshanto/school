<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_rules', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 10);
            $table->decimal('grade_point', 3, 1);
            $table->decimal('min_mark', 5, 2);
            $table->decimal('max_mark', 5, 2);
            $table->string('remark')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_rules');
    }
};