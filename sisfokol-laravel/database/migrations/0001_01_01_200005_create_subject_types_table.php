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
            
            // tenancy (nullable for global records)
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();

            // Indonesian modular columns
            $table->string('nama', 50)->nullable();            // 'Wajib','Muatan Lokal','Peminatan'
            $table->string('kode', 30)->nullable();

            // English legacy columns
            $table->string('code', 50)->nullable();
            $table->string('name', 100)->nullable();
            $table->text('description')->nullable();

            // audit columns
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'nama']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mapel_jenis');
    }
};
