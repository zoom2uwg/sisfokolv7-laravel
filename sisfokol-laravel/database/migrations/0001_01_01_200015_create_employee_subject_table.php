<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('mapel')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('tahun_ajaran')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['employee_id', 'subject_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_subject');
    }
};
