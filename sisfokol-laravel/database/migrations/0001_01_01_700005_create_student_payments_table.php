<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('tahun_ajaran')->restrictOnDelete();
            $table->foreignId('student_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('treasurer_id')->nullable()->constrained('treasurers')->nullOnDelete();
            $table->string('invoice_number', 100)->unique();
            $table->date('payment_date');
            $table->decimal('total', 15, 2)->default(0);
            $table->string('payment_method', 50)->default('cash'); // cash, transfer
            $table->text('note')->nullable();
            $table->string('legacy_id', 50)->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_payments');
    }
};
