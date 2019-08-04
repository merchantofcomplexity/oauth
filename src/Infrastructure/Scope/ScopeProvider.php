<?php

namespace MerchantOfComplexity\Oauth\Infrastructure\Scope;

use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers\ProvideScope;

class ScopeProvider implements ProvideScope
{
    /**
     * @var array
     */
    private $scopes = [];

    public function scopeOfIdentifier(string $identifier): ?ScopeModel
    {
        return $this->scopes[$identifier] ?? null;
    }

    public function store(ScopeModel $scope): void
    {
        $this->scopes[(string) $scope] = $scope;
    }
}