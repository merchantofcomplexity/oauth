<?php

namespace MerchantOfComplexity\Oauth\Providers;

use DateInterval;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
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
use MerchantOfComplexity\Oauth\Infrastructure\AccessToken\AccessTokenProvider;
use MerchantOfComplexity\Oauth\League\Repository\AccessTokenRepository;
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
        AccessTokenRepositoryInterface::class => AccessTokenProvider::class,
        AuthCodeRepositoryInterface::class => AccessTokenRepository::class,
        RefreshTokenRepositoryInterface::class => RefreshTokenRepository::class,
        ScopeRepositoryInterface::class => ScopeRepository::class,
    ];

    public function register(): void
    {
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
     * @throws \Exception
     */
    protected function registerAuthorizationServer(): void
    {
        $config = config('oauth');

        $key = $config->get('authorization_server.private_key');

        if ($passphrase = $config->get('authorization_server.private_key')) {
            $key = new CryptKey($key, $passphrase);
        }

        $authorizationServer = new AuthorizationServer(
            $this->app->get(ClientRepositoryInterface::class),
            $this->app->get(AccessTokenRepositoryInterface::class),
            $this->app->get(ScopeRepositoryInterface::class),
            $key,
            $config->get('authorization_server.encryption_key'),
        );

        $refreshTtl = new DateInterval($config->get('authorization_server.refresh_token_ttl'));
        $accessTtl = new DateInterval($config->get('authorization_server.access_token_ttl'));
        $authCodeTTl = new DateInterval($config->get('authorization_server.auth_code_ttl'));

        $this->enableAuthorizationCodeGrant($authorizationServer, $authCodeTTl, $refreshTtl, $accessTtl);
        $this->enableRefreshTokenGrant($authorizationServer, $refreshTtl, $accessTtl);
        $this->enableClientCredentialsGrant($authorizationServer, $accessTtl);
        $this->enableImplicitGrant($authorizationServer, $accessTtl);
        $this->enablePasswordGrant($authorizationServer, $refreshTtl, $accessTtl);

        $this->app->instance(AuthorizationServer::class, $authorizationServer);
    }

    protected function registerResourceServer(): void
    {
        $config = config('oauth');

        $publicKey = $config->get('resource_server.public_key');

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

    public function provides()
    {
        return array_merge(array_keys($this->bindings), [
            HttpMessageFactoryInterface::class,
            AuthorizationServer::class,
            ResourceServer::class
        ]);
    }

}