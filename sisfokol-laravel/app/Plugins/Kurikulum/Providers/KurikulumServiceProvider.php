<?php

namespace App\Plugins\Kurikulum\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Illuminate\Support\Facades\Route;

class KurikulumServiceProvider extends EventServiceProvider
{
    protected $subscribe = [
        \App\Plugins\Kurikulum\Subscribers\EvaluationFrameworkSubscriber::class,
        \App\Plugins\Kurikulum\Subscribers\RaporSectionSubscriber::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        parent::boot();

        // Load plugin-scoped views under namespace "kurikulum::"
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'kurikulum');

        // Load plugin routes
        $routesFile = __DIR__ . '/../routes.php';
        if (file_exists($routesFile)) {
            Route::middleware('web')->group($routesFile);
        }
    }
}

