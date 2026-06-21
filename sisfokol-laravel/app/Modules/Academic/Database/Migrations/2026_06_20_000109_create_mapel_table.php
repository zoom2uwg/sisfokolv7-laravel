<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mapel', function (Blueprint $table) {
            $table->id();
            tenant_and_audit_columns($table);
            $table->string('kode', 30);
            $table->string('nama', 100);
            $table->unsignedBigInteger('mapel_jenis_id')->nullable();
            $table->foreign('mapel_jenis_id')->references('id')->on('mapel_jenis')->nullOnDelete();
            $table->decimal('kkm', 5, 2)->default(75.00);
            $table->unsignedBigInteger('kurikulum_id')->nullable();  // FK added in kurikulum plugin Epic 9
            $table->string('jenjang', 10)->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'kode']);
        });
    }
    public function down(): void { Schema::dropIfExists('mapel'); }
};
