<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('tahun_ajaran')->restrictOnDelete();
            $table->foreignId('classroom_id')->constrained('kelas')->restrictOnDelete();
            $table->foreignId('subject_id')->constrained('mapel')->restrictOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete(); // teacher
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->foreignId('day_id')->constrained('days')->restrictOnDelete();
            $table->foreignId('time_slot_id')->constrained('time_slots')->restrictOnDelete();
            $table->string('week_type', 20)->default('all'); // all, odd, even (ganjil/genap)
            $table->text('description')->nullable();
            $table->string('legacy_id', 50)->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['academic_year_id', 'classroom_id', 'day_id', 'time_slot_id'], 'schedule_unique_slot');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
