<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('tahun_ajaran')->restrictOnDelete();
            $table->foreignId('student_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('payment_item_id')->constrained('payment_items')->restrictOnDelete();
            $table->string('period', 50)->nullable(); // Januari 2026, Tahun 2026, dst
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('paid', 15, 2)->default(0);
            $table->decimal('remaining', 15, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->string('status', 20)->default('unpaid'); // unpaid, partial, paid
            $table->string('legacy_id', 50)->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['student_id', 'payment_item_id', 'period'], 'student_bill_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_bills');
    }
};
