<?php

namespace MerchantOfComplexity\Oauth\Infrastructure;

use MerchantOfComplexity\Oauth\Infrastructure\Scope\ScopeModel;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\ScopeInterface;

trait HasScopes
{
    /**
     * @return ScopeInterface[]
     */
    public function getScopes(): array
    {
        if (is_string($this['scopes'])) {
            return array_map(function(?string $scope){
                return new ScopeModel($scope);
            }, json_decode($this['scopes']));
        }

        return $this['scopes'] ?? [];
    }
}