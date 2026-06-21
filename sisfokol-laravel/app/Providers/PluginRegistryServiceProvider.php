<?php

namespace App\Providers;

use App\Support\PluginRegistry;
use Illuminate\Support\ServiceProvider;

class PluginRegistryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PluginRegistry::class, function ($app) {
            $registry = new PluginRegistry();
            $registry->rescan();
            $registry->syncToDatabase();
            return $registry;
        });
    }

    public function boot(): void
    {
        $registry = $this->app->make(PluginRegistry::class);
        foreach ($registry->all() as $plugin) {
            $provider = $plugin->providerClass();
            if ($provider && class_exists($provider)) {
                $this->app->register($provider);
            }
        }
    }
}
