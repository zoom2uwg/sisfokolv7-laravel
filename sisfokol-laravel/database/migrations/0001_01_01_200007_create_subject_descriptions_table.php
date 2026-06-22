<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_descriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('mapel')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('tahun_ajaran')->restrictOnDelete();
            $table->string('category', 50)->nullable(); // pengetahuan, keterampilan, sikap
            $table->text('description');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_descriptions');
    }
};
