<?php

namespace MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers;

use MerchantOfComplexity\Oauth\Infrastructure\RefreshToken\RefreshTokenModel;

interface ProvideRefreshToken
{
    /**
     * Find refresh token by his identifier
     *
     * @param string $identifier
     * @return RefreshTokenModel|null
     */
    public function refreshTokenOfIdentifier(string $identifier): ?RefreshTokenModel;

    /**
     * Persist new refresh token
     *
     * @param array $data
     * @return RefreshTokenModel
     */
    public function store(array $data): RefreshTokenModel;
}