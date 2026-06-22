<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_monthly_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('tahun_ajaran')->restrictOnDelete();
            $table->foreignId('student_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('mapel')->cascadeOnDelete();
            $table->tinyInteger('semester')->default(1); // 1/2
            $table->string('month', 20)->nullable(); // Januari, Februari, dst
            $table->decimal('score', 5, 2)->nullable();
            $table->string('predicate', 10)->nullable();
            $table->text('description')->nullable();
            $table->string('legacy_id', 50)->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_monthly_scores');
    }
};
