<?php

namespace MerchantOfComplexity\Oauth\League\Repository;

use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use MerchantOfComplexity\Oauth\Infrastructure\AuthorizationCode\AuthCodeProvider;
use MerchantOfComplexity\Oauth\Infrastructure\Client\ClientProvider;
use MerchantOfComplexity\Oauth\League\Entity\AuthCode;
use MerchantOfComplexity\Oauth\Support\Contracts\Transformer\ScopeTransformer;

final class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    /**
     * @var AuthCodeProvider
     */
    private $authCodeProvider;

    /**
     * @var ClientProvider
     */
    private $clientProvider;

    /**
     * @var ScopeTransformer
     */
    private $scopeTransformer;

    public function __construct(AuthCodeProvider $authCodeProvider,
                                ClientProvider $clientProvider,
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
            'scopes' => $this->scopeTransformer->toModelArray($authCodeEntity->getScopes()),
            'expires_at' => $authCodeEntity->getExpiryDateTime()
        ];

        $this->authCodeProvider->store($data);
    }

    /**
     * Revoke an auth code.
     *
     * @param string $codeId
     */
    public function revokeAuthCode($codeId)
    {
        // TODO: Implement revokeAuthCode() method.
    }

    /**
     * Check if the auth code has been revoked.
     *
     * @param string $codeId
     *
     * @return bool Return true if this code has been revoked
     */
    public function isAuthCodeRevoked($codeId)
    {
        // TODO: Implement isAuthCodeRevoked() method.
    }
}