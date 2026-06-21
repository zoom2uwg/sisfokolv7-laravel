<?php

use App\Modules\Auth\Controllers\AuthController;
use App\Modules\Auth\Controllers\PasswordResetController;
use App\Modules\Auth\Controllers\ImpersonationController;
use App\Modules\Auth\Controllers\DashboardController;
use App\Modules\Auth\Controllers\AuditLogController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    });

    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('/password/change', [PasswordResetController::class, 'show'])->name('password.change');
        Route::post('/password/change', [PasswordResetController::class, 'store'])->name('password.change.store');

        Route::post('/impersonate/{target}/start', [ImpersonationController::class, 'start'])->name('impersonate.start');
        Route::post('/impersonate/stop', [ImpersonationController::class, 'stop'])->name('impersonate.stop');

        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.index');

        // RBAC Builder Routes
        Route::prefix('admin/rbac')->group(function () {
            Route::get('/', [\App\Modules\Auth\Controllers\RbacRoleController::class, 'index'])->name('rbac.index');
            Route::post('/role/{roleId}/permissions', [\App\Modules\Auth\Controllers\RbacRoleController::class, 'syncPermissions'])->name('rbac.role.permissions');
            Route::get('/menus', [\App\Modules\Auth\Controllers\RbacMenuController::class, 'index'])->name('rbac.menus');
            Route::post('/menus', [\App\Modules\Auth\Controllers\RbacMenuController::class, 'update'])->name('rbac.menus.update');
            Route::get('/fields', [\App\Modules\Auth\Controllers\RbacFieldController::class, 'index'])->name('rbac.fields');
            Route::post('/fields', [\App\Modules\Auth\Controllers\RbacFieldController::class, 'update'])->name('rbac.fields.update');
        });

        Route::prefix('admin/user-roles')->group(function () {
            Route::get('/', [\App\Modules\Auth\Controllers\RbacUserController::class, 'index'])->name('rbac.users');
            Route::post('/{user}/roles', [\App\Modules\Auth\Controllers\RbacUserController::class, 'assignRole'])->name('rbac.users.roles');
        });
    });
});
