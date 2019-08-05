<?php

namespace MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers;

use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\ScopeInterface;

interface ProvideScope
{
    /**
     * Find scope by his identifier
     *
     * @param string $identifier
     * @return ScopeInterface|null
     */
    public function scopeOfIdentifier(string $identifier): ?ScopeInterface;

    /**
     * Persist new scope
     *
     * @param ScopeInterface $scope
     */
    public function store(ScopeInterface $scope): void;
}