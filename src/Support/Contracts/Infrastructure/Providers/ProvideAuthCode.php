<?php

namespace MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers;

use MerchantOfComplexity\Oauth\Infrastructure\AuthorizationCode\AuthCodeModel;

interface ProvideAuthCode
{
    /**
     * Find Authorization code by his identifier
     *
     * @param string $identifier
     * @return AuthCodeModel|null
     */
    public function authCodeOfIdentifier(string $identifier): ?AuthCodeModel;

    /**
     * Persist new authorization code
     *
     * @param array $data
     * @return AuthCodeModel
     */
    public function store(array $data): AuthCodeModel;
}