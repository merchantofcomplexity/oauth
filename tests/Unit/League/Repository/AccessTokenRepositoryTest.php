<?php

namespace MerchantOfComplexityTest\Oauth\Unit\League\Repository;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
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
        $repo = $this->accessTokenRepositoryInstance();

        $scope = new Scope();
        $scope->setIdentifier('scope_id');

        $token = $repo->getNewToken($this->clientEntity->reveal(), [$scope], 'identity_id');

        $this->assertEquals([$scope], $token->getScopes());
        $this->assertEquals('identity_id', $token->getUserIdentifier());
        $this->assertInstanceOf(ClientEntityInterface::class, $token->getClient());
        $this->assertEquals($this->clientEntity->reveal(), $token->getClient());
    }

    /**
     * @test
     */
    public function it_persist_new_token(): void
    {
        $this->tokenEntity->getIdentifier()->willReturn('token_id');
        $this->tokenEntity->getUserIdentifier()->willReturn('identity_id');

        $scope = new Scope();
        $scope->setIdentifier('foo');

        $this->tokenEntity->getScopes()->willReturn([$scope]);
        $this->tokenEntity->getExpiryDateTime()->willReturn(
            $datetime = new \DateTimeImmutable('now')
        );
        $this->scopeTransformer->toStringArray([$scope])->willReturn(['foo']);

        $this->tokenProvider->tokenOfIdentifier('token_id')->willReturn(null);

        $this->tokenEntity->getClient()->willReturn($this->clientEntity);
        $this->clientEntity->getIdentifier()->willReturn('client_id');
        $this->clientProvider->clientOfIdentifier('client_id')->willReturn($this->client);

        $this->client->getId()->willReturn('client_id');
        $this->client->reveal();

        $expected = [
            'identifier' => 'token_id',
            'client_id' => 'client_id',
            'identity_id' => 'identity_id',
            'scopes' => json_encode(['foo']),
            'expires_at' => $datetime
        ];

        $this->tokenProvider->store(Argument::exact($expected))->shouldBeCalled();

        $this->accessTokenRepositoryInstance()->persistNewAccessToken($this->tokenEntity->reveal());
    }

    /**
     * @test
     * @expectedException \League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException
     */
    public function it_raise_exception_when_token_identifier_already_exists(): void
    {
        $token = $this->prophesize(AccessTokenInterface::class);

        $this->tokenProvider->tokenOfIdentifier(Argument::type('string'))->willReturn(
            $token->reveal()
        );

        $this->tokenEntity->getIdentifier()->willReturn('baz');

        $this->accessTokenRepositoryInstance()->persistNewAccessToken($this->tokenEntity->reveal());
    }

    /**
     * @test
     */
    public function it_revoke_token(): void
    {
        $token = $this->prophesize(AccessTokenInterface::class);
        $token->revoke()->shouldBeCalled();

        $this->tokenProvider->tokenOfIdentifier(Argument::exact('token_id'))->willReturn(
            $token->reveal()
        );

        $this->accessTokenRepositoryInstance()->revokeAccessToken('token_id');
    }

    /**
     * @test
     */
    public function it_check_token_is_revoked(): void
    {
        $token = $this->prophesize(AccessTokenInterface::class);

        $token->isRevoked()->shouldBeCalled()->willReturn(true);

        $this->tokenProvider->tokenOfIdentifier(Argument::exact('token_id'))->willReturn(
            $token->reveal()
        );

        $this->assertTrue($this->accessTokenRepositoryInstance()->isAccessTokenRevoked('token_id'));
    }

    /**
     * @test
     */
    public function it_check_token_is_revoked_2(): void
    {
        $token = $this->prophesize(AccessTokenInterface::class);

        $token->isRevoked()->shouldNotBeCalled();
        $token->reveal();

        $this->tokenProvider->tokenOfIdentifier(Argument::exact('token_id'))->willReturn(
            null
        );

        $this->assertTrue($this->accessTokenRepositoryInstance()->isAccessTokenRevoked('token_id'));
    }

    /**
     * @test
     */
    public function it_check_token_is_not_revoked(): void
    {
        $token = $this->prophesize(AccessTokenInterface::class);

        $token->isRevoked()->shouldBeCalled()->willReturn(false);

        $this->tokenProvider->tokenOfIdentifier(Argument::exact('token_id'))->willReturn(
            $token->reveal()
        );

        $this->assertFalse($this->accessTokenRepositoryInstance()->isAccessTokenRevoked('token_id'));
    }

    private function accessTokenRepositoryInstance(): AccessTokenRepositoryInterface
    {
        return new AccessTokenRepository(
            $this->tokenProvider->reveal(),
            $this->clientProvider->reveal(),
            $this->scopeTransformer->reveal(),
        );
    }

    private $tokenEntity;
    private $tokenProvider;
    private $client;
    private $clientEntity;
    private $clientProvider;
    private $scopeTransformer;

    protected function setUp(): void
    {
        $this->client = $this->prophesize(ClientInterface::class);
        $this->clientEntity = $this->prophesize(ClientEntityInterface::class);
        $this->clientProvider = $this->prophesize(ProvideClient::class);
        $this->tokenEntity = $this->prophesize(AccessTokenEntityInterface::class);
        $this->tokenProvider = $this->prophesize(ProvideAccessToken::class);
        $this->scopeTransformer = $this->prophesize(ScopeTransformer::class);
    }
}