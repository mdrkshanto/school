<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('author')->nullable();
            $table->string('isbn')->nullable()->unique();
            $table->string('publisher')->nullable();
            $table->string('category', 40)->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('available_count')->default(1);
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};