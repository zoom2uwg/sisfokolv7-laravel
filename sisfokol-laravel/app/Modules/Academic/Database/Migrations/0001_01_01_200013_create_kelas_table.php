<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            
            // tenancy (nullable for global records)
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();

            // Indonesian modular columns
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->unsignedBigInteger('wali_kelas_id')->nullable();
            $table->foreign('wali_kelas_id')->references('id')->on('guru')->nullOnDelete();
            $table->string('nama', 30)->nullable();              // '7-A'
            $table->tinyInteger('tingkat')->nullable();          // 7/8/9
            $table->unsignedSmallInteger('kapasitas')->default(32);

            // English legacy columns (nullable)
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->string('name', 50)->nullable(); // e.g. X IPA 1
            $table->string('level', 20)->nullable(); // X, XI, XII
            $table->string('major', 100)->nullable(); // IPA, IPS, etc
            $table->integer('capacity')->default(30);
            $table->unsignedBigInteger('homeroom_teacher_id')->nullable();
            $table->text('description')->nullable();
            $table->string('legacy_id', 50)->nullable()->index();

            // audit columns
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'tingkat']);
        });
    }
    public function down(): void { Schema::dropIfExists('kelas'); }
};
