<?php

namespace MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers;

use MerchantOfComplexity\Oauth\Infrastructure\Scope\ScopeModel;

interface ProvideScope
{
    /**
     * Find scope by his identifier
     *
     * @param string $identifier
     * @return ScopeModel|null
     */
    public function scopeOfIdentifier(string $identifier): ?ScopeModel;

    /**
     * Persist new scope
     *
     * @param ScopeModel $scope
     */
    public function store(ScopeModel $scope): void;
}