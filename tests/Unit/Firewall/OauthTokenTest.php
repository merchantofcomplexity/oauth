<?php

namespace MerchantOfComplexityTest\Oauth\Unit\Firewall;

use MerchantOfComplexity\Authters\Support\Contract\Domain\Identity;
use MerchantOfComplexity\Authters\Support\Contract\Firewall\Key\ContextKey;
use MerchantOfComplexity\Oauth\Firewall\OauthToken;
use MerchantOfComplexity\Oauth\Support\Value\AccessTokenId;
use MerchantOfComplexityTest\Oauth\TestCase;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;

class OauthTokenTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_be_constructed(): void
    {
        $identity = $this->prophesize(Identity::class);
        $firewallKey = $this->prophesize(ContextKey::class);
        $psrRequest = $this->prophesize(ServerRequestInterface::class);

        $psrRequest->getAttribute('oauth_scopes', [])->willReturn([]);
        $psrRequest->getAttribute('oauth_access_token_id', null)->willReturn('bar');
        $identity->getRoles()->willReturn([]);

        $token = new OauthToken($request = $psrRequest->reveal(), $key = $firewallKey->reveal(), $id = $identity->reveal());

        $this->assertInstanceOf(AccessTokenId::class, $token->getCredentials());
        $this->assertEquals('bar', $token->getCredentials()->getValue());

        $this->assertEquals($request, $token->getServerRequest());
        $this->assertEquals($key, $token->getFirewallKey());
        $this->assertEquals($id, $token->getIdentity());
    }

    /**
     * @test
     */
    public function it_build_oauth_roles_from_request(): void
    {
        $key = $this->prophesize(ContextKey::class);
        $psrRequest = $this->prophesize(ServerRequestInterface::class);

        $psrRequest->getAttribute('oauth_scopes', [])->willReturn(['foo', 'foo_bar']);
        $psrRequest->getAttribute('oauth_access_token_id', null)->willReturn('bar');

        $token = new OauthToken($psrRequest->reveal(), $key->reveal(), null);

        $expected = [
            'ROLE_OAUTH_FOO',
            'ROLE_OAUTH_FOO_BAR'
        ];

        $this->assertEquals($expected, $token->getRoleNames());
    }

    /**
     * @test
     */
    public function it_build_oauth_roles_from_request_and_identity(): void
    {
        $identity = $this->prophesize(Identity::class);
        $key = $this->prophesize(ContextKey::class);
        $psrRequest = $this->prophesize(ServerRequestInterface::class);

        $psrRequest->getAttribute('oauth_scopes', [])->willReturn(['foo', 'foo_bar']);
        $psrRequest->getAttribute('oauth_access_token_id', null)->willReturn('bar');

        $identity->getRoles()->willReturn(['ROLE_BAZ']);

        $token = new OauthToken($psrRequest->reveal(), $key->reveal(), $identity->reveal());

        $expected = [
            'ROLE_OAUTH_FOO',
            'ROLE_OAUTH_FOO_BAR',
            'ROLE_BAZ',
        ];

        $this->assertEquals($expected, $token->getRoleNames());
    }

    protected function getPsrHttp(): HttpMessageFactoryInterface
    {
        $psr17Factory = new Psr17Factory();

        return new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
    }
}