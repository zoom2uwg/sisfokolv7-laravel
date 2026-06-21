<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('guru', function (Blueprint $table) {
            $table->id();
            tenant_and_audit_columns($table);
            $table->string('nip', 30)->nullable();
            $table->string('nama', 100);
            $table->enum('jenis_kelamin', ['L', 'P'])->default('L');
            $table->string('telepon', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('jabatan', 100)->nullable();
            $table->string('foto')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'nip']);
        });
    }
    public function down(): void { Schema::dropIfExists('guru'); }
};
