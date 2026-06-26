<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table classrooms is merged into kelas table in app/Modules/Academic/Database/Migrations/0001_01_01_200013_create_kelas_table.php
    }

    public function down(): void
    {
        //
    }
};
