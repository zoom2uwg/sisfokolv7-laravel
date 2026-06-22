<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('tahun_ajaran')->restrictOnDelete();
            $table->string('name', 200); // SPP, Uang Pangkal, Ujian, dll
            $table->string('code', 50)->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('frequency', 50)->default('once'); // once, monthly, yearly
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('legacy_id', 50)->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_items');
    }
};
