<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            tenant_and_audit_columns($table);
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->unsignedBigInteger('wali_kelas_id')->nullable();
            $table->foreign('wali_kelas_id')->references('id')->on('guru')->nullOnDelete();
            $table->string('nama', 30);              // '7-A'
            $table->tinyInteger('tingkat');          // 7/8/9
            $table->unsignedSmallInteger('kapasitas')->default(32);
            $table->timestamps();
            $table->index(['tenant_id', 'tingkat']);
        });
    }
    public function down(): void { Schema::dropIfExists('kelas'); }
};
