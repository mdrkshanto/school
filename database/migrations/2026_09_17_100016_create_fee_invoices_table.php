<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_invoices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('session_id')->constrained('academic_sessions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_structure_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_no')->unique();
            $table->decimal('amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('remark')->nullable();
            $table->timestamps();
            $table->index(['status', 'due_date']);
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_invoices');
    }
};