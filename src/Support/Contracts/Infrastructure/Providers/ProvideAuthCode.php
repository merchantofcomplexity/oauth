<?php

namespace MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers;

use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\AuthorizationCodeInterface;

interface ProvideAuthCode
{
    /**
     * Find Authorization code by his identifier
     *
     * @param string $identifier
     * @return AuthorizationCodeInterface|null
     */
    public function authCodeOfIdentifier(string $identifier): ?AuthorizationCodeInterface;

    /**
     * Persist new authorization code
     *
     * @param array $data
     */
    public function store(array $data): void;
}