<?php

namespace MerchantOfComplexity\Oauth\Providers;

use DateInterval;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use League\Event\Emitter;
use League\Event\EmitterInterface;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\ImplicitGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use League\OAuth2\Server\ResourceServer;
use MerchantOfComplexity\Oauth\Infrastructure\Scope\ScopeModel;
use MerchantOfComplexity\Oauth\Infrastructure\Scope\ScopeProvider;
use MerchantOfComplexity\Oauth\League\Repository\AccessTokenRepository;
use MerchantOfComplexity\Oauth\League\Repository\AuthCodeRepository;
use MerchantOfComplexity\Oauth\League\Repository\ClientRepository;
use MerchantOfComplexity\Oauth\League\Repository\IdentityRepository;
use MerchantOfComplexity\Oauth\League\Repository\RefreshTokenRepository;
use MerchantOfComplexity\Oauth\League\Repository\ScopeRepository;
use MerchantOfComplexity\Oauth\Support\Contracts\Transformer\OauthUserTransformer as BaseOauthUserTransformer;
use MerchantOfComplexity\Oauth\Support\Contracts\Transformer\ScopeTransformer as BaseScopeTransformer;
use MerchantOfComplexity\Oauth\Support\Transformer\OauthUserTransformer;
use MerchantOfComplexity\Oauth\Support\Transformer\ScopeTransformer;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;

class OauthServerServiceProvider extends ServiceProvider
{
    /**
     * @var bool
     */
    protected $defer = true;

    /**
     * @var array
     */
    public $bindings = [
        BaseScopeTransformer::class => ScopeTransformer::class,
        BaseOauthUserTransformer::class => OauthUserTransformer::class,
        ClientRepositoryInterface::class => ClientRepository::class,
        UserRepositoryInterface::class => IdentityRepository::class,
        AccessTokenRepositoryInterface::class => AccessTokenRepository::class,
        AuthCodeRepositoryInterface::class => AuthCodeRepository::class,
        RefreshTokenRepositoryInterface::class => RefreshTokenRepository::class,
        ScopeRepositoryInterface::class => ScopeRepository::class,
    ];

    public function register(): void
    {
        if (!$this->app->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__ . '/../../config/oauth.php', 'oauth');
        }

        foreach ($this->bindings as $abstract => $concrete) {
            $this->app->bindIf($abstract, $concrete);
        }

        // inMemory ScopeProvider
        $this->app->singleton(ScopeProvider::class, function () {
            $scopes = config('oauth.scopes', []);

            $scopeProvider = new ScopeProvider();

            foreach ($scopes as $scope) {
                $scopeProvider->store(new ScopeModel($scope));
            }

            return $scopeProvider;
        });

        $this->registerHttpMessageFactory();

        $this->registerAuthorizationServer();

        $this->registerResourceServer();
    }

    protected function registerHttpMessageFactory(): void
    {
        $this->app->bind(HttpMessageFactoryInterface::class, function (): HttpMessageFactoryInterface {
            $psr17Factory = new Psr17Factory();

            return new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
        });
    }

    /**
     * @throws Exception
     */
    protected function registerAuthorizationServer(): void
    {
        $key = config('oauth.authorization_server.private_key');

        if ($passphrase = config('oauth.authorization_server.private_key')) {
            $key = new CryptKey($key, $passphrase);
        }

        $authorizationServer = new AuthorizationServer(
            $this->app->get(ClientRepositoryInterface::class),
            $this->app->get(AccessTokenRepositoryInterface::class),
            $this->app->get(ScopeRepositoryInterface::class),
            $key,
            config('oauth.authorization_server.encryption_key')
        );

        $authorizationServer->setEmitter($this->getEmitterInstance());

        $refreshTtl = new DateInterval(config('oauth.authorization_server.refresh_token_ttl'));
        $accessTtl = new DateInterval(config('oauth.authorization_server.access_token_ttl'));
        $authCodeTTl = new DateInterval(config('oauth.authorization_server.auth_code_ttl'));

        $this->enableAuthorizationCodeGrant($authorizationServer, $authCodeTTl, $refreshTtl, $accessTtl);

        $this->enableImplicitGrant($authorizationServer, $accessTtl);

        if (true === config('oauth.authorization_server.enable_grants.refresh_token')) {
            $this->enableRefreshTokenGrant($authorizationServer, $refreshTtl, $accessTtl);
        }

        if (true === config('oauth.authorization_server.enable_grants.client_credentials')) {
            $this->enableClientCredentialsGrant($authorizationServer, $accessTtl);
        }

        if (true === config('oauth.authorization_server.enable_grants.password')) {
            $this->enablePasswordGrant($authorizationServer, $refreshTtl, $accessTtl);
        }

        $this->app->instance(AuthorizationServer::class, $authorizationServer);
    }

    protected function registerResourceServer(): void
    {
        $publicKey = config('oauth.resource_server.public_key');

        $this->app->bind(ResourceServer::class, function (Application $app) use ($publicKey) {
            return new ResourceServer(
                $app->get(AccessTokenRepositoryInterface::class),
                $publicKey
            );
        });
    }

    protected function enableRefreshTokenGrant(AuthorizationServer $server,
                                               DateInterval $refreshTtl,
                                               DateInterval $accessTtl): void
    {
        $grant = new RefreshTokenGrant($this->app->get(RefreshTokenRepositoryInterface::class));

        $grant->setRefreshTokenTTL($refreshTtl);

        $server->enableGrantType($grant, $accessTtl);
    }

    protected function enableAuthorizationCodeGrant(AuthorizationServer $server,
                                                    DateInterval $authCodeTtl,
                                                    DateInterval $refreshTtl,
                                                    DateInterval $accessTtl): void
    {
        $grant = new AuthCodeGrant(
            $this->app->get(AuthCodeRepositoryInterface::class),
            $this->app->get(RefreshTokenRepositoryInterface::class),
            $authCodeTtl
        );

        $grant->setRefreshTokenTTL($refreshTtl);

        $server->enableGrantType($grant, $accessTtl);
    }

    protected function enablePasswordGrant(AuthorizationServer $server, DateInterval $refreshTtl, DateInterval $accessTtl): void
    {
        $grant = $this->app->get(PasswordGrant::class);

        $grant->setRefreshTokenTTL($refreshTtl);

        $server->enableGrantType($grant, $accessTtl);
    }

    protected function enableImplicitGrant(AuthorizationServer $server, DateInterval $accessTtl): void
    {
        $server->enableGrantType(new ImplicitGrant($accessTtl), $accessTtl);
    }

    protected function enableClientCredentialsGrant(AuthorizationServer $server, DateInterval $accessTtl): void
    {
        $server->enableGrantType(new ClientCredentialsGrant(), $accessTtl);
    }

    protected function getEmitterInstance(): EmitterInterface
    {
        $emitter = new Emitter();

        $events = config('oauth.listeners');

        foreach ($events as $event) {
            foreach ($event as $eventName => $listenerClass) {
                $emitter->addListener($eventName, $listenerClass);
            }
        }

        return $emitter;
    }

    public function provides()
    {
        return array_merge(array_keys($this->bindings), [
            HttpMessageFactoryInterface::class,
            AuthorizationServer::class,
            ResourceServer::class
        ]);
    }
}