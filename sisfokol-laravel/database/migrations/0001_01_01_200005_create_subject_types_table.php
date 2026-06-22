<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mapel_jenis', function (Blueprint $table) {
            $table->id();
            tenant_and_audit_columns($table);
            $table->string('nama', 50);            // 'Wajib','Muatan Lokal','Peminatan'
            $table->string('kode', 30)->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'nama']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mapel_jenis');
    }
};
