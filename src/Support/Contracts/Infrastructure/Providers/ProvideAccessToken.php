<?php

namespace MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers;

use MerchantOfComplexity\Authters\Support\Contract\Domain\Identity;
use MerchantOfComplexity\Oauth\Infrastructure\AccessToken\AccessTokenModel;
use MerchantOfComplexity\Oauth\Infrastructure\Client\ClientModel;

interface ProvideAccessToken
{
    /**
     * Find access token by his identifier
     *
     * @param string $identifier
     * @return AccessTokenModel|null
     */
    public function tokenOfIdentifier(string $identifier): ?AccessTokenModel;

    /**
     * Return the last non revoked access token for a client and a user
     *
     * @param ClientModel $clientModel
     * @param Identity $identity
     * @return AccessTokenModel|null
     */
    public function findValidToken(ClientModel $clientModel, Identity $identity): ?AccessTokenModel;

    /**
     * Persist new access token
     *
     * @param array $data
     * @return AccessTokenModel
     */
    public function store(array $data): AccessTokenModel;
}