<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('tahun_ajaran')->restrictOnDelete();
            $table->foreignId('student_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('achievement_type_id')->constrained('achievement_types')->restrictOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->date('date')->nullable();
            $table->string('level', 100)->nullable(); // sekolah, kabupaten, provinsi, nasional
            $table->string('attachment_path', 255)->nullable();
            $table->string('legacy_id', 50)->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_achievements');
    }
};
