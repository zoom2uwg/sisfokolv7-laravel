<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_extracurricular', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('extracurricular_id')->constrained('extracurriculars')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('tahun_ajaran')->restrictOnDelete();
            $table->string('score', 10)->nullable(); // nilai predikat A/B/C/D
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'extracurricular_id', 'academic_year_id'], 'student_extracurricular_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_extracurricular');
    }
};
