<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('komponen_kompetensi', function (Blueprint $table) {
            $table->id();
            tenant_and_audit_columns($table);
            $table->unsignedBigInteger('struktur_id');
            $table->foreign('struktur_id')->references('id')->on('struktur_kurikulum')->cascadeOnDelete();
            $table->string('kode_kompetensi', 30);          // 'KI-3','CP-001'
            $table->text('teks_kompetensi');
            $table->enum('pendekatan_pedagogis', ['konvensional', 'deep_learning'])->default('konvensional');
            $table->timestamps();
            $table->index(['tenant_id', 'struktur_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('komponen_kompetensi');
    }
};
