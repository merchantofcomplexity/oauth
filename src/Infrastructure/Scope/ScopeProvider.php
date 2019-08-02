<?php

namespace MerchantOfComplexity\Oauth\Infrastructure\Scope;

class ScopeProvider
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