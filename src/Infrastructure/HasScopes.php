<?php

namespace MerchantOfComplexity\Oauth\Infrastructure;

use MerchantOfComplexity\Oauth\Infrastructure\Scope\ScopeModel;

trait HasScopes
{
    /**
     * @return ScopeModel[]
     */
    public function getScopes(): array
    {
        // fixMe
        if (is_string($this['scopes'])) {
            return array_map(function (?string $scope) {
                return new ScopeModel($scope);
            }, json_decode($this['scopes']));
        }

        return $this['scopes'] ?? [];
    }
}