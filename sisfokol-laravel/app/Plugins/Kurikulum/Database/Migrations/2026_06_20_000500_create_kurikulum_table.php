<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kurikulum', function (Blueprint $table) {
            $table->id();
            tenant_and_audit_columns($table);
            $table->string('kurikulum_id', 30);          // 'K13','KURMER','MULOK'
            $table->string('nama_kurikulum', 100);
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'kurikulum_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kurikulum');
    }
};
