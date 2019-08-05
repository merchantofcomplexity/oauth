<?php

namespace MerchantOfComplexity\Oauth\League\Repository;

use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use MerchantOfComplexity\Oauth\League\Entity\AuthCode;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers\ProvideAuthCode;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers\ProvideClient;
use MerchantOfComplexity\Oauth\Support\Contracts\Transformer\ScopeTransformer;

final class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    /**
     * @var ProvideAuthCode
     */
    private $authCodeProvider;

    /**
     * @var ProvideClient
     */
    private $clientProvider;

    /**
     * @var ScopeTransformer
     */
    private $scopeTransformer;

    public function __construct(ProvideAuthCode $authCodeProvider,
                                ProvideClient $clientProvider,
                                ScopeTransformer $scopeTransformer)
    {
        $this->authCodeProvider = $authCodeProvider;
        $this->clientProvider = $clientProvider;
        $this->scopeTransformer = $scopeTransformer;
    }

    public function getNewAuthCode(): AuthCodeEntityInterface
    {
        return new AuthCode();
    }

    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity): void
    {
        if ($this->authCodeProvider->authCodeOfIdentifier($authCodeEntity->getIdentifier())) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }

        $client = $this->clientProvider->clientOfIdentifier(
            $authCodeEntity->getClient()->getIdentifier()
        );

        $data = [
            'identifier' => $authCodeEntity->getIdentifier(),
            'client_id' => $client->getId(),
            'identity_id' => $authCodeEntity->getUserIdentifier(),
            'scopes' => json_encode($this->scopeTransformer->toModelArray($authCodeEntity->getScopes())),
            'expires_at' => $authCodeEntity->getExpiryDateTime()
        ];

        $this->authCodeProvider->store($data);
    }

    public function revokeAuthCode($codeId): void
    {
        $authCode = $this->authCodeProvider->authCodeOfIdentifier($codeId);

        if ($authCode) {
            $authCode->revoke();
        }
    }

    public function isAuthCodeRevoked($codeId): bool
    {
        $authCode = $this->authCodeProvider->authCodeOfIdentifier($codeId);

        if (!$authCode) {
            return true;
        }

        return $authCode->isRevoked();
    }
}