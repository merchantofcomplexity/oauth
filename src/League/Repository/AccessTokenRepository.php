<?php

namespace MerchantOfComplexity\Oauth\League\Repository;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use MerchantOfComplexity\Oauth\League\Entity\AccessToken;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers\ProvideAccessToken;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers\ProvideClient;
use MerchantOfComplexity\Oauth\Support\Contracts\Transformer\ScopeTransformer;

final class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    /**
     * @var ProvideAccessToken
     */
    private $accessTokenProvider;

    /**
     * @var ProvideClient
     */
    private $clientProvider;

    /**
     * @var ScopeTransformer
     */
    private $scopeTransformer;

    public function __construct(ProvideAccessToken $accessTokenProvider,
                                ProvideClient $clientProvider,
                                ScopeTransformer $scopeTransformer)
    {
        $this->accessTokenProvider = $accessTokenProvider;
        $this->clientProvider = $clientProvider;
        $this->scopeTransformer = $scopeTransformer;
    }

    public function getNewToken(ClientEntityInterface $clientEntity,
                                array $scopes,
                                $userIdentifier = null): AccessTokenEntityInterface
    {
        $accessToken = new AccessToken();

        $accessToken->setClient($clientEntity);
        $accessToken->setUserIdentifier($userIdentifier);

        foreach ($scopes as $scope) {
            $accessToken->addScope($scope);
        }

        return $accessToken;
    }

    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
    {
        if ($this->accessTokenProvider->tokenOfIdentifier($accessTokenEntity->getIdentifier())) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }

        $client = $this->clientProvider->clientOfIdentifier(
            $accessTokenEntity->getClient()->getIdentifier()
        );

        $data = [
            'identifier' => $accessTokenEntity->getIdentifier(),
            'client_id' => $client->getId(),
            'identity_id' => $accessTokenEntity->getUserIdentifier(),
            'scopes' => json_encode($this->scopeTransformer->toStringArray($accessTokenEntity->getScopes())),
            'expires_at' => $accessTokenEntity->getExpiryDateTime()
        ];

        $this->accessTokenProvider->store($data);
    }

    public function revokeAccessToken($tokenId): void
    {
        $accessToken = $this->accessTokenProvider->tokenOfIdentifier($tokenId);

        if ($accessToken) {
            $accessToken->revoke();
        }
    }

    public function isAccessTokenRevoked($tokenId): bool
    {
        $accessToken = $this->accessTokenProvider->tokenOfIdentifier($tokenId);

        if (!$accessToken) {
            return true;
        }

        return $accessToken->isRevoked();
    }
}