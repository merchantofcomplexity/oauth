<?php

namespace MerchantOfComplexity\Oauth\Infrastructure\Providers;

use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\ScopeInterface;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers\ProvideScope;

final class ScopeProvider implements ProvideScope
{
    /**
     * @var ScopeInterface[]
     */
    private $scopes = [];

    public function scopeOfIdentifier(string $identifier): ?ScopeInterface
    {
        return $this->scopes[$identifier] ?? null;
    }

    public function store(ScopeInterface $scope): void
    {
        $this->scopes[(string) $scope] = $scope;
    }
}