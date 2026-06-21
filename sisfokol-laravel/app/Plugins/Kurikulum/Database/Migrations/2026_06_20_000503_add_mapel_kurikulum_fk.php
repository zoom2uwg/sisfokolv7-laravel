<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mapel', function (Blueprint $table) {
            $table->foreign('kurikulum_id')->references('id')->on('kurikulum')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mapel', function (Blueprint $table) {
            $table->dropForeign(['kurikulum_id']);
        });
    }
};
