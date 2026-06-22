<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('tahun_ajaran')->restrictOnDelete();
            $table->foreignId('student_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('violation_point_id')->constrained('violation_points')->restrictOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete(); // pelapor
            $table->date('date');
            $table->text('description')->nullable();
            $table->string('legacy_id', 50)->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_violations');
    }
};
