<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_attitude_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('tahun_ajaran')->restrictOnDelete();
            $table->foreignId('student_id')->constrained('siswa')->cascadeOnDelete();
            $table->tinyInteger('semester')->default(1);
            $table->string('category', 50); // spiritual, sosial, dll
            $table->string('predicate', 10)->nullable(); // A/B/C/D
            $table->text('description')->nullable();
            $table->string('legacy_id', 50)->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_attitude_reports');
    }
};
