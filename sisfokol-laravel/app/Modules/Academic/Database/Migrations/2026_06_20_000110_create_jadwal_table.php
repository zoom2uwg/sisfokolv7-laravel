<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jadwal', function (Blueprint $table) {
            $table->id();
            tenant_and_audit_columns($table);
            $table->unsignedBigInteger('tahun_ajaran_id');
            $table->foreign('tahun_ajaran_id')->references('id')->on('tahun_ajaran')->cascadeOnDelete();
            $table->unsignedBigInteger('semester_id');
            $table->foreign('semester_id')->references('id')->on('semester')->cascadeOnDelete();
            $table->unsignedBigInteger('kelas_id');
            $table->foreign('kelas_id')->references('id')->on('kelas')->cascadeOnDelete();
            $table->unsignedBigInteger('mapel_id');
            $table->foreign('mapel_id')->references('id')->on('mapel')->cascadeOnDelete();
            $table->unsignedBigInteger('guru_id');
            $table->foreign('guru_id')->references('id')->on('guru')->cascadeOnDelete();
            $table->tinyInteger('hari');             // 1=Senin..7=Minggu
            $table->tinyInteger('jam_ke');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('ruang', 30)->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'tahun_ajaran_id', 'semester_id', 'kelas_id', 'hari', 'jam_ke'], 'uniq_jadwal_kelas_slot');
            $table->unique(['tenant_id', 'tahun_ajaran_id', 'semester_id', 'guru_id', 'hari', 'jam_ke'], 'uniq_jadwal_guru_slot');
            $table->index(['tenant_id', 'tahun_ajaran_id', 'semester_id', 'kelas_id', 'hari']);
        });
    }
    public function down(): void { Schema::dropIfExists('jadwal'); }
};
