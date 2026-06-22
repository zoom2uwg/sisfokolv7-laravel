<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homeroom_teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('classroom_id')->unique()->constrained('kelas')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['employee_id', 'classroom_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homeroom_teachers');
    }
};
