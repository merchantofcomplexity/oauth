<?php

namespace MerchantOfComplexityTest\Oauth\Unit\Firewall;

use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use MerchantOfComplexity\Authters\Support\Contract\Domain\Identity;
use MerchantOfComplexity\Authters\Support\Contract\Domain\IdentityProvider;
use MerchantOfComplexity\Authters\Support\Contract\Firewall\Key\ContextKey;
use MerchantOfComplexity\Authters\Support\Contract\Guard\Authentication\Tokenable;
use MerchantOfComplexity\Authters\Support\Contract\Value\IdentifierValue;
use MerchantOfComplexity\Oauth\Firewall\OauthToken;
use MerchantOfComplexity\Oauth\Firewall\ProvideOauthAuthentication;
use MerchantOfComplexity\Oauth\Support\Contracts\Firewall\OauthToken as BaseOauthToken;
use MerchantOfComplexityTest\Oauth\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class ProvideOauthAuthenticationTest extends TestCase
{
    /**
     * @test
     * @expectedException \MerchantOfComplexity\Authters\Support\Exception\AuthenticationServiceFailure
     */
    public function it_raise_exception_when_token_is_not_an_instance_of_oauth_token(): void
    {
        $auth = $this->authenticationProviderInstance();

        $token = $this->prophesize(Tokenable::class);

        $this->key->sameValueAs($this->key)->willReturn(true);
        $token->getFirewallKey()->willReturn($this->key);

        $auth->authenticate($token->reveal());
    }

    /**
     * @test
     * @expectedException \MerchantOfComplexity\Authters\Support\Exception\AuthenticationException
     */
    public function it_raise_exception_when_oauth_request_is_invalid(): void
    {
        $auth = $this->authenticationProviderInstance();

        $request = $this->prophesize(ServerRequestInterface::class);
        $token = $this->prophesize(BaseOauthToken::class);

        $token->getServerRequest()->willReturn($request);

        $this->server->validateAuthenticatedRequest($request)->willThrow(
            new OAuthServerException('message', 401, 'error_type')
        );

        $request->reveal();

        $auth->authenticate($token->reveal());
    }

    /**
     * @test
     */
    public function it_authenticate_token(): void
    {
        $auth = $this->authenticationProviderInstance();

        $request = $this->prophesize(ServerRequestInterface::class);
        $token = $this->prophesize(BaseOauthToken::class);
        $requestValidated = $this->prophesize(ServerRequestInterface::class);
        $identity = $this->prophesize(Identity::class);

        $token->getServerRequest()->willReturn($request);

        $requestValidated->getAttribute('oauth_user_id')->willReturn('foo_bar');
        $requestValidated->getAttribute('oauth_scopes',[])->willReturn([]);

        $this->server->validateAuthenticatedRequest($request)->willReturn($requestValidated);

        $identity->getRoles()->willReturn([]);
        $this->provider->requireIdentityOfIdentifier($this->identifier)->willReturn($identity->reveal());

        $request->reveal();
        $requestValidated->reveal();

        $authToken = $auth->authenticate($token->reveal());

        $this->assertInstanceOf(BaseOauthToken::class, $authToken);
        $this->assertInstanceOf(OauthToken::class, $authToken);

        $this->assertTrue($authToken->isAuthenticated());
    }

    /**
     * @test
     */
    public function it_check_token_is_supported(): void
    {
        $auth = $this->authenticationProviderInstance();

        $token = $this->prophesize(BaseOauthToken::class);

        $this->key->sameValueAs($this->key)->willReturn(true);
        $token->getFirewallKey()->willReturn($this->key);

        $this->assertTrue($auth->supportToken($token->reveal()));
    }

    protected function authenticationProviderInstance(): ProvideOauthAuthentication
    {
        $provider = $this->provider;
        $server = $this->server;
        $key = $this->key;
        $identifier = $this->identifier;

        return new class($provider, $server, $key, $identifier) extends ProvideOauthAuthentication
        {
            private $identifier;

            public function __construct($provider, $server, $key, $identifier)
            {
                parent::__construct($provider->reveal(), $server->reveal(), $key->reveal());

                $this->identifier = $identifier->reveal();
            }

            protected function retrieveIdentity(string $oauthUserId): ?Identity
            {
                return $this->identityProvider->requireIdentityOfIdentifier($this->identifier);
            }
        };
    }

    private $provider;
    private $server;
    private $key;
    private $identifier;

    protected function setUp(): void
    {
        $this->provider = $this->prophesize(IdentityProvider::class);
        $this->server = $this->prophesize(ResourceServer::class);
        $this->key = $this->prophesize(ContextKey::class);
        $this->identifier = $this->prophesize(IdentifierValue::class);
    }
}