<?php

namespace Tests\Unit\Auth;

use App\Models\User;
use App\Modules\Auth\Services\AuditLogger;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class AuditLoggerTest extends TestCase
{
    use RefreshDatabase;

    public function test_log_creates_audit_log_entry(): void
    {
        $tenant = Tenant::create(['nama' => 'T1', 'npsn' => '11111111']);
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $request = Request::create('/test', 'POST');
        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        app(AuditLogger::class)->log('test.event', $user, ['foo' => 'bar'], $request);

        $this->assertDatabaseHas('audit_logs', [
            'tenant_id' => $tenant->id,
            'user_id'   => $user->id,
            'event'     => 'test.event',
        ]);
    }

    public function test_log_stores_old_and_new_values_as_json(): void
    {
        $user = User::factory()->create(['tenant_id' => null]);
        $request = Request::create('/t', 'POST');

        app(AuditLogger::class)->log('e', $user, ['new' => 1], $request, ['old' => 2]);

        $log = \App\Modules\Auth\Models\AuditLog::where('event', 'e')->first();
        $this->assertEquals(['new' => 1], $log->new_values);
        $this->assertEquals(['old' => 2], $log->old_values);
    }
}
