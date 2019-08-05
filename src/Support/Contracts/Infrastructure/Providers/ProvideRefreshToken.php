<?php

namespace MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers;

use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\RefreshTokenInterface;

interface ProvideRefreshToken
{
    /**
     * Find refresh token by his identifier
     *
     * @param string $identifier
     * @return RefreshTokenInterface|null
     */
    public function refreshTokenOfIdentifier(string $identifier): ?RefreshTokenInterface;

    /**
     * Persist new refresh token
     *
     * @param array $data
     */
    public function store(array $data): void;
}