<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('body');
            $table->string('category', 20)->default('general');
            $table->string('audience', 20)->default('all');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index(['published_at', 'audience']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};