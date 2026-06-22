<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->foreignId('academic_year_id')->constrained('tahun_ajaran')->restrictOnDelete();
            $table->foreignId('classroom_id')->constrained('kelas')->restrictOnDelete();
            $table->string('nis', 100)->unique(); // Nomor Induk Siswa
            $table->string('nisn', 100)->nullable()->unique(); // Nomor Induk Siswa Nasional
            $table->string('name', 200);
            $table->string('gender', 20)->nullable(); // L/P
            $table->string('birth_place', 100)->nullable();
            $table->date('birth_date')->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('father_name', 200)->nullable();
            $table->string('mother_name', 200)->nullable();
            $table->string('parent_phone', 50)->nullable();
            $table->string('photo_path', 255)->nullable();
            $table->string('qrcode_path', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('legacy_id', 50)->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
