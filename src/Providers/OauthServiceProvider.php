<?php

namespace MerchantOfComplexity\Oauth\Providers;

use Illuminate\Support\AggregateServiceProvider;

class OauthServiceProvider extends AggregateServiceProvider
{
    protected $providers = [
        ConfigServiceProvider::class,
        OauthServerServiceProvider::class
    ];
}