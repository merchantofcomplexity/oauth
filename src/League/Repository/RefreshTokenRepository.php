<?php

namespace MerchantOfComplexity\Oauth\League\Repository;

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use MerchantOfComplexity\Oauth\League\Entity\RefreshToken;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers\ProvideAccessToken;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers\ProvideRefreshToken;

final class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    /**
     * @var ProvideRefreshToken
     */
    private $refreshTokenProvider;

    /**
     * @var ProvideAccessToken
     */
    private $accessTokenProvider;

    public function __construct(ProvideRefreshToken $refreshTokenProvider,
                                ProvideAccessToken $accessTokenProvider)
    {
        $this->refreshTokenProvider = $refreshTokenProvider;
        $this->accessTokenProvider = $accessTokenProvider;
    }

    public function getNewRefreshToken()
    {
        return new RefreshToken();
    }

    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        if ($this->refreshTokenProvider->refreshTokenOfIdentifier($refreshTokenEntity->getIdentifier())) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }

        $accessToken = $this->accessTokenProvider->tokenOfIdentifier(
            $refreshTokenEntity->getAccessToken()->getIdentifier()
        );

        $data = [
            'identifier' => $refreshTokenEntity->getIdentifier(),
            'expires_at' => $refreshTokenEntity->getExpiryDateTime(),
            'access_token' => $accessToken->getId()
        ];

        $this->refreshTokenProvider->store($data);
    }

    public function revokeRefreshToken($tokenId): void
    {
        $refreshToken = $this->refreshTokenProvider->refreshTokenOfIdentifier($tokenId);

        if ($refreshToken) {
            $refreshToken->revoke();
        }
    }

    public function isRefreshTokenRevoked($tokenId): bool
    {
        $refreshToken = $this->refreshTokenProvider->refreshTokenOfIdentifier($tokenId);

        if (!$refreshToken) {
            return true;
        }

        return $refreshToken->isRevoked();
    }
}