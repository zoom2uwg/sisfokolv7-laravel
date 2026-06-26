<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('struktur_kurikulum', function (Blueprint $table) {
            $table->id();
            tenant_and_audit_columns($table);
            $table->unsignedBigInteger('kurikulum_id');
            $table->foreign('kurikulum_id')->references('id')->on('kurikulum')->cascadeOnDelete();
            $table->string('jenjang', 10);                // 'SD','SMP','SMA'
            $table->string('kelas', 10);                  // '7','8','9'
            $table->string('fase', 5)->nullable();        // 'A'-'F' for Kurmer
            $table->enum('jenis_kegiatan', ['intrakurikuler', 'kokurikuler', 'ekstrakurikuler'])->default('intrakurikuler');
            $table->timestamps();
            $table->unique(['tenant_id', 'kurikulum_id', 'jenjang', 'kelas', 'jenis_kegiatan'], 'uniq_struktur_kur');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('struktur_kurikulum');
    }
};
