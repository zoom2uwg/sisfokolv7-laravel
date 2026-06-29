<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// ADR-010 / ADR-003: menu level platform (SuperAdmin-only) ditandai eksplisit
// agar user tenant (tenant_id !== null) tidak dapat melihatnya, sekalipun
// role-nya memegang wildcard permission '*'. Mencegah kebocoran visual menu
// global platform (Tenants, Branches, RBAC Builder, Audit Log, Plugin).
return new class extends Migration {
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->boolean('is_platform')->default(false)->after('is_system');
        });

        // Backfill menu platform global yang sudah ada (idempoten untuk DB existing).
        DB::table('menus')->whereIn('kode', [
            'tenancy.tenants',
            'tenancy.branches',
            'auth.rbac',
            'auth.audit',
            'auth.plugins',
        ])->update(['is_platform' => true]);
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn('is_platform');
        });
    }
};
