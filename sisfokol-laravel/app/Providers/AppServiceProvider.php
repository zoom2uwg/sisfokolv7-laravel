<?php

namespace App\Providers;

use App\Support\TenantContext;
use App\Modules\Auth\Models\AuditLog;
use App\Modules\Auth\Policies\AuditLogPolicy;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantContext::class, function () {
            return new TenantContext();
        });
        $this->app->singleton(\App\Modules\Auth\Services\AuditLogger::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set default locale Indonesia
        setlocale(LC_TIME, 'id_ID.utf8', 'id_ID', 'id');

        // Register User observer
        \App\Models\User::observe(\App\Modules\Auth\Observers\UserObserver::class);

        // Register Policy
        Gate::policy(AuditLog::class, AuditLogPolicy::class);

        Blueprint::macro('tenantAndAuditColumns', function (bool $withSoftDelete = true) {
            tenant_and_audit_columns($this, $withSoftDelete);
        });

        Blueprint::macro('auditColumns', function (bool $withSoftDelete = true) {
            audit_columns($this, $withSoftDelete);
        });
    }
}
