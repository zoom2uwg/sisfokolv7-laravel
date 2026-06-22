<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('siswa')->cascadeOnDelete();
            $table->date('date');
            $table->string('status', 20)->default('present'); // present, absent, sick, permit
            $table->text('note')->nullable();
            $table->string('legacy_id', 50)->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['schedule_id', 'student_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_attendances');
    }
};
