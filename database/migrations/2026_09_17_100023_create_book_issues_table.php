<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_issues', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('issued_at');
            $table->date('due_date');
            $table->timestamp('returned_at')->nullable();
            $table->string('status', 20)->default('issued');
            $table->string('remark')->nullable();
            $table->timestamps();
            $table->index(['status', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_issues');
    }
};