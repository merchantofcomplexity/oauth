<?php

namespace MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers;

use MerchantOfComplexity\Authters\Support\Contract\Domain\Identity;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\AccessTokenInterface;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\ClientInterface;

interface ProvideAccessToken
{
    /**
     * Find access token by his identifier
     *
     * @param string $identifier
     * @return AccessTokenInterface|null
     */
    public function tokenOfIdentifier(string $identifier): ?AccessTokenInterface;

    /**
     * Return the last non revoked access token for a client and a user
     *
     * @param ClientInterface $clientModel
     * @param Identity $identity
     * @return AccessTokenInterface|null
     */
    public function findValidToken(ClientInterface $clientModel, Identity $identity): ?AccessTokenInterface;

    /**
     * Persist new access token
     *
     * @param array $data
     */
    public function store(array $data): void;
}