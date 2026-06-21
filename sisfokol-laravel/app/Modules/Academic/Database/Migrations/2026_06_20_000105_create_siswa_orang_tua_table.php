<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('siswa_orang_tua', function (Blueprint $table) {
            $table->id();
            tenant_and_audit_columns($table, withSoftDelete: false);
            $table->unsignedBigInteger('siswa_id');
            $table->foreign('siswa_id')->references('id')->on('siswa')->cascadeOnDelete();
            $table->unsignedBigInteger('orang_tua_id');
            $table->foreign('orang_tua_id')->references('id')->on('orang_tua')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['tenant_id', 'siswa_id', 'orang_tua_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('siswa_orang_tua'); }
};
