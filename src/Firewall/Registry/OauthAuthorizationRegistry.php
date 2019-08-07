<?php

namespace MerchantOfComplexity\Oauth\Firewall\Registry;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use MerchantOfComplexity\Authters\Support\Contract\Firewall\FirewallRegistry;
use MerchantOfComplexity\Authters\Support\Firewall\FirewallAware;
use MerchantOfComplexity\Oauth\Http\Middleware\OauthAuthorization;
use MerchantOfComplexity\Oauth\Http\Middleware\OauthAuthorizationConfirmed;
use MerchantOfComplexity\Oauth\Http\Middleware\OauthAuthorizationDenied;

class OauthAuthorizationRegistry implements FirewallRegistry
{
    public function compose(FirewallAware $firewall, Closure $make)
    {
        if ($firewall->context()->contextKey()->getValue() === 'oauth') {
            $firewall->addPreService('oauth-authorize-confirmed',
                function (Application $app) {
                    return $app->get(OauthAuthorizationConfirmed::class);
                });

            $firewall->addPreService('oauth-authorize-denied',
                function (Application $app) {
                    return $app->get(OauthAuthorizationDenied::class);
                });

            // must be last
            $firewall->addPreService('oauth-authorize',
                function (Application $app) {
                    return $app->get(OauthAuthorization::class);
                });
        }

        return $make($firewall);
    }
}