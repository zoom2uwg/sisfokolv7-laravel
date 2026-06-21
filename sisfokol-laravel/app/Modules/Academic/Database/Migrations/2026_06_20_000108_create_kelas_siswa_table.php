<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kelas_siswa', function (Blueprint $table) {
            $table->id();
            tenant_and_audit_columns($table, withSoftDelete: false);
            $table->unsignedBigInteger('kelas_id');
            $table->foreign('kelas_id')->references('id')->on('kelas')->cascadeOnDelete();
            $table->unsignedBigInteger('siswa_id');
            $table->foreign('siswa_id')->references('id')->on('siswa')->cascadeOnDelete();
            $table->unsignedBigInteger('tahun_ajaran_id');
            $table->foreign('tahun_ajaran_id')->references('id')->on('tahun_ajaran')->cascadeOnDelete();
            $table->unsignedSmallInteger('no_urut')->default(1);
            $table->timestamps();
            $table->unique(['tenant_id', 'tahun_ajaran_id', 'kelas_id', 'siswa_id']);
            $table->index(['tenant_id', 'tahun_ajaran_id', 'kelas_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('kelas_siswa'); }
};
