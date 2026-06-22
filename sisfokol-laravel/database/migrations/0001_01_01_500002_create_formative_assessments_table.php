<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formative_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('tahun_ajaran')->restrictOnDelete();
            $table->foreignId('subject_id')->constrained('mapel')->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained('kelas')->cascadeOnDelete();
            $table->string('name', 200); // nama asesmen / kuis / ulangan harian
            $table->date('assessment_date');
            $table->text('description')->nullable();
            $table->string('legacy_id', 50)->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formative_assessments');
    }
};
