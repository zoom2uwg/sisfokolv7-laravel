<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mapel', function (Blueprint $table) {
            $table->id();
            
            // tenancy (nullable for global records)
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();

            // Indonesian modular columns
            $table->string('kode', 30)->nullable();
            $table->string('nama', 100)->nullable();
            $table->unsignedBigInteger('mapel_jenis_id')->nullable();
            $table->foreign('mapel_jenis_id')->references('id')->on('mapel_jenis')->nullOnDelete();
            $table->decimal('kkm', 5, 2)->default(75.00);
            $table->unsignedBigInteger('kurikulum_id')->nullable();  // FK added in kurikulum plugin Epic 9
            $table->string('jenjang', 10)->nullable();

            // English legacy columns
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->unsignedBigInteger('subject_type_id')->nullable();
            $table->string('code', 100)->nullable();
            $table->string('name', 200)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_exam')->default(false);
            $table->string('phase', 10)->nullable();
            $table->string('legacy_id', 50)->nullable()->index();

            // audit columns
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'kode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mapel');
    }
};
