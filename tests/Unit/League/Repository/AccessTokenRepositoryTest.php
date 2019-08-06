<?php

namespace MerchantOfComplexityTest\Oauth\Unit\League\Repository;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use MerchantOfComplexity\Oauth\League\Entity\Scope;
use MerchantOfComplexity\Oauth\League\Repository\AccessTokenRepository;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\AccessTokenInterface;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\ClientInterface;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers\ProvideAccessToken;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers\ProvideClient;
use MerchantOfComplexity\Oauth\Support\Contracts\Transformer\ScopeTransformer;
use MerchantOfComplexityTest\Oauth\TestCase;
use Prophecy\Argument;

class AccessTokenRepositoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_create_new_token_entity_instance(): void
    {
        $clientEntity = $this->prophesize(ClientEntityInterface::class);
        $tokenProvider = $this->prophesize(ProvideAccessToken::class);
        $clientProvider = $this->prophesize(ProvideClient::class);
        $scopeTransformer = $this->prophesize(ScopeTransformer::class);

        $repo = new AccessTokenRepository(
            $tokenProvider->reveal(),
            $clientProvider->reveal(),
            $scopeTransformer->reveal(),
        );

        $scope = new Scope();
        $scope->setIdentifier('baz');

        $token = $repo->getNewToken($clientEntity->reveal(), [$scope], 'bar_bar');

        $this->assertEquals([$scope], $token->getScopes());
        $this->assertEquals('bar_bar', $token->getUserIdentifier());
        $this->assertInstanceOf(ClientEntityInterface::class, $token->getClient());
        $this->assertEquals($clientEntity->reveal(), $token->getClient());
    }

    /**
     * @test
     */
    public function it_persist_new_token(): void
    {
        $tokenEntity = $this->prophesize(AccessTokenEntityInterface::class);
        $tokenProvider = $this->prophesize(ProvideAccessToken::class);
        $client = $this->prophesize(ClientInterface::class);
        $clientEntity = $this->prophesize(ClientEntityInterface::class);
        $clientProvider = $this->prophesize(ProvideClient::class);
        $scopeTransformer = $this->prophesize(ScopeTransformer::class);

    }

    /**
     * @test
     * @expectedException \League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException
     */
    public function it_raise_exception_when_token_identifier_already_exists(): void
    {
        $token = $this->prophesize(AccessTokenInterface::class);
        $tokenEntity = $this->prophesize(AccessTokenEntityInterface::class);
        $tokenProvider = $this->prophesize(ProvideAccessToken::class);
        $clientProvider = $this->prophesize(ProvideClient::class);
        $scopeTransformer = $this->prophesize(ScopeTransformer::class);

        $tokenProvider->tokenOfIdentifier(Argument::type('string'))->willReturn(
            $token->reveal()
        );

        $tokenEntity->getIdentifier()->willReturn('baz');

        $repo = new AccessTokenRepository(
            $tokenProvider->reveal(),
            $clientProvider->reveal(),
            $scopeTransformer->reveal(),
        );

        $repo->persistNewAccessToken($tokenEntity->reveal());
    }

    /**
     * @test
     */
    public function it_revoke_token(): void
    {
        $token = $this->prophesize(AccessTokenInterface::class);
        $tokenProvider = $this->prophesize(ProvideAccessToken::class);
        $clientProvider = $this->prophesize(ProvideClient::class);
        $scopeTransformer = $this->prophesize(ScopeTransformer::class);

        $token->revoke()->shouldBeCalled();

        $tokenProvider->tokenOfIdentifier(Argument::exact('foo'))->willReturn(
            $token->reveal()
        );

        $repo = new AccessTokenRepository(
            $tokenProvider->reveal(),
            $clientProvider->reveal(),
            $scopeTransformer->reveal(),
        );

        $repo->revokeAccessToken('foo');
    }

    /**
     * @test
     */
    public function it_check_token_is_revoked(): void
    {
        $token = $this->prophesize(AccessTokenInterface::class);
        $tokenProvider = $this->prophesize(ProvideAccessToken::class);
        $clientProvider = $this->prophesize(ProvideClient::class);
        $scopeTransformer = $this->prophesize(ScopeTransformer::class);

        $token->isRevoked()->shouldBeCalled()->willReturn(true);

        $tokenProvider->tokenOfIdentifier(Argument::exact('foo'))->willReturn(
            $token->reveal()
        );

        $repo = new AccessTokenRepository(
            $tokenProvider->reveal(),
            $clientProvider->reveal(),
            $scopeTransformer->reveal(),
        );

        $this->assertTrue($repo->isAccessTokenRevoked('foo'));
    }

    /**
     * @test
     */
    public function it_check_token_is_revoked_2(): void
    {
        $token = $this->prophesize(AccessTokenInterface::class);
        $tokenProvider = $this->prophesize(ProvideAccessToken::class);
        $clientProvider = $this->prophesize(ProvideClient::class);
        $scopeTransformer = $this->prophesize(ScopeTransformer::class);

        $token->isRevoked()->shouldNotBeCalled();
        $token->reveal();

        $tokenProvider->tokenOfIdentifier(Argument::exact('foo'))->willReturn(
            null
        );

        $repo = new AccessTokenRepository(
            $tokenProvider->reveal(),
            $clientProvider->reveal(),
            $scopeTransformer->reveal(),
        );

        $this->assertTrue($repo->isAccessTokenRevoked('foo'));
    }

    /**
     * @test
     */
    public function it_check_token_is_not_revoked(): void
    {
        $token = $this->prophesize(AccessTokenInterface::class);
        $tokenProvider = $this->prophesize(ProvideAccessToken::class);
        $clientProvider = $this->prophesize(ProvideClient::class);
        $scopeTransformer = $this->prophesize(ScopeTransformer::class);

        $token->isRevoked()->shouldBeCalled()->willReturn(false);

        $tokenProvider->tokenOfIdentifier(Argument::exact('foo'))->willReturn(
            $token->reveal()
        );

        $repo = new AccessTokenRepository(
            $tokenProvider->reveal(),
            $clientProvider->reveal(),
            $scopeTransformer->reveal(),
        );

        $this->assertFalse($repo->isAccessTokenRevoked('foo'));
    }
}