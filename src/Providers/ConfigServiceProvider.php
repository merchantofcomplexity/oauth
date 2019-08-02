<?php

namespace MerchantOfComplexity\Oauth\Providers;

use Illuminate\Support\ServiceProvider;

class ConfigServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [$this->getConfigPath() => config_path('oauth.php')],
                'config'
            );
        }
    }

    protected function mergeConfig(): void
    {
        $this->mergeConfigFrom($this->getConfigPath(), 'oauth');
    }

    protected function getConfigPath(): string
    {
        return __DIR__ . '/../../config/oauth.php';
    }
}