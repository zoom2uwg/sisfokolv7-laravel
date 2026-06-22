<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curriculum_competencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('tahun_ajaran')->restrictOnDelete();
            $table->foreignId('subject_id')->constrained('mapel')->cascadeOnDelete();
            $table->string('phase', 20); // E, F
            $table->string('code', 50); // nomor TP
            $table->text('description');
            $table->string('legacy_id', 50)->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['academic_year_id', 'subject_id', 'phase', 'code'], 'curriculum_competencies_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curriculum_competencies');
    }
};
