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
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\ResourceServer;
use MerchantOfComplexity\Oauth\Infrastructure\Models\AccessTokenModel;
use MerchantOfComplexity\Oauth\Infrastructure\Models\AuthCodeModel;
use MerchantOfComplexity\Oauth\Infrastructure\Models\ClientModel;
use MerchantOfComplexity\Oauth\Infrastructure\Models\RefreshTokenModel;
use MerchantOfComplexity\Oauth\Infrastructure\Models\ScopeModel;
use MerchantOfComplexity\Oauth\Infrastructure\Providers\AccessTokenProvider;
use MerchantOfComplexity\Oauth\Infrastructure\Providers\AuthCodeProvider;
use MerchantOfComplexity\Oauth\Infrastructure\Providers\ClientProvider;
use MerchantOfComplexity\Oauth\Infrastructure\RefreshToken\RefreshTokenProvider;
use MerchantOfComplexity\Oauth\Infrastructure\Scope\ScopeProvider;
use MerchantOfComplexity\Oauth\League\Repository\AccessTokenRepository;
use MerchantOfComplexity\Oauth\League\Repository\AuthCodeRepository;
use MerchantOfComplexity\Oauth\League\Repository\ClientRepository;
use MerchantOfComplexity\Oauth\League\Repository\RefreshTokenRepository;
use MerchantOfComplexity\Oauth\League\Repository\ScopeRepository;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\AccessTokenInterface;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\AuthorizationCodeInterface;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\ClientInterface;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\Eloquent\WithAccessToken;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\Eloquent\WithAuthorizationCode;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\Eloquent\WithClient;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\Eloquent\WithRefreshToken;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\RefreshTokenInterface;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\ScopeInterface;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers\ProvideAccessToken;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers\ProvideAuthCode;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers\ProvideClient;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers\ProvideRefreshToken;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers\ProvideScope;
use MerchantOfComplexity\Oauth\Support\Contracts\League\Repository\ClientRepositoryInterface;
use MerchantOfComplexity\Oauth\Support\Contracts\Transformer\OauthUserTransformer as BaseOauthUserTransformer;
use MerchantOfComplexity\Oauth\Support\Contracts\Transformer\ScopeTransformer as BaseScopeTransformer;
use MerchantOfComplexity\Oauth\Support\Transformer\OauthUserTransformer;
use MerchantOfComplexity\Oauth\Support\Transformer\ScopeTransformer;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;

class OauthServerServiceProvider extends ServiceProvider
{
    /**
     * @var bool
     */
    protected $defer = true;

    protected $models = [
        AccessTokenInterface::class => AccessTokenModel::class,
        AuthorizationCodeInterface::class => AuthCodeModel::class,
        ClientInterface::class => ClientModel::class,
        RefreshTokenInterface::class => RefreshTokenModel::class,
        ScopeInterface::class => ScopeModel::class,
    ];

    protected $providers = [
        ProvideClient::class => ClientProvider::class,
        ProvideAccessToken::class => AccessTokenProvider::class,
        ProvideRefreshToken::class => RefreshTokenProvider::class,
        ProvideAuthCode::class => AuthCodeProvider::class,
        //ProvideScope::class => ScopeProvider::class,
    ];

    protected $repositories = [
        ClientRepositoryInterface::class => ClientRepository::class,
        //UserRepositoryInterface::class => IdentityRepository::class,
        AccessTokenRepositoryInterface::class => AccessTokenRepository::class,
        AuthCodeRepositoryInterface::class => AuthCodeRepository::class,
        RefreshTokenRepositoryInterface::class => RefreshTokenRepository::class,
        ScopeRepositoryInterface::class => ScopeRepository::class,
    ];

    /**
     * @var array
     */
    public $bindings = [
        BaseScopeTransformer::class => ScopeTransformer::class,
        BaseOauthUserTransformer::class => OauthUserTransformer::class,
    ];

    /**
     * @throws Exception
     */
    public function register(): void
    {
        if (!$this->app->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__ . '/../../config/oauth.php', 'oauth');
        }

        $this->registerFactories();

        $this->registerInMemoryScopeProvider();

        $this->registerAuthorizationServer();

        $this->registerResourceServer();
    }

    protected function registerFactories(): void
    {
        foreach ($this->bindings as $abstract => $concrete) {
            $this->app->bindIf($abstract, $concrete);
        }

        foreach ($this->models as $abstract => $concrete) {
            $this->app->bindIf($abstract, $concrete);
        }

        // for eloquent
        $this->app->alias(AccessTokenInterface::class, WithAccessToken::class);
        $this->app->alias(AuthorizationCodeInterface::class, WithAuthorizationCode::class);
        $this->app->alias(RefreshTokenInterface::class, WithRefreshToken::class);
        $this->app->alias(ClientInterface::class, WithClient::class);

        foreach ($this->providers as $abstract => $concrete) {
            $this->app->bindIf($abstract, $concrete);
        }

        foreach ($this->repositories as $abstract => $concrete) {
            $this->app->bindIf($abstract, $concrete);
        }
    }

    protected function registerInMemoryScopeProvider(): void
    {
        // todo config
        $this->app->singleton(ProvideScope::class, function () {
            $scopes = config('oauth.scopes', []);

            $scopeProvider = new ScopeProvider();

            foreach ($scopes as $scope) {
                $scopeProvider->store(new ScopeModel($scope));
            }

            return $scopeProvider;
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

    /**
     * @param AuthorizationServer $server
     * @param DateInterval $authCodeTtl
     * @param DateInterval $refreshTtl
     * @param DateInterval $accessTtl
     * @throws Exception
     */
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

        foreach ($events as $eventName => $listeners) {
            foreach ($listeners as $listener) {
                $emitter->addListener($eventName, $this->app->get($listener));
            }
        }

        return $emitter;
    }

    public function boot(): void
    {
        $fqcnIdentityModel = config('oauth.auth.identity_model');

        if (class_exists($fqcnIdentityModel)) {
            foreach ([AuthCodeModel::class, AccessTokenModel::class, ClientModel::class] as $model) {
                $model::$identityModel = $fqcnIdentityModel;
            }
        }
    }

    public function provides(): array
    {
        return array_merge(
            array_keys($this->bindings), array_keys($this->providers), array_keys($this->repositories),
            [
                WithClient::class, WithAccessToken::class,
                WithAuthorizationCode::class, WithRefreshToken::class,
                ProvideScope::class,
                HttpMessageFactoryInterface::class,
                AuthorizationServer::class,
                ResourceServer::class,
            ]);
    }
}