<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orang_tua', function (Blueprint $table) {
            $table->id();
            tenant_and_audit_columns($table);
            $table->string('nama', 100);
            $table->enum('hubungan', ['ayah', 'ibu', 'wali'])->default('ayah');
            $table->string('telepon', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('pekerjaan', 100)->nullable();
            $table->text('alamat')->nullable();
            $table->string('username', 50)->nullable();
            $table->string('password')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'username']);
        });
    }
    public function down(): void { Schema::dropIfExists('orang_tua'); }
};
