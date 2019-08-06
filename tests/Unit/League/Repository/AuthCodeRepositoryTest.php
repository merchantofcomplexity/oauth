<?php

namespace MerchantOfComplexityTest\Oauth\Unit\League\Repository;

use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use MerchantOfComplexity\Oauth\League\Entity\Scope;
use MerchantOfComplexity\Oauth\League\Repository\AuthCodeRepository;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\AuthorizationCodeInterface;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\ClientInterface;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers\ProvideAuthCode;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers\ProvideClient;
use MerchantOfComplexity\Oauth\Support\Contracts\Transformer\ScopeTransformer;
use MerchantOfComplexityTest\Oauth\TestCase;

class AuthCodeRepositoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_return_new_authorization_code_entity(): void
    {
        $this->assertInstanceOf(
            AuthCodeEntityInterface::class,
            $this->authorizationCodeRepositoryInstance()->getNewAuthCode()
        );
    }

    /**
     * @test
     */
    public function it_persist_authorization_code(): void
    {
        $client = $this->prophesize(ClientEntityInterface::class);
        $clientModel = $this->prophesize(ClientInterface::class);

        $clientModel->getId()->willReturn('client_id');
        $client->getIdentifier()->willReturn('client_id');
        $this->authCode->getClient()->willReturn($client->reveal());

        $this->clientProvider->clientOfIdentifier('client_id')->willReturn($clientModel->reveal());

        $scope = new Scope();
        $scope->setIdentifier('foo');
        $this->authCode->getScopes()->willReturn([$scope]);

        $this->scopeTransformer->toStringArray([$scope])->willReturn(['foo']);

        $this->authCode->getUserIdentifier()->willReturn('identity_id');
        $this->authCode->getExpiryDateTime()->willReturn( $datetime = new \DateTimeImmutable('now'));

        $this->authCode->getIdentifier()->willReturn('code_id');
        $this->codeProvider->authCodeOfIdentifier('code_id')->willReturn(null);

        $expected = [
            'identifier' => 'code_id',
            'client_id' => 'client_id',
            'identity_id' => 'identity_id',
            'scopes' => json_encode(['foo']),
            'expires_at' => $datetime
        ];

        $this->codeProvider->store($expected)->shouldBeCalled();

        $this->authorizationCodeRepositoryInstance()->persistNewAuthCode($this->authCode->reveal());
    }

    /**
     * @test
     * @expectedException \League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException
     */
    public function it_raise_exception_when_authorization_code_already_exists(): void
    {
        $this->authCode->getIdentifier()->willReturn('code_id');
        $this->codeProvider->authCodeOfIdentifier('code_id')->willReturn($this->authCodeModel->reveal());

        $this->authorizationCodeRepositoryInstance()->persistNewAuthCode($this->authCode->reveal());
    }

    /**
     * @test
     */
    public function it_revoke_authorization_code(): void
    {
        $this->authCodeModel->revoke()->shouldBeCalled();
        $this->codeProvider->authCodeOfIdentifier('code_id')->willReturn($this->authCodeModel->reveal());

        $this->authorizationCodeRepositoryInstance()->revokeAuthCode('code_id');
    }

    /**
     * @test
     */
    public function it_does_not_revoke_authorization_code_if_not_exists(): void
    {
        $this->authCodeModel->revoke()->shouldNotBeCalled();
        $this->codeProvider->authCodeOfIdentifier('code_id')->willReturn(null);

        $this->authorizationCodeRepositoryInstance()->revokeAuthCode('code_id');
    }

    /**
     * @test
     */
    public function it_check_if_authorization_code_is_revoked(): void
    {
        $this->authCodeModel->isRevoked()->shouldBeCalled();
        $this->codeProvider->authCodeOfIdentifier('code_id')->willReturn($this->authCodeModel->reveal());

        $this->authorizationCodeRepositoryInstance()->isAuthCodeRevoked('code_id');
    }

    /**
     * @test
     */
    public function it_check_if_authorization_code_is_revoked_when_not_exists(): void
    {
        $this->authCodeModel->isRevoked()->shouldNotBeCalled();
        $this->codeProvider->authCodeOfIdentifier('code_id')->willReturn(null);

        $this->assertTrue($this->authorizationCodeRepositoryInstance()->isAuthCodeRevoked('code_id'));
    }

    private function authorizationCodeRepositoryInstance(): AuthCodeRepositoryInterface
    {
        return new AuthCodeRepository(
            $this->codeProvider->reveal(),
            $this->clientProvider->reveal(),
            $this->scopeTransformer->reveal()
        );
    }

    private $authCode;
    private $authCodeModel;
    private $codeProvider;
    private $clientProvider;
    private $scopeTransformer;


    protected function setUp(): void
    {
        $this->authCode = $this->prophesize(AuthCodeEntityInterface::class);
        $this->authCodeModel = $this->prophesize(AuthorizationCodeInterface::class);
        $this->codeProvider = $this->prophesize(ProvideAuthCode::class);
        $this->clientProvider = $this->prophesize(ProvideClient::class);
        $this->scopeTransformer = $this->prophesize(ScopeTransformer::class);

    }
}